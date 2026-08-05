# Image Payload Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Cắt dung lượng ảnh trang chủ từ 33,8 MB xuống dưới 1 MB bằng cách đưa ảnh đại diện vào image field, để Drupal image style sinh các cỡ, và cho frontend khai `srcset`/`sizes`.

**Architecture:** Ảnh đại diện của bài viết và dự án đổi từ trường text-URL sang image field trỏ vào managed file đã có sẵn trong Drupal. Một class `ImageSerializer` duy nhất dựng object `{url, srcset, width, height, alt}` từ một `ImageItem`, dùng chung cho cả ba serializer. Frontend có một component `UiResponsiveImage` duy nhất nhận object đó, nên logic `srcset` không bị lặp ở 24 chỗ.

**Tech Stack:** Drupal 11.4 (image, image_style), PHP 8.4, Nuxt 4 SSR, Vue 3, Tailwind 4. Không thêm dependency nào ở cả hai phía.

## Global Constraints

- Spec nguồn: `docs/superpowers/specs/2026-08-05-images-content-slug-design.md`. Phần A của spec. Phần B và C có kế hoạch riêng.
- **Không cài `@nuxt/image`.** Drupal cắt cỡ; frontend chỉ dùng `<img>` thuần.
- Image style xuất `webp`, chất lượng 82, `image_scale` theo chiều rộng, `upscale: false`, không crop.
- Trường cũ `field_article_image_url` / `field_project_image_url` **giữ nguyên**, không xóa trong kế hoạch này.
- Mọi script trong `scripts/setup/` phải chạy lại được nhiều lần cho cùng kết quả.
- Kernel test chạy bằng: `SIMPLETEST_DB="sqlite://localhost/sites/default/files/test.sqlite" vendor/bin/phpunit -c web/core/phpunit.xml.dist <path> --no-coverage`
- `LeadSubmissionTest::testGoodRecaptchaScoreIsStoredAlongsideTheLead` fail sẵn trên SQLite (`'0.90'` vs `'0.9'`), không liên quan, đừng sửa.
- Sau mọi thay đổi config: `ddev drush cex -y && git add config/sync`.
- Commit message tiếng Anh, theo conventional commits như lịch sử nhánh.

## File Structure

| File | Trách nhiệm |
|---|---|
| `config/sync/image.style.kb_{card_400,card_800,hero_1200,hero_1600}.yml` | Bốn cỡ webp |
| `web/modules/custom/keybolts_api/src/Serializer/ImageSerializer.php` | Đổi một `ImageItem` thành object responsive. Không biết gì về loại nội dung. |
| `web/modules/custom/keybolts_api/keybolts_api.services.yml` | Đăng ký service mới, tiêm vào ba serializer |
| `config/sync/field.storage.node.field_{article,project}_image.yml` | Storage image field |
| `config/sync/field.field.node.{article,project}.field_*_image.yml` | Instance |
| `scripts/setup/install_cover_image_fields.php` | Cài field + image style lên site đang chạy |
| `scripts/setup/migrate_image_urls_to_fields.php` | Gán fid từ URL cũ sang field mới |
| `frontend/app/components/ui/ResponsiveImage.vue` | Component `<img>` duy nhất biết `srcset`/`sizes`/`loading` |
| `frontend/app/types/page.ts` | `ResponsiveImage` interface, đổi `image: string` → `image: ResponsiveImage \| null` |

---

### Task 1: Image style và ImageSerializer

**Files:**
- Create: `config/sync/image.style.kb_card_400.yml`, `kb_card_800.yml`, `kb_hero_1200.yml`, `kb_hero_1600.yml`
- Create: `web/modules/custom/keybolts_api/src/Serializer/ImageSerializer.php`
- Modify: `web/modules/custom/keybolts_api/keybolts_api.services.yml`
- Test: `web/modules/custom/keybolts_api/tests/src/Kernel/ImageSerializerTest.php`

**Interfaces:**
- Produces: hai phương thức, cùng trả `['url'=>string,'srcset'=>string,'width'=>int,'height'=>int,'alt'=>string]` hoặc `NULL`:
  - `ImageSerializer::fromItem(?ImageItem $item): ?array` — dùng khi lặp qua trường nhiều ảnh.
  - `ImageSerializer::fromField(FieldItemListInterface $list): ?array` — lấy ảnh đầu tiên, chỉ gọi `fromItem()`.
  Task 3 gọi cả hai.
- Service id: `keybolts_api.image_serializer`.

- [ ] **Step 1: Viết test thất bại**

`web/modules/custom/keybolts_api/tests/src/Kernel/ImageSerializerTest.php`:

```php
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
```

- [ ] **Step 2: Chạy để xác nhận fail**

```bash
SIMPLETEST_DB="sqlite://localhost/sites/default/files/test.sqlite" \
  vendor/bin/phpunit -c web/core/phpunit.xml.dist \
  web/modules/custom/keybolts_api/tests/src/Kernel/ImageSerializerTest.php --no-coverage
```

Kỳ vọng: fail vì `image.style.kb_card_400.yml` không tồn tại.

- [ ] **Step 3: Tạo bốn image style**

