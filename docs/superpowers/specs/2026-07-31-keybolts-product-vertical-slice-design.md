# KEYBOLTS — THIẾT KẾ LÁT CẮT DỌC TRỤC SẢN PHẨM

**Ngày:** 31/07/2026
**Phạm vi:** Ngày 1–5 của `docs/keybolts-implementation-plan.md` v2.0, triển khai thật
**Trạng thái:** Đã được duyệt

---

## 1. Bối cảnh và ba quyết định nền

Dự án làm lại keybolts.com.vn. Thiết kế đã hoàn thành dưới dạng 10 file HTML prototype trong `design/` — đây là nguồn sự thật về giao diện. Kế hoạch triển khai đầy đủ nằm ở `docs/keybolts-implementation-plan.md` v2.0.

Ba quyết định được chốt trước khi thiết kế lát cắt này:

**1.1. Kiến trúc: Nuxt SSR tách riêng, giữ đúng plan v2.0.**
Drupal là CMS + API thuần tại `cms.keybolts.com.vn`; Nuxt 4 SSR chạy tiến trình Node riêng tại `keybolts.com.vn`. Yêu cầu ban đầu "custom theme Drupal + Vue" đã bị loại bỏ cho site công khai — Drupal không render HTML cho người dùng cuối. Đánh đổi được chấp nhận: production cần cả PHP lẫn Node + PM2, hai domain, deploy phức tạp hơn; đổi lại SSR đầy đủ, là điều kiện bắt buộc vì SEO/GEO là mục tiêu số một của dự án.

*Hệ quả:* nếu sau này muốn custom admin theme gắn nhận diện Keybolts cho trang quản trị, đó là việc riêng, không ảnh hưởng kiến trúc này.

**1.2. Phạm vi: lát cắt dọc trục sản phẩm.**
Đi xuyên suốt một lần từ taxonomy → content type → API → Nuxt → 3 trang chạy thật, thay vì làm trọn backend rồi mới tới frontend. Lý do: hai chỗ rủi ro nhất của dự án đều nằm trên trục này — ma trận biến thể và facet count — và chỉ lộ ra khi cả stack chạy thật.

**1.3. Biến thể: mỗi biến thể là một node riêng, nhóm theo dòng.**
Trong prototype, catalog liệt kê `Khóa Đồng Đại Sảnh / KB 1700-XL-PVD`, `Khóa Đồng Đại / KB 1700-L-PVD`, `Khóa Đồng Trung / KB 1700-M-PVD`, `Khóa Đồng Thông Phòng / KB 1700-S-PVD` thành bốn card riêng biệt; nhưng trang chi tiết của *Khóa Đồng Đại Sảnh* lại có bộ chọn size XL/L/M/S trỏ tới chính bốn thứ đó. Hai chỗ mô tả cùng một tập dữ liệu theo hai cách.

Chốt: mỗi mã hàng là một node, có URL riêng. Trường `field_family` (vd `KB 1700`) gom các node anh em. Bộ chọn size/hoàn thiện trên trang chi tiết là **kết quả truy vấn**, không phải dữ liệu nhập tay.

Đánh đổi: admin phải nhập từng mã và đặt đúng `field_family`, tốn công hơn mô hình một node chứa bảng biến thể. Đổi lại mỗi mã có một trang được index — đúng thứ một site catalog cần — và catalog khớp nguyên vẹn với prototype.

---

## 2. Cấu trúc repo

```
VietLong/
├── web/                                    Drupal 11 — API thuần
│   └── modules/custom/
│       ├── keybolts_core/                  content model · biến thể · chuẩn hoá tìm kiếm
│       └── keybolts_api/                   REST endpoints /api/v1/*
├── frontend/                               Nuxt 4 (SSR) — toàn bộ giao diện
├── design/                                 prototype — nguồn sự thật giao diện
├── scripts/seed/                           seed 26 SKU + tải & tối ưu ảnh site cũ
└── docs/
```

**Vì sao tách hai module Drupal.** `keybolts_core` giữ cấu trúc dữ liệu và logic nghiệp vụ — gom ma trận biến thể, đếm facet, chuẩn hoá chuỗi tiếng Việt — và **không biết gì về HTTP**. `keybolts_api` chỉ lo tầng vận chuyển: route, đọc tham số, phân trang, envelope, gắn cache tag. Ranh giới này cho phép đổi hình dạng API mà không đụng logic, đổi logic mà không đụng route, và test logic bằng kernel test không cần dựng request.

---

## 3. Mô hình dữ liệu Drupal

### 3.1. Taxonomy

