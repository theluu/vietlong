# Keybolts Static Pages Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the Giới thiệu, Đại lý, Liên hệ and Chính sách pages plus a 404, with every piece of copy editable in Drupal and a working lead-capture form.

**Architecture:** Drupal stays API-only. Four singleton content types (one node each) hold the page copy; a shared `branch` content type holds showroom data used by four pages; a `contact_submission` content entity stores form posts. Nuxt renders each page server-side from `GET /api/v1/page/{key}`.

**Tech Stack:** Drupal 11 (PHP 8.4), Paragraphs, field_group, Drupal core `flood` service, Nuxt 4 SSR, Tailwind 4, Vitest, PHPUnit kernel tests.

## Global Constraints

- Design files in `design/` are the authority. Re-read the relevant one immediately before building each page. A token appears several times per file — **take the last occurrence, never the first**.
- Vietnamese copy is transcribed verbatim from the prototype, including `–` en dashes and `·` separators. Never re-type from memory.
- Components use semantic tokens only (`text-text-muted`, `bg-surface`, …), never raw hex.
- **Tailwind arbitrary values must not contain nested parentheses.** `grid-cols-[repeat(auto-fill,minmax(230px,1fr))]` silently generates nothing. Add a `kb-*` class in `@layer components` in `app/assets/css/tokens.css` instead.
- Adding new `.vue` files can leave the dev server's Tailwind scan stale. Restart `npm run dev` if a new utility does not apply.
- The Nuxt dev server runs on **port 3100 bound to 0.0.0.0** (see `docs/drupal-environment-setup.md`). Do not change it — nginx proxies `vietlong.ddev.site` to it.
- Verify by **content**, never by status code alone.
- Kernel tests run with: `ddev exec "cd /var/www/html && SIMPLETEST_DB=mysql://db:db@db/db vendor/bin/phpunit -c web/core/phpunit.xml.dist web/modules/custom --no-coverage"`
- Frontend tests: `cd frontend && npm test`
- Export config after every content-model change: `ddev drush cex -y`, and commit `config/sync`.
- Never commit screenshots or other binaries.

## File Structure

**Backend**

| File | Responsibility |
|---|---|
| `scripts/setup/install_page_model.php` | Creates `branch` + 4 singleton types + paragraph types. Idempotent. |
| `scripts/setup/install_page_displays.php` | Form/view displays + field_group tabs for everything above. |
| `web/modules/custom/keybolts_core/src/Entity/ContactSubmission.php` | Content entity storing form posts. |
| `web/modules/custom/keybolts_api/src/Serializer/PageSerializer.php` | Node → page payload, one method per page type. |
| `web/modules/custom/keybolts_api/src/Serializer/BranchSerializer.php` | Branch node → array. |
| `web/modules/custom/keybolts_api/src/Controller/PageController.php` | `GET /api/v1/page/{key}`, `GET /api/v1/branches`. |
| `web/modules/custom/keybolts_api/src/Controller/ContactController.php` | `POST /api/v1/contact`. |

**Frontend**

| File | Responsibility |
|---|---|
| `frontend/app/services/pages.ts` | `fetchPage(key)`, `fetchBranches()`, `submitLead(payload)`. |
| `frontend/app/types/page.ts` | Payload types for all four pages + branch. |
| `frontend/app/utils/leadForm.ts` | Pure validation/normalisation. Unit-tested. |
| `frontend/app/components/page/*.vue` | One component per design block. |
| `frontend/app/components/page/LeadForm.vue` | Shared by Liên hệ, Đại lý, Trang chủ. |
| `frontend/app/pages/{gioi-thieu,dai-ly,lien-he,chinh-sach}.vue` | The four routes. |
| `frontend/app/error.vue` | 404 and 500. |

---

### Task 1: `branch` content type and the shared showroom data

**Interfaces:**
- Produces: content type `branch` with fields `field_tag`, `field_address`, `field_phone_display`, `field_phone_tel`, `field_map_url`, `field_sort_order`; five seeded nodes.

**Files:**
- Create: `scripts/setup/install_page_model.php`
- Create: `scripts/seed/seed_branches.php`

- [ ] **Step 1: Write the model script**

`scripts/setup/install_page_model.php` — reuse the helper style from `scripts/setup/install_product_model.php`, which already has `kb_field()`.

```php
<?php

/**
 * @file
 * Creates the content model for the static pages. Safe to run repeatedly.
 *
 * Run: ddev drush php:script scripts/setup/install_page_model.php
 */

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;

/**
 * Creates a node type when missing.
 */
function kbp_node_type(string $id, string $label): void {
  if (!NodeType::load($id)) {
    NodeType::create(['type' => $id, 'name' => $label, 'new_revision' => TRUE])->save();
    echo "node type: {$id}\n";
  }
}

/**
 * Creates a field storage + instance when missing.
 */
function kbp_field(
  string $entity_type,
  string $bundle,
  string $name,
  string $type,
  string $label,
  int $cardinality = 1,
  array $settings = [],
  array $instance = [],
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
      'settings' => $instance,
    ])->save();
    echo "  field: {$bundle}.{$name}\n";
  }
}

kbp_node_type('branch', 'Cơ sở / Showroom');

kbp_field('node', 'branch', 'field_tag', 'string', 'Nhãn (Bán buôn, Cơ sở 1…)');
kbp_field('node', 'branch', 'field_address', 'string_long', 'Địa chỉ');
kbp_field('node', 'branch', 'field_phone_display', 'string', 'Điện thoại (hiển thị)');
kbp_field('node', 'branch', 'field_phone_tel', 'string', 'Điện thoại (số gọi)');
kbp_field('node', 'branch', 'field_map_url', 'link', 'Link chỉ đường');
kbp_field('node', 'branch', 'field_sort_order', 'integer', 'Thứ tự');

echo "Done.\n";
```

- [ ] **Step 2: Run it and confirm the fields exist**

```bash
ddev drush php:script scripts/setup/install_page_model.php
ddev drush php:eval '$f=\Drupal::service("entity_field.manager")->getFieldDefinitions("node","branch"); print implode(", ", array_filter(array_keys($f), fn($n)=>str_starts_with($n,"field_")));'
```
Expected: `field_address, field_map_url, field_phone_display, field_phone_tel, field_sort_order, field_tag`

- [ ] **Step 3: Run it a second time to prove idempotency**

```bash
ddev drush php:script scripts/setup/install_page_model.php
```
Expected: prints only `Done.` — no `node type:` or `field:` lines.

- [ ] **Step 4: Write the seed script**

`scripts/seed/seed_branches.php` — copy taken verbatim from `BRANCHES` in `design/Keybolts Dealers.html`.

> Transcribe from **`BRANCHES` in the Dealers prototype**, not from the homepage's older `LOCATIONS` array. The two disagree: Dealers capitalises `Đại Lộ` and `Đường`, the homepage does not, and Dealers is the newer file. Verify the stored values against the design after seeding rather than trusting the script's own output.

```php
<?php

/**
 * @file
 * Seeds the five Keybolts branches. Safe to run repeatedly — matches on title.
 *
 * Run: ddev drush php:script scripts/seed/seed_branches.php
 */

use Drupal\node\Entity\Node;

const KB_BRANCHES = [
  ['Văn phòng bán buôn', 'Bán buôn', 'Khu phố Lê Hồng Phong, P. Đông Ngàn, TP. Từ Sơn, Bắc Ninh', '0912.411.309', '0912411309', 1],
  ['Showroom Từ Sơn', 'Cơ sở 1', '217-219 Trần Phú, P. Đông Ngàn, TP. Từ Sơn, Bắc Ninh', '0968.689.112', '0968689112', 2],
  ['Kho Võ Cường', 'Cơ sở 2', 'Cụm CN Võ Cường, P. Võ Cường, TP. Bắc Ninh', '0981.255.215', '0981255215', 3],
  ['Showroom Việt Trì', 'Cơ sở 3', '1308 Đại Lộ Hùng Vương, P. Tiên Cát, TP. Việt Trì, Phú Thọ', '0984.84.6655', '0984846655', 4],
  ['Showroom Vĩnh Yên', 'Cơ sở 4', '531 Đường Mê Linh, P. Khai Quang, TP. Vĩnh Yên, Vĩnh Phúc', '0984.84.6622', '0984846622', 5],
];

$storage = \Drupal::entityTypeManager()->getStorage('node');
foreach (KB_BRANCHES as [$name, $tag, $addr, $display, $tel, $weight]) {
  $existing = $storage->loadByProperties(['type' => 'branch', 'title' => $name]);
  $node = $existing ? reset($existing) : Node::create(['type' => 'branch', 'title' => $name]);
  $node->set('field_tag', $tag);
  $node->set('field_address', $addr);
  $node->set('field_phone_display', $display);
  $node->set('field_phone_tel', $tel);
  $node->set('field_sort_order', $weight);
  $node->setPublished()->save();
  echo ($existing ? 'updated: ' : 'created: ') . $name . "\n";
}
```

- [ ] **Step 5: Seed and verify the count**

```bash
ddev drush php:script scripts/seed/seed_branches.php
ddev drush php:eval '$ids=\Drupal::entityTypeManager()->getStorage("node")->getQuery()->accessCheck(FALSE)->condition("type","branch")->execute(); print count($ids);'
```
Expected: `5`. Run the seed twice — the count stays `5` and the second run prints `updated:` five times.

- [ ] **Step 6: Export config and commit**

```bash
ddev drush cex -y
git add config/sync scripts/setup/install_page_model.php scripts/seed/seed_branches.php
git commit -m "feat(core): add branch content type and seed the five showrooms"
```

---

### Task 2: `GET /api/v1/branches`

**Interfaces:**
- Consumes: `branch` content type from Task 1.
- Produces: `BranchSerializer::toArray(NodeInterface): array` returning `['id','tag','name','address','phoneDisplay','phoneTel','mapUrl']`; route `keybolts_api.branches`.

**Files:**
- Create: `web/modules/custom/keybolts_api/src/Serializer/BranchSerializer.php`
- Create: `web/modules/custom/keybolts_api/src/Controller/PageController.php`
- Modify: `web/modules/custom/keybolts_api/keybolts_api.routing.yml`
- Modify: `web/modules/custom/keybolts_api/keybolts_api.services.yml`
- Test: `web/modules/custom/keybolts_core/tests/src/Kernel/BranchApiTest.php`

- [ ] **Step 1: Write the failing test**

`web/modules/custom/keybolts_core/tests/src/Kernel/BranchApiTest.php`:

```php
<?php

declare(strict_types=1);

namespace Drupal\Tests\keybolts_core\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * Branches must come back in the editor's chosen order, not creation order.
 */
class BranchApiTest extends KernelTestBase {

  protected static $modules = [
    'system', 'user', 'field', 'text', 'node', 'link',
    'keybolts_core', 'keybolts_api',
  ];

  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installSchema('node', ['node_access']);
    $this->installConfig(['node', 'field']);

    NodeType::create(['type' => 'branch', 'name' => 'Branch'])->save();
    foreach ([['B', 2], ['A', 1], ['C', 3]] as [$title, $weight]) {
      $this->createBranchField();
      Node::create([
        'type' => 'branch',
        'title' => $title,
        'status' => 1,
        'field_sort_order' => $weight,
      ])->save();
    }
  }

  /**
   * Creates field_sort_order once; later calls are no-ops.
   */
  private function createBranchField(): void {
    if (\Drupal\field\Entity\FieldStorageConfig::loadByName('node', 'field_sort_order')) {
      return;
    }
    \Drupal\field\Entity\FieldStorageConfig::create([
      'field_name' => 'field_sort_order',
      'entity_type' => 'node',
      'type' => 'integer',
    ])->save();
    \Drupal\field\Entity\FieldConfig::create([
      'field_name' => 'field_sort_order',
      'entity_type' => 'node',
      'bundle' => 'branch',
      'label' => 'Sort',
    ])->save();
  }

  public function testBranchesAreOrderedBySortOrder(): void {
    $titles = array_map(
      static fn(array $b) => $b['name'],
      $this->container->get('keybolts_api.branch_serializer')->all(),
    );
    $this->assertSame(['A', 'B', 'C'], $titles);
  }
}
```

- [ ] **Step 2: Run it and watch it fail**

```bash
ddev exec "cd /var/www/html && SIMPLETEST_DB=mysql://db:db@db/db vendor/bin/phpunit -c web/core/phpunit.xml.dist web/modules/custom/keybolts_core/tests/src/Kernel/BranchApiTest.php --no-coverage"
```
Expected: FAIL — service `keybolts_api.branch_serializer` does not exist.

- [ ] **Step 3: Implement the serializer**

`web/modules/custom/keybolts_api/src/Serializer/BranchSerializer.php`:

```php
<?php

declare(strict_types=1);

namespace Drupal\keybolts_api\Serializer;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

/**
 * Branches are shared by the homepage, About, Dealers and Contact pages.
 */
class BranchSerializer {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Every published branch, in the editor's chosen order.
   *
   * @return array<int, array>
   */
  public function all(): array {
    $storage = $this->entityTypeManager->getStorage('node');
    $ids = $storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'branch')
      ->condition('status', 1)
      ->sort('field_sort_order', 'ASC')
      // Total order: without a unique tiebreaker, equal weights can swap
      // between requests.
      ->sort('nid', 'ASC')
      ->execute();
    return array_values(array_map(
      fn(NodeInterface $n) => $this->toArray($n),
      $storage->loadMultiple($ids),
    ));
  }

  public function toArray(NodeInterface $node): array {
    return [
      'id' => (int) $node->id(),
      'name' => $node->label(),
      'tag' => $this->str($node, 'field_tag'),
      'address' => $this->str($node, 'field_address'),
      'phoneDisplay' => $this->str($node, 'field_phone_display'),
      'phoneTel' => $this->str($node, 'field_phone_tel'),
      'mapUrl' => $node->hasField('field_map_url') && !$node->get('field_map_url')->isEmpty()
        ? (string) $node->get('field_map_url')->uri
        : '',
    ];
  }

  private function str(NodeInterface $node, string $field): string {
    if (!$node->hasField($field) || $node->get($field)->isEmpty()) {
      return '';
    }
    return (string) $node->get($field)->value;
  }
}
```

Register it in `keybolts_api.services.yml`:

```yaml
  keybolts_api.branch_serializer:
    class: Drupal\keybolts_api\Serializer\BranchSerializer
    arguments: ['@entity_type.manager']
```

- [ ] **Step 4: Run the test and watch it pass**

Same command as Step 2. Expected: PASS.

- [ ] **Step 5: Add the controller and route**

`web/modules/custom/keybolts_api/src/Controller/PageController.php`:

```php
<?php

declare(strict_types=1);

namespace Drupal\keybolts_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\keybolts_api\ApiEnvelope;
use Drupal\keybolts_api\Serializer\BranchSerializer;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Serves the singleton page payloads and the shared branch list.
 */
class PageController extends ControllerBase {

  public function __construct(
    private readonly BranchSerializer $branches,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static($container->get('keybolts_api.branch_serializer'));
  }

  /**
   * GET /api/v1/branches
   */
  public function branches() {
    return ApiEnvelope::make($this->branches->all(), [], [], ['node_list:branch']);
  }
}
```

