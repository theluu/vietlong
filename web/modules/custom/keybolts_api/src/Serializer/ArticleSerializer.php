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
      ->condition('field_sort_order', 98, '<')
      ->sort('field_sort_order')
      ->sort('nid')
      ->execute();
    return array_values(array_map($this->toArray(...), $storage->loadMultiple($ids)));
  }

  public function one(string $slug): array {
    $storage = $this->entityTypeManager->getStorage('node');
    $ids = $storage->getQuery()->accessCheck(TRUE)
      ->condition('type', 'article')->condition('status', 1)
      ->condition('field_article_slug', $slug)->range(0, 1)->execute();
    if (!$ids || !($node = $storage->load(reset($ids)))) {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }
    return $this->toArray($node) + [
      'author' => (string) $node->get('field_article_author')->value,
      'updated' => (string) $node->get('field_article_updated')->value,
      'quickAnswer' => (string) $node->get('field_article_quick_answer')->value,
      'sections' => $this->json($node, 'field_article_sections'),
      'compareRows' => $this->json($node, 'field_article_compare'),
      'faqs' => $this->json($node, 'field_article_faqs'),
    ];
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

  private function json(NodeInterface $node, string $field): array {
    $value = (string) $node->get($field)->value;
    $decoded = json_decode($value, TRUE);
    return is_array($decoded) ? $decoded : [];
  }
}
