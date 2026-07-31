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

  /**
   * Guards the aggregate-query rewrite of ProductFacetBuilder::counts().
   *
   * A hand-rolled aggregate query is easy to get subtly wrong: a term with
   * zero matching products could wrongly appear (e.g. iterating all terms
   * instead of grouped rows), and combining more than one active filter
   * could silently drop a JOIN condition. This covers both.
   */
  public function testFacetCountsOmitZeroCountsAndRespectMultipleActiveFilters(): void {
    // A category term with no products attached must never appear in the
    // facet at all — not even with a count of 0.
    $unused = Term::create(['vid' => 'product_category', 'name' => 'unused']);
    $unused->save();

    // Give two of the keybolts/dong nodes a finish value so a second axis
    // has real data to exercise; C is left without one.
    $pvd = Term::create(['vid' => 'finish', 'name' => 'pvd']);
    $pvd->save();
    $dsf = Term::create(['vid' => 'finish', 'name' => 'dsf']);
    $dsf->save();

    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $a_matches = $node_storage->loadByProperties(['title' => 'A']);
    $a = reset($a_matches);
    $a->set('field_finish', $pvd)->save();
    $b_matches = $node_storage->loadByProperties(['title' => 'B']);
    $b = reset($b_matches);
    $b->set('field_finish', $dsf)->save();

    $facet_service = $this->container->get('keybolts_core.product_facets');

    $facets = $facet_service->counts([]);
    $this->assertArrayNotHasKey($unused->id(), $facets['category']);
    $this->assertSame(1, $facets['finish'][$pvd->id()]);
    $this->assertSame(1, $facets['finish'][$dsf->id()]);

    // Two simultaneously active filters: brand=keybolts AND category=dong
    // match exactly A, B, C. Only A/B carry a finish value.
    $facets_two_axis = $facet_service->counts([
      'brand' => $this->terms['keybolts']->id(),
      'category' => $this->terms['dong']->id(),
    ]);
    $this->assertSame(1, $facets_two_axis['finish'][$pvd->id()]);
    $this->assertSame(1, $facets_two_axis['finish'][$dsf->id()]);

    // The brand axis still excludes its own filter but still respects the
    // active category filter: baltica has zero dong-category products, so
    // it must be entirely absent, while keybolts (all three dong products)
    // reports 3.
    $this->assertSame(3, $facets_two_axis['brand'][$this->terms['keybolts']->id()]);
    $this->assertArrayNotHasKey($this->terms['baltica']->id(), $facets_two_axis['brand']);
  }
}