| Vocabulary | Số term | Field riêng |
|---|---|---|
| `brand` | 2 — KEYBOLTS, BALTICA | `field_tag`, `field_desc`, `field_cta_label` |
| `product_category` | 8 — dong, tay-gat, thong-minh, van-tay, khach-san, cremone, ban-le, phu-kien | `field_image`, `field_number` (`01`–`08`), `field_short_desc` |
| `finish` | 4 — pvd, dsf, inox, dong | `field_swatch` (hex), `field_suffix` (`PVD`/`DSF`/`INOX`) |

### 3.2. Content type `product`

Các field quyết định hành vi biến thể và tìm kiếm:

| Field | Vai trò |
|---|---|
| `field_family` | `KB 1700` — khoá nhóm các node anh em |
| `field_size_key` / `field_size_label` / `field_size_note` | `xl` / `Đại sảnh XL` / `Cửa 2 cánh lớn` |
| `field_finish` | Entity ref → `finish`; mỗi node đúng **một** hoàn thiện |
| `field_search_text` | Sinh tự động khi lưu: tên + mã + danh mục + thương hiệu, **đã bỏ dấu** |

Các field còn lại theo mục 4.1 của plan v2.0: `field_product_code` (unique), `field_images`, `field_category`, `field_brand`, `field_badge`, `field_stock_status`, `field_contact_price`, `field_short_desc`, `field_desc_heading`, `field_description`, `field_highlights`, `field_specifications` (Paragraph k/v), `field_policy_cards` (Paragraph), `field_faqs` (Paragraph), `field_assurances` (Paragraph), `field_door_thickness`, `field_origin`, `field_certification`, `field_warranty`, `field_related_products`, `field_featured`, `field_featured_group`, `field_is_new`, `field_sort_order`, `field_meta_tags`.

### 3.3. Ma trận biến thể — quy tắc

`keybolts_core` truy vấn mọi node cùng `field_family`, gom thành ma trận `size_key × finish_key`. Mỗi ô có node tương ứng thì trả slug + mã sản phẩm; **tổ hợp không tồn tại thì đánh dấu không khả dụng, không bịa link**. Prototype hiển thị 3 hoàn thiện × 4 size, nhưng dữ liệu thật gần như chắc chắn khuyết ô — giao diện phải chịu được điều đó.

Hệ quả tích cực: admin thêm một mã mới với đúng `field_family` là bộ chọn tự có thêm lựa chọn, không cần sửa code hay cấu hình.

### 3.4. Module contrib

`jsonapi` (core) · `jsonapi_extras` · `pathauto` · `metatag` · `redirect` · `paragraphs` · `admin_toolbar`

---

## 4. API

Envelope thống nhất:

```json
{ "data": [], "meta": { "total": 26, "page": 1, "limit": 12 }, "facets": {} }
```

| Endpoint | Điểm đáng lưu ý |
|---|---|
| `GET /api/v1/homepage` | Gộp một request: 8 danh mục · 4 nhóm nổi bật (`dong`/`cremone`/`hotel`/`phukien`) · giải pháp · dự án · bài viết · 5 cơ sở |
| `GET /api/v1/products` | Tham số `brand` `category` `finish` `sort` `page`. Trả **facet count cho từng lựa chọn**. `sort` ∈ `featured\|az\|za\|cat`. 12 item/trang |
| `GET /api/v1/products/{slug}` | Chi tiết + **ma trận biến thể** + related + breadcrumb + JSON-LD |
| `GET /api/v1/products/suggest?q=` | Tìm trên `field_search_text` → gõ `khoa van tay` ra `khóa vân tay` |
| `GET /api/v1/menu/{name}` | Menu header/footer; frontend không hard-code |

**Cache:** mọi GET gắn cache tag Drupal, tự purge khi admin sửa nội dung. DDEV hiện chưa có Redis nên lát cắt này dùng cache backend mặc định; Redis thêm ở giai đoạn hạ tầng, không đổi code.

**CORS:** mở cho `localhost:3000` ở môi trường dev.

---

## 5. Frontend Nuxt 4

### 5.1. Token và Tailwind

Dùng **Tailwind CSS 4 với cấu hình CSS-first**. Tailwind 4 khai báo theme bằng directive `@theme` ngay trong CSS, và mỗi token vừa là CSS custom property vừa sinh ra utility class tương ứng. Nghĩa là `tokens.css` bê từ prototype **chính là** file cấu hình — không có tầng phiên dịch giữa thiết kế và code, nên không có chỗ để phát sinh lệch.

```css
@import "tailwindcss";

@theme {
  --color-charcoal-900: #282d30;
  --color-gold-200:     #f7e499;
  --breakpoint-lg:      992px;
  --font-sans:          "Roboto", -apple-system, "Segoe UI", sans-serif;
}
```

