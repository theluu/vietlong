# Đại lý / Liên hệ + reCAPTCHA v3 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Dựng lại `/dai-ly` và `/lien-he` đúng `design/Keybolts Dealers.html` + `design/Keybolts Contact.html`, gắn reCAPTCHA v3 cho cả 3 form (đại lý, liên hệ, tư vấn trang chủ), và ghi lead kèm điểm reCAPTCHA vào entity `contact_submission`.

**Architecture:** Frontend Nuxt SSR gọi API Drupal (`/api/v1/*`). Ba form dùng chung một composable `useRecaptcha()` nạp script Google lazy và sinh token theo action. `POST /api/v1/contact` xác thực token qua service `RecaptchaVerifier` rồi lưu vào entity `contact_submission` (đã có sẵn) với cột điểm mới. Secret key đọc từ `settings.php` — không vào config export.

**Tech Stack:** Nuxt 4 + Tailwind 4, Drupal 11 (module `keybolts_api`, `keybolts_core`), Google reCAPTCHA v3, Vitest + PHPUnit kernel tests.

## Global Constraints

- Design là nguồn chân lý. Đọc lại `design/*.html` ngay trước khi dựng từng trang (file có thể đổi giữa chừng).
- Token layout: mọi giá trị px lấy nguyên từ design qua arbitrary value Tailwind (`gap-[16px]`), không quy đổi sang scale.
- Tailwind 4 nuốt arbitrary value có **ngoặc lồng** (`grid-cols-[repeat(auto-fit,minmax(240px,1fr))]`) → khai báo class thật trong `frontend/app/assets/css/tokens.css` thay vì viết inline.
- **Không** commit secret key. Secret chỉ nằm ở `web/sites/default/settings.php` qua `Settings::get()`.
- Site key là public, đọc từ `NUXT_PUBLIC_RECAPTCHA_SITE_KEY`, mặc định rỗng.
- Khi chưa cấu hình key: form chạy bình thường, bỏ qua verify (dev/staging không bị chặn).
- Nguồn lead giữ nguyên 3 giá trị: `contact` | `dealer` | `consult`.
- Storage giữ entity `contact_submission` (không chuyển sang node) — quyết định của chủ dự án 2026-08-04.
- Honeypot `website` hiện có phải giữ nguyên, reCAPTCHA là lớp thứ hai chứ không thay thế.

## Quyết định đã chốt

| Vấn đề | Chốt |
|---|---|
| Nơi lưu lead | Giữ entity `contact_submission`, bổ sung field `email` + `recaptcha_score`, thêm trang xem chi tiết 1 lead |
| reCAPTCHA key | Chưa có — build theo env/settings, chưa cấu hình thì bỏ qua verify |
| Verify lỗi mạng | **Fail-open**: log warning, vẫn nhận lead (không mất khách thật) |
| Điểm dưới ngưỡng | **Fail-closed**: trả 422, không lưu |
| Ngưỡng mặc định | `0.5`, đổi được qua `Settings::get('keybolts_recaptcha_threshold')` |
| Field `email` | Thêm vào entity + API nhận nếu client gửi. **Không** thêm input vào form vì cả 3 design đều không có ô email |

## File Structure

**Tạo mới**
| File | Trách nhiệm |
|---|---|
| `frontend/app/composables/useRecaptcha.ts` | Nạp script v3 một lần, `execute(action)` trả token hoặc `null` |
| `frontend/app/components/page/CenteredHero.vue` | Hero tối, căn giữa, eyebrow có gạch hai bên (design dùng chung cho 2 trang) |
| `frontend/app/components/page/SectionHeading.vue` | Cụm tiêu đề căn giữa (eyebrow ± gạch, h2, intro) |
| `frontend/app/components/page/BenefitGrid.vue` | Lưới 4 thẻ quyền lợi đại lý (số lớn brass, title, desc) |
| `frontend/app/components/page/FormPanel.vue` | Khối 2 cột "nội dung trái + form phải" dùng chung cho đại lý & liên hệ |
| `frontend/app/components/page/BranchMap.vue` | Section bản đồ: danh sách cơ sở chọn được + iframe Google Maps |
| `web/modules/custom/keybolts_core/src/Service/RecaptchaVerifier.php` | Gọi Google siteverify, trả `?float` điểm |
| `web/modules/custom/keybolts_core/tests/src/Kernel/RecaptchaVerifierTest.php` | Test verifier với http client giả |