Append to `keybolts_api.routing.yml`:

```yaml
keybolts_api.branches:
  path: '/api/v1/branches'
  defaults:
    _controller: '\Drupal\keybolts_api\Controller\PageController::branches'
  methods: [GET]
  requirements:
    _permission: 'access content'
```

- [ ] **Step 6: Verify against the live site**

```bash
ddev drush cr
curl -sk "https://vietlong.ddev.site/api/v1/branches" | python3 -c "
import json,sys
d=json.load(sys.stdin)['data']
print('count', len(d))
print([b['tag'] for b in d])
print(d[0])
"
```
Expected: `count 5`; tags in order `['Bán buôn','Cơ sở 1','Cơ sở 2','Cơ sở 3','Cơ sở 4']`; the first row carries both `phoneDisplay` `0912.411.309` and `phoneTel` `0912411309`.

- [ ] **Step 7: Commit**

```bash
git add web/modules/custom/keybolts_api web/modules/custom/keybolts_core/tests
git commit -m "feat(api): add branches endpoint with editor-controlled ordering"
```

---

### Task 3: Homepage reads branches from the API

**Interfaces:**
- Consumes: `GET /api/v1/branches`.
- Produces: `fetchBranches(): Promise<ApiResponse<Branch[]>>` in `app/services/pages.ts`; `Branch` type in `app/types/page.ts`.

**Files:**
- Create: `frontend/app/types/page.ts`
- Create: `frontend/app/services/pages.ts`
- Modify: `frontend/app/components/home/BranchList.vue`
- Modify: `frontend/app/utils/homeContent.ts` (delete `LOCATIONS`)

- [ ] **Step 1: Add the type**

`frontend/app/types/page.ts`:

```ts
export interface Branch {
  id: number
  name: string
  tag: string
  address: string
  phoneDisplay: string
  phoneTel: string
  mapUrl: string
}
```

- [ ] **Step 2: Add the service**

`frontend/app/services/pages.ts`:

```ts
import { apiFetch } from './http'
import type { Branch } from '~/types/page'

export const fetchBranches = () => apiFetch<Branch[]>('/branches')
```

- [ ] **Step 3: Rewrite `BranchList` to use it**

`frontend/app/components/home/BranchList.vue`:

```vue
<script setup lang="ts">
import { fetchBranches } from '~/services/pages'

// Stable key so the header, homepage and Contact page share one request.
const { data } = await useAsyncData('branches', () => fetchBranches())
const branches = computed(() => data.value?.data ?? [])
</script>

<template>
  <section class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] py-16">
    <span class="text-eyebrow text-brass-700 font-bold tracking-[0.24em] uppercase">Hệ thống</span>
    <h2 class="text-display-lg mt-3 mb-10 font-bold tracking-[-0.03em]">Showroom &amp; kho hàng</h2>

    <div class="grid grid-cols-1 gap-px border border-border bg-border sm:grid-cols-2 lg:grid-cols-3">
      <div
        v-for="loc in branches"
        :key="loc.id"
        class="flex flex-col gap-3 bg-background p-[26px] transition hover:bg-surface"
      >
        <span class="text-heading font-bold">{{ loc.name }}</span>
        <span class="text-caption text-text-muted leading-relaxed">{{ loc.address }}</span>
        <a
          :href="`tel:${loc.phoneTel}`"
          class="text-body text-brass-700 font-bold no-underline"
        >{{ loc.phoneDisplay }}</a>
      </div>
    </div>
  </section>
</template>
```

- [ ] **Step 4: Delete the duplicated data**

Remove the whole `LOCATIONS` export from `frontend/app/utils/homeContent.ts`. Confirm nothing still imports it:

```bash
cd frontend && grep -rn "LOCATIONS" app/ || echo "no references — good"
```
Expected: `no references — good`.

- [ ] **Step 5: Verify the homepage still lists five branches**

```bash
curl -s "http://localhost:3100/" | grep -c "Showroom Từ Sơn"
curl -s "http://localhost:3100/" | grep -o "0912.411.309" | head -1
```
Expected: at least `1`, and the phone number prints. If nothing renders, restart `npm run dev` — a new import can leave the Tailwind/Vite scan stale.

- [ ] **Step 6: Commit**

```bash
git add frontend/app
git commit -m "refactor(frontend): read showrooms from the API instead of hard-coded data"
```

---

### Task 4: `contact_submission` entity

**Interfaces:**
- Produces: content entity `contact_submission` with base fields `name`, `phone`, `message`, `source`, `ip`, `created`; admin route `/admin/keybolts/submissions`.

**Files:**
- Create: `web/modules/custom/keybolts_core/src/Entity/ContactSubmission.php`
- Create: `web/modules/custom/keybolts_core/src/ContactSubmissionListBuilder.php`
- Modify: `web/modules/custom/keybolts_core/keybolts_core.links.menu.yml` (create if absent)
- Modify: `web/modules/custom/keybolts_core/keybolts_core.info.yml` — add the `options` dependency
- Modify: the four kernel tests in `web/modules/custom/keybolts_core/tests/src/Kernel/` — add `options` to `$modules`

> **Two things this task will break if you skip them.**
>
> 1. The entity needs a `route_provider` handler (included in the code below). Without it Drupal never generates `entity.contact_submission.collection`, the menu link points at a non-existent route, and `/admin/keybolts/submissions` returns 404.
> 2. The `source` field is `list_string`, which lives in the **`options`** module. Declare `options` in `keybolts_core.info.yml`, *and* add it to `$modules` in each existing kernel test. `KernelTestBase::enableModules()` does not resolve info.yml dependencies, and once the entity type is registered, `FieldDefinitionListener` walks every entity type — so a missing `options` fails 18 of the 22 existing tests, not just the new ones.

- [ ] **Step 1: Write the entity**

`web/modules/custom/keybolts_core/src/Entity/ContactSubmission.php`:

```php
<?php

declare(strict_types=1);

namespace Drupal\keybolts_core\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Stores a lead captured by one of the site's forms.
 *
 * A content entity rather than a node: these are operational records, not
 * published content, and they must not appear in /admin/content beside the
 * catalogue.
 *
 * @ContentEntityType(
 *   id = "contact_submission",
 *   label = @Translation("Yêu cầu liên hệ"),
 *   base_table = "contact_submission",
 *   handlers = {
 *     "list_builder" = "Drupal\keybolts_core\ContactSubmissionListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer nodes",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "collection" = "/admin/keybolts/submissions",
 *   },
 * )
 */
class ContactSubmission extends ContentEntityBase {

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Họ tên'))
      ->setRequired(TRUE);

    $fields['phone'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Số điện thoại'))
      ->setRequired(TRUE);

    $fields['message'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Nội dung'));

    $fields['source'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Nguồn'))
      ->setSetting('allowed_values', [
        'contact' => 'Liên hệ',
        'dealer' => 'Đăng ký đại lý',
        'consult' => 'Tư vấn',
      ])
      ->setDefaultValue('contact');

    $fields['ip'] = BaseFieldDefinition::create('string')
      ->setLabel(t('IP'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Thời gian'));

    return $fields;
  }
}
```

- [ ] **Step 2: Write the list builder**

`web/modules/custom/keybolts_core/src/ContactSubmissionListBuilder.php`:

```php
<?php

declare(strict_types=1);

namespace Drupal\keybolts_core;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Newest first — an editor checking for new leads wants today's at the top.
 */
class ContactSubmissionListBuilder extends EntityListBuilder {

  protected function getEntityIds(): array {
    return $this->getStorage()->getQuery()
      ->accessCheck(TRUE)
      ->sort('created', 'DESC')
      ->pager(50)
      ->execute();
  }

  public function buildHeader(): array {
    return [
      'created' => $this->t('Thời gian'),
      'name' => $this->t('Họ tên'),
      'phone' => $this->t('Điện thoại'),
      'source' => $this->t('Nguồn'),
      'message' => $this->t('Nội dung'),
    ] + parent::buildHeader();
  }

  public function buildRow(EntityInterface $entity): array {
    /** @var \Drupal\keybolts_core\Entity\ContactSubmission $entity */
    return [
      'created' => \Drupal::service('date.formatter')->format((int) $entity->get('created')->value, 'short'),
      'name' => $entity->get('name')->value,
      'phone' => $entity->get('phone')->value,
      'source' => $entity->get('source')->value,
      'message' => mb_substr((string) $entity->get('message')->value, 0, 80),
    ] + parent::buildRow($entity);
  }
}
```

- [ ] **Step 3: Add the admin menu link**

`web/modules/custom/keybolts_core/keybolts_core.links.menu.yml`:

```yaml
keybolts_core.submissions:
  title: 'Yêu cầu liên hệ'
  route_name: entity.contact_submission.collection
  parent: system.admin_content
```

- [ ] **Step 4: Install the entity schema and confirm the table exists**

```bash
ddev drush cr
ddev drush entity:updates -y 2>/dev/null || ddev drush php:eval '\Drupal::entityDefinitionUpdateManager()->installEntityType(\Drupal::entityTypeManager()->getDefinition("contact_submission"));'
ddev drush sql:query "SHOW TABLES LIKE 'contact_submission'"
```
Expected: the table name prints.

- [ ] **Step 5: Confirm the admin page loads**

```bash
ddev drush uli --uri=https://vietlong.ddev.site "/admin/keybolts/submissions"
```
Open the printed URL. Expected: an empty table with the five column headings.

- [ ] **Step 6: Commit**

```bash
git add web/modules/custom/keybolts_core
git commit -m "feat(core): add contact_submission entity with an admin listing"
```

---

### Task 5: `POST /api/v1/contact`

**Interfaces:**
- Consumes: `contact_submission` from Task 4.
- Produces: route `keybolts_api.contact`; accepts `{name, phone, message, source, website}`.

**Files:**
- Create: `web/modules/custom/keybolts_api/src/Controller/ContactController.php`
- Modify: `web/modules/custom/keybolts_api/keybolts_api.routing.yml`
- Test: `web/modules/custom/keybolts_core/tests/src/Kernel/ContactSubmissionTest.php`

- [ ] **Step 1: Write the failing tests**

`web/modules/custom/keybolts_core/tests/src/Kernel/ContactSubmissionTest.php`:

```php
<?php

declare(strict_types=1);

namespace Drupal\Tests\keybolts_core\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\keybolts_api\Controller\ContactController;
use Symfony\Component\HttpFoundation\Request;

/**
 * The lead form is the site's only write endpoint, so its guards matter.
 */
class ContactSubmissionTest extends KernelTestBase {

  protected static $modules = [
    'system', 'user', 'field', 'text', 'node',
    'keybolts_core', 'keybolts_api',
  ];

  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('contact_submission');
    $this->installSchema('system', ['sequences']);
  }

  private function post(array $body): array {
    $request = Request::create('/api/v1/contact', 'POST', [], [], [], [], json_encode($body));
    $controller = ContactController::create($this->container);
    $response = $controller->submit($request);
    return [$response->getStatusCode(), json_decode($response->getContent(), TRUE)];
  }

  private function countSubmissions(): int {
    return (int) $this->container->get('entity_type.manager')
      ->getStorage('contact_submission')
      ->getQuery()->accessCheck(FALSE)->count()->execute();
  }

  public function testValidSubmissionIsStored(): void {
    [$status] = $this->post([
      'name' => 'Nguyễn Văn A',
      'phone' => '0912411309',
      'message' => 'Cần báo giá',
      'source' => 'dealer',
    ]);
    $this->assertSame(201, $status);
    $this->assertSame(1, $this->countSubmissions());
  }

  public function testMissingFieldsReturn422AndStoreNothing(): void {
    [$status, $body] = $this->post(['name' => '', 'phone' => '']);
    $this->assertSame(422, $status);
    $this->assertContains('name', $body['errors']);
    $this->assertContains('phone', $body['errors']);
    $this->assertSame(0, $this->countSubmissions());
  }

  /**
   * A bot told "you failed" learns how to pass. Answer 201 and drop it.
   */
  public function testHoneypotLooksSuccessfulButStoresNothing(): void {
    [$status] = $this->post([
      'name' => 'Bot',
      'phone' => '0900000000',
      'website' => 'http://spam.example',
    ]);
    $this->assertSame(201, $status);
    $this->assertSame(0, $this->countSubmissions());
  }

  public function testUnknownSourceFallsBackToContact(): void {
    $this->post(['name' => 'A', 'phone' => '1', 'source' => 'nonsense']);
    $ids = $this->container->get('entity_type.manager')
      ->getStorage('contact_submission')
      ->getQuery()->accessCheck(FALSE)->execute();
    $entity = $this->container->get('entity_type.manager')
      ->getStorage('contact_submission')->load(reset($ids));
    $this->assertSame('contact', $entity->get('source')->value);
  }
}
```

- [ ] **Step 2: Run them and watch them fail**

```bash
ddev exec "cd /var/www/html && SIMPLETEST_DB=mysql://db:db@db/db vendor/bin/phpunit -c web/core/phpunit.xml.dist web/modules/custom/keybolts_core/tests/src/Kernel/ContactSubmissionTest.php --no-coverage"
```
Expected: FAIL — class `ContactController` not found.

- [ ] **Step 3: Implement the controller**

`web/modules/custom/keybolts_api/src/Controller/ContactController.php`:

```php
<?php

declare(strict_types=1);

namespace Drupal\keybolts_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Flood\FloodInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * The only write endpoint on the site.
 */
class ContactController extends ControllerBase {

  private const ALLOWED_SOURCES = ['contact', 'dealer', 'consult'];
  private const FLOOD_EVENT = 'keybolts_api.contact';
  private const FLOOD_LIMIT = 5;
  private const FLOOD_WINDOW = 600;

  public function __construct(
    private readonly FloodInterface $flood,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static($container->get('flood'));
  }

  /**
   * POST /api/v1/contact
   */
  public function submit(Request $request): JsonResponse {
    $data = json_decode((string) $request->getContent(), TRUE) ?: [];

    // Honeypot: a hidden field only a bot fills in. Answer as if accepted —
    // telling it that it failed just teaches it how to pass.
    if (!empty($data['website'])) {
      return $this->noStore(['ok' => TRUE], 201);
    }

    $ip = (string) $request->getClientIp();
    if (!$this->flood->isAllowed(self::FLOOD_EVENT, self::FLOOD_LIMIT, self::FLOOD_WINDOW, $ip)) {
      return $this->noStore(['error' => 'Quá nhiều yêu cầu. Vui lòng thử lại sau.'], 429);
    }

    $name = trim((string) ($data['name'] ?? ''));
    $phone = trim((string) ($data['phone'] ?? ''));
    $errors = [];
    if ($name === '') {
      $errors[] = 'name';
    }
    if ($phone === '') {
      $errors[] = 'phone';
    }
    if ($errors) {
      return $this->noStore(['errors' => $errors], 422);
    }

    $source = (string) ($data['source'] ?? 'contact');
    if (!in_array($source, self::ALLOWED_SOURCES, TRUE)) {
      $source = 'contact';
    }

    $this->entityTypeManager()->getStorage('contact_submission')->create([
      'name' => mb_substr($name, 0, 255),
      'phone' => mb_substr($phone, 0, 60),
      'message' => mb_substr(trim((string) ($data['message'] ?? '')), 0, 4000),
      'source' => $source,
      'ip' => $ip,
    ])->save();

    $this->flood->register(self::FLOOD_EVENT, self::FLOOD_WINDOW, $ip);

    return $this->noStore(['ok' => TRUE], 201);
  }

  /**
   * This endpoint writes, so no layer may ever cache its response.
   */
  private function noStore(array $payload, int $status): JsonResponse {
    $response = new JsonResponse($payload, $status);
    $response->headers->set('Cache-Control', 'no-store');
    return $response;
  }
}
```

