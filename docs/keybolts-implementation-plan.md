# KEYBOLTS — KẾ HOẠCH TRIỂN KHAI CHI TIẾT

**Dự án:** Làm lại website keybolts.com.vn
**Nền tảng:** Drupal 11 (Headless CMS) + Nuxt 4 / Vue.js (Frontend SSR)
**Thời gian:** 2 tuần — 10 ngày làm việc
**Phiên bản tài liệu:** 2.0 — 31/07/2026 — *cập nhật toàn bộ theo bộ thiết kế đã chốt trong `design/`*
**Người lập:** Lưu Xuân Thế · 0932.355.207 · thexuanluu@gmail.com

> **Thay đổi lớn so với bản 1.0:** thiết kế đã hoàn thành và bàn giao dưới dạng **10 file HTML prototype chạy được** (không phải Figma). Toàn bộ sitemap, content model, API, design system và timeline trong tài liệu này đã được viết lại để khớp đúng với thiết kế đó. Phần khác biệt giữa bản 1.0 và thiết kế thực tế được ghi rõ ở **mục 15 — Nhật ký thay đổi**.

---

## 0. ĐỌC TRƯỚC — GIẢ ĐỊNH VÀ CẢNH BÁO PHẠM VI

Timeline 2 tuần chỉ đạt được nếu **đúng các giả định dưới đây**. Nếu một giả định sai, timeline phải giãn.

### 0.1. Giả định về nhân sự

| Vai trò | Số lượng | Tham gia |
|---|---|---|
| UI/UX Designer | 1 | Ngày 1–2 (chuyển giao + bổ sung màn thiếu), 7–9 (design QA) |
| Drupal Backend | 1 | Ngày 1–10 (full) |
| Nuxt Frontend | 2 | Ngày 1–10 (full) |
| PM / QA | 1 | Ngày 1–10 (50%) |

Thiết kế đã xong nên designer **không còn nằm trên đường găng**. Frontend bắt đầu từ **ngày 1** thay vì ngày 2.

Nếu triển khai bằng **1 người full-stack**, timeline thực tế là **4–5 tuần**, không phải 2 tuần.

### 0.2. Đầu vào — trạng thái hiện tại

Nhờ bộ thiết kế, một phần lớn thông tin đã có sẵn. Bảng dưới phân biệt rõ **đã có** và **còn thiếu**.

**Đã có (trích từ `design/`, chỉ cần khách xác nhận):**

| Hạng mục | Giá trị trong thiết kế |
|---|---|
| Pháp nhân | Công ty TNHH XNK Khóa Cửa Việt Long |
| Thương hiệu | Keybolts (khóa & khóa điện tử) · Baltica (phụ kiện đồng) |
| Hotline | 1900 9018 |
| Email | khoacuavietlong@gmail.com |
| Trụ sở | Khu phố Lê Hồng Phong, P. Đông Ngàn, TP. Từ Sơn, Bắc Ninh |
| 5 cơ sở | VP bán buôn (0912.411.309) · Showroom Từ Sơn 217–219 Trần Phú (0968.689.112) · Kho Võ Cường, TP. Bắc Ninh (0981.255.215) · Showroom Việt Trì, 1308 ĐL Hùng Vương (0984.84.6655) · Showroom Vĩnh Yên, 531 Mê Linh (0984.84.6622) |
| Giờ làm việc | T2–T7: 8:00–17:30 (footer) · 8:00–18:00 cả tuần (trang Liên hệ) — **cần thống nhất** |
| Kênh ngoài | Facebook `khoacuacaocapvietlong` · Shopee · Lazada · YouTube · Zalo |
| Chứng nhận | CE-CFF · bảo hành 5–10 năm |
| Ảnh sản phẩm | Đang trỏ về `https://keybolts.com.vn/sites/default/files/...` → **ảnh gốc nằm trên site cũ**, migration lấy được |

**Còn thiếu — phải có trước ngày 1, nếu thiếu thì tính từ ngày nhận đủ:**

- Logo vector (SVG/AI) — thiết kế hiện dùng logo chữ.
- Quyền truy cập website cũ: hosting, database, FTP/SSH (để export dữ liệu + **toàn bộ thư mục ảnh**).
- Quyền quản lý domain (DNS) để trỏ khi go-live.
- Hạ tầng server production hoặc quyết định thuê VPS.
- Số Zalo OA (thiết kế đang dùng chung hotline 1900 9018), email nhận lead (`sales@`?).
- Danh sách sản phẩm ưu tiên: thiết kế mẫu có **26 SKU**, trang Giới thiệu ghi **200+ mã** → cần danh sách thật đầy đủ.
- Người duyệt (1 đầu mối duy nhất) và cam kết phản hồi trong **24h**.

**Nội dung cần khách chốt (mâu thuẫn trong bản thiết kế):**

| Điểm | Chỗ A | Chỗ B | Cần chốt |
|---|---|---|---|
| Số năm kinh nghiệm | Trang chủ: "15+ năm" | Giới thiệu: "Từ 2014" (= 12 năm) | Con số đúng |
| Giờ làm việc | Footer: T2–T7 8:00–17:30 | Liên hệ: cả tuần 8:00–18:00 | Một mốc duy nhất |
| Menu chính | Trang chủ + Chi tiết SP: Sản phẩm · Giải pháp · Dự án · Kiến thức · Đại lý | 7 trang còn lại: Sản phẩm · Giới thiệu · Dự án · Tin tức · Đại lý · Liên hệ | **Một cấu trúc menu duy nhất** (xem 3.1) |

### 0.3. Phạm vi 2 tuần

| ✓ LÀM trong 2 tuần | ✕ KHÔNG LÀM — đẩy Phase 2 |
|---|---|
| 10 template trang có trong `design/` (mục 2) ở mức hoàn chỉnh | Thanh toán online / giỏ hàng |
| Content model Drupal đầy đủ theo mục 4 | CRM, đại lý đăng nhập, dealer portal |
| Migration sản phẩm + ảnh + danh mục + bài viết từ site cũ | Trang **chi tiết giải pháp** (`/giai-phap/{slug}`) — thiết kế chưa có, chỉ làm khối giải pháp trên trang chủ trỏ về bộ lọc |
| Bộ lọc (thương hiệu/danh mục/hoàn thiện), search overlay, form lead, Zalo/hotline | Viết mới nội dung SEO/GEO — chỉ dựng **cấu trúc** để đăng sau |
| SEO kỹ thuật: SSR, meta, sitemap, canonical, breadcrumb, schema, 301 redirect | Đa ngôn ngữ · Semantic search / AI search |
| Responsive theo 5 breakpoint của design system, tối ưu ảnh, cache | Chụp lại ảnh sản phẩm (dùng ảnh hiện có trên site cũ) |
| Trang 404 / 500 / kết quả tìm kiếm — **dựng mới theo design token** (thiết kế chưa có) | 2FA, WAF nâng cao, monitoring stack đầy đủ (chỉ bật uptime + backup cơ bản) |
| Lên dev → staging → production, bàn giao tài khoản + hướng dẫn | |

---

## 1. KIẾN TRÚC TỔNG THỂ

```
                    Người dùng / Googlebot / AI Crawler
                                   │
                          Nginx Reverse Proxy
                  (SSL · brotli/gzip · cache tĩnh)
                    ┌──────────────┴──────────────┐
                    │                             │
            keybolts.com.vn              cms.keybolts.com.vn
             Nuxt 4 (SSR, Node)            Drupal 11 (PHP-FPM)
                    │                             │
                    └────── JSON:API / REST ──────┤
                                                  │
                                        MariaDB 10.11 + Redis
                                                  │
                                        Storage ảnh (local/S3)
```

**Nguyên tắc:** Frontend không truy cập database. Mọi dữ liệu đi qua API. Drupal không render HTML cho người dùng cuối, chỉ phục vụ admin + API.

### 1.1. Stack chốt

| Lớp | Công nghệ | Ghi chú |
|---|---|---|
| CMS | Drupal 11.x | PHP 8.3, Composer-managed |
| Database | MariaDB 10.11 | UTF8MB4 |
| Cache | Redis 7 | Drupal cache + API response cache |
| API | JSON:API (core) + custom module `keybolts_api` | REST cho search & lead |
| Frontend | Nuxt 4 + Vue 3 + TypeScript | SSR mode |
| CSS | **CSS custom properties (design token từ `design/`) + Tailwind CSS 4 preset map 1-1** | Xem 7.6 — token là nguồn sự thật, không viết lại màu bằng tay |
| State | Pinia | Filter/search state, trạng thái drawer + overlay |
| Web server | Nginx | Reverse proxy · SSL · brotli/gzip · cache tĩnh (ảnh, JS, CSS) |
| CI/CD | GitHub Actions | Build + deploy tự động |
| Local | DDEV (Drupal) + Node 22 (Nuxt) | |

---

## 2. SITEMAP — DANH SÁCH TRANG ĐẦY ĐỦ

### 2.1. Bảng trang — ánh xạ với file thiết kế

| # | Trang | URL | File thiết kế | Kiểu render | Nguồn dữ liệu |
|---|---|---|---|---|---|
| 1 | Trang chủ | `/` | `Keybolts Homepage.html` | SSR + ISR 10 phút | `/api/v1/homepage` |
| 2 | Tổng sản phẩm | `/san-pham` | `Keybolts Products.html` | SSR | `/api/v1/products` |
| 3 | Danh mục sản phẩm | `/danh-muc/{slug}` | *dùng lại template Products* | SSR | `/api/v1/products?category=` |
| 4 | Thương hiệu | `/thuong-hieu/{keybolts\|baltica}` | *dùng lại template Products* | SSR | `/api/v1/products?brand=` |
| 5 | Chi tiết sản phẩm | `/san-pham/{slug}` | `Keybolts Product Detail.html` | SSR + ISR 30 phút | `/api/v1/products/{slug}` |
| 6 | Danh sách dự án | `/du-an` | `Keybolts Projects.html` | SSR | `/api/v1/projects` |
| 7 | Tin tức / Tư vấn | `/tin-tuc` | `Keybolts News.html` | SSR | `/api/v1/articles` |
| 8 | Chi tiết bài viết | `/tin-tuc/{slug}` | `Keybolts Article.html` | SSR + ISR | `/api/v1/articles/{slug}` |
| 9 | Hệ thống đại lý | `/dai-ly` | `Keybolts Dealers.html` | SSR | `/api/v1/branches` + form |
| 10 | Giới thiệu | `/gioi-thieu` | `Keybolts About.html` | SSG | Page node |
| 11 | Liên hệ | `/lien-he` | `Keybolts Contact.html` | SSG | Page + branches |
| 12 | Chính sách | `/chinh-sach/{slug}` | `Keybolts Policies.html` | SSG | Policy nodes |
| 13 | Kết quả tìm kiếm | `/tim-kiem?q=` | ✕ chưa có — dựng theo template Products | CSR | `/api/v1/products/search` |
| 14 | Trang 404 | `/404` | ✕ chưa có — dựng theo token | Static | — |
| 15 | Trang lỗi 500 | `/500` | ✕ chưa có — dựng theo token | Static | — |

