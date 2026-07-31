<?php

namespace Drupal\Tests\keybolts_core\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * @group keybolts
 */
#[RunTestsInSeparateProcesses]
class VariantMatrixBuilderTest extends KernelTestBase {

  protected static $modules = ['system', 'user', 'field', 'text', 'node', 'taxonomy', 'path_alias', 'keybolts_core'];

  /**
   * Creates the product bundle and the fields the builder reads.
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installEntitySchema('taxonomy_term');
    $this->installEntitySchema('path_alias');
    $this->installConfig(['node']);
    \Drupal\node\Entity\NodeType::create(['type' => 'product', 'name' => 'Product'])->save();
    \Drupal\taxonomy\Entity\Vocabulary::create(['vid' => 'finish', 'name' => 'Finish'])->save();

    $string_fields = [
      'field_product_code', 'field_family', 'field_size_key',
      'field_size_label', 'field_size_note',
    ];
    foreach ($string_fields as $name) {
      \Drupal\field\Entity\FieldStorageConfig::create([
        'field_name' => $name, 'entity_type' => 'node', 'type' => 'string',
      ])->save();
      \Drupal\field\Entity\FieldConfig::create([
        'field_name' => $name, 'entity_type' => 'node', 'bundle' => 'product', 'label' => $name,
      ])->save();
    }
    \Drupal\field\Entity\FieldStorageConfig::create([
      'field_name' => 'field_finish', 'entity_type' => 'node',
      'type' => 'entity_reference', 'settings' => ['target_type' => 'taxonomy_term'],
    ])->save();
    \Drupal\field\Entity\FieldConfig::create([
      'field_name' => 'field_finish', 'entity_type' => 'node',
      'bundle' => 'product', 'label' => 'Finish',
    ])->save();
    \Drupal\field\Entity\FieldStorageConfig::create([
      'field_name' => 'field_swatch', 'entity_type' => 'taxonomy_term', 'type' => 'string',
    ])->save();
    \Drupal\field\Entity\FieldConfig::create([
      'field_name' => 'field_swatch', 'entity_type' => 'taxonomy_term',
      'bundle' => 'finish', 'label' => 'Swatch',
    ])->save();
  }

  /**
   * Creates one product node.
   */
  private function product(string $title, string $code, string $family, string $size, string $label, $finish): Node {
    $node = Node::create([
      'type' => 'product', 'title' => $title, 'status' => 1,
      'field_product_code' => $code, 'field_family' => $family,
      'field_size_key' => $size, 'field_size_label' => $label,
      'field_size_note' => 'note', 'field_finish' => $finish,
    ]);
    $node->save();
    return $node;
  }

  public function testSiblingSizesAreExposedWithSlugs(): void {
    $pvd = \Drupal\taxonomy\Entity\Term::create(['vid' => 'finish', 'name' => 'Vàng bóng PVD', 'field_swatch' => '#c69148']);
    $pvd->save();

    $xl = $this->product('Đại Sảnh', 'KB 1700-XL-PVD', 'KB 1700', 'xl', 'Đại sảnh XL', $pvd);
    $this->product('Đại', 'KB 1700-L-PVD', 'KB 1700', 'l', 'Đại L', $pvd);

    $matrix = $this->container->get('keybolts_core.variant_matrix')->build($xl);

    $this->assertSame('KB 1700', $matrix['family']);
    $this->assertCount(2, $matrix['sizes']);
    $keys = array_column($matrix['sizes'], 'key');
    $this->assertContains('xl', $keys);
    $this->assertContains('l', $keys);
    foreach ($matrix['sizes'] as $size) {
      $this->assertTrue($size['available']);
      $this->assertNotNull($size['slug']);
    }
  }

  public function testMissingCombinationIsUnavailableAndHasNoSlug(): void {
    $pvd = \Drupal\taxonomy\Entity\Term::create(['vid' => 'finish', 'name' => 'PVD', 'field_swatch' => '#c69148']);
    $pvd->save();
    $dsf = \Drupal\taxonomy\Entity\Term::create(['vid' => 'finish', 'name' => 'DSF', 'field_swatch' => '#6b6f5c']);
    $dsf->save();

    // XL exists in both finishes; L only in PVD.
    $xl_pvd = $this->product('XL PVD', 'KB 1700-XL-PVD', 'KB 1700', 'xl', 'Đại sảnh XL', $pvd);
    $this->product('XL DSF', 'KB 1700-XL-DSF', 'KB 1700', 'xl', 'Đại sảnh XL', $dsf);
    $this->product('L PVD', 'KB 1700-L-PVD', 'KB 1700', 'l', 'Đại L', $pvd);

    // Viewing XL/DSF: size L is not available in DSF.
    $xl_dsf = $this->container->get('entity_type.manager')->getStorage('node')
      ->loadByProperties(['field_product_code' => 'KB 1700-XL-DSF']);
    $matrix = $this->container->get('keybolts_core.variant_matrix')->build(reset($xl_dsf));

    $sizes = array_column($matrix['sizes'], NULL, 'key');
    $this->assertTrue($sizes['xl']['available']);
    $this->assertFalse($sizes['l']['available']);
    $this->assertNull($sizes['l']['slug']);

    // Viewing XL/PVD: both finishes are available for size XL.
    $matrix_pvd = $this->container->get('keybolts_core.variant_matrix')->build($xl_pvd);
    $finishes = array_column($matrix_pvd['finishes'], NULL, 'key');
    $this->assertCount(2, $finishes);
    foreach ($finishes as $finish) {
      $this->assertTrue($finish['available']);
    }
  }

  public function testProductWithoutFamilyReturnsItselfOnly(): void {
    $node = $this->product('Lẻ', 'KB 9999', '', '', '', NULL);
    $matrix = $this->container->get('keybolts_core.variant_matrix')->build($node);
    $this->assertSame([], $matrix['sizes']);
    $this->assertSame([], $matrix['finishes']);
  }
}