**Sửa**
| File | Thay đổi |
|---|---|
| `frontend/app/assets/css/tokens.css` | Thêm `.kb-benefit-grid`, `.kb-branch-grid`, `.kb-channel-grid`, `.kb-form-panel` |
| `frontend/app/components/page/LeadForm.vue` | Dựng lại theo design + gắn reCAPTCHA |
| `frontend/app/components/page/BranchGrid.vue` | Dựng lại theo design (dùng cả ở `/gioi-thieu`) |
| `frontend/app/components/page/ContactChannels.vue` | Dựng lại theo design (icon tròn, giá trị cỡ display) |
| `frontend/app/components/page/CriteriaList.vue` | Dựng lại theo design (check SVG, bỏ gạch dưới) |
| `frontend/app/components/home/ConsultForm.vue` | Gắn reCAPTCHA, dùng chung `submitLead` |
| `frontend/app/pages/dai-ly.vue` | Ráp lại theo design |
| `frontend/app/pages/lien-he.vue` | Ráp lại theo design |
| `frontend/app/services/pages.ts` | `LeadPayload` thêm `email?`, `recaptchaToken?`, `recaptchaAction?` |
| `frontend/nuxt.config.ts` | `runtimeConfig.public.recaptchaSiteKey` |
| `frontend/app/utils/leadForm.ts` | Thêm `RECAPTCHA_ACTIONS` map source → action |
| `web/modules/custom/keybolts_core/src/Entity/ContactSubmission.php` | Thêm `email`, `recaptcha_score`; thêm route canonical |
| `web/modules/custom/keybolts_core/ContactSubmissionListBuilder.php` | Cột điểm reCAPTCHA |
| `web/modules/custom/keybolts_core/keybolts_core.services.yml` | Đăng ký `keybolts_core.recaptcha` |
| `web/modules/custom/keybolts_api/src/Controller/ContactController.php` | Verify token, lưu điểm + email |
| `web/modules/custom/keybolts_core/tests/src/Kernel/ContactSubmissionTest.php` | Test đường đi reCAPTCHA |
| `docs/HANDOFF.md` | Hướng dẫn đặt key |

---

### Task 1: Service xác thực reCAPTCHA (backend, không phụ thuộc gì)

**Files:**
- Create: `web/modules/custom/keybolts_core/src/Service/RecaptchaVerifier.php`
- Modify: `web/modules/custom/keybolts_core/keybolts_core.services.yml`
- Test: `web/modules/custom/keybolts_core/tests/src/Kernel/RecaptchaVerifierTest.php`

**Interfaces:**
- Produces: `RecaptchaVerifier::isEnabled(): bool`, `RecaptchaVerifier::verify(string $token, string $action): ?float` — trả điểm 0..1, `NULL` khi không verify được (chưa cấu hình key, hoặc lỗi mạng). Ném không bao giờ.
- Consumes: `@http_client`, `@settings`, `@logger.factory`.

- [ ] **Step 1: Viết test thất bại**

```php
// RecaptchaVerifierTest.php
public function testDisabledWhenSecretMissing(): void {
  $verifier = $this->container->get('keybolts_core.recaptcha');
  $this->assertFalse($verifier->isEnabled());
  $this->assertNull($verifier->verify('tok', 'contact'));
}
```

- [ ] **Step 2: Chạy test, xác nhận fail**

Run: `ddev exec "cd /var/www/html && SIMPLETEST_DB=mysql://db:db@db/db ./vendor/bin/phpunit -c web/core/phpunit.xml.dist web/modules/custom/keybolts_core/tests --filter RecaptchaVerifier"`
Expected: FAIL — service `keybolts_core.recaptcha` không tồn tại.

- [ ] **Step 3: Viết service**

