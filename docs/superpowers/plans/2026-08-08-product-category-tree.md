# Cây danh mục sản phẩm 3 cấp — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Dựng lại `product_category` thành cây 3 cấp gồm 4 nhóm gốc theo feedback (`feedback/1.jpg`–`3.jpg`), gán lại toàn bộ 186 sản phẩm đã import vào đúng danh mục cha/con, và cho cây đó hiện ra ở lưới trang chủ lẫn mega menu.

**Architecture:** Toàn bộ thay đổi cấu trúc nằm ở **dữ liệu taxonomy**, không phải config — term là content entity nên không đi qua `drush cex`. Hai script PHP idempotent trong `scripts/setup/` là nguồn sự thật để tái lập cây trên mọi môi trường. Backend truy vấn đã hierarchy-aware ở mọi độ sâu sẵn (`ProductQuery::categoryWithDescendants()`, `ProductFacetBuilder::rollUpToAncestors()`, `HomepageController::withDescendants()` đều dùng `loadTree(..., NULL, ...)` / `loadAllParents()`), nên chỉ hai chỗ hardcode ở tầng trình bày cần sửa.

**Tech Stack:** Drupal 11.4 (taxonomy), PHP 8.4, Nuxt 4 SSR, Vue 3, Tailwind 4. Không thêm dependency nào.

---

## Global Constraints

- **Không xóa term nào.** Cả 8 term gốc cũ (tid 3–10) được tái dùng làm cấp 2, nên mọi link `/danh-muc/{tid}` đang tồn tại vẫn sống. Xóa term sẽ 404 hàng loạt URL đã index.
- **Không đụng `drush cim` / `drush cex`** cho phần taxonomy — term là content, không phải config. Chỉ Task 3–4 có thay đổi code cần commit.
- Mọi script trong `scripts/setup/` phải **chạy lại nhiều lần cho cùng kết quả** (idempotent), theo đúng quy ước các script sẵn có.
- Script tra term theo **tid cố định** khi tid đã biết, và theo **tên** khi là term mới tạo — không bao giờ hardcode tid của term mới.
- Kernel test chạy bằng:
  `SIMPLETEST_DB="sqlite://localhost/sites/default/files/test.sqlite" vendor/bin/phpunit -c web/core/phpunit.xml.dist <path> --no-coverage`
- `LeadSubmissionTest::testGoodRecaptchaScoreIsStoredAlongsideTheLead` fail sẵn trên SQLite (`'0.90'` vs `'0.9'`), không liên quan, đừng sửa.
- Frontend dev server phải chạy (`cd frontend && npm run dev`, cổng 3100) để kiểm chứng, nếu không `/` trả 502.
- Commit message tiếng Anh, theo conventional commits như lịch sử nhánh `feat/static-pages`.

---

## Hiện trạng (đo ngày 2026-08-08)

Vocabulary `product_category`: 8 term gốc + 14 term con, 186 sản phẩm published.

| tid | Tên | Cha | SP gắn trực tiếp |
|---|---|---|---|
| 3 | Khóa đồng | — | 0 |
| 4 | Khóa tay gạt | — | 0 |
| 5 | Khóa thông minh | — | 18 |
| 6 | Khóa vân tay | — | 12 |
| 7 | Khóa khách sạn | — | 0 |
| 8 | Chốt Cremone | — | 0 |
| 9 | Bản lề & tay co | — | 0 |
| 10 | Phụ kiện cửa | — | 0 |
| 22 | Bản Lề | 9 | 17 |
| 23 | Phụ Kiện Cửa Gỗ | 10 | 31 |
| 24 | Chốt Cửa Cremone Đồng | 8 | 9 |
| 25 | Chốt Cửa Cremone Inox | 8 | 4 |
| 26 | Phụ Kiện Tủ-Bếp | 10 | 21 |
| 27 | Khóa Âm | 3 | 2 |
| 28 | Khóa Tay Gạt Inox | 4 | 4 |
| 29 | Khoá Thẻ Từ Khách Sạn | 7 | 5 |
| 30 | Khóa Tay Gạt Đồng Thông Phòng | 4 | 14 |
| 31 | Khóa Tay Gạt Đồng Trung | 4 | 11 |
| 32 | Khóa Tay Gạt Đồng Đại | 4 | 14 |
| 33 | Khóa Tay Gạt Đồng Đại Sảnh | 4 | 14 |
| 34 | Khoá Đồng Đại Sảnh Full Size | 3 | 10 |

