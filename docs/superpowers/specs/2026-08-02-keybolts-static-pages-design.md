# Keybolts — trang tĩnh, cơ sở dùng chung và 404

Ngày: 2026-08-02
Trạng thái: đã chốt, chờ viết plan

## 1. Phạm vi

Đợt 1 của phần việc còn lại. Dựng 4 trang tĩnh và trang 404:

- `/gioi-thieu` — Giới thiệu
- `/dai-ly` — Đại lý
- `/lien-he` — Liên hệ
- `/chinh-sach` — Chính sách
- trang 404

Nguồn thiết kế: `design/Keybolts About.html`, `Dealers.html`, `Contact.html`,
`Policies.html`. Design là nguồn đúng — đọc lại ngay trước khi dựng từng trang,
và lấy **lần xuất hiện cuối cùng** của mỗi token, không lấy lần đầu.

### Ngoài phạm vi đợt này

- Tin tức, Article, Dự án (đợt 2 — cần content type `article`, `project`)
- Gọn hoá form nhập 28 field sản phẩm và soát FE 3 trang đã có (đợt 3)
- Gửi email khi có form mới
- Nhúng bản đồ có API key

## 2. Quyết định đã chốt

| Quyết định | Chọn | Vì sao |
|---|---|---|
| Nội dung sửa ở đâu | Toàn bộ trong CMS | Người dùng yêu cầu toàn quyền biên tập |
| Mô hình hoá | Mỗi trang một content type riêng, node singleton | Form nhập bám sát design nên không nhập sai được, và FE không phải xử lý khối lạ. Page builder linh hoạt đánh đổi trực tiếp cả hai mục tiêu "nhập liệu gọn" và "giống 100% design" |
| Form | Lưu vào Drupal, xem trong admin | Không phụ thuộc cấu hình SMTP nên chạy được ngay; gửi mail để đợt sau |
| Cơ sở/showroom | Content type `branch` dùng chung | Xuất hiện ở 4 trang; hiện đang hard-code trong `homeContent.ts` |

## 3. Mô hình nội dung

### 3.1 `branch` — nhiều node

Cơ sở/showroom dùng chung cho Trang chủ, Giới thiệu, Đại lý, Liên hệ.

| Field | Kiểu | Ghi chú |
|---|---|---|
| `title` | — | Tên cơ sở, vd "Showroom Từ Sơn" |
| `field_tag` | string | "Bán buôn", "Cơ sở 1"… |
| `field_address` | string_long | |
| `field_phone_display` | string | Có dấu chấm: `0912.411.309` |
| `field_phone_tel` | string | Chỉ số: `0912411309` |
| `field_map_url` | link | Cho nút "Chỉ đường"; để trống thì nút ẩn |
| `field_sort_order` | integer | |

`field_phone_display` và `field_phone_tel` tách riêng vì design hiển thị số có
dấu chấm nhưng `href="tel:"` phải là số trơn.

### 3.2 Bốn node singleton

Mỗi content type chỉ có đúng một node. Trang biên tập vào thẳng node đó.

**`about_page`**

- Hero: `field_eyebrow`, `title`, `field_subtitle`, `field_hero_image`,
  `field_hero_caption`, `field_cta_primary` (link), `field_cta_secondary` (link)
- `field_facts` → paragraph `fact` ×5 — 2014 / 5 / 200+ / 10 / CE-CFF
- Câu chuyện: `field_story_eyebrow`, `field_story_title`, `field_story_body`
  (text_long, có định dạng), `field_credentials` (string, nhiều giá trị) ×4
- `field_segments` → paragraph `segment` ×4 — nhóm khách hàng
- `field_steps` → paragraph `numbered_item` ×5 — quy trình
- `field_values` → paragraph `value_item` ×3 — cam kết

**`dealers_page`**

- Hero: `field_eyebrow`, `title`, `field_subtitle`
- `field_benefits` → paragraph `numbered_item` ×4
- `field_criteria` (string, nhiều giá trị) ×4
- Form: `field_form_title`, `field_form_desc`, `field_success_title`,
  `field_success_desc`