**Trang hệ thống:** `/sitemap.xml`, `/robots.txt`, `/rss.xml`.

**Ba quyết định về routing (khác bản 1.0 — cần khách xác nhận):**

1. **Không có trang chi tiết dự án.** Thiết kế `Projects.html` render dự án dạng card có tiêu đề, loại công trình, mô tả và dòng sản phẩm sử dụng — **không có link đi tiếp**. Giữ nguyên: dự án là card, không có `/du-an/{slug}`. Nếu khách muốn trang chi tiết → Phase 2.
2. **Không có trang chi tiết giải pháp.** Khối "Chọn theo loại công trình" trên trang chủ (Biệt thự · Căn hộ · Khách sạn · Văn phòng) trỏ về `/san-pham` với bộ lọc tương ứng, không phải sang `/giai-phap/{slug}`.
3. **`/danh-muc/{slug}` và `/thuong-hieu/{slug}` là bổ sung của đội dự án, không có trong thiết kế.** Thiết kế lọc bằng chip trên cùng một trang `/san-pham`. Nhưng breadcrumb trang chi tiết sản phẩm đã là `Trang chủ / Sản phẩm / Khóa đồng / …` → cần URL danh mục thật để SEO và breadcrumb có đích đến. Hai route này **dùng lại y nguyên layout Products**, chỉ khác: H1 = tên danh mục, có mô tả danh mục, canonical trỏ về chính nó, chip danh mục ở trạng thái đã chọn.

**Trang cảm ơn `/cam-on` đã bỏ.** Mọi form trong thiết kế dùng **trạng thái thành công hiển thị tại chỗ** ("Đã nhận yêu cầu! …" + nút "Gửi yêu cầu khác"). GA4 `generate_lead` bắn khi trạng thái này xuất hiện.

### 2.2. Taxonomy sản phẩm — theo đúng thiết kế

**Vocabulary `brand`** (2 term, có mô tả + tag + CTA riêng, hiển thị thành 2 card lớn trên `/san-pham`):

| Key | Tên | Tag | Mô tả |
|---|---|---|---|
| `keybolts` | KEYBOLTS | Khóa cửa & khóa điện tử | Khóa đồng, tay gạt, thông minh, vân tay, thẻ từ khách sạn — dòng chủ lực, CE-CFF, BH 5–10 năm |
| `baltica` | BALTICA | Phụ kiện đồng cao cấp | Chốt Cremone, bản lề, tay co, tay nắm, phụ kiện cửa bằng đồng — cho biệt thự và nội thất tân cổ điển |

**Vocabulary `product_category`** — thiết kế dùng **8 danh mục phẳng** (không 2 cấp như bản 1.0):

| Key | Tên | Ghi chú hiển thị |
|---|---|---|
| `dong` | Khóa đồng | |
| `tay-gat` | Khóa tay gạt | |
| `thong-minh` | Khóa thông minh | Có trên trang chủ, ô "01" |
| `van-tay` | Khóa vân tay | |
| `khach-san` | Khóa khách sạn | Thẻ từ |
| `cremone` | Chốt Cremone | Thuộc Baltica |
| `ban-le` | Bản lề & tay co | |
| `phu-kien` | Phụ kiện cửa | |

> Trang chủ hiển thị 8 ô danh mục có đánh số `01`–`08` (bao gồm cả `Khóa chống trộm`). Danh sách chip trên `/san-pham` là 8 mục ở bảng trên + "Tất cả sản phẩm". **Cần chốt với khách:** `Khóa chống trộm` là danh mục riêng hay chỉ là nhãn marketing → nếu là danh mục thì cả hai chỗ phải là 9.

**Vocabulary `finish` (Hoàn thiện)** — mới, không có trong bản 1.0. Mỗi term có **mã màu swatch** để render ô màu trong bộ lọc:

| Key | Nhãn | Swatch |
|---|---|---|
| `pvd` | Vàng bóng PVD | `#c69148` |
| `dsf` | Rêu DSF | `#6b6f5c` |
| `inox` | Màu INOX | `#b9bec2` |
| `dong` | Đồng nguyên chất | `#a97434` |

**Vocabulary `project_type`:** Biệt thự · Khách sạn · Căn hộ / Chung cư · Văn phòng.

**Vocabulary `article_category`:** Chọn khóa (`guide`) · So sánh (`compare`) · Hướng dẫn (`howto`) · FAQ (`faq`).

> Danh mục, thương hiệu, hoàn thiện do admin tự thêm/sửa/sắp xếp trong Drupal. Không hard-code trong frontend — kể cả mã màu swatch.

---

## 3. MENU — CẤU TRÚC ĐIỀU HƯỚNG

### 3.1. Header Desktop

```
┌──────────────────────────────────────────────────────────────────────┐
│ TOP BAR — nền charcoal-900, chữ trắng                                │
│ Nhà nhập khẩu & phân phối khóa cửa cao cấp — Bắc Ninh · Phú Thọ ·    │
│ Vĩnh Phúc   │ Chứng nhận CE-CFF │ Bảo hành 5–10 năm │ 📞 1900 9018   │
├──────────────────────────────────────────────────────────────────────┤
│ MAIN BAR — nền trắng                                                 │
│ [KEYBOLTS]                     Sản phẩm▾  Giới thiệu  Dự án          │
│  Premium Hardware              Tin tức  Đại lý  Liên hệ              │
│  Khóa cửa & phụ kiện nhập khẩu           [🔍]  [Nhận tư vấn]         │
└──────────────────────────────────────────────────────────────────────┘
```

**Chốt cấu trúc menu (giải quyết mâu thuẫn ở 0.2):** dùng bộ 6 mục của 7/10 trang thiết kế — **Sản phẩm · Giới thiệu · Dự án · Tin tức · Đại lý · Liên hệ**. Lý do: mỗi mục có một trang thật đứng sau; bộ "Giải pháp / Kiến thức" ở trang chủ trỏ tới các anchor và trang không tồn tại (`#solutions`, `#guides`).

- Tagline "Premium Hardware / Khóa cửa & phụ kiện nhập khẩu" cạnh logo **chỉ hiện khi viewport rộng** (`isWide`), ẩn ở màn hẹp.
- Các mục top bar phụ (CE-CFF, bảo hành) ẩn dần khi hẹp (`utilWide`).
- Nút CTA `Nhận tư vấn` nền `gold-200`, bo tròn viên thuốc (`radius-sm` = 50px), chữ in hoa.

**Mega menu "Sản phẩm"** (mở khi hover, bật/tắt qua prop `showMegaMenu`):

| Cột 1 — Khóa cửa | Cột 2 — Phụ kiện | Cột 3 — Điểm nhấn |
|---|---|---|
| Khóa thông minh | Bản lề | Ảnh + "Bộ sưu tập đồng →" |
| Khóa vân tay | Chốt cửa Cremone | |
| Khóa khách sạn | Phụ kiện cửa | |
| Khóa tay gạt đồng | Phụ kiện tủ bếp | |
| Khóa chống trộm | | |

### 3.2. Header Mobile

```
┌────────────────────────────────┐
│ [☰]   [KEYBOLTS]    [🔍]  [📞] │
└────────────────────────────────┘

Panel điều hướng (trượt xuống, toàn chiều rộng):
  Sản phẩm
  Giới thiệu
  Dự án
  Tin tức
  Đại lý
  Liên hệ
  ─────────────────
  [ Nhận tư vấn ]  ← nút full-width, nền gold-200
```

Thiết kế mobile dùng **panel trượt xuống dưới header**, không phải drawer trượt từ trái như bản 1.0. Menu mobile là **danh sách phẳng, không accordion** — mega menu chỉ tồn tại ở desktop.

### 3.3. Search Overlay (dùng chung mọi trang)

Không phải ô input trong header. Bấm 🔍 → mở **overlay toàn màn hình**:

| Thành phần | Hành vi |
|---|---|
| Ô nhập | Autofocus, debounce 300ms |
| Tiêu đề động | Rỗng → "Gợi ý phổ biến"; có query → "Kết quả (n)" |
| Gợi ý | Mỗi dòng: tên sản phẩm + meta (danh mục · model), có thumbnail |
| Quick tags | Chip từ khoá bấm để điền sẵn vào ô nhập |
| Empty state | "Không tìm thấy sản phẩm phù hợp." + "Gọi 1900 9018 để được tư vấn →" |
| Đóng | ESC · click nền · nút ✕ |
| Enter | Chuyển sang `/tim-kiem?q=` (trang này đội dự án dựng thêm) |

### 3.4. Sticky CTA Bar (chỉ mobile, cố định đáy màn hình)

```
┌──────────┬──────────┬────────────────────┐
│  📞 Gọi  │ 💬 Zalo  │   Nhận tư vấn      │
└──────────┴──────────┴────────────────────┘
   flex:1     flex:1     flex nở (nền gold-200)
```

Bật/tắt qua prop `stickyMobileCta`. Ẩn khi đang mở panel menu hoặc search overlay.

### 3.5. Footer (nền charcoal-900, 4 cột desktop → xếp dọc mobile)