`config/sync/image.style.kb_card_400.yml` — ba file còn lại giống hệt, chỉ đổi `name`, `label`, `width` và **cả hai uuid** (mỗi effect cần uuid riêng, trùng uuid giữa các style sẽ làm config import báo lỗi; sinh bằng `php -r 'printf("%s\n", \Drupal\Component\Uuid\Php::class);'` hoặc `uuidgen`):

```yaml
uuid: 8f2c1d40-0a3e-4b71-9c52-1e6b7a90d301
langcode: vi
status: true
dependencies: {  }
name: kb_card_400
label: 'Keybolts card 400'
effects:
  1c7b4e20-6d18-4a93-8f05-2b3c9d7e4a11:
    uuid: 1c7b4e20-6d18-4a93-8f05-2b3c9d7e4a11
    id: image_scale
    weight: 1
    data:
      width: 400
      height: null
      upscale: false
  2d8c5f31-7e29-4ba4-9016-3c4dae8f5b22:
    uuid: 2d8c5f31-7e29-4ba4-9016-3c4dae8f5b22
    id: image_convert_avif
    weight: 2
    data:
      extension: webp
```

Bảng giá trị cho bốn file:

| File | `name` | `label` | `width` |
|---|---|---|---|
| `image.style.kb_card_400.yml` | `kb_card_400` | `Keybolts card 400` | 400 |
| `image.style.kb_card_800.yml` | `kb_card_800` | `Keybolts card 800` | 800 |
| `image.style.kb_hero_1200.yml` | `kb_hero_1200` | `Keybolts hero 1200` | 1200 |
| `image.style.kb_hero_1600.yml` | `kb_hero_1600` | `Keybolts hero 1600` | 1600 |

`image_convert_avif` là plugin id đúng trong core này dù tên nghe lạ — đối chiếu `config/sync/image.style.wide.yml` đang chạy, nó dùng chính id đó với `extension: webp`. Đừng đổi thành `image_convert`.

Chất lượng webp lấy từ `system.image.gd` (`jpeg_quality`) chứ không đặt trên từng style; kiểm tra bằng `ddev drush cget system.image.gd` và để nguyên nếu đã là 82.

- [ ] **Step 4: Viết ImageSerializer**

`web/modules/custom/keybolts_api/src/Serializer/ImageSerializer.php`:

```php
<?php

declare(strict_types=1);

namespace Drupal\keybolts_api\Serializer;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\image\Plugin\Field\FieldType\ImageItem;

/**
 * Turns one image field into the shape an <img srcset> needs.
 *
 * Knows nothing about articles, projects or products — it takes a field and
 * gives back widths, which is why all three serializers can share it.
 */
final class ImageSerializer {

  /** Style machine name => the width it scales to. Ordered smallest first. */
  private const STYLES = [
    'kb_card_400' => 400,
    'kb_card_800' => 800,
    'kb_hero_1200' => 1200,
    'kb_hero_1600' => 1600,
  ];

  /** What a bare `src` falls back to for browsers that ignore srcset. */
  private const DEFAULT_STYLE = 'kb_card_800';

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly FileUrlGeneratorInterface $fileUrlGenerator,
  ) {}

  /** Ảnh đầu tiên của một trường. Trường nhiều ảnh thì lặp và gọi fromItem(). */
  public function fromField(FieldItemListInterface $list): ?array {
    $item = $list->first();
    return $item instanceof ImageItem ? $this->fromItem($item) : NULL;
  }

  public function fromItem(?ImageItem $item): ?array {
    if (!$item || !$item->entity) {
      return NULL;
    }
    $uri = $item->entity->getFileUri();
    $width = (int) $item->width;
    $storage = $this->entityTypeManager->getStorage('image_style');

    // `upscale: false` means a style wider than the original silently returns
    // the original's size. Advertising `1600w` for an 800px file would make the
    // browser download it believing it is bigger than it is, and then not
    // download the one it actually needed. So only offer styles that fit —
    // keeping the smallest regardless, or a tiny original would have no src.
    $usable = array_filter(
      self::STYLES,
      static fn (int $styleWidth): bool => $width === 0 || $styleWidth <= $width,
    );
    if (!$usable) {
      $usable = array_slice(self::STYLES, 0, 1, TRUE);
    }

    $srcset = [];
    foreach ($usable as $name => $styleWidth) {
      $style = $storage->load($name);
      if ($style) {
        $srcset[] = $style->buildUrl($uri) . ' ' . $styleWidth . 'w';
      }
    }

    $default = $storage->load(self::DEFAULT_STYLE) ?? $storage->load(array_key_first($usable));

    return [
      'url' => $default
        ? $default->buildUrl($uri)
        : $this->fileUrlGenerator->generateAbsoluteString($uri),
      'srcset' => implode(', ', $srcset),
      'width' => $width,
      'height' => (int) $item->height,
      'alt' => (string) $item->alt,
    ];
  }

}
```

- [ ] **Step 5: Đăng ký service**

Thêm vào `web/modules/custom/keybolts_api/keybolts_api.services.yml`:

```yaml
  keybolts_api.image_serializer:
    class: Drupal\keybolts_api\Serializer\ImageSerializer
    arguments:
      - '@entity_type.manager'
      - '@file_url_generator'
```