Append to `keybolts_api.routing.yml`:

```yaml
keybolts_api.contact:
  path: '/api/v1/contact'
  defaults:
    _controller: '\Drupal\keybolts_api\Controller\ContactController::submit'
  methods: [POST]
  requirements:
    _permission: 'access content'
```

- [ ] **Step 4: Run the tests and watch them pass**

Same command as Step 2. Expected: 4 tests PASS.

- [ ] **Step 5: Verify against the live endpoint**

```bash
ddev drush cr
curl -sk -X POST "https://vietlong.ddev.site/api/v1/contact" \
  -H 'Content-Type: application/json' \
  -d '{"name":"Kiểm thử","phone":"0912411309","message":"Thử","source":"contact"}' \
  -w "\nstatus=%{http_code}\n"
ddev drush php:eval 'print \Drupal::entityTypeManager()->getStorage("contact_submission")->getQuery()->accessCheck(FALSE)->count()->execute();'
```
Expected: `status=201`, and the count prints `1`.

- [ ] **Step 6: Commit**

```bash
git add web/modules/custom/keybolts_api web/modules/custom/keybolts_core/tests
git commit -m "feat(api): accept lead submissions with honeypot and flood control"
```

---

### Task 6: `LeadForm` component and its validation helper

**Interfaces:**
- Consumes: `POST /api/v1/contact`.
- Produces: `validateLead(state): string[]` and `normalisePhone(raw): string` in `app/utils/leadForm.ts`; `<PageLeadForm :source="..." :title="..." :desc="..." :success-title="..." :success-desc="..." />`.

**Files:**
- Create: `frontend/app/utils/leadForm.ts`
- Create: `frontend/app/components/page/LeadForm.vue`
- Modify: `frontend/app/services/pages.ts`
- Test: `frontend/test/leadForm.spec.ts`

- [ ] **Step 1: Write the failing test**

`frontend/test/leadForm.spec.ts`:

```ts
import { describe, expect, it } from 'vitest'
import { normalisePhone, validateLead } from '../app/utils/leadForm'

describe('validateLead', () => {
  it('requires name and phone', () => {
    expect(validateLead({ name: '', phone: '', message: '' })).toEqual(['name', 'phone'])
  })

  it('treats whitespace as empty', () => {
    expect(validateLead({ name: '   ', phone: '\t', message: '' })).toEqual(['name', 'phone'])
  })

  it('passes when both are present', () => {
    expect(validateLead({ name: 'Nguyễn Văn A', phone: '0912411309', message: '' })).toEqual([])
  })
})

describe('normalisePhone', () => {
  it('strips the separators the design displays', () => {
    expect(normalisePhone('0912.411.309')).toBe('0912411309')
    expect(normalisePhone('0968 689 112')).toBe('0968689112')
    expect(normalisePhone('(0981) 255-215')).toBe('0981255215')
  })

  it('keeps a leading + for international numbers', () => {
    expect(normalisePhone('+84 912 411 309')).toBe('+84912411309')
  })
})
```

- [ ] **Step 2: Run it and watch it fail**

```bash
cd frontend && npx vitest run test/leadForm.spec.ts
```
Expected: FAIL — cannot resolve `../app/utils/leadForm`.

- [ ] **Step 3: Implement the helper**

`frontend/app/utils/leadForm.ts`:

```ts
export interface LeadState {
  name: string
  phone: string
  message: string
}

/** Returns the names of the invalid fields; empty means valid. */
export function validateLead(state: LeadState): string[] {
  const errors: string[] = []
  if (!state.name.trim()) errors.push('name')
  if (!state.phone.trim()) errors.push('phone')
  return errors
}

/**
 * The design prints numbers as 0912.411.309, and people paste them with
 * spaces, dots, dashes and brackets. Send the server digits only.
 */
export function normalisePhone(raw: string): string {
  const trimmed = raw.trim()
  const plus = trimmed.startsWith('+') ? '+' : ''
  return plus + trimmed.replace(/\D/g, '')
}
```

- [ ] **Step 4: Run the test and watch it pass**

```bash
cd frontend && npx vitest run test/leadForm.spec.ts
```
Expected: 5 tests PASS.

- [ ] **Step 5: Add the submit service**

Append to `frontend/app/services/pages.ts`:

```ts
export interface LeadPayload {
  name: string
  phone: string
  message: string
  source: 'contact' | 'dealer' | 'consult'
  /** Honeypot — must stay empty for real users. */
  website?: string
}

export async function submitLead(payload: LeadPayload): Promise<void> {
  const base = useRuntimeConfig().public.apiBase
  await $fetch(`${base}/contact`, { method: 'POST', body: payload })
}
```

- [ ] **Step 6: Build the component**

`frontend/app/components/page/LeadForm.vue`:

```vue
<script setup lang="ts">
import { submitLead, type LeadPayload } from '~/services/pages'
import { normalisePhone, validateLead } from '~/utils/leadForm'

const props = defineProps<{
  source: LeadPayload['source']
  title: string
  desc: string
  successTitle: string
  successDesc: string
}>()

const state = reactive({ name: '', phone: '', message: '' })
const website = ref('')          // honeypot
const errors = ref<string[]>([])
const sending = ref(false)
const sent = ref(false)
const failed = ref(false)

const invalid = (field: string) => errors.value.includes(field)

async function submit() {
  errors.value = validateLead(state)
  if (errors.value.length) return
  sending.value = true
  failed.value = false
  try {
    await submitLead({
      name: state.name.trim(),
      phone: normalisePhone(state.phone),
      message: state.message.trim(),
      source: props.source,
      website: website.value,
    })
    sent.value = true
  }
  catch {
    failed.value = true
  }
  finally {
    sending.value = false
  }
}

function reset() {
  state.name = ''
  state.phone = ''
  state.message = ''
  errors.value = []
  sent.value = false
  failed.value = false
}
</script>

<template>
  <div class="bg-background p-8 text-text">
    <div v-if="sent" class="flex flex-col gap-4">
      <span class="text-display font-bold">{{ successTitle }}</span>
      <p class="text-body text-text-muted m-0">{{ successDesc }}</p>
      <button
        type="button"
        class="text-body w-fit cursor-pointer rounded-sm bg-charcoal-900 px-6 py-3 font-bold text-white"
        @click="reset"
      >Gửi yêu cầu khác</button>
    </div>

    <form v-else class="flex flex-col gap-4" novalidate @submit.prevent="submit">
      <div class="flex flex-col gap-1">
        <span class="text-heading font-bold">{{ title }}</span>
        <p class="text-caption text-text-muted m-0 leading-relaxed">{{ desc }}</p>
      </div>

      <!-- Honeypot: off-screen, not display:none, so bots that skip hidden
           fields still fill it. Never shown to real users. -->
      <input
        v-model="website"
        type="text"
        name="website"
        tabindex="-1"
        autocomplete="off"
        aria-hidden="true"
        class="absolute -left-[9999px] h-0 w-0 opacity-0"
      >

      <label class="flex flex-col gap-2">
        <span class="text-caption text-text-muted">Họ tên</span>
        <input
          v-model="state.name"
          class="text-body rounded-sm border px-4 py-3"
          :class="invalid('name') ? 'border-danger' : 'border-border'"
        >
      </label>

      <label class="flex flex-col gap-2">
        <span class="text-caption text-text-muted">Số điện thoại</span>
        <input
          v-model="state.phone"
          inputmode="tel"
          class="text-body rounded-sm border px-4 py-3"
          :class="invalid('phone') ? 'border-danger' : 'border-border'"
        >
      </label>

      <label class="flex flex-col gap-2">
        <span class="text-caption text-text-muted">Nội dung</span>
        <textarea v-model="state.message" rows="3" class="text-body rounded-sm border border-border px-4 py-3" />
      </label>

      <p v-if="errors.length" class="text-caption text-danger m-0">
        Vui lòng nhập họ tên và số điện thoại.
      </p>
      <p v-if="failed" class="text-caption text-danger m-0">
        Không gửi được. Vui lòng gọi {{ HOTLINE }}.
      </p>

      <button
        type="submit"
        :disabled="sending"
        class="text-body cursor-pointer rounded-sm bg-gold-200 px-8 py-4 font-bold tracking-[0.06em] text-charcoal-900 uppercase disabled:opacity-60"
      >{{ sending ? 'Đang gửi…' : 'Gửi yêu cầu' }}</button>
    </form>
  </div>
</template>
```

- [ ] **Step 7: Commit**

```bash
cd frontend && npm test
git add frontend/app frontend/test
git commit -m "feat(frontend): add shared lead form with validation and honeypot"
```

---

### Task 7: Paragraph types and the `about_page` model

**Interfaces:**
- Produces: paragraph types `fact`, `numbered_item`, `segment`, `value_item`; content type `about_page` with the fields listed below.

**Files:**
- Modify: `scripts/setup/install_page_model.php`
- Create: `scripts/seed/seed_about.php`

> Re-read `design/Keybolts About.html` before this task. Copy the strings verbatim.

- [ ] **Step 1: Append the paragraph types**

Add to `scripts/setup/install_page_model.php`, before the final `echo`:

```php
use Drupal\paragraphs\Entity\ParagraphsType;

/**
 * Creates a paragraph type when missing.
 */
function kbp_paragraph(string $id, string $label): void {
  if (!ParagraphsType::load($id)) {
    ParagraphsType::create(['id' => $id, 'label' => $label])->save();
    echo "paragraph type: {$id}\n";
  }
}

kbp_paragraph('fact', 'Con số');
kbp_field('paragraph', 'fact', 'field_fact_number', 'string', 'Con số');
kbp_field('paragraph', 'fact', 'field_fact_label', 'string', 'Nhãn');

// Serves both the About process steps and the Dealers benefits: same shape.
kbp_paragraph('numbered_item', 'Mục có số thứ tự');
kbp_field('paragraph', 'numbered_item', 'field_item_number', 'string', 'Số');
kbp_field('paragraph', 'numbered_item', 'field_item_title', 'string', 'Tiêu đề');
kbp_field('paragraph', 'numbered_item', 'field_item_desc', 'string_long', 'Mô tả');

kbp_paragraph('segment', 'Nhóm khách hàng');
kbp_field('paragraph', 'segment', 'field_seg_title', 'string', 'Tiêu đề');
kbp_field('paragraph', 'segment', 'field_seg_desc', 'string_long', 'Mô tả');
kbp_field('paragraph', 'segment', 'field_seg_cta', 'link', 'Nút');
kbp_field('paragraph', 'segment', 'field_seg_image', 'image', 'Ảnh');

kbp_paragraph('value_item', 'Cam kết');
kbp_field('paragraph', 'value_item', 'field_value_title', 'string', 'Tiêu đề');
kbp_field('paragraph', 'value_item', 'field_value_desc', 'string_long', 'Mô tả');
```

- [ ] **Step 2: Append the `about_page` type**

```php
kbp_node_type('about_page', 'Trang Giới thiệu');

kbp_field('node', 'about_page', 'field_eyebrow', 'string', 'Eyebrow');
kbp_field('node', 'about_page', 'field_subtitle', 'string_long', 'Mô tả ngắn');
kbp_field('node', 'about_page', 'field_hero_image', 'image', 'Ảnh hero');
kbp_field('node', 'about_page', 'field_hero_caption', 'string', 'Chú thích ảnh');
kbp_field('node', 'about_page', 'field_cta_primary', 'link', 'Nút chính');
kbp_field('node', 'about_page', 'field_cta_secondary', 'link', 'Nút phụ');

kbp_field('node', 'about_page', 'field_story_eyebrow', 'string', 'Câu chuyện — eyebrow');
kbp_field('node', 'about_page', 'field_story_title', 'string', 'Câu chuyện — tiêu đề');
kbp_field('node', 'about_page', 'field_story_body', 'text_long', 'Câu chuyện — nội dung');
kbp_field('node', 'about_page', 'field_credentials', 'string', 'Chứng nhận', -1);

kbp_paragraph_ref('about_page', 'field_facts', 'Con số', ['fact']);
kbp_paragraph_ref('about_page', 'field_segments', 'Nhóm khách hàng', ['segment']);
kbp_paragraph_ref('about_page', 'field_steps', 'Quy trình', ['numbered_item']);
kbp_paragraph_ref('about_page', 'field_values', 'Cam kết', ['value_item']);
```

Add this helper near `kbp_field`:

```php
/**
 * Creates an unlimited paragraph reference field restricted to given bundles.
 */
function kbp_paragraph_ref(string $bundle, string $name, string $label, array $targets): void {
  kbp_field(
    'node', $bundle, $name, 'entity_reference_revisions', $label, -1,
    ['target_type' => 'paragraph'],
    ['handler' => 'default:paragraph', 'handler_settings' => [
      'target_bundles' => array_combine($targets, $targets),
      'negate' => 0,
    ]],
  );
}
```

- [ ] **Step 3: Run and verify**

```bash
ddev drush php:script scripts/setup/install_page_model.php
ddev drush php:eval '$f=\Drupal::service("entity_field.manager")->getFieldDefinitions("node","about_page"); print count(array_filter(array_keys($f), fn($n)=>str_starts_with($n,"field_")));'
```
Expected: `14`.

- [ ] **Step 4: Seed the About node**

`scripts/seed/seed_about.php` — creates the single node with copy from the prototype. Matches on type so it never duplicates.

