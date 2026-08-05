<?php

declare(strict_types=1);

namespace Drupal\Tests\keybolts_core\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\path_alias\Entity\PathAlias;

/** Tests the dynamic news-card payload. */
final class ArticleApiTest extends KernelTestBase {

  protected static $modules = [
    'system', 'user', 'field', 'text', 'filter', 'file', 'image', 'node', 'path_alias', 'options',
    'keybolts_core', 'keybolts_api',
  ];

  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installEntitySchema('file');
    $this->installEntitySchema('path_alias');
    $this->installSchema('node', ['node_access']);
    $this->installSchema('file', ['file_usage']);
    $this->installConfig(['node', 'field', 'filter']);
    // basic_html lives in site config, not the filter module's, so a kernel
    // test has to make one or check_markup() returns an empty string.
    \Drupal\filter\Entity\FilterFormat::create([
      'format' => 'basic_html',
      'name' => 'Basic HTML',
    ])->save();
    NodeType::create(['type' => 'article', 'name' => 'Article'])->save();
    foreach ([
      'field_article_slug', 'field_article_category_key', 'field_article_category',
      'field_article_summary', 'field_article_read_time', 'field_article_image_url',
      'field_article_author', 'field_article_updated', 'field_article_quick_answer',
      'field_article_compare', 'field_article_faqs',
      'field_article_products',
    ] as $name) {
      $this->field($name, 'string');
    }
    $this->field('field_article_body', 'text_long');
    // ArticleSerializer reads field_article_image directly (Task 3); this suite
    // doesn't exercise image rendering itself (ImageSerializerTest owns that),
    // it just needs the field to exist so ->get() doesn't blow up.
    $this->field('field_article_image', 'image');
    $this->field('field_sort_order', 'integer');
  }

  public function testArticlesArePublishedAndOrdered(): void {
    Node::create(['type' => 'article', 'title' => 'B', 'status' => 1, 'field_sort_order' => 2])->save();
    Node::create(['type' => 'article', 'title' => 'A', 'status' => 1, 'field_sort_order' => 1, 'field_article_slug' => 'a'])->save();
    Node::create(['type' => 'article', 'title' => 'Hidden', 'status' => 0, 'field_sort_order' => 0])->save();
    $rows = $this->container->get('keybolts_api.article_serializer')->all();
    $this->assertSame(['A', 'B'], array_column($rows, 'title'));
    $this->assertSame('a', $rows[0]['slug']);
  }

  public function testArticleDetailAndMissingSlug(): void {
    Node::create([
      'type' => 'article', 'title' => 'Detail', 'status' => 1,
      'field_article_slug' => 'detail', 'field_sort_order' => 1,
      'field_article_body' => [
        'value' => '<h2 id="phan-mot">Phần một</h2><p>Nội dung.</p>',
        'format' => 'basic_html',
      ],
    ])->save();
    $serializer = $this->container->get('keybolts_api.article_serializer');
    // check_markup() ran, so the heading survives and nothing raw leaks.
    $this->assertStringContainsString('<h2 id="phan-mot">Phần một</h2>', $serializer->one('detail')['body']);
    $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
    $serializer->one('missing');
  }

  public function testMentionedProductsResolveAndSkipUnknownSlugs(): void {
    NodeType::create(['type' => 'product', 'name' => 'Product'])->save();
    $product = Node::create(['type' => 'product', 'title' => 'Khóa Vân Tay Cửa Gỗ', 'status' => 1]);
    $product->save();
    PathAlias::create([
      'path' => '/node/' . $product->id(),
      'alias' => '/san-pham/khoa-van-tay-cua-go',
      'langcode' => 'en',
    ])->save();
    // AliasManager only reverse-resolves prefixes present in the router's path
    // roots, which no kernel test builds. Seed it so /node/N maps to its alias.
    $this->container->get('state')->set('router.path_roots', ['node']);
    $this->container->get('path_alias.prefix_list')->clear();

    Node::create([
      'type' => 'article', 'title' => 'Mentions', 'status' => 1,
      'field_article_slug' => 'mentions', 'field_sort_order' => 1,
      'field_article_products' => json_encode(['khoa-van-tay-cua-go', 'khong-ton-tai']),
    ])->save();

    $products = $this->container->get('keybolts_api.article_serializer')->one('mentions')['products'];
    $this->assertCount(1, $products);
    $this->assertSame('Khóa Vân Tay Cửa Gỗ', $products[0]['name']);
    $this->assertSame('san-pham/khoa-van-tay-cua-go', $products[0]['slug']);
  }

  private function field(string $name, string $type): void {
    FieldStorageConfig::create(['field_name' => $name, 'entity_type' => 'node', 'type' => $type])->save();
    FieldConfig::create(['field_name' => $name, 'entity_type' => 'node', 'bundle' => 'article', 'label' => $name])->save();
  }
}
