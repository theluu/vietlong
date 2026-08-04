# Yêu cầu: Quản trị toàn bộ nội dung website

Viết 2026-08-04. Đây là **bản yêu cầu**, chưa phải kế hoạch thực thi. Chốt xong
tài liệu này mới viết plan và code.

**Mục tiêu:** một người không biết code đăng nhập vào `/admin`, sửa được **mọi
thứ** hiển thị trên website — nội dung từng trang, menu, mạng xã hội, thông tin
liên hệ, key reCAPTCHA — mà không cần gọi lập trình viên và không cần deploy lại.

---

## 1. Hiện trạng

### Đã quản trị được (nằm trong Drupal)

| Nội dung | Nơi sửa |
|---|---|
| Sản phẩm (26) | `/admin/content` → Sản phẩm |
| Bài viết (13) | `/admin/content` → Bài viết |
| Dự án (12) | `/admin/content` → Dự án |
| Cơ sở/Showroom (5) | `/admin/content` → Cơ sở |
| Trang Giới thiệu / Đại lý / Liên hệ / Chính sách / Tin tức / Dự án | Node đơn, mỗi trang 1 bản ghi |
| Yêu cầu liên hệ (lead) | `/admin/content` → Yêu cầu liên hệ |

### Chưa quản trị được — đang hardcode trong code

| Thứ | Nằm ở đâu | Hậu quả |
|---|---|---|
| **Menu chính** (6 mục) | `composables/useSiteChrome.ts` → `NAV_ITEMS` | Đổi tên/thêm mục phải sửa code + deploy |
| **Hotline** `1900 9018` | `useSiteChrome.ts` → `HOTLINE` | Đổi số phải sửa code; số này xuất hiện ở 12 chỗ |
| **Toàn bộ nội dung trang chủ** | `utils/homeContent.ts` — 7 khối: `USPS`, `HERO_STATS`, `SOLUTIONS`, `TECH_FEATURES`, `PROJECT_TYPES`, `ARTICLES`, `FEATURED_TABS` | **Trang chủ hoàn toàn không sửa được từ admin** |
| **Mạng xã hội** (Facebook, YouTube, Zalo) | `SiteFooter.vue`, `StickyMobileCta.vue` | Đổi link phải sửa code |
| **Thông tin công ty** (tên, email, địa chỉ, copyright) | `SiteFooter.vue` | |
| **Cột link ở footer** | `SiteFooter.vue` | |
| **Key reCAPTCHA** | `settings.php` + `frontend/.env` | Phải SSH vào server |

**Kết luận:** trang chủ — trang quan trọng nhất — hiện không sửa được chút nào từ
giao diện quản trị. Đây là khoảng trống lớn nhất.

### Role hiện có

`content_editor` đã tồn tại (mặc định của Drupal) nhưng **chỉ có 14 quyền** và
**không có quyền nào trên sản phẩm/bài viết/dự án**. Thực tế không dùng được.

---

## 2. Yêu cầu về phân quyền

### Role `Biên tập viên nội dung`

**Được phép:**
- Tạo / sửa / xoá: Sản phẩm, Bài viết, Dự án, Cơ sở
- Sửa (không tạo/xoá): 6 trang đơn — Giới thiệu, Đại lý, Liên hệ, Chính sách, Tin tức, Dự án
- Sửa: Cấu hình chung (thông tin công ty, mạng xã hội, hotline, menu, nội dung trang chủ)
- Xem và xoá: Yêu cầu liên hệ (lead)
- Upload ảnh, dùng trình soạn thảo
- Xoá cache (để nội dung mới lên ngay)

**Không được phép:**
- Cài/gỡ module, đổi cấu hình hệ thống, sửa content type hay field
- Quản lý người dùng và phân quyền
- Truy cập báo cáo lỗi hệ thống, cập nhật Drupal
- **Sửa key reCAPTCHA** — xem mục 4, chỉ quản trị viên

### Role `Quản trị viên`
Giữ nguyên `administrator` sẵn có.

---

## 3. Yêu cầu theo từng trang

Ký hiệu: **[Đ]** = đã làm được · **[M]** = phải làm mới

### 3.1 Chung toàn site (mọi trang)

| Nhóm | Trường cần sửa | TT |
|---|---|---|
| Thanh trên cùng | Dòng chữ trái, chứng nhận, bảo hành, hotline | [M] |
| Header | Logo, dòng tagline, nhãn nút CTA + link | [M] |
| **Menu chính** | Danh sách mục: nhãn + link + thứ tự, thêm/xoá được | [M] |
| Mega menu | Hiện lấy từ danh mục sản phẩm thật | [Đ] |
| **Hotline** | Số hiển thị + số gọi (`tel:`) — dùng lại ở 12 chỗ | [M] |
| **Footer** | Mô tả công ty, tên pháp nhân, email, địa chỉ, copyright | [M] |
| **Footer — cột link** | 3 cột, mỗi cột: tiêu đề + danh sách link, thêm/xoá được | [M] |
| **Mạng xã hội** | Facebook, YouTube, Zalo, TikTok… — nhãn + link + hiện/ẩn | [M] |
| Nút gọi nổi | Hiện/ẩn, số điện thoại | [M] |
| SEO | Tiêu đề mặc định, mô tả mặc định, ảnh chia sẻ | [M] |

### 3.2 Trang chủ `/` — **toàn bộ đang hardcode**

