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

  /** The first product, used wherever a test needs one to differ. */
  private ?int $faceidNid = NULL;

  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installEntitySchema('taxonomy_term');
    $this->installSchema('node', ['node_access']);
    $this->installConfig(['node']);
    NodeType::create(['type' => 'product', 'name' => 'Product'])->save();

    foreach (['brand', 'product_category', 'finish', 'door_position'] as $vid) {
      Vocabulary::create(['vid' => $vid, 'name' => $vid])->save();
    }
    foreach ([
      'field_brand' => 'brand',
      'field_category' => 'product_category',
      'field_finish' => 'finish',
      'field_door_position' => 'door_position',
    ] as $field => $vid) {
      FieldStorageConfig::create([
        'field_name' => $field, 'entity_type' => 'node',
        'type' => 'entity_reference', 'settings' => ['target_type' => 'taxonomy_term'],
        // Matches production: one lock suits several door positions, and the
        // default cardinality of 1 would silently drop all but the first.
        'cardinality' => $field === 'field_door_position'
          ? FieldStorageConfig::CARDINALITY_UNLIMITED
          : 1,
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

    foreach (['field_faceid', 'field_remote_app'] as $flag) {
      FieldStorageConfig::create([
        'field_name' => $flag, 'entity_type' => 'node', 'type' => 'boolean',
      ])->save();
      FieldConfig::create([
        'field_name' => $flag, 'entity_type' => 'node',
        'bundle' => 'product', 'label' => $flag,
      ])->save();
    }

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
      $node = Node::create([
        'type' => 'product', 'title' => $title, 'status' => 1,
        'field_category' => $this->terms['leaf'],
      ]);
      $node->save();
      $this->faceidNid ??= (int) $node->id();
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

  public function testAFeatureIsCountedNotTurnedIntoACategory(): void {
    Node::load($this->faceidNid)->set('field_faceid', 1)->save();

    $facets = $this->container->get('keybolts_core.product_facets')->labelled([]);

    $this->assertSame(1, $facets['feature']['faceid']['count']);
    $this->assertSame(0, $facets['feature']['remoteApp']['count']);
    $this->assertSame('FaceID', $facets['feature']['faceid']['label']);
  }

  public function testFilteringOnAFeatureNarrowsToProductsThatHaveIt(): void {
    Node::load($this->faceidNid)->set('field_faceid', 1)->save();

    $query = $this->container->get('keybolts_core.product_query');
    $this->assertSame(2, $query->find([])['total']);
    $this->assertSame(1, $query->find(['faceid' => 1])['total']);
  }

  public function testOneProductAnswersToSeveralDoorPositions(): void {
    $front = Term::create(['vid' => 'door_position', 'name' => 'Cửa chính']);
    $front->save();
    $bedroom = Term::create(['vid' => 'door_position', 'name' => 'Cửa phòng']);
    $bedroom->save();

    // The whole point of the field: no duplicate record per position.
    Node::load($this->faceidNid)->set('field_door_position', [$front->id(), $bedroom->id()])->save();

    $query = $this->container->get('keybolts_core.product_query');
    $this->assertSame(1, $query->find(['position' => $front->id()])['total']);
    $this->assertSame(1, $query->find(['position' => $bedroom->id()])['total']);

    $facets = $this->container->get('keybolts_core.product_facets')->labelled([]);
    $this->assertSame(1, $facets['position'][(int) $front->id()]['count']);
    $this->assertSame(1, $facets['position'][(int) $bedroom->id()]['count']);
  }

}
