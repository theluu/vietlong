# Giới thiệu & Tin tức — Design Alignment Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax.

**Goal:** Dựng lại `/gioi-thieu` theo `design/Keybolts About.html` và `/tin-tuc` theo `design/Keybolts News.html`.

**Architecture:** Sửa component trong `frontend/app/components/page/`, ráp lại hai trang. Không đổi API — mọi dữ liệu cần thiết đã có, trừ icon của khối "Cam kết" (map theo thứ tự, xem Global Constraints).

**Tech Stack:** Nuxt 4 SSR + Tailwind 4.

## Global Constraints

- Đọc lại `design/*.html` ngay trước khi sửa từng khối. File có thể đổi giữa chừng.
- Giá trị px lấy nguyên từ design qua arbitrary value (`gap-[16px]`), không quy đổi sang spacing scale (base 3px).
- Tailwind 4 nuốt arbitrary value có **ngoặc lồng** → khai class thật trong `tokens.css`.
- Hai trang này **không có breadcrumb** trong design. `PageHero` (có breadcrumb) không dùng ở đây nữa.
- `ValueItem` từ API chỉ có `title` + `desc`, design thì mỗi thẻ có một icon. Map icon theo **thứ tự** trong một mảng cố định 4 icon (shield / award / truck / headset), lặp lại nếu nhiều hơn 4. Không thêm field vào Drupal cho một chi tiết trang trí.
- `PageBranchGrid` đang dùng ở 3 trang với **hai kiểu khác nhau trong design**: About dùng lưới hairline (`gap:1px`, nền border, thẻ không viền riêng), Đại lý/Liên hệ dùng thẻ rời (`gap:18px`, viền + hover). Thêm prop `variant`, mặc định `cards` để không đụng hai trang vừa làm.

## Khác biệt đã đối chiếu

### `/gioi-thieu`

| Khối | Hiện tại | Design |
|---|---|---|
| Hero | `PageHero` chung, có breadcrumb, ảnh không tỉ lệ | Không breadcrumb; eyebrow có **một** gạch bên trái; h1 tới `--text-display-xl`; ảnh `aspect-4/5` + caption phủ gradient |
| Fact strip | Section rời, nền `surface`, số màu brass | **Nằm trong hero**, nền charcoal, số `gold-200`, ngăn bằng đường dọc `rgba(255,255,255,.14)` |
| Câu chuyện | Chỉ chữ; credentials là `<ul>` có `✓` | 2 cột: chữ + **2 ảnh so le** (ảnh trái lệch xuống 28px); credentials là lưới 2 cột với SVG check |
| Khách hàng | Tiêu đề canh trái | Tiêu đề **canh giữa** có 2 gạch; ảnh zoom khi hover; CTA dưới cùng có `border-top` |
| Quy trình | `<ol>` xếp dọc | Lưới hairline 5 thẻ, số màu `brass-500`, hover đổi nền |
| Cam kết | Tiêu đề canh trái, không icon | Canh giữa, gạch vàng; mỗi thẻ có **icon trong vòng tròn** |
| Cơ sở | Thẻ rời (sau khi sửa cho trang Đại lý) | Lưới hairline |
| CTA cuối | **Không có** | Khối 2 cột: chữ + 2 nút / ảnh |

### `/tin-tuc`

| Khối | Hiện tại | Design |
|---|---|---|
| Hero | `PageHero`, có breadcrumb | Hero tối canh giữa, eyebrow 2 gạch (giống `PageCenteredHero` đã có) |
| Bài nổi bật | **Không có** | Thẻ lớn 2 cột, badge vàng "Nổi bật", đứng trên bộ lọc |
| Lưới bài | `minmax(290px,1fr)` — khớp | Giữ nguyên |
| Phân trang | Chỉ số trang; nhãn range nằm bên cạnh | Nút ‹ › 44px hai đầu, số ở giữa, **nhãn range canh giữa bên dưới** |

**Bài nổi bật lấy ở đâu.** `field_sort_order = 99` bị `ArticleSerializer::all()` lọc ra (`< 98`), nên bài `nen-chon-khoa-van-tay-nao-cho-cua-go` hiện **không có đường nào vào từ giao diện**. Trang tin tức sẽ fetch riêng bằng slug.

---

### Task 1: BranchGrid nhận hai kiểu

**Files:** Modify `frontend/app/components/page/BranchGrid.vue`, `frontend/app/pages/gioi-thieu.vue`

**Interfaces:** Produces `PageBranchGrid` props `{ eyebrow, title, branches, variant?: 'cards' | 'hairline' }`, mặc định `'cards'`.

