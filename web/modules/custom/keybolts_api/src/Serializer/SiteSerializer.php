<?php

declare(strict_types=1);

namespace Drupal\keybolts_api\Serializer;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\ParagraphInterface;

/**
 * The site-wide chrome: top bar, header, hotline, footer, social links and the
 * main menu.
 *
 * All of this was hard-coded in the Nuxt app, so a phone number change needed
 * a developer and a deploy. The frontend now reads it at runtime, which is
 * what makes "edit it in admin and see it live" possible.
 */
final class SiteSerializer {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly MenuLinkTreeInterface $menuTree,
  ) {}

  public function all(): array {
    $node = $this->settingsNode();
    return [
      'menu' => $this->menu('main'),
      'topbar' => [
        'text' => $this->str($node, 'field_topbar_text'),
        'badges' => $this->multi($node, 'field_topbar_badges'),
      ],
      'header' => [
        'tagline' => $this->str($node, 'field_header_tagline'),
        'cta' => $this->link($node, 'field_header_cta'),
      ],
      'contact' => [
        'hotline' => $this->str($node, 'field_hotline') ?: '1900 9018',
        'hotlineTel' => $this->str($node, 'field_hotline_tel') ?: '19009018',
        'email' => $this->str($node, 'field_email'),
        'companyName' => $this->str($node, 'field_company_name'),
        'companyShort' => $this->str($node, 'field_company_short'),
        'address' => $this->str($node, 'field_address'),
        'workingHours' => $this->multi($node, 'field_working_hours'),
      ],
      'footer' => [
        'description' => $this->str($node, 'field_footer_desc'),
        'copyright' => $this->str($node, 'field_copyright'),
        'columns' => $this->footerColumns($node),
      ],
      'social' => $this->social($node),
      'seo' => [
        'title' => $this->str($node, 'field_seo_title'),
        'description' => $this->str($node, 'field_seo_desc'),
      ],
    ];
  }

  /**
   * Cache tags so an edit invalidates the frontend's copy immediately.
   */
  public function cacheTags(): array {
    return ['node_list:site_settings', 'config:system.menu.main'];
  }

  private function settingsNode(): ?NodeInterface {
    $nodes = $this->entityTypeManager->getStorage('node')
      ->loadByProperties(['type' => 'site_settings']);
    return $nodes ? reset($nodes) : NULL;
  }

  /**
   * Flattens the enabled links of a menu into label/url pairs.
   */
  private function menu(string $name): array {
    $parameters = (new MenuTreeParameters())->onlyEnabledLinks()->setMaxDepth(2);
    $tree = $this->menuTree->load($name, $parameters);
    $tree = $this->menuTree->transform($tree, [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ]);

    $out = [];
    foreach ($tree as $element) {
      $link = $element->link;
      $item = [
        'label' => (string) $link->getTitle(),
        'to' => $link->getUrlObject()->toString(),
      ];
      if ($element->subtree) {
        $item['children'] = [];
        foreach ($element->subtree as $child) {
          $item['children'][] = [
            'label' => (string) $child->link->getTitle(),
            'to' => $child->link->getUrlObject()->toString(),
          ];
        }
      }
      $out[] = $item;
    }
    return $out;
  }

  private function social(?NodeInterface $node): array {
    $out = [];
    foreach ($this->paragraphs($node, 'field_social') as $p) {
      $url = $this->link($p, 'field_social_url');
      if (!$url) {
        continue;
      }
      $out[] = [
        'label' => $this->str($p, 'field_social_label'),
        'icon' => $this->str($p, 'field_social_icon'),
        'url' => $url['url'],
      ];
    }
    return $out;
  }

  private function footerColumns(?NodeInterface $node): array {
    $out = [];
    foreach ($this->paragraphs($node, 'field_footer_columns') as $column) {
      $links = [];
      foreach ($this->paragraphs($column, 'field_fcol_links') as $link) {
        $resolved = $this->link($link, 'field_flink_url');
        $links[] = [
          'label' => $this->str($link, 'field_flink_label'),
          'to' => $resolved['url'] ?? '#',
        ];
      }
      $out[] = ['title' => $this->str($column, 'field_fcol_title'), 'links' => $links];
    }
    return $out;
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

  private function link(NodeInterface|ParagraphInterface|null $entity, string $field): ?array {
    if (!$entity || !$entity->hasField($field) || $entity->get($field)->isEmpty()) {
      return NULL;
    }
    $item = $entity->get($field)->first();
    return [
      'label' => (string) $item->title,
      'url' => $item->getUrl()->toString(),
    ];
  }

}