- [ ] **Step 6: Chạy test, kỳ vọng pass**

```bash
SIMPLETEST_DB="sqlite://localhost/sites/default/files/test.sqlite" \
  vendor/bin/phpunit -c web/core/phpunit.xml.dist \
  web/modules/custom/keybolts_api/tests/src/Kernel/ImageSerializerTest.php --no-coverage
```

Kỳ vọng: 6 test pass.

- [ ] **Step 7: Commit**

```bash
git add config/sync/image.style.kb_*.yml \
        web/modules/custom/keybolts_api/src/Serializer/ImageSerializer.php \
        web/modules/custom/keybolts_api/keybolts_api.services.yml \
        web/modules/custom/keybolts_api/tests/src/Kernel/ImageSerializerTest.php
git commit -m "feat(api): build responsive srcsets from image fields"
```

---

### Task 2: Image field cho ảnh đại diện, và migrate dữ liệu

**Files:**
- Create: `config/sync/field.storage.node.field_article_image.yml`, `field.storage.node.field_project_image.yml`
- Create: `config/sync/field.field.node.article.field_article_image.yml`, `field.field.node.project.field_project_image.yml`
- Create: `scripts/setup/install_cover_image_fields.php`
- Create: `scripts/setup/migrate_image_urls_to_fields.php`
- Modify: `config/sync/core.entity_form_display.node.article.default.yml`, `...node.project.default.yml`

**Interfaces:**
- Consumes: image style từ Task 1 (script cài luôn cả style, để một lệnh dựng được site sạch).
- Produces: trường `field_article_image` và `field_project_image` có dữ liệu. Task 3 đọc chúng.

- [ ] **Step 1: Viết config storage**

`config/sync/field.storage.node.field_article_image.yml` (bản project giống hệt, đổi `field_name`, `id` và `uuid`):

```yaml
uuid: 4a1e9c72-58b3-4d06-a1f4-7c2e35b8d910
langcode: vi
status: true
dependencies:
  module:
    - file
    - image
    - node
id: node.field_article_image
field_name: field_article_image
entity_type: node
type: image
settings:
  target_type: file
  display_field: false
  display_default: true
  uri_scheme: public
  default_image:
    uuid: null
    alt: ''
    title: ''
    width: null
    height: null
module: image
locked: false
cardinality: 1
translatable: true
indexes: {  }
persist_with_no_fields: false
custom_storage: false
```

- [ ] **Step 2: Viết config instance**

`config/sync/field.field.node.article.field_article_image.yml` (bản project đổi `bundle`, `id`, `field_name`, `uuid`, `label` thành `Ảnh dự án`):

```yaml
uuid: 6b3f0d84-9a15-4e27-b038-5d1c47f9ea23
langcode: vi
status: true
dependencies:
  config:
    - field.storage.node.field_article_image
    - node.type.article
  module:
    - image
id: node.article.field_article_image
field_name: field_article_image
entity_type: node
bundle: article
label: 'Ảnh bài viết'
description: 'Ảnh hiển thị trên thẻ tin tức và đầu trang chi tiết.'
required: false
translatable: true
default_value: {  }
default_value_callback: ''
settings:
  handler: 'default:file'
  handler_settings: {  }
  file_directory: '[date:custom:Y]-[date:custom:m]'
  file_extensions: 'png gif jpg jpeg webp'
  max_filesize: ''
  max_resolution: ''
  min_resolution: ''
  alt_field: true
  alt_field_required: true
  title_field: false
  title_field_required: false
  default_image:
    uuid: null
    alt: ''
    title: ''
    width: null
    height: null
field_type: image
```

- [ ] **Step 3: Viết script cài field và style**

`scripts/setup/install_cover_image_fields.php`:

```php
<?php

/**
 * @file
 * Ảnh đại diện của bài viết và dự án, dưới dạng image field.
 *
 * Trước đây hai loại này giữ ảnh trong một trường text chứa URL, và các URL đó
 * trỏ về site cũ keybolts.com.vn — ảnh gốc máy ảnh, tấm nặng nhất 12,4 MB.
 * Drupal không xử lý được ảnh nằm sau một URL ngoài, nên image style chưa từng
 * chạm được vào chúng. Đưa ảnh vào image field là điều kiện để cắt cỡ.
 *
 * Trường URL cũ được giữ nguyên, xóa ở một bước riêng sau khi đã verify.
 *
 * Safe to run repeatedly.
 *
 * Run: ddev drush php:script scripts/setup/install_cover_image_fields.php
 */

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\image\Entity\ImageStyle;
use Symfony\Component\Yaml\Yaml;

$sync = dirname(__DIR__, 2) . '/config/sync';

/** Tạo hoặc cập nhật một config entity từ file YAML, giữ nguyên uuid đang có. */
$apply = function (string $entityTypeId, string $file) use ($sync): void {
  $data = Yaml::parseFile("$sync/$file");
  $storage = \Drupal::entityTypeManager()->getStorage($entityTypeId);
  $existing = $storage->load($data['id'] ?? $data['name']);
  if ($existing === NULL) {
    $storage->create($data)->save();
    echo "Created $file\n";
    return;
  }
  foreach ($data as $key => $value) {
    if ($key !== 'uuid') {
      $existing->set($key, $value);
    }
  }
  $existing->save();
  echo "Updated $file\n";
};

foreach (['kb_card_400', 'kb_card_800', 'kb_hero_1200', 'kb_hero_1600'] as $style) {
  $apply('image_style', "image.style.$style.yml");
}

foreach (['article', 'project'] as $bundle) {
  $apply('field_storage_config', "field.storage.node.field_{$bundle}_image.yml");
  $apply('field_config', "field.field.node.$bundle.field_{$bundle}_image.yml");
}

echo "Xong. Chạy tiếp migrate_image_urls_to_fields.php.\n";
```