**`contact_page`**

- Hero: `field_eyebrow`, `title`, `field_subtitle`
- `field_channels` → paragraph `contact_channel` — hotline, Zalo, email
- `field_company_name`, `field_company_address`
- `field_form_title`, `field_form_desc`, `field_success_title`,
  `field_success_desc`
- `field_response_title`, `field_response_body` — khối "Chúng tôi trả lời trong
  24 giờ"

**`policies_page`**

- Hero: `field_eyebrow`, `title`, `field_subtitle`
- `field_sections` → paragraph `policy_section` (nhiều)
- `field_support_title`, `field_support_note` — hộp "Cần hỗ trợ?"

### 3.3 Paragraph

Dùng lại tối đa để form nhập ít loại khối:

| Paragraph | Field | Dùng ở |
|---|---|---|
| `fact` | `field_fact_number`, `field_fact_label` | About |
| `numbered_item` | `field_item_number`, `field_item_title`, `field_item_desc` | About (quy trình), Dealers (quyền lợi) |
| `segment` | `field_seg_title`, `field_seg_desc`, `field_seg_cta` (link), `field_seg_image` | About |
| `value_item` | `field_value_title`, `field_value_desc` | About |
| `policy_section` | `field_pol_label`, `field_pol_eyebrow`, `field_pol_title`, `field_pol_intro`, `field_pol_items` → `policy_item`, `field_pol_note` | Policies |
| `policy_item` | `field_pol_key`, `field_pol_value` | lồng trong `policy_section` |
| `contact_channel` | `field_ch_label`, `field_ch_value`, `field_ch_note`, `field_ch_url` | Contact |

`numbered_item` phục vụ cả quy trình 5 bước lẫn 4 quyền lợi vì cấu trúc trùng
khít (số / tiêu đề / mô tả).

### 3.4 `contact_submission` — entity lưu form

Content entity riêng, không phải node — nó là dữ liệu vận hành, không phải nội
dung xuất bản, và không nên lẫn vào `/admin/content`.

| Field | Kiểu |
|---|---|
| `name` | string, bắt buộc |
| `phone` | string, bắt buộc |
| `message` | string_long |
| `source` | list_string: `contact` \| `dealer` \| `consult` |
| `created` | timestamp |
| `ip` | string |

Có route admin `/admin/keybolts/submissions` liệt kê theo thời gian.

### 3.5 Nhập liệu gọn gàng

Mọi form node được nhóm thành tab dọc, đặt tên đúng theo khối của design:
"Hero", "Con số", "Câu chuyện", "Khách hàng", "Quy trình", "Cam kết". Đây cũng
là cách sẽ áp cho 28 field sản phẩm ở đợt 3.

Việc này cần **`drupal/field_group`, hiện chưa được cài** — plan phải thêm nó qua
composer và bật lên như một bước riêng, trước khi cấu hình form display. Không
có module này thì mọi field vẫn nằm trong một danh sách phẳng.

Nếu vì lý do nào đó không dùng được `field_group`, phương án thay thế là sắp xếp
thứ tự field cho hợp lý và viết mô tả rõ ràng cho từng field — kém hơn nhưng vẫn
dùng được, và không chặn phần còn lại của đợt.

## 4. API

Envelope giữ nguyên như `ApiEnvelope` hiện có.

### `GET /api/v1/page/{key}`

`key` ∈ `about` | `dealers` | `contact` | `policies`. Trả `data` là payload đã
dựng theo đúng khối của design, để FE không phải suy luận cấu trúc. Key không
hợp lệ hoặc node chưa tạo → 404.

Cache tag: `node_list:{key}_page`.

### `GET /api/v1/branches`

Danh sách `branch` đã sắp theo `field_sort_order`. Cache tag `node_list:branch`.

### `POST /api/v1/contact`

Body: `{ name, phone, message, source, website }`.