| Cột 1 — Thương hiệu | Cột 2 — Sản phẩm | Cột 3 — Hỗ trợ | Cột 4 — Liên hệ |
|---|---|---|---|
| Mô tả ngắn công ty | Khóa thông minh | Cam kết chất lượng | 📞 1900 9018 |
| Tên pháp nhân đầy đủ | Khóa vân tay | Chính sách bảo hành | khoacuavietlong@gmail.com |
| "Sản phẩm có mặt tại": Facebook · Shopee · Lazada · YouTube · Zalo | Khóa khách sạn | Giao nhận hàng | Địa chỉ trụ sở |
| Badge "Đã đăng ký Bộ Công Thương" (link online.gov.vn) | Khóa đồng nhập khẩu | Đổi trả hàng | Giờ làm việc |
| | Bản lề & phụ kiện | Câu hỏi thường gặp | |
| | Chốt cửa Cremone | Trở thành đại lý | |

**Footer bottom:** © 2026 Công ty TNHH XNK Khóa Cửa Việt Long — Keybolts · Điều khoản · Bảo mật · Sitemap

### 3.6. Breadcrumb

Xuất hiện trên mọi trang trừ trang chủ, đặt ngay trên hero, dạng `Trang chủ / Sản phẩm / Khóa đồng / Khóa Đồng Đại Sảnh`. Có schema `BreadcrumbList`.

---

## 4. CONTENT MODEL DRUPAL 11

### 4.1. Content Type: `product` (Sản phẩm)

Bảng dưới đã bổ sung các field mà **thiết kế bắt buộc phải có** và bỏ các field thiết kế không dùng. Cột "Vị trí" chỉ ra nơi field xuất hiện trên giao diện.

| Field | Machine name | Kiểu | Bắt buộc | Vị trí / Ghi chú |
|---|---|---|---|---|
| Tên sản phẩm | `title` | Text | ✓ | H1, card |
| Đường dẫn | `path` | Pathauto | ✓ | `/san-pham/[title]` |
| Mã sản phẩm | `field_product_code` | Text | ✓ | "Mã sản phẩm: KB 1700-XL-PVD", unique |
| Ảnh sản phẩm | `field_images` | Image (multi) | ✓ | Gallery — thiết kế dùng **4 ảnh**, alt bắt buộc |
| Danh mục | `field_category` | Taxonomy ref | ✓ | `product_category`, dùng cho breadcrumb + lọc |
| Thương hiệu | `field_brand` | Taxonomy ref | ✓ | `brand` — badge KEYBOLTS (nền charcoal, chữ gold) / BALTICA (nền brass-700, chữ trắng) |
| **Hoàn thiện** | `field_finish` | Taxonomy ref | ✓ | `finish` — bộ chọn swatch trên trang chi tiết + bộ lọc |
| **Hoàn thiện khả dụng** | `field_finish_options` | Taxonomy (multi) | | Các màu người dùng chọn được ở trang chi tiết (thiết kế: 3 lựa chọn) |
| **Biến thể kích thước** | `field_size_variants` | Paragraph (multi) | | Mỗi dòng: `label` (Đại sảnh XL) · `note` (Cửa 2 cánh lớn) · `code` (KB 1700-XL) |
| **Nhãn card** | `field_badge` | List | | Bán chạy · Mới · Cao cấp — hiển thị góc ảnh card |
| Tình trạng | `field_stock_status` | List | ✓ | "Còn hàng — giao 2–5 ngày" / Hết hàng / Đặt trước |
| Giá | `field_price` | Decimal | | **Thiết kế luôn hiển thị "Liên hệ"** — giữ field cho Phase 2 |
| Liên hệ để biết giá | `field_contact_price` | Boolean | | Mặc định TRUE; kèm dòng "Giá thay đổi theo size, hoàn thiện và số lượng" |
| Mô tả ngắn | `field_short_desc` | Text long | ✓ | Card + meta description |
| **Tiêu đề mô tả** | `field_desc_heading` | Text | | H3 trong tab "Mô tả sản phẩm" |
| Mô tả chi tiết | `field_description` | Text (formatted) | ✓ | Tab 1 — CKEditor |
| **Điểm nổi bật** | `field_highlights` | Text (multi) | | Danh sách gạch đầu dòng trong tab mô tả (thiết kế: 5 dòng) |
| Thông số kỹ thuật | `field_specifications` | Paragraph (multi) | ✓ | Tab 2 — cặp `k` / `v`; thiết kế dùng 8–10 dòng |
| **Chính sách hiển thị** | `field_policy_cards` | Paragraph (multi) | | Tab 3 — 3 thẻ `title` + `desc` (Bảo hành / Giao hàng / Đổi trả) |
| **Hỏi đáp** | `field_faqs` | Paragraph (multi) | | Tab 4 — cặp `q` / `a`, dùng cho schema `FAQPage` |
| **Cam kết** | `field_assurances` | Paragraph (multi) | | Dải dưới nút CTA — `title` + `desc` |
| Loại cửa phù hợp | `field_door_type` | Taxonomy (multi) | | Gỗ tự nhiên, công nghiệp, kính, nhôm, sắt |
| Độ dày cửa | `field_door_thickness` | Text | | "40 – 55 mm" — thông số quyết định khi tư vấn |
| Công nghệ mở khóa | `field_unlock_methods` | Taxonomy (multi) | | Vân tay, thẻ từ, mật mã, app, chìa cơ |
| Chất liệu | `field_material` | Taxonomy ref | | |
| Xuất xứ | `field_origin` | Text | | |
| Chứng nhận | `field_certification` | Text (multi) | | "CE-CFF" |
| Bảo hành | `field_warranty` | Text | | "5–10 năm" |
| Sản phẩm liên quan | `field_related_products` | Node ref (multi) | | 3–4 sp; tự động theo danh mục nếu trống |
| Nổi bật | `field_featured` | Boolean | | |
| **Nhóm nổi bật** | `field_featured_group` | List | | `dong` · `cremone` · `hotel` · `phukien` — 4 tab khối "Sản phẩm nổi bật" trang chủ |
| Sản phẩm mới | `field_is_new` | Boolean | | |
| Thứ tự | `field_sort_order` | Integer | | Dùng cho sắp xếp "Nổi bật" |
| SEO | `field_meta_tags` | Metatag | | Title, description, OG |

**Bỏ khỏi phạm vi** (thiết kế không dùng, tránh tạo field chết): `field_videos`, `field_color`, `field_dimensions`, `field_weight`, `field_promotion`, `field_installation`, `field_installation_video`, `field_warranty_policy`, `field_shipping_policy`, `field_related_articles`. Nếu khách cần → Phase 2.

### 4.2. Content Type: `article` (Tin tức / Tư vấn)

Trang `Article.html` là bài viết chuẩn GEO/AI-search, cần model riêng chứ không chỉ `body`:

| Field | Kiểu | Ghi chú |
|---|---|---|
| `title`, `path` | | `/tin-tuc/[title]` |
| `field_thumbnail` | Image | Card + ảnh nổi bật |
| `field_article_category` | Taxonomy ref | `article_category` (guide/compare/howto/faq) |
| `field_summary` | Text long | Sapo dưới H1 + card |
| **`field_quick_answer`** | Text long | Khối "Trả lời nhanh" đầu bài — **thành phần GEO quan trọng nhất**, cũng dùng cho `speakable` schema |
| **`field_sections`** | Paragraph (multi) | Mỗi section: `id` (anchor) · `h` (tiêu đề) · `paras` (nhiều đoạn) · `list` (bullet) · `note` (callout) → sinh mục lục tự động |
| **`field_compare_table`** | Paragraph (multi) | Bảng so sánh 4 cột (Loại cửa · Độ dày · Kiểu khóa nên dùng · Dự phòng) |
| **`field_faqs`** | Paragraph (multi) | `q`/`a` → schema `FAQPage` |
| **`field_mentioned_products`** | Node ref (multi) | Sidebar "Sản phẩm nhắc đến" |
| `field_author` | Entity ref → `author` | Hộp tác giả cuối bài (tên + mô tả nhóm) |
| `field_publish_date`, **`field_updated_date`** | Date | Hiển thị "Cập nhật 07/2026" |
| **`field_read_time`** | Integer | "8 phút đọc" |
| `field_featured` | Boolean | Bài nổi bật đầu trang `/tin-tuc` |
| `field_meta_tags` | Metatag | |

**Mục lục ("Nội dung bài viết")** sinh tự động từ `field_sections`, không nhập tay.

### 4.3. Các content type khác

| Content type | Field chính |
|---|---|
| `project` | `title`, `field_project_type` (taxonomy), `field_summary`, `field_image`, `field_products_used` (text: "KB 1700 series"), `field_sort_order` — **không có path/body**, dự án không có trang riêng |
| `branch` | `title`, `field_tag` (Bán buôn / Cơ sở 1…), `field_address`, `field_phone`, `field_tel` (số thuần cho `tel:`), `field_sort_order` — Google Maps sinh từ địa chỉ, **không cần field embed** |
| `policy` | `title`, `path`, `field_policy_key` (warranty/shipping/returns/payment/privacy), `field_eyebrow` ("Chính sách 01"), `field_intro`, `field_items` (Paragraph `k`/`v`), `field_note`, `field_sort_order`, metatag |
| `page` | `title`, `path`, `body`, metatag — dùng cho Giới thiệu (kèm các field dưới) |
| `author` | `title`, `field_bio`, `field_avatar` |

**Trang Giới thiệu** cần thêm các field có cấu trúc (đặt trên `page` hoặc content type `about` riêng):
`field_facts` (5 con số: 2014 / 5 / 200+ / 10 / CE-CFF) · `field_credentials` (4 dòng) · `field_segments` (4 nhóm khách: tiêu đề, mô tả, CTA, ảnh) · `field_process_steps` (5 bước: `n`, `title`, `desc`) · `field_values` (4 cam kết: `title`, `desc`, icon).

**Trang Đại lý** cần: `field_benefits` (4 quyền lợi `n`/`title`/`desc`) · `field_criteria` (4 điều kiện, danh sách text).

**Bỏ content type `banner`** — thiết kế dùng hero tĩnh, không có slider (xem 6.1). Bỏ `solution` — không có trang chi tiết giải pháp; 4 ô giải pháp trang chủ là cấu hình đơn giản (tiêu đề, mô tả, tag, ảnh, link lọc), lưu như `page` block hoặc config.

