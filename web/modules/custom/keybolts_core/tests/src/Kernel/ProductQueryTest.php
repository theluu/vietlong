<?php

namespace Drupal\Tests\keybolts_core\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\keybolts_core\Service\ProductQuery;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * @group keybolts
 */
#[RunTestsInSeparateProcesses]
class ProductQueryTest extends KernelTestBase {

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

  /**
   * Guards the tiebreaker invariant directly, with no database involved.
   *
   * Paging through real rows can never reliably prove a tiebreaker is
   * missing: the engine's tie order is undefined, and in practice can be
   * stable by coincidence (e.g. MariaDB returning ties in primary-key order
   * for this query shape), so an integration test that pages and inspects
   * results can pass identically with or without the fix. Asserting the
   * spec directly is deterministic on any database, because it never runs
   * a query at all.
   */
  public function testSortSpecEndsWithUniqueNidTiebreakerForEveryKey(): void {
    foreach (['featured', 'az', 'za', 'cat', 'some-unrecognised-value', ''] as $sort) {
      $spec = ProductQuery::sortSpec($sort);
      $this->assertSame(
        ['nid', 'ASC'],
        end($spec),
        "sort '$sort' must end with a unique nid tiebreaker"
      );
    }
  }

  public function testSortSpecPromisedKeysPrecedeTheTiebreaker(): void {
    $this->assertSame([['title', 'ASC'], ['nid', 'ASC']], ProductQuery::sortSpec('az'));
    $this->assertSame([['title', 'DESC'], ['nid', 'ASC']], ProductQuery::sortSpec('za'));
    $this->assertSame(
      [['field_category', 'ASC'], ['title', 'ASC'], ['nid', 'ASC']],
      ProductQuery::sortSpec('cat')
    );
    $this->assertSame(
      [['field_sort_order', 'ASC'], ['created', 'DESC'], ['nid', 'ASC']],
      ProductQuery::sortSpec('featured')
    );
  }

  public function testSortSpecUnknownKeyFallsBackToFeatured(): void {
    $this->assertSame(ProductQuery::sortSpec('featured'), ProductQuery::sortSpec('totally-bogus'));
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

  /**
   * A facet keyed only by term ID cannot be drawn as a filter list: nothing
   * else in the API resolves a term ID to its name. labelled() carries the
   * label (and swatch, where the vocabulary has one) alongside the count.
   */
  public function testLabelledFacetsCarryTermNames(): void {
    $facets = $this->container->get('keybolts_core.product_facets')->labelled([]);

    $keybolts_id = $this->terms['keybolts']->id();
    $this->assertSame(
      $this->terms['keybolts']->label(),
      $facets['brand'][$keybolts_id]['label'],
    );
    // The count must survive the decoration unchanged.
    $counts = $this->container->get('keybolts_core.product_facets')->counts([]);
    $this->assertSame($counts['brand'][$keybolts_id], $facets['brand'][$keybolts_id]['count']);

    // A term with no products is still absent, exactly as in counts().
    $unused = Term::create(['vid' => 'product_category', 'name' => 'unused']);
    $unused->save();
    $this->assertArrayNotHasKey(
      $unused->id(),
      $this->container->get('keybolts_core.product_facets')->labelled([])['category'],
    );
  }

  /**
   * Guards the `nid` tiebreaker added to every branch of applySort().
   *
   * A sort with no unique final key is not a total order: when paginated
   * with range(), the database is free to place a tied row on either side
   * of a page boundary, differently on each request. That means the same
   * product can appear on two pages, or on none. Bulk-imported catalogues
   * routinely produce exactly this: identical `created` timestamps and a
   * default `field_sort_order` of 0 for every row.
   *
   * This test only proves something by checking coverage, not order: it
   * would fail under the pre-fix sort (no `nid` tiebreaker) roughly as
   * often as the database happens to reorder ties across separate range()
   * queries, and always passes once every sort branch ends in a unique key.
   */
  public function testPaginationIsStableUnderSortTies(): void {
    // Deliberately identical field_sort_order and created values — exactly
    // what defeats a sort with no unique tiebreaker.
    $tied_ids = [];
    foreach (range(1, 6) as $i) {
      $node = Node::create([
        'type' => 'product', 'title' => "Tied $i", 'status' => 1,
        'field_brand' => $this->terms['keybolts'], 'field_category' => $this->terms['dong'],
        'field_sort_order' => 5, 'created' => 1000000000,
      ]);
      $node->save();
      $tied_ids[] = (int) $node->id();
    }

    $query_service = $this->container->get('keybolts_core.product_query');
    // 5 fixture nodes (A-E, also tied on created/sort_order by default) + 6
    // deliberately tied nodes = 11 total, paginated 3 at a time.
    $total = $query_service->find([])['total'];
    $this->assertSame(11, $total);

    $seen = [];
    $pages = (int) ceil($total / 3);
    for ($page = 1; $page <= $pages; $page++) {
      $result = $query_service->find([], 'featured', $page, 3);
      foreach ($result['nodes'] as $node) {
        $seen[] = (int) $node->id();
      }
    }

    $this->assertCount($total, $seen, 'Every product must appear exactly once across all pages.');
    $this->assertCount($total, array_unique($seen), 'No product may appear on more than one page.');
    foreach ($tied_ids as $tid) {
      $this->assertContains($tid, $seen);
    }
  }
}
