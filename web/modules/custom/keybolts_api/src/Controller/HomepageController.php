<?php

declare(strict_types=1);

namespace Drupal\keybolts_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\keybolts_api\ApiEnvelope;
use Drupal\keybolts_api\Serializer\HomeSerializer;
use Drupal\keybolts_api\Serializer\ProductSerializer;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Aggregates everything the homepage needs into one response.
 */
class HomepageController extends ControllerBase {

  private const FEATURED_GROUPS = ['dong', 'cremone', 'hotel', 'phukien'];

  /**
   * Enough cards that the carousel arrows have somewhere to go.
   *
   * The grid shows four across at desktop width, so a tab of exactly four was
   * a carousel with nothing to scroll.
   */
  private const FEATURED_LIMIT = 8;

  /**
   * The categories a tab draws from before an editor has curated it.
   *
   * field_featured_group is still the real control. But it is empty on the
   * whole imported catalogue, and the old fallback — newest products, site
   * wide — handed all four tabs the same four cards, so the tab strip looked
   * broken. Scoping the fallback by category keeps four tabs distinct.
   */
  private const FEATURED_FALLBACK_CATEGORIES = [
    'dong' => ['Khóa đồng'],
    'cremone' => ['Chốt Cremone'],
    'hotel' => ['Khóa khách sạn'],
    'phukien' => ['Phụ kiện cửa', 'Bản lề & tay co'],
  ];

  public function __construct(
    private readonly ProductSerializer $serializer,
    private readonly HomeSerializer $home,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('keybolts_api.product_serializer'),
      $container->get('keybolts_api.home_serializer'),
    );
  }

  /**
   * GET /api/v1/homepage
   */
  public function index() {
    return ApiEnvelope::make(
      [
        'categories' => $this->categories(),
        'brands' => $this->brands(),
        'featured' => $this->featured(),
      ] + $this->home->all(),
      [],
      [],
      ['node_list:product', 'node_list:home_page', 'taxonomy_term_list'],
    );
  }

  /**
   * The eight catalogue categories, in weight order.
   */
  private function categories(): array {
    $terms = $this->entityTypeManager()->getStorage('taxonomy_term')
      // Depth 1: the CSV import files products under finer child terms, and
      // the homepage grid is a designed set of eight tiles, not a directory.
      ->loadTree('product_category', 0, 1, TRUE);
    $out = [];
    foreach ($terms as $term) {
      // The whole card image, not just its url: flattening it here would make
      // this the one grid on the homepage that cannot pick a smaller file.
      $image = NULL;
      $ids = $this->entityTypeManager()->getStorage('node')->getQuery()
        ->accessCheck(TRUE)
        ->condition('type', 'product')
        ->condition('status', 1)
        // Descendants included: the CSV files every product under a leaf term,
        // so six of the eight tiles matched nothing and came back imageless.
        ->condition('field_category', $this->withDescendants((int) $term->id()), 'IN')
        // Not every product carries a photo; take the first one that does.
        ->range(0, 12)
        ->execute();
      foreach ($this->entityTypeManager()->getStorage('node')->loadMultiple($ids) as $product) {
        $image = $this->serializer->card($product)['image'] ?? NULL;
        if ($image) {
          break;
        }
      }
      $out[] = [
        'id' => (int) $term->id(),
        'name' => $term->label(),
        'number' => $term->hasField('field_number') ? (string) $term->get('field_number')->value : '',
        'desc' => $term->hasField('field_short_desc') ? (string) $term->get('field_short_desc')->value : '',
        'image' => $image,
      ];
    }
    usort($out, static fn(array $a, array $b): int => $a['number'] <=> $b['number']);
    return $out;
  }

  private function brands(): array {
    $terms = $this->entityTypeManager()->getStorage('taxonomy_term')
      ->loadTree('brand', 0, NULL, TRUE);
    return array_values(array_map(static fn($t) => [
      'id' => (int) $t->id(),
      'name' => $t->label(),
      'tag' => $t->hasField('field_tag') ? (string) $t->get('field_tag')->value : '',
      'desc' => $t->getDescription() ?? '',
      'cta' => $t->hasField('field_cta_label') ? (string) $t->get('field_cta_label')->value : '',
    ], $terms));
  }

  /**
   * Four tabs of featured products, matching the prototype's tab groups.
   *
   * Falls back to the most recent products in a group's absence so the
   * homepage is never empty before an editor has curated the tabs.
   */
  private function featured(): array {
    $storage = $this->entityTypeManager()->getStorage('node');
    $out = [];
    foreach (self::FEATURED_GROUPS as $group) {
      $ids = array_values($storage->getQuery()
        ->accessCheck(TRUE)
        ->condition('type', 'product')
        ->condition('status', 1)
        ->condition('field_featured_group', $group)
        ->sort('created', 'DESC')
        ->range(0, self::FEATURED_LIMIT)
        ->execute());
      if (count($ids) < self::FEATURED_LIMIT) {
        // Merged, not unioned: an entity query keys its result by revision id,
        // so `+=` silently dropped fallback rows whose key already existed.
        $ids = array_merge($ids, $this->fallbackFeatured($group, self::FEATURED_LIMIT - count($ids), $ids));
      }
      $nodes = $storage->loadMultiple($ids);
      $out[$group] = array_values(array_map(
        fn($nid) => $this->serializer->card($nodes[$nid]),
        array_filter($ids, static fn($nid) => isset($nodes[$nid])),
      ));
    }
    return $out;
  }

  /**
   * Products for a tab nobody has curated yet.
   *
   * @param string $group
   *   The featured-group key the tab is showing.
   * @param int $limit
   *   How many cards the tab is still short of.
   * @param array $exclude
   *   Node ids the tab already has.
   */
  private function fallbackFeatured(string $group, int $limit, array $exclude): array {
    $terms = [];
    foreach (self::FEATURED_FALLBACK_CATEGORIES[$group] ?? [] as $name) {
      foreach ($this->entityTypeManager()->getStorage('taxonomy_term')
        ->loadByProperties(['vid' => 'product_category', 'name' => $name]) as $term) {
        $terms = array_merge($terms, $this->withDescendants((int) $term->id()));
      }
    }
    $query = $this->entityTypeManager()->getStorage('node')->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'product')
      ->condition('status', 1)
      ->sort('created', 'DESC')
      ->range(0, $limit);
    // Without a mapped category this is the old site-wide fallback, which is
    // still better than an empty tab.
    if ($terms) {
      $query->condition('field_category', $terms, 'IN');
    }
    if ($exclude) {
      $query->condition('nid', $exclude, 'NOT IN');
    }
    return array_values($query->execute());
  }

  /**
   * A category term id together with every id beneath it.
   */
  private function withDescendants(int $tid): array {
    $ids = [$tid];
    foreach ($this->entityTypeManager()->getStorage('taxonomy_term')
      ->loadTree('product_category', $tid, NULL, FALSE) as $child) {
      $ids[] = (int) $child->tid;
    }
    return $ids;
  }

}
