<?php

declare(strict_types=1);

namespace Drupal\keybolts_api\Serializer;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\ParagraphInterface;

/**
 * The homepage's editorial content.
 *
 * Every section here used to live in utils/homeContent.ts, which made the most
 * important page on the site the only one an editor could not change.
 */
final class HomeSerializer {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly ImageSerializer $imageSerializer,
  ) {}

  public function all(): array {
    $node = $this->node();
    return [
      'hero' => [
        'eyebrow' => $this->str($node, 'field_hero_eyebrow'),
        // Lines and the gold-gradient word are resolved by the frontend, which
        // owns the styling; the editor only marks intent with * and newlines.
        'title' => $this->str($node, 'field_hero_title'),
        'subtitle' => $this->str($node, 'field_hero_subtitle'),
        // Empty until an editor uploads any; the frontend then falls back to
        // a featured product's photo, which is what the hero used to show.
        'images' => $this->images($node, 'field_hero_images'),
        'ctaPrimary' => $this->link($node, 'field_hero_cta1'),
        'ctaSecondary' => $this->link($node, 'field_hero_cta2'),
        'stats' => array_map(fn(ParagraphInterface $p) => [
          'value' => $this->str($p, 'field_stat_value'),
          'label' => $this->str($p, 'field_stat_label'),
        ], $this->paragraphs($node, 'field_hero_stats')),
      ],
      'usps' => array_map(fn(ParagraphInterface $p) => [
        'title' => $this->str($p, 'field_usp_title'),
        'desc' => $this->str($p, 'field_usp_desc'),
      ], $this->paragraphs($node, 'field_usps')),
      'categorySection' => [
        'eyebrow' => $this->str($node, 'field_cat_eyebrow'),
        'title' => $this->str($node, 'field_cat_title'),
      ],
      'featuredSection' => [
        'eyebrow' => $this->str($node, 'field_feat_eyebrow'),
        'title' => $this->str($node, 'field_feat_title'),
        'tabs' => array_map(fn(ParagraphInterface $p) => [
          'key' => $this->str($p, 'field_tab_key'),
          'label' => $this->str($p, 'field_tab_label'),
        ], $this->paragraphs($node, 'field_feat_tabs')),
      ],
      'solutionSection' => [
        'eyebrow' => $this->str($node, 'field_sol_eyebrow'),
        'title' => $this->str($node, 'field_sol_title'),
        'items' => array_map(fn(ParagraphInterface $p) => [
          'title' => $this->str($p, 'field_sol_title'),
          'desc' => $this->str($p, 'field_sol_desc'),
          'tags' => $this->multi($p, 'field_sol_tags'),
          'image' => $this->image($p, 'field_sol_image'),
          'link' => $this->link($p, 'field_sol_link'),
        ], $this->paragraphs($node, 'field_solutions')),
      ],
      'tech' => [
        'eyebrow' => $this->str($node, 'field_tech_eyebrow'),
        'title' => $this->str($node, 'field_tech_title'),
        'desc' => $this->str($node, 'field_tech_desc'),
        'features' => $this->multi($node, 'field_tech_features'),
        'image' => $this->image($node, 'field_tech_image'),
        'cta' => $this->link($node, 'field_tech_cta'),
      ],
      'consult' => [
        'eyebrow' => $this->str($node, 'field_consult_eyebrow'),
        'title' => $this->str($node, 'field_consult_title'),
        'desc' => $this->str($node, 'field_consult_desc'),
      ],
      'seo' => [
        'title' => $this->str($node, 'field_seo_title'),
        'description' => $this->str($node, 'field_seo_desc'),
      ],
    ];
  }

  private function node(): ?NodeInterface {
    $nodes = $this->entityTypeManager->getStorage('node')
      ->loadByProperties(['type' => 'home_page']);
    return $nodes ? reset($nodes) : NULL;
  }

  /** @return \Drupal\paragraphs\ParagraphInterface[] */
  private function paragraphs(NodeInterface|ParagraphInterface|null $entity, string $field): array {
    if (!$entity || !$entity->hasField($field)) {
      return [];
    }
    $out = [];
    foreach ($entity->get($field) as $item) {
      if ($item->entity instanceof ParagraphInterface) {
        $out[] = $item->entity;
      }
    }
    return $out;
  }

  private function str(NodeInterface|ParagraphInterface|null $entity, string $field): string {
    if (!$entity || !$entity->hasField($field) || $entity->get($field)->isEmpty()) {
      return '';
    }
    return (string) $entity->get($field)->value;
  }

  private function multi(NodeInterface|ParagraphInterface|null $entity, string $field): array {
    if (!$entity || !$entity->hasField($field)) {
      return [];
    }
    return array_values(array_filter(array_map(
      static fn(array $item) => (string) ($item['value'] ?? ''),
      $entity->get($field)->getValue(),
    )));
  }

  private function image(NodeInterface|ParagraphInterface|null $entity, string $field): ?array {
    if (!$entity || !$entity->hasField($field)) {
      return NULL;
    }
    return $this->imageSerializer->fromField($entity->get($field));
  }

  /** Every image on a multi-value field, in the order the editor arranged them. */
  private function images(NodeInterface|ParagraphInterface|null $entity, string $field): array {
    if (!$entity || !$entity->hasField($field)) {
      return [];
    }
    $out = [];
    foreach ($entity->get($field) as $item) {
      $image = $this->imageSerializer->fromItem($item);
      if ($image) {
        $out[] = $image;
      }
    }
    return $out;
  }

  private function link(NodeInterface|ParagraphInterface|null $entity, string $field): ?array {
    if (!$entity || !$entity->hasField($field) || $entity->get($field)->isEmpty()) {
      return NULL;
    }
    $item = $entity->get($field)->first();
    return ['label' => (string) $item->title, 'url' => $item->getUrl()->toString()];
  }

}