- [ ] **Step 1:** Thêm prop `variant` với `withDefaults`.
- [ ] **Step 2:** `hairline` → wrapper `kb-branch-grid gap-px bg-border border border-border`, thẻ `bg-background` không viền, không hover. `cards` → giữ nguyên hiện tại.
- [ ] **Step 3:** Mở `/dai-ly` và `/lien-he` xác nhận không đổi.
- [ ] **Step 4:** Commit.

---

### Task 2: Hero trang Giới thiệu (kèm fact strip)

**Files:** Create `frontend/app/components/page/AboutHero.vue`; Modify `tokens.css`

**Interfaces:** Produces `PageAboutHero` props `{ eyebrow, title, subtitle, image?, caption?, ctaPrimary?, ctaSecondary?, facts }`.

- [ ] **Step 1:** Thêm class lưới vào `tokens.css`:

```css
  .kb-about-hero  { display: grid; grid-template-columns: 1fr; }
  .kb-fact-strip  { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); }
  @media (width >= 900px) {
    .kb-about-hero { grid-template-columns: 1.15fr 0.85fr; }
  }
```

- [ ] **Step 2:** Section `kb-hero-glow bg-charcoal-900 relative overflow-hidden`, nội dung `pt-[clamp(52px,6vw,90px)]`.
- [ ] **Step 3:** Cột trái: eyebrow **một gạch trái** `w-[36px] h-[2px] bg-gold-200`; h1 `text-[clamp(var(--text-display),4.2vw,var(--text-display-xl))] leading-[1.06] tracking-[-0.03em]`; mô tả `max-w-[560px] leading-[1.8] text-white/82`; 2 nút (gold đặc / viền trắng 40%).
- [ ] **Step 4:** Cột phải: `aspect-[4/5]`, viền `rgba(247,228,153,.3)`, ảnh zoom hover, caption phủ `bg-gradient-to-t from-charcoal-900/92`.
- [ ] **Step 5:** Fact strip trong hero: `border-t border-white/14`, mỗi ô `px-[22px] py-[26px] border-r border-white/14`, số `text-display-lg text-gold-200 leading-none`, nhãn `text-caption tracking-[0.1em] uppercase text-white/62`.
- [ ] **Step 6:** Xoá `PageFactStrip` khỏi trang (component vẫn giữ, chưa dùng nơi khác thì xoá file).
- [ ] **Step 7:** Commit.

---

### Task 3: Khối Câu chuyện

**Files:** Modify `frontend/app/components/page/StoryBlock.vue`

**Interfaces:** Consumes `{ eyebrow, title, body, credentials, images?: string[] }`. `images` mặc định rỗng → không render cột ảnh.

- [ ] **Step 1:** Lưới `repeat(auto-fit,minmax(320px,1fr))` (class `.kb-story-grid` trong tokens.css), `gap-[clamp(32px,4vw,64px)] items-center`.
- [ ] **Step 2:** Cột trái: eyebrow, h2 `text-display-lg leading-[1.15]`, body `v-html` `leading-[1.9] text-text-muted`.
- [ ] **Step 3:** Credentials → lưới `repeat(auto-fit,minmax(160px,1fr))` gap 14px, mỗi mục SVG check `stroke-brass-700 stroke-[2.6]` + chữ `text-caption font-bold`.
- [ ] **Step 4:** Cột phải: 2 ảnh `aspect-[3/4]`, ảnh đầu `mt-[28px]`, cả hai zoom hover.
- [ ] **Step 5:** Commit.

---

### Task 4: Khách hàng, Quy trình, Cam kết

**Files:** Modify `SegmentGrid.vue`, `ValueGrid.vue`; Create `ProcessGrid.vue`; Modify `tokens.css`

**Interfaces:**
- `PageSegmentGrid` props không đổi.
- `PageProcessGrid` props `{ eyebrow, title, intro?, steps: NumberedItem[] }`.
- `PageValueGrid` props không đổi.

- [ ] **Step 1:** Thêm class:

```css
  .kb-segment-grid { grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); }
  .kb-process-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); }
  .kb-value-grid   { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); }
```

- [ ] **Step 2:** `SegmentGrid` dùng `PageSectionHeading` (đã có, `rules`), ảnh trong khối `aspect-[4/3] overflow-hidden` zoom hover, CTA `mt-auto pt-[14px] border-t`.
- [ ] **Step 3:** `ProcessGrid`: `kb-process-grid gap-px bg-border border border-border`; thẻ `bg-background p-[28px_24px] hover:bg-surface`; số `text-display text-brass-500 leading-none`.
- [ ] **Step 4:** `ValueGrid`: heading canh giữa gạch vàng; thẻ `border-white/16 p-[30px] hover:border-gold-200 hover:bg-white/4`; icon trong vòng `w-[44px] h-[44px] rounded-full border-gold-200/40`.

