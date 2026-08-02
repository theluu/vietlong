<?php

declare(strict_types=1);

namespace Drupal\Tests\keybolts_core\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** Tests project collection and detail serialization. */
final class ProjectApiTest extends KernelTestBase {

  protected static $modules = ['system', 'user', 'field', 'node', 'path_alias', 'options', 'keybolts_core', 'keybolts_api'];

  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installSchema('node', ['node_access']);
    $this->installConfig(['node', 'field']);
    NodeType::create(['type' => 'project', 'name' => 'Project'])->save();
    foreach (['field_project_slug', 'field_project_type_key', 'field_project_type', 'field_project_desc', 'field_project_products', 'field_project_image_url'] as $name) {
      $this->field($name, 'string');
    }
    $this->field('field_sort_order', 'integer');
  }

  public function testProjectsAreOrderedAndDetailIsFound(): void {
    Node::create(['type' => 'project', 'title' => 'B', 'status' => 1, 'field_sort_order' => 2, 'field_project_slug' => 'b'])->save();
    Node::create(['type' => 'project', 'title' => 'A', 'status' => 1, 'field_sort_order' => 1, 'field_project_slug' => 'a'])->save();
    Node::create(['type' => 'project', 'title' => 'Hidden', 'status' => 0, 'field_sort_order' => 0])->save();
    $serializer = $this->container->get('keybolts_api.project_serializer');
    $this->assertSame(['A', 'B'], array_column($serializer->all(), 'title'));
    $this->assertSame('B', $serializer->one('b')['title']);
    $this->expectException(NotFoundHttpException::class);
    $serializer->one('missing');
  }

  private function field(string $name, string $type): void {
    FieldStorageConfig::create(['field_name' => $name, 'entity_type' => 'node', 'type' => $type])->save();
    FieldConfig::create(['field_name' => $name, 'entity_type' => 'node', 'bundle' => 'project', 'label' => $name])->save();
  }
}