Chỉ 6 term gốc mang `field_number` (01–08) + `field_short_desc` — đó là dữ liệu lưới trang chủ.

---

## Cây đích

```
01  Khóa thông minh                     tid 5    giữ nguyên gốc
    ├─ Khóa thông minh cửa gỗ           tid 6    đổi tên (từ "Khóa vân tay") + gán cha 5
    ├─ Khóa thông minh cửa nhôm kính    MỚI      cha 5
    ├─ Khóa thông minh cửa cổng         MỚI      cha 5 — placeholder, 0 SP
    └─ Khóa khách sạn                   tid 7    gán cha 5
       └─ Khoá thẻ từ khách sạn         tid 29   giữ cha 7
02  Khóa cơ                             MỚI      gốc
    ├─ Khóa đồng                        tid 3    gán cha "Khóa cơ"
    │  ├─ Khoá đồng đại sảnh full size  tid 34   giữ cha 3
    │  ├─ Khóa tay gạt đồng đại sảnh    tid 33   đổi cha 4 → 3
    │  ├─ Khóa tay gạt đồng đại         tid 32   đổi cha 4 → 3
    │  ├─ Khóa tay gạt đồng trung       tid 31   đổi cha 4 → 3
    │  ├─ Khóa tay gạt đồng thông phòng tid 30   đổi cha 4 → 3
    │  └─ Khóa âm                       tid 27   giữ cha 3
    └─ Khóa inox                        tid 4    đổi tên (từ "Khóa tay gạt") + gán cha "Khóa cơ"
       └─ Khóa tay gạt inox             tid 28   giữ cha 4
03  Chốt cửa & Bản lề                   MỚI      gốc
    ├─ Chốt cửa Cremone                 tid 8    đổi tên + gán cha "Chốt cửa & Bản lề"
    │  ├─ Chốt cửa Cremone đồng         tid 24   giữ cha 8
    │  └─ Chốt cửa Cremone inox         tid 25   giữ cha 8
    └─ Bản lề cửa                       tid 9    đổi tên (từ "Bản lề & tay co") + gán cha
       ├─ Bản lề inox                   tid 22   đổi tên (từ "Bản Lề"), giữ cha 9
       └─ Bản lề đồng                   MỚI      cha 9
04  Phụ kiện cửa & Nội thất             tid 10   đổi tên (từ "Phụ kiện cửa"), giữ gốc
    ├─ Phụ kiện cửa gỗ                  tid 23   giữ cha 10
    │  ├─ Chặn cửa / Hít cửa            MỚI      cha 23
    │  ├─ Tay co                        MỚI      cha 23
    │  ├─ Mắt thần                      MỚI      cha 23
    │  └─ Phụ kiện cửa khác             MỚI      cha 23
    └─ Phụ kiện tủ & bếp                tid 26   đổi tên (từ "Phụ Kiện Tủ-Bếp"), giữ cha 10
```

Tổng: 30 term — 9 term mới, 21 term cũ tái dùng. Không term nào bị xóa.

### Hai chỗ cây đích lệch so với ảnh feedback, và lý do