```ts
// Bốn icon theo đúng thứ tự cam kết trong design; lặp lại nếu API trả nhiều hơn.
const ICONS = ['shield', 'award', 'truck', 'headset'] as const
const iconFor = (i: number) => ICONS[i % ICONS.length]
```

- [ ] **Step 5:** Bỏ `PageStepList` khỏi `/gioi-thieu` (vẫn dùng ở `/dai-ly`? — kiểm tra: **không**, đại lý đã chuyển sang `PageBenefitGrid`; nếu không còn nơi dùng thì xoá file).
- [ ] **Step 6:** Commit.

---

### Task 5: CTA cuối trang Giới thiệu + ráp trang

**Files:** Create `frontend/app/components/page/ClosingCta.vue`; Modify `frontend/app/pages/gioi-thieu.vue`

**Interfaces:** Produces `PageClosingCta` props `{ eyebrow, title, body, phone, image }`.

- [ ] **Step 1:** Dùng lại `.kb-form-panel` (đã có, `minmax(340px,1fr)`) hoặc thêm `.kb-cta-panel` `minmax(320px,1fr)`.
- [ ] **Step 2:** Cột trái chữ + 2 nút (charcoal/gold và viền `neutral-300`); cột phải ảnh `min-h-[280px]` viền trái, zoom hover.
- [ ] **Step 3:** Ráp `/gioi-thieu` theo thứ tự design: AboutHero → StoryBlock → SegmentGrid → ProcessGrid → ValueGrid → BranchGrid(`hairline`) → ClosingCta.
- [ ] **Step 4:** Chụp màn hình 1440px và 390px đối chiếu design.
- [ ] **Step 5:** Commit.

---

### Task 6: Trang Tin tức

**Files:** Modify `frontend/app/pages/tin-tuc/index.vue`; Create `frontend/app/components/page/FeaturedArticle.vue`, `frontend/app/components/page/Pager.vue`

**Interfaces:**
- `PageFeaturedArticle` props `{ article: ArticleDetail | NewsArticle }`.
- `PagePager` props `{ page: number; pageCount: number; rangeLabel: string }`, emit `update:page`.

- [ ] **Step 1:** Thay `PageHero` bằng `PageCenteredHero` (đã có từ trang Liên hệ).
- [ ] **Step 2:** Fetch bài nổi bật riêng — nó bị `field_sort_order = 99` lọc khỏi danh sách nên không có đường nào vào:

```ts
const FEATURED_SLUG = 'nen-chon-khoa-van-tay-nao-cho-cua-go'
const { data: featuredData } = await useAsyncData(
  `article:${FEATURED_SLUG}`,
  () => fetchArticle(FEATURED_SLUG),
  { default: () => null },
)
const featured = computed(() => featuredData.value?.data ?? null)
```

Bọc trong `try`/`default` để bài bị gỡ không làm hỏng cả trang.

- [ ] **Step 3:** `FeaturedArticle`: lưới `repeat(auto-fit,minmax(320px,1fr))` viền, ảnh `aspect-16/10` zoom hover + badge `bg-gold-200 text-charcoal-900` góc trên trái, cột phải `gap-[14px] justify-center p-[clamp(26px,3vw,44px)]`, meta `tác giả · thời gian đọc`.
- [ ] **Step 4:** `Pager`: nút ‹ › `w-[44px] h-[44px] border-neutral-300` hover charcoal/gold; số `min-w-[44px] h-[44px]`, trang hiện tại nền charcoal chữ gold; nhãn range `text-center mt-[16px]`.
- [ ] **Step 5:** Ráp lại trang; giữ nguyên bộ lọc và logic phân trang hiện có.
- [ ] **Step 6:** Chụp màn hình đối chiếu; kiểm tra bấm vào bài nổi bật ra đúng `/tin-tuc/nen-chon-khoa-van-tay-nao-cho-cua-go`.
- [ ] **Step 7:** Chạy `npm test`; commit.

---

## Self-Review

**Spec coverage:** 8 khác biệt của `/gioi-thieu` → Task 1–5; 4 khác biệt của `/tin-tuc` → Task 6.

**Rủi ro**
- `BranchGrid` dùng chung 3 trang → Task 1 Step 3 kiểm hồi quy.
- `PageStepList`/`PageFactStrip` có thể không còn nơi dùng → grep trước khi xoá, đừng để file chết.
- Bài nổi bật fetch theo slug cứng. Nếu editor đổi slug thì khối biến mất chứ không lỗi trang — chấp nhận, và ghi chú trong code.
