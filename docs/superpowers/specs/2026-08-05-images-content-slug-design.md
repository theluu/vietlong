# Ảnh nhẹ, nội dung chi tiết nhập được, slug tự sinh

Ngày: 2026-08-05
Nhánh: `feat/static-pages`

Ba việc độc lập nhau về kỹ thuật nhưng cùng chạm vào bài viết và dự án, nên đi
chung một spec. Phần A đứng riêng được; phần B và C chạm cùng bộ trường nên nên
làm liền nhau.

---

## Bối cảnh đo được

Trang chủ production tải **33,8 MB ảnh**. Phân bổ:

| Nguồn | Dung lượng | Tỉ lệ |
|---|---|---|
| 6 ảnh hotlink từ `keybolts.com.vn` | 31,8 MB | 94% |
| Ảnh local đã là `.webp` | 2,0 MB | 6% |

Nặng nhất là `_r3_0152_copy_0.jpg` — 12,4 MB cho một tấm; kế đó 5,5 / 5,3 / 5,0 /
3,6 MB. Đây là ảnh gốc máy ảnh chưa qua xử lý, nằm trên server của site cũ.

Nguyên nhân cấu trúc: `field_article_image_url` và `field_project_image_url` là
**trường text chứa URL**, không phải image field. Drupal image style về nguyên
tắc không áp được lên URL ngoài. Thêm một ảnh bị hardcode thẳng trong
`TechBlock.vue:10`.

Toàn frontend không có chỗ nào dùng `srcset`/`sizes`; 28 thẻ `<img>` thì chỉ 10
thẻ có `loading`.

Điểm gỡ nút: **cả 9 ảnh đó đều đã có bản `.webp` là managed file trong Drupal
này rồi.** Migrate chỉ là gán `fid`, không phải tải gì từ site cũ.

---

## Phần A — Ảnh

### A1. Ảnh đại diện thành image field

Tạo `field_article_image` và `field_project_image` (kiểu `image`, một giá trị,
bắt buộc `alt`). Thay cho `field_article_image_url` / `field_project_image_url`.

Script migrate idempotent (`scripts/setup/migrate_image_urls_to_fields.php`) tra
`fid` theo bảng ánh xạ dưới đây rồi gán vào trường mới:

| URL cũ (tên file) | Managed file | fid |
|---|---|---|
| `6y7a5709_2.jpg` | `public://products/6y7a5709-2.webp` | 3 |
| `6y7a5711_0.jpg` | `public://products/6y7a5711-0.webp` | 2 |
| `6y7a5713_0.jpg` | `public://products/6y7a5713-0.webp` | 5 |
| `6y7a5715.jpg` | `public://products/6y7a5715.webp` | 6 |
| `6y7a5717_0.jpg` | `public://products/6y7a5717-0.webp` | 19 |
| `_r3_0152_copy_0.jpg` | `public://products/-r3-0152-copy-0.webp` | 16 |
| `_r3_0183_copy.jpg` | `public://products/-r3-0183-copy.webp` | 15 |
| `kb_1700-xl-pvd.png` | `public://products/kb-1700-xl-pvd.webp` | 1 |
| `khoa_thong_minh_t28_0.png` | `public://products/khoa-thong-minh-t28-0.webp` | 8 |

Script tra `fid` bằng truy vấn theo `uri` chứ không hardcode số, để chạy được
trên cả local lẫn dev nơi `fid` có thể lệch. Bảng trên chỉ để đối chiếu.

Trường URL cũ **giữ lại**, không xóa trong lần này. Xóa ở một commit riêng sau
khi đã verify trên dev — nếu ánh xạ sai thì dữ liệu gốc vẫn còn.

Nếu một node có URL không nằm trong bảng ánh xạ, script **dừng và báo tên file**
thay vì bỏ qua im lặng.

### A2. Image style và API

Bốn image style mới, đều xuất `webp` chất lượng 82, scale theo chiều rộng, không
crop (giữ nguyên tỉ lệ gốc để frontend tự cắt bằng `object-cover`):

| Machine name | Rộng | Dùng ở |
|---|---|---|
| `kb_card_400` | 400px | thẻ card trên lưới, ảnh liên quan |
| `kb_card_800` | 800px | card trên màn hình lớn / mật độ 2x |
| `kb_hero_1200` | 1200px | ảnh đầu trang detail |
| `kb_hero_1600` | 1600px | hero mật độ 2x |

Serializer đổi từ trả chuỗi URL sang trả một object:

