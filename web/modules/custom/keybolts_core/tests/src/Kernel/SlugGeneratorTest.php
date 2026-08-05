<?php

declare(strict_types=1);

namespace Drupal\Tests\keybolts_core\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * The slug is what the API looks a node up by, so getting it wrong does not
 * make a page ugly — it makes the page unreachable. These pin the rules that
 * keep an existing URL from moving under a live article.
 */
#[RunTestsInSeparateProcesses]
class SlugGeneratorTest extends KernelTestBase {

  protected static $modules = [
    'system', 'user', 'field', 'text', 'filter', 'node',
    'path_alias', 'options', 'taxonomy', 'keybolts_core',
  ];

  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    // Re-saving a node clears its grants, which needs the table to exist.
    $this->installSchema('node', ['node_access']);
    $this->installConfig(['system', 'node', 'field', 'filter']);

    foreach (['article', 'project'] as $bundle) {
      NodeType::create(['type' => $bundle, 'name' => ucfirst($bundle)])->save();
      foreach ([
        "field_{$bundle}_slug" => 'string',
        "field_{$bundle}_slug_auto" => 'boolean',
      ] as $name => $type) {
        FieldStorageConfig::create([
          'field_name' => $name, 'entity_type' => 'node', 'type' => $type,
        ])->save();
        FieldConfig::create([
          'field_name' => $name, 'entity_type' => 'node',
          'bundle' => $bundle, 'label' => $name,
        ])->save();
      }
    }
  }

  private function make(string $title, ?bool $auto = TRUE, ?string $slug = NULL, string $bundle = 'article'): Node {
    $values = ['type' => $bundle, 'title' => $title];
    if ($auto !== NULL) {
      $values["field_{$bundle}_slug_auto"] = $auto;
    }
    if ($slug !== NULL) {
      $values["field_{$bundle}_slug"] = $slug;
    }
    $node = Node::create($values);
    $node->save();
    return $node;
  }

  private function slug(Node $node, string $bundle = 'article'): string {
    return (string) $node->get("field_{$bundle}_slug")->value;
  }

  /**
   * The exact string this produces is not cosmetic: it has to match what the
   * eight live articles already store, or turning the feature on moves every
   * published URL at once.
   */
  public function testVietnameseTitleBecomesTheSlugAlreadyInUse(): void {
    $this->assertSame(
      'cach-chon-khoa-theo-do-day-cua',
      $this->slug($this->make('Cách chọn khóa theo độ dày cửa')),
    );
    $this->assertSame(
      'khoa-the-tu-va-khoa-van-tay-khac-nhau-the-nao',
      $this->slug($this->make('Khóa thẻ từ và khóa vân tay khác nhau thế nào?')),
    );
    $this->assertSame(
      'khoa-dong-va-khoa-inox-chon-loai-nao',
      $this->slug($this->make('Khóa đồng và khóa inox — chọn loại nào?')),
    );
  }

  public function testAHandTypedSlugSurvivesWhenTheBoxIsUnticked(): void {
    $node = $this->make('Tiêu đề hoàn toàn khác', auto: FALSE, slug: 'slug-toi-tu-chon');

    $this->assertSame('slug-toi-tu-chon', $this->slug($node));
  }

  /**
   * Two articles can legitimately share a title. Whichever saves second must
   * not silently steal the first one's URL.
   */
  public function testASecondNodeWithTheSameTitleGetsASuffix(): void {
    $this->make('Khóa vân tay cho căn hộ');
    $second = $this->make('Khóa vân tay cho căn hộ');

    $this->assertSame('khoa-van-tay-cho-can-ho-2', $this->slug($second));
  }

  /**
   * The uniqueness check must exclude the node doing the saving. Otherwise
   * every re-save of an unchanged article walks its own slug one step further
   * and breaks the link that was working a moment ago.
   */
  public function testResavingANodeKeepsItsOwnSlug(): void {
    $node = $this->make('Bảo dưỡng khóa đồng mạ PVD');
    $this->assertSame('bao-duong-khoa-dong-ma-pvd', $this->slug($node));

    $node->setTitle('Bảo dưỡng khóa đồng mạ PVD');
    $node->save();

    $this->assertSame('bao-duong-khoa-dong-ma-pvd', $this->slug($node));
  }

  /**
   * An empty slug makes the API 404 forever, so a title made only of
   * punctuation must still come out with something addressable.
   */
  public function testATitleWithNothingTransliterableStillGetsASlug(): void {
    $slug = $this->slug($this->make('!!! ??? ---'));

    $this->assertNotSame('', $slug);
    $this->assertMatchesRegularExpression('/^[a-z0-9-]+$/', $slug);
  }

  public function testProjectsFollowTheSameRules(): void {
    $node = $this->make('Biệt thự đơn lập — bộ khóa đồng đồng bộ', bundle: 'project');

    $this->assertSame('biet-thu-don-lap-bo-khoa-dong-dong-bo', $this->slug($node, 'project'));
  }

  /**
   * Renaming with the box ticked is meant to move the slug — that is the
   * whole point of the box — so pin it rather than leaving it to chance.
   */
  public function testRenamingWithTheBoxTickedMovesTheSlug(): void {
    $node = $this->make('Tiêu đề ban đầu');
    $node->setTitle('Tiêu đề đã sửa');
    $node->save();

    $this->assertSame('tieu-de-da-sua', $this->slug($node));
  }

}