- [ ] **Step 4: Viết script migrate**

`scripts/setup/migrate_image_urls_to_fields.php`:

```php
<?php

/**
 * @file
 * Chuyển ảnh đại diện từ URL trỏ về site cũ sang image field.
 *
 * Cả chín ảnh đang hotlink đều đã có bản .webp là managed file trong Drupal này
 * (chúng được dùng cho ảnh sản phẩm), nên đây là một phép tra fid chứ không
 * phải tải file. Ánh xạ theo tên file: dấu gạch dưới thành gạch ngang, bỏ đuôi.
 *
 * Không đoán: URL nào không khớp một managed file thì script DỪNG và báo tên,
 * vì bỏ qua im lặng sẽ để lại một node không ảnh mà không ai biết.
 *
 * Safe to run repeatedly — node nào đã có ảnh thì bỏ qua.
 *
 * Run: ddev drush php:script scripts/setup/migrate_image_urls_to_fields.php
 */

$fileStorage = \Drupal::entityTypeManager()->getStorage('file');
$nodeStorage = \Drupal::entityTypeManager()->getStorage('node');

/** Tên file cũ (không đuôi, gạch dưới) => managed file tương ứng. */
$resolve = function (string $url) use ($fileStorage): ?int {
  $base = pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_FILENAME);
  // `_r3_0152_copy_0` => `-r3-0152-copy-0`, khớp cách Drupal đặt tên khi import.
  $candidate = str_replace('_', '-', $base) . '.webp';
  $ids = $fileStorage->getQuery()->accessCheck(FALSE)
    ->condition('uri', '%/' . $candidate, 'LIKE')->range(0, 1)->execute();
  return $ids ? (int) reset($ids) : NULL;
};

$moved = 0;
$skipped = 0;
foreach ([['article', 'field_article_image_url', 'field_article_image'],
          ['project', 'field_project_image_url', 'field_project_image']] as [$bundle, $old, $new]) {
  $ids = $nodeStorage->getQuery()->accessCheck(FALSE)->condition('type', $bundle)->execute();
  foreach ($nodeStorage->loadMultiple($ids) as $node) {
    if (!$node->get($new)->isEmpty()) {
      $skipped++;
      continue;
    }
    $url = trim((string) $node->get($old)->value);
    if ($url === '') {
      continue;
    }
    $fid = $resolve($url);
    if ($fid === NULL) {
      throw new \RuntimeException(
        "Không tìm được managed file cho $url (node {$node->id()}). " .
        'Import ảnh này vào Drupal trước rồi chạy lại.'
      );
    }
    $node->set($new, ['target_id' => $fid, 'alt' => $node->label()]);
    $node->save();
    $moved++;
  }
}

echo "Đã gán ảnh cho $moved node, bỏ qua $skipped node đã có ảnh.\n";
```

- [ ] **Step 5: Chạy cả hai script trên local**

```bash
ddev drush php:script scripts/setup/install_cover_image_fields.php
ddev drush php:script scripts/setup/migrate_image_urls_to_fields.php
ddev drush cr
```

Kỳ vọng: không ném exception, và báo `Đã gán ảnh cho 25 node`. Con số này đã đếm trên dữ liệu thật: 13/13 bài viết và 12/12 dự án đều có URL ảnh. Ra ít hơn 25 nghĩa là có node bị bỏ sót — dừng lại điều tra, đừng đi tiếp.

- [ ] **Step 6: Xác minh dữ liệu**

```bash
ddev drush php:eval '
foreach ([["article","field_article_image"],["project","field_project_image"]] as [$b,$f]) {
  $s = \Drupal::entityTypeManager()->getStorage("node");
  $ids = $s->getQuery()->accessCheck(FALSE)->condition("type",$b)->execute();
  $with = 0;
  foreach ($s->loadMultiple($ids) as $n) { if (!$n->get($f)->isEmpty()) $with++; }
  echo "$b: $with/" . count($ids) . " node có ảnh" . PHP_EOL;
}'
```

Kỳ vọng: mọi node có URL cũ đều đã có ảnh. Nếu lệch, dừng và điều tra chứ không đi tiếp.

- [ ] **Step 7: Chạy lại migrate lần hai để chứng minh idempotent**

```bash
ddev drush php:script scripts/setup/migrate_image_urls_to_fields.php
```

Kỳ vọng: `Đã gán ảnh cho 0 node, bỏ qua N node đã có ảnh.`

- [ ] **Step 8: Đưa trường mới vào form display**