```php
[
  'url'    => string,  // kb_card_800 làm mặc định cho <img src>
  'srcset' => string,  // "…400.webp 400w, …800.webp 800w, …"
  'width'  => int,     // kích thước gốc, để tính tỉ lệ
  'height' => int,
  'alt'    => string,
]
```

Áp cho `ArticleSerializer`, `ProjectSerializer` và cả `ProductSerializer`
(`field_images` hiện đang trả URL file gốc, chưa qua image style).

Gom phần dựng object này vào một class dùng chung —
`Serializer\ImageSerializer` — để ba serializer không lặp lại logic và để test
được độc lập. Đây là ranh giới rõ: đầu vào là một `ImageItem`, đầu ra là mảng
trên, không phụ thuộc gì vào loại nội dung.

### A3. Frontend

Không thêm dependency. `@nuxt/image` bị loại có chủ đích: khi ảnh đã nằm trong
image field thì Drupal đã cắt cỡ rồi, thêm `@nuxt/image` là dựng lại đúng việc
đó lần thứ hai kèm `sharp` và một tầng cache nữa trên server Nuxt.

Sửa 28 thẻ `<img>`:

- `srcset` + `sizes` khai theo bề rộng ô thật của từng component
- `width` / `height` từ kích thước gốc, chặn layout shift
- `loading="lazy"` cho mọi ảnh dưới màn hình đầu (hiện 18/28 thiếu)
- `fetchpriority="high"` cho ảnh hero trang chủ và ảnh đầu trang detail
- `decoding="async"` mặc định

Kiểu ảnh trong `types/page.ts` đổi từ `string` sang interface tương ứng object ở
A2. TypeScript sẽ chỉ ra đúng mọi chỗ cần sửa.

### A4. Dọn dẹp

- Gỡ ảnh hardcode `keybolts.com.vn/...t28_0.png` trong `TechBlock.vue:10`
  (769 KB → 64 KB), chuyển sang lấy từ dữ liệu như các ảnh khác.
- Xóa 1,1 MB thư mục rác `web/sites/default/files/home/web/sites/default/files/about/`
  do rsync lồng sai đường dẫn.

---

## Phần B — Nội dung chi tiết cho bài viết và dự án

### B1. Trường body

Thêm `field_article_body` và `field_project_body`, kiểu `text_long`, format mặc
định `basic_html`. Role `bien_tap_vien` đã sẵn quyền `use text format basic_html`
nên không cần cấp thêm gì.

Đặt trong tab "Nội dung chi tiết" của form display đã có.

### B2. Migrate 8 bài viết từ JSON sang HTML

`field_article_sections` đang là JSON gõ tay trong textarea, cấu trúc mỗi phần:
`{ id, title, paragraphs[], list[], note }`. Ánh xạ sang HTML không mất dữ liệu:

| JSON | HTML |
|---|---|
| `title` | `<h2 id="{id}">` |
| `paragraphs[]` | `<p>` mỗi phần tử |
| `list[]` | `<ul><li>` |
| `note` | `<blockquote>` |

Script `scripts/setup/migrate_article_sections_to_body.php`, idempotent: bỏ qua
node nào đã có body. Sau khi migrate, `field_article_sections` ngừng được đọc
nhưng vẫn giữ lại một nhịp như trường ảnh cũ.

`field_article_compare` và `field_article_faqs` **không** đụng tới — chúng render
thành bảng so sánh và khối FAQ có cấu trúc riêng, không phải văn xuôi.

### B3. API

Body trả về HTML đã lọc qua `check_markup($value, $format)`. Đây là điểm chặn
duy nhất: biên tập viên dán nội dung từ nguồn lạ cũng không chèn được script,
vì `basic_html` không cho qua thẻ `<script>` và các thuộc tính `on*`.

Không trả raw value ra API trong bất kỳ trường hợp nào.

### B4. Frontend

Trang detail render body bằng `v-html` — an toàn vì HTML đã lọc ở B3.

Mục lục bài viết hiện dựng từ mảng `sections`; đổi sang quét các thẻ `h2` trong
body đã render và lấy `id` của chúng. Dự án cũng có mục lục theo cùng cơ chế nếu
body có từ hai `h2` trở lên.

Kiểu chữ cho nội dung do editor nhập cần một lớp bao (`prose`-style) định nghĩa
sẵn cỡ chữ, khoảng cách dòng và margin cho `h2/h3/p/ul/ol/blockquote/img`, khớp
với các token đang dùng trong `tokens.css`. Không để mặc định của trình duyệt lọt
ra ngoài.

