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
   * Sort keys mirror the design's select: featured | az | za | cat.
   *
   * There is deliberately no price sort — price always renders as "Liên hệ".
   * An unrecognised sort value falls back to featured.
   *
   * Every branch ends with `sort('nid', 'ASC')`. The preceding keys are not
   * unique per row on their own — bulk-imported products routinely share a
   * `created` timestamp and a default `field_sort_order` of 0 — and this
   * query is paginated with `range()`. Without a unique final key, ties are
   * ordered arbitrarily by the database and can differ between the count
   * query and the page query, or between two page requests, causing a
   * product to appear on two pages or on none. `nid` is unique per row and
   * gives the order (and therefore the pagination) a total order.
   */
  private function applySort(QueryInterface $query, string $sort): void {
    match ($sort) {
      'az' => $query->sort('title', 'ASC'),
      'za' => $query->sort('title', 'DESC'),
      'cat' => $query->sort('field_category', 'ASC')->sort('title', 'ASC'),
      default => $query->sort('field_sort_order', 'ASC')->sort('created', 'DESC'),
    };
    $query->sort('nid', 'ASC');
  }

}
