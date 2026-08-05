<?php

declare(strict_types=1);

namespace Drupal\Tests\keybolts_api\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Symfony\Component\Yaml\Yaml;
use Drupal\image\Entity\ImageStyle;

/**
 * The whole point of the image work is that a card never downloads a hero-sized
 * file. That promise lives in the srcset this class builds, so it gets pinned.
 */
class ImageSerializerTest extends KernelTestBase {

  protected static $modules = [
    'system', 'user', 'field', 'file', 'image', 'text', 'node',
    'path_alias', 'options', 'taxonomy', 'keybolts_core', 'keybolts_api',
  ];

  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('file');
    $this->installSchema('file', ['file_usage']);
    $this->installConfig(['system', 'node', 'field']);

    foreach (['kb_card_400', 'kb_card_800', 'kb_hero_1200', 'kb_hero_1600'] as $name) {
      ImageStyle::create(Yaml::parseFile($this->root . "/../config/sync/image.style.$name.yml"))->save();
    }

    NodeType::create(['type' => 'article', 'name' => 'Article'])->save();
    FieldStorageConfig::create([
      'field_name' => 'field_article_image',
      'entity_type' => 'node',
      'type' => 'image',
    ])->save();
    FieldConfig::create([
      'field_name' => 'field_article_image',
      'entity_type' => 'node',
      'bundle' => 'article',
      'label' => 'Ảnh',
    ])->save();
  }

  /** A real file on disk, because buildUrl() needs a URI it can hash. */
  private function file(int $width, int $height): File {
    $dir = 'public://test';
    \Drupal::service('file_system')->prepareDirectory($dir, \Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY);
    $uri = "$dir/cover-$width.png";
    $image = imagecreatetruecolor($width, $height);
    imagepng($image, \Drupal::service('file_system')->realpath($dir) . "/cover-$width.png");
    imagedestroy($image);
    $file = File::create(['uri' => $uri, 'status' => 1]);
    $file->save();
    return $file;
  }

  private function serialize(int $width, int $height, string $alt = 'Ảnh bìa'): ?array {
    $file = $this->file($width, $height);
    $node = Node::create([
      'type' => 'article',
      'title' => 'Bài',
      'field_article_image' => ['target_id' => $file->id(), 'alt' => $alt, 'width' => $width, 'height' => $height],
    ]);
    $node->save();
    return $this->container->get('keybolts_api.image_serializer')
      ->fromField($node->get('field_article_image'));
  }

  public function testSrcsetCarriesEveryStyleThatFitsInsideTheOriginal(): void {
    $out = $this->serialize(2000, 1200);

    $this->assertStringContainsString('400w', $out['srcset']);
    $this->assertStringContainsString('800w', $out['srcset']);
    $this->assertStringContainsString('1200w', $out['srcset']);
    $this->assertStringContainsString('1600w', $out['srcset']);
  }

  /**
   * `upscale: false` means a 900px original cannot actually produce a 1600px
   * derivative. Advertising one would make the browser pick a file that is
   * smaller than its own descriptor claims — the exact bug this avoids.
   */
  public function testStylesWiderThanTheOriginalAreNotAdvertised(): void {
    $out = $this->serialize(900, 600);

    $this->assertStringContainsString('400w', $out['srcset']);
    $this->assertStringContainsString('800w', $out['srcset']);
    $this->assertStringNotContainsString('1200w', $out['srcset']);
    $this->assertStringNotContainsString('1600w', $out['srcset']);
  }

  /** Even a tiny original must offer something, or <img> has no src at all. */
  public function testATinyOriginalStillGetsTheSmallestStyle(): void {
    $out = $this->serialize(120, 90);

    $this->assertStringContainsString('400w', $out['srcset']);
  }

  public function testDimensionsAndAltComeStraightFromTheField(): void {
    $out = $this->serialize(2000, 1200, 'Khóa vân tay KB-9008');

    $this->assertSame(2000, $out['width']);
    $this->assertSame(1200, $out['height']);
    $this->assertSame('Khóa vân tay KB-9008', $out['alt']);
  }

  public function testUrlIsAbsoluteAndPointsAtADerivativeNotTheOriginal(): void {
    $out = $this->serialize(2000, 1200);

    $this->assertStringStartsWith('http', $out['url']);
    $this->assertStringContainsString('styles/kb_card_800', $out['url']);
  }

  /**
   * An empty field must collapse to NULL, not to an object with an empty url —
   * the frontend renders `v-if="image"` and a truthy husk would print a broken
   * image icon on every card with no picture.
   */
  public function testAnEmptyFieldSerializesToNull(): void {
    $node = Node::create(['type' => 'article', 'title' => 'Không ảnh']);
    $node->save();

    $this->assertNull(
      $this->container->get('keybolts_api.image_serializer')
        ->fromField($node->get('field_article_image')),
    );
  }

}