1. **Nhánh 4.2 để phẳng.** Ảnh đề xuất *Tay nắm tủ / Bản lề bật / Ray trượt*, nhưng cả 21 SP hiện có đều là giá–rổ–tủ kho bếp (Giá Bát, Giá Dao Thớt, Giá Xoong Nồi, Giá Góc, Tủ kho); không SP nào rơi vào 3 nhóm đó. Theo chỉ đạo, không tạo danh mục con rỗng cho nhánh này — 21 SP nằm trực tiếp dưới "Phụ kiện tủ & bếp". Khi nhập hàng tay nắm/bản lề bật/ray trượt thì thêm term trong backend.
2. **Đặt tên "Tay co", không phải "Tay co thủy lực".** 11 SP `TAY CO HK NGỌC/TRƠN 608/613/614 size 96–128` là tay co cỡ nhỏ, không phải tay co thủy lực (door closer). Đặt đúng tên hàng đang có.

Ngoài ra 2 nhánh placeholder trong ảnh (*1.3 Cửa cổng*, *2.2 Khóa inox*) vẫn được tạo: 1.3 tạo mới rỗng, còn 2.2 đã có sẵn 4 SP dưới "Khóa tay gạt inox".

---

## Gán lại sản phẩm

**Nhóm A — 108 SP không đụng tới.** Chỉ term đổi cha/đổi tên, `field_category` của node giữ nguyên: tid 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34.

**Nhóm B — 78 SP đổi `field_category`, rule suy từ tiêu đề (đã đối chiếu từng dòng với DB):**

| Từ | Rule tiêu đề | Sang | Số SP |
|---|---|---|---|
| 22 | `LIKE '%KB-SUS%'` | Bản lề inox (tid 22, giữ chỗ) | 9 |
| 22 | còn lại (`Bản Lề Đồng Cối/Lá KB 81x`) | Bản lề đồng (MỚI) | 8 |
| 23 | `LIKE 'Chặn%' OR LIKE 'Chăn%'` | Chặn cửa / Hít cửa | 10 |
| 23 | `LIKE 'TAY CO%'` | Tay co | 11 |
| 23 | `LIKE 'Mắt Thần%'` | Mắt thần | 2 |
| 23 | còn lại (Chốt Hé ×2, Núm ×2, Trùy ×4) | Phụ kiện cửa khác | 8 |
| 6 | `LIKE '%Cửa Kính%'` (KB 8150) | Khóa thông minh cửa nhôm kính | 1 |
| 6 | còn lại (11 × "Khóa Biệt Thự") | Khóa thông minh cửa gỗ (tid 6, giữ chỗ) | 11 |
| 5 | tất cả | Khóa thông minh cửa gỗ (tid 6) | 18 |

Các rule đã verify bằng `COUNT(*)`: 9 / 8 / 10 / 11 / 2, phần dư 8 → tổng 31 khớp đúng số SP của tid 23; 9 + 8 = 17 khớp tid 22. Tổng nhóm B = 78, cộng nhóm A 108 = 186, khớp tổng số sản phẩm.

> **GIẢ ĐỊNH CẦN ANH DUYỆT LẠI SAU KHI CHẠY:** 18 SP đang ở tid 5 (baltica BD01/C21/K42/P20/Z15/Z17 và Keybolts D01/Min-01/P60/P70-1/P70-5/P80/P90/T18/T28/T29/T88/V517) **không có bất kỳ trường nào cho biết lắp cửa gỗ hay cửa nhôm kính** — `products.csv` chỉ có cột `danh_muc` trùng đúng taxonomy cũ, còn `field_description`/`field_highlights` của nhóm này rỗng. Script gán mặc định cả 18 vào **Khóa thông minh cửa gỗ**. Sau khi chạy, sửa lại từng SP trong backend tại `/admin/content?type=product` — đổi trường Danh mục là xong, không cần chạy lại script.

Sau khi gán: **0 sản phẩm nào còn nằm trực tiếp trên term cấp 1** — đây là điều kiện kiểm chứng ở Task 5.

---

## File Structure