- `website` là **honeypot** — trường ẩn; nếu có giá trị thì trả 201 như bình
  thường nhưng **không lưu gì**. Báo lỗi cho bot chỉ giúp nó dò ra cách vượt.
- Thiếu `name` hoặc `phone` → 422 kèm danh sách trường lỗi.
- Quá 5 lần trong 10 phút từ cùng IP → 429.
- `source` không hợp lệ → mặc định `contact`.

Endpoint này ghi dữ liệu nên phải nằm ngoài mọi cache và trả
`Cache-Control: no-store`.

## 5. Frontend

### Trang

| Route | File | Nguồn dữ liệu |
|---|---|---|
| `/gioi-thieu` | `app/pages/gioi-thieu.vue` | `page/about` + `branches` |
| `/dai-ly` | `app/pages/dai-ly.vue` | `page/dealers` + `branches` |
| `/lien-he` | `app/pages/lien-he.vue` | `page/contact` + `branches` |
| `/chinh-sach` | `app/pages/chinh-sach.vue` | `page/policies` |
| 404 | `app/error.vue` | — |

### Component

Tách theo khối của design, mỗi file một việc: `PageHero`, `FactStrip`,
`StoryBlock`, `SegmentGrid`, `StepList`, `ValueGrid`, `BenefitGrid`,
`CriteriaList`, `PolicyNav`, `PolicySection`, `ContactChannels`, `BranchGrid`,
`LeadForm`.

`LeadForm` dùng chung cho cả Liên hệ, Đại lý và form tư vấn ở Trang chủ — nhận
`source` qua prop, tự xử lý trạng thái gửi / thành công / lỗi, và có sẵn honeypot.

### Dọn trùng lặp

Xoá `LOCATIONS` khỏi `app/utils/homeContent.ts`; `HomeBranchList` chuyển sang
đọc `/api/v1/branches`. Đây là lý do `branch` được tách ra ngay từ đợt này.

### Trang 404

Design **không có** trang 404. Dựng từ hệ thống hiện có, không bịa phong cách
mới: nền `charcoal-900`, tiêu đề gradient vàng như hero Trang chủ, câu dẫn tiếng
Việt, hai nút "Về trang chủ" và "Xem sản phẩm", cộng ô tìm kiếm mở
`SearchOverlay`. `app/error.vue` xử lý cả 404 và 500, phân biệt bằng
`error.statusCode`.

## 6. Kiểm thử

**Kernel (PHPUnit)**

- Serializer trang trả đúng khối, và trả 404 khi node singleton chưa tồn tại
- `POST /api/v1/contact`: lưu bản ghi hợp lệ; 422 khi thiếu trường; honeypot dính
  thì trả 201 mà **không** tạo bản ghi; 429 khi vượt tần suất
- `/api/v1/branches` sắp đúng theo `field_sort_order`

**Vitest**

- Helper validate form phía client: bắt buộc name/phone, chuẩn hoá số điện thoại

**Trình duyệt**

Mỗi trang đối chiếu prototype ở 375 / 768 / 1440px, kiểm tra bằng *nội dung* chứ
không chỉ mã trạng thái — bài học từ lần proxy trả 200 nhưng phục vụ nhầm site
của project khác.

## 7. Điều kiện hoàn thành

- [ ] 4 trang render server-side với dữ liệu thật từ CMS, không hard-code
- [ ] Mọi link trên menu và footer trỏ tới trang thật, không còn 404 ngoài ý muốn
- [ ] Gửi form từ Liên hệ và Đại lý tạo được bản ghi, xem được trong admin
- [ ] Honeypot và rate limit hoạt động đúng như mục 4
- [ ] Trang chủ đọc cơ sở từ API; `LOCATIONS` đã bị xoá khỏi code
- [ ] 404 hiển thị đúng cho URL không tồn tại, giữ nguyên chrome của site
- [ ] `drupal/field_group` đã cài và bật; form nhập của cả 4 content type đã nhóm
      tab, nhập được không cần hướng dẫn
- [ ] Toàn bộ test xanh; không tràn ngang ở 375 / 768 / 1440