**Bỏ `dealer` (danh sách đại lý bán lẻ).** Thiết kế trang `/dai-ly` là **trang tuyển đại lý** (quyền lợi + điều kiện + form đăng ký) và hiển thị **5 cơ sở của chính công ty** (`branch`), không phải danh sách đại lý bên thứ ba. Nếu khách muốn danh bạ đại lý → Phase 2.

### 4.4. Custom Entity: `lead`

Không dùng content type (tránh lẫn với nội dung). Dùng custom entity để phân quyền riêng.

| Field | Kiểu |
|---|---|
| `full_name` | Text (bắt buộc) |
| `phone` | Text (bắt buộc, validate SĐT VN) |
| `email` | Email (optional — chỉ form trang chủ có) |
| `message` | Text long — nhãn thay đổi theo form: "Nhu cầu / loại cửa" · "Số lượng & loại cửa" · "Nội dung" |
| `product_ref` | Entity ref → product (auto-fill từ trang chi tiết) |
| `product_variant` | Text (auto — model đang chọn, vd `KB 1700-XL-PVD`) |
| `source_url` | Text (auto) |
| `lead_source` | List: **Trang chủ · Chi tiết sản phẩm · Danh sách sản phẩm · Liên hệ · Đăng ký đại lý · Sticky bar** |
| `status` | List: Mới / Đã liên hệ / Đang tư vấn / Đã báo giá / Thành công / Thất bại |
| `assigned_to` | User ref |
| `note` | Text long (nội bộ) |
| `created` | Timestamp (auto) |

### 4.5. Taxonomy Vocabularies

`brand` (2 term, có logo + tag + desc + CTA) · `product_category` (8 term phẳng, có image + description + metatag + weight) · **`finish`** (4 term, có mã màu swatch) · `article_category` (4 term) · `project_type` (4 term) · `door_type` · `material` · `unlock_method`

### 4.6. Roles & Permissions

| Role | Quyền |
|---|---|
| **Administrator** | Toàn quyền |
| **Content Manager** | CRUD tất cả content type, taxonomy. Không sửa cấu hình/module |
| **Product Manager** | CRUD product + product_category + brand + finish. Xem lead |
| **Editor** | CRUD article, project. Không publish (cần duyệt) |
| **Sales** | Chỉ xem + sửa `status`/`note` của lead |

---

## 5. API DESIGN

### 5.1. JSON:API (core, chỉ đọc)

```
GET /jsonapi/node/product?filter[...]&include=field_images,field_brand,field_finish&page[limit]=12
GET /jsonapi/node/article?sort=-field_publish_date
GET /jsonapi/taxonomy_term/product_category
```

Bật `jsonapi_extras` để rút gọn field name và ẩn field nội bộ.

### 5.2. Custom endpoints — module `keybolts_api`

| Method | Endpoint | Mục đích |
|---|---|---|
| GET | `/api/v1/homepage` | Gộp trong **1 request**: 8 danh mục · 4 nhóm sản phẩm nổi bật (`dong`/`cremone`/`hotel`/`phukien`) · 4 giải pháp · khối công nghệ · 3 loại dự án · 4 bài viết · 5 cơ sở · dải USP |
| GET | `/api/v1/products?brand=&category=&finish=&sort=&page=` | Danh sách + **facet count cho từng brand và từng category** (thiết kế hiển thị số bên cạnh mỗi lựa chọn). `sort` ∈ `featured\|az\|za\|cat`. Mặc định **12 item/trang** |
| GET | `/api/v1/products/{slug}` | Chi tiết + gallery + finish options + size variants + highlights + specs + policy cards + FAQs + related + breadcrumb + JSON-LD |
| GET | `/api/v1/products/suggest?q=` | Gợi ý cho search overlay: tên, model, danh mục, thumbnail. Không dấu-insensitive |
| GET | `/api/v1/articles?category=&page=` | Danh sách + bài nổi bật + filter chip |
| GET | `/api/v1/articles/{slug}` | Chi tiết + sections + mục lục + FAQ + sản phẩm nhắc đến + bài liên quan |
| GET | `/api/v1/projects?type=&page=` | Danh sách dự án + filter theo loại công trình |
| GET | `/api/v1/branches` | 5 cơ sở — dùng cho trang chủ, Liên hệ, Đại lý, About |
| GET | `/api/v1/policies` · `/api/v1/policies/{key}` | 5 chính sách + nav bên trái |
| GET | `/api/v1/menu/{name}` | Menu tree cho header/footer/mega menu |
| POST | `/api/v1/leads` | Nhận lead (rate limit 5 req/phút/IP + honeypot) |
| GET | `/api/v1/redirects` | Bảng 301 cho Nuxt middleware |

**Chuẩn response:**
```json
{ "data": [...], "meta": { "total": 128, "page": 1, "limit": 12 }, "facets": { "brand": {"keybolts": 18, "baltica": 8}, "category": {"dong": 6, "tay-gat": 3} } }
```

Mọi GET có cache tag Drupal → Redis, TTL 10 phút, tự purge khi admin sửa nội dung.

---

## 6. FEATURE LIST CHI TIẾT THEO TRANG

### 6.1. Trang chủ — `Keybolts Homepage.html`