| File | Trách nhiệm |
|---|---|
| `scripts/setup/restructure_product_categories.php` | Tạo 6 term mới, đổi tên 7 term, gán lại cha, đặt `field_number`/`field_short_desc` cho 4 gốc. Không đụng node. |
| `scripts/setup/reassign_product_categories.php` | Đổi `field_category` của 54 node theo bảng rule. Không đụng term. |
| `web/modules/custom/keybolts_api/src/Controller/HomepageController.php` | Trả `categories` dạng lồng (`children`), cập nhật `FEATURED_FALLBACK_CATEGORIES` theo tên mới |
| `frontend/app/components/layout/MegaMenu.vue` | Bỏ `slice(0,5)`/`slice(5)`, dựng menu từ cây thật |
| `frontend/app/types/page.ts` | Thêm `children` vào `HomeCategory` |
| `web/modules/custom/keybolts_core/src/Service/ProductFacetBuilder.php` | Gắn nhãn cho chính danh mục đang lọc, kể cả khi nó rỗng |
| `web/modules/custom/keybolts_core/tests/src/Kernel/ProductFacetTreeTest.php` | Test roll-up 3 cấp và danh mục rỗng |

Tách làm hai script vì hai mối quan tâm khác nhau: một cái dựng cây, một cái xếp hàng vào cây. Chạy lại script 2 không cần chạy lại script 1, và sửa tay trong backend cũng không bị script 1 ghi đè.

---

### Task 1: Script dựng lại cây taxonomy

**Files:**
- Create: `scripts/setup/restructure_product_categories.php`

**Interfaces:**
- Chạy bằng `ddev drush scr scripts/setup/restructure_product_categories.php`
- In ra từng thao tác (`created` / `renamed` / `reparented` / `skipped`) và tổng kết cuối.

- [x] **Step 1: Khung script idempotent**

Đọc `scripts/setup/install_product_model.php` trước để bám đúng lối viết sẵn có (cách lấy `\Drupal::entityTypeManager()`, cách in log).

Script khai báo cây đích thành một mảng dữ liệu duy nhất, mỗi dòng gồm: `tid` (NULL nếu là term mới), `name`, `parent` (0 hoặc tid/tên cha), `number`, `short_desc`. Một vòng lặp duy nhất áp mảng đó lên DB. Không viết 22 khối `if` riêng.

Quy tắc idempotent:
- Term có `tid` → `load()`, chỉ `save()` khi tên hoặc cha thực sự khác.
- Term mới → tra theo `loadByProperties(['vid' => 'product_category', 'name' => $name])`; đã có thì dùng lại, chưa có thì tạo.
- Chạy lần hai phải in toàn `skipped`.

- [x] **Step 2: Đặt lại `field_number` cho lưới trang chủ**

4 gốc nhận `01`–`04` đúng thứ tự cây. **Xóa `field_number` và `field_short_desc` trên 6 term bị hạ cấp** (tid 3, 4, 6, 7, 8, 9) — chúng không còn là tile trang chủ, để số cũ lại sẽ gây nhầm khi biên tập.

`field_short_desc` cho 2 gốc mới, viết theo giọng các mô tả sẵn có:
- Khóa cơ — "Khóa đồng và inox cơ khí, bền bỉ cho mọi loại cửa."
- Chốt cửa & Bản lề — "Chốt Cremone và bản lề đồng, inox chịu tải cao."

Hai gốc tái dùng giữ mô tả cũ (tid 5 "Điều khiển tiện lợi, bảo mật hiện đại." / tid 10 "Chốt, tay nắm và phụ kiện đi kèm đầy đủ.").

- [x] **Step 3: Chạy và kiểm tra cây**

```bash
ddev drush scr scripts/setup/restructure_product_categories.php
ddev drush scr scripts/setup/restructure_product_categories.php   # phải toàn "skipped"
```

Kiểm tra cấu trúc:

```bash
ddev drush sqlq "SELECT t.tid, t.name, COALESCE(h.parent_target_id,0) AS parent \
FROM taxonomy_term_field_data t \
LEFT JOIN taxonomy_term__parent h ON h.entity_id=t.tid \
WHERE t.vid='product_category' ORDER BY parent, t.tid"
```

Phải thấy đúng 4 dòng `parent = 0`, và mỗi term con trỏ đúng cha theo cây đích ở trên.