```php
<?php

/**
 * @file
 * Seeds the single Giới thiệu node. Safe to run repeatedly.
 *
 * Run: ddev drush php:script scripts/seed/seed_about.php
 */

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Builds paragraphs from rows, replacing whatever was there.
 */
function kbp_paras(string $type, array $rows, array $map): array {
  $out = [];
  foreach ($rows as $row) {
    $values = ['type' => $type];
    foreach ($map as $i => $field) {
      $values[$field] = $row[$i];
    }
    $p = Paragraph::create($values);
    $p->save();
    $out[] = ['target_id' => $p->id(), 'target_revision_id' => $p->getRevisionId()];
  }
  return $out;
}

$storage = \Drupal::entityTypeManager()->getStorage('node');
$existing = $storage->loadByProperties(['type' => 'about_page']);
$node = $existing ? reset($existing) : Node::create(['type' => 'about_page']);

$node->setTitle('Nhà nhập khẩu khóa cửa cao cấp phục vụ công trình toàn quốc');
$node->set('field_eyebrow', 'Về Keybolts');
$node->set('field_subtitle', 'Từ 2014, Keybolts — thương hiệu của Công ty TNHH XNK Khóa Cửa Việt Long — nhập khẩu và phân phối khóa cửa, khóa thông minh, khóa khách sạn và phụ kiện cửa cao cấp. Hàng chính hãng, chứng nhận CE-CFF, bảo hành 5–10 năm, giao toàn quốc từ 5 kho miền Bắc.');
$node->set('field_hero_caption', 'Khóa đồng đại sảnh — hoàn thiện vàng bóng PVD');
$node->set('field_cta_primary', ['uri' => 'tel:19009018', 'title' => 'Gọi 1900 9018']);
$node->set('field_cta_secondary', ['uri' => 'internal:/san-pham', 'title' => 'Xem catalogue']);

$node->set('field_story_eyebrow', 'Câu chuyện');
$node->set('field_story_title', 'Ổ khóa không chỉ để an toàn');
$node->set('field_story_body', [
  'value' => '<p>Với Keybolts, bộ khóa trên cánh cửa là thứ khách nhìn thấy đầu tiên và chạm vào mỗi ngày — vừa là lớp bảo vệ, vừa là chi tiết nói lên gu thẩm mỹ của ngôi nhà. Vì vậy chúng tôi chỉ nhập những dòng khóa đạt cả hai: cơ khí chắc chắn và hoàn thiện đẹp.</p><p>Mỗi lô hàng về kho đều được kiểm tra cơ khí và lớp mạ trước khi nhập kho. Chúng tôi từ chối những dòng khóa rẻ nhưng nhanh xuống màu — vì bảo hành 10 năm chỉ có nghĩa khi sản phẩm thực sự trụ được 10 năm.</p>',
  'format' => 'basic_html',
]);
$node->set('field_credentials', [
  'Chứng nhận CE-CFF quốc tế',
  'Hóa đơn VAT cho mọi đơn hàng',
  'Kiểm tra từng lô trước nhập kho',
  'Sẵn linh kiện thay thế tại kho',
]);

$node->set('field_facts', kbp_paras('fact', [
  ['2014', 'Năm thành lập'],
  ['5', 'Showroom & kho'],
  ['200+', 'Mã sản phẩm'],
  ['10', 'Năm bảo hành'],
  ['CE-CFF', 'Chứng nhận'],
], ['field_fact_number', 'field_fact_label']));

$node->set('field_steps', kbp_paras('numbered_item', [
  ['01', 'Tiếp nhận nhu cầu', 'Gọi hotline hoặc gửi ảnh cửa qua Zalo — không cần biết trước tên model.'],
  ['02', 'Khảo sát kỹ thuật', 'Xác định loại cửa, độ dày cánh, chiều mở và phong cách hoàn thiện.'],
  ['03', 'Báo giá & hợp đồng', 'Gửi phương án kèm giá trong 24 giờ làm việc, xuất hóa đơn VAT.'],
  ['04', 'Giao hàng & lắp đặt', 'Giao 2–5 ngày toàn quốc, hỗ trợ kỹ thuật lắp đặt và bàn giao.'],
  ['05', 'Bảo hành 5–10 năm', 'Phiếu bảo hành theo bộ, sẵn linh kiện thay thế tại 5 cơ sở.'],
], ['field_item_number', 'field_item_title', 'field_item_desc']));

$node->set('field_values', kbp_paras('value_item', [
  ['Hàng chính hãng', 'Nhập khẩu nguyên bộ, có chứng từ và tem chống giả — không bán hàng trôi nổi.'],
  ['Tư vấn đúng kỹ thuật', 'Chọn khóa theo độ dày cửa, chiều mở và loại cánh — không bán sai model.'],
  ['Hậu mãi rõ ràng', 'Phiếu bảo hành theo bộ, hỗ trợ kỹ thuật và thay linh kiện suốt thời gian bảo hành.'],
], ['field_value_title', 'field_value_desc']));

$segments = [
  ['Chủ nhà & biệt thự', 'Chọn bộ khóa đồng bộ cho toàn bộ cánh cửa trong nhà, hợp phong cách nội thất.', 'Xem khóa đồng'],
  ['Khách sạn & resort', 'Khóa thẻ từ số lượng lớn, cấp thẻ master, phương án chìa cơ dự phòng.', 'Xem khóa khách sạn'],
  ['Nhà thầu & thi công', 'Báo giá theo hồ sơ dự án, giao theo tiến độ thi công, hỗ trợ kỹ thuật tại công trình.', 'Xem dự án'],
  ['Đại lý & cửa hàng', 'Giá đại lý theo cấp, hàng mẫu trưng bày, bảo vệ khu vực kinh doanh.', 'Chính sách đại lý'],
];
$seg_values = [];
foreach ($segments as [$title, $desc, $cta]) {
  $p = Paragraph::create([
    'type' => 'segment',
    'field_seg_title' => $title,
    'field_seg_desc' => $desc,
    'field_seg_cta' => ['uri' => 'internal:/san-pham', 'title' => $cta],
  ]);
  $p->save();
  $seg_values[] = ['target_id' => $p->id(), 'target_revision_id' => $p->getRevisionId()];
}
$node->set('field_segments', $seg_values);

$node->setPublished()->save();
echo ($existing ? 'updated' : 'created') . " about_page node {$node->id()}\n";
```

- [ ] **Step 5: Seed and verify counts**

```bash
ddev drush php:script scripts/seed/seed_about.php
ddev drush php:eval '$n=reset(\Drupal::entityTypeManager()->getStorage("node")->loadByProperties(["type"=>"about_page"])); foreach(["field_facts","field_steps","field_values","field_segments","field_credentials"] as $f){ print "$f=".count($n->get($f))." "; }'
```
Expected: `field_facts=5 field_steps=5 field_values=3 field_segments=4 field_credentials=4`

- [ ] **Step 6: Export config and commit**

```bash
ddev drush cex -y
git add config/sync scripts
git commit -m "feat(core): add about_page model, shared paragraph types and seed"
```

---

### Task 8: `GET /api/v1/page/{key}`

**Interfaces:**
- Consumes: `about_page` from Task 7, `BranchSerializer` from Task 2.
- Produces: `PageSerializer::about(NodeInterface): array`; route `keybolts_api.page` accepting `about|dealers|contact|policies`.

**Files:**
- Create: `web/modules/custom/keybolts_api/src/Serializer/PageSerializer.php`
- Modify: `web/modules/custom/keybolts_api/src/Controller/PageController.php`
- Modify: `keybolts_api.routing.yml`, `keybolts_api.services.yml`
- Test: `web/modules/custom/keybolts_core/tests/src/Kernel/PageApiTest.php`

- [ ] **Step 1: Write the failing test**

`web/modules/custom/keybolts_core/tests/src/Kernel/PageApiTest.php`:

```php
<?php

declare(strict_types=1);

namespace Drupal\Tests\keybolts_core\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\keybolts_api\Controller\PageController;
use Drupal\node\Entity\NodeType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * A singleton page that has not been created yet must 404, not 500.
 */
class PageApiTest extends KernelTestBase {

  protected static $modules = [
    'system', 'user', 'field', 'text', 'node', 'link', 'image',
    'paragraphs', 'entity_reference_revisions', 'file',
    'keybolts_core', 'keybolts_api',
  ];

  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installEntitySchema('paragraph');
    $this->installEntitySchema('file');
    $this->installSchema('node', ['node_access']);
    $this->installConfig(['node', 'field']);
    NodeType::create(['type' => 'about_page', 'name' => 'About'])->save();
  }

  public function testUnknownKeyIs404(): void {
    $this->expectException(NotFoundHttpException::class);
    PageController::create($this->container)->page('nonsense');
  }

  public function testMissingSingletonIs404(): void {
    // The type exists but no node has been created yet.
    $this->expectException(NotFoundHttpException::class);
    PageController::create($this->container)->page('about');
  }
}
```

- [ ] **Step 2: Run and watch it fail**

```bash
ddev exec "cd /var/www/html && SIMPLETEST_DB=mysql://db:db@db/db vendor/bin/phpunit -c web/core/phpunit.xml.dist web/modules/custom/keybolts_core/tests/src/Kernel/PageApiTest.php --no-coverage"
```
Expected: FAIL — `PageController::page()` does not exist.

- [ ] **Step 3: Implement `PageSerializer`**

`web/modules/custom/keybolts_api/src/Serializer/PageSerializer.php`:

```php
<?php

declare(strict_types=1);

namespace Drupal\keybolts_api\Serializer;

use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\node\NodeInterface;

/**
 * Turns each singleton page node into the exact blocks its design renders.
 *
 * One method per page rather than a generic walker: the frontend should never
 * have to infer structure, and a wrong key here fails loudly in tests.
 */
class PageSerializer {

  public function __construct(
    private readonly FileUrlGeneratorInterface $fileUrlGenerator,
  ) {}

  public function about(NodeInterface $n): array {
    return [
      'eyebrow' => $this->str($n, 'field_eyebrow'),
      'title' => $n->label(),
      'subtitle' => $this->str($n, 'field_subtitle'),
      'heroImage' => $this->image($n, 'field_hero_image'),
      'heroCaption' => $this->str($n, 'field_hero_caption'),
      'ctaPrimary' => $this->link($n, 'field_cta_primary'),
      'ctaSecondary' => $this->link($n, 'field_cta_secondary'),
      'facts' => $this->paras($n, 'field_facts', [
        'number' => 'field_fact_number', 'label' => 'field_fact_label',
      ]),
      'storyEyebrow' => $this->str($n, 'field_story_eyebrow'),
      'storyTitle' => $this->str($n, 'field_story_title'),
      'storyBody' => $n->hasField('field_story_body') && !$n->get('field_story_body')->isEmpty()
        ? $n->get('field_story_body')->processed
        : '',
      'credentials' => $this->multi($n, 'field_credentials'),
      'segments' => $this->paras($n, 'field_segments', [
        'title' => 'field_seg_title', 'desc' => 'field_seg_desc',
      ], 'field_seg_cta', 'field_seg_image'),
      'steps' => $this->paras($n, 'field_steps', [
        'number' => 'field_item_number', 'title' => 'field_item_title', 'desc' => 'field_item_desc',
      ]),
      'values' => $this->paras($n, 'field_values', [
        'title' => 'field_value_title', 'desc' => 'field_value_desc',
      ]),
    ];
  }

  public function dealers(NodeInterface $n): array {
    return [
      'eyebrow' => $this->str($n, 'field_eyebrow'),
      'title' => $n->label(),
      'subtitle' => $this->str($n, 'field_subtitle'),
      'benefits' => $this->paras($n, 'field_benefits', [
        'number' => 'field_item_number', 'title' => 'field_item_title', 'desc' => 'field_item_desc',
      ]),
      'criteria' => $this->multi($n, 'field_criteria'),
      'formTitle' => $this->str($n, 'field_form_title'),
      'formDesc' => $this->str($n, 'field_form_desc'),
      'successTitle' => $this->str($n, 'field_success_title'),
      'successDesc' => $this->str($n, 'field_success_desc'),
    ];
  }

  public function contact(NodeInterface $n): array {
    return [
      'eyebrow' => $this->str($n, 'field_eyebrow'),
      'title' => $n->label(),
      'subtitle' => $this->str($n, 'field_subtitle'),
      'channels' => $this->paras($n, 'field_channels', [
        'label' => 'field_ch_label', 'value' => 'field_ch_value', 'note' => 'field_ch_note',
      ], 'field_ch_url'),
      'companyName' => $this->str($n, 'field_company_name'),
      'companyAddress' => $this->str($n, 'field_company_address'),
      'responseTitle' => $this->str($n, 'field_response_title'),
      'responseBody' => $this->str($n, 'field_response_body'),
      'formTitle' => $this->str($n, 'field_form_title'),
      'formDesc' => $this->str($n, 'field_form_desc'),
      'successTitle' => $this->str($n, 'field_success_title'),
      'successDesc' => $this->str($n, 'field_success_desc'),
    ];
  }

  public function policies(NodeInterface $n): array {
    $sections = [];
    if ($n->hasField('field_sections')) {
      foreach ($n->get('field_sections') as $item) {
        $p = $item->entity;
        if (!$p) {
          continue;
        }
        $items = [];
        if ($p->hasField('field_pol_items')) {
          foreach ($p->get('field_pol_items') as $sub) {
            $row = $sub->entity;
            if (!$row) {
              continue;
            }
            $items[] = [
              'k' => (string) $row->get('field_pol_key')->value,
              'v' => (string) $row->get('field_pol_value')->value,
            ];
          }
        }
        $sections[] = [
          'key' => 'sec-' . $p->id(),
          'label' => (string) $p->get('field_pol_label')->value,
          'eyebrow' => (string) $p->get('field_pol_eyebrow')->value,
          'title' => (string) $p->get('field_pol_title')->value,
          'intro' => (string) $p->get('field_pol_intro')->value,
          'note' => (string) $p->get('field_pol_note')->value,
          'items' => $items,
        ];
      }
    }
    return [
      'eyebrow' => $this->str($n, 'field_eyebrow'),
      'title' => $n->label(),
      'subtitle' => $this->str($n, 'field_subtitle'),
      'sections' => $sections,
      'supportTitle' => $this->str($n, 'field_support_title'),
      'supportNote' => $this->str($n, 'field_support_note'),
    ];
  }

  /**
   * Flattens a paragraph field. $map is output key => paragraph field name.
   */
  private function paras(
    NodeInterface $n,
    string $field,
    array $map,
    ?string $link_field = NULL,
    ?string $image_field = NULL,
  ): array {
    if (!$n->hasField($field)) {
      return [];
    }
    $rows = [];
    foreach ($n->get($field) as $item) {
      $p = $item->entity;
      if (!$p) {
        continue;
      }
      $row = [];
      foreach ($map as $out => $source) {
        $row[$out] = $p->hasField($source) && !$p->get($source)->isEmpty()
          ? (string) $p->get($source)->value
          : '';
      }
      if ($link_field && $p->hasField($link_field) && !$p->get($link_field)->isEmpty()) {
        $row['ctaLabel'] = (string) $p->get($link_field)->title;
        $row['ctaUrl'] = $this->uriToPath((string) $p->get($link_field)->uri);
      }
      if ($image_field && $p->hasField($image_field) && !$p->get($image_field)->isEmpty()) {
        $file = $p->get($image_field)->entity;
        $row['image'] = $file
          ? $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri())
          : '';
      }
      $rows[] = $row;
    }
    return $rows;
  }

  private function link(NodeInterface $n, string $field): array {
    if (!$n->hasField($field) || $n->get($field)->isEmpty()) {
      return ['label' => '', 'url' => ''];
    }
    return [
      'label' => (string) $n->get($field)->title,
      'url' => $this->uriToPath((string) $n->get($field)->uri),
    ];
  }

  /**
   * Drupal stores internal links as `internal:/foo`; the frontend wants `/foo`.
   */
  private function uriToPath(string $uri): string {
    return str_starts_with($uri, 'internal:') ? substr($uri, 9) : $uri;
  }

  private function image(NodeInterface $n, string $field): string {
    if (!$n->hasField($field) || $n->get($field)->isEmpty()) {
      return '';
    }
    $file = $n->get($field)->entity;
    return $file ? $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri()) : '';
  }

  private function str(NodeInterface $n, string $field): string {
    if (!$n->hasField($field) || $n->get($field)->isEmpty()) {
      return '';
    }
    return (string) $n->get($field)->value;
  }

  private function multi(NodeInterface $n, string $field): array {
    if (!$n->hasField($field)) {
      return [];
    }
    return array_values(array_map(
      static fn(array $i) => (string) ($i['value'] ?? ''),
      $n->get($field)->getValue(),
    ));
  }
}
```

