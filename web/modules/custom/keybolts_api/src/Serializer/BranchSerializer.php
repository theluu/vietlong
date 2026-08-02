<?php

declare(strict_types=1);

namespace Drupal\keybolts_api\Serializer;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

/**
 * Branches are shared by the homepage, About, Dealers and Contact pages.
 */
class BranchSerializer {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Every published branch, in the editor's chosen order.
   *
   * @return array<int, array>
   */
  public function all(): array {
    $storage = $this->entityTypeManager->getStorage('node');
    $ids = $storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'branch')
      ->condition('status', 1)
      ->sort('field_sort_order', 'ASC')
      // Total order: without a unique tiebreaker, equal weights can swap
      // between requests.
      ->sort('nid', 'ASC')
      ->execute();
    return array_values(array_map(
      fn(NodeInterface $n) => $this->toArray($n),
      $storage->loadMultiple($ids),
    ));
  }

  public function toArray(NodeInterface $node): array {
    return [
      'id' => (int) $node->id(),
      'name' => $node->label(),
      'tag' => $this->str($node, 'field_tag'),
      'address' => $this->str($node, 'field_address'),
      'phoneDisplay' => $this->str($node, 'field_phone_display'),
      'phoneTel' => $this->str($node, 'field_phone_tel'),
      'mapUrl' => $node->hasField('field_map_url') && !$node->get('field_map_url')->isEmpty()
        ? (string) $node->get('field_map_url')->uri
        : '',
    ];
  }

  private function str(NodeInterface $node, string $field): string {
    if (!$node->hasField($field) || $node->get($field)->isEmpty()) {
      return '';
    }
    return (string) $node->get($field)->value;
  }
}
