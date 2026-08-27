<?php

declare(strict_types=1);

namespace Drupal\Tests\keybolts_api\Kernel;

use Drupal\Core\File\FileSystemInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\KernelTests\KernelTestBase;
use Drupal\keybolts_api\Controller\HomepageController;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Symfony\Component\Yaml\Yaml;

/**
 * The "Khám phá sản phẩm" block on the homepage.
 *
 * Its heading used to be hardcoded in CategoryGrid.vue and its tile photo was
 * always borrowed from a product, so the two things an editor most wanted to
 * change — the wording and the picture — were the two they could not.
 */
class HomepageCategoryTest extends KernelTestBase {

  protected static $modules = [
    'system', 'user', 'field', 'file', 'image', 'text', 'node', 'link',
    'path_alias', 'options', 'taxonomy', 'paragraphs',
    'entity_reference_revisions', 'keybolts_core', 'keybolts_api',
  ];

  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('file');
    $this->installEntitySchema('taxonomy_term');
    $this->installEntitySchema('paragraph');
    $this->installSchema('file', ['file_usage']);
    $this->installSchema('node', ['node_access']);
    $this->installConfig(['system', 'node', 'field']);

    foreach ([
      'kb_card_400_avif', 'kb_card_400_webp',
      'kb_card_800_avif', 'kb_card_800_webp',
      'kb_hero_1200_avif', 'kb_hero_1200_webp',
      'kb_hero_1600_avif', 'kb_hero_1600_webp',
    ] as $name) {
      ImageStyle::create(Yaml::parseFile($this->root . "/../config/sync/image.style.$name.yml"))->save();
    }

    Vocabulary::create(['vid' => 'product_category', 'name' => 'Danh mục'])->save();
    Vocabulary::create(['vid' => 'brand', 'name' => 'Thương hiệu'])->save();
    $this->termField('field_number', 'string');
    $this->termField('field_short_desc', 'string');
    $this->termField('field_image', 'image');

    NodeType::create(['type' => 'home_page', 'name' => 'Trang chủ'])->save();
    $this->nodeField('home_page', 'field_cat_eyebrow', 'string');
    $this->nodeField('home_page', 'field_cat_title', 'string');
    $this->nodeField('home_page', 'field_cat_desc', 'string_long');

    NodeType::create(['type' => 'product', 'name' => 'Sản phẩm'])->save();
    $this->nodeField('product', 'field_images', 'image', 12);
    $this->nodeField('product', 'field_featured_group', 'string');
    FieldStorageConfig::create([
      'field_name' => 'field_category',
      'entity_type' => 'node',
      'type' => 'entity_reference',
      'settings' => ['target_type' => 'taxonomy_term'],
    ])->save();
    FieldConfig::create([
      'field_name' => 'field_category',
      'entity_type' => 'node',
      'bundle' => 'product',
      'label' => 'Danh mục',
    ])->save();
  }

  private function termField(string $name, string $type): void {
    FieldStorageConfig::create([
      'field_name' => $name, 'entity_type' => 'taxonomy_term', 'type' => $type,
    ])->save();
    FieldConfig::create([
      'field_name' => $name, 'entity_type' => 'taxonomy_term',
      'bundle' => 'product_category', 'label' => $name,
    ])->save();
  }

  private function nodeField(string $bundle, string $name, string $type, int $cardinality = 1): void {
    if (!FieldStorageConfig::loadByName('node', $name)) {
      FieldStorageConfig::create([
        'field_name' => $name, 'entity_type' => 'node', 'type' => $type,
        'cardinality' => $cardinality,
      ])->save();
    }
    FieldConfig::create([
      'field_name' => $name, 'entity_type' => 'node',
      'bundle' => $bundle, 'label' => $name,
    ])->save();
  }

  /** A real file on disk, because buildUrl() needs a URI it can hash. */
  private function file(string $name): File {
    $dir = 'public://test';
    \Drupal::service('file_system')->prepareDirectory($dir, FileSystemInterface::CREATE_DIRECTORY);
    $image = imagecreatetruecolor(1200, 900);
    imagepng($image, \Drupal::service('file_system')->realpath($dir) . "/$name.png");
    imagedestroy($image);
    $file = File::create(['uri' => "$dir/$name.png", 'status' => 1]);
    $file->save();
    return $file;
  }

  /** One root category with a product filed under it. */
  private function category(array $values = []): Term {
    $term = Term::create([
      'vid' => 'product_category',
      'name' => 'Khóa thông minh',
      'field_number' => '01',
      'field_short_desc' => 'Vân tay, thẻ từ, mã số',
    ] + $values);
    $term->save();
    $node = Node::create([
      'type' => 'product',
      'title' => 'Khóa KB-9008',
      'status' => 1,
      'field_category' => ['target_id' => $term->id()],
      'field_images' => [[
        'target_id' => $this->file('product-shot')->id(),
        'alt' => 'Khóa', 'width' => 1200, 'height' => 900,
      ]],
    ]);
    $node->save();
    return $term;
  }

  private function payload(): array {
    $response = HomepageController::create($this->container)->index();
    return json_decode($response->getContent(), TRUE)['data'];
  }

  /**
   * The blurb under "Khám phá sản phẩm" had no field at all, so it could only
   * be changed by editing a .vue file.
   */
  public function testCategorySectionCarriesTheEditableDescription(): void {
    Node::create([
      'type' => 'home_page',
      'title' => 'Trang chủ',
      'field_cat_eyebrow' => 'Danh mục',
      'field_cat_title' => 'Khám phá sản phẩm',
      'field_cat_desc' => 'Chọn theo loại cửa và nhu cầu sử dụng.',
    ])->save();

    $section = $this->payload()['categorySection'];

    $this->assertSame('Danh mục', $section['eyebrow']);
    $this->assertSame('Khám phá sản phẩm', $section['title']);
    $this->assertSame('Chọn theo loại cửa và nhu cầu sử dụng.', $section['desc']);
  }

  /**
   * The point of the whole change: an editor picks the tile photo. The term
   * already had field_image; the controller simply never read it.
   */
  public function testTileImageComesFromTheCategoryTermWhenAnEditorSetsOne(): void {
    $this->category([
      'field_image' => [[
        'target_id' => $this->file('category-cover')->id(),
        'alt' => 'Khóa thông minh', 'width' => 1200, 'height' => 900,
      ]],
    ]);

    $tile = $this->payload()['categories'][0];

    $this->assertStringContainsString('category-cover', $tile['image']['url']);
    $this->assertSame('Khóa thông minh', $tile['image']['alt']);
    // Still a proper responsive image, not a bare file url.
    $this->assertStringContainsString('styles/kb_card_800_webp', $tile['image']['url']);
    $this->assertStringContainsString('400w', $tile['image']['srcset']);
  }

  /**
   * Existing behaviour, pinned: nobody has set a category photo yet, and the
   * homepage must not go blank the moment this field exists.
   */
  public function testTileImageStillFallsBackToAProductPhoto(): void {
    $this->category();

    $tile = $this->payload()['categories'][0];

    $this->assertStringContainsString('product-shot', $tile['image']['url']);
  }

}