```php
<?php

declare(strict_types=1);

namespace Drupal\keybolts_core\Service;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Site\Settings;
use GuzzleHttp\ClientInterface;

/**
 * Verifies a reCAPTCHA v3 token with Google.
 *
 * Returns NULL — not a score — whenever the answer is unknown (no secret
 * configured, network failure). Callers treat NULL as "let it through": a real
 * customer must never lose a lead because Google was unreachable.
 */
final class RecaptchaVerifier {

  private const ENDPOINT = 'https://www.google.com/recaptcha/api/siteverify';

  public function __construct(
    private readonly ClientInterface $httpClient,
    private readonly Settings $settings,
    private readonly LoggerChannelFactoryInterface $loggerFactory,
  ) {}

  public function isEnabled(): bool {
    return (string) $this->settings->get('keybolts_recaptcha_secret', '') !== '';
  }

  public function threshold(): float {
    return (float) $this->settings->get('keybolts_recaptcha_threshold', 0.5);
  }

  public function verify(string $token, string $action): ?float {
    if (!$this->isEnabled() || $token === '') {
      return NULL;
    }
    try {
      $response = $this->httpClient->request('POST', self::ENDPOINT, [
        'form_params' => [
          'secret' => (string) $this->settings->get('keybolts_recaptcha_secret', ''),
          'response' => $token,
        ],
        'timeout' => 5,
      ]);
      $body = json_decode((string) $response->getBody(), TRUE) ?: [];
    }
    catch (\Throwable $e) {
      $this->loggerFactory->get('keybolts')->warning('reCAPTCHA unreachable: @m', ['@m' => $e->getMessage()]);
      return NULL;
    }
    if (empty($body['success'])) {
      return 0.0;
    }
    // A token minted for a different form is as suspect as a bot token.
    if (isset($body['action']) && $body['action'] !== $action) {
      return 0.0;
    }
    return isset($body['score']) ? (float) $body['score'] : NULL;
  }
}
```

```yaml
# keybolts_core.services.yml — thêm vào cuối khối services
  keybolts_core.recaptcha:
    class: Drupal\keybolts_core\Service\RecaptchaVerifier
    arguments: ['@http_client', '@settings', '@logger.factory']
```

- [ ] **Step 4: Bổ sung test cho điểm thấp / action lệch / lỗi mạng**

```php
private function verifierWith(callable $handler): RecaptchaVerifier {
  $mock = new MockHandler([$handler()]);
  $client = new Client(['handler' => HandlerStack::create($mock)]);
  return new RecaptchaVerifier($client, new Settings([
    'keybolts_recaptcha_secret' => 'shh',
  ]), $this->container->get('logger.factory'));
}

public function testScoreIsReturned(): void {
  $v = $this->verifierWith(fn() => new Response(200, [], json_encode(['success' => TRUE, 'score' => 0.9, 'action' => 'contact'])));
  $this->assertSame(0.9, $v->verify('tok', 'contact'));
}

public function testMismatchedActionScoresZero(): void {
  $v = $this->verifierWith(fn() => new Response(200, [], json_encode(['success' => TRUE, 'score' => 0.9, 'action' => 'dealer'])));
  $this->assertSame(0.0, $v->verify('tok', 'contact'));
}

public function testNetworkFailureReturnsNull(): void {
  $v = $this->verifierWith(fn() => new RequestException('down', new GuzzleRequest('POST', '/')));
  $this->assertNull($v->verify('tok', 'contact'));
}
```

- [ ] **Step 5: Chạy test, xác nhận pass**

Run: lệnh ở Step 2. Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add web/modules/custom/keybolts_core/src/Service/RecaptchaVerifier.php \
        web/modules/custom/keybolts_core/keybolts_core.services.yml \
        web/modules/custom/keybolts_core/tests/src/Kernel/RecaptchaVerifierTest.php
