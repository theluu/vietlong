<?php

declare(strict_types=1);

namespace Drupal\keybolts_api\Serializer;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

/** Serializes published news cards in editor-controlled order. */
final class ArticleSerializer {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  public function all(): array {
    $storage = $this->entityTypeManager->getStorage('node');
    $ids = $storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'article')
      ->condition('status', 1)
      ->sort('field_sort_order')
      ->sort('nid')
      ->execute();
    return array_values(array_map($this->toArray(...), $storage->loadMultiple($ids)));
  }

  private function toArray(NodeInterface $node): array {
    return [
      'id' => (int) $node->id(),
      'slug' => (string) $node->get('field_article_slug')->value,
      'categoryKey' => (string) $node->get('field_article_category_key')->value,
      'category' => (string) $node->get('field_article_category')->value,
      'title' => $node->label(),
      'summary' => (string) $node->get('field_article_summary')->value,
      'readTime' => (string) $node->get('field_article_read_time')->value,
      'image' => (string) $node->get('field_article_image_url')->value,
    ];
  }
}
