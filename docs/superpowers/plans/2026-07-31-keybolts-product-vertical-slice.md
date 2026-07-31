# Keybolts Product Vertical Slice — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ship the Keybolts product axis end to end — Drupal 11 content model and REST API, plus a Nuxt 4 SSR frontend rendering the homepage, product listing and product detail exactly as the `design/` prototypes specify.

**Architecture:** Drupal 11 is API-only and never renders HTML for end users. Two custom modules split by responsibility: `keybolts_core` owns the content model and business logic (variant grouping, facet counting, Vietnamese text normalisation) and knows nothing about HTTP; `keybolts_api` owns only the transport layer (routes, query params, pagination, envelope, cache tags). Nuxt 4 runs SSR as a separate Node process and consumes `/api/v1/*`.

**Tech Stack:** Drupal 11.4.4 · PHP 8.4.22 · Drush 13.7.6 · MariaDB 11.8 · DDEV (`vietlong`) · Nuxt 4 · Vue 3 · TypeScript · Tailwind CSS 4 · Vitest · PHPUnit

## Global Constraints

- Drupal site URL is `https://vietlong.ddev.site`; admin credentials `admin` / `admin`.
- All Drush commands run through DDEV: `ddev drush …`. All Composer commands: `ddev composer …`.
- Frontend runs on the **host** (Node v20.19.6, npm 10.8.2), not inside DDEV — this avoids mutagen syncing `node_modules`. Nuxt 4 requires Node `^20.19.0 || >=22.12.0`; v20.19.6 satisfies this at the floor.
- Frontend dev server is `http://localhost:3000`. API base URL is `https://vietlong.ddev.site/api/v1`.
- **`design/*.html` is the single source of truth for UI.** These files are edited by the user during the project. Before implementing any page, re-read its design file and reconcile against this plan. Never build a page from an extraction made earlier in a session.
- The real markup inside a design file is a JSON string in `<script type="__bundler/template">`; page data and interaction logic are in the `<script type="text/x-dc">` block. Extract with `json.loads`, do not read the raw file.
- Design tokens are copied verbatim from the prototypes. Never invent a colour, size or spacing value.
- Product listing paginates at **12 items per page**.
- Sort options are exactly `featured` | `az` | `za` | `cat`. There is no price sort — price always displays as "Liên hệ".
- API envelope is always `{ "data": …, "meta": { "total": int, "page": int, "limit": int }, "facets": {…} }`. `facets` may be an empty object but the key is always present.
- All user-facing copy is Vietnamese, taken verbatim from the design prototypes.
- Every Drupal config change must be exportable: after each task that changes config, run `ddev drush cex -y` and commit `config/sync`.

---

## File Structure

**Drupal — `web/modules/custom/keybolts_core/`** (content model + business logic, no HTTP)

| File | Responsibility |
|---|---|
| `keybolts_core.info.yml` | Module declaration |
| `keybolts_core.services.yml` | Service wiring |
| `keybolts_core.module` | `hook_entity_presave` populating `field_search_text` |
| `src/Service/TextNormalizer.php` | Vietnamese diacritic stripping |
| `src/Service/VariantMatrixBuilder.php` | Group sibling nodes by `field_family` into a size × finish matrix |
| `src/Service/ProductQuery.php` | Filter + sort + paginate product nodes |
| `src/Service/ProductFacetBuilder.php` | Count products per brand / category / finish |
| `tests/src/Kernel/*` | Kernel tests for the four services |

**Drupal — `web/modules/custom/keybolts_api/`** (HTTP only)

| File | Responsibility |
|---|---|
| `keybolts_api.info.yml` / `keybolts_api.routing.yml` / `keybolts_api.services.yml` | Module + routes + wiring |
| `src/ApiEnvelope.php` | Builds the `{data, meta, facets}` response with cache metadata |
| `src/Serializer/ProductSerializer.php` | Node → array shape used by the frontend |
| `src/Controller/ProductController.php` | `list`, `detail`, `suggest` |
| `src/Controller/HomepageController.php` | Aggregated homepage payload |
| `src/Controller/MenuController.php` | Menu trees |

**Setup + seed — `scripts/`**

| File | Responsibility |
|---|---|
| `scripts/setup/install_product_model.php` | Creates vocabularies, fields, form/view displays. Idempotent. |
| `scripts/seed/extract_catalog.py` | Pulls `CATALOG`, `CATALOG_CATS`, `BRANDS`, `FINISHES` out of the design prototype into JSON |
| `scripts/seed/catalog.json` | Generated seed data (committed) |
| `scripts/seed/seed_products.php` | Creates terms + 26 product nodes, downloads and optimises images. Idempotent. |

**Frontend — `frontend/`**

| File | Responsibility |
|---|---|
| `nuxt.config.ts` | SSR config, Tailwind, runtime config |
| `scripts/compute-oklch.mjs` | Resolves `oklch(from …)` to static hex, writes `app/assets/css/_generated-palette.css` |
| `app/assets/css/tokens.css` | `@theme` block — the Tailwind config |
| `app/composables/useViewport.ts` | `isMobile` / `isWide` / `utilWide` |
| `app/services/http.ts`, `app/services/products.ts`, `app/services/homepage.ts` | API access layer — components never call `$fetch` directly |
| `app/types/product.ts` | Shared TypeScript types |
| `app/components/layout/*` | TopBar, MainBar, MegaMenu, MobileNavPanel, SearchOverlay, StickyMobileCta, SiteFooter, Breadcrumb |
| `app/components/product/*` | ProductCard, FilterSidebar, FinishSwatchGroup, SortSelect, Pagination, Gallery, VariantSelector, TabGroup, SpecTable, FaqAccordion |
| `app/components/home/*` | Hero, StatStrip, UspStrip, CategoryGrid, FeaturedTabs, SolutionGrid, TechBlock, ProjectGrid, ArticleGrid, ConsultForm, BranchList |
| `app/pages/index.vue`, `san-pham/index.vue`, `san-pham/[slug].vue`, `danh-muc/[slug].vue`, `thuong-hieu/[slug].vue` | Routes |
| `app/utils/productFilterState.ts` | Filter ↔ query-string state (unit tested) |

---

### Task 1: Version control baseline

No git repository exists yet. Every later task ends in a commit, so this must come first.

**Files:**
- Create: `.gitignore`

- [ ] **Step 1: Create `.gitignore`**

```gitignore
# Drupal scaffold & dependencies
/vendor/
/web/core/
/web/modules/contrib/
/web/themes/contrib/
/web/profiles/contrib/
/web/libraries/
/web/sites/default/files/
/web/sites/default/settings.local.php
/web/sites/default/settings.php
/web/sites/default/services.yml

# DDEV
/.ddev/.*/
/.ddev/db_snapshots/

# Frontend
/frontend/node_modules/
/frontend/.nuxt/
/frontend/.output/
/frontend/dist/
/frontend/app/assets/css/_generated-palette.css

# OS / editor
.DS_Store
*.log
```

- [ ] **Step 2: Initialise the repository and make the baseline commit**

```bash
git init
git add -A
git commit -m "chore: baseline Drupal 11 project, design prototypes and docs"
```

- [ ] **Step 3: Verify the working tree is clean and `vendor/` is excluded**

Run: `git status --short && git ls-files | grep -c '^vendor/'`
Expected: no output from `git status`; the count is `0`.

---

### Task 2: Contrib modules and CORS

**Interfaces:**
- Produces: enabled modules `jsonapi`, `jsonapi_extras`, `pathauto`, `metatag`, `redirect`, `paragraphs`, `admin_toolbar`; CORS allowing `http://localhost:3000`.

