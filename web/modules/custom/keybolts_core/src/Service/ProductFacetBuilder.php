<?php

declare(strict_types=1);

namespace Drupal\keybolts_core\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Counts products per taxonomy value for the listing sidebar.
 *
 * Runs on every product-listing request, including the unfiltered catalogue
 * page, so counts are computed with an aggregate database query (COUNT(*)
 * grouped by target_id per axis) rather than by loading node entities. At
 * 200+ SKUs, loading every matching node three times over (once per axis)
 * just to read a single target_id off each would be several hundred full
 * entity loads on the hottest page of the site.
 */
class ProductFacetBuilder {

  private const AXES = [
    'brand' => 'field_brand',
    'category' => 'field_category',
    'finish' => 'field_finish',
    // Which door a lock suits — a way of searching, not a place the product
    // lives, so one product answers to several of these.
    'position' => 'field_door_position',
  ];

  /**
   * Boolean features counted as one number each, not per term.
   *
   * These say how many products would remain if the feature were required,
   * so the sidebar can show "FaceID (7)" without inventing a vocabulary for
   * something that is a yes/no on the product.
   */
  private const FLAGS = [
    'faceid' => 'field_faceid',
    'remoteApp' => 'field_remote_app',
  ];

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly ProductQuery $productQuery,
  ) {}

  /**
   * Counts each axis with its own filter removed.
   *
   * Counting an axis under its own filter would always yield a single non-zero
   * value, which tells the user nothing about what else they could pick.
   * Other active filters (e.g. brand while counting category) still apply,
   * so the sidebar reflects what's reachable from the current selection.
   *
   * @return array<string, array<int, int>>
   */
  public function counts(array $filters): array {
    $out = [];
    foreach (self::AXES as $axis => $field) {
      $scoped = $filters;
      unset($scoped[$axis]);
      $out[$axis] = $this->countAxis($field, $scoped);
    }
    return $out;
  }

  /**
   * The same counts, decorated with the term label the sidebar renders.
   *
   * counts() returns term IDs only, which a client cannot draw a filter list
   * from — there is no endpoint that resolves a term ID to its name. Loading
   * the terms here costs one multi-load of only the terms that actually
   * appear in the result, rather than the whole vocabulary.
   *
   * @return array<string, array<int, array{label: string, count: int, swatch?: string}>>
   */
  public function labelled(array $filters): array {
    $counts = $this->counts($filters);

    // A category with nothing in it yet still needs a label. /danh-muc/<tid>
    // reads the term's name off this payload, so an empty category — the
    // placeholder an editor creates before the stock arrives — answered 404
    // rather than "no products". Only the category being filtered on is
    // forced in; the sidebar keeps listing reachable options only.
    if (isset($filters['category']) && !isset($counts['category'][(int) $filters['category']])) {
      $counts['category'][(int) $filters['category']] = 0;
    }

    $ids = array_unique(array_merge(...array_map('array_keys', array_values($counts))));
    $terms = $ids
      ? $this->entityTypeManager->getStorage('taxonomy_term')->loadMultiple($ids)
      : [];

    $out = [];
    foreach ($counts as $axis => $tally) {
      $out[$axis] = [];
      foreach ($tally as $tid => $count) {
        $term = $terms[$tid] ?? NULL;
        if (!$term) {
          // A term deleted between the aggregate query and this load has
          // nothing to label; dropping it beats rendering a blank filter.
          continue;
        }
        $row = ['label' => $term->label(), 'count' => $count];
        if ($term->hasField('field_swatch') && !$term->get('field_swatch')->isEmpty()) {
          $row['swatch'] = (string) $term->get('field_swatch')->value;
        }
        // Categories are three levels deep. Without the parent, a client can
        // only draw them as one flat list, where a group of 69 products and
        // one of its own leaves of 14 look like siblings.
        //
        // Weight travels with it because this payload is keyed by term id:
        // integer-like keys come back out of JSON.parse in ascending numeric
        // order whatever order they were sent in, so the client has to sort
        // siblings itself and needs something to sort by.
        if ($axis === 'category') {
          $row['parent'] = (int) ($term->get('parent')->target_id ?? 0);
          $row['weight'] = (int) $term->getWeight();
        }
        $out[$axis][$tid] = $row;
      }
    }

    // Shaped like the term axes so the sidebar can render them with the same
    // code: a key, a label and a count.
    $out['feature'] = [];
    foreach ($this->flagCounts($filters) as $key => $count) {
      $out['feature'][$key] = [
        'label' => $key === 'faceid' ? 'FaceID' : 'Mở cửa từ xa qua app',
        'count' => $count,
      ];
    }
    return $out;
  }

  /**
   * How many products carry each boolean feature under the current filters.
   *
   * Counted with the feature's own filter removed, for the same reason the
   * term axes are: counting it under itself only ever returns the number
   * already on screen.
   *
   * @return array<string, int>
   */
  private function flagCounts(array $filters): array {
    $out = [];
    foreach (self::FLAGS as $key => $field) {
      $scoped = $filters;
      unset($scoped[$key]);

      $query = $this->entityTypeManager->getStorage('node')->getQuery()
        ->accessCheck(TRUE)
        ->condition('type', 'product')
        ->condition('status', 1)
        ->condition($field, 1);
      $this->applyFilters($query, $scoped);
      $out[$key] = (int) $query->count()->execute();
    }
    return $out;
  }

  /**
   * Puts the active filters on a query, term axes and feature flags alike.
   *
   * Shared so a facet count and the listing itself never disagree about what
   * "currently filtered" means.
   *
   * @param \Drupal\Core\Entity\Query\QueryInterface|\Drupal\Core\Entity\Query\QueryAggregateInterface $query
   *   The query to narrow.
   */
  private function applyFilters($query, array $filters): void {
    foreach (self::AXES as $key => $filter_field) {
      if (empty($filters[$key])) {
        continue;
      }
      // Match ProductQuery: a category filter covers its whole subtree, or
      // the other axes would be counted against a parent term that no
      // product references directly and come back empty.
      $query->condition($filter_field, $key === 'category'
        ? $this->productQuery->categoryWithDescendants((int) $filters[$key])
        : $filters[$key], $key === 'category' ? 'IN' : '=');
    }
    foreach (self::FLAGS as $key => $field) {
      if (!empty($filters[$key])) {
        $query->condition($field, 1);
      }
    }
  }

  /**
   * Runs a single GROUP BY / COUNT(*) query for one axis.
   *
   * Uses the entity query aggregate API rather than hand-rolled SQL so that
   * access checking and the published/bundle conditions are expressed the
   * same way as everywhere else the catalogue is queried (see
   * ProductQuery::baseQuery()), while still compiling down to one query
   * with a GROUP BY instead of loading entities.
   *
   * @return array<int, int>
   *   Term ID => product count. A term with zero matching products simply
   *   never appears as a group in the result — there is no row to produce a
   *   zero from, so it is correctly absent rather than present with 0.
   */
  private function countAxis(string $field, array $filters): array {
    $query = $this->entityTypeManager->getStorage('node')->getAggregateQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'product')
      ->condition('status', 1);

    $this->applyFilters($query, $filters);

    $query->aggregate('nid', 'COUNT')->groupBy($field);
    $result = $query->execute();

    $tid_key = $field . '_target_id';
    $tally = [];
    foreach ($result as $row) {
      // Nodes with no value on this field produce a NULL group via the
      // query's LEFT JOIN; there is no term to attribute that count to.
      if ($row[$tid_key] === NULL) {
        continue;
      }
      $tally[(int) $row[$tid_key]] = (int) $row['nid_count'];
    }
    return $field === 'field_category' ? $this->rollUpToAncestors($tally) : $tally;
  }

  /**
   * Adds each category's count to every ancestor above it.
   *
   * Products are filed against leaf terms only, so grouping by the raw field
   * never produces a row for the eight top-level categories the site is
   * designed around — they had no label to render and no count, which is why
   * /danh-muc/<parent id> answered 404.
   *
   * @param array<int, int> $tally
   *   Leaf term ID => product count.
   *
   * @return array<int, int>
   *   The same, plus a subtotal for every ancestor term.
   */
  private function rollUpToAncestors(array $tally): array {
    $storage = $this->entityTypeManager->getStorage('taxonomy_term');
    $out = $tally;
    foreach ($tally as $tid => $count) {
      foreach ($storage->loadAllParents($tid) as $ancestor) {
        $aid = (int) $ancestor->id();
        if ($aid !== $tid) {
          $out[$aid] = ($out[$aid] ?? 0) + $count;
        }
      }
    }
    return $out;
  }

}
