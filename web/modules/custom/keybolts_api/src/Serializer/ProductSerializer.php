<?php

declare(strict_types=1);

namespace Drupal\keybolts_api\Serializer;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\node\NodeInterface;

/**
 * Converts product nodes into the shapes the frontend consumes.
 */
class ProductSerializer {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly FileUrlGeneratorInterface $fileUrlGenerator,
    private readonly AliasManagerInterface $aliasManager,
  ) {}

  /**
   * The shape used by product cards in listings and carousels.
   */
  public function card(NodeInterface $node): array {
    return [
      'id' => (int) $node->id(),
      'slug' => $this->slug($node),
      'name' => $node->label(),
      'model' => $this->str($node, 'field_product_code'),
      'family' => $this->str($node, 'field_family'),
      'badge' => $this->listLabel($node, 'field_badge'),
      'brand' => $this->term($node, 'field_brand'),
      'category' => $this->term($node, 'field_category'),
      'finish' => $this->term($node, 'field_finish'),
      'image' => $this->firstImage($node),
      'stockStatus' => $this->listLabel($node, 'field_stock_status'),
      'contactPrice' => (bool) $this->str($node, 'field_contact_price'),
    ];
  }

  /**
   * The card shape plus everything the detail page renders.
   */
  public function detail(NodeInterface $node): array {
    return $this->card($node) + [
      'shortDesc' => $this->str($node, 'field_short_desc'),
      'descHeading' => $this->str($node, 'field_desc_heading'),
      'description' => $node->hasField('field_description') && !$node->get('field_description')->isEmpty()
        ? $node->get('field_description')->processed
        : '',
      'highlights' => $this->multi($node, 'field_highlights'),
      'certification' => $this->multi($node, 'field_certification'),
      'warranty' => $this->str($node, 'field_warranty'),
      'doorThickness' => $this->str($node, 'field_door_thickness'),
      'origin' => $this->str($node, 'field_origin'),
      'sizeLabel' => $this->str($node, 'field_size_label'),
      'sizeNote' => $this->str($node, 'field_size_note'),
      'images' => $this->images($node),
      'specifications' => $this->paragraphs($node, 'field_specifications', [
        'k' => 'field_spec_key', 'v' => 'field_spec_value',
      ]),
      'faqs' => $this->paragraphs($node, 'field_faqs', [
        'q' => 'field_faq_question', 'a' => 'field_faq_answer',
      ]),
      'policyCards' => $this->paragraphs($node, 'field_policy_cards', [
        'title' => 'field_card_title', 'desc' => 'field_card_desc',
      ]),
    ];
  }

  /**
   * Flattens a paragraph reference field into plain rows.
   *
   * @param array $map
   *   Output key => paragraph field name.
   */
  private function paragraphs(NodeInterface $node, string $field, array $map): array {
    if (!$node->hasField($field)) {
      return [];
    }
    $rows = [];
    foreach ($node->get($field) as $item) {
      $paragraph = $item->entity;
      if (!$paragraph) {
        continue;
      }
      $row = [];
      foreach ($map as $out => $source) {
        $row[$out] = $paragraph->hasField($source) && !$paragraph->get($source)->isEmpty()
          ? (string) $paragraph->get($source)->value
          : '';
      }
      $rows[] = $row;
    }
    return $rows;
  }

  private function str(NodeInterface $node, string $field): string {
    if (!$node->hasField($field) || $node->get($field)->isEmpty()) {
      return '';
    }
    return (string) $node->get($field)->value;
  }

  private function multi(NodeInterface $node, string $field): array {
    if (!$node->hasField($field)) {
      return [];
    }
    return array_map(
      static fn(array $item) => (string) ($item['value'] ?? ''),
      $node->get($field)->getValue()
    );
  }

  /**
   * The human label of a list field, e.g. "Còn hàng — giao 2–5 ngày".
   */
  private function listLabel(NodeInterface $node, string $field): ?string {
    if (!$node->hasField($field) || $node->get($field)->isEmpty()) {
      return NULL;
    }
    $key = $node->get($field)->value;
    $allowed = $node->get($field)->getFieldDefinition()
      ->getFieldStorageDefinition()->getSetting('allowed_values');
    return $allowed[$key] ?? (string) $key;
  }

  private function term(NodeInterface $node, string $field): ?array {
    if (!$node->hasField($field) || $node->get($field)->isEmpty()) {
      return NULL;
    }
    $term = $node->get($field)->entity;
    if (!$term) {
      return NULL;
    }
    $out = ['id' => (int) $term->id(), 'name' => $term->label()];
    if ($term->hasField('field_swatch') && !$term->get('field_swatch')->isEmpty()) {
      $out['swatch'] = (string) $term->get('field_swatch')->value;
    }
    return $out;
  }

  private function firstImage(NodeInterface $node): ?array {
    return $this->images($node)[0] ?? NULL;
  }

  private function images(NodeInterface $node): array {
    if (!$node->hasField('field_images')) {
      return [];
    }
    $out = [];
    foreach ($node->get('field_images') as $item) {
      $file = $item->entity;
      if (!$file) {
        continue;
      }
      $out[] = [
        'url' => $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri()),
        'alt' => (string) $item->alt,
      ];
    }
    return $out;
  }

  private function slug(NodeInterface $node): string {
    return ltrim($this->aliasManager->getAliasByPath('/node/' . $node->id()), '/');
  }
}