Register in `keybolts_api.services.yml`:

```yaml
  keybolts_api.page_serializer:
    class: Drupal\keybolts_api\Serializer\PageSerializer
    arguments: ['@file_url_generator']
```

- [ ] **Step 4: Add the controller action and route**

`PageController` currently takes only the branch serializer. Replace its
constructor and `create()` with these, so both serializers are injected:

```php
  public function __construct(
    private readonly BranchSerializer $branches,
    private readonly PageSerializer $pages,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('keybolts_api.branch_serializer'),
      $container->get('keybolts_api.page_serializer'),
    );
  }
```

Add the `use Drupal\keybolts_api\Serializer\PageSerializer;` import alongside the
existing `BranchSerializer` one, then add the action:

```php
  private const PAGE_TYPES = [
    'about' => 'about_page',
    'dealers' => 'dealers_page',
    'contact' => 'contact_page',
    'policies' => 'policies_page',
  ];

  /**
   * GET /api/v1/page/{key}
   */
  public function page(string $key) {
    if (!isset(self::PAGE_TYPES[$key])) {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }
    $type = self::PAGE_TYPES[$key];
    $nodes = $this->entityTypeManager()->getStorage('node')
      ->loadByProperties(['type' => $type, 'status' => 1]);
    $node = $nodes ? reset($nodes) : NULL;
    if (!$node) {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }
    return ApiEnvelope::make(
      $this->pages->{$key}($node),
      [],
      [],
      ['node_list:' . $type],
    );
  }
```

Route:

```yaml
keybolts_api.page:
  path: '/api/v1/page/{key}'
  defaults:
    _controller: '\Drupal\keybolts_api\Controller\PageController::page'
  methods: [GET]
  requirements:
    _permission: 'access content'
```

- [ ] **Step 5: Run the tests and watch them pass**

Same command as Step 2. Expected: 2 tests PASS.

- [ ] **Step 6: Verify against the live endpoint**

```bash
ddev drush cr
curl -sk "https://vietlong.ddev.site/api/v1/page/about" | python3 -c "
import json,sys
d=json.load(sys.stdin)['data']
print('title:', d['title'])
print('facts:', len(d['facts']), 'steps:', len(d['steps']), 'values:', len(d['values']), 'segments:', len(d['segments']))
print('cta:', d['ctaPrimary'])
"
curl -sk -o /dev/null -w "unknown key -> %{http_code}\n" "https://vietlong.ddev.site/api/v1/page/nonsense"
```
Expected: facts 5, steps 5, values 3, segments 4; `ctaPrimary` is `{'label': 'Gọi 1900 9018', 'url': 'tel:19009018'}`; unknown key returns `404`.

- [ ] **Step 7: Commit**

```bash
git add web/modules/custom/keybolts_api web/modules/custom/keybolts_core/tests
git commit -m "feat(api): add singleton page endpoint with per-page serializers"
```

---

### Task 9: `/gioi-thieu` page

**Interfaces:**
- Consumes: `GET /api/v1/page/about`, `GET /api/v1/branches`.
- Produces: route `/gioi-thieu`; components `PageHero`, `PageFactStrip`, `PageStoryBlock`, `PageSegmentGrid`, `PageStepList`, `PageValueGrid`, `PageBranchGrid`.

**Files:**
- Create: `frontend/app/components/page/{Hero,FactStrip,StoryBlock,SegmentGrid,StepList,ValueGrid,BranchGrid}.vue`
- Create: `frontend/app/pages/gioi-thieu.vue`
- Modify: `frontend/app/types/page.ts`, `frontend/app/services/pages.ts`

> Re-read `design/Keybolts About.html` immediately before building. Take the **last** occurrence of any token.

- [ ] **Step 1: Add the types**

Append to `frontend/app/types/page.ts`:

```ts
export interface CtaLink { label: string; url: string }
export interface Fact { number: string; label: string }
export interface NumberedItem { number: string; title: string; desc: string }
export interface Segment { title: string; desc: string; ctaLabel?: string; ctaUrl?: string; image?: string }
export interface ValueItem { title: string; desc: string }

export interface AboutPage {
  eyebrow: string
  title: string
  subtitle: string
  heroImage: string
  heroCaption: string
  ctaPrimary: CtaLink
  ctaSecondary: CtaLink
  facts: Fact[]
  storyEyebrow: string
  storyTitle: string
  storyBody: string
  credentials: string[]
  segments: Segment[]
  steps: NumberedItem[]
  values: ValueItem[]
}
```

Append to `frontend/app/services/pages.ts`:

```ts
import type { AboutPage } from '~/types/page'

export const fetchPage = <T>(key: string) => apiFetch<T>(`/page/${key}`)
export const fetchAbout = () => fetchPage<AboutPage>('about')
```

- [ ] **Step 2: Add the grid classes**

Nested parentheses in Tailwind arbitrary values generate nothing. Append inside `@layer components` in `frontend/app/assets/css/tokens.css`:

```css
  .kb-fact-strip {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  }

  .kb-segment-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  }
```

- [ ] **Step 3: Build the shared blocks**

`frontend/app/components/page/Hero.vue`:

```vue
<script setup lang="ts">
import type { CtaLink } from '~/types/page'

defineProps<{
  eyebrow: string
  title: string
  subtitle: string
  image?: string
  caption?: string
  ctaPrimary?: CtaLink
  ctaSecondary?: CtaLink
  breadcrumb: { label: string; url: string }[]
}>()

const isExternal = (url: string) => /^(https?:|tel:|mailto:)/.test(url)
</script>

<template>
  <section class="bg-charcoal-900 text-white">
    <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] pt-6">
      <LayoutBreadcrumb :items="breadcrumb" />
    </div>

    <div class="mx-auto grid max-w-[var(--container-max)] gap-10 px-[clamp(20px,4vw,48px)] py-14 lg:grid-cols-2">
      <div class="flex flex-col justify-center gap-5">
        <span class="text-eyebrow text-gold-200 font-bold tracking-[0.24em] uppercase">{{ eyebrow }}</span>
        <h1 class="text-display-lg m-0 font-bold tracking-[-0.03em]">{{ title }}</h1>
        <p class="text-heading m-0 leading-relaxed font-light text-white/75">{{ subtitle }}</p>

        <div v-if="ctaPrimary?.url || ctaSecondary?.url" class="mt-2 flex flex-wrap gap-4">
          <component
            :is="isExternal(ctaPrimary!.url) ? 'a' : resolveComponent('NuxtLink')"
            v-if="ctaPrimary?.url"
            :href="isExternal(ctaPrimary.url) ? ctaPrimary.url : undefined"
            :to="isExternal(ctaPrimary.url) ? undefined : ctaPrimary.url"
            class="text-body rounded-sm bg-gold-200 px-8 py-4 font-bold tracking-[0.06em] text-charcoal-900 uppercase no-underline hover:bg-white"
          >{{ ctaPrimary.label }}</component>

          <component
            :is="isExternal(ctaSecondary!.url) ? 'a' : resolveComponent('NuxtLink')"
            v-if="ctaSecondary?.url"
            :href="isExternal(ctaSecondary.url) ? ctaSecondary.url : undefined"
            :to="isExternal(ctaSecondary.url) ? undefined : ctaSecondary.url"
            class="text-body rounded-sm border border-white/30 px-8 py-4 tracking-[0.06em] text-white uppercase no-underline hover:border-gold-200 hover:text-gold-200"
          >{{ ctaSecondary.label }}</component>
        </div>
      </div>

      <div v-if="image" class="flex flex-col gap-3">
        <img :src="image" :alt="caption || title" class="w-full object-cover" fetchpriority="high">
        <span v-if="caption" class="text-caption text-white/60">{{ caption }}</span>
      </div>
    </div>
  </section>
</template>
```

`frontend/app/components/page/FactStrip.vue`:

```vue
<script setup lang="ts">
import type { Fact } from '~/types/page'
defineProps<{ facts: Fact[] }>()
</script>

<template>
  <div class="border-b border-border bg-surface">
    <div class="kb-fact-strip mx-auto max-w-[var(--container-max)] gap-8 px-[clamp(20px,4vw,48px)] py-10">
      <div v-for="f in facts" :key="f.label" class="flex flex-col gap-1">
        <span class="text-display-lg text-brass-700 leading-none font-bold tracking-[-0.03em]">{{ f.number }}</span>
        <span class="text-caption text-text-muted">{{ f.label }}</span>
      </div>
    </div>
  </div>
</template>
```

`frontend/app/components/page/StoryBlock.vue`:

```vue
<script setup lang="ts">
defineProps<{ eyebrow: string; title: string; body: string; credentials: string[] }>()
</script>

<template>
  <section class="mx-auto grid max-w-[var(--container-max)] gap-10 px-[clamp(20px,4vw,48px)] py-16 lg:grid-cols-2">
    <div>
      <span class="text-eyebrow text-brass-700 font-bold tracking-[0.24em] uppercase">{{ eyebrow }}</span>
      <h2 class="text-display-lg mt-3 font-bold tracking-[-0.03em]">{{ title }}</h2>
    </div>
    <div class="flex flex-col gap-6">
      <!-- Pre-rendered by Drupal's text format. -->
      <div class="text-body kb-prose leading-relaxed text-text-muted" v-html="body" />
      <ul class="m-0 flex list-none flex-col gap-3 p-0">
        <li v-for="c in credentials" :key="c" class="text-body flex gap-3 border-b border-border pb-3">
          <span class="text-brass-700 font-bold">✓</span>{{ c }}
        </li>
      </ul>
    </div>
  </section>
</template>
```

`frontend/app/components/page/SegmentGrid.vue`:

```vue
<script setup lang="ts">
import type { Segment } from '~/types/page'
defineProps<{ eyebrow: string; title: string; segments: Segment[] }>()
</script>

<template>
  <section class="bg-surface">
    <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] py-16">
      <span class="text-eyebrow text-brass-700 font-bold tracking-[0.24em] uppercase">{{ eyebrow }}</span>
      <h2 class="text-display-lg mt-3 mb-10 font-bold tracking-[-0.03em]">{{ title }}</h2>

      <div class="kb-segment-grid gap-[clamp(16px,1.6vw,24px)]">
        <NuxtLink
          v-for="s in segments"
          :key="s.title"
          :to="s.ctaUrl || '/san-pham'"
          class="flex flex-col gap-3 border border-border bg-background p-6 text-inherit no-underline transition hover:-translate-y-1 hover:border-brass-500 hover:shadow-floating"
        >
          <img v-if="s.image" :src="s.image" :alt="s.title" class="aspect-[4/3] w-full object-cover" loading="lazy">
          <span class="text-heading font-bold">{{ s.title }}</span>
          <span class="text-caption text-text-muted leading-relaxed">{{ s.desc }}</span>
          <span v-if="s.ctaLabel" class="text-caption text-brass-700 mt-auto pt-3 font-bold">{{ s.ctaLabel }} →</span>
        </NuxtLink>
      </div>
    </div>
  </section>
</template>
```

`frontend/app/components/page/StepList.vue`:

```vue
<script setup lang="ts">
import type { NumberedItem } from '~/types/page'
defineProps<{ eyebrow: string; title: string; intro?: string; steps: NumberedItem[] }>()
</script>

<template>
  <section class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] py-16">
    <span class="text-eyebrow text-brass-700 font-bold tracking-[0.24em] uppercase">{{ eyebrow }}</span>
    <h2 class="text-display-lg mt-3 font-bold tracking-[-0.03em]">{{ title }}</h2>
    <p v-if="intro" class="text-heading text-text-muted mt-4 max-w-[720px] font-light leading-relaxed">{{ intro }}</p>

    <ol class="m-0 mt-10 flex list-none flex-col gap-px bg-border p-0">
      <li v-for="s in steps" :key="s.number" class="flex gap-6 bg-background p-6">
        <span class="text-display text-brass-700 font-bold tracking-[-0.03em]">{{ s.number }}</span>
        <span class="flex flex-col gap-2">
          <span class="text-heading font-bold">{{ s.title }}</span>
          <span class="text-body text-text-muted leading-relaxed">{{ s.desc }}</span>
        </span>
      </li>
    </ol>
  </section>
</template>
```

`frontend/app/components/page/ValueGrid.vue`:

```vue
<script setup lang="ts">
import type { ValueItem } from '~/types/page'
defineProps<{ eyebrow: string; title: string; values: ValueItem[] }>()
</script>

<template>
  <section class="bg-charcoal-900 text-white">
    <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] py-16">
      <span class="text-eyebrow text-gold-200 font-bold tracking-[0.24em] uppercase">{{ eyebrow }}</span>
      <h2 class="text-display-lg mt-3 mb-10 font-bold tracking-[-0.03em]">{{ title }}</h2>

      <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
        <div v-for="v in values" :key="v.title" class="flex flex-col gap-3 border border-white/12 p-6">
          <span class="text-heading text-gold-200 font-bold">{{ v.title }}</span>
          <span class="text-body leading-relaxed text-white/72">{{ v.desc }}</span>
        </div>
      </div>
    </div>
  </section>
</template>
```

`frontend/app/components/page/BranchGrid.vue`:

```vue
<script setup lang="ts">
import type { Branch } from '~/types/page'
defineProps<{ eyebrow: string; title: string; branches: Branch[] }>()
</script>

<template>
  <section class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] py-16">
    <span class="text-eyebrow text-brass-700 font-bold tracking-[0.24em] uppercase">{{ eyebrow }}</span>
    <h2 class="text-display-lg mt-3 mb-10 font-bold tracking-[-0.03em]">{{ title }}</h2>

    <div class="grid grid-cols-1 gap-px border border-border bg-border sm:grid-cols-2 lg:grid-cols-3">
      <div v-for="b in branches" :key="b.id" class="flex flex-col gap-3 bg-background p-[26px]">
        <span class="text-caption text-brass-700 font-bold tracking-[0.14em] uppercase">{{ b.tag }}</span>
        <span class="text-heading font-bold">{{ b.name }}</span>
        <span class="text-caption text-text-muted leading-relaxed">{{ b.address }}</span>
        <a :href="`tel:${b.phoneTel}`" class="text-body text-brass-700 font-bold no-underline">{{ b.phoneDisplay }}</a>
        <a
          v-if="b.mapUrl"
          :href="b.mapUrl"
          rel="noopener"
          class="text-caption text-text-muted mt-auto pt-2 no-underline hover:text-brass-700"
        >Chỉ đường →</a>
      </div>
    </div>
  </section>
</template>
```

- [ ] **Step 4: Build the page**

`frontend/app/pages/gioi-thieu.vue`:

