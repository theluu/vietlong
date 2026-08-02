<?php

declare(strict_types=1);

namespace Drupal\Tests\keybolts_core\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/** Tests the dynamic news-card payload. */
final class ArticleApiTest extends KernelTestBase {

  protected static $modules = [
    'system', 'user', 'field', 'text', 'node', 'path_alias', 'options',
    'keybolts_core', 'keybolts_api',
  ];

  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installSchema('node', ['node_access']);
    $this->installConfig(['node', 'field']);
    NodeType::create(['type' => 'article', 'name' => 'Article'])->save();
    foreach ([
      'field_article_slug', 'field_article_category_key', 'field_article_category',
      'field_article_summary', 'field_article_read_time', 'field_article_image_url',
      'field_article_author', 'field_article_updated', 'field_article_quick_answer',
      'field_article_sections', 'field_article_compare', 'field_article_faqs',
    ] as $name) {
      $this->field($name, 'string');
    }
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
      'field_article_sections' => json_encode([['title' => 'Section']]),
    ])->save();
    $serializer = $this->container->get('keybolts_api.article_serializer');
    $this->assertSame('Section', $serializer->one('detail')['sections'][0]['title']);
    $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
    $serializer->one('missing');
  }

  private function field(string $name, string $type): void {
    FieldStorageConfig::create(['field_name' => $name, 'entity_type' => 'node', 'type' => $type])->save();
    FieldConfig::create(['field_name' => $name, 'entity_type' => 'node', 'bundle' => 'article', 'label' => $name])->save();
  }
}
