<?php

declare(strict_types=1);

namespace Drupal\keybolts_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\keybolts_api\ApiEnvelope;
use Drupal\keybolts_api\Serializer\ProductSerializer;
use Drupal\keybolts_core\Service\ProductFacetBuilder;
use Drupal\keybolts_core\Service\ProductQuery;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Product endpoints.
 */
class ProductController extends ControllerBase {

  private const PER_PAGE = 12;

  public function __construct(
    private readonly ProductQuery $productQuery,
    private readonly ProductFacetBuilder $facetBuilder,
    private readonly ProductSerializer $serializer,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('keybolts_core.product_query'),
      $container->get('keybolts_core.product_facets'),
      $container->get('keybolts_api.product_serializer'),
    );
  }

  /**
   * GET /api/v1/products
   */
  public function list(Request $request) {
    $filters = array_filter([
      'brand' => $request->query->get('brand'),
      'category' => $request->query->get('category'),
      'finish' => $request->query->get('finish'),
    ]);
    $sort = (string) $request->query->get('sort', 'featured');
    $page = max(1, (int) $request->query->get('page', 1));

    $result = $this->productQuery->find($filters, $sort, $page, self::PER_PAGE);

    return ApiEnvelope::make(
      array_values(array_map(
        fn($node) => $this->serializer->card($node),
        $result['nodes']
      )),
      [
        'total' => $result['total'],
        'page' => $page,
        'limit' => self::PER_PAGE,
      ],
      $this->facetBuilder->counts($filters),
    );
  }

  /**
   * GET /api/v1/products/suggest
   *
   * Stub — implemented in Task 11. Declared now only so the route order
   * (suggest before the {slug} wildcard) is established early.
   */
  public function suggest(Request $request): JsonResponse {
    return new JsonResponse(['message' => 'Not implemented'], 501);
  }

  /**
   * GET /api/v1/products/{slug}
   *
   * Stub — implemented in Task 10.
   */
  public function detail(string $slug): JsonResponse {
    return new JsonResponse(['message' => 'Not implemented'], 501);
  }
}