```vue
<script setup lang="ts">
import { fetchAbout, fetchBranches } from '~/services/pages'

const { data } = await useAsyncData('page:about', () => fetchAbout())
const { data: branchData } = await useAsyncData('branches', () => fetchBranches())

const page = computed(() => data.value?.data)
const branches = computed(() => branchData.value?.data ?? [])

if (!page.value) {
  throw createError({ statusCode: 404, statusMessage: 'Không tìm thấy trang', fatal: true })
}

useSeoMeta({
  title: () => `${page.value?.title} | Keybolts`,
  description: () => page.value?.subtitle,
})

useHead({ link: [{ rel: 'canonical', href: 'https://keybolts.com.vn/gioi-thieu' }] })

const breadcrumb = [
  { label: 'Trang chủ', url: '/' },
  { label: 'Giới thiệu', url: '/gioi-thieu' },
]
</script>

<template>
  <div v-if="page">
    <PageHero
      :eyebrow="page.eyebrow"
      :title="page.title"
      :subtitle="page.subtitle"
      :image="page.heroImage"
      :caption="page.heroCaption"
      :cta-primary="page.ctaPrimary"
      :cta-secondary="page.ctaSecondary"
      :breadcrumb="breadcrumb"
    />
    <PageFactStrip :facts="page.facts" />
    <PageStoryBlock
      :eyebrow="page.storyEyebrow"
      :title="page.storyTitle"
      :body="page.storyBody"
      :credentials="page.credentials"
    />
    <PageSegmentGrid eyebrow="Khách hàng" title="Keybolts phục vụ ai?" :segments="page.segments" />
    <PageStepList
      eyebrow="Quy trình"
      title="Từ tư vấn đến bảo hành"
      intro="Năm bước rõ ràng — bạn biết chính xác điều gì sẽ diễn ra sau khi bấm gọi."
      :steps="page.steps"
    />
    <PageValueGrid eyebrow="Cam kết" title="Điều Keybolts đảm bảo bằng văn bản" :values="page.values" />
    <PageBranchGrid eyebrow="Hệ thống" title="Showroom &amp; kho hàng" :branches="branches" />
  </div>
</template>
```

- [ ] **Step 5: Verify by content, not status code**

```bash
curl -s "http://localhost:3100/gioi-thieu" > /tmp/about.html
for s in "Về Keybolts" "Ổ khóa không chỉ để an toàn" "Từ tư vấn đến bảo hành" "Showroom Từ Sơn" "2014"; do
  printf "  %-34s %s\n" "$s" "$(grep -c "$s" /tmp/about.html)"
done
```
Expected: every line at least `1`. If a new utility class does not apply, restart `npm run dev`.

- [ ] **Step 6: Commit**

```bash
git add frontend/app
git commit -m "feat(frontend): add Giới thiệu page from CMS content"
```

---

### Task 10: `dealers_page` and `/dai-ly`

**Interfaces:**
- Consumes: `numbered_item` from Task 7, `PageLeadForm` from Task 6, `PageBranchGrid` from Task 9.
- Produces: content type `dealers_page`; route `/dai-ly`; component `PageCriteriaList`.

**Files:**
- Modify: `scripts/setup/install_page_model.php`
- Create: `scripts/seed/seed_dealers.php`, `frontend/app/components/page/CriteriaList.vue`, `frontend/app/pages/dai-ly.vue`
- Modify: `frontend/app/types/page.ts`, `frontend/app/services/pages.ts`

> Re-read `design/Keybolts Dealers.html` first.

- [ ] **Step 1: Add the model**

Append to `scripts/setup/install_page_model.php`:

```php
kbp_node_type('dealers_page', 'Trang Đại lý');
kbp_field('node', 'dealers_page', 'field_eyebrow', 'string', 'Eyebrow');
kbp_field('node', 'dealers_page', 'field_subtitle', 'string_long', 'Mô tả ngắn');
kbp_field('node', 'dealers_page', 'field_criteria', 'string', 'Điều kiện', -1);
kbp_field('node', 'dealers_page', 'field_form_title', 'string', 'Form — tiêu đề');
kbp_field('node', 'dealers_page', 'field_form_desc', 'string_long', 'Form — mô tả');
kbp_field('node', 'dealers_page', 'field_success_title', 'string', 'Form — tiêu đề thành công');
kbp_field('node', 'dealers_page', 'field_success_desc', 'string_long', 'Form — mô tả thành công');
kbp_paragraph_ref('dealers_page', 'field_benefits', 'Quyền lợi', ['numbered_item']);
```

- [ ] **Step 2: Seed it**

`scripts/seed/seed_dealers.php`:

```php
<?php

/**
 * @file
 * Seeds the single Đại lý node. Safe to run repeatedly.
 */

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

$storage = \Drupal::entityTypeManager()->getStorage('node');
$existing = $storage->loadByProperties(['type' => 'dealers_page']);
$node = $existing ? reset($existing) : Node::create(['type' => 'dealers_page']);

$node->setTitle('Trở thành đại lý Keybolts');
$node->set('field_eyebrow', 'Hợp tác');
$node->set('field_subtitle', 'Chính sách giá riêng cho đại lý, nhà thầu và cửa hàng vật liệu — hỗ trợ hàng mẫu, catalogue và kỹ thuật bán hàng.');
$node->set('field_criteria', [
  'Cửa hàng vật liệu xây dựng, nội thất hoặc kim khí đang kinh doanh',
  'Nhà thầu, đơn vị thi công cửa và nội thất',
  'Có kho hoặc mặt bằng trưng bày sản phẩm',
  'Cam kết doanh số tối thiểu theo cấp đại lý',
]);
$node->set('field_form_title', 'Đăng ký làm đại lý');
$node->set('field_form_desc', 'Điền thông tin, bộ phận kinh doanh sẽ gửi bảng giá đại lý và chính sách hợp tác.');
$node->set('field_success_title', 'Đã nhận thông tin!');
$node->set('field_success_desc', 'Keybolts sẽ liên hệ trong 24 giờ làm việc.');

$benefits = [
  ['01', 'Giá đại lý theo cấp', 'Chiết khấu theo doanh số và cấp đại lý, có bảng giá riêng cập nhật hàng quý.'],
  ['02', 'Hỗ trợ hàng mẫu', 'Cấp hàng mẫu và kệ trưng bày cho đại lý có showroom.'],
  ['03', 'Bảo vệ khu vực', 'Hạn chế số lượng đại lý trên cùng địa bàn để tránh cạnh tranh giá.'],
  ['04', 'Đào tạo kỹ thuật', 'Hướng dẫn lắp đặt, xử lý bảo hành và tư vấn chọn khóa cho nhân viên bán hàng.'],
];
$values = [];
foreach ($benefits as [$n, $title, $desc]) {
  $p = Paragraph::create([
    'type' => 'numbered_item',
    'field_item_number' => $n,
    'field_item_title' => $title,
    'field_item_desc' => $desc,
  ]);
  $p->save();
  $values[] = ['target_id' => $p->id(), 'target_revision_id' => $p->getRevisionId()];
}
$node->set('field_benefits', $values);

$node->setPublished()->save();
echo ($existing ? 'updated' : 'created') . " dealers_page node {$node->id()}\n";
```

- [ ] **Step 3: Run both scripts and verify**

```bash
ddev drush php:script scripts/setup/install_page_model.php
ddev drush php:script scripts/seed/seed_dealers.php
ddev drush cr
curl -sk "https://vietlong.ddev.site/api/v1/page/dealers" | python3 -c "
import json,sys
d=json.load(sys.stdin)['data']
print('benefits', len(d['benefits']), '| criteria', len(d['criteria']))
print(d['benefits'][0])
"
```
Expected: `benefits 4 | criteria 4`, first benefit `{'number': '01', 'title': 'Giá đại lý theo cấp', ...}`.

- [ ] **Step 4: Add the type and service**

Append to `frontend/app/types/page.ts`:

```ts
export interface DealersPage {
  eyebrow: string
  title: string
  subtitle: string
  benefits: NumberedItem[]
  criteria: string[]
  formTitle: string
  formDesc: string
  successTitle: string
  successDesc: string
}
```

Append to `frontend/app/services/pages.ts`:

```ts
import type { DealersPage } from '~/types/page'
export const fetchDealers = () => fetchPage<DealersPage>('dealers')
```

- [ ] **Step 5: Build `CriteriaList` and the page**

`frontend/app/components/page/CriteriaList.vue`:

```vue
<script setup lang="ts">
defineProps<{ eyebrow: string; title: string; items: string[] }>()
</script>

<template>
  <div class="flex flex-col gap-5">
    <span class="text-eyebrow text-brass-700 font-bold tracking-[0.24em] uppercase">{{ eyebrow }}</span>
    <h2 class="text-display m-0 font-bold tracking-[-0.02em]">{{ title }}</h2>
    <ul class="m-0 flex list-none flex-col gap-3 p-0">
      <li v-for="c in items" :key="c" class="text-body flex gap-3 border-b border-border pb-3 leading-relaxed">
        <span class="text-brass-700 font-bold">✓</span>{{ c }}
      </li>
    </ul>
  </div>
</template>
```

`frontend/app/pages/dai-ly.vue`:

```vue
<script setup lang="ts">
import { fetchBranches, fetchDealers } from '~/services/pages'

const { data } = await useAsyncData('page:dealers', () => fetchDealers())
const { data: branchData } = await useAsyncData('branches', () => fetchBranches())

const page = computed(() => data.value?.data)
const branches = computed(() => branchData.value?.data ?? [])

if (!page.value) {
  throw createError({ statusCode: 404, statusMessage: 'Không tìm thấy trang', fatal: true })
}

useSeoMeta({
  title: () => `${page.value?.title} | Keybolts`,
  description: () => page.value?.subtitle,
})
useHead({ link: [{ rel: 'canonical', href: 'https://keybolts.com.vn/dai-ly' }] })
</script>

<template>
  <div v-if="page">
    <PageHero
      :eyebrow="page.eyebrow"
      :title="page.title"
      :subtitle="page.subtitle"
      :breadcrumb="[
        { label: 'Trang chủ', url: '/' },
        { label: 'Đại lý', url: '/dai-ly' },
      ]"
    />

    <PageStepList eyebrow="Quyền lợi" title="Keybolts hỗ trợ đại lý những gì" :steps="page.benefits" />

    <section class="bg-surface">
      <div class="mx-auto grid max-w-[var(--container-max)] gap-10 px-[clamp(20px,4vw,48px)] py-16 lg:grid-cols-2">
        <PageCriteriaList eyebrow="Điều kiện" title="Ai có thể làm đại lý?" :items="page.criteria" />
        <PageLeadForm
          source="dealer"
          :title="page.formTitle"
          :desc="page.formDesc"
          :success-title="page.successTitle"
          :success-desc="page.successDesc"
        />
      </div>
    </section>

    <PageBranchGrid eyebrow="Hệ thống" title="Điểm bán &amp; kho hàng" :branches="branches" />
  </div>
</template>
```

- [ ] **Step 6: Verify and commit**

```bash
curl -s "http://localhost:3100/dai-ly" | grep -c "Ai có thể làm đại lý?"
curl -s "http://localhost:3100/dai-ly" | grep -c "Giá đại lý theo cấp"
git add frontend/app scripts config/sync
git commit -m "feat(frontend): add Đại lý page with dealer registration form"
```
Expected: both counts at least `1`.

---

### Task 11: `contact_page` and `/lien-he`

**Interfaces:**
- Produces: paragraph type `contact_channel`; content type `contact_page`; route `/lien-he`; component `PageContactChannels`.

**Files:**
- Modify: `scripts/setup/install_page_model.php`
- Create: `scripts/seed/seed_contact.php`, `frontend/app/components/page/ContactChannels.vue`, `frontend/app/pages/lien-he.vue`
- Modify: `frontend/app/types/page.ts`, `frontend/app/services/pages.ts`

> Re-read `design/Keybolts Contact.html` first.

- [ ] **Step 1: Add the model**

```php
kbp_paragraph('contact_channel', 'Kênh liên hệ');
kbp_field('paragraph', 'contact_channel', 'field_ch_label', 'string', 'Nhãn');
kbp_field('paragraph', 'contact_channel', 'field_ch_value', 'string', 'Giá trị');
kbp_field('paragraph', 'contact_channel', 'field_ch_note', 'string', 'Ghi chú');
kbp_field('paragraph', 'contact_channel', 'field_ch_url', 'link', 'Liên kết');

kbp_node_type('contact_page', 'Trang Liên hệ');
kbp_field('node', 'contact_page', 'field_eyebrow', 'string', 'Eyebrow');
kbp_field('node', 'contact_page', 'field_subtitle', 'string_long', 'Mô tả ngắn');
kbp_field('node', 'contact_page', 'field_company_name', 'string', 'Tên công ty');
kbp_field('node', 'contact_page', 'field_company_address', 'string_long', 'Địa chỉ trụ sở');
kbp_field('node', 'contact_page', 'field_response_title', 'string', 'Khối phản hồi — tiêu đề');
kbp_field('node', 'contact_page', 'field_response_body', 'string_long', 'Khối phản hồi — nội dung');
kbp_field('node', 'contact_page', 'field_form_title', 'string', 'Form — tiêu đề');
kbp_field('node', 'contact_page', 'field_form_desc', 'string_long', 'Form — mô tả');
kbp_field('node', 'contact_page', 'field_success_title', 'string', 'Form — tiêu đề thành công');
kbp_field('node', 'contact_page', 'field_success_desc', 'string_long', 'Form — mô tả thành công');
kbp_paragraph_ref('contact_page', 'field_channels', 'Kênh liên hệ', ['contact_channel']);
```

- [ ] **Step 2: Seed it**

`scripts/seed/seed_contact.php`:

```php
<?php

/**
 * @file
 * Seeds the single Liên hệ node. Safe to run repeatedly.
 */

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

$storage = \Drupal::entityTypeManager()->getStorage('node');
$existing = $storage->loadByProperties(['type' => 'contact_page']);
$node = $existing ? reset($existing) : Node::create(['type' => 'contact_page']);

$node->setTitle('Kết nối với Keybolts');
$node->set('field_eyebrow', 'Liên hệ');
$node->set('field_subtitle', 'Gọi hotline, nhắn Zalo hoặc để lại thông tin — đội kỹ thuật và kinh doanh trực từ 8:00 đến 18:00 tất cả các ngày trong tuần.');
$node->set('field_company_name', 'Công ty TNHH XNK Khóa Cửa Việt Long');
$node->set('field_company_address', 'Trụ sở: Khu phố Lê Hồng Phong, P. Đông Ngàn, TP. Từ Sơn, Bắc Ninh');
$node->set('field_response_title', 'Chúng tôi trả lời trong 24 giờ');
$node->set('field_response_body', 'Nếu bạn đang chọn khóa cho công trình, hãy nêu rõ loại cửa, độ dày cánh và số lượng — Keybolts sẽ gửi phương án và báo giá phù hợp.');
$node->set('field_form_title', 'Form liên hệ');
$node->set('field_form_desc', 'Điền thông tin bên dưới, Keybolts sẽ liên hệ lại theo số điện thoại bạn cung cấp.');
$node->set('field_success_title', 'Đã nhận thông tin!');
$node->set('field_success_desc', 'Keybolts sẽ liên hệ trong 24 giờ làm việc.');

$channels = [
  ['Hotline', '1900 9018', '8:00 – 18:00, cả tuần', 'tel:19009018'],
  ['Zalo', '1900 9018', 'Gửi ảnh cửa để được tư vấn nhanh', 'https://zalo.me/19009018'],
  ['Email', 'khoacuavietlong@gmail.com', 'Báo giá công trình & hợp tác', 'mailto:khoacuavietlong@gmail.com'],
];
$values = [];
foreach ($channels as [$label, $value, $note, $uri]) {
  $p = Paragraph::create([
    'type' => 'contact_channel',
    'field_ch_label' => $label,
    'field_ch_value' => $value,
    'field_ch_note' => $note,
    'field_ch_url' => ['uri' => $uri, 'title' => $value],
  ]);
  $p->save();
  $values[] = ['target_id' => $p->id(), 'target_revision_id' => $p->getRevisionId()];
}
$node->set('field_channels', $values);

$node->setPublished()->save();
echo ($existing ? 'updated' : 'created') . " contact_page node {$node->id()}\n";
```