Toàn bộ giá trị lấy nguyên từ prototype: màu, thang chữ (12/14/16/24/40/56), weight 300/400/700, thang spacing gốc 3px, breakpoint 576/768/992/1200/1300, container 1360px, `--radius-sm` 50px, shadow none trừ floating, duration 150/200ms.

### 5.2. Xử lý `oklch(from …)`

Prototype dùng relative color syntax cho toàn thang `neutral-*` và `gold-*`. Cú pháp này chưa được hỗ trợ rộng — Safari cũ và một số WebView Android render sai màu thương hiệu.

Giải pháp: script tính sẵn toàn bộ thang ra hex tĩnh, đặt làm giá trị mặc định trong `@theme`; bản `oklch()` chỉ bọc trong `@supports` như lớp nâng cao. Kết quả: trình duyệt cũ vẫn ra đúng màu, trình duyệt mới được hưởng gamut rộng hơn.

### 5.3. Responsive

Một composable `useViewport` duy nhất cấp `isMobile` / `isWide` / `utilWide`, đúng theo logic trong prototype (tagline cạnh logo chỉ hiện khi rộng; mục phụ top bar ẩn dần; bản đồ + danh sách cơ sở xếp dọc dưới 900px). Không rải media query rời rạc khắp component.

### 5.4. Trang trong lát cắt

`/` (11 section) · `/san-pham` · `/san-pham/[slug]` · `/danh-muc/[slug]` và `/thuong-hieu/[slug]` dùng lại layout listing với H1, mô tả và canonical riêng.

### 5.5. Quy tắc

- Không gọi API trực tiếp trong component — mọi call qua `services/`
- Không hard-code URL API — dùng `runtimeConfig`
- Không hard-code màu, cỡ chữ, khoảng cách — chỉ dùng token
- Component vượt 300 dòng phải tách

---

## 6. Seed dữ liệu

Script Drush tạo 2 brand, 8 danh mục, 4 hoàn thiện, 26 SKU lấy từ mảng `CATALOG` trong prototype, gán `field_family` theo tiền tố mã sản phẩm.

Ảnh tải từ site cũ — đã xác nhận URL còn sống (HTTP 200). **Nhưng file gốc rất nặng: 2.6 MB cho một PNG, 5.4 MB cho một JPG.** Để nguyên là không dùng được, nên seed phải chuyển WebP và sinh nhiều kích thước ngay lúc nạp. Đây cũng là bằng chứng sớm cho hạng mục "tối ưu ảnh" trong plan: nó là việc bắt buộc, không phải tinh chỉnh cuối dự án.

Script chạy lại được nhiều lần mà không tạo trùng.

---

## 7. Kiểm thử

Chỉ test phần có logic thật, không test getter/setter:

| Loại | Đối tượng |
|---|---|
| PHPUnit kernel | Gom ma trận biến thể, **kể cả trường hợp tổ hợp khuyết**; đếm facet; chuẩn hoá bỏ dấu tiếng Việt |
| Vitest | Composable state bộ lọc — đẩy/đọc query string, hoạt động đúng khi back/forward |
| Đối chiếu mắt | Mở prototype và bản build song song ở 375 / 768 / 1440px |

---

## 8. Điều kiện hoàn thành lát cắt

- [ ] `ddev drush status` sạch, 6 module contrib bật, 3 taxonomy có đủ term
- [ ] 26 SKU có ảnh đã tối ưu, `field_family` đúng nhóm
- [ ] 4 endpoint trả đúng envelope; facet count khớp số sản phẩm thật
- [ ] `/api/v1/products/suggest?q=khoa van tay` trả về khóa vân tay
- [ ] Nuxt SSR: xem HTML nguồn thấy nội dung sản phẩm, không phải div rỗng
- [ ] Ba trang khớp prototype ở 375 / 768 / 1440px
- [ ] Đổi hoàn thiện × size trên trang chi tiết điều hướng đúng node anh em
- [ ] Không còn `oklch(from …)` trong CSS đầu ra; kiểm tra màu trên Safari
- [ ] Test ở mục 7 chạy xanh

---

## 9. Ngoài phạm vi lát cắt này

Các content type `article`, `project`, `branch`, `policy`, `page`, `author`; entity `lead` và toàn bộ form; 7 trang còn lại; migration thật từ site cũ; Redis; deploy staging/production. Tất cả theo plan v2.0, làm ở các lát cắt sau.

---

*Ghi chú: thư mục dự án chưa phải git repository nên tài liệu này chưa được commit.*
