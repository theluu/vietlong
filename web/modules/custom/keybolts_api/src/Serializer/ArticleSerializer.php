<?php

declare(strict_types=1);

namespace Drupal\keybolts_api\Serializer;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\path_alias\AliasManagerInterface;

/** Serializes published news cards in editor-controlled order. */
final class ArticleSerializer {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly AliasManagerInterface $aliasManager,
    private readonly ProductSerializer $productSerializer,
    private readonly ImageSerializer $imageSerializer,
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
      'body' => $this->body($node, 'field_article_body'),
      'compareRows' => $this->rows($node, 'field_article_compare', ['door', 'thickness', 'lock', 'backup']),
      'faqs' => $this->rows($node, 'field_article_faqs', ['question', 'answer']),
      'products' => $this->products($node),
    ];
  }

  /**
   * Resolves the editor-picked product slugs into cards.
   *
   * The field stores bare aliases (`khoa-van-tay-cua-go`) rather than a copy of
   * the product data, so a renamed or unpublished product drops out of the
   * sidebar instead of going stale.
   */
  private function products(NodeInterface $node): array {
    if (!$node->hasField('field_article_products')) {
      return [];
    }
    $storage = $this->entityTypeManager->getStorage('node');
    $cards = [];
    foreach ($this->lines($node, 'field_article_products') as $slug) {
      if (!is_string($slug) || $slug === '') {
        continue;
      }
      $path = $this->aliasManager->getPathByAlias('/san-pham/' . $slug);
      if (!preg_match('#^/node/(\d+)$#', $path, $m)) {
        continue;
      }
      $product = $storage->load((int) $m[1]);
      if ($product instanceof NodeInterface && $product->isPublished()) {
        $cards[] = $this->productSerializer->card($product);
      }
    }
    return $cards;
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
      'image' => $this->imageSerializer->fromField($node->get('field_article_image')),
    ];
  }

  /**
   * Editor-written HTML, run through the text format's filters.
   *
   * This is the only gate: the raw value never leaves Drupal, so a paste from
   * an untrusted source cannot carry a script tag or an on* attribute out to
   * the frontend, which renders this with v-html.
   */
  private function body(NodeInterface $node, string $field): string {
    if (!$node->hasField($field) || $node->get($field)->isEmpty()) {
      return '';
    }
    $item = $node->get($field)->first();
    return (string) check_markup(
      (string) $item->value,
      $item->format ?: 'basic_html',
      $node->language()->getId(),
    );
  }

  /**
   * Rows typed one per line, columns separated by `|`.
   *
   * This replaced hand-written JSON. An editor asked to type
   * `[{"door":"Gỗ tự nhiên",...}]` will eventually miss a brace and lose the
   * whole block to a silent parse failure; a line either splits or it does
   * not. Old JSON values are still read so a half-migrated site keeps working.
   *
   * @param string[] $columns
   *   Output keys, in the order the columns are typed.
   */
  private function rows(NodeInterface $node, string $field, array $columns): array {
    $value = trim((string) $node->get($field)->value);
    if ($value === '') {
      return [];
    }
    if (str_starts_with($value, '[')) {
      $decoded = json_decode($value, TRUE);
      return is_array($decoded) ? $decoded : [];
    }

    $out = [];
    foreach (preg_split('/\r\n|\r|\n/', $value) as $line) {
      $line = trim($line);
      if ($line === '') {
        continue;
      }
      $cells = array_map('trim', explode('|', $line));
      // Short lines pad rather than drop: a missing trailing column is a
      // half-finished row the editor can still see and finish, not a reason
      // to make their work vanish.
      $row = [];
      foreach (array_values($columns) as $i => $key) {
        $row[$key] = $cells[$i] ?? '';
      }
      $out[] = $row;
    }
    return $out;
  }

  /** One value per line — product slugs, and nothing else so far. */
  private function lines(NodeInterface $node, string $field): array {
    $value = trim((string) $node->get($field)->value);
    if ($value === '') {
      return [];
    }
    if (str_starts_with($value, '[')) {
      $decoded = json_decode($value, TRUE);
      return is_array($decoded) ? $decoded : [];
    }
    return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $value))));
  }
}