Mở `/admin/structure/types/manage/article/form-display` và `/admin/structure/types/manage/project/form-display`, kéo trường ảnh mới vào tab "Nội dung chi tiết", đặt trường URL cũ xuống vùng Disabled. Rồi export:

```bash
ddev drush cex -y
```

- [ ] **Step 9: Commit**

```bash
git add config/sync scripts/setup/install_cover_image_fields.php \
        scripts/setup/migrate_image_urls_to_fields.php
git commit -m "feat(content): move article and project covers into image fields"
```

---

### Task 3: Ba serializer trả object ảnh

**Files:**
- Modify: `web/modules/custom/keybolts_api/src/Serializer/ArticleSerializer.php:90`
- Modify: `web/modules/custom/keybolts_api/src/Serializer/ProjectSerializer.php:44`
- Modify: `web/modules/custom/keybolts_api/src/Serializer/ProductSerializer.php:145-165`
- Modify: `web/modules/custom/keybolts_api/keybolts_api.services.yml`
- Test: `web/modules/custom/keybolts_api/tests/src/Kernel/ImageSerializerTest.php` (thêm)

**Interfaces:**
- Consumes: `keybolts_api.image_serializer` từ Task 1; trường ảnh từ Task 2.
- Produces: khóa `image` trong payload API đổi từ `string` sang object/`null`. Task 4 đọc shape này.

- [ ] **Step 1: Thêm test cho ProductSerializer**

Thêm vào `ImageSerializerTest.php`:

```php
  /**
   * Ảnh sản phẩm trước nay trả URL file gốc, không qua image style. Đây là
   * nguồn 2 MB còn lại trên trang chủ sau khi đã gỡ ảnh hotlink.
   */
  public function testProductImagesGoThroughImageStylesToo(): void {
    $file = $this->file(2000, 1200);
    NodeType::create(['type' => 'product', 'name' => 'Product'])->save();
    FieldStorageConfig::create([
      'field_name' => 'field_images', 'entity_type' => 'node', 'type' => 'image', 'cardinality' => 12,
    ])->save();
    FieldConfig::create([
      'field_name' => 'field_images', 'entity_type' => 'node', 'bundle' => 'product', 'label' => 'Ảnh',
    ])->save();
    $node = Node::create([
      'type' => 'product',
      'title' => 'Khóa KB-9008',
      'field_images' => [['target_id' => $file->id(), 'alt' => 'Khóa', 'width' => 2000, 'height' => 1200]],
    ]);
    $node->save();

    $card = $this->container->get('keybolts_api.product_serializer')->card($node);

    $this->assertIsArray($card['image']);
    $this->assertStringContainsString('styles/kb_card_800', $card['image']['url']);
    $this->assertStringContainsString('400w', $card['image']['srcset']);
  }
```

Service id của product serializer phải đối chiếu trong `keybolts_api.services.yml` trước khi viết — nếu khác `keybolts_api.product_serializer` thì sửa test theo file thật, đừng đổi service.

- [ ] **Step 2: Chạy, kỳ vọng fail**

```bash
SIMPLETEST_DB="sqlite://localhost/sites/default/files/test.sqlite" \
  vendor/bin/phpunit -c web/core/phpunit.xml.dist \
  web/modules/custom/keybolts_api/tests/src/Kernel/ImageSerializerTest.php --no-coverage
```

Kỳ vọng: `testProductImagesGoThroughImageStylesToo` fail vì `image` vẫn là mảng `{url, alt}` không có `srcset`.

- [ ] **Step 3: Tiêm ImageSerializer vào ba serializer**

Trong `keybolts_api.services.yml`, thêm `'@keybolts_api.image_serializer'` vào `arguments` của cả ba serializer, và thêm tham số constructor tương ứng:

```php
    private readonly ImageSerializer $imageSerializer,
```

- [ ] **Step 4: Đổi ArticleSerializer**

`ArticleSerializer.php`, trong `toArray()` đổi dòng `'image' => (string) $node->get('field_article_image_url')->value,` thành:

```php
      'image' => $this->imageSerializer->fromField($node->get('field_article_image')),
```

- [ ] **Step 5: Đổi ProjectSerializer**

`ProjectSerializer.php`, đổi `'image' => (string) $node->get('field_project_image_url')->value,` thành:

```php
      'image' => $this->imageSerializer->fromField($node->get('field_project_image')),
```

- [ ] **Step 6: Đổi ProductSerializer**

Thay `images()` và `firstImage()`:

```php
  private function firstImage(NodeInterface $node): ?array {
    return $this->images($node)[0] ?? NULL;
  }

  private function images(NodeInterface $node): array {
    if (!$node->hasField('field_images')) {
      return [];
    }
    $out = [];
    foreach ($node->get('field_images') as $item) {
      $one = $this->imageSerializer->fromItem($item);
      if ($one) {
        $out[] = $one;
      }
    }
    return $out;
  }
```

`FileUrlGeneratorInterface` không còn được `ProductSerializer` dùng sau thay đổi này — bỏ khỏi constructor và khỏi `keybolts_api.services.yml`, đừng để tham số chết.

- [ ] **Step 7: Chạy toàn bộ test module**