---

### Task 2: Script gán lại sản phẩm

**Files:**
- Create: `scripts/setup/reassign_product_categories.php`

**Interfaces:**
- Chạy bằng `ddev drush scr scripts/setup/reassign_product_categories.php`
- Nhận cờ `--dry-run` để in bảng dự kiến mà không lưu. Mặc định là lưu thật.

- [x] **Step 1: Viết script theo bảng rule**

Mỗi rule là một bộ ba: term nguồn, closure khớp tiêu đề, tên term đích. Script tra tid đích **theo tên** (Task 1 vừa tạo chúng), fail sớm và rõ ràng nếu không tìm thấy — chạy Task 2 trước Task 1 phải báo lỗi, không được im lặng bỏ qua.

Thứ tự rule quan trọng: rule "còn lại" của mỗi term nguồn chạy sau cùng, sau khi các rule cụ thể đã khớp hết.

Chỉ `save()` node khi `field_category` thực sự đổi, để `changed` không nhảy vô cớ trên 132 node không liên quan.

- [x] **Step 2: Dry-run và đối chiếu số liệu**

```bash
ddev drush scr scripts/setup/reassign_product_categories.php -- --dry-run
```

Bảng in ra phải khớp đúng cột "Số SP" trong mục *Gán lại sản phẩm*: 9, 8, 10, 11, 2, 8, 1, 11, 18. Sai một dòng nào thì sửa rule, chưa chạy thật.

- [x] **Step 3: Chạy thật, rồi kiểm tra không SP nào treo ở cấp 1**

```bash
ddev drush scr scripts/setup/reassign_product_categories.php
ddev drush cr
```

```bash
ddev drush sqlq "SELECT t.name, COUNT(*) AS n \
FROM node__field_category c \
JOIN taxonomy_term_field_data t ON t.tid=c.field_category_target_id \
LEFT JOIN taxonomy_term__parent h ON h.entity_id=t.tid \
WHERE COALESCE(h.parent_target_id,0)=0 GROUP BY 1"
```

Phải trả **0 dòng**. Và tổng SP không đổi:

```bash
ddev drush sqlq "SELECT COUNT(*) FROM node__field_category"   # 186
```

---

### Task 3: HomepageController trả cây lồng

**Files:**
- Modify: `web/modules/custom/keybolts_api/src/Controller/HomepageController.php`
- Test: `web/modules/custom/keybolts_api/tests/src/Kernel/HomepageCategoryTreeTest.php` (create)

**Interfaces:**
- `GET /api/v1/homepage` → `data.categories` là mảng 4 phần tử gốc, mỗi phần tử thêm khóa `children`, đệ quy:
  `['id'=>int,'name'=>string,'number'=>string,'desc'=>string,'image'=>?array,'children'=>array]`
- Khóa `children` là **bổ sung**; `id/name/number/desc/image` giữ nguyên hình dạng nên `CategoryGrid.vue` không phải sửa.

- [x] **Step 1: Viết test thất bại**

Test dựng vocabulary 3 cấp (gốc → con → cháu), gọi controller, khẳng định:
- `categories` có đúng 1 gốc,
- `categories[0]['children'][0]['children'][0]['name']` là term cấp 3,
- term cấp 2 và 3 **không** xuất hiện ở tầng trên cùng của mảng.

- [x] **Step 2: Đổi `categories()` sang dựng cây**

`loadTree('product_category', 0, 1, TRUE)` hiện chỉ lấy cấp 1. Đổi sang `loadTree('product_category', 0, NULL, TRUE)` rồi gom theo `$term->parents` thành cây lồng.

**Giữ nguyên chi phí truy vấn ảnh:** vòng lặp tìm ảnh đại diện hiện chạy một entity query cho mỗi term. Với 22 term thay vì 8 thì phí. **Chỉ tính `image` cho term gốc**; term con trả `'image' => NULL` — mega menu chỉ cần tên và id.