- [ ] **Step 3: Run and verify the payload**

```bash
ddev drush php:script scripts/setup/install_page_model.php
ddev drush php:script scripts/seed/seed_contact.php
ddev drush cr
curl -sk "https://vietlong.ddev.site/api/v1/page/contact" | python3 -c "
import json,sys
d=json.load(sys.stdin)['data']
print('channels', len(d['channels']))
for c in d['channels']: print(' ', c['label'], c['value'], c.get('ctaUrl'))
"
```
Expected: `channels 3`; the Hotline row carries `ctaUrl` `tel:19009018`.

- [ ] **Step 4: Add the type and service**

```ts
export interface ContactChannel {
  label: string
  value: string
  note: string
  ctaLabel?: string
  ctaUrl?: string
}

export interface ContactPage {
  eyebrow: string
  title: string
  subtitle: string
  channels: ContactChannel[]
  companyName: string
  companyAddress: string
  responseTitle: string
  responseBody: string
  formTitle: string
  formDesc: string
  successTitle: string
  successDesc: string
}
```

```ts
import type { ContactPage } from '~/types/page'
export const fetchContact = () => fetchPage<ContactPage>('contact')
```

- [ ] **Step 5: Build `ContactChannels` and the page**

`frontend/app/components/page/ContactChannels.vue`:

```vue
<script setup lang="ts">
import type { ContactChannel } from '~/types/page'
defineProps<{ channels: ContactChannel[] }>()
</script>

<template>
  <div class="grid grid-cols-1 gap-px border border-border bg-border md:grid-cols-3">
    <a
      v-for="c in channels"
      :key="c.label"
      :href="c.ctaUrl || '#'"
      rel="noopener"
      class="flex flex-col gap-2 bg-background p-6 text-inherit no-underline transition hover:bg-surface"
    >
      <span class="text-caption text-brass-700 font-bold tracking-[0.14em] uppercase">{{ c.label }}</span>
      <span class="text-heading font-bold">{{ c.value }}</span>
      <span class="text-caption text-text-muted leading-relaxed">{{ c.note }}</span>
    </a>
  </div>
</template>
```

`frontend/app/pages/lien-he.vue`:

```vue
<script setup lang="ts">
import { fetchBranches, fetchContact } from '~/services/pages'

const { data } = await useAsyncData('page:contact', () => fetchContact())
const { data: branchData } = await useAsyncData('branches', () => fetchBranches())

const page = computed(() => data.value?.data)
const branches = computed(() => branchData.value?.data ?? [])

if (!page.value) {
  throw createError({ statusCode: 404, statusMessage: 'Không tìm thấy trang', fatal: true })
}

useSeoMeta({
  title: () => `${page.value?.title} | Keybolts`,
  description: () => page.value?.subtitle,
})
useHead({ link: [{ rel: 'canonical', href: 'https://keybolts.com.vn/lien-he' }] })
</script>

<template>
  <div v-if="page">
    <PageHero
      :eyebrow="page.eyebrow"
      :title="page.title"
      :subtitle="page.subtitle"
      :breadcrumb="[
        { label: 'Trang chủ', url: '/' },
        { label: 'Liên hệ', url: '/lien-he' },
      ]"
    />

    <div class="mx-auto max-w-[var(--container-max)] px-[clamp(20px,4vw,48px)] py-12">
      <PageContactChannels :channels="page.channels" />
    </div>

    <section class="bg-surface">
      <div class="mx-auto grid max-w-[var(--container-max)] gap-10 px-[clamp(20px,4vw,48px)] py-16 lg:grid-cols-2">
        <div class="flex flex-col gap-4">
          <h2 class="text-display m-0 font-bold tracking-[-0.02em]">{{ page.responseTitle }}</h2>
          <p class="text-body text-text-muted m-0 leading-relaxed">{{ page.responseBody }}</p>
          <span class="text-body mt-4 font-bold">{{ page.companyName }}</span>
          <span class="text-caption text-text-muted">{{ page.companyAddress }}</span>
        </div>

        <PageLeadForm
          source="contact"
          :title="page.formTitle"
          :desc="page.formDesc"
          :success-title="page.successTitle"
          :success-desc="page.successDesc"
        />
      </div>
    </section>

    <PageBranchGrid eyebrow="Địa chỉ" title="Showroom &amp; kho hàng" :branches="branches" />
  </div>
</template>
```

- [ ] **Step 6: Submit a real lead through the page and confirm it stored**

```bash
BEFORE=$(ddev drush php:eval 'print \Drupal::entityTypeManager()->getStorage("contact_submission")->getQuery()->accessCheck(FALSE)->count()->execute();')
curl -sk -X POST "https://vietlong.ddev.site/api/v1/contact" -H 'Content-Type: application/json' \
  -d '{"name":"Trần B","phone":"0968689112","message":"Từ trang liên hệ","source":"contact"}' -o /dev/null -w "status=%{http_code}\n"
AFTER=$(ddev drush php:eval 'print \Drupal::entityTypeManager()->getStorage("contact_submission")->getQuery()->accessCheck(FALSE)->count()->execute();')
echo "before=$BEFORE after=$AFTER"
```
Expected: `status=201`, and `after` is exactly one greater than `before`.

- [ ] **Step 7: Commit**

```bash
git add frontend/app scripts config/sync
git commit -m "feat(frontend): add Liên hệ page with working contact form"
```

---

### Task 12: `policies_page` and `/chinh-sach`

**Interfaces:**
- Produces: paragraph types `policy_section` and `policy_item`; content type `policies_page`; route `/chinh-sach`.

**Files:**
- Modify: `scripts/setup/install_page_model.php`
- Create: `scripts/seed/seed_policies.php`, `frontend/app/components/page/PolicyPanel.vue`, `frontend/app/pages/chinh-sach.vue`

> Re-read `design/Keybolts Policies.html` first. `POLICIES` there is a keyed map — transcribe every section.

- [ ] **Step 1: Add the model**

```php
kbp_paragraph('policy_item', 'Khoản mục chính sách');
kbp_field('paragraph', 'policy_item', 'field_pol_key', 'string', 'Tiêu đề khoản');
kbp_field('paragraph', 'policy_item', 'field_pol_value', 'string_long', 'Nội dung khoản');

kbp_paragraph('policy_section', 'Mục chính sách');
kbp_field('paragraph', 'policy_section', 'field_pol_label', 'string', 'Nhãn tab');
kbp_field('paragraph', 'policy_section', 'field_pol_eyebrow', 'string', 'Eyebrow');
kbp_field('paragraph', 'policy_section', 'field_pol_title', 'string', 'Tiêu đề');
kbp_field('paragraph', 'policy_section', 'field_pol_intro', 'string_long', 'Dẫn nhập');
kbp_field('paragraph', 'policy_section', 'field_pol_note', 'string_long', 'Ghi chú');
kbp_field(
  'paragraph', 'policy_section', 'field_pol_items', 'entity_reference_revisions', 'Khoản mục', -1,
  ['target_type' => 'paragraph'],
  ['handler' => 'default:paragraph', 'handler_settings' => [
    'target_bundles' => ['policy_item' => 'policy_item'], 'negate' => 0,
  ]],
);

kbp_node_type('policies_page', 'Trang Chính sách');
kbp_field('node', 'policies_page', 'field_eyebrow', 'string', 'Eyebrow');
kbp_field('node', 'policies_page', 'field_subtitle', 'string_long', 'Mô tả ngắn');
kbp_field('node', 'policies_page', 'field_support_title', 'string', 'Hộp hỗ trợ — tiêu đề');
kbp_field('node', 'policies_page', 'field_support_note', 'string', 'Hộp hỗ trợ — ghi chú');
kbp_paragraph_ref('policies_page', 'field_sections', 'Mục chính sách', ['policy_section']);
```

- [ ] **Step 2: Seed the warranty section**

`scripts/seed/seed_policies.php` — transcribe every section from the prototype. The warranty section below shows the exact shape; add the remaining sections from `POLICIES` the same way.

```php
<?php

/**
 * @file
 * Seeds the single Chính sách node. Safe to run repeatedly.
 */

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

const KB_POLICIES = [
  [
    'Bảo hành', 'Chính sách 01', 'Chính sách bảo hành',
    'Toàn bộ sản phẩm Keybolts được bảo hành 5–10 năm tùy dòng, áp dụng cho lỗi kỹ thuật từ nhà sản xuất.',
    'Xuất trình phiếu bảo hành hoặc hóa đơn khi yêu cầu bảo hành. Với đơn công trình, Keybolts lưu hồ sơ theo mã dự án.',
    [
      ['Thời hạn', 'Khóa cơ và khóa đồng: 10 năm. Khóa điện tử, khóa vân tay, khóa thẻ từ: 5 năm cho phần cơ, 2 năm cho bo mạch và cụm điện tử.'],
      ['Phạm vi', 'Lỗi vật liệu, lỗi cơ khí, bong tróc lớp mạ trong điều kiện sử dụng bình thường trong nhà.'],
      ['Đổi mới', 'Đổi sản phẩm mới trong 12 tháng đầu nếu lỗi phần cơ không khắc phục được.'],
      ['Không áp dụng', 'Hư hỏng do va đập, phá khóa, tự tháo lắp sai kỹ thuật, ngập nước hoặc dùng hóa chất tẩy mạnh lên bề mặt mạ.'],
    ],
  ],
  // Add the remaining sections from POLICIES in design/Keybolts Policies.html
  // using exactly this shape.
];

$storage = \Drupal::entityTypeManager()->getStorage('node');
$existing = $storage->loadByProperties(['type' => 'policies_page']);
$node = $existing ? reset($existing) : Node::create(['type' => 'policies_page']);

$node->setTitle('Chính sách Keybolts');
$node->set('field_eyebrow', 'Cam kết');
$node->set('field_subtitle', 'Bảo hành, giao hàng, đổi trả, thanh toán và bảo mật thông tin — rõ ràng bằng văn bản, áp dụng cho cả khách lẻ và đơn công trình.');
$node->set('field_support_title', 'Cần hỗ trợ?');
$node->set('field_support_note', 'Bộ phận bảo hành trực 8:00 – 18:00');

$sections = [];
foreach (KB_POLICIES as [$label, $eyebrow, $title, $intro, $note, $items]) {
  $item_values = [];
  foreach ($items as [$k, $v]) {
    $item = Paragraph::create([
      'type' => 'policy_item',
      'field_pol_key' => $k,
      'field_pol_value' => $v,
    ]);
    $item->save();
    $item_values[] = ['target_id' => $item->id(), 'target_revision_id' => $item->getRevisionId()];
  }
  $section = Paragraph::create([
    'type' => 'policy_section',
    'field_pol_label' => $label,
    'field_pol_eyebrow' => $eyebrow,
    'field_pol_title' => $title,
    'field_pol_intro' => $intro,
    'field_pol_note' => $note,
    'field_pol_items' => $item_values,
  ]);
  $section->save();
  $sections[] = ['target_id' => $section->id(), 'target_revision_id' => $section->getRevisionId()];
}
$node->set('field_sections', $sections);

$node->setPublished()->save();
echo ($existing ? 'updated' : 'created') . " policies_page node {$node->id()}\n";
```

- [ ] **Step 3: Run and verify nesting survives**

```bash
ddev drush php:script scripts/setup/install_page_model.php
ddev drush php:script scripts/seed/seed_policies.php
ddev drush cr
curl -sk "https://vietlong.ddev.site/api/v1/page/policies" | python3 -c "
import json,sys
d=json.load(sys.stdin)['data']
print('sections', len(d['sections']))
print('first section items', len(d['sections'][0]['items']))
print(d['sections'][0]['items'][0])
"
```
Expected: at least `sections 1`, `first section items 4`, and the first item is `{'k': 'Thời hạn', 'v': 'Khóa cơ và khóa đồng: 10 năm…'}`. Nested paragraphs are the risk here — if `items` is empty, the nested reference field is misconfigured.

- [ ] **Step 4: Add the type and service**

```ts
export interface PolicyItem { k: string; v: string }
export interface PolicySection {
  key: string
  label: string
  eyebrow: string
  title: string
  intro: string
  note: string
  items: PolicyItem[]
}
export interface PoliciesPage {
  eyebrow: string
  title: string
  subtitle: string
  sections: PolicySection[]
  supportTitle: string
  supportNote: string
}
```

```ts
import type { PoliciesPage } from '~/types/page'
export const fetchPolicies = () => fetchPage<PoliciesPage>('policies')
```

- [ ] **Step 5: Build the page**

`frontend/app/pages/chinh-sach.vue`:

```vue
<script setup lang="ts">
import { fetchPolicies } from '~/services/pages'

const { data } = await useAsyncData('page:policies', () => fetchPolicies())
const page = computed(() => data.value?.data)

if (!page.value) {
  throw createError({ statusCode: 404, statusMessage: 'Không tìm thấy trang', fatal: true })
}

const active = ref(page.value.sections[0]?.key ?? '')
const current = computed(() => page.value!.sections.find(s => s.key === active.value) ?? page.value!.sections[0])

useSeoMeta({
  title: () => `${page.value?.title} | Keybolts`,
  description: () => page.value?.subtitle,
})
useHead({ link: [{ rel: 'canonical', href: 'https://keybolts.com.vn/chinh-sach' }] })
</script>

<template>
  <div v-if="page">
    <PageHero
      :eyebrow="page.eyebrow"
      :title="page.title"
      :subtitle="page.subtitle"
      :breadcrumb="[
        { label: 'Trang chủ', url: '/' },
        { label: 'Chính sách', url: '/chinh-sach' },
      ]"
    />

    <div class="mx-auto grid max-w-[var(--container-max)] gap-8 px-[clamp(20px,4vw,48px)] py-12 lg:grid-cols-[260px_minmax(0,1fr)]">
      <aside class="flex flex-col gap-4">
        <nav class="flex flex-col border border-border">
          <button
            v-for="s in page.sections"
            :key="s.key"
            type="button"
            class="text-body cursor-pointer border-none border-l-2 px-5 py-3 text-left transition"
            :class="s.key === active
              ? 'border-brass-500 bg-surface text-text font-bold'
              : 'border-transparent bg-background text-text-muted hover:bg-surface'"
            @click="active = s.key"
          >{{ s.label }}</button>
        </nav>

        <div class="flex flex-col gap-2 bg-charcoal-900 p-6 text-white">
          <span class="text-caption text-gold-200 font-bold tracking-[0.14em] uppercase">
            {{ page.supportTitle }}
          </span>
          <a :href="HOTLINE_TEL" class="text-heading text-gold-200 font-bold no-underline">{{ HOTLINE }}</a>
          <span class="text-caption text-white/60">{{ page.supportNote }}</span>
        </div>
      </aside>

      <article v-if="current" class="flex flex-col gap-5">
        <span class="text-eyebrow text-brass-700 font-bold tracking-[0.18em] uppercase">{{ current.eyebrow }}</span>
        <h2 class="text-display m-0 font-bold tracking-[-0.02em]">{{ current.title }}</h2>
        <p class="text-body text-text-muted m-0 leading-relaxed">{{ current.intro }}</p>

        <dl class="m-0 flex flex-col gap-px bg-border">
          <div
            v-for="it in current.items"
            :key="it.k"
            class="grid gap-2 bg-background p-5 md:grid-cols-[190px_minmax(0,1fr)]"
          >
            <dt class="text-body font-bold">{{ it.k }}</dt>
            <dd class="text-body text-text-muted m-0 leading-relaxed">{{ it.v }}</dd>
          </div>
        </dl>

        <p v-if="current.note" class="text-caption text-text-muted m-0 border-l-2 border-brass-500 pl-4 leading-relaxed">
          {{ current.note }}
        </p>
      </article>
    </div>
  </div>
</template>
```