git commit -m "feat(api): add reCAPTCHA v3 verifier service"
```

---

### Task 2: Entity lưu điểm + email, thêm trang chi tiết lead

**Files:**
- Modify: `web/modules/custom/keybolts_core/src/Entity/ContactSubmission.php`
- Modify: `web/modules/custom/keybolts_core/ContactSubmissionListBuilder.php`

**Interfaces:**
- Produces: base field `email` (string, optional), `recaptcha_score` (decimal, optional, precision 3 scale 2). Route canonical `/admin/keybolts/submissions/{contact_submission}`.
- Consumes: không.

- [ ] **Step 1: Thêm field vào `baseFieldDefinitions`**

```php
    $fields['email'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Email'))
      ->setDisplayConfigurable('view', TRUE);

    $fields['recaptcha_score'] = BaseFieldDefinition::create('decimal')
      ->setLabel(t('Điểm reCAPTCHA'))
      ->setSetting('precision', 3)
      ->setSetting('scale', 2)
      ->setDescription(t('Trống nghĩa là không xác thực được (chưa cấu hình key hoặc Google không phản hồi).'))
      ->setDisplayConfigurable('view', TRUE);
```

- [ ] **Step 2: Bật route canonical trong annotation**

Trong khối `handlers` thêm `"view_builder" = "Drupal\Core\Entity\EntityViewBuilder",`; trong `links` thêm `"canonical" = "/admin/keybolts/submissions/{contact_submission}",`.

- [ ] **Step 3: Thêm cột điểm vào list builder**

```php
  // buildHeader(): chèn trước parent::buildHeader()
      'recaptcha_score' => $this->t('reCAPTCHA'),

  // buildRow(): chèn tương ứng
      'recaptcha_score' => $entity->get('recaptcha_score')->value ?? '—',
```

- [ ] **Step 4: Cập nhật schema và kiểm tra**

Run:
```bash
ddev drush entity:updates -y || ddev drush php:eval '\Drupal::entityDefinitionUpdateManager()->applyUpdates();'
ddev drush cr
```
Expected: không lỗi; `/admin/keybolts/submissions` mở được.

- [ ] **Step 5: Commit**

```bash
git add web/modules/custom/keybolts_core/src/Entity/ContactSubmission.php \
        web/modules/custom/keybolts_core/ContactSubmissionListBuilder.php
git commit -m "feat(api): store email and reCAPTCHA score on contact submissions"
```

---

### Task 3: ContactController gọi verifier

**Files:**
- Modify: `web/modules/custom/keybolts_api/src/Controller/ContactController.php`
- Test: `web/modules/custom/keybolts_core/tests/src/Kernel/ContactSubmissionTest.php`

**Interfaces:**
- Consumes: `RecaptchaVerifier::verify()`, `::isEnabled()`, `::threshold()` (Task 1); field `email`, `recaptcha_score` (Task 2).
- Produces: `POST /api/v1/contact` nhận thêm `recaptchaToken`, `recaptchaAction`, `email`. Trả `422 {"errors":["recaptcha"]}` khi điểm dưới ngưỡng.

- [ ] **Step 1: Viết test thất bại**

```php
public function testLowScoreIsRejectedAndStoresNothing(): void {
  $this->setVerifier(0.1);
  [$status, $body] = $this->post(['name' => 'A', 'phone' => '0900000000', 'recaptchaToken' => 'tok']);
  $this->assertSame(422, $status);
  $this->assertContains('recaptcha', $body['errors']);
  $this->assertSame(0, $this->countSubmissions());
}

public function testGoodScoreIsStoredWithScore(): void {
  $this->setVerifier(0.9);
  [$status] = $this->post(['name' => 'A', 'phone' => '0900000000', 'recaptchaToken' => 'tok']);
  $this->assertSame(201, $status);
  $this->assertSame('0.90', $this->latest()->get('recaptcha_score')->value);
}

public function testUnverifiableSubmissionIsStillAccepted(): void {
  // Google unreachable → NULL → fail open, score left empty.
  $this->setVerifier(NULL);
  [$status] = $this->post(['name' => 'A', 'phone' => '0900000000', 'recaptchaToken' => 'tok']);
  $this->assertSame(201, $status);
  $this->assertNull($this->latest()->get('recaptcha_score')->value);
}
```

Helper `setVerifier()` đặt một double vào container:

```php
private function setVerifier(?float $score): void {
  $stub = new class($score) extends RecaptchaVerifier {
    public function __construct(private readonly ?float $score) {}
    public function isEnabled(): bool { return TRUE; }
    public function threshold(): float { return 0.5; }
    public function verify(string $token, string $action): ?float { return $this->score; }
  };
  $this->container->set('keybolts_core.recaptcha', $stub);
}

private function latest(): object {
  $storage = $this->container->get('entity_type.manager')->getStorage('contact_submission');
  $ids = $storage->getQuery()->accessCheck(FALSE)->sort('id', 'DESC')->range(0, 1)->execute();
  return $storage->load(reset($ids));
}
```

- [ ] **Step 2: Chạy test, xác nhận fail**

Run: `ddev exec "cd /var/www/html && SIMPLETEST_DB=mysql://db:db@db/db ./vendor/bin/phpunit -c web/core/phpunit.xml.dist web/modules/custom/keybolts_core/tests --filter ContactSubmission"`
Expected: FAIL — controller chưa biết `recaptchaToken`.

- [ ] **Step 3: Sửa controller**

Constructor nhận thêm `private readonly RecaptchaVerifier $recaptcha`; `create()` thêm `$container->get('keybolts_core.recaptcha')`.

Chèn sau khối validate name/phone, trước khi tạo entity:

```php
    $source = (string) ($data['source'] ?? 'contact');
    if (!in_array($source, self::ALLOWED_SOURCES, TRUE)) {
      $source = 'contact';
    }

    // reCAPTCHA is the second gate after the honeypot. An unknown answer
    // (no key configured, Google down) must not cost a real lead, so only a
    // score we actually received and that came back low is rejected.
    $score = $this->recaptcha->verify(
      (string) ($data['recaptchaToken'] ?? ''),
      (string) ($data['recaptchaAction'] ?? $source),
    );
    if ($score !== NULL && $score < $this->recaptcha->threshold()) {
      return $this->noStore(['errors' => ['recaptcha']], 422);
    }
```

Trong mảng `create([...])` thêm:

```php
      'email' => mb_substr(trim((string) ($data['email'] ?? '')), 0, 254),
      'recaptcha_score' => $score,
```

- [ ] **Step 4: Chạy test, xác nhận pass**

Run: lệnh ở Step 2. Expected: PASS toàn bộ (gồm cả các test cũ về honeypot/flood).

- [ ] **Step 5: Ghi tài liệu key**

Thêm vào `docs/HANDOFF.md`:

```markdown
### reCAPTCHA v3

Site key (public) — `frontend/.env`:
```
NUXT_PUBLIC_RECAPTCHA_SITE_KEY=6Lxxxxxxxxxxxxxxxxxx
```

Secret key — `web/sites/default/settings.php` (KHÔNG commit):
```php
$settings['keybolts_recaptcha_secret'] = '6Lxxxxxxxxxxxxxxxxxx';
$settings['keybolts_recaptcha_threshold'] = 0.5;
```

Chưa đặt key thì form vẫn gửi được, chỉ bỏ qua bước xác thực.
```

- [ ] **Step 6: Commit**

```bash
git add web/modules/custom/keybolts_api/src/Controller/ContactController.php \
        web/modules/custom/keybolts_core/tests/src/Kernel/ContactSubmissionTest.php docs/HANDOFF.md
git commit -m "feat(api): verify reCAPTCHA v3 tokens on lead submission"
```

---

### Task 4: Composable reCAPTCHA phía frontend

**Files:**
- Create: `frontend/app/composables/useRecaptcha.ts`
- Modify: `frontend/nuxt.config.ts`, `frontend/app/services/pages.ts`, `frontend/app/utils/leadForm.ts`
- Test: `frontend/test/leadForm.spec.ts`

**Interfaces:**
- Produces: `useRecaptcha(): { execute(action: string): Promise<string | null> }`; `RECAPTCHA_ACTIONS: Record<LeadPayload['source'], string>`.
- Consumes: `runtimeConfig.public.recaptchaSiteKey`.

- [ ] **Step 1: Viết test thất bại cho map action**

```ts
// frontend/test/leadForm.spec.ts — thêm
import { RECAPTCHA_ACTIONS } from '../app/utils/leadForm'

describe('RECAPTCHA_ACTIONS', () => {
  it('gives every lead source its own action name', () => {
    const actions = Object.values(RECAPTCHA_ACTIONS)
    expect(actions).toHaveLength(3)
    expect(new Set(actions).size).toBe(3)
    expect(RECAPTCHA_ACTIONS.dealer).toBe('dealer_form')
  })
})
```

- [ ] **Step 2: Chạy test, xác nhận fail**

Run: `cd frontend && npm test`
Expected: FAIL — `RECAPTCHA_ACTIONS` chưa export.

- [ ] **Step 3: Thêm map + payload + runtime config**

```ts
// frontend/app/utils/leadForm.ts
/** Google reports per-action stats, so each form gets its own name. */
export const RECAPTCHA_ACTIONS = {
  contact: 'contact_form',
  dealer: 'dealer_form',
  consult: 'consult_form',
} as const
```

```ts
// frontend/app/services/pages.ts — LeadPayload
  email?: string
  recaptchaToken?: string
  recaptchaAction?: string
```

```ts
// frontend/nuxt.config.ts — runtimeConfig.public
      recaptchaSiteKey: process.env.NUXT_PUBLIC_RECAPTCHA_SITE_KEY || '',
```

- [ ] **Step 4: Viết composable**

```ts
let scriptPromise: Promise<void> | null = null

/**
 * reCAPTCHA v3 without a Nuxt module: the script is ~90KB and only three
 * forms need it, so it loads on first submit rather than on every page.
 *
 * Every failure path resolves to null. A visitor must never be blocked from
 * sending a lead because Google's script did not load.
 */
export function useRecaptcha() {
  const siteKey = useRuntimeConfig().public.recaptchaSiteKey as string

  function loadScript(): Promise<void> {
    if (scriptPromise) return scriptPromise
    scriptPromise = new Promise<void>((resolve, reject) => {
      const script = document.createElement('script')
      script.src = `https://www.google.com/recaptcha/api.js?render=${encodeURIComponent(siteKey)}`
      script.async = true
      script.onload = () => resolve()
      script.onerror = () => reject(new Error('recaptcha script failed'))
      document.head.appendChild(script)
    }).catch((error) => {
      scriptPromise = null   // let the next submit retry
      throw error
    })
    return scriptPromise
  }

  async function execute(action: string): Promise<string | null> {
    if (!siteKey || import.meta.server) return null
    try {
      await loadScript()
      const grecaptcha = (window as unknown as { grecaptcha?: ReCaptcha }).grecaptcha
      if (!grecaptcha) return null
      return await new Promise<string | null>((resolve) => {
        grecaptcha.ready(() => {
          grecaptcha.execute(siteKey, { action }).then(resolve).catch(() => resolve(null))
        })
      })
    }
    catch {
      return null
    }
  }

  return { execute }
}

interface ReCaptcha {
  ready: (cb: () => void) => void
  execute: (siteKey: string, options: { action: string }) => Promise<string>
}
```

- [ ] **Step 5: Chạy test, xác nhận pass**

Run: `cd frontend && npm test`. Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add frontend/app/composables/useRecaptcha.ts frontend/app/utils/leadForm.ts \
        frontend/app/services/pages.ts frontend/nuxt.config.ts frontend/test/leadForm.spec.ts
git commit -m "feat(frontend): add lazy reCAPTCHA v3 composable"
```

---

### Task 5: LeadForm dựng lại theo design + gắn reCAPTCHA

**Files:**
- Modify: `frontend/app/components/page/LeadForm.vue`
- Modify: `frontend/app/components/home/ConsultForm.vue`

**Interfaces:**
- Consumes: `useRecaptcha()` (Task 4), `RECAPTCHA_ACTIONS` (Task 4), `submitLead()`.
- Produces: `PageLeadForm` props `source, title, desc, successTitle, successDesc, submitLabel?` — cả 2 trang dùng lại nguyên props cũ, chỉ thêm `submitLabel`.

Design (`Keybolts Dealers.html:1476-1497`, `Keybolts Contact.html:1486-1507`) — hai form giống hệt nhau, chỉ khác tiêu đề và nhãn nút:

- Form: `flex flex-col gap-[18px]`
- Cụm tiêu đề: `gap-[6px] mb-[2px]`, tên 16px bold, mô tả 12px muted `leading-[1.7]`
- Label: `flex flex-col gap-[8px]`, chữ nhãn 10px bold `tracking-[.16em]` uppercase muted
- Input/textarea: `px-[16px] py-[14px]`, `border-border`, nền `bg-background`, focus `border-brass-500`, textarea `rows=4` `resize-y`
- Nút: nền `charcoal-900`, chữ `gold-200`, `py-[17px]`, `rounded-sm`, uppercase `tracking-[.08em]`, hover `bg-neutral-700`, kèm SVG mũi tên
- Trạng thái đã gửi: tiêu đề 24px bold + mô tả + nút viền `border-border` bo `rounded-sm` `px-[24px] py-[12px]` uppercase 12px

- [ ] **Step 1: Đọc lại design ngay trước khi sửa**

Run: `python3 -c "import json;print(json.loads(open('design/Keybolts Contact.html').readlines()[390])[:0] or '')"` rồi trích vùng form. Không dựng theo trí nhớ.

- [ ] **Step 2: Thêm reCAPTCHA vào `submit()` của LeadForm**

```ts
const { execute } = useRecaptcha()

async function submit() {
  errors.value = validateLead(state)
  if (errors.value.length) return
  sending.value = true
  failed.value = false
  try {
    const token = await execute(RECAPTCHA_ACTIONS[props.source])
    await submitLead({
      name: state.name.trim(),
      phone: normalisePhone(state.phone),
      message: state.message.trim(),
      source: props.source,
      website: website.value,
      recaptchaToken: token ?? undefined,
      recaptchaAction: RECAPTCHA_ACTIONS[props.source],
    })
    sent.value = true
  }
  catch (error) {
    // 422 with errors:["recaptcha"] means Google scored the visitor as a bot.
    blocked.value = (error as { data?: { errors?: string[] } })?.data?.errors?.includes('recaptcha') ?? false
    failed.value = !blocked.value
  }
  finally {
    sending.value = false
  }
}
```

Thêm `const blocked = ref(false)` và thông báo riêng:

```html
<p v-if="blocked" class="text-caption text-danger m-0">
  Không xác thực được yêu cầu. Vui lòng tải lại trang hoặc gọi {{ HOTLINE }}.
</p>
```

- [ ] **Step 3: Dựng lại template theo số đo ở trên**

- [ ] **Step 4: Áp cùng logic reCAPTCHA cho `ConsultForm.vue`**

Giữ nguyên layout trang chủ (đã khớp design homepage), chỉ đổi phần `submit()` sang dùng `execute(RECAPTCHA_ACTIONS.consult)` và thêm `blocked`.

- [ ] **Step 5: Kiểm tra thủ công**

Run: mở `https://vietlong.ddev.site/lien-he`, gửi form trống → báo lỗi; gửi hợp lệ → hiện trạng thái thành công.
Run: `ddev drush php:eval '\Drupal::entityTypeManager()->getStorage("contact_submission")->getQuery()->accessCheck(FALSE)->count()->execute();'` → tăng 1.

- [ ] **Step 6: Commit**

```bash
git add frontend/app/components/page/LeadForm.vue frontend/app/components/home/ConsultForm.vue
git commit -m "feat(frontend): rebuild lead form to design and gate it with reCAPTCHA"
```

---

### Task 6: Component dùng chung cho 2 trang

**Files:**
- Create: `frontend/app/components/page/CenteredHero.vue`, `SectionHeading.vue`, `BenefitGrid.vue`, `FormPanel.vue`, `BranchMap.vue`
- Modify: `frontend/app/components/page/BranchGrid.vue`, `ContactChannels.vue`, `CriteriaList.vue`, `frontend/app/assets/css/tokens.css`

**Interfaces:**
- Produces:
  - `PageCenteredHero` props `{ eyebrow: string; title: string; subtitle: string }`
  - `PageSectionHeading` props `{ eyebrow: string; title: string; intro?: string; rules?: boolean }`
  - `PageBenefitGrid` props `{ items: NumberedItem[] }`
  - `PageFormPanel` — slot `left` + slot `form`, khung 2 cột `auto-fit minmax(340px,1fr)` viền `border-border`, cột phải nền `surface` viền trái
  - `PageBranchMap` props `{ branches: Branch[] }`
  - `PageBranchGrid` props giữ nguyên `{ eyebrow, title, branches }`
- Consumes: `Branch`, `NumberedItem` từ `~/types/page`.

Class CSS cần thêm (ngoặc lồng, Tailwind không sinh được):

```css
  /* Đại lý & liên hệ — lưới auto-fit lấy nguyên từ design */
  .kb-benefit-grid  { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }
  .kb-branch-grid   { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); }
  .kb-channel-grid  { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }
  .kb-form-panel    { display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); }

  /* Bản đồ: danh sách trái 380px, iframe phải; xếp chồng dưới 900px */
  .kb-map-grid { display: grid; grid-template-columns: 1fr; }
  @media (width >= 900px) {
    .kb-map-grid { grid-template-columns: 380px minmax(0, 1fr); }
  }
```

- [ ] **Step 1: Đọc lại cả hai design file**

- [ ] **Step 2: Thêm class vào `tokens.css`** (khối `@layer components`)

- [ ] **Step 3: Viết `CenteredHero.vue`**

Section `bg-charcoal-900 text-white py-[clamp(52px,6vw,84px)] relative overflow-hidden`, phủ `radial-gradient(circle at 82% 12%, rgba(247,228,153,.14), transparent 55%)` bằng `<div class="absolute inset-0">` với `style` inline (gradient có ngoặc lồng). Nội dung `max-w-[var(--container-max)] flex flex-col items-center text-center gap-[16px]`; eyebrow 12px bold `tracking-[.24em]` uppercase `text-gold-200` kèm 2 gạch `w-[36px] h-[2px] bg-gold-200`; h1 `text-[clamp(var(--text-display),3.8vw,var(--text-display-lg))] leading-[1.1] tracking-[-.03em]`; mô tả `max-w-[720px] text-heading leading-[1.75] text-white/86`.

**Không có breadcrumb** — cả hai design đều bỏ breadcrumb ở 2 trang này.

- [ ] **Step 4: Viết `SectionHeading.vue`, `BenefitGrid.vue`, `FormPanel.vue`**

`BenefitGrid`: thẻ `flex flex-col gap-[12px] p-[30px] border border-border`, hover `border-brass-500 shadow-floating`; số `text-display font-bold text-brass-700 leading-none`; title 16px bold; desc 12px muted `leading-[1.8]`.

- [ ] **Step 5: Viết `BranchMap.vue`**

```ts
const active = ref(0)
const mapSrc = computed(() =>
  `https://maps.google.com/maps?q=${encodeURIComponent(props.branches[active.value]?.address ?? '')}&z=15&output=embed`)
const directions = (address: string) =>
  `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(address)}`
```

Danh sách: `kb-track max-h-[340px] md:max-h-[520px] overflow-y-auto bg-surface`; mỗi mục `p-[20px_22px] cursor-pointer border-b border-border border-l-[3px]`, mục đang chọn `border-l-brass-500 bg-background`, còn lại `border-l-transparent bg-transparent`. Mục phải là `<button>` để dùng được bàn phím. iframe `min-h-[340px] md:min-h-[520px]`, `loading="lazy"`, `referrerpolicy="no-referrer-when-downgrade"`, `title="Bản đồ showroom Keybolts"`.

- [ ] **Step 6: Dựng lại `BranchGrid`, `ContactChannels`, `CriteriaList`**

`BranchGrid` dùng ở cả `/gioi-thieu` — mở trang đó kiểm tra lại sau khi sửa.

- [ ] **Step 7: Commit**

```bash
git add frontend/app/components/page frontend/app/assets/css/tokens.css
git commit -m "feat(frontend): add shared hero, benefit, form-panel and map sections"
```

---

### Task 7: Ráp lại 2 trang

**Files:**
- Modify: `frontend/app/pages/dai-ly.vue`, `frontend/app/pages/lien-he.vue`

**Interfaces:**
- Consumes: toàn bộ component ở Task 6, `PageLeadForm` ở Task 5.

Thứ tự section theo design:

**`/dai-ly`** — `CenteredHero` → `BenefitGrid` (nền background) → `FormPanel` (nền surface, viền trên; trái = eyebrow "Điều kiện" + h2 + `CriteriaList`, phải = `LeadForm` source `dealer`) → `BranchGrid` "Hệ thống / Điểm bán & kho hàng" → `BranchMap`.

**`/lien-he`** — `CenteredHero` → section nền background gồm `ContactChannels` + `FormPanel` (trái = "Gửi yêu cầu" + h2 + mô tả + khối tên/địa chỉ công ty có `border-t`, phải = `LeadForm` source `contact`) → `BranchGrid` "Địa chỉ / Showroom & kho hàng" (nền surface, viền trên) → `BranchMap`.

- [ ] **Step 1: Ráp `dai-ly.vue`**
- [ ] **Step 2: Ráp `lien-he.vue`**
- [ ] **Step 3: Kiểm tra 2 trang ở 1440px và 390px**

Run: mở `https://vietlong.ddev.site/dai-ly` và `/lien-he`, chụp màn hình đối chiếu design.
Kiểm: hero căn giữa không breadcrumb; lưới quyền lợi 4 cột; khối form 2 cột; lưới cơ sở; bản đồ đổi khi bấm cơ sở khác.

- [ ] **Step 4: Kiểm tra hồi quy `/gioi-thieu`** (dùng chung `BranchGrid`)

- [ ] **Step 5: Chạy toàn bộ test**

Run: `cd frontend && npm test`
Run: `ddev exec "cd /var/www/html && SIMPLETEST_DB=mysql://db:db@db/db ./vendor/bin/phpunit -c web/core/phpunit.xml.dist web/modules/custom/keybolts_core/tests"`
Expected: cả hai PASS.

- [ ] **Step 6: Commit**

```bash
git add frontend/app/pages/dai-ly.vue frontend/app/pages/lien-he.vue
git commit -m "feat(frontend): align dealer and contact pages with design"
```

---

## Self-Review

**Spec coverage**
| Yêu cầu | Task |
|---|---|
| `/dai-ly` giống design | 6, 7 |
| `/lien-he` giống design | 6, 7 |
| reCAPTCHA v3 cho form đại lý | 4, 5 |
| reCAPTCHA v3 cho form liên hệ | 4, 5 |
| reCAPTCHA v3 cho form trang chủ | 4, 5 |
| Submit lưu vào content storage | 2, 3 (entity `contact_submission`) |

**Rủi ro đã biết**
- `BranchGrid` dùng chung với `/gioi-thieu` → Task 7 Step 4 kiểm hồi quy.
- Iframe Google Maps chặn bởi consent/adblock ở một số máy → iframe có `title`, trang vẫn dùng được nếu iframe trống.
- Chưa có key reCAPTCHA → không test được đường đi điểm thật; test kernel dùng double nên vẫn phủ logic.