### B5. Gỡ văn bản hardcode

`du-an/[slug].vue:79` đang chứa một đoạn văn cứng hiện y hệt nhau ở mọi dự án
("Công trình dùng…, chọn theo độ dày cánh và chiều mở của từng nhóm cửa…"). Gỡ,
thay bằng body. Khối `ArticleCallout` phía trên vẫn giữ vì nó lấy từ
`project.description` — dữ liệu thật.

---

## Phần C — Slug tự sinh

### C1. Ô tích

Thêm `field_article_slug_auto` và `field_project_slug_auto` (boolean, mặc định
bật), nhãn "Tự sinh từ tiêu đề", đặt ngay dưới ô slug.

### C2. Sinh slug

`hook_ENTITY_TYPE_presave()` trong `keybolts_core`: nếu ô tích đang bật thì sinh
lại slug từ tiêu đề. Bỏ tích thì giá trị gõ tay được giữ nguyên, không đụng vào.

**Hệ quả cần biết:** ô tích bật nghĩa là đổi tiêu đề một bài đã đăng sẽ đổi cả
URL của nó, và link cũ 404. Module `redirect` đã cài nhưng không đỡ được ca này,
vì slug ở đây là trường dữ liệu chứ không phải path alias. Chọn cách này vì sản
phẩm trên site **đã hành xử đúng như vậy** (slug lấy từ alias pathauto, đổi tên
là đổi URL), nên giữ một hành vi thống nhất tốt hơn là mỗi loại nội dung một
kiểu. Biên tập viên muốn giữ URL cũ thì bỏ tích trước khi sửa tiêu đề.

Thuật toán khớp đúng cấu hình pathauto đang chạy trên site (`transliterate: true`,
separator `-`, `case: true`, `max_length: 150`): dịch chuyển ký tự bằng dịch vụ
`transliteration` của Drupal, hạ chữ thường, thay mọi ký tự không phải
`[a-z0-9]` bằng `-`, gộp `-` liên tiếp, cắt `-` ở hai đầu.

Đã đối chiếu trên dữ liệu thật: "Cách chọn khóa theo độ dày cửa" →
`cach-chon-khoa-theo-do-day-cua`, khớp chính xác slug đang lưu. Nên bật tính năng
này **không làm đổi URL của bài nào đang chạy**.

### C3. Chống trùng

Trước khi gán, truy vấn xem slug đã tồn tại ở node khác cùng loại chưa. Nếu có,
thêm hậu tố `-2`, `-3`… cho tới khi rỗng. Node đang lưu tự loại mình khỏi phép
kiểm tra để lưu lại lần hai không tự đẩy slug của chính nó lên `-2`.

Slug rỗng sau khi sinh (tiêu đề toàn ký tự đặc biệt) thì phải lùi về một giá trị
khác chứ không được để trống — trống sẽ làm API 404 vĩnh viễn. **Không dùng
`nid`**: ở `presave` của một node mới, `nid` chưa tồn tại. Dùng `bai-viet` /
`du-an` rồi để C3 tự đẩy thành `bai-viet-2`, `bai-viet-3` — cùng một cơ chế
chống trùng, không phải viết đường riêng.

---

## Kiểm thử

Kernel test trong `keybolts_core` và `keybolts_api`:

- `ImageSerializer` dựng đúng `srcset` cho cả bốn style, và trả `NULL` gọn gàng
  khi trường ảnh rỗng thay vì tạo mảng có `url` rỗng.
- Body đi qua `check_markup`: `<script>` và `onerror=` bị loại khỏi output API.
- Sinh slug từ tiêu đề tiếng Việt có dấu ra đúng chuỗi mong đợi.
- Chống trùng: hai node cùng tiêu đề ra `x` và `x-2`; lưu lại node thứ nhất lần
  nữa vẫn giữ `x`, không nhảy thành `x-2`.
- Ô tích tắt thì presave không đụng vào slug gõ tay.
- Migrate JSON → HTML: chạy hai lần cho kết quả giống hệt một lần (idempotent).

Đo lại thực tế sau khi xong phần A: tổng dung lượng ảnh trang chủ trước và sau,
ghi con số thật vào commit message thay vì ước lượng.

---

## Thứ tự triển khai

Phần A trước và triển khai riêng — nó là vấn đề cấp bách nhất, độc lập với B và
C, và đo được kết quả ngay. B và C đi cùng nhau ở lần sau vì cùng chạm bộ trường
của article/project và cùng cần một lần `drush cex`.