- [ ] **Step 6: Verify and commit**

```bash
curl -s "http://localhost:3100/chinh-sach" | grep -c "Chính sách bảo hành"
curl -s "http://localhost:3100/chinh-sach" | grep -c "Thời hạn"
git add frontend/app scripts config/sync
git commit -m "feat(frontend): add Chính sách page with nested policy sections"
```
Expected: both at least `1`.

---

### Task 13: 404 page

**Interfaces:**
- Produces: `frontend/app/error.vue` handling 404 and every other status.

**Files:**
- Create: `frontend/app/error.vue`

> The design has **no** 404 mockup. Build it from the existing system — charcoal chrome, the homepage's gold gradient heading, no new visual language.

- [ ] **Step 1: Build it**

`frontend/app/error.vue`:

```vue
<script setup lang="ts">
import type { NuxtError } from '#app'

const props = defineProps<{ error: NuxtError }>()

const isNotFound = computed(() => props.error?.statusCode === 404)

const title = computed(() => (isNotFound.value ? 'Không tìm thấy trang' : 'Đã xảy ra lỗi'))
const message = computed(() =>
  isNotFound.value
    ? 'Trang bạn tìm không tồn tại hoặc đã được chuyển đi. Thử xem danh mục sản phẩm hoặc gọi hotline để được tư vấn.'
    : 'Hệ thống đang gặp sự cố. Vui lòng thử lại sau ít phút hoặc gọi hotline để được hỗ trợ ngay.',
)

useHead({ title: () => `${title.value} | Keybolts` })

// A soft 404 that returns 200 gets the page indexed. clearError restores the
// real status by navigating rather than swallowing it.
const goHome = () => clearError({ redirect: '/' })
</script>

<template>
  <div class="flex min-h-screen flex-col bg-charcoal-900 text-white">
    <div class="mx-auto flex w-full max-w-[var(--container-max)] flex-1 flex-col justify-center gap-6 px-[clamp(20px,4vw,48px)] py-20">
      <span class="text-eyebrow text-gold-200 font-bold tracking-[0.24em] uppercase">
        Lỗi {{ error?.statusCode ?? 500 }}
      </span>

      <h1 class="text-[clamp(40px,5.4vw,72px)] m-0 leading-[1.02] font-bold tracking-[-0.035em]">
        <span class="kb-hero-gradient">{{ title }}</span>
      </h1>

      <p class="text-heading m-0 max-w-[520px] leading-relaxed font-light text-white/75">
        {{ message }}
      </p>

      <div class="mt-2 flex flex-wrap gap-4">
        <button
          type="button"
          class="text-body cursor-pointer rounded-sm bg-gold-200 px-8 py-4 font-bold tracking-[0.06em] text-charcoal-900 uppercase"
          @click="goHome"
        >Về trang chủ</button>
        <NuxtLink
          to="/san-pham"
          class="text-body rounded-sm border border-white/30 px-8 py-4 tracking-[0.06em] text-white uppercase no-underline hover:border-gold-200 hover:text-gold-200"
        >Xem sản phẩm</NuxtLink>
        <a
          :href="HOTLINE_TEL"
          class="text-body rounded-sm border border-white/30 px-8 py-4 tracking-[0.06em] text-white uppercase no-underline hover:border-gold-200 hover:text-gold-200"
        >Gọi {{ HOTLINE }}</a>
      </div>
    </div>
  </div>
</template>
```

- [ ] **Step 2: Verify the status code is a real 404, not a soft one**

```bash
curl -s -o /tmp/404.html -w "status=%{http_code}\n" "http://localhost:3100/khong-ton-tai-abc"
grep -c "Không tìm thấy trang" /tmp/404.html
grep -c "Lỗi 404" /tmp/404.html
```
Expected: `status=404` — **not** 200 — and both greps at least `1`. A 404 page served with status 200 gets indexed by search engines, which is the whole reason to check.

- [ ] **Step 3: Verify an unknown product slug also 404s**

```bash
curl -s -o /dev/null -w "%{http_code}\n" "http://localhost:3100/san-pham/khong-co-that"
curl -s -o /dev/null -w "%{http_code}\n" "http://localhost:3100/danh-muc/999999"
```
Expected: `404` for both.

- [ ] **Step 4: Commit**

```bash
git add frontend/app/error.vue
git commit -m "feat(frontend): add 404 page built from the existing design system"
```

---

### Task 14: Group the edit forms into tabs

**Interfaces:**
- Produces: `field_group` installed; vertical tabs on `about_page`, `dealers_page`, `contact_page`, `policies_page`, `branch`.

**Files:**
- Modify: `composer.json`
- Create: `scripts/setup/install_page_displays.php`

- [ ] **Step 1: Install the module**

```bash
ddev composer require drupal/field_group
ddev drush en field_group -y
ddev drush pm:list --status=enabled --filter=field_group --format=list
```
Expected: `field_group` prints. If the package cannot be resolved, skip to Step 4 and fall back to field ordering alone — this must not block the rest of the task.

- [ ] **Step 2: Write the display script**

`scripts/setup/install_page_displays.php`:

```php
<?php

/**
 * @file
 * Form displays and tab groups for the static-page content types.
 *
 * Without an entity_form_display a Drupal edit form renders none of its
 * fields — the same gap that left the product form empty.
 *
 * Run: ddev drush php:script scripts/setup/install_page_displays.php
 */

const KB_PAGE_FORMS = [
  'branch' => [
    'Thông tin' => ['field_tag' => 1, 'field_address' => 2, 'field_sort_order' => 3],
    'Liên hệ' => ['field_phone_display' => 1, 'field_phone_tel' => 2, 'field_map_url' => 3],
  ],
  'about_page' => [
    'Hero' => ['field_eyebrow' => 1, 'field_subtitle' => 2, 'field_hero_image' => 3, 'field_hero_caption' => 4, 'field_cta_primary' => 5, 'field_cta_secondary' => 6],
    'Con số' => ['field_facts' => 1],
    'Câu chuyện' => ['field_story_eyebrow' => 1, 'field_story_title' => 2, 'field_story_body' => 3, 'field_credentials' => 4],
    'Khách hàng' => ['field_segments' => 1],
    'Quy trình' => ['field_steps' => 1],
    'Cam kết' => ['field_values' => 1],
  ],
  'dealers_page' => [
    'Hero' => ['field_eyebrow' => 1, 'field_subtitle' => 2],
    'Quyền lợi' => ['field_benefits' => 1],
    'Điều kiện' => ['field_criteria' => 1],
    'Form' => ['field_form_title' => 1, 'field_form_desc' => 2, 'field_success_title' => 3, 'field_success_desc' => 4],
  ],
  'contact_page' => [
    'Hero' => ['field_eyebrow' => 1, 'field_subtitle' => 2],
    'Kênh liên hệ' => ['field_channels' => 1],
    'Công ty' => ['field_company_name' => 1, 'field_company_address' => 2, 'field_response_title' => 3, 'field_response_body' => 4],
    'Form' => ['field_form_title' => 1, 'field_form_desc' => 2, 'field_success_title' => 3, 'field_success_desc' => 4],
  ],
  'policies_page' => [
    'Hero' => ['field_eyebrow' => 1, 'field_subtitle' => 2],
    'Mục chính sách' => ['field_sections' => 1],
    'Hỗ trợ' => ['field_support_title' => 1, 'field_support_note' => 2],
  ],
];

/** Picks a sensible widget from the field's type. */
function kbp_widget(string $type): string {
  return match ($type) {
    'string_long' => 'string_textarea',
    'text_long' => 'text_textarea',
    'image' => 'image_image',
    'link' => 'link_default',
    'integer' => 'number',
    'boolean' => 'boolean_checkbox',
    'entity_reference_revisions' => 'paragraphs',
    default => 'string_textfield',
  };
}

$etm = \Drupal::entityTypeManager();
$field_manager = \Drupal::service('entity_field.manager');
$has_group = \Drupal::moduleHandler()->moduleExists('field_group');

foreach (KB_PAGE_FORMS as $bundle => $groups) {
  $id = "node.{$bundle}.default";
  $display = $etm->getStorage('entity_form_display')->load($id)
    ?: $etm->getStorage('entity_form_display')->create([
      'targetEntityType' => 'node', 'bundle' => $bundle, 'mode' => 'default', 'status' => TRUE,
    ]);

  $defs = $field_manager->getFieldDefinitions('node', $bundle);
  $display->setComponent('title', ['type' => 'string_textfield', 'weight' => 0]);
  $display->setComponent('status', ['type' => 'boolean_checkbox', 'weight' => 90]);

  $group_weight = 1;
  foreach ($groups as $label => $fields) {
    $children = [];
    foreach ($fields as $field => $weight) {
      if (!isset($defs[$field])) {
        echo "  SKIP missing {$bundle}.{$field}\n";
        continue;
      }
      $display->setComponent($field, [
        'type' => kbp_widget($defs[$field]->getType()),
        'weight' => $weight,
      ]);
      $children[] = $field;
    }
    if ($has_group && $children) {
      $group_name = 'group_' . preg_replace('/[^a-z0-9]+/', '_', mb_strtolower($label));
      $display->setThirdPartySetting('field_group', $group_name, [
        'children' => $children,
        'label' => $label,
        'parent_name' => '',
        'region' => 'content',
        'weight' => $group_weight++,
        'format_type' => 'tab',
        'format_settings' => ['formatter' => 'closed', 'description' => ''],
      ]);
    }
  }
  $display->save();
  echo "form display: {$bundle}" . ($has_group ? ' (tabs)' : ' (flat — field_group missing)') . "\n";
}

echo "\nDone.\n";
```

- [ ] **Step 3: Run it and confirm the tabs render**

```bash
ddev drush php:script scripts/setup/install_page_displays.php
ddev drush cr
ddev drush uli --uri=https://vietlong.ddev.site "/admin/content"
```
Open a Giới thiệu node's edit form. Expected: six tabs — Hero, Con số, Câu chuyện, Khách hàng, Quy trình, Cam kết — each holding its fields, and every field populated with the seeded copy.

- [ ] **Step 4: Export config and commit**

```bash
ddev drush cex -y
git add composer.json composer.lock config/sync scripts/setup/install_page_displays.php
git commit -m "feat(admin): group the static-page edit forms into tabs"
```

---

### Task 15: Final verification

**Files:** none — this task only verifies.

- [ ] **Step 1: Every route answers, and unknown ones 404**

```bash
for p in / /san-pham /gioi-thieu /dai-ly /lien-he /chinh-sach; do
  printf "  %-16s %s\n" "$p" "$(curl -sk -o /dev/null -m 60 -w '%{http_code}' "https://vietlong.ddev.site$p")"
done
for p in /khong-ton-tai /danh-muc/999999 /san-pham/khong-co; do
  printf "  %-20s %s (want 404)\n" "$p" "$(curl -sk -o /dev/null -m 60 -w '%{http_code}' "https://vietlong.ddev.site$p")"
done
```
Expected: six `200`s then three `404`s.

- [ ] **Step 2: No broken links remain in the header or footer**

```bash
curl -sk "https://vietlong.ddev.site/" | grep -oE 'href="/[a-z0-9/-]*"' | sort -u | sed 's/href="//;s/"//' | while read -r p; do
  code=$(curl -sk -o /dev/null -m 30 -w '%{http_code}' "https://vietlong.ddev.site$p")
  [ "$code" = "200" ] || echo "  BROKEN $p -> $code"
done
echo "done"
```
Expected: only `done`. Any line beginning `BROKEN` is a nav or footer link with no page behind it — fix it or point it somewhere real.

- [ ] **Step 3: All tests pass**

```bash
ddev exec "cd /var/www/html && SIMPLETEST_DB=mysql://db:db@db/db vendor/bin/phpunit -c web/core/phpunit.xml.dist web/modules/custom --no-coverage"
cd frontend && npm test
```
Expected: PHPUnit green (21 existing + the new Branch, Page and ContactSubmission tests); Vitest green (9 existing + 5 lead-form tests).

- [ ] **Step 4: Production build is clean**

```bash
cd frontend && npm run build
grep -ro "fonts.gstatic.com" .output/public/_nuxt/*.css | wc -l
```
Expected: build succeeds; `0` external font requests.

- [ ] **Step 5: No horizontal overflow at any width**

Load `/gioi-thieu`, `/dai-ly`, `/lien-he`, `/chinh-sach` and a 404 at 375, 768 and 1440px. For each, confirm in the browser console:

```js
document.documentElement.scrollWidth <= document.documentElement.clientWidth + 1
```
Expected: `true` every time.

- [ ] **Step 6: The lead form works end to end from the browser**

Open `/lien-he`, fill in name and phone, submit. Expected: the success panel replaces the form, and the new row appears at the top of `/admin/keybolts/submissions`.

- [ ] **Step 7: Walk the spec's acceptance list**

Check every box in section 7 of `docs/superpowers/specs/2026-08-02-keybolts-static-pages-design.md`.

- [ ] **Step 8: Commit any fixes**

```bash
git add -A
git commit -m "test: final verification pass for the static pages"
```

---

## Self-Review Notes

**Spec coverage.** Every section of the spec maps to a task: content model §3.1 → Task 1, §3.2–3.3 → Tasks 7, 10, 11, 12, §3.4 → Task 4, §3.5 → Task 14; API §4 → Tasks 2, 5, 8; frontend §5 → Tasks 3, 6, 9, 10, 11, 12, 13; testing §6 → the test steps in each task plus Task 15.

**Deliberate ordering.** `branch` comes first because four later pages read it and the homepage's duplicated copy has to go. The lead form (Task 6) precedes both pages that embed it. `field_group` is deliberately last: it is cosmetic, and a missing contrib package must not block the pages.

**Known risks.**
- Nested paragraphs (`policy_section` → `policy_item`) are the most fragile piece; Task 12 Step 3 checks the nesting explicitly rather than trusting a 200.
- `error.vue` must return a real 404 status, not 200. Task 13 Step 2 asserts the status code for exactly that reason.
- Adding `.vue` files can leave the dev server's Tailwind scan stale — restart `npm run dev` whenever a new utility fails to apply.