```bash
SIMPLETEST_DB="sqlite://localhost/sites/default/files/test.sqlite" \
  vendor/bin/phpunit -c web/core/phpunit.xml.dist web/modules/custom --no-coverage
```

Kỳ vọng: tất cả pass trừ `testGoodRecaptchaScoreIsStoredAlongsideTheLead` đã fail sẵn.

- [ ] **Step 8: Kiểm tra API thật**

```bash
ddev drush cr
curl -sk https://vietlong.ddev.site/api/v1/articles | head -c 600
```

Kỳ vọng: `image` là object có `srcset`, không phải chuỗi `https://keybolts.com.vn/...`.

- [ ] **Step 9: Commit**

```bash
git add web/modules/custom/keybolts_api
git commit -m "feat(api): serve every image through image styles"
```

---

### Task 4: Component ảnh dùng chung ở frontend

**Files:**
- Create: `frontend/app/components/ui/ResponsiveImage.vue`
- Modify: `frontend/app/types/page.ts:16,105,132,148`
- Test: `frontend/test/responsive-image.spec.ts`

**Interfaces:**
- Consumes: shape `{url, srcset, width, height, alt}` từ Task 3.
- Produces: `<UiResponsiveImage :image sizes class priority />`. Task 5 thay 21 thẻ `<img>` bằng nó.

- [ ] **Step 1: Thêm kiểu**

Trong `frontend/app/types/page.ts`, thêm trên cùng:

```ts
/** Ảnh đã qua image style của Drupal, kèm mọi cỡ trình duyệt được chọn. */
export interface ResponsiveImage {
  url: string
  srcset: string
  width: number
  height: number
  alt: string
}
```

Rồi đổi bốn khai báo `image: string` (dòng 16 `Segment`, 105 `NewsArticle`, 132 `RelatedItem`, 148 `Project`) thành `image: ResponsiveImage | null`. Với `Segment.image?` giữ dấu `?` và đổi kiểu.

- [ ] **Step 2: Viết test thất bại**

`frontend/test/responsive-image.spec.ts`:

```ts
import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import ResponsiveImage from '../app/components/ui/ResponsiveImage.vue'

const image = {
  url: 'https://x.test/styles/kb_card_800/cover.webp',
  srcset: 'https://x.test/styles/kb_card_400/cover.webp 400w, https://x.test/styles/kb_card_800/cover.webp 800w',
  width: 2000,
  height: 1200,
  alt: 'Khóa vân tay',
}

describe('ResponsiveImage', () => {
  it('phát ra srcset và sizes để trình duyệt tự chọn cỡ', () => {
    const img = mount(ResponsiveImage, { props: { image, sizes: '(min-width: 1024px) 400px, 100vw' } }).get('img')
    expect(img.attributes('srcset')).toBe(image.srcset)
    expect(img.attributes('sizes')).toBe('(min-width: 1024px) 400px, 100vw')
  })

  it('khai width và height thật để trang không nhảy khi ảnh tải xong', () => {
    const img = mount(ResponsiveImage, { props: { image, sizes: '100vw' } }).get('img')
    expect(img.attributes('width')).toBe('2000')
    expect(img.attributes('height')).toBe('1200')
  })

  it('mặc định lazy, nhưng ảnh ưu tiên thì tải ngay', () => {
    const lazy = mount(ResponsiveImage, { props: { image, sizes: '100vw' } }).get('img')
    expect(lazy.attributes('loading')).toBe('lazy')

    const eager = mount(ResponsiveImage, { props: { image, sizes: '100vw', priority: true } }).get('img')
    expect(eager.attributes('loading')).toBe('eager')
    expect(eager.attributes('fetchpriority')).toBe('high')
  })

  it('không render gì khi không có ảnh, thay vì để lại thẻ img hỏng', () => {
    expect(mount(ResponsiveImage, { props: { image: null, sizes: '100vw' } }).find('img').exists()).toBe(false)
  })

  it('alt lấy từ dữ liệu, nhưng prop alt đè được khi ngữ cảnh cần khác', () => {
    const img = mount(ResponsiveImage, { props: { image, sizes: '100vw', alt: 'Ảnh dự án Vinhomes' } }).get('img')
    expect(img.attributes('alt')).toBe('Ảnh dự án Vinhomes')
  })
})
```

- [ ] **Step 3: Chạy, kỳ vọng fail**

```bash
cd frontend && npm test -- responsive-image
```

Kỳ vọng: fail vì file component chưa tồn tại.

- [ ] **Step 4: Viết component**

`frontend/app/components/ui/ResponsiveImage.vue`:

```vue
<script setup lang="ts">
import type { ResponsiveImage } from '~/types/page'

const props = withDefaults(defineProps<{
  image: ResponsiveImage | null | undefined
  /** Bề rộng ô ảnh thật sự chiếm, để trình duyệt chọn đúng file trong srcset. */
  sizes: string
  /** Ảnh trong màn hình đầu: tải ngay thay vì đợi cuộn tới. */
  priority?: boolean
  /** Đè alt khi ngữ cảnh mô tả ảnh chính xác hơn dữ liệu. */
  alt?: string
}>(), { priority: false })

const altText = computed(() => props.alt ?? props.image?.alt ?? '')
</script>

<template>
  <img
    v-if="image"
    :src="image.url"
    :srcset="image.srcset"
    :sizes="sizes"
    :width="image.width"
    :height="image.height"
    :alt="altText"
    :loading="priority ? 'eager' : 'lazy'"
    :fetchpriority="priority ? 'high' : undefined"
    decoding="async"
  >
</template>
```

