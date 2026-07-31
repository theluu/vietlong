<?php

declare(strict_types=1);

namespace Drupal\keybolts_core\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryInterface;

/**
 * Filters, sorts and paginates product nodes.
 */
class ProductQuery {

  /** Filter key => field name. */
  private const FILTER_FIELDS = [
    'brand' => 'field_brand',
    'category' => 'field_category',
    'finish' => 'field_finish',
  ];

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * @return array{nodes: array, total: int}
   */
  public function find(array $filters, string $sort = 'featured', int $page = 1, int $limit = 12): array {
    $storage = $this->entityTypeManager->getStorage('node');

    $total = (int) $this->baseQuery($filters)->count()->execute();

    $query = $this->baseQuery($filters);
    $this->applySort($query, $sort);
    $page = max(1, $page);
    $query->range(($page - 1) * $limit, $limit);
    $ids = $query->execute();

    return [
      'nodes' => $ids ? $storage->loadMultiple($ids) : [],
      'total' => $total,
    ];
  }

  /**
   * Builds a query with the shared bundle/status conditions and filters.
   */
  public function baseQuery(array $filters): QueryInterface {
    $query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'product')
      ->condition('status', 1);

    foreach (self::FILTER_FIELDS as $key => $field) {
      if (!empty($filters[$key])) {
        $query->condition($field, $filters[$key]);
      }
    }
    return $query;
  }

  /**
   * The ordered [field, direction] pairs for a sort key.
   *
   * Sort keys mirror the design's select: featured | az | za | cat. There is
   * deliberately no price sort — price always renders as "Liên hệ". An
   * unrecognised sort value falls back to featured.
   *
   * Every spec ends with `['nid', 'ASC']`. The preceding keys are not unique
   * per row on their own — bulk-imported products routinely share a
   * `created` timestamp and a default `field_sort_order` of 0 — and
   * `find()` paginates with `range()`. Without a unique final key, ties are
   * ordered arbitrarily by the database and can differ between the count
   * query and the page query, or between two page requests, causing a
   * product to appear on two pages or on none. `nid` is unique per row and
   * gives every sort a total order.
   *
   * Extracted as a pure function (rather than inlined in applySort()) so the
   * tiebreaker invariant can be asserted directly, with no database
   * involved: paging through real rows can never reliably prove a
   * tiebreaker is present, because the engine's tie order is undefined and,
   * in practice, is often stable by coincidence anyway.
   *
   * @return array<int, array{0: string, 1: string}>
   */
  public static function sortSpec(string $sort): array {
    $spec = match ($sort) {
      'az' => [['title', 'ASC']],
      'za' => [['title', 'DESC']],
      'cat' => [['field_category', 'ASC'], ['title', 'ASC']],
      default => [['field_sort_order', 'ASC'], ['created', 'DESC']],
    };
    $spec[] = ['nid', 'ASC'];
    return $spec;
  }

  private function applySort(QueryInterface $query, string $sort): void {
    foreach (self::sortSpec($sort) as [$field, $direction]) {
      $query->sort($field, $direction);
    }
  }

}