| # | Section | Feature |
|---|---|---|
| 1 | Hero | **Hero tĩnh chia đôi** (không phải slider): nền charcoal-900 + vệt sáng radial gold, eyebrow "Keybolts Collection", H1 3 dòng có chữ "đẳng cấp" tô gradient gold→brass, sapo, 2 CTA (`Xem bộ sưu tập` → #categories, `Tư vấn miễn phí` → #consultation), ảnh sản phẩm cột phải. Ảnh hero **preload** (LCP) |
| 2 | Dải số liệu | 15+ năm kinh nghiệm · 05 showroom & kho · 10 năm bảo hành |
| 3 | Dải USP | 4 ô: Bảo hành 5–10 năm · Giao hàng toàn quốc · Tư vấn theo loại cửa · Đạt chuẩn CE-CFF |
| 4 | Danh mục | Grid 8 ô có số thứ tự `01`–`08`, ảnh + tên + mô tả + "Khám phá". Mobile: scroll ngang có nút prev/next |
| 5 | Sản phẩm nổi bật | **4 tab** (Khoá đồng nhập khẩu · CREMONE chốt khoá · Khoá khách sạn · Phụ kiện khác), mỗi tab 4–5 sản phẩm, card có danh mục, tên, model, hoàn thiện, nút "Liên hệ tư vấn". Tab mặc định cấu hình qua prop `defaultFeaturedTab` |
| 6 | Giải pháp | 4 ô Biệt thự / Căn hộ / Khách sạn / Văn phòng — ảnh, mô tả, chip tag → link `/san-pham` đã lọc |
| 7 | Công nghệ | Khối "Khóa vân tay thế hệ mới": ảnh lớn + 4 gạch đầu dòng tính năng + CTA "Xem dòng khóa thông minh" |
| 8 | Dự án | 3 card loại công trình (Biệt thự · Khách sạn · Căn hộ), hover zoom |
| 9 | Tin tức / Tư vấn | 4 bài mới nhất, card có danh mục + tiêu đề + tóm tắt |
| 10 | Tư vấn (`#consultation`) | Form: Họ tên · SĐT · Email · Nhu cầu/loại cửa → "Gửi yêu cầu tư vấn". Có nút gọi hotline kèm. **Trạng thái thành công tại chỗ** + nút "Gửi yêu cầu khác" |
| 11 | Hệ thống (`#dealer`) | 5 cơ sở: tên, địa chỉ, SĐT bấm gọi + CTA "Đăng ký làm đại lý" |

### 6.2. Trang tổng sản phẩm — `Keybolts Products.html`

| Feature | Chi tiết |
|---|---|
| Hero | Breadcrumb · eyebrow "Bộ sưu tập" · H1 · mô tả · 3 số liệu (tổng mã sản phẩm · 08 danh mục · 5–10 năm bảo hành) |
| Khối thương hiệu | 2 card lớn KEYBOLTS / BALTICA + card "Tất cả" — mỗi card có tên, số mã, tag, mô tả, CTA → bấm để lọc |
| Chip danh mục | Hàng chip ngang: Tất cả + 8 danh mục. Chip active: nền charcoal-900, chữ gold-200 |
| Sidebar lọc | **Thương hiệu** (có số đếm) · **Danh mục sản phẩm** (có số đếm) · **Hoàn thiện** (ô màu swatch). Kèm card "Tư vấn kỹ thuật" có hotline |
| Sắp xếp | Nổi bật · Tên A → Z · Tên Z → A · Theo danh mục. **Không có sắp xếp theo giá** (giá là "Liên hệ") |
| Product card | Ảnh, badge (Bán chạy/Mới/Cao cấp), badge thương hiệu, danh mục, tên, model, hoàn thiện, "Xem chi tiết" |
| Phân trang | **12 sản phẩm/trang**, pagination có số, hiển thị "Hiển thị x–y trên tổng z" |
| URL state | Filter đẩy vào query string → share/back/forward hoạt động, SSR đọc được |
| Empty state | "Không có sản phẩm phù hợp" + nút "Xóa bộ lọc" |
| Mobile | Sidebar lọc chuyển thành drawer full-screen, nút "Áp dụng (n)" cố định đáy |
| Skeleton | Loading skeleton khi đổi filter (không nhảy layout) |
| CTA cuối | Dải "Báo giá theo số lượng lớn" cho nhà thầu & đại lý — nút Gọi + Gửi yêu cầu |
| SEO | H1 = tên danh mục khi ở `/danh-muc/{slug}`, canonical bỏ query param filter |

### 6.3. Trang chi tiết sản phẩm — `Keybolts Product Detail.html`

| Feature | Chi tiết |
|---|---|
| Gallery | 4 ảnh, ảnh chính + thumbnail; badge "Chứng nhận CE-CFF"; ghi chú "Ảnh thật sản phẩm — chụp tại showroom, màu có thể chênh nhẹ theo ánh sáng". Zoom/lightbox, lazy ảnh 2+ |
| Khối mua | Eyebrow danh mục · H1 · "Mã sản phẩm: {model đang chọn}" · trạng thái "Còn hàng — giao 2–5 ngày" · chip tin cậy |
| Giá | Luôn "Liên hệ" + dòng giải thích "Giá thay đổi theo size, hoàn thiện và số lượng" |
| **Chọn hoàn thiện** | 3 swatch màu — đổi swatch **cập nhật lại mã model** hiển thị |
| **Chọn kích thước** | 4 lựa chọn (Đại sảnh XL / Đại L / Trung M / Thông phòng S), mỗi cái có ghi chú loại cửa và mã riêng — đổi size **cập nhật lại mã model** |
| CTA | `Gọi 1900 9018` (`tel:`) · `Nhận báo giá` (cuộn tới form) |
| Cam kết | 3–4 dòng `title` + `desc` ngay dưới CTA |
| Tabs | **Mô tả sản phẩm · Thông số kỹ thuật · Bảo hành & chính sách · Hỏi đáp** (mobile = accordion) |
| Tab mô tả | H3 + các đoạn văn + danh sách 5 điểm nổi bật |
| Tab thông số | Bảng cặp k/v (mã, chất liệu thân, hoàn thiện, loại khóa, loại cửa, độ dày cửa, chốt, bảo hành…) |
| Tab bảo hành | 3 thẻ: Bảo hành 5–10 năm · Giao hàng toàn quốc · Đổi trả 7 ngày |
| Tab hỏi đáp | Accordion Q&A |
| Sản phẩm liên quan | 3–4 sản phẩm cùng bộ sưu tập |
| Form báo giá | Section cuối: H2 động **"Báo giá cho model {model đang chọn}"**, trường Họ tên · SĐT · Số lượng & loại cửa. Kèm hotline + giờ trực. Success tại chỗ |
| Schema | `Product` + `Offer` (priceSpecification: liên hệ) + `BreadcrumbList` + `FAQPage` JSON-LD |

> **Lưu ý kỹ thuật quan trọng:** model hiển thị là **hàm của (hoàn thiện × kích thước)**. Backend phải trả bảng biến thể; frontend không được ghép chuỗi bằng tay.

### 6.4. Trang dự án — `Keybolts Projects.html`

Hero + breadcrumb · chip lọc (Tất cả · Biệt thự · Khách sạn · Căn hộ · Văn phòng) · grid card (loại · tiêu đề · mô tả · dòng sản phẩm sử dụng · ảnh) · pagination có "Hiển thị x–y trên z" · ghi chú "Hình ảnh công trình thực tế đang được cập nhật" cho card thiếu ảnh · dải CTA "Bạn đang triển khai công trình?".

### 6.5. Trang tin tức — `Keybolts News.html`

Hero + breadcrumb · **khối bài nổi bật** (ảnh lớn, nhãn "Buying Guide", tiêu đề, tóm tắt, tác giả + thời gian đọc) · chip lọc (Tất cả · Chọn khóa · So sánh · Hướng dẫn · FAQ) · grid card · pagination.

### 6.6. Trang bài viết — `Keybolts Article.html`

| Feature | Chi tiết |
|---|---|
| Header bài | Breadcrumb · nhãn danh mục · H1 · sapo · tác giả · "Cập nhật MM/YYYY" · "n phút đọc" |
| **Trả lời nhanh** | Khối nổi bật đầu bài — 2–3 câu trả lời thẳng câu hỏi tiêu đề. **Đây là thành phần GEO chính**, cần cả `speakable` schema |
| Mục lục | Sidebar dính (sticky) "Nội dung bài viết", sinh tự động từ các section, đánh số |
| Thân bài | Section có H2 + đoạn văn + bullet + callout ghi chú |
| Bảng so sánh | Bảng 4 cột theo loại cửa |
| FAQ | Accordion → schema `FAQPage` |
| Hộp tác giả | Tên + mô tả + nút "Hỏi kỹ thuật" |
| Sidebar phụ | "Sản phẩm nhắc đến" (tên + model) · card CTA "Gửi ảnh cửa, nhận gợi ý model trong 24h" + hotline |
| Bài liên quan | 3 bài + link "Xem tất cả bài viết" |
| Schema | `Article` + `FAQPage` + `BreadcrumbList` |

### 6.7. Trang đại lý — `Keybolts Dealers.html`

Hero "Trở thành đại lý Keybolts" · **4 quyền lợi** đánh số 01–04 (Giá đại lý theo cấp · Hỗ trợ hàng mẫu · Bảo vệ khu vực · Đào tạo kỹ thuật) · **4 điều kiện** dạng danh sách · **form đăng ký** (Họ tên · SĐT · Nội dung → lead nguồn "Đăng ký đại lý") · danh sách 5 cơ sở · **bản đồ tương tác**: chọn cơ sở bên trái → Google Maps embed đổi theo, mỗi cơ sở có nút "Chỉ đường" mở Google Maps directions.

### 6.8. Trang liên hệ — `Keybolts Contact.html`

Hero · 3 card kênh liên hệ (Hotline 1900 9018 · Zalo · Email) · form (Họ tên · SĐT · Nội dung) kèm thông tin pháp nhân + trụ sở · danh sách 5 cơ sở · bản đồ tương tác giống 6.7.

### 6.9. Trang chính sách — `Keybolts Policies.html`

**Một trang, 5 chính sách**: Bảo hành · Giao hàng · Đổi trả · Thanh toán · Bảo mật thông tin. Nav dọc bên trái dính (sticky, `top:120px`), bấm để đổi nội dung; mỗi chính sách có eyebrow "Chính sách 0n", H2, đoạn mở đầu, danh sách cặp k/v, ghi chú cuối. Card hỗ trợ có hotline + "Bộ phận bảo hành trực 8:00–18:00".

> URL: `/chinh-sach/{key}` SSR từng chính sách để index riêng, nav trái chuyển trang (không chỉ đổi state) — đảm bảo mỗi chính sách có URL và meta riêng.

### 6.10. Trang giới thiệu — `Keybolts About.html`

Hero 2 cột (H1 + mô tả + 2 CTA + ảnh) · dải 5 số liệu · khối "Câu chuyện" 2 đoạn + 4 chứng chỉ · **4 nhóm khách hàng** (Chủ nhà & biệt thự · Khách sạn & resort · Nhà thầu & thi công · Đại lý & cửa hàng) mỗi nhóm có CTA riêng · **quy trình 5 bước** 01–05 · **4 cam kết** có icon SVG · 5 cơ sở · dải CTA "Gửi bản vẽ hoặc ảnh cửa — nhận phương án trong 24h".

### 6.11. Lead / Form (dùng chung)

| Feature | Chi tiết |
|---|---|
| 4 biến thể | Tư vấn trang chủ (Tên · SĐT · Email · Nhu cầu) · Báo giá sản phẩm (Tên · SĐT · Số lượng & loại cửa) · Liên hệ (Tên · SĐT · Nội dung) · Đăng ký đại lý (Tên · SĐT · Nội dung) |
| Validate | Client + server; SĐT theo regex VN; chặn submit đúp |
| Chống spam | Honeypot + rate limit IP + kiểm tra thời gian điền tối thiểu 3s |
| Sau submit | **Trạng thái thành công tại chỗ**, không chuyển trang. Nội dung: "Đã nhận yêu cầu! Keybolts sẽ liên hệ trong 24 giờ làm việc…" + nút "Gửi yêu cầu khác". Bắn GA4 `generate_lead` |
| Thông báo | Email tới hộp thư sales (template HTML có link mở lead trong admin) |
| Lưu trữ | Entity `lead` trong Drupal, có bộ lọc theo trạng thái/nguồn/ngày |
| Export | Nút export CSV trong admin |

### 6.12. Toàn site

| Feature | Chi tiết |
|---|---|
| SEO | Meta riêng từng trang, OG image, canonical, `sitemap.xml` tự sinh, `robots.txt` |
| Schema | Organization, LocalBusiness (×5 cơ sở), WebSite (+SearchAction), Product, Offer, Article, FAQPage, BreadcrumbList |
| GEO / AI Search | Khối "Trả lời nhanh" ở bài viết, FAQ có schema, bảng thông số dạng bảng thật (không phải ảnh), nội dung render sẵn trong HTML nhờ SSR |
| 301 redirect | Bảng mapping URL cũ → mới, chạy ở Nuxt middleware + Nginx |
| Ảnh | WebP/AVIF, `srcset` 4 kích thước, lazy load, `width/height` chống CLS |
| Hiệu năng | Code splitting theo route, preload font, critical CSS, brotli |
| Accessibility | Contrast ≥ 4.5:1, focus ring, alt text, ARIA cho mega menu / panel mobile / overlay search / tabs / accordion |
| Analytics | GA4 + GSC + (tuỳ chọn) GTM; track: `view_item`, `search`, `filter_apply`, `select_variant`, click hotline/Zalo, `generate_lead` |
| Error handling | 404 có gợi ý, 500 có nút thử lại, API lỗi → toast + fallback |

---

## 7. DESIGN SYSTEM — TRÍCH TỪ `design/`

> Toàn bộ giá trị dưới đây **lấy nguyên từ CSS custom properties trong file thiết kế**, không phải đề xuất mới. Frontend phải import đúng bộ token này.

### 7.1. Màu

**Màu gốc:**

| Token | Giá trị | Dùng cho |
|---|---|---|
| `--charcoal-900` | `#282d30` | Màu thương hiệu chính: header top bar, hero, footer, nút/chip active |
| `--gold-200` | `#f7e499` | Màu nhấn: CTA chính, eyebrow, chữ trên nền tối |
| `--white` | `#ffffff` | Nền chính |
| `--surface-50` | `#f8f8f8` | Nền phụ |
| `--border-100` | `#eeeeee` | Đường kẻ, viền |
| `--ink-900` | `#000000` | Chữ chính |

**Màu dẫn xuất** (thiết kế dùng `oklch(from …)` — xem cảnh báo 7.6):

| Token | Giá trị |
|---|---|
| `--neutral-100…700` | `oklch(from var(--charcoal-900) L C h)` với L = .97 / .92 / .82 / .65 / .48 / .34 / .26 |
| `--gold-100` / `--gold-300` | `oklch(from var(--gold-200) .96 .03 h)` / `(.82 .09 h)` |
| `--brass-500` | `oklch(0.62 0.11 75)` — nhấn mạnh, viền active |
| `--brass-700` | `oklch(0.42 0.09 55)` — link, badge Baltica |
| `--success-500` | `oklch(0.6 0.1 145)` |
| `--warning-500` | `oklch(0.75 0.13 75)` |
| `--danger-500` | `oklch(0.55 0.16 25)` |

**Token ngữ nghĩa** (component chỉ được dùng nhóm này):

`--color-primary` = charcoal-900 · `--color-on-primary` = white · `--color-background` = white · `--color-surface` = surface-50 · `--color-border` = border-100 · `--color-text` = ink-900 · `--color-text-muted` = neutral-500 · `--color-text-on-dark-muted` = `rgba(255,255,255,.68)` · `--color-accent` = gold-200 · `--color-accent-strong` = brass-500 · `--color-accent-ink` = brass-700 · `--color-link` = brass-700 · `--color-link-hover` = charcoal-900 · `--color-success` / `--color-warning` / `--color-danger`

**Swatch hoàn thiện sản phẩm** (dữ liệu, không phải token UI): PVD `#c69148` · DSF `#6b6f5c` · INOX `#b9bec2` · Đồng `#a97434`.

### 7.2. Typography

**Font: `Roboto`** (`--font-sans`, fallback `-apple-system, "Segoe UI", sans-serif`); `Nunito Sans` dùng ở một vài khối phụ. Weight dùng: **300 / 400 / 700**.

| Token | Giá trị | Ghi chú |
|---|---|---|
| `--text-display-xl` | 56px | Hero H1 (qua `clamp`, phóng tới ×1.3 ở màn lớn) |
| `--text-display-lg` | 40px | Cận dưới của `clamp` cho H1 |
| `--text-display` | 24px / w700 / lh 1.2 | H2 section |
| `--text-heading` | 16px / w300 / lh 1.4 | Sapo, mô tả section |
| `--text-body` | 14px / w400 / lh 1.2 | Chữ nội dung, nút |
| `--text-caption` | 12px | Chú thích |
| `--text-eyebrow` | 12px / w700 / letter-spacing .24em / UPPERCASE | Nhãn trên tiêu đề |

> H1 hero dùng `letter-spacing: -.035em`, `line-height: 1.02`, `text-wrap: balance` và **gradient text** (gold-100 → gold-200 → brass-500) cho từ nhấn.

**Việc phải làm:** self-host WOFF2 (Roboto + Nunito Sans, subset Vietnamese), `font-display: swap`, chỉ nạp weight 300/400/700. Thiết kế đang link Google Fonts — **không được giữ nguyên khi lên production**.

### 7.3. Spacing & Layout

- **Thang spacing gốc 3px:** `--space-1` 3 · `2` 6 · `3` 9 · `4` 12 · `5` 15 · `6` 24 · `7` 30 · `8` 33 · `9` 45
- **Container:** max **1360px** (không phải 1280), padding ngang `clamp(20px, 4vw, 48px)`
- **Breakpoint:** `sm 576` · `md 768` · `lg 992` · `xl 1200` · `xxl 1300`
- **Bo góc:** `--radius-none` 0 · **`--radius-sm` 50px (viên thuốc — dùng cho mọi nút và chip)** · `--radius-full` 9999px
- **Đổ bóng:** `--shadow-card` **none** · `--shadow-elevated` **none** · `--shadow-floating` `0 8px 24px rgba(40,45,48,.16)` (chỉ overlay/dropdown)
- **Chuyển động:** `--duration-fast` 150ms · `--duration-base` / `--duration-slow` 200ms · `--easing-standard` `ease-in-out`

> Ngôn ngữ hình ảnh: **phẳng, không đổ bóng, viền mảnh, nút bo tròn viên thuốc** — khác hẳn mô tả "bo góc 0, phong cách architectural" ở bản 1.0. Đây là điểm sai lệch lớn nhất giữa tài liệu cũ và thiết kế thật.

### 7.4. Breakpoint hành vi (lấy từ logic thiết kế, không chỉ từ CSS)

| Ngưỡng | Hành vi |
|---|---|
| `< 900px` | Bản đồ + danh sách cơ sở xếp dọc; hero About 1 cột |
| `isMobile` | Header đổi sang layout ☰ / logo / 🔍 / 📞; sticky CTA bar bật; sidebar lọc → drawer |
| `isWide` | Hiện tagline cạnh logo |
| `utilWide` | Hiện các mục phụ trên top bar |

Frontend phải theo dõi viewport bằng một composable dùng chung (`useViewport`), **không rải media query rời rạc**.

### 7.5. Bộ file thiết kế bàn giao

10 file HTML prototype chạy được trong `design/` — đây là **nguồn sự thật duy nhất**, thay cho Figma:

`Keybolts Homepage.html` · `Keybolts Products.html` · `Keybolts Product Detail.html` · `Keybolts Projects.html` · `Keybolts News.html` · `Keybolts Article.html` · `Keybolts Dealers.html` · `Keybolts Contact.html` · `Keybolts About.html` · `Keybolts Policies.html`

Mỗi file là component tự chứa: token CSS inline, dữ liệu mẫu, và logic tương tác (tab, filter, phân trang, overlay, map picker, trạng thái form).

**3 màn còn thiếu, đội dự án dựng theo token:** Kết quả tìm kiếm · 404 · 500. Cần designer duyệt trong **ngày 2**.

### 7.6. Ba cảnh báo kỹ thuật khi chuyển thiết kế sang code

1. **`oklch(from …)` (relative color syntax) chưa được hỗ trợ rộng** — Safari cũ và một số WebView Android sẽ render sai màu neutral/gold. **Bắt buộc:** tính sẵn giá trị tĩnh cho toàn bộ thang `neutral-*` và `gold-*` khi build, `oklch()` chỉ giữ làm progressive enhancement.
2. **Style trong prototype là inline** — không được copy nguyên. Ngày 1–2 phải trích thành file token (`assets/css/tokens.css`) + Tailwind preset map 1-1, rồi component chỉ tham chiếu token.
3. **Ảnh trong prototype hotlink về `keybolts.com.vn/sites/default/files/…`** — xác nhận ảnh gốc còn trên site cũ, nhưng **tuyệt đối không hotlink ở production**: migration phải tải về, chuyển WebP/AVIF, sinh `srcset` và đặt tên có nghĩa.

### 7.7. Component chuẩn — kiểm kê từ thiết kế

| Nhóm | Component |
|---|---|
| Layout | TopBar · MainBar · MegaMenu · MobileNavPanel · SearchOverlay · StickyMobileCta · Footer · Breadcrumb |
| Card | ProductCard (có badge + badge thương hiệu) · CategoryCard (có số thứ tự) · SolutionCard (có chip tag) · ProjectCard · ArticleCard · BranchCard · SegmentCard · BenefitCard (đánh số) · PolicyCard |
| Danh sách sản phẩm | BrandChooser · FilterSidebar · FilterChipRow · FinishSwatchGroup · SortSelect · Pagination · EmptyState · Skeleton |
| Chi tiết sản phẩm | Gallery · FinishSelector · SizeSelector · AssuranceList · TabGroup (→ Accordion ở mobile) · SpecTable · FaqAccordion |
| Bài viết | QuickAnswerBox · TableOfContents (sticky) · ArticleSection · CompareTable · AuthorBox · MentionedProducts |
| Form | LeadForm (4 biến thể) · Input · Textarea · SuccessState |
| Khác | StatStrip · UspStrip · CtaBand · MapPicker · StickyPolicyNav · Badge · Chip · Toast |

**Definition of Done cho 1 component** (giữ nguyên từ bản 1.0, bổ sung 2 mục):
- Khớp prototype ở 375px / 768px / 1440px — **đối chiếu trực tiếp bằng cách mở song song file trong `design/`**
- Không hard-code text/màu/URL — dùng props + token ngữ nghĩa
- **Không dùng `oklch(from …)` trực tiếp** — chỉ token đã tính sẵn
- Có `loading` và `empty` state nếu render dữ liệu
- Có type TypeScript đầy đủ
- Không có lỗi ESLint / console warning
- Kiểm tra bằng keyboard (tab, enter, esc)

---

## 8. QUY TRÌNH THỰC HIỆN

### 8.1. Ba luồng trong 2 tuần

```
NGÀY  1   2   3   4   5   6   7   8   9   10
      │───│                                  ① TRÍCH DESIGN SYSTEM + 3 màn thiếu
      │───────────────────────────│          ② CẮT CODE + BUILD FRONTEND
      │───────────────────────────────│      ③ BACKEND (content model + API + migration)
                          │───────────────│  ④ STAGING → PROD
```

Vì thiết kế đã xong, **cả ba luồng khởi động ngay ngày 1**. Backend không chờ design; frontend không chờ dữ liệu thật (dùng dữ liệu mẫu có sẵn trong prototype cho tới khi API xong).

### 8.2. ① Trích design system (Ngày 1–2)

| Bước | Đầu ra | Duyệt |
|---|---|---|
| Audit site cũ | Bảng inventory: số sản phẩm thật, danh mục, bài viết, ảnh, URL có traffic | Nội bộ |
| Trích token | `tokens.css` + Tailwind preset, **có giá trị tĩnh thay `oklch(from …)`** | Nội bộ |
| Kiểm kê component | Danh sách ở 7.7, gán độ ưu tiên và người làm | Nội bộ |
| Chốt các điểm mâu thuẫn | Menu chính · số năm kinh nghiệm · giờ làm việc · "Khóa chống trộm" là danh mục hay nhãn | **Khách duyệt cuối ngày 1** |
| Dựng 3 màn thiếu | Kết quả tìm kiếm · 404 · 500 | **Khách duyệt cuối ngày 2** |

**Quy tắc:** thiết kế đã chốt. Yêu cầu đổi giao diện sau ngày 2 → ghi vào Change Log, đánh giá tác động thời gian trước khi làm.

### 8.3. ② Cắt code (Ngày 1–7)

Quy trình mỗi component:

```
Mở prototype trong design/ → Vue component → responsive theo 5 breakpoint
     → a11y check → gắn props/types → review → merge
```

**Thứ tự cắt (ưu tiên theo mức chặn):**
1. Ngày 1–2: token, layout, TopBar/MainBar, MegaMenu, MobileNavPanel, SearchOverlay, StickyMobileCta, Footer, Breadcrumb, Button, Input, ProductCard
2. Ngày 2–3: Trang chủ đầy đủ 11 section
3. Ngày 3–4: Trang tổng sản phẩm + sidebar lọc + facet + phân trang
4. Ngày 4–5: Chi tiết sản phẩm + gallery + finish/size selector + tabs + form báo giá
5. Ngày 5–6: Tin tức, chi tiết bài viết (mục lục + trả lời nhanh + FAQ), dự án
6. Ngày 6–7: Đại lý, liên hệ (MapPicker dùng chung), giới thiệu, chính sách, tìm kiếm, 404/500

### 8.4. ③ Backend — tích hợp & migration (Ngày 1–8)

| Ngày | Việc |
|---|---|
| 1 | DDEV + Drupal 11 + Redis; Pathauto, Metatag, Redirect; **taxonomy `brand`, `product_category`, `finish` theo mục 2.2** |
| 2 | Content type `product` + toàn bộ field mục 4.1 (gồm `field_size_variants`, `field_finish_options`, `field_faqs`); **chốt contract API và bàn giao cho FE** |
| 3 | `article` (sections, quick answer, compare table), `project`, `branch`, `policy`, `page`, `author`; roles/permissions; entity `lead` |
| 4 | Module `keybolts_api`: `/homepage`, `/products` (**có facet count**), `/products/{slug}` (**có bảng biến thể model**) |
| 5 | `/products/suggest`, `/articles`, `/projects`, `/branches`, `/policies`, `/menu`, `POST /leads` + email; cache layer |
| 6 | Script migration: export site cũ → chuẩn hoá → `migrate` module. **Tải toàn bộ ảnh về, chuyển WebP** |
| 7 | Chạy migration lần 1 lên staging; đối chiếu; sinh bảng 301 redirect |
| 8 | Migration lần cuối; tối ưu query, index (đặc biệt query facet count); bật cache production |

**Frontend track:**

| Ngày | Việc |
|---|---|
| 1 | Khởi tạo Nuxt 4 + TypeScript; import token; service layer + dữ liệu mẫu lấy từ prototype |
| 2–7 | Dựng trang theo thứ tự 8.3, đấu API thật ngay khi backend xong từng endpoint |
| 8 | SEO composables, JSON-LD (Product/Article/FAQ/LocalBusiness), sitemap, redirect middleware, error pages |

**Quy tắc code:**
- Không gọi API trực tiếp trong component — mọi call qua `services/`
- Không hard-code URL API — dùng biến môi trường
- Không hard-code màu, cỡ chữ, khoảng cách — chỉ dùng token ngữ nghĩa
- Component > 300 dòng phải tách
- Commit theo Conventional Commits, PR nhỏ, review trước khi merge `develop`

### 8.5. ④ Lên dev → staging → production (Ngày 4–10)

**Ba môi trường:**

| Môi trường | Domain | Mục đích | Ai truy cập |
|---|---|---|---|
| Local | `keybolts.ddev.site` | Phát triển | Dev |
| Dev / Staging | `staging.keybolts.com.vn` + `cms-staging.keybolts.com.vn` | Tích hợp, QA, khách duyệt | Đội dự án + khách (có Basic Auth, `noindex`) |
| Production | `keybolts.com.vn` + `cms.keybolts.com.vn` | Chính thức | Công khai |

**Git flow:**
```
feature/* → develop → (auto deploy staging) → main → (deploy prod, có approve)
```

**Pipeline GitHub Actions:**

*Frontend:* `install → lint → typecheck → build → deploy → restart PM2 → smoke test`
*Backend:* `composer install --no-dev → rsync → drush updb → drush cim → drush cr`

**Lịch lên môi trường:**

| Ngày | Mốc |
|---|---|
| 4 | Dựng server staging (Nginx, PHP-FPM, MariaDB, Redis, Node, SSL), deploy lần đầu |
| 7 | Staging có dữ liệu migration lần 1 → khách xem được bản chạy thật |
| 8 | Dựng server production, deploy code (chưa trỏ DNS), test bằng file hosts |
| 9 | UAT trên staging; fix bug; migration lần cuối lên prod |
| 10 | Go-live: trỏ DNS → verify → bàn giao |

**Cấu hình server production:**
- Nginx: reverse proxy, gzip + brotli, security headers (HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, CSP cơ bản)
- Nginx cache tĩnh: ảnh/JS/CSS có hash trong tên file → `Cache-Control: public, max-age=31536000, immutable`; HTML trang SSR → `s-maxage` ngắn + revalidate
- SSL: Let's Encrypt + auto-renew
- PM2: chạy Nuxt SSR, cluster mode, auto restart
- Cron: `drush cron` mỗi 15 phút; backup DB hằng ngày (giữ 14 ngày) + backup file ảnh hằng tuần
- Chặn truy cập `/admin`, `/user/login` ngoài IP văn phòng (hoặc bật rate limit chặt)
- `cms.keybolts.com.vn` gắn `noindex`

---

## 9. TIMELINE 2 TUẦN — CHI TIẾT THEO NGÀY

### TUẦN 1

| Ngày | Design / Token | Backend | Frontend | Mốc cần khách |
|---|---|---|---|---|
| **1**<br>T2 | Audit site cũ · Trích token + Tailwind preset · Kiểm kê component | DDEV + Drupal 11 + Redis · 3 taxonomy chính | Init Nuxt + TS · Import token · Service layer + mock từ prototype | Bàn giao logo vector, truy cập site cũ + thư mục ảnh.<br>**Chốt 4 điểm mâu thuẫn (0.2)** |
| **2**<br>T3 | Dựng 3 màn thiếu (search / 404 / 500) | Content type `product` + toàn bộ field · **Chốt contract API** | Layout · Header + mega menu · SearchOverlay · Footer · Card | **Duyệt 3 màn bổ sung** |
| **3**<br>T4 | Design QA đợt 1 | Các content type còn lại · Roles · Entity `lead` | Trang chủ đầy đủ 11 section | |
| **4**<br>T5 | | `keybolts_api`: homepage, products + facet, product detail | Trang tổng sản phẩm + sidebar lọc + phân trang | Xác nhận hạ tầng server |
| **5**<br>T6 | | Endpoint suggest, articles, projects, branches, policies, leads + email | Chi tiết sản phẩm: gallery, finish/size selector, tabs, form báo giá | **Demo bản chạy — duyệt trang chủ + sản phẩm** |
| — | **Cuối tuần 1: staging chạy được trang chủ + danh sách + chi tiết sản phẩm với dữ liệu mẫu** | | | |

### TUẦN 2

| Ngày | Backend | Frontend | Deploy | Mốc cần khách |
|---|---|---|---|---|
| **6**<br>T2 | Script migration: export site cũ → chuẩn hoá → tải ảnh về, chuyển WebP | Tin tức + chi tiết bài viết (mục lục, trả lời nhanh, FAQ) · Dự án | | |
| **7**<br>T3 | Chạy migration lần 1 lên staging · Sinh bảng 301 | Đại lý, liên hệ (MapPicker), giới thiệu, chính sách, tìm kiếm, 404/500 | Staging có dữ liệu thật | **Khách review nội dung đã migrate** |
| **8**<br>T4 | Migration lần cuối · Tối ưu query facet, index · Bật cache | SEO: meta, JSON-LD, sitemap, redirect middleware · Tối ưu ảnh & tốc độ | Dựng server production, deploy (chưa trỏ DNS) | |
| **9**<br>T5 | Fix bug backend · Cấu hình cron, backup | Fix bug frontend · Đối chiếu prototype ở 3 breakpoint · Lighthouse | UAT trên staging | **UAT — chốt danh sách bug trước 17h** |
| **10**<br>T6 | Bàn giao tài khoản admin · Hướng dẫn sử dụng | Smoke test sau go-live | **GO-LIVE**: trỏ DNS · SSL · GA4/GSC · submit sitemap | Nghiệm thu & bàn giao |

### Sau go-live (không tính trong 2 tuần)

- **Ngày 11–17:** bảo hành lỗi, theo dõi 404 trong GSC, xử lý redirect sót, theo dõi lead về đúng email.
- **Tuần 3+:** Phase 2 theo mục 0.3 (cột "KHÔNG LÀM") — ưu tiên: trang chi tiết dự án, trang chi tiết giải pháp, danh bạ đại lý bán lẻ, hiển thị giá.

---

## 10. CHECKLIST GO-LIVE (NGÀY 10)

### Trước khi trỏ DNS
- [ ] Backup toàn bộ website cũ (code + database + ảnh), lưu 2 nơi
- [ ] Production build chạy ổn định, test qua file `hosts`
- [ ] **Không còn ảnh nào hotlink về `keybolts.com.vn/sites/default/files/…`** — kiểm tra bằng grep trên build output
- [ ] **Không còn `oklch(from …)` trong CSS production**; kiểm tra màu trên Safari iOS thật
- [ ] **Font self-host, không còn request tới `fonts.googleapis.com`**
- [ ] Bảng 301 redirect đã import và test **20 URL cũ có traffic cao nhất**
- [ ] `robots.txt` cho phép crawl; gỡ `noindex` khỏi frontend prod
- [ ] `cms.keybolts.com.vn` đang `noindex` + chặn IP
- [ ] SSL hợp lệ cho cả 2 domain, auto-renew đã bật
- [ ] Form lead gửi thử từ **cả 4 biến thể** → email về đúng hộp thư, lead lưu vào Drupal đúng `lead_source`
- [ ] Hotline `tel:1900 9018` và link Zalo bấm được trên điện thoại thật
- [ ] **Chọn hoàn thiện × kích thước trên trang chi tiết trả về đúng mã model**
- [ ] GA4 + GSC đã gắn, GTM (nếu dùng) đã publish
- [ ] Cron + backup tự động đã chạy thành công ít nhất 1 lần

### Sau khi trỏ DNS
- [ ] Kiểm tra 10 URL đại diện trên 4G thật (Android + iPhone)
- [ ] Submit `sitemap.xml` trong Search Console
- [ ] Yêu cầu index thủ công cho trang chủ + 5 danh mục chính
- [ ] Lighthouse mobile: Performance ≥ 80, SEO ≥ 95, Accessibility ≥ 90
- [ ] Test 301: gõ 20 URL cũ → về đúng URL mới, không có chuỗi redirect
- [ ] Kiểm tra schema bằng Rich Results Test (Product, Article, FAQPage, Organization, LocalBusiness, Breadcrumb)
- [ ] Theo dõi log lỗi 5xx trong 24h đầu

### Bàn giao
- [ ] Tài khoản admin + phân quyền cho từng người
- [ ] Tài liệu hướng dẫn: thêm sản phẩm (gồm biến thể hoàn thiện/kích thước), đăng tin, sửa chính sách, xem lead (kèm ảnh chụp màn hình)
- [ ] Thông tin server, domain, SSL, backup
- [ ] Repository + quyền truy cập
- [ ] Biên bản nghiệm thu

---

## 11. TIÊU CHÍ NGHIỆM THU

| Nhóm | Tiêu chí | Cách đo |
|---|---|---|
| Giao diện | Khớp prototype trong `design/` ở 375 / 768 / 1440px | Mở song song file thiết kế và bản build, đối chiếu từng section |
| Chức năng | 100% feature mục 6 hoạt động | Test case checklist |
| CMS | Admin tự thêm/sửa sản phẩm (kèm biến thể), danh mục, bài viết, dự án, chính sách, cơ sở mà không cần dev | Khách thao tác thử tại buổi nghiệm thu |
| Dữ liệu | Không thiếu sản phẩm/ảnh so với inventory ngày 1 | Đối chiếu số lượng |
| SEO | Có SSR, meta riêng từng trang, sitemap, canonical, breadcrumb, schema | View source + Rich Results Test |
| GEO | Bài viết có khối "Trả lời nhanh" + FAQ schema; thông số sản phẩm là bảng HTML thật | Kiểm tra HTML nguồn |
| Redirect | 100% URL cũ trong danh sách top-100 traffic có 301 | Script kiểm tra hàng loạt |
| Tốc độ | Lighthouse mobile Performance ≥ 80; LCP < 2.5s, CLS < 0.1 | PageSpeed Insights |
| Bảo mật | HTTPS, security headers, admin bị giới hạn, không lộ version | securityheaders.com + kiểm tra thủ công |
| Lead | Gửi form → lưu Drupal + email về sales trong < 1 phút | Test 4 biến thể form từ 4 trang khác nhau |

---

## 12. RỦI RO & PHƯƠNG ÁN

| Rủi ro | Khả năng | Ảnh hưởng | Phương án |
|---|---|---|---|
| **Số SKU thật lớn hơn nhiều so với mẫu** (thiết kế có 26, About ghi 200+) | Cao | Trễ migration, facet chậm | Lấy con số thật ngay ngày 1; migrate theo lô ưu tiên danh mục có traffic; đánh index cho query facet |
| **Biến thể hoàn thiện × kích thước không có dữ liệu trên site cũ** | Cao | Trang chi tiết mất tính năng chính | Ngày 1 kiểm tra dữ liệu cũ. Nếu không có → nhập tay cho 20 SP chủ lực, còn lại hiển thị 1 biến thể mặc định |
| **`oklch(from …)` render sai trên trình duyệt cũ** | Trung bình | Lệch màu thương hiệu | Tính giá trị tĩnh khi build (7.6 mục 1); test Safari iOS ngày 2 |
| **Ảnh trên site cũ chất lượng thấp / thiếu** | Cao | Giảm hiệu quả thiết kế | Dùng ảnh hiện có + xử lý nền; đề xuất chụp lại ở Phase 2 |
| Khách duyệt chậm 4 điểm mâu thuẫn ở 0.2 | Trung bình | Trễ ngày 2–3 | Chốt SLA 24h; quá hạn dùng phương án đội dự án đề xuất |
| Dữ liệu site cũ bẩn (thông số nằm trong 1 ô text) | Cao | Trễ 1–2 ngày | Audit ngay ngày 1; parse bán tự động sang `field_specifications`; sản phẩm lỗi đưa vào danh sách nhập tay sau go-live |
| Không có quyền truy cập DNS đúng ngày 10 | Trung bình | Không go-live được | Xác nhận quyền từ ngày 1; hạ TTL xuống 300s từ ngày 8 |
| Phát sinh yêu cầu ngoài phạm vi (trang chi tiết dự án / giải pháp) | Cao | Trễ | Ghi vào Change Log, báo tác động thời gian trước khi làm |
| Server production chưa sẵn sàng | Trung bình | Trễ 1 ngày | Chuẩn bị VPS dự phòng từ ngày 4 |

---

## 13. PHÂN CÔNG & TRÁCH NHIỆM

| Việc | Đội dự án | Keybolts |
|---|---|---|
| Thiết kế | **Đã hoàn thành** (10 prototype) + dựng 3 màn còn thiếu | Chốt 4 điểm mâu thuẫn trong 24h |
| Nội dung sản phẩm | Migrate từ site cũ | Cung cấp danh sách SKU thật + dữ liệu biến thể nếu site cũ không có |
| Ảnh sản phẩm | Tải về, tối ưu, đặt tên, gắn alt | Cung cấp ảnh gốc chất lượng cao (nếu có) |
| Bài viết mới | Dựng cấu trúc (sections, trả lời nhanh, FAQ) | Viết nội dung (Phase 2) |
| Hạ tầng | Cấu hình, deploy | Cung cấp/duyệt chi phí server, domain |
| DNS | Hướng dẫn bản ghi | Thực hiện trỏ hoặc cấp quyền |
| Nghiệm thu | Chuẩn bị checklist | Test và ký biên bản |
| Vận hành sau bàn giao | Bảo hành 30 ngày | Cập nhật nội dung hằng ngày |

---

## 14. QUY ƯỚC LÀM VIỆC

- **Daily standup** 15 phút, 9h sáng.
- **Demo cho khách:** cuối ngày 2, 5, 7 và 9 (4 lần).
- **Kênh liên lạc:** 1 group duy nhất. Yêu cầu qua kênh khác không được ghi nhận.
- **Change request:** mọi thay đổi ngoài tài liệu này phải ghi vào Change Log kèm đánh giá tác động thời gian, có xác nhận mới làm.
- **Môi trường khách xem:** luôn là `staging.keybolts.com.vn`, không xem trên máy dev.
- **Bug:** khách báo qua group kèm ảnh chụp màn hình + tên thiết bị + trình duyệt.
- **Nguồn sự thật về giao diện:** file trong `design/`. Khi tài liệu này và prototype khác nhau, **prototype thắng** — và tài liệu phải được sửa lại.

---

## 15. NHẬT KÝ THAY ĐỔI — v1.0 → v2.0

| Mục | Bản 1.0 | Bản 2.0 (theo `design/`) |
|---|---|---|
| Bàn giao thiết kế | Figma 3 page, 24 màn | 10 file HTML prototype chạy được |
| Timeline design | Ngày 1–5, chặn frontend | Ngày 1–2, không chặn ai |
| Số trang | 21 route | 15 route (bỏ chi tiết dự án, chi tiết giải pháp, tìm đại lý, đăng ký đại lý riêng, cảm ơn; thêm route thương hiệu) |
| Danh mục sản phẩm | 3 nhóm, 2 cấp, 14 term | 8 term phẳng |
| Thương hiệu | "Keybolts + brand khác" | 2 thương hiệu cụ thể: KEYBOLTS · BALTICA |
| Hoàn thiện (finish) | Không có | **Vocabulary mới + bộ chọn swatch + bộ lọc** |
| Biến thể sản phẩm | Không có | **Hoàn thiện × kích thước → sinh mã model động** |
| Hero trang chủ | Slider 3–5 banner | Hero tĩnh chia đôi → **bỏ content type `banner`** |
| Sản phẩm nổi bật | Carousel 8 sp | 4 tab theo nhóm |
| Bộ lọc | 8 nhóm gồm khoảng giá | 3 nhóm: thương hiệu · danh mục · hoàn thiện (không lọc giá) |
| Phân trang | 24 sp/trang | **12 sp/trang** |
| Tab chi tiết SP | 5 tab | 4 tab: Mô tả · Thông số · Bảo hành & chính sách · Hỏi đáp |
| Tìm kiếm | Ô input trong header | **Overlay toàn màn hình** + quick tags |
| Menu mobile | Drawer trái, accordion 2 cấp | Panel trượt xuống, danh sách phẳng |
| Sau submit form | Redirect `/cam-on` | **Trạng thái thành công tại chỗ** |
| Chính sách | Nhiều node rời | 1 trang, 5 mục, nav dọc dính |
| Bài viết | title + body + thumbnail | **sections có anchor · trả lời nhanh · bảng so sánh · FAQ · sản phẩm nhắc đến · thời gian đọc · ngày cập nhật** |
| Đại lý | Danh sách đại lý + geo search | **Trang tuyển đại lý** + 5 cơ sở công ty + map picker |
| Màu | ink/sand/brass, hệ tự đặt | charcoal-900 `#282d30` · gold-200 `#f7e499` · brass oklch · hệ token ngữ nghĩa |
| Font | Be Vietnam Pro | **Roboto** (+ Nunito Sans phụ) |
| Bo góc | 0 — "architectural" | **50px viên thuốc** cho nút và chip |
| Container | 1280px | **1360px** |
| Breakpoint | 640/768/1024/1280 | **576/768/992/1200/1300** |
| Spacing | thang 4px | **thang gốc 3px** |
| Đổ bóng | có shadow card | **none** (trừ overlay) |

---

*© 2026 Lưu Xuân Thế · 0932.355.207 · thexuanluu@gmail.com — Tài liệu thuộc bản quyền tác giả, dùng cho mục đích triển khai dự án Keybolts.*
