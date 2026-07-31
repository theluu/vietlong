<?php

declare(strict_types=1);

namespace Drupal\keybolts_core\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\path_alias\AliasManagerInterface;

/**
 * Builds the size × finish selector for a product from its sibling nodes.
 *
 * Each variant is a separate node; they are grouped by field_family. The
 * selector is therefore derived data — adding a node with the right family
 * extends the selector with no code or config change.
 */
class VariantMatrixBuilder {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly AliasManagerInterface $aliasManager,
  ) {}

  /**
   * @return array{family: string, sizes: array, finishes: array}
   */
  public function build(NodeInterface $product): array {
    $family = $product->hasField('field_family') ? (string) $product->get('field_family')->value : '';
    if ($family === '') {
      return ['family' => '', 'sizes' => [], 'finishes' => []];
    }

    $siblings = $this->loadFamily($family);
    $current_size = $this->sizeKey($product);
    $current_finish = $this->finishKey($product);

    return [
      'family' => $family,
      'sizes' => $this->sizeAxis($siblings, $current_finish),
      'finishes' => $this->finishAxis($siblings, $current_size),
    ];
  }

  /**
   * Loads every published product sharing the family.
   *
   * @return \Drupal\node\NodeInterface[]
   */
  private function loadFamily(string $family): array {
    $storage = $this->entityTypeManager->getStorage('node');
    $ids = $storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'product')
      ->condition('status', 1)
      ->condition('field_family', $family)
      ->execute();
    return $ids ? $storage->loadMultiple($ids) : [];
  }

  /**
   * Sizes offered, resolved against the currently selected finish.
   */
  private function sizeAxis(array $siblings, string $current_finish): array {
    $axis = [];
    foreach ($siblings as $node) {
      $key = $this->sizeKey($node);
      if ($key === '') {
        continue;
      }
      // First sighting establishes the label; later ones may fill the slug.
      $axis[$key] ??= [
        'key' => $key,
        'label' => (string) $node->get('field_size_label')->value,
        'note' => (string) $node->get('field_size_note')->value,
        'available' => FALSE,
        'slug' => NULL,
        'code' => NULL,
      ];
      if ($this->finishKey($node) === $current_finish) {
        $axis[$key]['available'] = TRUE;
        $axis[$key]['slug'] = $this->slug($node);
        $axis[$key]['code'] = (string) $node->get('field_product_code')->value;
      }
    }
    return array_values($axis);
  }

  /**
   * Finishes offered, resolved against the currently selected size.
   */
  private function finishAxis(array $siblings, string $current_size): array {
    $axis = [];
    foreach ($siblings as $node) {
      $term = $node->hasField('field_finish') ? $node->get('field_finish')->entity : NULL;
      if (!$term) {
        continue;
      }
      $key = $this->finishKey($node);
      $axis[$key] ??= [
        'key' => $key,
        'label' => $term->label(),
        'swatch' => $term->hasField('field_swatch') ? (string) $term->get('field_swatch')->value : '',
        'available' => FALSE,
        'slug' => NULL,
        'code' => NULL,
      ];
      if ($this->sizeKey($node) === $current_size) {
        $axis[$key]['available'] = TRUE;
        $axis[$key]['slug'] = $this->slug($node);
        $axis[$key]['code'] = (string) $node->get('field_product_code')->value;
      }
    }
    return array_values($axis);
  }

  private function sizeKey(NodeInterface $node): string {
    return $node->hasField('field_size_key') ? (string) $node->get('field_size_key')->value : '';
  }

  private function finishKey(NodeInterface $node): string {
    $term = $node->hasField('field_finish') ? $node->get('field_finish')->entity : NULL;
    return $term ? (string) $term->id() : '';
  }

  /**
   * Path alias without the leading slash, e.g. `san-pham/khoa-dong-dai-sanh`.
   */
  private function slug(NodeInterface $node): string {
    $path = $this->aliasManager->getAliasByPath('/node/' . $node->id());
    return ltrim($path, '/');
  }

}
