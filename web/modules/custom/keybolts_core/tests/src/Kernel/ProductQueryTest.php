<?php

namespace Drupal\Tests\keybolts_core\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * @group keybolts
 */
#[RunTestsInSeparateProcesses]
class ProductQueryTest extends KernelTestBase {

  protected static $modules = ['system', 'user', 'field', 'text', 'node', 'taxonomy', 'path_alias', 'keybolts_core'];

  private array $terms = [];

  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installEntitySchema('taxonomy_term');
    $this->installConfig(['node']);
    NodeType::create(['type' => 'product', 'name' => 'Product'])->save();

    foreach (['brand', 'product_category', 'finish'] as $vid) {
      Vocabulary::create(['vid' => $vid, 'name' => $vid])->save();
    }
    foreach ([
      'field_brand' => 'brand',
      'field_category' => 'product_category',
      'field_finish' => 'finish',
    ] as $field => $vid) {
      FieldStorageConfig::create([
        'field_name' => $field, 'entity_type' => 'node',
        'type' => 'entity_reference', 'settings' => ['target_type' => 'taxonomy_term'],
      ])->save();
      FieldConfig::create([
        'field_name' => $field, 'entity_type' => 'node', 'bundle' => 'product', 'label' => $field,
      ])->save();
    }
    FieldStorageConfig::create([
      'field_name' => 'field_sort_order', 'entity_type' => 'node', 'type' => 'integer',
    ])->save();
    FieldConfig::create([
      'field_name' => 'field_sort_order', 'entity_type' => 'node',
      'bundle' => 'product', 'label' => 'Sort',
    ])->save();

    foreach (['keybolts', 'baltica'] as $name) {
      $t = Term::create(['vid' => 'brand', 'name' => $name]);
      $t->save();
      $this->terms[$name] = $t;
    }
    foreach (['dong', 'cremone'] as $name) {
      $t = Term::create(['vid' => 'product_category', 'name' => $name]);
      $t->save();
      $this->terms[$name] = $t;
    }

    // 3 keybolts/dong, 2 baltica/cremone.
    foreach (['A', 'B', 'C'] as $title) {
      Node::create([
        'type' => 'product', 'title' => $title, 'status' => 1,
        'field_brand' => $this->terms['keybolts'], 'field_category' => $this->terms['dong'],
      ])->save();
    }
    foreach (['D', 'E'] as $title) {
      Node::create([
        'type' => 'product', 'title' => $title, 'status' => 1,
        'field_brand' => $this->terms['baltica'], 'field_category' => $this->terms['cremone'],
      ])->save();
    }
  }

  public function testUnfilteredQueryReturnsEverything(): void {
    $result = $this->container->get('keybolts_core.product_query')->find([]);
    $this->assertSame(5, $result['total']);
  }

  public function testBrandFilterNarrowsResults(): void {
    $result = $this->container->get('keybolts_core.product_query')
      ->find(['brand' => $this->terms['keybolts']->id()]);
    $this->assertSame(3, $result['total']);
  }

  public function testPaginationLimitsNodesButNotTotal(): void {
    $result = $this->container->get('keybolts_core.product_query')->find([], 'az', 1, 2);
    $this->assertSame(5, $result['total']);
    $this->assertCount(2, $result['nodes']);
  }

  public function testSortAzOrdersByTitle(): void {
    $result = $this->container->get('keybolts_core.product_query')->find([], 'az', 1, 10);
    $titles = array_map(fn($n) => $n->label(), array_values($result['nodes']));
    $this->assertSame(['A', 'B', 'C', 'D', 'E'], $titles);
  }

  public function testSortZaReversesTitleOrder(): void {
    $result = $this->container->get('keybolts_core.product_query')->find([], 'za', 1, 10);
    $titles = array_map(fn($n) => $n->label(), array_values($result['nodes']));
    $this->assertSame(['E', 'D', 'C', 'B', 'A'], $titles);
  }

  public function testFacetCountsExcludeTheirOwnAxis(): void {
    // Filtering by brand=keybolts must still report baltica's count, so the
    // user can see what switching brand would give.
    $facets = $this->container->get('keybolts_core.product_facets')
      ->counts(['brand' => $this->terms['keybolts']->id()]);

    $this->assertSame(3, $facets['brand'][$this->terms['keybolts']->id()]);
    $this->assertSame(2, $facets['brand'][$this->terms['baltica']->id()]);
    // The category axis IS constrained by the active brand filter.
    $this->assertSame(3, $facets['category'][$this->terms['dong']->id()]);
    $this->assertArrayNotHasKey($this->terms['cremone']->id(), $facets['category']);
  }
}
