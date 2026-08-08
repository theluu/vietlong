<?php

declare(strict_types=1);

namespace Drupal\keybolts_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\keybolts_api\ApiEnvelope;
use Drupal\keybolts_api\Serializer\ProductSerializer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * One category and the choices directly beneath it.
 *
 * Serves the landing page of a top category, which the catalogue brief asks
 * to be a way of choosing a direction rather than a wall of models: someone
 * looking for a smart lock picks a door type first, because a lock for a
 * wooden door and one for an aluminium-and-glass door are different products
 * and no amount of scrolling resolves that for them.
 *
 * Separate from the homepage endpoint on purpose. Every child needs its own
 * photo, and finding one costs a product query per term — a price the
 * homepage should not pay for a panel it never renders.
 */
class CategoryController extends ControllerBase {

  /**
   * How many products to look through for a child's photo.
   */
  private const IMAGE_SEARCH_LIMIT = 12;

  public function __construct(
    private readonly ProductSerializer $serializer,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static($container->get('keybolts_api.product_serializer'));
  }

  /**
   * GET /api/v1/categories/{tid}
   */
  public function show(string $tid) {
    $term = $this->entityTypeManager()->getStorage('taxonomy_term')->load((int) $tid);
    if (!$term || $term->bundle() !== 'product_category') {
      throw new NotFoundHttpException('Không tìm thấy danh mục');
    }

    $children = [];
    foreach ($this->entityTypeManager()->getStorage('taxonomy_term')
      ->loadTree('product_category', (int) $tid, 1, TRUE) as $child) {
      $count = $this->countIn((int) $child->id());
      // A branch with nothing in it is a plan, not a choice. Offering it
      // would send someone to an empty listing.
      if (!$count) {
        continue;
      }
      $children[] = [
        'id' => (int) $child->id(),
        'name' => $child->label(),
        'desc' => $child->hasField('field_short_desc')
          ? (string) $child->get('field_short_desc')->value
          : '',
        'count' => $count,
        'image' => $this->firstPhoto((int) $child->id()),
      ];
    }

    return ApiEnvelope::make(
      [
        'id' => (int) $term->id(),
        'name' => $term->label(),
        'desc' => $term->hasField('field_short_desc')
          ? (string) $term->get('field_short_desc')->value
          : '',
        'parent' => (int) ($term->get('parent')->target_id ?? 0),
        'total' => $this->countIn((int) $tid),
        'children' => $children,
      ],
      [],
      [],
      ['node_list:product', 'taxonomy_term_list'],
    );
  }

  /**
   * Published products filed under a term or anything below it.
   */
  private function countIn(int $tid): int {
    return (int) $this->entityTypeManager()->getStorage('node')->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'product')
      ->condition('status', 1)
      ->condition('field_category', $this->withDescendants($tid), 'IN')
      ->count()
      ->execute();
  }

  /**
   * The first photo among a branch's products, or NULL if none has one.
   */
  private function firstPhoto(int $tid): ?array {
    $ids = $this->entityTypeManager()->getStorage('node')->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'product')
      ->condition('status', 1)
      ->condition('field_category', $this->withDescendants($tid), 'IN')
      ->range(0, self::IMAGE_SEARCH_LIMIT)
      ->execute();
    foreach ($this->entityTypeManager()->getStorage('node')->loadMultiple($ids) as $product) {
      $image = $this->serializer->card($product)['image'] ?? NULL;
      if ($image) {
        return $image;
      }
    }
    return NULL;
  }

  /**
   * A term id plus every id beneath it, to any depth.
   *
   * @return int[]
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
