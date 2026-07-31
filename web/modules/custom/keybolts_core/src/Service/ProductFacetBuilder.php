<?php

declare(strict_types=1);

namespace Drupal\keybolts_core\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Counts products per taxonomy value for the listing sidebar.
 */
class ProductFacetBuilder {

  private const AXES = [
    'brand' => 'field_brand',
    'category' => 'field_category',
    'finish' => 'field_finish',
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
    $storage = $this->entityTypeManager->getStorage('node');
    $out = [];

    foreach (self::AXES as $axis => $field) {
      $scoped = $filters;
      unset($scoped[$axis]);

      $ids = $this->productQuery->baseQuery($scoped)->execute();
      $tally = [];
      foreach ($storage->loadMultiple($ids) as $node) {
        if (!$node->hasField($field) || $node->get($field)->isEmpty()) {
          continue;
        }
        $tid = (int) $node->get($field)->target_id;
        $tally[$tid] = ($tally[$tid] ?? 0) + 1;
      }
      $out[$axis] = $tally;
    }

    return $out;
  }

}
