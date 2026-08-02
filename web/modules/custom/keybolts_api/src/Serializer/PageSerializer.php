<?php

declare(strict_types=1);

namespace Drupal\keybolts_api\Serializer;

use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\node\NodeInterface;

/**
 * Turns each singleton page node into the exact blocks its design renders.
 *
 * One method per page rather than a generic walker: the frontend should never
 * have to infer structure, and a wrong key here fails loudly in tests.
 */
class PageSerializer {

  public function __construct(
    private readonly FileUrlGeneratorInterface $fileUrlGenerator,
  ) {}

  public function about(NodeInterface $n): array {
    return [
      'eyebrow' => $this->str($n, 'field_eyebrow'),
      'title' => $n->label(),
      'subtitle' => $this->str($n, 'field_subtitle'),
      'heroImage' => $this->image($n, 'field_hero_image'),
      'heroCaption' => $this->str($n, 'field_hero_caption'),
      'ctaPrimary' => $this->link($n, 'field_cta_primary'),
      'ctaSecondary' => $this->link($n, 'field_cta_secondary'),
      'facts' => $this->paras($n, 'field_facts', [
        'number' => 'field_fact_number', 'label' => 'field_fact_label',
      ]),
      'storyEyebrow' => $this->str($n, 'field_story_eyebrow'),
      'storyTitle' => $this->str($n, 'field_story_title'),
      'storyBody' => $n->hasField('field_story_body') && !$n->get('field_story_body')->isEmpty()
        ? $n->get('field_story_body')->processed
        : '',
      'credentials' => $this->multi($n, 'field_credentials'),
      'segments' => $this->paras($n, 'field_segments', [
        'title' => 'field_seg_title', 'desc' => 'field_seg_desc',
      ], 'field_seg_cta', 'field_seg_image'),
      'steps' => $this->paras($n, 'field_steps', [
        'number' => 'field_item_number', 'title' => 'field_item_title', 'desc' => 'field_item_desc',
      ]),
      'values' => $this->paras($n, 'field_values', [
        'title' => 'field_value_title', 'desc' => 'field_value_desc',
      ]),
    ];
  }

  public function dealers(NodeInterface $n): array {
    return [
      'eyebrow' => $this->str($n, 'field_eyebrow'),
      'title' => $n->label(),
      'subtitle' => $this->str($n, 'field_subtitle'),
      'benefits' => $this->paras($n, 'field_benefits', [
        'number' => 'field_item_number', 'title' => 'field_item_title', 'desc' => 'field_item_desc',
      ]),
      'criteria' => $this->multi($n, 'field_criteria'),
      'formTitle' => $this->str($n, 'field_form_title'),
      'formDesc' => $this->str($n, 'field_form_desc'),
      'successTitle' => $this->str($n, 'field_success_title'),
      'successDesc' => $this->str($n, 'field_success_desc'),
    ];
  }

  public function contact(NodeInterface $n): array {
    return [
      'eyebrow' => $this->str($n, 'field_eyebrow'),
      'title' => $n->label(),
      'subtitle' => $this->str($n, 'field_subtitle'),
      'channels' => $this->paras($n, 'field_channels', [
        'label' => 'field_ch_label', 'value' => 'field_ch_value', 'note' => 'field_ch_note',
      ], 'field_ch_url'),
      'companyName' => $this->str($n, 'field_company_name'),
      'companyAddress' => $this->str($n, 'field_company_address'),
      'responseTitle' => $this->str($n, 'field_response_title'),
      'responseBody' => $this->str($n, 'field_response_body'),
      'formTitle' => $this->str($n, 'field_form_title'),
      'formDesc' => $this->str($n, 'field_form_desc'),
      'successTitle' => $this->str($n, 'field_success_title'),
      'successDesc' => $this->str($n, 'field_success_desc'),
    ];
  }

  public function policies(NodeInterface $n): array {
    $sections = [];
    if ($n->hasField('field_sections')) {
      foreach ($n->get('field_sections') as $item) {
        $p = $item->entity;
        if (!$p) {
          continue;
        }
        $items = [];
        if ($p->hasField('field_pol_items')) {
          foreach ($p->get('field_pol_items') as $sub) {
            $row = $sub->entity;
            if (!$row) {
              continue;
            }
            $items[] = [
              'k' => (string) $row->get('field_pol_key')->value,
              'v' => (string) $row->get('field_pol_value')->value,
            ];
          }
        }
        $sections[] = [
          'key' => 'sec-' . $p->id(),
          'label' => (string) $p->get('field_pol_label')->value,
          'eyebrow' => (string) $p->get('field_pol_eyebrow')->value,
          'title' => (string) $p->get('field_pol_title')->value,
          'intro' => (string) $p->get('field_pol_intro')->value,
          'note' => (string) $p->get('field_pol_note')->value,
          'items' => $items,
        ];
      }
    }
    return [
      'eyebrow' => $this->str($n, 'field_eyebrow'),
      'title' => $n->label(),
      'subtitle' => $this->str($n, 'field_subtitle'),
      'sections' => $sections,
      'supportTitle' => $this->str($n, 'field_support_title'),
      'supportNote' => $this->str($n, 'field_support_note'),
    ];
  }

  /**
   * Flattens a paragraph field. $map is output key => paragraph field name.
   */
  private function paras(
    NodeInterface $n,
    string $field,
    array $map,
    ?string $link_field = NULL,
    ?string $image_field = NULL,
  ): array {
    if (!$n->hasField($field)) {
      return [];
    }
    $rows = [];
    foreach ($n->get($field) as $item) {
      $p = $item->entity;
      if (!$p) {
        continue;
      }
      $row = [];
      foreach ($map as $out => $source) {
        $row[$out] = $p->hasField($source) && !$p->get($source)->isEmpty()
          ? (string) $p->get($source)->value
          : '';
      }
      if ($link_field && $p->hasField($link_field) && !$p->get($link_field)->isEmpty()) {
        $row['ctaLabel'] = (string) $p->get($link_field)->title;
        $row['ctaUrl'] = $this->uriToPath((string) $p->get($link_field)->uri);
      }
      if ($image_field && $p->hasField($image_field) && !$p->get($image_field)->isEmpty()) {
        $file = $p->get($image_field)->entity;
        $row['image'] = $file
          ? $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri())
          : '';
      }
      $rows[] = $row;
    }
    return $rows;
  }

  private function link(NodeInterface $n, string $field): array {
    if (!$n->hasField($field) || $n->get($field)->isEmpty()) {
      return ['label' => '', 'url' => ''];
    }
    return [
      'label' => (string) $n->get($field)->title,
      'url' => $this->uriToPath((string) $n->get($field)->uri),
    ];
  }

  /**
   * Drupal stores internal links as `internal:/foo`; the frontend wants `/foo`.
   */
  private function uriToPath(string $uri): string {
    return str_starts_with($uri, 'internal:') ? substr($uri, 9) : $uri;
  }

  private function image(NodeInterface $n, string $field): string {
    if (!$n->hasField($field) || $n->get($field)->isEmpty()) {
      return '';
    }
    $file = $n->get($field)->entity;
    return $file ? $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri()) : '';
  }

  private function str(NodeInterface $n, string $field): string {
    if (!$n->hasField($field) || $n->get($field)->isEmpty()) {
      return '';
    }
    return (string) $n->get($field)->value;
  }

  private function multi(NodeInterface $n, string $field): array {
    if (!$n->hasField($field)) {
      return [];
    }
    return array_values(array_map(
      static fn(array $i) => (string) ($i['value'] ?? ''),
      $n->get($field)->getValue(),
    ));
  }
}