`usort` theo `number` chỉ áp ở tầng gốc; term con sắp theo `weight` như `loadTree` đã trả.

Sửa luôn comment `The eight catalogue categories, in weight order.` — giờ là bốn, và có cây con.

- [x] **Step 3: Sửa `FEATURED_FALLBACK_CATEGORIES`**

Hằng số này tra term **theo tên** (`loadByProperties(['name' => $name])`, dòng ~167). Task 1 đổi tên 3 term nó đang trỏ tới, nên nếu bỏ qua bước này 3 trong 4 tab trang chủ sẽ âm thầm rơi về fallback site-wide — không lỗi, chỉ sai nội dung.

| Nhóm | Tên cũ | Tên mới |
|---|---|---|
| `dong` | `Khóa đồng` | `Khóa đồng` (không đổi) |
| `cremone` | `Chốt Cremone` | `Chốt cửa Cremone` |
| `hotel` | `Khóa khách sạn` | `Khóa khách sạn` (không đổi) |
| `phukien` | `Phụ kiện cửa`, `Bản lề & tay co` | `Phụ kiện cửa & Nội thất`, `Bản lề cửa` |

- [x] **Step 3b: Danh mục rỗng phải mở được, không 404**

Phát sinh khi chạy thật: `/danh-muc/{tid}` lấy tên danh mục từ facet payload (`danh-muc/[slug].vue:14`), mà `ProductFacetBuilder` chỉ đếm term **có** sản phẩm. Nên "Khóa thông minh cửa cổng" (placeholder 0 SP) trả 404 ngay khi mega menu link tới nó — và bất kỳ danh mục nào biên tập viên tạo trước khi có hàng cũng sẽ vậy.

Sửa tại gốc trong `ProductFacetBuilder::labelled()`: khi có filter `category`, ép chính term đó vào tally với count 0 nếu nó chưa có mặt. Chỉ term đang được lọc, để sidebar vẫn chỉ liệt kê lựa chọn còn hàng.

- [x] **Step 4: Chạy test**

```bash
SIMPLETEST_DB="sqlite://localhost/sites/default/files/test.sqlite" \
  vendor/bin/phpunit -c web/core/phpunit.xml.dist \
  web/modules/custom/keybolts_api/tests/src/Kernel/HomepageCategoryTreeTest.php --no-coverage
```

Rồi chạy cả thư mục test của hai module để chắc không vỡ gì.

---

### Task 4: MegaMenu dựng từ cây thật

**Files:**
- Modify: `frontend/app/components/layout/MegaMenu.vue`
- Modify: `frontend/app/types/page.ts`

**Interfaces:**
- `HomeCategory` thêm `children: HomeCategory[]`.

- [x] **Step 1: Thêm `children` vào type**

Trong `page.ts`, `HomeCategory` nhận `children: HomeCategory[]`. Kiểu đệ quy — TypeScript cho phép interface tự tham chiếu.

- [x] **Step 2: Viết lại MegaMenu**

Bỏ hai dòng `locks`/`accessories` (`MegaMenu.vue:15-16`) — chúng cắt mảng phẳng theo vị trí 5, một giả định giờ sai hoàn toàn.

Menu mới: **mỗi nhóm gốc là một cột**, tiêu đề cột là tên nhóm gốc (link tới `/danh-muc/{id}`), bên dưới liệt kê các danh mục cấp 2. Bốn cột thay cho `grid-cols-[1fr_1fr_220px]` hiện tại; giữ nguyên bảng màu và cỡ chữ (`text-eyebrow text-brass-700` cho tiêu đề cột, `text-body` cho mục con) để không lệch với phần còn lại của header.

Không hiện cấp 3 trong mega menu — 4 cột đã kín 720px; cấp 3 để trang danh mục lo.

Nới `w-[720px]` cho vừa 4 cột, vẫn giữ `max-w-[84vw]` để không tràn ở màn hẹp.

Giữ nguyên link "Bộ sưu tập đồng →" tới `/san-pham`, chuyển xuống hàng dưới cùng.