**Files:**
- Modify: `composer.json` (via composer)
- Create: `web/sites/default/services.yml` (from DDEV's generated default)

- [ ] **Step 1: Require the contrib modules**

```bash
ddev composer require \
  drupal/jsonapi_extras \
  drupal/pathauto \
  drupal/metatag \
  drupal/redirect \
  drupal/paragraphs \
  drupal/admin_toolbar
```

- [ ] **Step 2: Enable them**

```bash
ddev drush en -y jsonapi jsonapi_extras pathauto metatag redirect paragraphs admin_toolbar
```

- [ ] **Step 3: Verify all seven report Enabled**

Run: `ddev drush pml --status=enabled --type=module | grep -E "jsonapi|pathauto|metatag|redirect|paragraphs|admin_toolbar"`
Expected: seven rows, each showing `Enabled`.

- [ ] **Step 4: Enable CORS for the frontend dev server**

Edit `web/sites/default/services.yml` — set the `cors.config` block to:

```yaml
parameters:
  cors.config:
    enabled: true
    allowedHeaders: ['content-type', 'authorization', 'accept']
    allowedMethods: ['GET', 'POST', 'OPTIONS']
    allowedOrigins: ['http://localhost:3000']
    exposedHeaders: false
    maxAge: 3600
    supportsCredentials: false
```

- [ ] **Step 5: Rebuild cache and verify the CORS header is sent**

```bash
ddev drush cr
curl -sI -H "Origin: http://localhost:3000" https://vietlong.ddev.site/ | grep -i access-control-allow-origin
```
Expected: `access-control-allow-origin: http://localhost:3000`

- [ ] **Step 6: Commit**

```bash
git add composer.json composer.lock web/sites/default/services.yml
git commit -m "feat: add contrib modules and enable CORS for frontend dev server"
```

---

### Task 3: `keybolts_core` module skeleton and `TextNormalizer`

Vietnamese search must be diacritic-insensitive: typing `khoa van tay` must match `Khóa Vân Tay`. This is the first piece of real logic, so it comes with the module skeleton.

**Interfaces:**
- Produces: service `keybolts_core.text_normalizer`, class `Drupal\keybolts_core\Service\TextNormalizer` with `public function normalize(string $text): string`.

**Files:**
- Create: `web/modules/custom/keybolts_core/keybolts_core.info.yml`
- Create: `web/modules/custom/keybolts_core/keybolts_core.services.yml`
- Create: `web/modules/custom/keybolts_core/src/Service/TextNormalizer.php`
- Test: `web/modules/custom/keybolts_core/tests/src/Kernel/TextNormalizerTest.php`

- [ ] **Step 1: Create the module skeleton**

`web/modules/custom/keybolts_core/keybolts_core.info.yml`:

```yaml
name: 'Keybolts Core'
type: module
description: 'Content model and business logic for the Keybolts catalogue.'
package: Keybolts
core_version_requirement: ^11
dependencies:
  - drupal:node
  - drupal:taxonomy
```

`web/modules/custom/keybolts_core/keybolts_core.services.yml`:

```yaml
services:
  keybolts_core.text_normalizer:
    class: Drupal\keybolts_core\Service\TextNormalizer
```

- [ ] **Step 2: Write the failing test**

`web/modules/custom/keybolts_core/tests/src/Kernel/TextNormalizerTest.php`:

```php
<?php

namespace Drupal\Tests\keybolts_core\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * @group keybolts
 */
class TextNormalizerTest extends KernelTestBase {

  protected static $modules = ['keybolts_core'];

  public function testStripsVietnameseDiacritics(): void {
    $normalizer = $this->container->get('keybolts_core.text_normalizer');
    $this->assertSame('khoa van tay', $normalizer->normalize('Khóa Vân Tay'));
  }

  public function testHandlesDWithStroke(): void {
    $normalizer = $this->container->get('keybolts_core.text_normalizer');
    $this->assertSame('khoa dong dai sanh', $normalizer->normalize('Khóa Đồng Đại Sảnh'));
  }

  public function testCollapsesWhitespaceAndPunctuation(): void {
    $normalizer = $this->container->get('keybolts_core.text_normalizer');
    $this->assertSame('kb 1700 xl pvd', $normalizer->normalize('KB 1700-XL-PVD'));
  }

  public function testIsIdempotent(): void {
    $normalizer = $this->container->get('keybolts_core.text_normalizer');
    $once = $normalizer->normalize('Chốt Cremone Đồng');
    $this->assertSame($once, $normalizer->normalize($once));
  }
}
```

- [ ] **Step 3: Run the test to verify it fails**

Run: `ddev exec ./vendor/bin/phpunit -c web/core/phpunit.xml.dist web/modules/custom/keybolts_core/tests/src/Kernel/TextNormalizerTest.php`
Expected: FAIL — service `keybolts_core.text_normalizer` does not exist.

- [ ] **Step 4: Implement `TextNormalizer`**

`web/modules/custom/keybolts_core/src/Service/TextNormalizer.php`:

```php
<?php

declare(strict_types=1);

namespace Drupal\keybolts_core\Service;

/**
 * Normalises Vietnamese text for diacritic-insensitive matching.
 */
class TextNormalizer {

  /**
   * Vietnamese letters mapped to their ASCII equivalents.
   *
   * Explicit rather than relying on the intl Transliterator, because the
   * transliterator's handling of 'đ' varies across ICU versions and this
   * mapping must stay stable — it is written into stored search text.
   */
  private const MAP = [
    'à' => 'a', 'á' => 'a', 'ạ' => 'a', 'ả' => 'a', 'ã' => 'a',
    'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ậ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a',
    'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ặ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a',
    'è' => 'e', 'é' => 'e', 'ẹ' => 'e', 'ẻ' => 'e', 'ẽ' => 'e',
    'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ệ' => 'e', 'ể' => 'e', 'ễ' => 'e',
    'ì' => 'i', 'í' => 'i', 'ị' => 'i', 'ỉ' => 'i', 'ĩ' => 'i',
    'ò' => 'o', 'ó' => 'o', 'ọ' => 'o', 'ỏ' => 'o', 'õ' => 'o',
    'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ộ' => 'o', 'ổ' => 'o', 'ỗ' => 'o',
    'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ợ' => 'o', 'ở' => 'o', 'ỡ' => 'o',
    'ù' => 'u', 'ú' => 'u', 'ụ' => 'u', 'ủ' => 'u', 'ũ' => 'u',
    'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ự' => 'u', 'ử' => 'u', 'ữ' => 'u',
    'ỳ' => 'y', 'ý' => 'y', 'ỵ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y',
    'đ' => 'd',
  ];

  /**
   * Lowercases, strips diacritics and reduces separators to single spaces.
   */
  public function normalize(string $text): string {
    $text = mb_strtolower($text, 'UTF-8');
    $text = strtr($text, self::MAP);
    // Anything that is not an ASCII letter or digit becomes a separator.
    $text = preg_replace('/[^a-z0-9]+/', ' ', $text) ?? '';
    return trim($text);
  }
}
```

- [ ] **Step 5: Enable the module and run the tests**

```bash
ddev drush en -y keybolts_core
ddev exec ./vendor/bin/phpunit -c web/core/phpunit.xml.dist web/modules/custom/keybolts_core/tests/src/Kernel/TextNormalizerTest.php
```
Expected: 4 tests, 4 assertions minimum, all PASS.

- [ ] **Step 6: Commit**

```bash
git add web/modules/custom/keybolts_core
git commit -m "feat(core): add keybolts_core module with Vietnamese text normalizer"
```

---

### Task 4: Product content model

Creates the taxonomies and the `product` content type programmatically. A script rather than hand-written config YAML: the model has ~30 fields, and four YAML files per field is unmaintainable. Config is captured afterwards with `drush cex`, so deployment is still config-driven.

**Interfaces:**
- Produces: vocabularies `brand`, `product_category`, `finish`; content type `product` with all fields listed in the spec, including `field_family`, `field_size_key`, `field_size_label`, `field_size_note`, `field_finish`, `field_search_text`.

**Files:**
- Create: `scripts/setup/install_product_model.php`

- [ ] **Step 1: Write the setup script**

`scripts/setup/install_product_model.php` — creates vocabularies, then fields. Idempotent: every create is guarded by a load.

```php
<?php

/**
 * @file
 * Creates the Keybolts product content model. Safe to run repeatedly.
 *
 * Run: ddev drush php:script scripts/setup/install_product_model.php
 */

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;
use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Creates a vocabulary when it is missing.
 */
function kb_vocabulary(string $id, string $label): void {
  if (!Vocabulary::load($id)) {
    Vocabulary::create(['vid' => $id, 'name' => $label])->save();
    echo "vocabulary: {$id}\n";
  }
}

/**
 * Creates a field storage + instance when missing.
 *
 * @param array $settings
 *   Storage settings, e.g. target_type for entity_reference.
 * @param array $instance
 *   Instance settings, e.g. handler_settings for entity_reference.
 */
function kb_field(
  string $entity_type,
  string $bundle,
  string $name,
  string $type,
  string $label,
  int $cardinality = 1,
  array $settings = [],
  array $instance = [],
  bool $required = FALSE,
): void {
  if (!FieldStorageConfig::loadByName($entity_type, $name)) {
    FieldStorageConfig::create([
      'field_name' => $name,
      'entity_type' => $entity_type,
      'type' => $type,
      'cardinality' => $cardinality,
      'settings' => $settings,
    ])->save();
  }
  if (!FieldConfig::loadByName($entity_type, $bundle, $name)) {
    FieldConfig::create([
      'field_name' => $name,
      'entity_type' => $entity_type,
      'bundle' => $bundle,
      'label' => $label,
      'required' => $required,
      'settings' => $instance,
    ])->save();
    echo "field: {$bundle}.{$name}\n";
  }
}

// --- Vocabularies -----------------------------------------------------------
kb_vocabulary('brand', 'Thương hiệu');
kb_vocabulary('product_category', 'Danh mục sản phẩm');
kb_vocabulary('finish', 'Hoàn thiện');

// Vocabulary fields.
kb_field('taxonomy_term', 'brand', 'field_tag', 'string', 'Tag');
kb_field('taxonomy_term', 'brand', 'field_cta_label', 'string', 'Nhãn CTA');
kb_field('taxonomy_term', 'product_category', 'field_number', 'string', 'Số thứ tự');
kb_field('taxonomy_term', 'product_category', 'field_short_desc', 'string_long', 'Mô tả ngắn');
kb_field('taxonomy_term', 'product_category', 'field_image', 'image', 'Ảnh');
kb_field('taxonomy_term', 'finish', 'field_swatch', 'string', 'Mã màu');
kb_field('taxonomy_term', 'finish', 'field_suffix', 'string', 'Hậu tố mã');

// --- Content type -----------------------------------------------------------
if (!NodeType::load('product')) {
  NodeType::create(['type' => 'product', 'name' => 'Sản phẩm'])->save();
  echo "content type: product\n";
}

$ref = fn(string $vid) => [
  'handler' => 'default:taxonomy_term',
  'handler_settings' => ['target_bundles' => [$vid => $vid]],
];

// Identity and grouping.
kb_field('node', 'product', 'field_product_code', 'string', 'Mã sản phẩm', 1, [], [], TRUE);
kb_field('node', 'product', 'field_family', 'string', 'Dòng sản phẩm');
kb_field('node', 'product', 'field_size_key', 'string', 'Mã size');
kb_field('node', 'product', 'field_size_label', 'string', 'Tên size');
kb_field('node', 'product', 'field_size_note', 'string', 'Ghi chú size');
kb_field('node', 'product', 'field_search_text', 'string_long', 'Chuỗi tìm kiếm');

// Classification.
kb_field('node', 'product', 'field_brand', 'entity_reference', 'Thương hiệu', 1, ['target_type' => 'taxonomy_term'], $ref('brand'), TRUE);
kb_field('node', 'product', 'field_category', 'entity_reference', 'Danh mục', 1, ['target_type' => 'taxonomy_term'], $ref('product_category'), TRUE);
kb_field('node', 'product', 'field_finish', 'entity_reference', 'Hoàn thiện', 1, ['target_type' => 'taxonomy_term'], $ref('finish'));

// Media.
kb_field('node', 'product', 'field_images', 'image', 'Ảnh sản phẩm', 12);

// Merchandising.
kb_field('node', 'product', 'field_badge', 'list_string', 'Nhãn', 1, [
  'allowed_values' => ['ban-chay' => 'Bán chạy', 'moi' => 'Mới', 'cao-cap' => 'Cao cấp'],
]);
kb_field('node', 'product', 'field_stock_status', 'list_string', 'Tình trạng', 1, [
  'allowed_values' => [
    'con-hang' => 'Còn hàng — giao 2–5 ngày',
    'het-hang' => 'Hết hàng',
    'dat-truoc' => 'Đặt trước',
  ],
]);
kb_field('node', 'product', 'field_featured', 'boolean', 'Nổi bật');
kb_field('node', 'product', 'field_featured_group', 'list_string', 'Nhóm nổi bật', 1, [
  'allowed_values' => [
    'dong' => 'Khoá đồng nhập khẩu',
    'cremone' => 'CREMONE chốt khoá',
    'hotel' => 'Khoá khách sạn',
    'phukien' => 'Phụ kiện khác',
  ],
]);
kb_field('node', 'product', 'field_is_new', 'boolean', 'Sản phẩm mới');
kb_field('node', 'product', 'field_sort_order', 'integer', 'Thứ tự');
kb_field('node', 'product', 'field_contact_price', 'boolean', 'Liên hệ để biết giá');

// Descriptive content.
kb_field('node', 'product', 'field_short_desc', 'string_long', 'Mô tả ngắn', 1, [], [], TRUE);
kb_field('node', 'product', 'field_desc_heading', 'string', 'Tiêu đề mô tả');
kb_field('node', 'product', 'field_description', 'text_long', 'Mô tả chi tiết');
kb_field('node', 'product', 'field_highlights', 'string_long', 'Điểm nổi bật', -1);

// Technical attributes.
kb_field('node', 'product', 'field_door_thickness', 'string', 'Độ dày cửa');
kb_field('node', 'product', 'field_origin', 'string', 'Xuất xứ');
kb_field('node', 'product', 'field_certification', 'string', 'Chứng nhận', -1);
kb_field('node', 'product', 'field_warranty', 'string', 'Bảo hành');

// Relations.
kb_field('node', 'product', 'field_related_products', 'entity_reference', 'Sản phẩm liên quan', -1,
  ['target_type' => 'node'],
  ['handler' => 'default:node', 'handler_settings' => ['target_bundles' => ['product' => 'product']]]
);

echo "done\n";
```

- [ ] **Step 2: Run the script**

```bash
ddev drush php:script scripts/setup/install_product_model.php
```
Expected: lines listing each vocabulary, content type and field created.

- [ ] **Step 3: Verify idempotency by running it again**

Run: `ddev drush php:script scripts/setup/install_product_model.php`
Expected: only `done` — nothing is recreated.

- [ ] **Step 4: Verify the field list**

Run: `ddev drush field:info node product --format=csv | wc -l`
Expected: at least `28` lines (27 fields plus the header).

- [ ] **Step 5: Configure Pathauto for products**

```bash
ddev drush php:eval "
\Drupal\pathauto\Entity\PathautoPattern::create([
  'id' => 'product',
  'label' => 'Product',
  'type' => 'canonical_entities:node',
  'pattern' => '/san-pham/[node:title]',
  'selection_criteria' => [[
    'id' => 'entity_bundle:node',
    'negate' => FALSE,
    'context_mapping' => ['node' => 'node'],
    'bundles' => ['product' => 'product'],
  ]],
])->save();
"
```

- [ ] **Step 6: Export config and commit**

```bash
ddev drush cex -y
git add scripts/setup config/sync
git commit -m "feat(core): add product content model and taxonomies"
```

---

### Task 5: Seed the catalogue with real data and optimised images

Design images are live on the old site but enormous — a single PNG is 2.6 MB and one JPG is 5.4 MB. The seed converts to WebP at load time; leaving originals in place would make the frontend unusable and hide a real risk until late.

**Interfaces:**
- Consumes: content model from Task 4.
- Produces: 2 brand terms, 8 category terms, 4 finish terms, 26 product nodes with `field_family` populated and WebP images.

**Files:**
- Create: `scripts/seed/extract_catalog.py`
- Create: `scripts/seed/catalog.json` (generated, committed)
- Create: `scripts/seed/seed_products.php`

- [ ] **Step 1: Write the extraction script**

`scripts/seed/extract_catalog.py` — pulls the four data arrays out of the products prototype. Re-run it whenever the design changes.

```python
#!/usr/bin/env python3
"""Extract catalogue seed data from the Products design prototype."""

import json
import pathlib
import re

ROOT = pathlib.Path(__file__).resolve().parents[2]
SRC = ROOT / "design" / "Keybolts Products.html"
OUT = pathlib.Path(__file__).parent / "catalog.json"


def page_template(html: str) -> str:
    """The real markup is a JSON string inside a bundler script tag."""
    m = re.search(r'<script type="__bundler/template">\s*(".*?")\s*</script>', html, re.S)
    if not m:
        raise SystemExit("template script not found")
    return json.loads(m.group(1))


def js_array(source: str, name: str) -> list[dict]:
    """Parse a `NAME = [ {...}, ... ];` literal into Python dicts."""
    m = re.search(rf"\b{name}\s*=\s*\[(.*?)\n  \];", source, re.S)
    if not m:
        raise SystemExit(f"array {name} not found")
    items = []
    for obj in re.finditer(r"\{([^{}]*)\}", m.group(1)):
        entry = {}
        for k, v in re.findall(r"(\w+):\s*'((?:[^'\\]|\\.)*)'", obj.group(1)):
            entry[k] = v.replace("\\'", "'")
        for k, v in re.findall(r"(\w+):\s*(true|false)", obj.group(1)):
            entry[k] = v == "true"
        if entry:
            items.append(entry)
    return items


def family_of(model: str) -> str:
    """`KB 1700-XL-PVD` -> `KB 1700`. Falls back to the leading token."""
    head = model.split("-")[0].strip()
    return head or model


def main() -> None:
    source = page_template(SRC.read_text(encoding="utf-8"))
    data = {
        "brands": js_array(source, "BRANDS"),
        "categories": js_array(source, "CATALOG_CATS"),
        "finishes": js_array(source, "FINISHES"),
        "products": js_array(source, "CATALOG"),
    }
    for p in data["products"]:
        p["family"] = family_of(p.get("model", ""))
    data["brands"] = [b for b in data["brands"] if b.get("key") != "all"]
    data["categories"] = [c for c in data["categories"] if c.get("key") != "all"]
    OUT.write_text(json.dumps(data, ensure_ascii=False, indent=2), encoding="utf-8")
    print(f"{OUT.name}: {len(data['products'])} products, "
          f"{len(data['categories'])} categories, {len(data['finishes'])} finishes")


if __name__ == "__main__":
    main()
```

- [ ] **Step 2: Run it and verify the counts**

Run: `python3 scripts/seed/extract_catalog.py`
Expected: `catalog.json: 26 products, 8 categories, 4 finishes`

- [ ] **Step 3: Write the seed script**

`scripts/seed/seed_products.php`:

```php
<?php

/**
 * @file
 * Seeds Keybolts taxonomy terms and product nodes. Safe to run repeatedly.
 *
 * Run: ddev drush php:script scripts/seed/seed_products.php
 */

use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

$data = json_decode(file_get_contents(dirname(__DIR__, 2) . '/scripts/seed/catalog.json'), TRUE);
$fs = \Drupal::service('file_system');
$dir = 'public://products';
$fs->prepareDirectory($dir, \Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY);

/**
 * Loads a term by name in a vocabulary, creating it when missing.
 */
function kb_term(string $vid, string $name, array $fields = []): Term {
  $existing = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
    ->loadByProperties(['vid' => $vid, 'name' => $name]);
  if ($existing) {
    return reset($existing);
  }
  $term = Term::create(['vid' => $vid, 'name' => $name] + $fields);
  $term->save();
  return $term;
}

/**
 * Downloads a remote image and re-encodes it as WebP.
 *
 * The originals are multi-megabyte; storing them unconverted would make the
 * frontend unusable.
 */
function kb_image(string $url, string $dir): ?File {
  $name = preg_replace('/[^a-z0-9]+/i', '-', pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_FILENAME));
  $dest = "{$dir}/{$name}.webp";
  $fs = \Drupal::service('file_system');

  $existing = \Drupal::entityTypeManager()->getStorage('file')
    ->loadByProperties(['uri' => $dest]);
  if ($existing) {
    return reset($existing);
  }

  $bytes = @file_get_contents($url);
  if ($bytes === FALSE) {
    echo "  ! download failed: {$url}\n";
    return NULL;
  }

  try {
    $img = new \Imagick();
    $img->readImageBlob($bytes);
    $img->setImageFormat('webp');
    $img->setImageCompressionQuality(82);
    if ($img->getImageWidth() > 1600) {
      $img->resizeImage(1600, 0, \Imagick::FILTER_LANCZOS, 1);
    }
    $img->stripImage();
    $blob = $img->getImageBlob();
    $img->destroy();
  }
  catch (\Throwable $e) {
    echo "  ! convert failed: {$url} ({$e->getMessage()})\n";
    return NULL;
  }

  $uri = $fs->saveData($blob, $dest, \Drupal\Core\File\FileExists::Replace);
  $file = File::create(['uri' => $uri]);
  $file->setPermanent();
  $file->save();
  echo sprintf("  image %s: %dKB -> %dKB\n", $name, strlen($bytes) / 1024, strlen($blob) / 1024);
  return $file;
}

// --- Terms ------------------------------------------------------------------
$brands = $cats = $finishes = [];
foreach ($data['brands'] as $b) {
  $brands[$b['key']] = kb_term('brand', $b['name'], [
    'description' => $b['desc'] ?? '',
    'field_tag' => $b['tag'] ?? '',
    'field_cta_label' => $b['cta'] ?? '',
  ]);
}
foreach ($data['categories'] as $i => $c) {
  $cats[$c['key']] = kb_term('product_category', $c['label'], [
    'field_number' => sprintf('%02d', $i + 1),
  ]);
}
foreach ($data['finishes'] as $f) {
  $finishes[$f['key']] = kb_term('finish', $f['label'], [
    'field_swatch' => $f['swatch'] ?? '',
    'field_suffix' => strtoupper($f['key']),
  ]);
}
echo sprintf("terms: %d brands, %d categories, %d finishes\n",
  count($brands), count($cats), count($finishes));

// --- Sizes ------------------------------------------------------------------
// Size is encoded in the model string, e.g. `KB 1700-XL-PVD` -> XL.
$sizes = [
  'XL' => ['Đại sảnh XL', 'Cửa 2 cánh lớn'],
  'L'  => ['Đại L', 'Cửa chính 1 cánh'],
  'M'  => ['Trung M', 'Cửa phòng lớn'],
  'S'  => ['Thông phòng S', 'Cửa phòng ngủ'],
];

// --- Products ---------------------------------------------------------------
$created = 0;
foreach ($data['products'] as $p) {
  $existing = \Drupal::entityTypeManager()->getStorage('node')
    ->loadByProperties(['type' => 'product', 'field_product_code' => $p['model']]);
  if ($existing) {
    continue;
  }

  $size_key = '';
  $size_label = '';
  $size_note = '';
  foreach ($sizes as $key => [$label, $note]) {
    if (preg_match('/-' . $key . '(-|$)/', $p['model'])) {
      $size_key = strtolower($key);
      $size_label = $label;
      $size_note = $note;
      break;
    }
  }

  $values = [
    'type' => 'product',
    'title' => $p['name'],
    'status' => 1,
    'field_product_code' => $p['model'],
    'field_family' => $p['family'],
    'field_size_key' => $size_key,
    'field_size_label' => $size_label,
    'field_size_note' => $size_note,
    'field_brand' => $brands[$p['brand']] ?? NULL,
    'field_category' => $cats[$p['cat']] ?? NULL,
    'field_finish' => $finishes[$p['finish']] ?? NULL,
    'field_short_desc' => sprintf('%s — mã %s.', $p['name'], $p['model']),
    'field_stock_status' => 'con-hang',
    'field_contact_price' => TRUE,
    'field_warranty' => '5–10 năm',
    'field_certification' => ['CE-CFF'],
    'field_sort_order' => 0,
  ];
  if (!empty($p['badge'])) {
    $values['field_badge'] = match ($p['badge']) {
      'Bán chạy' => 'ban-chay',
      'Mới' => 'moi',
      'Cao cấp' => 'cao-cap',
      default => NULL,
    };
  }

  echo "product: {$p['name']} ({$p['model']})\n";
  if (!empty($p['img']) && ($file = kb_image($p['img'], $dir))) {
    $values['field_images'] = [
      ['target_id' => $file->id(), 'alt' => $p['name'] . ' — ' . $p['model']],
    ];
  }

  Node::create($values)->save();
  $created++;
}

echo "created {$created} products\n";
```

- [ ] **Step 4: Run the seed**

Run: `ddev drush php:script scripts/seed/seed_products.php`
Expected: term counts, then one `product:` line per SKU with an `image … KB -> … KB` line showing a large size reduction, ending in `created 26 products`.

- [ ] **Step 5: Verify counts and that images really shrank**

```bash
ddev drush php:eval "echo \Drupal::entityQuery('node')->condition('type','product')->accessCheck(FALSE)->count()->execute() . \" products\n\";"
ddev exec 'ls -lS web/sites/default/files/products | head -5'
```
Expected: `26 products`; the largest WebP is well under 500 KB.

- [ ] **Step 6: Verify idempotency**

Run: `ddev drush php:script scripts/seed/seed_products.php`
Expected: `created 0 products`.

- [ ] **Step 7: Commit**

```bash
git add scripts/seed
git commit -m "feat(seed): seed 26 SKUs from design prototype with WebP images"
```

---

### Task 6: Populate `field_search_text` on save

**Interfaces:**
- Consumes: `keybolts_core.text_normalizer` from Task 3.
- Produces: `hook_entity_presave` writing normalised search text into `field_search_text` for every product.

**Files:**
- Create: `web/modules/custom/keybolts_core/keybolts_core.module`
- Test: `web/modules/custom/keybolts_core/tests/src/Kernel/ProductSearchTextTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Drupal\Tests\keybolts_core\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * @group keybolts
 */
class ProductSearchTextTest extends KernelTestBase {

  protected static $modules = ['system', 'user', 'field', 'text', 'node', 'taxonomy', 'keybolts_core'];

  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installConfig(['node']);
    NodeType::create(['type' => 'product', 'name' => 'Product'])->save();
    foreach (['field_product_code' => 'string', 'field_search_text' => 'string_long'] as $name => $type) {
      FieldStorageConfig::create([
        'field_name' => $name, 'entity_type' => 'node', 'type' => $type,
      ])->save();
      FieldConfig::create([
        'field_name' => $name, 'entity_type' => 'node', 'bundle' => 'product', 'label' => $name,
      ])->save();
    }
  }

  public function testSearchTextIsPopulatedOnSave(): void {
    $node = Node::create([
      'type' => 'product',
      'title' => 'Khóa Vân Tay Cửa Kính',
      'field_product_code' => 'KB 8150',
    ]);
    $node->save();

    $text = $node->get('field_search_text')->value;
    $this->assertStringContainsString('khoa van tay cua kinh', $text);
    $this->assertStringContainsString('kb 8150', $text);
  }

  public function testSearchTextIsRefreshedOnRename(): void {
    $node = Node::create(['type' => 'product', 'title' => 'Khóa Đồng', 'field_product_code' => 'KB 1']);
    $node->save();
    $node->setTitle('Chốt Cremone');
    $node->save();

    $this->assertStringContainsString('chot cremone', $node->get('field_search_text')->value);
    $this->assertStringNotContainsString('khoa dong', $node->get('field_search_text')->value);
  }
}
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `ddev exec ./vendor/bin/phpunit -c web/core/phpunit.xml.dist web/modules/custom/keybolts_core/tests/src/Kernel/ProductSearchTextTest.php`
Expected: FAIL — `field_search_text` is empty.

- [ ] **Step 3: Implement the hook**

`web/modules/custom/keybolts_core/keybolts_core.module`:

```php
<?php

/**
 * @file
 * Hook implementations for Keybolts Core.
 */

declare(strict_types=1);

use Drupal\Core\Entity\EntityInterface;

/**
 * Implements hook_entity_presave().
 *
 * Denormalises a diacritic-free search string onto the product so that
 * suggest queries are a plain indexed LIKE rather than a runtime transform.
 */
function keybolts_core_entity_presave(EntityInterface $entity): void {
  if ($entity->getEntityTypeId() !== 'node' || $entity->bundle() !== 'product') {
    return;
  }
  if (!$entity->hasField('field_search_text')) {
    return;
  }

  $parts = [$entity->label()];
  foreach (['field_product_code', 'field_family'] as $field) {
    if ($entity->hasField($field) && !$entity->get($field)->isEmpty()) {
      $parts[] = $entity->get($field)->value;
    }
  }
  foreach (['field_category', 'field_brand', 'field_finish'] as $field) {
    if ($entity->hasField($field) && !$entity->get($field)->isEmpty()) {
      $term = $entity->get($field)->entity;
      if ($term) {
        $parts[] = $term->label();
      }
    }
  }

  $normalizer = \Drupal::service('keybolts_core.text_normalizer');
  $entity->set('field_search_text', $normalizer->normalize(implode(' ', $parts)));
}
```

- [ ] **Step 4: Run the tests**

Run: `ddev exec ./vendor/bin/phpunit -c web/core/phpunit.xml.dist web/modules/custom/keybolts_core/tests/src/Kernel/ProductSearchTextTest.php`
Expected: 2 tests PASS.

- [ ] **Step 5: Backfill the seeded nodes**

```bash
ddev drush php:eval "
foreach (\Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type'=>'product']) as \$n) { \$n->save(); }
echo \"resaved\n\";
"
ddev drush php:eval "
\$n = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type'=>'product']);
\$first = reset(\$n);
echo \$first->get('field_search_text')->value . \"\n\";
"
```
Expected: a lowercase, accent-free string such as `khoa dong dai sanh kb 1700 xl pvd kb 1700 khoa dong keybolts vang bong pvd`.

- [ ] **Step 6: Commit**

```bash
git add web/modules/custom/keybolts_core
git commit -m "feat(core): denormalise diacritic-free search text on product save"
```

---

### Task 7: `VariantMatrixBuilder`

The detail page's finish and size selectors are a query over sibling nodes sharing `field_family`. Missing combinations must be reported as unavailable rather than linking nowhere — real catalogue data will always have gaps.

**Interfaces:**
- Produces: service `keybolts_core.variant_matrix`, class `VariantMatrixBuilder` with
  `public function build(NodeInterface $product): array` returning
  `['family' => string, 'sizes' => [['key','label','note','available' => bool,'slug' => ?string,'code' => ?string]], 'finishes' => [['key','label','swatch','available' => bool,'slug' => ?string,'code' => ?string]]]`.
  Availability is evaluated against the *current* product's other axis: sizes are resolved holding the current finish fixed, finishes holding the current size fixed.

**Files:**
- Create: `web/modules/custom/keybolts_core/src/Service/VariantMatrixBuilder.php`
- Modify: `web/modules/custom/keybolts_core/keybolts_core.services.yml`
- Test: `web/modules/custom/keybolts_core/tests/src/Kernel/VariantMatrixBuilderTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Drupal\Tests\keybolts_core\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;

/**
 * @group keybolts
 */
class VariantMatrixBuilderTest extends KernelTestBase {

  protected static $modules = ['system', 'user', 'field', 'text', 'node', 'taxonomy', 'keybolts_core'];

  /**
   * Creates the product bundle and the fields the builder reads.
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installEntitySchema('taxonomy_term');
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
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `ddev exec ./vendor/bin/phpunit -c web/core/phpunit.xml.dist web/modules/custom/keybolts_core/tests/src/Kernel/VariantMatrixBuilderTest.php`
Expected: FAIL — service `keybolts_core.variant_matrix` does not exist.

- [ ] **Step 3: Implement the builder**

`web/modules/custom/keybolts_core/src/Service/VariantMatrixBuilder.php`:

```php
<?php

declare(strict_types=1);

namespace Drupal\keybolts_core\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

/**
 * Builds the size × finish selector for a product from its sibling nodes.
 *
 * Each variant is a separate node; they are grouped by field_family. The
 * selector is therefore derived data — adding a node with the right family
 * extends the selector with no code or config change.
 */
class VariantMatrixBuilder {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * @return array{family: string, sizes: array, finishes: array}
   */
  public function build(NodeInterface $product): array {
    $family = $product->hasField('field_family') ? (string) $product->get('field_family')->value : '';
    if ($family === '') {
      return ['family' => '', 'sizes' => [], 'finishes' => []];
    }

    $siblings = $this->loadFamily($family);
    $current_size = $this->sizeKey($product);
    $current_finish = $this->finishKey($product);

    return [
      'family' => $family,
      'sizes' => $this->sizeAxis($siblings, $current_finish),
      'finishes' => $this->finishAxis($siblings, $current_size),
    ];
  }

  /**
   * Loads every published product sharing the family.
   *
   * @return \Drupal\node\NodeInterface[]
   */
  private function loadFamily(string $family): array {
    $storage = $this->entityTypeManager->getStorage('node');
    $ids = $storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'product')
      ->condition('status', 1)
      ->condition('field_family', $family)
      ->execute();
    return $ids ? $storage->loadMultiple($ids) : [];
  }

  /**
   * Sizes offered, resolved against the currently selected finish.
   */
  private function sizeAxis(array $siblings, string $current_finish): array {
    $axis = [];
    foreach ($siblings as $node) {
      $key = $this->sizeKey($node);
      if ($key === '') {
        continue;
      }
      // First sighting establishes the label; later ones may fill the slug.
      $axis[$key] ??= [
        'key' => $key,
        'label' => (string) $node->get('field_size_label')->value,
        'note' => (string) $node->get('field_size_note')->value,
        'available' => FALSE,
        'slug' => NULL,
        'code' => NULL,
      ];
      if ($this->finishKey($node) === $current_finish) {
        $axis[$key]['available'] = TRUE;
        $axis[$key]['slug'] = $this->slug($node);
        $axis[$key]['code'] = (string) $node->get('field_product_code')->value;
      }
    }
    return array_values($axis);
  }

  /**
   * Finishes offered, resolved against the currently selected size.
   */
  private function finishAxis(array $siblings, string $current_size): array {
    $axis = [];
    foreach ($siblings as $node) {
      $term = $node->hasField('field_finish') ? $node->get('field_finish')->entity : NULL;
      if (!$term) {
        continue;
      }
      $key = $this->finishKey($node);
      $axis[$key] ??= [
        'key' => $key,
        'label' => $term->label(),
        'swatch' => $term->hasField('field_swatch') ? (string) $term->get('field_swatch')->value : '',
        'available' => FALSE,
        'slug' => NULL,
        'code' => NULL,
      ];
      if ($this->sizeKey($node) === $current_size) {
        $axis[$key]['available'] = TRUE;
        $axis[$key]['slug'] = $this->slug($node);
        $axis[$key]['code'] = (string) $node->get('field_product_code')->value;
      }
    }
    return array_values($axis);
  }

  private function sizeKey(NodeInterface $node): string {
    return $node->hasField('field_size_key') ? (string) $node->get('field_size_key')->value : '';
  }

  private function finishKey(NodeInterface $node): string {
    $term = $node->hasField('field_finish') ? $node->get('field_finish')->entity : NULL;
    return $term ? (string) $term->id() : '';
  }

  /**
   * Path alias without the leading slash, e.g. `san-pham/khoa-dong-dai-sanh`.
   */
  private function slug(NodeInterface $node): string {
    $path = \Drupal::service('path_alias.manager')
      ->getAliasByPath('/node/' . $node->id());
    return ltrim($path, '/');
  }
}
```

- [ ] **Step 4: Register the service**

Append to `web/modules/custom/keybolts_core/keybolts_core.services.yml`:

```yaml
  keybolts_core.variant_matrix:
    class: Drupal\keybolts_core\Service\VariantMatrixBuilder
    arguments: ['@entity_type.manager']
```

- [ ] **Step 5: Run the tests**

Run: `ddev exec ./vendor/bin/phpunit -c web/core/phpunit.xml.dist web/modules/custom/keybolts_core/tests/src/Kernel/VariantMatrixBuilderTest.php`
Expected: 3 tests PASS.

- [ ] **Step 6: Commit**

```bash
git add web/modules/custom/keybolts_core
git commit -m "feat(core): build size x finish variant matrix from sibling nodes"
```

---

### Task 8: `ProductQuery` and `ProductFacetBuilder`

**Interfaces:**
- Produces:
  - service `keybolts_core.product_query`, class `ProductQuery` with
    `public function find(array $filters, string $sort = 'featured', int $page = 1, int $limit = 12): array`
    returning `['nodes' => NodeInterface[], 'total' => int]`. `$filters` accepts keys `brand`, `category`, `finish` (term IDs or machine names as strings; empty values ignored).
  - service `keybolts_core.product_facets`, class `ProductFacetBuilder` with
    `public function counts(array $filters): array` returning
    `['brand' => [term_id => int], 'category' => [...], 'finish' => [...]]`.
    Each axis is counted with **its own filter removed**, so a user can always see what selecting a different value would yield.

**Files:**
- Create: `web/modules/custom/keybolts_core/src/Service/ProductQuery.php`
- Create: `web/modules/custom/keybolts_core/src/Service/ProductFacetBuilder.php`
- Modify: `web/modules/custom/keybolts_core/keybolts_core.services.yml`
- Test: `web/modules/custom/keybolts_core/tests/src/Kernel/ProductQueryTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Drupal\Tests\keybolts_core\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * @group keybolts
 */
class ProductQueryTest extends KernelTestBase {

  protected static $modules = ['system', 'user', 'field', 'text', 'node', 'taxonomy', 'keybolts_core'];

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
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `ddev exec ./vendor/bin/phpunit -c web/core/phpunit.xml.dist web/modules/custom/keybolts_core/tests/src/Kernel/ProductQueryTest.php`
Expected: FAIL — service `keybolts_core.product_query` does not exist.

- [ ] **Step 3: Implement `ProductQuery`**

`web/modules/custom/keybolts_core/src/Service/ProductQuery.php`:

```php
<?php

declare(strict_types=1);

namespace Drupal\keybolts_core\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Filters, sorts and paginates product nodes.
 */
class ProductQuery {

  /** Filter key => field name. */
  private const FILTER_FIELDS = [
    'brand' => 'field_brand',
    'category' => 'field_category',
    'finish' => 'field_finish',
  ];

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * @return array{nodes: array, total: int}
   */
  public function find(array $filters, string $sort = 'featured', int $page = 1, int $limit = 12): array {
    $storage = $this->entityTypeManager->getStorage('node');

    $total = (int) $this->baseQuery($filters)->count()->execute();

    $query = $this->baseQuery($filters);
    $this->applySort($query, $sort);
    $page = max(1, $page);
    $query->range(($page - 1) * $limit, $limit);
    $ids = $query->execute();

    return [
      'nodes' => $ids ? $storage->loadMultiple($ids) : [],
      'total' => $total,
    ];
  }

  /**
   * Builds a query with the shared bundle/status conditions and filters.
   */
  public function baseQuery(array $filters): \Drupal\Core\Entity\Query\QueryInterface {
    $query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'product')
      ->condition('status', 1);

    foreach (self::FILTER_FIELDS as $key => $field) {
      if (!empty($filters[$key])) {
        $query->condition($field, $filters[$key]);
      }
    }
    return $query;
  }

  /**
   * Sort keys mirror the design's select: featured | az | za | cat.
   *
   * There is deliberately no price sort — price always renders as "Liên hệ".
   */
  private function applySort(\Drupal\Core\Entity\Query\QueryInterface $query, string $sort): void {
    match ($sort) {
      'az' => $query->sort('title', 'ASC'),
      'za' => $query->sort('title', 'DESC'),
      'cat' => $query->sort('field_category', 'ASC')->sort('title', 'ASC'),
      default => $query->sort('field_sort_order', 'ASC')->sort('created', 'DESC'),
    };
  }
}
```

- [ ] **Step 4: Implement `ProductFacetBuilder`**

`web/modules/custom/keybolts_core/src/Service/ProductFacetBuilder.php`:

```php
<?php

declare(strict_types=1);

namespace Drupal\keybolts_core\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Counts products per taxonomy value for the listing sidebar.
 */
class ProductFacetBuilder {

  private const AXES = [
    'brand' => 'field_brand',
    'category' => 'field_category',
    'finish' => 'field_finish',
  ];

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly ProductQuery $productQuery,
  ) {}

  /**
   * Counts each axis with its own filter removed.
   *
   * Counting an axis under its own filter would always yield a single non-zero
   * value, which tells the user nothing about what else they could pick.
   *
   * @return array<string, array<int, int>>
   */
  public function counts(array $filters): array {
    $storage = $this->entityTypeManager->getStorage('node');
    $out = [];

    foreach (self::AXES as $axis => $field) {
      $scoped = $filters;
      unset($scoped[$axis]);

      $ids = $this->productQuery->baseQuery($scoped)->execute();
      $tally = [];
      foreach ($storage->loadMultiple($ids) as $node) {
        if (!$node->hasField($field) || $node->get($field)->isEmpty()) {
          continue;
        }
        $tid = (int) $node->get($field)->target_id;
        $tally[$tid] = ($tally[$tid] ?? 0) + 1;
      }
      $out[$axis] = $tally;
    }

    return $out;
  }
}
```

- [ ] **Step 5: Register both services**

Append to `web/modules/custom/keybolts_core/keybolts_core.services.yml`:

```yaml
  keybolts_core.product_query:
    class: Drupal\keybolts_core\Service\ProductQuery
    arguments: ['@entity_type.manager']

  keybolts_core.product_facets:
    class: Drupal\keybolts_core\Service\ProductFacetBuilder
    arguments: ['@entity_type.manager', '@keybolts_core.product_query']
```

- [ ] **Step 6: Run the tests**

Run: `ddev exec ./vendor/bin/phpunit -c web/core/phpunit.xml.dist web/modules/custom/keybolts_core/tests/src/Kernel/ProductQueryTest.php`
Expected: 6 tests PASS.

- [ ] **Step 7: Commit**

```bash
git add web/modules/custom/keybolts_core
git commit -m "feat(core): add product query and facet counting"
```

---

### Task 9: `keybolts_api` — envelope, serializer, product list endpoint

**Interfaces:**
- Consumes: `keybolts_core.product_query`, `keybolts_core.product_facets`.
- Produces: `GET /api/v1/products` returning the standard envelope; class `ApiEnvelope` with
  `public static function make(mixed $data, array $meta = [], array $facets = []): CacheableJsonResponse`;
  class `ProductSerializer` with `public function card(NodeInterface $n): array` and
  `public function detail(NodeInterface $n): array`.

**Files:**
- Create: `web/modules/custom/keybolts_api/keybolts_api.info.yml`
- Create: `web/modules/custom/keybolts_api/keybolts_api.routing.yml`
- Create: `web/modules/custom/keybolts_api/keybolts_api.services.yml`
- Create: `web/modules/custom/keybolts_api/src/ApiEnvelope.php`
- Create: `web/modules/custom/keybolts_api/src/Serializer/ProductSerializer.php`
- Create: `web/modules/custom/keybolts_api/src/Controller/ProductController.php`

- [ ] **Step 1: Create module files**

`keybolts_api.info.yml`:

```yaml
name: 'Keybolts API'
type: module
description: 'REST endpoints consumed by the Nuxt frontend.'
package: Keybolts
core_version_requirement: ^11
dependencies:
  - keybolts_core:keybolts_core
```

`keybolts_api.routing.yml`:

```yaml
keybolts_api.products:
  path: '/api/v1/products'
  defaults:
    _controller: '\Drupal\keybolts_api\Controller\ProductController::list'
  methods: [GET]
  requirements:
    _permission: 'access content'

keybolts_api.product_suggest:
  path: '/api/v1/products/suggest'
  defaults:
    _controller: '\Drupal\keybolts_api\Controller\ProductController::suggest'
  methods: [GET]
  requirements:
    _permission: 'access content'

keybolts_api.product_detail:
  path: '/api/v1/products/{slug}'
  defaults:
    _controller: '\Drupal\keybolts_api\Controller\ProductController::detail'
  methods: [GET]
  requirements:
    _permission: 'access content'
```

> Route order matters: `suggest` is declared before `{slug}` so `/api/v1/products/suggest` is not swallowed by the wildcard.

`keybolts_api.services.yml`:

```yaml
services:
  keybolts_api.product_serializer:
    class: Drupal\keybolts_api\Serializer\ProductSerializer
    arguments: ['@entity_type.manager', '@file_url_generator', '@path_alias.manager']
```

- [ ] **Step 2: Implement `ApiEnvelope`**

`web/modules/custom/keybolts_api/src/ApiEnvelope.php`:

```php
<?php

declare(strict_types=1);

namespace Drupal\keybolts_api;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheableJsonResponse;

/**
 * Builds the single response shape every endpoint returns.
 */
final class ApiEnvelope {

  /**
   * @param array $cache_tags
   *   Tags that invalidate this response, e.g. ['node_list:product'].
   */
  public static function make(
    mixed $data,
    array $meta = [],
    array $facets = [],
    array $cache_tags = ['node_list:product'],
    int $max_age = 600,
  ): CacheableJsonResponse {
    $response = new CacheableJsonResponse([
      'data' => $data,
      'meta' => $meta,
      'facets' => (object) $facets,
    ]);
    $cacheability = (new CacheableMetadata())
      ->setCacheTags($cache_tags)
      ->setCacheContexts(['url.query_args'])
      ->setCacheMaxAge($max_age);
    $response->addCacheableDependency($cacheability);
    return $response;
  }
}
```

- [ ] **Step 3: Implement `ProductSerializer`**

`web/modules/custom/keybolts_api/src/Serializer/ProductSerializer.php`:

```php
<?php

declare(strict_types=1);

namespace Drupal\keybolts_api\Serializer;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\node\NodeInterface;

/**
 * Converts product nodes into the shapes the frontend consumes.
 */
class ProductSerializer {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly FileUrlGeneratorInterface $fileUrlGenerator,
    private readonly AliasManagerInterface $aliasManager,
  ) {}

  /**
   * The shape used by product cards in listings and carousels.
   */
  public function card(NodeInterface $node): array {
    return [
      'id' => (int) $node->id(),
      'slug' => $this->slug($node),
      'name' => $node->label(),
      'model' => $this->str($node, 'field_product_code'),
      'family' => $this->str($node, 'field_family'),
      'badge' => $this->listLabel($node, 'field_badge'),
      'brand' => $this->term($node, 'field_brand'),
      'category' => $this->term($node, 'field_category'),
      'finish' => $this->term($node, 'field_finish'),
      'image' => $this->firstImage($node),
      'stockStatus' => $this->listLabel($node, 'field_stock_status'),
      'contactPrice' => (bool) $this->str($node, 'field_contact_price'),
    ];
  }

  /**
   * The card shape plus everything the detail page renders.
   */
  public function detail(NodeInterface $node): array {
    return $this->card($node) + [
      'shortDesc' => $this->str($node, 'field_short_desc'),
      'descHeading' => $this->str($node, 'field_desc_heading'),
      'description' => $node->hasField('field_description') && !$node->get('field_description')->isEmpty()
        ? $node->get('field_description')->processed
        : '',
      'highlights' => $this->multi($node, 'field_highlights'),
      'certification' => $this->multi($node, 'field_certification'),
      'warranty' => $this->str($node, 'field_warranty'),
      'doorThickness' => $this->str($node, 'field_door_thickness'),
      'origin' => $this->str($node, 'field_origin'),
      'sizeLabel' => $this->str($node, 'field_size_label'),
      'sizeNote' => $this->str($node, 'field_size_note'),
      'images' => $this->images($node),
    ];
  }

  private function str(NodeInterface $node, string $field): string {
    if (!$node->hasField($field) || $node->get($field)->isEmpty()) {
      return '';
    }
    return (string) $node->get($field)->value;
  }

  private function multi(NodeInterface $node, string $field): array {
    if (!$node->hasField($field)) {
      return [];
    }
    return array_map(
      static fn(array $item) => (string) ($item['value'] ?? ''),
      $node->get($field)->getValue()
    );
  }

  /**
   * The human label of a list field, e.g. "Còn hàng — giao 2–5 ngày".
   */
  private function listLabel(NodeInterface $node, string $field): ?string {
    if (!$node->hasField($field) || $node->get($field)->isEmpty()) {
      return NULL;
    }
    $key = $node->get($field)->value;
    $allowed = $node->get($field)->getFieldDefinition()
      ->getFieldStorageDefinition()->getSetting('allowed_values');
    return $allowed[$key] ?? (string) $key;
  }

  private function term(NodeInterface $node, string $field): ?array {
    if (!$node->hasField($field) || $node->get($field)->isEmpty()) {
      return NULL;
    }
    $term = $node->get($field)->entity;
    if (!$term) {
      return NULL;
    }
    $out = ['id' => (int) $term->id(), 'name' => $term->label()];
    if ($term->hasField('field_swatch') && !$term->get('field_swatch')->isEmpty()) {
      $out['swatch'] = (string) $term->get('field_swatch')->value;
    }
    return $out;
  }

  private function firstImage(NodeInterface $node): ?array {
    return $this->images($node)[0] ?? NULL;
  }

  private function images(NodeInterface $node): array {
    if (!$node->hasField('field_images')) {
      return [];
    }
    $out = [];
    foreach ($node->get('field_images') as $item) {
      $file = $item->entity;
      if (!$file) {
        continue;
      }
      $out[] = [
        'url' => $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri()),
        'alt' => (string) $item->alt,
      ];
    }
    return $out;
  }

  private function slug(NodeInterface $node): string {
    return ltrim($this->aliasManager->getAliasByPath('/node/' . $node->id()), '/');
  }
}
```

- [ ] **Step 4: Implement the list action**

`web/modules/custom/keybolts_api/src/Controller/ProductController.php` — start with `list` only; `detail` and `suggest` arrive in Tasks 10 and 11.

```php
<?php

declare(strict_types=1);

namespace Drupal\keybolts_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\keybolts_api\ApiEnvelope;
use Drupal\keybolts_api\Serializer\ProductSerializer;
use Drupal\keybolts_core\Service\ProductFacetBuilder;
use Drupal\keybolts_core\Service\ProductQuery;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Product endpoints.
 */
class ProductController extends ControllerBase {

  private const PER_PAGE = 12;

  public function __construct(
    private readonly ProductQuery $productQuery,
    private readonly ProductFacetBuilder $facetBuilder,
    private readonly ProductSerializer $serializer,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('keybolts_core.product_query'),
      $container->get('keybolts_core.product_facets'),
      $container->get('keybolts_api.product_serializer'),
    );
  }

  /**
   * GET /api/v1/products
   */
  public function list(Request $request) {
    $filters = array_filter([
      'brand' => $request->query->get('brand'),
      'category' => $request->query->get('category'),
      'finish' => $request->query->get('finish'),
    ]);
    $sort = (string) $request->query->get('sort', 'featured');
    $page = max(1, (int) $request->query->get('page', 1));

    $result = $this->productQuery->find($filters, $sort, $page, self::PER_PAGE);

    return ApiEnvelope::make(
      array_values(array_map(
        fn($node) => $this->serializer->card($node),
        $result['nodes']
      )),
      [
        'total' => $result['total'],
        'page' => $page,
        'limit' => self::PER_PAGE,
      ],
      $this->facetBuilder->counts($filters),
    );
  }
}
```

- [ ] **Step 5: Enable the module and call the endpoint**

```bash
ddev drush en -y keybolts_api
ddev drush cr
curl -s "https://vietlong.ddev.site/api/v1/products?page=1" | python3 -m json.tool | head -40
```
Expected: `data` holds 12 product objects, `meta.total` is `26`, `meta.limit` is `12`, and `facets` has `brand`, `category` and `finish` keys.

- [ ] **Step 6: Verify filtering and facets behave**

```bash
curl -s "https://vietlong.ddev.site/api/v1/products" | python3 -c "import json,sys; d=json.load(sys.stdin); print('total', d['meta']['total']); print('facet axes', sorted(d['facets']))"
curl -s "https://vietlong.ddev.site/api/v1/products?sort=az" | python3 -c "import json,sys; print([p['name'] for p in json.load(sys.stdin)['data']][:3])"
```
Expected: `total 26`; axes `['brand', 'category', 'finish']`; the `az` names are alphabetically ordered.

- [ ] **Step 7: Commit**

```bash
git add web/modules/custom/keybolts_api
git commit -m "feat(api): add product list endpoint with facets"
```

---

### Task 10: Product detail endpoint

**Interfaces:**
- Consumes: `keybolts_core.variant_matrix`, `ProductSerializer::detail()`.
- Produces: `GET /api/v1/products/{slug}` returning `data` with keys from `detail()` plus `variants`, `related`, `breadcrumb`, `jsonLd`.

**Files:**
- Modify: `web/modules/custom/keybolts_api/src/Controller/ProductController.php`

- [ ] **Step 1: Add the constructor dependency**

Add `VariantMatrixBuilder` to `ProductController`: a `private readonly VariantMatrixBuilder $variantMatrix` constructor parameter, `use Drupal\keybolts_core\Service\VariantMatrixBuilder;` at the top, and `$container->get('keybolts_core.variant_matrix')` as the fourth argument in `create()`.

- [ ] **Step 2: Add the `detail` action**

```php
  /**
   * GET /api/v1/products/{slug}
   *
   * The slug is the path alias without the `san-pham/` prefix.
   */
  public function detail(string $slug) {
    $path = \Drupal::service('path_alias.manager')
      ->getPathByAlias('/san-pham/' . $slug);
    if (!preg_match('#^/node/(\d+)$#', $path, $m)) {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }

    $node = $this->entityTypeManager()->getStorage('node')->load((int) $m[1]);
    if (!$node || $node->bundle() !== 'product' || !$node->isPublished()) {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }

    $data = $this->serializer->detail($node);
    $data['variants'] = $this->variantMatrix->build($node);
    $data['related'] = $this->relatedProducts($node);
    $data['breadcrumb'] = [
      ['label' => 'Trang chủ', 'url' => '/'],
      ['label' => 'Sản phẩm', 'url' => '/san-pham'],
      ...($data['category']
        ? [['label' => $data['category']['name'], 'url' => '/danh-muc/' . $data['category']['id']]]
        : []),
      ['label' => $node->label(), 'url' => '/' . $data['slug']],
    ];
    $data['jsonLd'] = $this->productJsonLd($data);

    return ApiEnvelope::make($data, [], [], ['node:' . $node->id(), 'node_list:product']);
  }

  /**
   * Up to 4 siblings in the same category, excluding the product itself.
   */
  private function relatedProducts($node): array {
    $category = $node->get('field_category')->target_id;
    if (!$category) {
      return [];
    }
    $ids = $this->entityTypeManager()->getStorage('node')->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'product')
      ->condition('status', 1)
      ->condition('field_category', $category)
      ->condition('nid', $node->id(), '<>')
      ->range(0, 4)
      ->execute();
    if (!$ids) {
      return [];
    }
    return array_values(array_map(
      fn($n) => $this->serializer->card($n),
      $this->entityTypeManager()->getStorage('node')->loadMultiple($ids)
    ));
  }

  /**
   * Schema.org Product. Price is always "contact for price", expressed as a
   * PriceSpecification without a value rather than a fabricated number.
   */
  private function productJsonLd(array $data): array {
    return [
      '@context' => 'https://schema.org',
      '@type' => 'Product',
      'name' => $data['name'],
      'sku' => $data['model'],
      'brand' => ['@type' => 'Brand', 'name' => $data['brand']['name'] ?? 'Keybolts'],
      'category' => $data['category']['name'] ?? '',
      'image' => array_column($data['images'], 'url'),
      'description' => $data['shortDesc'],
      'offers' => [
        '@type' => 'Offer',
        'availability' => $data['stockStatus'] === 'Hết hàng'
          ? 'https://schema.org/OutOfStock'
          : 'https://schema.org/InStock',
        'priceCurrency' => 'VND',
        'priceSpecification' => ['@type' => 'PriceSpecification', 'priceCurrency' => 'VND'],
      ],
    ];
  }
```

- [ ] **Step 3: Rebuild and fetch a real product**

```bash
ddev drush cr
SLUG=$(ddev drush php:eval "
\$n = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type'=>'product','field_product_code'=>'KB 1700-XL-PVD']);
\$f = reset(\$n);
echo ltrim(str_replace('/san-pham/','',\Drupal::service('path_alias.manager')->getAliasByPath('/node/'.\$f->id())),'/');
")
echo "slug=$SLUG"
curl -s "https://vietlong.ddev.site/api/v1/products/$SLUG" | python3 -m json.tool | head -50
```
Expected: `data.model` is `KB 1700-XL-PVD`, `data.variants.family` is `KB 1700`, `data.variants.sizes` lists the sibling sizes, `data.breadcrumb` has four entries.

- [ ] **Step 4: Verify a bad slug returns 404**

Run: `curl -s -o /dev/null -w "%{http_code}\n" "https://vietlong.ddev.site/api/v1/products/khong-ton-tai"`
Expected: `404`

- [ ] **Step 5: Commit**

```bash
git add web/modules/custom/keybolts_api
git commit -m "feat(api): add product detail endpoint with variant matrix and JSON-LD"
```

---

### Task 11: Suggest endpoint

**Interfaces:**
- Consumes: `field_search_text` populated in Task 6.
- Produces: `GET /api/v1/products/suggest?q=` returning up to 8 cards.

**Files:**
- Modify: `web/modules/custom/keybolts_api/src/Controller/ProductController.php`

- [ ] **Step 1: Add the `suggest` action**

```php
  /**
   * GET /api/v1/products/suggest?q=
   *
   * Matches against the denormalised diacritic-free field so that
   * `khoa van tay` finds `Khóa Vân Tay`.
   */
  public function suggest(Request $request) {
    $raw = trim((string) $request->query->get('q', ''));
    if ($raw === '') {
      return ApiEnvelope::make([], ['total' => 0, 'page' => 1, 'limit' => 8]);
    }

    $needle = \Drupal::service('keybolts_core.text_normalizer')->normalize($raw);
    $ids = $this->entityTypeManager()->getStorage('node')->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'product')
      ->condition('status', 1)
      ->condition('field_search_text', '%' . $needle . '%', 'LIKE')
      ->range(0, 8)
      ->execute();

    $nodes = $ids ? $this->entityTypeManager()->getStorage('node')->loadMultiple($ids) : [];

    return ApiEnvelope::make(
      array_values(array_map(fn($n) => $this->serializer->card($n), $nodes)),
      ['total' => count($nodes), 'page' => 1, 'limit' => 8],
    );
  }
```

- [ ] **Step 2: Verify diacritic-insensitive matching**

```bash
ddev drush cr
curl -s "https://vietlong.ddev.site/api/v1/products/suggest?q=khoa+dong" | python3 -c "import json,sys; d=json.load(sys.stdin); print(d['meta']['total'], [p['name'] for p in d['data']][:3])"
curl -s "https://vietlong.ddev.site/api/v1/products/suggest?q=Khóa+Đồng" | python3 -c "import json,sys; print(json.load(sys.stdin)['meta']['total'])"
```
Expected: both return the same non-zero total, and the names are the brass-lock products.

- [ ] **Step 3: Verify an empty query returns an empty list, not an error**

Run: `curl -s "https://vietlong.ddev.site/api/v1/products/suggest?q=" | python3 -m json.tool`
Expected: `data` is `[]`, `meta.total` is `0`, HTTP 200.

- [ ] **Step 4: Commit**

```bash
git add web/modules/custom/keybolts_api
git commit -m "feat(api): add diacritic-insensitive product suggest endpoint"
```

---

### Task 12: Homepage and menu endpoints

**Interfaces:**
- Produces: `GET /api/v1/homepage` returning `data` with `categories`, `featured` (keyed by `dong`/`cremone`/`hotel`/`phukien`), `brands`; `GET /api/v1/menu/{name}` returning a menu tree.

**Files:**
- Create: `web/modules/custom/keybolts_api/src/Controller/HomepageController.php`
- Modify: `web/modules/custom/keybolts_api/keybolts_api.routing.yml`

> Before writing this task's frontend consumer, re-read `design/Keybolts Homepage.html` — the homepage aggregates eleven sections and the prototype is the authority on which ones carry data.

- [ ] **Step 1: Add routes**

Append to `keybolts_api.routing.yml`:

```yaml
keybolts_api.homepage:
  path: '/api/v1/homepage'
  defaults:
    _controller: '\Drupal\keybolts_api\Controller\HomepageController::index'
  methods: [GET]
  requirements:
    _permission: 'access content'
```

- [ ] **Step 2: Implement the controller**

```php
<?php

declare(strict_types=1);

namespace Drupal\keybolts_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\keybolts_api\ApiEnvelope;
use Drupal\keybolts_api\Serializer\ProductSerializer;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Aggregates everything the homepage needs into one response.
 */
class HomepageController extends ControllerBase {

  private const FEATURED_GROUPS = ['dong', 'cremone', 'hotel', 'phukien'];

  public function __construct(
    private readonly ProductSerializer $serializer,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static($container->get('keybolts_api.product_serializer'));
  }

  /**
   * GET /api/v1/homepage
   */
  public function index() {
    return ApiEnvelope::make(
      [
        'categories' => $this->categories(),
        'brands' => $this->brands(),
        'featured' => $this->featured(),
      ],
      [],
      [],
      ['node_list:product', 'taxonomy_term_list'],
    );
  }

  /**
   * The eight catalogue categories, in weight order.
   */
  private function categories(): array {
    $terms = $this->entityTypeManager()->getStorage('taxonomy_term')
      ->loadTree('product_category', 0, NULL, TRUE);
    $out = [];
    foreach ($terms as $term) {
      $out[] = [
        'id' => (int) $term->id(),
        'name' => $term->label(),
        'number' => $term->hasField('field_number') ? (string) $term->get('field_number')->value : '',
        'desc' => $term->hasField('field_short_desc') ? (string) $term->get('field_short_desc')->value : '',
      ];
    }
    return $out;
  }

  private function brands(): array {
    $terms = $this->entityTypeManager()->getStorage('taxonomy_term')
      ->loadTree('brand', 0, NULL, TRUE);
    return array_values(array_map(static fn($t) => [
      'id' => (int) $t->id(),
      'name' => $t->label(),
      'tag' => $t->hasField('field_tag') ? (string) $t->get('field_tag')->value : '',
      'desc' => $t->getDescription(),
      'cta' => $t->hasField('field_cta_label') ? (string) $t->get('field_cta_label')->value : '',
    ], $terms));
  }

  /**
   * Four tabs of featured products, matching the prototype's tab groups.
   *
   * Falls back to the most recent products in a group's absence so the
   * homepage is never empty before an editor has curated the tabs.
   */
  private function featured(): array {
    $storage = $this->entityTypeManager()->getStorage('node');
    $out = [];
    foreach (self::FEATURED_GROUPS as $group) {
      $ids = $storage->getQuery()
        ->accessCheck(TRUE)
        ->condition('type', 'product')
        ->condition('status', 1)
        ->condition('field_featured_group', $group)
        ->range(0, 5)
        ->execute();
      if (!$ids) {
        $ids = $storage->getQuery()
          ->accessCheck(TRUE)
          ->condition('type', 'product')
          ->condition('status', 1)
          ->sort('created', 'DESC')
          ->range(0, 5)
          ->execute();
      }
      $out[$group] = array_values(array_map(
        fn($n) => $this->serializer->card($n),
        $storage->loadMultiple($ids)
      ));
    }
    return $out;
  }
}
```

- [ ] **Step 3: Verify the payload**

```bash
ddev drush cr
curl -s "https://vietlong.ddev.site/api/v1/homepage" | python3 -c "
import json,sys
d = json.load(sys.stdin)['data']
print('categories', len(d['categories']))
print('brands', [b['name'] for b in d['brands']])
print('featured groups', {k: len(v) for k, v in d['featured'].items()})
"
```
Expected: `categories 8`; brands `['KEYBOLTS', 'BALTICA']` in some order; each featured group has up to 5 entries.

- [ ] **Step 4: Commit**

```bash
git add web/modules/custom/keybolts_api
git commit -m "feat(api): add aggregated homepage endpoint"
```

---

### Task 13: Nuxt scaffold, Tailwind 4 and design tokens

**Interfaces:**
- Produces: a running Nuxt 4 SSR app at `http://localhost:3000`; `app/assets/css/tokens.css` exposing every design token as both a CSS custom property and a Tailwind utility namespace.

**Files:**
- Create: `frontend/` (scaffold), `frontend/nuxt.config.ts`, `frontend/app/assets/css/tokens.css`, `frontend/scripts/compute-oklch.mjs`

> **Re-read `design/Keybolts Homepage.html` before this task** and re-extract the `:root` custom properties. Token values below were read on 2026-07-31; if the design has changed, the design wins.

- [ ] **Step 1: Scaffold Nuxt**

```bash
cd frontend 2>/dev/null || npx nuxi@latest init frontend --packageManager npm --no-install --gitInit false
cd frontend && npm install
npm install tailwindcss @tailwindcss/vite
```

- [ ] **Step 2: Write the oklch resolver**

`frontend/scripts/compute-oklch.mjs` — the prototype uses `oklch(from var(--x) L C h)`, which Safari and older Android WebViews do not support. This precomputes static hex so the brand colours never degrade.

```js
#!/usr/bin/env node
/**
 * Resolves the prototype's relative-oklch palette into static hex values.
 *
 * Writes app/assets/css/_generated-palette.css, imported by tokens.css.
 */
import { writeFileSync, mkdirSync } from 'node:fs'
import { dirname, resolve } from 'node:path'
import { fileURLToPath } from 'node:url'

const OUT = resolve(dirname(fileURLToPath(import.meta.url)), '../app/assets/css/_generated-palette.css')

/** sRGB hex -> linear RGB */
const srgbToLinear = (c) => (c <= 0.04045 ? c / 12.92 : ((c + 0.055) / 1.055) ** 2.4)
const linearToSrgb = (c) => (c <= 0.0031308 ? 12.92 * c : 1.055 * c ** (1 / 2.4) - 0.055)

function hexToOklch(hex) {
  const [r, g, b] = [1, 3, 5].map((i) => srgbToLinear(parseInt(hex.slice(i, i + 2), 16) / 255))
  const l = Math.cbrt(0.4122214708 * r + 0.5363325363 * g + 0.0514459929 * b)
  const m = Math.cbrt(0.2119034982 * r + 0.6806995451 * g + 0.1073969566 * b)
  const s = Math.cbrt(0.0883024619 * r + 0.2817188376 * g + 0.6299787005 * b)
  const L = 0.2104542553 * l + 0.793617785 * m - 0.0040720468 * s
  const A = 1.9779984951 * l - 2.428592205 * m + 0.4505937099 * s
  const B = 0.0259040371 * l + 0.7827717662 * m - 0.808675766 * s
  return { L, C: Math.hypot(A, B), h: (Math.atan2(B, A) * 180) / Math.PI }
}

function oklchToHex(L, C, hDeg) {
  const h = (hDeg * Math.PI) / 180
  const A = C * Math.cos(h)
  const B = C * Math.sin(h)
  const l = (L + 0.3963377774 * A + 0.2158037573 * B) ** 3
  const m = (L - 0.1055613458 * A - 0.0638541728 * B) ** 3
  const s = (L - 0.0894841775 * A - 1.291485548 * B) ** 3
  const rgb = [
    4.0767416621 * l - 3.3077115913 * m + 0.2309699292 * s,
    -1.2684380046 * l + 2.6097574011 * m - 0.3413193965 * s,
    -0.0041960863 * l - 0.7034186147 * m + 1.707614701 * s,
  ]
  return (
    '#' +
    rgb
      .map((c) => Math.round(Math.min(1, Math.max(0, linearToSrgb(c))) * 255).toString(16).padStart(2, '0'))
      .join('')
  )
}

// Base colours and the L/C ramps, copied verbatim from the prototype.
const CHARCOAL = '#282d30'
const GOLD = '#f7e499'
const NEUTRAL = { 100: [0.97, 0.002], 200: [0.92, 0.003], 300: [0.82, 0.004], 400: [0.65, 0.005], 500: [0.48, 0.006], 600: [0.34, 0.006], 700: [0.26, 0.005] }
const GOLD_RAMP = { 100: [0.96, 0.03], 300: [0.82, 0.09] }

const charcoalHue = hexToOklch(CHARCOAL).h
const goldHue = hexToOklch(GOLD).h

const lines = ['/* GENERATED by scripts/compute-oklch.mjs — do not edit. */', ':root {']
for (const [step, [L, C]] of Object.entries(NEUTRAL)) {
  lines.push(`  --color-neutral-${step}: ${oklchToHex(L, C, charcoalHue)};`)
}
for (const [step, [L, C]] of Object.entries(GOLD_RAMP)) {
  lines.push(`  --color-gold-${step}: ${oklchToHex(L, C, goldHue)};`)
}
lines.push(`  --color-brass-500: ${oklchToHex(0.62, 0.11, 75)};`)
lines.push(`  --color-brass-700: ${oklchToHex(0.42, 0.09, 55)};`)
lines.push(`  --color-success-500: ${oklchToHex(0.6, 0.1, 145)};`)
lines.push(`  --color-warning-500: ${oklchToHex(0.75, 0.13, 75)};`)
lines.push(`  --color-danger-500: ${oklchToHex(0.55, 0.16, 25)};`)
lines.push('}')

mkdirSync(dirname(OUT), { recursive: true })
writeFileSync(OUT, lines.join('\n') + '\n')
console.log(`wrote ${OUT}`)
console.log(lines.filter((l) => l.includes('--color')).join('\n'))
```

- [ ] **Step 3: Run it and sanity-check the output**

```bash
cd frontend && node scripts/compute-oklch.mjs
```
Expected: seven `--color-neutral-*` values running light to dark, `--color-brass-500` around `#b0894f`, `--color-brass-700` a darker brown. Every value is a 6-digit hex — no `NaN`.

- [ ] **Step 4: Write `tokens.css`**

`frontend/app/assets/css/tokens.css` — this file *is* the Tailwind config. Every token below is copied from the prototype.

```css
@import "tailwindcss";
@import "./_generated-palette.css";

@theme {
  /* Brand */
  --color-charcoal-900: #282d30;
  --color-gold-200:     #f7e499;
  --color-white:        #ffffff;
  --color-surface-50:   #f8f8f8;
  --color-border-100:   #eeeeee;
  --color-ink-900:      #000000;

  /* Semantic — components use only these */
  --color-primary:      var(--color-charcoal-900);
  --color-on-primary:   var(--color-white);
  --color-background:   var(--color-white);
  --color-surface:      var(--color-surface-50);
  --color-border:       var(--color-border-100);
  --color-text:         var(--color-ink-900);
  --color-text-muted:   var(--color-neutral-500);
  --color-accent:       var(--color-gold-200);
  --color-accent-strong: var(--color-brass-500);
  --color-accent-ink:   var(--color-brass-700);
  --color-link:         var(--color-brass-700);

  /* Type */
  --font-sans: "Roboto", -apple-system, "Segoe UI", sans-serif;
  --text-eyebrow:    12px;
  --text-caption:    12px;
  --text-body:       14px;
  --text-heading:    16px;
  --text-display:    24px;
  --text-display-lg: 40px;
  --text-display-xl: 56px;

  /* Spacing — 3px base */
  --spacing: 3px;

  /* Breakpoints */
  --breakpoint-sm:  576px;
  --breakpoint-md:  768px;
  --breakpoint-lg:  992px;
  --breakpoint-xl:  1200px;
  --breakpoint-2xl: 1300px;

  /* Radius — pill buttons and chips */
  --radius-none: 0px;
  --radius-sm:   50px;
  --radius-full: 9999px;

  /* Elevation — flat by design; only overlays float */
  --shadow-floating: 0 8px 24px rgba(40, 45, 48, 0.16);

  /* Motion */
  --ease-standard: ease-in-out;
}

:root {
  --container-max: 1360px;
  --weight-light: 300;
  --weight-regular: 400;
  --weight-bold: 700;
  --duration-fast: 150ms;
  --duration-base: 200ms;
}

/* Progressive enhancement: wider gamut where relative colour is supported. */
@supports (color: oklch(from #fff l c h)) {
  :root {
    --color-neutral-500: oklch(from var(--color-charcoal-900) 0.48 0.006 h);
    --color-neutral-300: oklch(from var(--color-charcoal-900) 0.82 0.004 h);
  }
}

body {
  font-family: var(--font-sans);
  font-size: var(--text-body);
  color: var(--color-text);
  background: var(--color-background);
}
```

- [ ] **Step 5: Wire Tailwind and the token file into Nuxt**

`frontend/nuxt.config.ts`:

```ts
import tailwindcss from '@tailwindcss/vite'

export default defineNuxtConfig({
  compatibilityDate: '2026-07-31',
  ssr: true,
  css: ['~/assets/css/tokens.css'],
  vite: { plugins: [tailwindcss()] },
  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'https://vietlong.ddev.site/api/v1',
    },
  },
})
```

Add to `frontend/package.json` scripts: `"palette": "node scripts/compute-oklch.mjs"`, and make `dev` run it first: `"dev": "npm run palette && nuxt dev"`.

- [ ] **Step 6: Start the dev server and confirm tokens resolve**

```bash
cd frontend && npm run dev
```
Then in another shell:
```bash
curl -s http://localhost:3000/ | grep -c "<!DOCTYPE html>"
```
Expected: `1` — the page is server-rendered.

- [ ] **Step 7: Commit**

```bash
git add frontend
git commit -m "feat(frontend): scaffold Nuxt 4 with Tailwind 4 and design tokens"
```

---

### Task 14: API service layer, types and `useViewport`

**Interfaces:**
- Produces:
  - `app/types/product.ts` — `ProductCard`, `ProductDetail`, `VariantMatrix`, `Facets`, `ApiResponse<T>`
  - `app/services/http.ts` — `apiFetch<T>(path: string, query?: Record<string, unknown>): Promise<ApiResponse<T>>`
  - `app/services/products.ts` — `fetchProducts`, `fetchProduct`, `suggestProducts`
  - `app/composables/useViewport.ts` — `{ width, isMobile, isWide, utilWide }`
  - `app/utils/productFilterState.ts` — `toQuery(state)` / `fromQuery(query)`

**Files:**
- Create: the five files above
- Test: `frontend/test/productFilterState.spec.ts`

- [ ] **Step 1: Define the types**

`frontend/app/types/product.ts`:

```ts
export interface TermRef { id: number; name: string; swatch?: string }

export interface ProductCard {
  id: number
  slug: string
  name: string
  model: string
  family: string
  badge: string | null
  brand: TermRef | null
  category: TermRef | null
  finish: TermRef | null
  image: { url: string; alt: string } | null
  stockStatus: string | null
  contactPrice: boolean
}

export interface VariantOption {
  key: string
  label: string
  note?: string
  swatch?: string
  available: boolean
  slug: string | null
  code: string | null
}

export interface VariantMatrix {
  family: string
  sizes: VariantOption[]
  finishes: VariantOption[]
}

export interface ProductDetail extends ProductCard {
  shortDesc: string
  descHeading: string
  description: string
  highlights: string[]
  certification: string[]
  warranty: string
  doorThickness: string
  origin: string
  sizeLabel: string
  sizeNote: string
  images: { url: string; alt: string }[]
  variants: VariantMatrix
  related: ProductCard[]
  breadcrumb: { label: string; url: string }[]
  jsonLd: Record<string, unknown>
}

export type Facets = Record<string, Record<string, number>>

export interface ApiResponse<T> {
  data: T
  meta: { total: number; page: number; limit: number }
  facets: Facets
}
```

- [ ] **Step 2: Write the HTTP layer**

`frontend/app/services/http.ts` — the single place that knows the API exists. Components never call `$fetch`.

```ts
import type { ApiResponse } from '~/types/product'

export async function apiFetch<T>(
  path: string,
  query: Record<string, unknown> = {},
): Promise<ApiResponse<T>> {
  const base = useRuntimeConfig().public.apiBase
  const clean = Object.fromEntries(
    Object.entries(query).filter(([, v]) => v !== undefined && v !== null && v !== ''),
  )
  return await $fetch<ApiResponse<T>>(`${base}${path}`, { query: clean })
}
```

`frontend/app/services/products.ts`:

```ts
import type { ProductCard, ProductDetail } from '~/types/product'
import { apiFetch } from './http'

export interface ProductFilters {
  brand?: string
  category?: string
  finish?: string
  sort?: string
  page?: number
}

export const fetchProducts = (filters: ProductFilters = {}) =>
  apiFetch<ProductCard[]>('/products', filters as Record<string, unknown>)

export const fetchProduct = (slug: string) =>
  apiFetch<ProductDetail>(`/products/${slug}`)

export const suggestProducts = (q: string) =>
  apiFetch<ProductCard[]>('/products/suggest', { q })
```

- [ ] **Step 3: Write `useViewport`**

`frontend/app/composables/useViewport.ts` — one source of truth for the breakpoint behaviour the prototype encodes, rather than media queries scattered across components.

```ts
export function useViewport() {
  const width = ref(1440)

  const update = () => { width.value = window.innerWidth }

  onMounted(() => {
    update()
    window.addEventListener('resize', update, { passive: true })
  })
  onUnmounted(() => window.removeEventListener('resize', update))

  return {
    width,
    // Thresholds mirror the prototype's own state flags.
    isMobile: computed(() => width.value < 992),
    isWide: computed(() => width.value >= 1300),
    utilWide: computed(() => width.value >= 1200),
  }
}
```

- [ ] **Step 4: Write the failing filter-state test**

`frontend/test/productFilterState.spec.ts`:

```ts
import { describe, expect, it } from 'vitest'
import { fromQuery, toQuery } from '../app/utils/productFilterState'

describe('productFilterState', () => {
  it('omits empty values from the query string', () => {
    expect(toQuery({ brand: '', category: '3', finish: '', sort: 'featured', page: 1 }))
      .toEqual({ category: '3' })
  })

  it('keeps non-default sort and page', () => {
    expect(toQuery({ brand: '', category: '', finish: '', sort: 'az', page: 2 }))
      .toEqual({ sort: 'az', page: '2' })
  })

  it('round-trips through the query string', () => {
    const state = { brand: '1', category: '3', finish: '7', sort: 'za', page: 4 }
    expect(fromQuery(toQuery(state))).toEqual(state)
  })

  it('falls back to defaults for a missing or malformed query', () => {
    expect(fromQuery({})).toEqual({ brand: '', category: '', finish: '', sort: 'featured', page: 1 })
    expect(fromQuery({ page: 'abc' }).page).toBe(1)
  })

  it('rejects an unknown sort value', () => {
    expect(fromQuery({ sort: 'price' }).sort).toBe('featured')
  })
})
```

- [ ] **Step 5: Run the test to verify it fails**

```bash
cd frontend && npm install -D vitest && npx vitest run test/productFilterState.spec.ts
```
Expected: FAIL — cannot resolve `../app/utils/productFilterState`.

- [ ] **Step 6: Implement the filter state helper**

`frontend/app/utils/productFilterState.ts`:

```ts
export interface FilterState {
  brand: string
  category: string
  finish: string
  sort: string
  page: number
}

const SORTS = ['featured', 'az', 'za', 'cat'] as const
const DEFAULTS: FilterState = { brand: '', category: '', finish: '', sort: 'featured', page: 1 }

/** State -> query object, omitting anything at its default. */
export function toQuery(state: FilterState): Record<string, string> {
  const q: Record<string, string> = {}
  if (state.brand) q.brand = state.brand
  if (state.category) q.category = state.category
  if (state.finish) q.finish = state.finish
  if (state.sort && state.sort !== DEFAULTS.sort) q.sort = state.sort
  if (state.page > 1) q.page = String(state.page)
  return q
}

/** Query object -> state, tolerating anything the user pastes into the URL. */
export function fromQuery(query: Record<string, unknown>): FilterState {
  const str = (v: unknown) => (typeof v === 'string' ? v : '')
  const sort = str(query.sort)
  const page = Number.parseInt(str(query.page), 10)
  return {
    brand: str(query.brand),
    category: str(query.category),
    finish: str(query.finish),
    sort: (SORTS as readonly string[]).includes(sort) ? sort : DEFAULTS.sort,
    page: Number.isFinite(page) && page > 0 ? page : 1,
  }
}
```

- [ ] **Step 7: Run the tests**

Run: `cd frontend && npx vitest run test/productFilterState.spec.ts`
Expected: 5 tests PASS.

- [ ] **Step 8: Commit**

```bash
git add frontend
git commit -m "feat(frontend): add API service layer, types, viewport and filter state"
```

---

### Task 15: Layout shell

**Re-read `design/Keybolts Homepage.html` before starting.** The header, mega menu, mobile panel, search overlay, sticky CTA and footer are shared by every page; their markup and copy come from the prototype verbatim.

**Interfaces:**
- Produces: `app/layouts/default.vue` wrapping `<TopBar>`, `<MainBar>`, `<MegaMenu>`, `<MobileNavPanel>`, `<SearchOverlay>`, `<slot />`, `<SiteFooter>`, `<StickyMobileCta>`.

**Files:**
- Create: `frontend/app/layouts/default.vue`
- Create: `frontend/app/components/layout/TopBar.vue`, `MainBar.vue`, `MegaMenu.vue`, `MobileNavPanel.vue`, `SearchOverlay.vue`, `StickyMobileCta.vue`, `SiteFooter.vue`, `Breadcrumb.vue`
- Create: `frontend/app/composables/useSiteChrome.ts` (shared open/close state for menu, mega menu and search)

- [ ] **Step 1: Extract the header and footer markup from the prototype**

```bash
python3 - <<'PY'
import json, pathlib, re
src = pathlib.Path("design/Keybolts Homepage.html").read_text(encoding="utf-8")
tpl = json.loads(re.search(r'<script type="__bundler/template">\s*(".*?")\s*</script>', src, re.S).group(1))
pathlib.Path("/tmp/kb-homepage.html").write_text(tpl, encoding="utf-8")
print("header/footer markup written to /tmp/kb-homepage.html")
PY
```

Read `/tmp/kb-homepage.html` and copy the exact strings: top-bar tagline, `1900 9018`, nav labels, mega-menu columns, footer columns, legal line.

- [ ] **Step 2: Implement `useSiteChrome`**

`frontend/app/composables/useSiteChrome.ts`:

```ts
/** Shared open/close state for the header's three overlapping surfaces. */
export const useSiteChrome = () => {
  const mobileNavOpen = useState('chrome:mobileNav', () => false)
  const megaMenuOpen = useState('chrome:megaMenu', () => false)
  const searchOpen = useState('chrome:search', () => false)

  // Only one surface may be open at a time; the sticky CTA hides behind any.
  const closeAll = () => {
    mobileNavOpen.value = false
    megaMenuOpen.value = false
    searchOpen.value = false
  }
  const openSearch = () => { closeAll(); searchOpen.value = true }
  const toggleMobileNav = () => {
    const next = !mobileNavOpen.value
    closeAll()
    mobileNavOpen.value = next
  }

  const anyOpen = computed(() => mobileNavOpen.value || megaMenuOpen.value || searchOpen.value)

  return { mobileNavOpen, megaMenuOpen, searchOpen, anyOpen, closeAll, openSearch, toggleMobileNav }
}
```

- [ ] **Step 3: Build the eight layout components**

Each must match the prototype at 375 / 768 / 1440px and use only semantic tokens. Required behaviours:
- `TopBar` — tagline plus CE-CFF and warranty items hidden below `utilWide`; hotline always visible.
- `MainBar` — logo with tagline shown only when `isWide`; six nav items (Sản phẩm · Giới thiệu · Dự án · Tin tức · Đại lý · Liên hệ); search and `Nhận tư vấn` CTA.
- `MegaMenu` — opens on hover over Sản phẩm; three columns (Khóa cửa, Phụ kiện, Bộ sưu tập đồng); closes on mouse leave and on `Escape`.
- `MobileNavPanel` — slides down below the header, flat list, full-width `Nhận tư vấn` button.
- `SearchOverlay` — full-screen; autofocus; debounce 300ms calling `suggestProducts`; heading is `Gợi ý phổ biến` when empty and `Kết quả (n)` when searching; empty state `Không tìm thấy sản phẩm phù hợp.` plus `Gọi 1900 9018 để được tư vấn →`; closes on `Escape` and backdrop click; `Enter` navigates to `/tim-kiem?q=`.
- `StickyMobileCta` — visible only when `isMobile` and `!anyOpen`; three actions Gọi / Zalo / Nhận tư vấn.
- `SiteFooter` — four columns on desktop, stacked on mobile, exact copy from the prototype.
- `Breadcrumb` — `Trang chủ / …` with a `BreadcrumbList` JSON-LD script.

- [ ] **Step 4: Compose the layout**

`frontend/app/layouts/default.vue` renders the chrome around `<slot />` and locks body scroll while `anyOpen`.

- [ ] **Step 5: Verify server-rendered chrome**

```bash
curl -s http://localhost:3000/ | grep -o "1900 9018" | head -1
curl -s http://localhost:3000/ | grep -c "Khóa cửa & phụ kiện nhập khẩu"
```
Expected: `1900 9018` appears; the tagline count is at least `1` — proving the chrome is in the SSR output, not injected by client JS.

- [ ] **Step 6: Check keyboard access**

Load `http://localhost:3000/`, press Tab to the search button, press Enter, confirm the overlay opens and focuses the input, press Escape and confirm it closes and focus returns to the trigger.

- [ ] **Step 7: Commit**

```bash
git add frontend/app
git commit -m "feat(frontend): add shared layout chrome, mega menu and search overlay"
```

---

### Task 16: Product listing page

**Re-read `design/Keybolts Products.html` before starting this task — the user changed this page's design after this plan was written. Diff the current prototype against the behaviours below and update them before writing code.**

**Interfaces:**
- Consumes: `fetchProducts`, `fromQuery`/`toQuery`, `ProductCard`, `Facets`.
- Produces: `/san-pham` with filtering, facet counts, sorting and pagination driven entirely by the URL.

**Files:**
- Create: `frontend/app/pages/san-pham/index.vue`
- Create: `frontend/app/components/product/ProductCard.vue`, `FilterSidebar.vue`, `FinishSwatchGroup.vue`, `SortSelect.vue`, `Pagination.vue`, `EmptyState.vue`, `BrandChooser.vue`

- [ ] **Step 1: Re-extract the current design**

```bash
python3 - <<'PY'
import json, pathlib, re
src = pathlib.Path("design/Keybolts Products.html").read_text(encoding="utf-8")
tpl = json.loads(re.search(r'<script type="__bundler/template">\s*(".*?")\s*</script>', src, re.S).group(1))
pathlib.Path("/tmp/kb-products.html").write_text(tpl, encoding="utf-8")
logic = re.search(r'<script type="text/x-dc".*?>(.*?)</script>', src, re.S)
pathlib.Path("/tmp/kb-products-logic.js").write_text(logic.group(1) if logic else "", encoding="utf-8")
print("markup -> /tmp/kb-products.html; logic -> /tmp/kb-products-logic.js")
PY
```

Read both files. Confirm — or correct — these assumptions before coding: 12 items per page; sort options Nổi bật / Tên A → Z / Tên Z → A / Theo danh mục; sidebar axes Thương hiệu, Danh mục sản phẩm, Hoàn thiện; brand chooser cards above the chip row; contractor CTA band at the foot.

- [ ] **Step 2: Build the page with URL-driven state**

The page reads `route.query` through `fromQuery`, fetches via `useAsyncData` keyed on the query so SSR and client stay in sync, and writes changes back with `router.push`. Filter state never lives only in component memory — that is what makes share, back and forward work.

```vue
<script setup lang="ts">
import { fetchProducts } from '~/services/products'
import { fromQuery, toQuery, type FilterState } from '~/utils/productFilterState'

const route = useRoute()
const router = useRouter()

const state = computed<FilterState>(() => fromQuery(route.query as Record<string, unknown>))

const { data, pending } = await useAsyncData(
  () => `products:${JSON.stringify(toQuery(state.value))}`,
  () => fetchProducts({ ...toQuery(state.value), page: state.value.page }),
  { watch: [state] },
)

function update(patch: Partial<FilterState>) {
  // Any filter change resets to page 1 — staying on page 4 of a narrower
  // result set would strand the user on an empty page.
  const next = { ...state.value, ...patch }
  if (!('page' in patch)) next.page = 1
  router.push({ query: toQuery(next) })
}
</script>
```

- [ ] **Step 3: Render facet counts beside each option**

`FilterSidebar` receives `facets` from the response and shows the count next to every brand, category and finish. Because the API counts each axis with its own filter removed, the user always sees what switching to another value would yield.

- [ ] **Step 4: Verify SSR and URL state**

```bash
curl -s "http://localhost:3000/san-pham" | grep -c "Xem chi tiết"
curl -s "http://localhost:3000/san-pham?sort=az" | grep -o 'data-product-name="[^"]*"' | head -3
```
Expected: the first command returns `12` (a full page of cards rendered server-side); the second shows alphabetically ordered names.

- [ ] **Step 5: Verify pagination and empty state in the browser**

Load `/san-pham`, pick a brand, confirm the URL gains `?brand=…` and counts update; go to page 2, press Back, confirm page 1 returns; combine filters until nothing matches and confirm `Không có sản phẩm phù hợp` with a working `Xóa bộ lọc`.

- [ ] **Step 6: Commit**

```bash
git add frontend/app
git commit -m "feat(frontend): add product listing with facets, sorting and pagination"
```

---

### Task 17: Product detail page

**Re-read `design/Keybolts Product Detail.html` before starting.**

**Interfaces:**
- Consumes: `fetchProduct`, `ProductDetail`, `VariantMatrix`.
- Produces: `/san-pham/[slug]` with gallery, variant selectors, tabs and JSON-LD.

**Files:**
- Create: `frontend/app/pages/san-pham/[slug].vue`
- Create: `frontend/app/components/product/Gallery.vue`, `VariantSelector.vue`, `TabGroup.vue`, `SpecTable.vue`, `FaqAccordion.vue`, `AssuranceList.vue`

- [ ] **Step 1: Build the page**

```vue
<script setup lang="ts">
import { fetchProduct } from '~/services/products'

const route = useRoute()
const slug = computed(() => String(route.params.slug))

const { data } = await useAsyncData(
  () => `product:${slug.value}`,
  () => fetchProduct(slug.value),
)

const product = computed(() => data.value?.data)

if (!product.value) {
  throw createError({ statusCode: 404, statusMessage: 'Không tìm thấy sản phẩm' })
}

useSeoMeta({
  title: () => `${product.value?.name} ${product.value?.model} | Keybolts`,
  description: () => product.value?.shortDesc,
  ogImage: () => product.value?.image?.url,
})

useHead({
  script: [{ type: 'application/ld+json', innerHTML: () => JSON.stringify(product.value?.jsonLd) }],
})
</script>
```

- [ ] **Step 2: Implement `VariantSelector`**

Renders finish swatches and size options from `product.variants`. An option with `available: false` is shown disabled with no link — never a link to nowhere. An available option is a `<NuxtLink>` to `/${option.slug}`, so switching variant is real navigation to a real indexable URL.

```vue
<script setup lang="ts">
import type { VariantOption } from '~/types/product'
defineProps<{ options: VariantOption[]; currentKey: string; kind: 'finish' | 'size' }>()
</script>
```

- [ ] **Step 3: Verify variant navigation works end to end**

Load a `KB 1700` product, click a different size, confirm the URL changes to the sibling's slug, the displayed model code updates, and Back returns to the previous variant.

- [ ] **Step 4: Verify SSR includes the content and the schema**

```bash
SLUG=$(curl -s "http://localhost:3000/api-probe" >/dev/null 2>&1; curl -s "https://vietlong.ddev.site/api/v1/products?page=1" | python3 -c "import json,sys; print(json.load(sys.stdin)['data'][0]['slug'])")
curl -s "http://localhost:3000/$SLUG" | grep -c 'application/ld+json'
curl -s "http://localhost:3000/$SLUG" | grep -c 'Thông số kỹ thuật'
```
Expected: both counts are at least `1` — the schema and the tab content are in the server-rendered HTML.

- [ ] **Step 5: Commit**

```bash
git add frontend/app
git commit -m "feat(frontend): add product detail page with variant navigation"
```

---

### Task 18: Homepage

**Re-read `design/Keybolts Homepage.html` before starting.**

**Interfaces:**
- Consumes: `/api/v1/homepage`.
- Produces: `/` rendering the eleven sections in the prototype's order.

**Files:**
- Create: `frontend/app/pages/index.vue`
- Create: `frontend/app/services/homepage.ts`
- Create: `frontend/app/components/home/Hero.vue`, `StatStrip.vue`, `UspStrip.vue`, `CategoryGrid.vue`, `FeaturedTabs.vue`, `SolutionGrid.vue`, `TechBlock.vue`, `ProjectGrid.vue`, `ArticleGrid.vue`, `ConsultForm.vue`, `BranchList.vue`

- [ ] **Step 1: Add the homepage service**

`frontend/app/services/homepage.ts`:

```ts
import { apiFetch } from './http'
import type { ProductCard } from '~/types/product'

export interface HomepagePayload {
  categories: { id: number; name: string; number: string; desc: string }[]
  brands: { id: number; name: string; tag: string; desc: string; cta: string }[]
  featured: Record<string, ProductCard[]>
}

export const fetchHomepage = () => apiFetch<HomepagePayload>('/homepage')
```

- [ ] **Step 2: Build the sections**

Order and content per the prototype: static split Hero (charcoal background, radial gold wash, gradient headline, two CTAs — **not** a slider); stat strip; four-item USP strip; eight numbered category cards; `FeaturedTabs` with four tabs; four solution cards linking to filtered `/san-pham`; smart-lock tech block; three project cards; four article cards; consultation form section; branch list.

`Hero` sets `fetchpriority="high"` on its image — it is the LCP element.

- [ ] **Step 3: Verify the homepage renders server-side with real data**

```bash
curl -s http://localhost:3000/ | grep -c "Khóa cửa"
curl -s http://localhost:3000/ | grep -c "Khám phá sản phẩm"
```
Expected: both at least `1`.

- [ ] **Step 4: Verify the featured tabs switch without a network round trip**

Load `/`, click through the four tabs, confirm the product grid changes instantly — all four groups arrive in the single `/homepage` response.

- [ ] **Step 5: Commit**

```bash
git add frontend/app
git commit -m "feat(frontend): add homepage with eleven sections"
```

---

### Task 19: Category and brand routes, SEO, final verification

**Interfaces:**
- Produces: `/danh-muc/[slug]` and `/thuong-hieu/[slug]` reusing the listing, each with its own H1 and canonical.

**Files:**
- Create: `frontend/app/pages/danh-muc/[slug].vue`, `frontend/app/pages/thuong-hieu/[slug].vue`
- Create: `frontend/app/components/product/ProductListing.vue` (extracted from Task 16's page so all three routes share it)

- [ ] **Step 1: Extract the shared listing component**

Move the body of `pages/san-pham/index.vue` into `components/product/ProductListing.vue`, accepting props `{ title: string; description?: string; lockedFilter?: { axis: 'brand' | 'category'; value: string } }`. When `lockedFilter` is set the axis is pre-applied and hidden from the sidebar. `pages/san-pham/index.vue` becomes a thin wrapper.

- [ ] **Step 2: Add the two routes**

Each loads its term, renders `<ProductListing>` with the term name as H1 and the term description below it, and sets a self-referencing canonical so filter query parameters never split ranking signals:

```ts
useHead({
  link: [{ rel: 'canonical', href: `https://keybolts.com.vn${route.path}` }],
})
```

- [ ] **Step 3: Verify all three listing routes render**

```bash
for p in /san-pham "/danh-muc/1" "/thuong-hieu/1"; do
  printf "%s -> " "$p"
  curl -s -o /dev/null -w "%{http_code}\n" "http://localhost:3000$p"
done
```
Expected: three `200`s.

- [ ] **Step 4: Run every test**

```bash
ddev exec ./vendor/bin/phpunit -c web/core/phpunit.xml.dist web/modules/custom/keybolts_core/tests
cd frontend && npx vitest run
```
Expected: all PHPUnit tests pass (four test classes); all Vitest tests pass.

- [ ] **Step 5: Confirm no unsupported colour syntax reaches the browser**

```bash
cd frontend && npm run build
grep -r "oklch(from" .output/public/_nuxt/*.css | grep -v "@supports" | wc -l
```
Expected: `0` outside a `@supports` block.

- [ ] **Step 6: Confirm no image is hotlinked from the old site**

```bash
curl -s http://localhost:3000/san-pham | grep -c "keybolts.com.vn/sites/default/files"
```
Expected: `0` — every image is served from the local Drupal file system.

- [ ] **Step 7: Walk the acceptance list**

Check each box in section 8 of `docs/superpowers/specs/2026-07-31-keybolts-product-vertical-slice-design.md`. Compare `/`, `/san-pham` and a detail page side by side with the prototypes at 375 / 768 / 1440px.

- [ ] **Step 8: Commit**

```bash
git add frontend/app
git commit -m "feat(frontend): add category and brand listing routes with canonicals"
```

---

## Self-Review Notes

**Spec coverage:** every section of the design spec maps to a task — repo structure (Tasks 1, 3, 9, 13); taxonomies and content type (4); variant rule (7, 17); contrib modules (2); the five endpoints (9–12); Tailwind `@theme` (13); oklch resolution (13, 19); `useViewport` (14); the five routes (16–19); seed and image optimisation (5); the three test layers (3, 6, 7, 8, 14, 19); acceptance list (19).

**Known gap, deliberate:** `GET /api/v1/menu/{name}` is listed in the spec but no task implements it. The layout in Task 15 takes its six nav items from the prototype, where they are static. Building a menu endpoint before any editor needs to change the menu would be speculative. Add it when a second consumer or an editing requirement appears.

**Type consistency:** `ProductCard` / `ProductDetail` / `VariantMatrix` / `VariantOption` / `FilterState` are defined once in Task 14 and referenced unchanged afterwards. `ApiEnvelope::make()`, `ProductSerializer::card()` / `detail()`, `VariantMatrixBuilder::build()`, `ProductQuery::find()` / `baseQuery()` and `ProductFacetBuilder::counts()` keep the same signatures across all tasks that use them.
