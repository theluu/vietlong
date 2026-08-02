<?php

namespace Drupal\Tests\keybolts_core\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * @group keybolts
 */
#[RunTestsInSeparateProcesses]
class ProductSearchTextTest extends KernelTestBase {

  protected static $modules = ['system', 'user', 'field', 'text', 'node', 'taxonomy', 'path_alias', 'options', 'keybolts_core'];

  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installSchema('node', ['node_access']);
    $this->installConfig(['node']);
    NodeType::create(['type' => 'product', 'name' => 'Product'])->save();
    foreach (['field_product_code' => 'string', 'field_search_text' => 'string_long'] as $name => $type) {
      FieldStorageConfig::create([
        'field_name' => $name, 'entity_type' => 'node', 'type' => $type,
      ])->save();
      FieldConfig::create([
        'field_name' => $name, 'entity_type' => 'node', 'bundle' => 'product', 'label' => $name,
      ])->save();
    }
  }

  public function testSearchTextIsPopulatedOnSave(): void {
    $node = Node::create([
      'type' => 'product',
      'title' => 'Khóa Vân Tay Cửa Kính',
      'field_product_code' => 'KB 8150',
    ]);
    $node->save();

    $text = $node->get('field_search_text')->value;
    $this->assertStringContainsString('khoa van tay cua kinh', $text);
    $this->assertStringContainsString('kb 8150', $text);
  }

  public function testSearchTextIsRefreshedOnRename(): void {
    $node = Node::create(['type' => 'product', 'title' => 'Khóa Đồng', 'field_product_code' => 'KB 1']);
    $node->save();
    $node->setTitle('Chốt Cremone');
    $node->save();

    $this->assertStringContainsString('chot cremone', $node->get('field_search_text')->value);
    $this->assertStringNotContainsString('khoa dong', $node->get('field_search_text')->value);
  }
}