- [x] **Step 3: Kiểm bằng mắt**

Mở `https://vietlong.ddev.site/`, rê vào "Sản phẩm". Bốn cột đúng tên 4 nhóm gốc, mục con đúng cây. Bấm thử một mục cấp 2 → trang danh mục ra đúng sản phẩm.

---

### Task 5: Kiểm chứng đầu-cuối

- [x] **Step 1: Facet roll-up đúng ở 3 cấp**

`ProductFacetBuilder::rollUpToAncestors()` dùng `loadAllParents()` nên đã đi hết chuỗi tổ tiên. Xác nhận bằng số thật: mở `/danh-muc/{tid gốc "Khóa cơ"}` — tổng SP phải bằng tổng các nhánh con cộng lại: **69** (Khóa âm 2 + Full size 10 + Đại sảnh 14 + Đại 14 + Trung 11 + Thông phòng 14 + Tay gạt inox 4).

- [x] **Step 2: Trang danh mục sống ở cả 3 cấp**

Mỗi cấp phải trả 200 và ra đúng sản phẩm, không 404:

```bash
for t in 5 6 3 4 22 23 26 8 24; do
  curl -sS -o /dev/null -w "danh-muc/$t -> %{http_code}\n" -k "https://vietlong.ddev.site/danh-muc/$t"
done
```

- [x] **Step 3: Lưới trang chủ và mega menu**

`curl -sk https://vietlong.ddev.site/api/v1/homepage | python3 -m json.tool` — `data.categories` đúng 4 phần tử, `number` là `01`–`04`, mỗi phần tử có `children` không rỗng (trừ nhánh nào cố ý rỗng), và `image` khác null ở cả 4 gốc.

- [x] **Step 4: Toàn bộ test**

```bash
SIMPLETEST_DB="sqlite://localhost/sites/default/files/test.sqlite" \
  vendor/bin/phpunit -c web/core/phpunit.xml.dist \
  web/modules/custom/keybolts_core/tests web/modules/custom/keybolts_api/tests --no-coverage
cd frontend && npm test
```

Chỉ `LeadSubmissionTest::testGoodRecaptchaScoreIsStoredAlongsideTheLead` được phép fail (lỗi sẵn có, SQLite).

- [x] **Step 5: Commit**

Hai commit tách bạch: một cho script dữ liệu, một cho thay đổi trình bày.

---

## Rollback

Không có thao tác xóa, nên rollback là chạy ngược:

- **Cây taxonomy:** đổi lại tên và cha 16 term tái dùng về bảng *Hiện trạng* ở trên; 6 term mới xóa được vô hại **sau khi** đã gán lại sản phẩm về term cũ.
- **Sản phẩm:** cột `danh_muc` trong `docs/products.csv` vẫn giữ nguyên phân loại gốc của cả 186 SP, đối chiếu 1-1 với 15 term cũ — đủ để dựng lại `field_category` ban đầu.
- **Code:** `git revert` hai commit của Task 3–4.

Chụp ảnh trạng thái trước khi chạy để chắc ăn:

```bash
ddev drush sqlq "SELECT entity_id, field_category_target_id FROM node__field_category" \
  > /tmp/category-before.tsv
```

---

## Ngoài phạm vi lần này

Các bộ lọc trong ảnh feedback (*Tìm theo nhu cầu*, *Lọc tính năng* FaceID / Mở cửa từ xa qua App, *Vị trí cửa*, *Thuộc tính cửa* 1/2/4 cánh) **không** thuộc kế hoạch này — chúng là facet, không phải danh mục, và chưa có field nào trong DB chứa dữ liệu đó. Riêng *Dòng khóa* thì đã có sẵn `field_family` (10 giá trị: KB 1700, KB 1701, KB 8110, KB 108, KB-SUS 201, KB K33, KB-SUS 304, KB 36101, KB CXK1, KB K60), có thể lên facet mà không cần nhập liệu — để dành cho kế hoạch sau.
