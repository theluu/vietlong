<?php

declare(strict_types=1);

namespace Drupal\keybolts_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\keybolts_api\ApiEnvelope;
use Drupal\keybolts_api\Serializer\ProductSerializer;
use Drupal\keybolts_core\Service\ProductFacetBuilder;
use Drupal\keybolts_core\Service\ProductQuery;
use Drupal\keybolts_core\Service\TextNormalizer;
use Drupal\keybolts_core\Service\VariantMatrixBuilder;
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
    private readonly VariantMatrixBuilder $variantMatrix,
    private readonly TextNormalizer $textNormalizer,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('keybolts_core.product_query'),
      $container->get('keybolts_core.product_facets'),
      $container->get('keybolts_api.product_serializer'),
      $container->get('keybolts_core.variant_matrix'),
      $container->get('keybolts_core.text_normalizer'),
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
      $this->facetBuilder->labelled($filters),
    );
  }

  /**
   * GET /api/v1/products/suggest?q=
   *
   * Matches against the denormalised diacritic-free field so that
   * `khoa van tay` finds `Khóa Vân Tay`.
   */
  public function suggest(Request $request) {
    $raw = trim((string) $request->query->get('q', ''));
    // Cap input length to prevent excess processing.
    $raw = substr($raw, 0, 100);

    // Normalise first, then guard on the normalised needle.
    $needle = $this->textNormalizer->normalize($raw);
    if ($needle === '') {
      return ApiEnvelope::make([], ['total' => 0, 'page' => 1, 'limit' => 8]);
    }

    $storage = $this->entityTypeManager()->getStorage('node');
    $base_query = $storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'product')
      ->condition('status', 1)
      ->condition('field_search_text', '%' . $needle . '%', 'LIKE');

    // Get the true total count.
    $total = (clone $base_query)->count()->execute();

    // Get paginated results (capped at 8).
    $ids = $base_query->range(0, 8)->execute();

    $nodes = $ids ? $storage->loadMultiple($ids) : [];

    return ApiEnvelope::make(
      array_values(array_map(fn($n) => $this->serializer->card($n), $nodes)),
      ['total' => $total, 'page' => 1, 'limit' => 8],
    );
  }

  /**
   * GET /api/v1/products/{slug}
   *
   * The slug is the path alias without the `san-pham/` prefix.
   */
  public function detail(string $slug) {
    $path = \Drupal::service('path_alias.manager')
      ->getPathByAlias('/san-pham/' . $slug);
    if (!preg_match('#^/node/(\d+)$#', $path, $m)) {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }

    $node = $this->entityTypeManager()->getStorage('node')->load((int) $m[1]);
    if (!$node || $node->bundle() !== 'product' || !$node->isPublished()) {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }

    $data = $this->serializer->detail($node);
    // The approved detail design uses sibling size photography as the
    // product-family gallery when a node only owns one primary image.
    if (count($data['images']) < 4 && !empty($data['family'])) {
      $family_ids = $this->entityTypeManager()->getStorage('node')->getQuery()
        ->accessCheck(TRUE)
        ->condition('type', 'product')
        ->condition('status', 1)
        ->condition('field_family', $data['family'])
        ->condition('nid', $node->id(), '<>')
        ->range(0, 3)
        ->execute();
      foreach ($this->entityTypeManager()->getStorage('node')->loadMultiple($family_ids) as $sibling) {
        $image = $this->serializer->card($sibling)['image'];
        if ($image) {
          $data['images'][] = $image;
        }
      }
    }
    $data['variants'] = $this->variantMatrix->build($node);
    $data['related'] = $this->relatedProducts($node);
    $data['breadcrumb'] = [
      ['label' => 'Trang chủ', 'url' => '/'],
      ['label' => 'Sản phẩm', 'url' => '/san-pham'],
      ...($data['category']
        ? [['label' => $data['category']['name'], 'url' => '/danh-muc/' . $data['category']['id']]]
        : []),
      ['label' => $node->label(), 'url' => '/' . $data['slug']],
    ];
    $data['jsonLd'] = $this->productJsonLd($data);

    return ApiEnvelope::make($data, [], [], ['node:' . $node->id(), 'node_list:product']);
  }

  /**
   * Up to 8 siblings in the same category, excluding the product itself.
   */
  private function relatedProducts($node): array {
    $category = $node->get('field_category')->target_id;
    if (!$category) {
      return [];
    }
    $ids = $this->entityTypeManager()->getStorage('node')->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'product')
      ->condition('status', 1)
      ->condition('field_category', $category)
      ->condition('nid', $node->id(), '<>')
      ->range(0, 8)
      ->execute();
    if (count($ids) < 8) {
      $fallback = $this->entityTypeManager()->getStorage('node')->getQuery()
        ->accessCheck(TRUE)
        ->condition('type', 'product')
        ->condition('status', 1)
        ->condition('nid', array_merge([(int) $node->id()], array_values($ids)), 'NOT IN')
        ->range(0, 8 - count($ids))
        ->execute();
      $ids += $fallback;
    }
    return array_values(array_map(
      fn($n) => $this->serializer->card($n),
      $this->entityTypeManager()->getStorage('node')->loadMultiple($ids)
    ));
  }

  /**
   * Schema.org Product. Price is always "contact for price", expressed as a
   * PriceSpecification without a value rather than a fabricated number.
   */
  private function productJsonLd(array $data): array {
    return [
      '@context' => 'https://schema.org',
      '@type' => 'Product',
      'name' => $data['name'],
      'sku' => $data['model'],
      'brand' => ['@type' => 'Brand', 'name' => $data['brand']['name'] ?? 'Keybolts'],
      'category' => $data['category']['name'] ?? '',
      'image' => array_column($data['images'], 'url'),
      'description' => $data['shortDesc'],
      'offers' => [
        '@type' => 'Offer',
        'availability' => $data['stockStatus'] === 'Hết hàng'
          ? 'https://schema.org/OutOfStock'
          : 'https://schema.org/InStock',
        'priceCurrency' => 'VND',
        'priceSpecification' => ['@type' => 'PriceSpecification', 'priceCurrency' => 'VND'],
      ],
    ];
  }
}
