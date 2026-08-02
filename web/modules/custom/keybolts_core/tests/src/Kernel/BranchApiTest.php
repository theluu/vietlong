<?php

declare(strict_types=1);

namespace Drupal\Tests\keybolts_core\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Branches must come back in the editor's chosen order, not creation order.
 */
#[RunTestsInSeparateProcesses]
class BranchApiTest extends KernelTestBase {

  protected static $modules = [
    'system', 'user', 'field', 'text', 'node', 'link', 'path_alias',
    'options', 'keybolts_core', 'keybolts_api',
  ];

  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installSchema('node', ['node_access']);
    $this->installConfig(['node', 'field']);

    NodeType::create(['type' => 'branch', 'name' => 'Branch'])->save();
    foreach ([['B', 2], ['A', 1], ['C', 3]] as [$title, $weight]) {
      $this->createBranchField();
      Node::create([
        'type' => 'branch',
        'title' => $title,
        'status' => 1,
        'field_sort_order' => $weight,
      ])->save();
    }
  }

  /**
   * Creates field_sort_order once; later calls are no-ops.
   */
  private function createBranchField(): void {
    if (\Drupal\field\Entity\FieldStorageConfig::loadByName('node', 'field_sort_order')) {
      return;
    }
    \Drupal\field\Entity\FieldStorageConfig::create([
      'field_name' => 'field_sort_order',
      'entity_type' => 'node',
      'type' => 'integer',
    ])->save();
    \Drupal\field\Entity\FieldConfig::create([
      'field_name' => 'field_sort_order',
      'entity_type' => 'node',
      'bundle' => 'branch',
      'label' => 'Sort',
    ])->save();
  }

  public function testBranchesAreOrderedBySortOrder(): void {
    $titles = array_map(
      static fn(array $b) => $b['name'],
      $this->container->get('keybolts_api.branch_serializer')->all(),
    );
    $this->assertSame(['A', 'B', 'C'], $titles);
  }
}