- [ ] **Step 5: Chạy test, kỳ vọng pass**

```bash
cd frontend && npm test -- responsive-image
```

- [ ] **Step 6: Commit**

```bash
git add frontend/app/components/ui/ResponsiveImage.vue frontend/app/types/page.ts \
        frontend/test/responsive-image.spec.ts
git commit -m "feat(frontend): add one image component that knows about srcset"
```

---

### Task 5: Thay các thẻ img bằng component, gỡ ảnh hardcode

**Files:**
- Modify: 19 file `.vue` liệt kê ở Step 1 (24 thẻ `<img>` nhận dữ liệu)
- Modify: `frontend/app/components/home/TechBlock.vue:10` (ảnh tĩnh, xử lý riêng ở Step 2)
- Create: `frontend/public/images/khoa-thong-minh-t28.webp`

**Interfaces:**
- Consumes: `UiResponsiveImage` từ Task 4.

- [ ] **Step 1: Thay từng thẻ img nhận dữ liệu**

Các file và giá trị `sizes` tương ứng với bề rộng ô thật:

| File | `sizes` | `priority` |
|---|---|---|
| `components/home/Hero.vue` | `100vw` | có |
| `components/home/CategoryGrid.vue` | `(min-width: 1024px) 25vw, 50vw` | không |
| `components/home/FeaturedTabs.vue` | `(min-width: 1024px) 300px, 70vw` | không |
| `components/home/SolutionGrid.vue` | `(min-width: 1024px) 33vw, 100vw` | không |
| `components/home/ContentPanels.vue` (2 thẻ) | `(min-width: 1024px) 25vw, 50vw` | không |
| `components/product/ProductCard.vue` | `(min-width: 1024px) 300px, 50vw` | không |
| `components/product/Gallery.vue` (2 thẻ) | `(min-width: 1024px) 600px, 100vw` | thẻ đầu: có |
| `components/product/ListingHero.vue` (3 thẻ) | `(min-width: 1024px) 33vw, 100vw` | không |
| `components/page/NewsCard.vue` | `(min-width: 1024px) 33vw, 100vw` | không |
| `components/page/ProjectCard.vue` | `(min-width: 1024px) 33vw, 100vw` | không |
| `components/page/FeaturedArticle.vue` | `(min-width: 1024px) 50vw, 100vw` | không |
| `components/page/SegmentGrid.vue` | `(min-width: 1024px) 33vw, 100vw` | không |
| `components/page/StoryBlock.vue` | `(min-width: 1024px) 50vw, 100vw` | không |
| `components/page/Hero.vue` | `100vw` | có |
| `components/page/AboutHero.vue` | `100vw` | có |
| `components/page/ClosingCta.vue` | `100vw` | không |
| `components/article/Related.vue` | `(min-width: 1024px) 25vw, 50vw` | không |
| `pages/tin-tuc/[slug].vue` (2 thẻ) | `(min-width: 1024px) 760px, 100vw` | thẻ đầu: có |
| `pages/du-an/[slug].vue` | `(min-width: 1024px) 760px, 100vw` | có |

Giữ nguyên toàn bộ `class` đang có trên thẻ `<img>` — chuyển thẳng sang component, Vue sẽ truyền xuống thẻ `img` bên trong. Ví dụ `SolutionGrid.vue:31`:

```vue
<UiResponsiveImage
  :image="sol.image"
  sizes="(min-width: 1024px) 33vw, 100vw"
  class="size-full object-cover transition duration-500 group-hover:scale-105"
/>
```

**Không đụng** ba thẻ ảnh tĩnh: `layout/MainBar.vue`, `layout/SiteFooter.vue`, `layout/SearchOverlay.vue` — chúng là logo trong `/public`, không đi qua Drupal.

- [ ] **Step 2: Gỡ ảnh hardcode trong TechBlock**

`TechBlock.vue:10` đang trỏ thẳng `https://keybolts.com.vn/sites/default/files/khoa_thong_minh_t28_0.png` — 769 KB, và là ảnh hotlink cuối cùng còn sót. Bản local `public://products/khoa-thong-minh-t28-0.webp` chỉ 64 KB.

Khối này không nhận dữ liệu từ API nên không có object ảnh để truyền. Copy file vào thư mục tĩnh của Nuxt và trỏ đường dẫn tương đối, để nó không phụ thuộc host của Drupal:

```bash
cp web/sites/default/files/products/khoa-thong-minh-t28-0.webp \
   frontend/public/images/khoa-thong-minh-t28.webp
```

Rồi sửa dòng 10 — giữ nguyên `class`, chỉ đổi `src` và thêm ba thuộc tính:

```vue
<img src="/images/khoa-thong-minh-t28.webp" alt="Khóa thông minh Keybolts T28" width="1200" height="1200" loading="lazy" decoding="async" class="size-full object-contain">
```

