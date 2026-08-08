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
 * Facet counting against the three-level catalogue tree.
 *
 * @group keybolts
 */
#[RunTestsInSeparateProcesses]
class ProductFacetTreeTest extends KernelTestBase {

  protected static $modules = ['system', 'user', 'field', 'text', 'node', 'taxonomy', 'path_alias', 'options', 'keybolts_core'];

  private array $terms = [];

  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installEntitySchema('taxonomy_term');
    $this->installSchema('node', ['node_access']);
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

    // ProductQuery::find() always sorts by this, so the field has to exist
    // even though nothing here sets it.
    FieldStorageConfig::create([
      'field_name' => 'field_sort_order', 'entity_type' => 'node', 'type' => 'integer',
    ])->save();
    FieldConfig::create([
      'field_name' => 'field_sort_order', 'entity_type' => 'node',
      'bundle' => 'product', 'label' => 'Sort',
    ])->save();

    // The shape the catalogue actually has: root -> category -> leaf.
    //   root
    //   ├─ mid            (2 products, on its leaf)
    //   │  └─ leaf
    //   └─ empty          a placeholder with nothing filed under it
    foreach ([
      'root' => 0,
      'mid' => 'root',
      'leaf' => 'mid',
      'empty' => 'root',
    ] as $name => $parent) {
      $term = Term::create([
        'vid' => 'product_category',
        'name' => $name,
        'parent' => $parent ? [$this->terms[$parent]->id()] : [0],
      ]);
      $term->save();
      $this->terms[$name] = $term;
    }

    foreach (['A', 'B'] as $title) {
      Node::create([
        'type' => 'product', 'title' => $title, 'status' => 1,
        'field_category' => $this->terms['leaf'],
      ])->save();
    }
  }

  public function testCountRollsUpThroughEveryAncestor(): void {
    $counts = $this->container->get('keybolts_core.product_facets')->counts([])['category'];

    $this->assertSame(2, $counts[(int) $this->terms['leaf']->id()]);
    $this->assertSame(2, $counts[(int) $this->terms['mid']->id()], 'The parent subtotals its leaf.');
    $this->assertSame(2, $counts[(int) $this->terms['root']->id()], 'The root subtotals two levels down.');
  }

  public function testFilteringOnAMidLevelCategoryIncludesItsDescendants(): void {
    $result = $this->container->get('keybolts_core.product_query')
      ->find(['category' => $this->terms['mid']->id()]);

    $this->assertSame(2, $result['total']);
  }

  public function testAnEmptyCategoryStillCarriesItsLabel(): void {
    $tid = (int) $this->terms['empty']->id();
    $facets = $this->container->get('keybolts_core.product_facets')
      ->labelled(['category' => $tid]);

    // /danh-muc/<tid> reads the name off this payload and 404s without it,
    // which is what a category created before its stock used to do.
    $this->assertSame('empty', $facets['category'][$tid]['label']);
    $this->assertSame(0, $facets['category'][$tid]['count']);
  }

  public function testAnEmptyCategoryIsNotOfferedAsAFilterElsewhere(): void {
    $facets = $this->container->get('keybolts_core.product_facets')->labelled([]);

    $this->assertArrayNotHasKey((int) $this->terms['empty']->id(), $facets['category']);
  }

}
