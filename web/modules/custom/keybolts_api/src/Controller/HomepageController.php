<?php

declare(strict_types=1);

namespace Drupal\keybolts_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\keybolts_api\ApiEnvelope;
use Drupal\keybolts_api\Serializer\ProductSerializer;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Aggregates everything the homepage needs into one response.
 */
class HomepageController extends ControllerBase {

  private const FEATURED_GROUPS = ['dong', 'cremone', 'hotel', 'phukien'];

  public function __construct(
    private readonly ProductSerializer $serializer,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static($container->get('keybolts_api.product_serializer'));
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
      ],
      [],
      [],
      ['node_list:product', 'taxonomy_term_list'],
    );
  }

  /**
   * The eight catalogue categories, in weight order.
   */
  private function categories(): array {
    $terms = $this->entityTypeManager()->getStorage('taxonomy_term')
      ->loadTree('product_category', 0, NULL, TRUE);
    $out = [];
    foreach ($terms as $term) {
      $out[] = [
        'id' => (int) $term->id(),
        'name' => $term->label(),
        'number' => $term->hasField('field_number') ? (string) $term->get('field_number')->value : '',
        'desc' => $term->hasField('field_short_desc') ? (string) $term->get('field_short_desc')->value : '',
      ];
    }
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
      $ids = $storage->getQuery()
        ->accessCheck(TRUE)
        ->condition('type', 'product')
        ->condition('status', 1)
        ->condition('field_featured_group', $group)
        ->range(0, 5)
        ->execute();
      if (!$ids) {
        $ids = $storage->getQuery()
          ->accessCheck(TRUE)
          ->condition('type', 'product')
          ->condition('status', 1)
          ->sort('created', 'DESC')
          ->range(0, 5)
          ->execute();
      }
      $out[$group] = array_values(array_map(
        fn($n) => $this->serializer->card($n),
        $storage->loadMultiple($ids)
      ));
    }
    return $out;
  }
}