| Khối | Nội dung cần sửa | TT |
|---|---|---|
| Hero | Eyebrow, tiêu đề (có từ gradient), mô tả, 2 nút, ảnh nền | [M] |
| Hero — số liệu | 3 con số + nhãn | [M] |
| Dải tin cậy (USP) | 4 mục: icon + tiêu đề + mô tả | [M] |
| Danh mục | Tiêu đề khối; thẻ lấy từ danh mục thật | [M] tiêu đề |
| Sản phẩm nổi bật | Tiêu đề khối, các tab, sản phẩm mỗi tab | [M] |
| Giải pháp | Tiêu đề khối + danh sách giải pháp (ảnh, tiêu đề, mô tả, link) | [M] |
| Công nghệ | Tiêu đề, mô tả, 4 đặc điểm, ảnh | [M] |
| Dự án & Kiến thức | Tiêu đề 2 cột + danh sách hiển thị | [M] |
| Form tư vấn | Tiêu đề, mô tả, nhãn nút | [M] |
| Cơ sở | Lấy từ node Cơ sở | [Đ] |

### 3.3 Các trang còn lại

| Trang | Đã sửa được | Cần bổ sung |
|---|---|---|
| `/san-pham` | Sản phẩm, danh mục, bộ lọc [Đ] | Tiêu đề trang, mô tả, nhãn khối trợ giúp [M] |
| `/san-pham/[slug]` | Toàn bộ thông tin sản phẩm [Đ] | — |
| `/gioi-thieu` | Hero, câu chuyện, khách hàng, quy trình, cam kết [Đ] | Nội dung khối CTA cuối trang [M] |
| `/tin-tuc` | Hero, bài viết [Đ] | Chọn **bài nổi bật** (đang hardcode slug) [M] |
| `/tin-tuc/[slug]` | Toàn bộ [Đ] | — |
| `/du-an`, `/du-an/[slug]` | Toàn bộ [Đ] | Đoạn mô tả "Sản phẩm sử dụng" đang hardcode [M] |
| `/dai-ly` | Hero, quyền lợi, điều kiện, form [Đ] | — |
| `/lien-he` | Hero, kênh liên hệ, form, cơ sở [Đ] | — |
| `/chinh-sach` | Toàn bộ [Đ] | — |
| Trang 404 | — | Tiêu đề, mô tả, nút [M] |

---

## 4. Yêu cầu về reCAPTCHA

Sửa được từ `/admin`, **chỉ quản trị viên**:

- Bật / tắt
- Site key, Secret key
- Ngưỡng điểm (0–1, mặc định 0.5)
- Nút **kiểm tra kết nối** — gọi thử Google và báo kết quả ngay

**Ràng buộc bảo mật:**
- Secret key hiển thị dạng `••••1234`, chỉ ghi đè khi nhập giá trị mới
- Secret **không** được nằm trong config export (`config/sync`) vì thư mục đó vào git — lưu ở `State` hoặc file settings
- Site key là public, vào config bình thường
- Frontend đọc site key **qua API lúc chạy**, không nhúng lúc build — nếu không thì đổi key vẫn phải build lại, tức là chưa đạt mục tiêu

## 5. Yêu cầu về giao diện quản trị

- **Trang Bảng điều khiển** tại `/admin/keybolts`: lối vào theo *trang của website*, không theo cấu trúc kỹ thuật của Drupal. Biên tập viên nghĩ "sửa trang chủ", không nghĩ "sửa node type home_page".
- Gom nhóm: **Nội dung trang** · **Danh mục sản phẩm** · **Cấu hình chung** · **Yêu cầu liên hệ**
- Mỗi trang có nút **Xem trang** mở đúng URL công khai
- Form nhập chia tab theo đúng thứ tự khối trên trang thật (đã làm cho Sản phẩm và các trang đơn)
- Ảnh có xem trước, giới hạn dung lượng, tự nén
- Tiếng Việt toàn bộ nhãn và mô tả trường
- Có mô tả hướng dẫn dưới mỗi trường khó hiểu

## 6. Yêu cầu kỹ thuật

- Nội dung mới **lên ngay** sau khi lưu — Nuxt SSR đang cache API, cần chiến lược xoá cache khi node được lưu
- Không được làm chậm trang: cấu hình chung gọi 1 API duy nhất, cache theo tag
- Sửa nội dung **không cần build lại frontend** — đây là ràng buộc quan trọng nhất, nó loại trừ mọi phương án nhúng dữ liệu lúc build
- Giữ nguyên kiến trúc headless hiện tại
- Mọi content type mới phải có script cài đặt idempotent trong `scripts/setup/`

## 7. Ngoài phạm vi

- Đa ngôn ngữ
- Kéo thả bố cục trang (Layout Builder)
- Quy trình duyệt bài nhiều bước
- Sửa giao diện/màu sắc từ admin
- Thương mại điện tử (giỏ hàng, thanh toán)

---

## 8. Câu hỏi cần chốt trước khi làm

1. **Menu chính** — dùng hệ thống Menu sẵn có của Drupal (mạnh, quen thuộc, hỗ trợ menu con) hay tạo cấu trúc riêng đơn giản hơn?
2. **Trang chủ** — gom vào 1 node đơn `home_page` với nhiều tab, hay tách thành từng khối rời để sắp xếp lại thứ tự?
3. **Bài nổi bật ở trang Tin tức** — chọn bằng ô tick trên bài viết, hay chọn trong cấu hình trang Tin tức?
4. **Ai được sửa reCAPTCHA** — chỉ quản trị viên, hay biên tập viên cũng được?
5. **Thứ tự ưu tiên** — làm trang chủ trước (khoảng trống lớn nhất) hay cấu hình chung trước (footer, menu, hotline — nhanh thấy kết quả)?