`width`/`height` phải là kích thước thật của file — đọc bằng `ddev drush php:eval 'print_r(getimagesize("web/sites/default/files/products/khoa-thong-minh-t28-0.webp"));'` chứ đừng chép con số trên.

- [ ] **Step 3: Kiểm tra TypeScript bắt hết chỗ sót**

```bash
cd frontend && npx nuxi typecheck
```

Kỳ vọng: không còn lỗi kiểu. Vì Task 4 đã đổi `image: string` thành object, mọi chỗ còn dùng ảnh như chuỗi sẽ hiện ra ở đây.

- [ ] **Step 4: Chạy toàn bộ test frontend**

```bash
cd frontend && npm test
```

- [ ] **Step 5: Xem bằng mắt**

```bash
cd frontend && npm run dev
```

Mở `https://vietlong.ddev.site/`, `/san-pham`, `/tin-tuc`, `/du-an` và một trang chi tiết mỗi loại. Kiểm tra: ảnh hiện đủ, không vỡ khung, không nhảy layout khi tải.

- [ ] **Step 6: Commit**

```bash
git add frontend/app
git commit -m "feat(frontend): render every content image responsively"
```

---

### Task 6: Đo lại và dọn rác

**Files:**
- Delete: `web/sites/default/files/home/web/`

- [ ] **Step 1: Đo dung lượng ảnh trang chủ sau khi sửa**

```bash
cd /private/tmp/claude-501/-Users-mac-Desktop-PROJECT-VietLong/c8c9cc7c-f33c-4dfd-a669-ae93cadb14bf/scratchpad
curl -sk https://vietlong.ddev.site/ -o after.html
grep -o 'src="[^"]*\.\(webp\|jpg\|png\|jpeg\)"' after.html | sed 's/src="//;s/"//' | sort -u > after.txt
while read u; do curl -sk -o /dev/null -w "%{size_download}\n" "$u"; done < after.txt | paste -sd+ | bc
```

Ghi con số thật vào commit message. Trước khi sửa: 34.579.400 byte (33,8 MB).

- [ ] **Step 2: Xóa thư mục rác**

Thư mục `web/sites/default/files/home/web/sites/default/files/about/` là bản sao lồng nhau do một lệnh rsync sai đường dẫn, 1,1 MB. Xác nhận nó chỉ chứa bản sao rồi mới xóa:

```bash
find web/sites/default/files/home/web -type f | head -20
ddev drush php:eval '
$ids = \Drupal::entityTypeManager()->getStorage("file")->getQuery()
  ->accessCheck(FALSE)->condition("uri", "public://home/web/%", "LIKE")->execute();
echo count($ids) . " managed file nằm trong thư mục này" . PHP_EOL;'
```

Chỉ xóa nếu kết quả là 0 managed file:

```bash
rm -rf web/sites/default/files/home/web
```

- [ ] **Step 3: Commit**

```bash
git commit -am "chore: drop the duplicated files directory left by a bad rsync"
```

---

### Task 7: In đậm dòng mô tả dưới tên sản phẩm trong slide trang chủ

**Files:**
- Modify: `frontend/app/components/home/FeaturedTabs.vue:70`

Việc này không liên quan tới ảnh, để riêng một commit để review độc lập.

- [ ] **Step 1: Đổi độ đậm**

Dòng 70 hiện là:

```vue
<span class="text-caption text-neutral-600 leading-[1.6]">{{ p.model }}<template v-if="p.finish"> · {{ p.finish.name }}</template></span>
```

Đổi thành:

```vue
<span class="text-caption font-bold text-neutral-700 leading-[1.6]">{{ p.model }}<template v-if="p.finish"> · {{ p.finish.name }}</template></span>
```

`font-bold` là phần được yêu cầu; `text-neutral-600` lên `text-neutral-700` vì chữ đậm ở màu xám nhạt trông bẩn hơn là rõ. Nếu người dùng chỉ muốn đúng độ đậm mà giữ nguyên màu, bỏ phần đổi màu.

- [ ] **Step 2: Xem bằng mắt**

Mở `https://vietlong.ddev.site/` phần "Sản phẩm nổi bật", đối chiếu dòng mã sản phẩm dưới tên.

- [ ] **Step 3: Commit**

```bash
git add frontend/app/components/home/FeaturedTabs.vue
git commit -m "style(frontend): strengthen the model line under product names"
```

---

## Sau khi xong

Deploy lên dev theo đúng trình tự đã dùng cho view leads:

```bash
git push origin feat/static-pages
ssh root@45.118.145.203 'cd /var/www/vietlong.themeshub.net && git pull --ff-only origin feat/static-pages'
ssh root@45.118.145.203 'cd /var/www/vietlong.themeshub.net && vendor/bin/drush php:script scripts/setup/install_cover_image_fields.php && vendor/bin/drush php:script scripts/setup/migrate_image_urls_to_fields.php && vendor/bin/drush cr'
ssh root@45.118.145.203 'cd /var/www/vietlong.themeshub.net/frontend && npm run build && systemctl restart vietlong-nuxt'
```

Rồi đo lại dung lượng trang chủ production bằng đúng lệnh ở Task 6 Step 1, đổi host thành `https://vietlong.themeshub.net/`.
