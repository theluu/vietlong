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
  ];

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
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
        $out[$axis][$tid] = $row;
      }
    }
    return $out;
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

    foreach (self::AXES as $key => $filter_field) {
      if (!empty($filters[$key])) {
        $query->condition($filter_field, $filters[$key]);
      }
    }

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
    return $tally;
  }

}
