# KEYBOLTS WEBSITE REDESIGN — MASTER SPEC FOR CLAUDE

## 1. Mục tiêu

Làm lại website hiện tại:

- Website gốc: https://keybolts.com.vn/
- Lĩnh vực: Khóa cửa, khóa thông minh, khóa vân tay, khóa khách sạn, bản lề và phụ kiện cửa.
- Mục tiêu không chỉ là thay giao diện.
- Website mới phải trở thành:
  - Website thương hiệu.
  - Catalog sản phẩm.
  - Kênh thu khách hàng tiềm năng.
  - Công cụ hỗ trợ Sales.
  - Nền tảng SEO.
  - Nền tảng GEO / AI Search.
  - Nền tảng có thể mở rộng CRM, đại lý, e-commerce và AI sau này.

Công nghệ bắt buộc:

- Backend / CMS: Drupal 11.
- Frontend: Vue.js.
- Khuyến nghị triển khai public frontend bằng Nuxt dựa trên Vue.js để hỗ trợ SSR/SSG tốt hơn cho SEO, GEO và tốc độ tải trang.
- Kiến trúc: Decoupled / Headless.
- Drupal quản lý dữ liệu và nghiệp vụ.
- Vue/Nuxt chịu trách nhiệm giao diện người dùng.
- Giao tiếp Drupal ↔ Frontend qua API.

---

# 2. Nguyên tắc khi Claude thực hiện

Claude cần thực hiện theo 3 góc nhìn đồng thời:

1. Business:
   - Người không biết công nghệ vẫn hiểu website mới giải quyết vấn đề gì.
   - Thấy rõ lợi ích và lý do nên làm lại ngay.

2. UX/UI:
   - Website phải hiện đại, cao cấp, phù hợp thương hiệu khóa và thiết bị nội thất.
   - Mobile First.
   - Ưu tiên hình ảnh sản phẩm.
   - CTA bán hàng rõ ràng.

3. Technical:
   - Có cấu trúc dữ liệu sạch.
   - Dễ quản trị.
   - Dễ mở rộng.
   - Bảo mật.
   - Tốc độ tốt.
   - SEO tốt.
   - GEO / AI Search tốt.

Không làm website theo tư duy chỉ đổi theme.

---

# 3. Cấu trúc nội dung proposal gửi khách hàng

Khi viết proposal hoặc mô tả gửi khách hàng, bắt buộc theo flow:

## 3.1. Nỗi đau

Mô tả đơn giản, tránh thuật ngữ kỹ thuật.

Website hiện tại có nhiều dữ liệu và sản phẩm nhưng:

- Giao diện đã cũ.
- Chưa thể hiện tốt hình ảnh thương hiệu cao cấp.
- Danh mục sản phẩm nhiều nhưng khó tìm nhanh.
- Thông tin sản phẩm chưa được bố trí tối ưu cho quyết định mua hàng.
- Trải nghiệm mobile chưa phải trọng tâm.
- Luồng từ xem sản phẩm đến liên hệ tư vấn chưa rõ.
- Website thiên về catalog hơn là công cụ bán hàng.
- Khó tận dụng dữ liệu để mở rộng CRM, đại lý, app hoặc AI.
- Cấu trúc nội dung chưa tối ưu đầy đủ cho Google và các công cụ AI Search mới.

Thông điệp chính:

> Website hiện tại giống một kho thông tin sản phẩm. Website mới cần trở thành một công cụ thương hiệu và bán hàng thực sự.

---

## 3.2. Ai hưởng lợi

### Khách hàng

Khách cần thực hiện được flow đơn giản:

Tìm nhu cầu  
→ Chọn loại khóa  
→ Lọc sản phẩm phù hợp  
→ Xem ảnh và thông số  
→ So sánh  
→ Hỏi giá  
→ Chat Zalo / gọi điện / gửi yêu cầu tư vấn.

### Sales

Sales nhận được:

- Số điện thoại.
- Email.
- Sản phẩm khách quan tâm.
- Trang khách gửi yêu cầu.
- Nội dung khách cần tư vấn.
- Lead đăng ký đại lý.

Có thể mở rộng thành CRM.

### Người quản trị

Không cần sửa code khi:

- Thêm sản phẩm.
- Sửa sản phẩm.
- Thay ảnh.
- Thay banner.
- Thêm danh mục.
- Đăng tin.
- Cập nhật chính sách.
- Thêm đại lý.
- Chọn sản phẩm nổi bật.

### Doanh nghiệp

Website phục vụ đồng thời:

- Thương hiệu.
- Catalog.
- Marketing.
- SEO.
- GEO.
- Lead Generation.
- Sales.
- Đại lý.
- Khả năng mở rộng dài hạn.

---

# 4. Giải pháp tổng thể

Website mới cần chuyển từ:

Website giới thiệu + Catalog

thành:

Brand Website  
+ Product Catalog  
+ Lead Generation  
+ Sales Support  
+ SEO  
+ GEO / AI Search  
+ Dealer Platform  
+ Future CRM  
+ Future E-commerce  
+ Future AI Assistant.

Luồng kinh doanh chính:

Google / Facebook / Zalo / AI Search  
→ Website Keybolts  
→ Danh mục  
→ Sản phẩm  
→ Xem thông tin  
→ Nhận báo giá / Zalo / Hotline  
→ Sales  
→ Khách hàng.

Không đặt checkout online là mục tiêu bắt buộc của giai đoạn đầu.

---

# 5. Sitemap đề xuất

## 5.1. Trang chủ

Các section:

1. Header.
2. Hero Banner.
3. Danh mục sản phẩm chính.
4. Sản phẩm nổi bật.
5. Sản phẩm mới.
6. Giải pháp theo nhu cầu.
7. Vì sao chọn Keybolts.
8. Dự án / Công trình.
9. Thương hiệu / Đối tác.
10. Tin tức / Tư vấn.
11. Hệ thống đại lý.
12. CTA nhận tư vấn.
13. Footer.

---

## 5.2. Sản phẩm

Trang tổng sản phẩm.

Có:

- Danh mục.
- Tìm kiếm.
- Bộ lọc.
- Sắp xếp.
- Pagination hoặc Load More.

Các nhóm sản phẩm có thể gồm:

- Khóa thông minh.
- Khóa vân tay.
- Khóa khách sạn.
- Khóa đồng.
- Khóa tay gạt.
- Khóa âm.
- Khóa chống trộm.
- Bản lề.
- Chốt cửa.
- Phụ kiện cửa.
- Phụ kiện tủ / bếp.

Không hard-code taxonomy.

---

## 5.3. Trang danh mục

Ví dụ:

Khóa cửa  
→ Khóa thông minh  
→ Khóa vân tay  
→ Khóa khách sạn.

Bộ lọc có thể gồm:

- Loại sản phẩm.
- Thương hiệu.
- Loại cửa.
- Chất liệu.
- Màu sắc.
- Công nghệ mở khóa.
- Mức giá.
- Tình trạng hàng.

Filter phải linh hoạt theo category.

---

## 5.4. Chi tiết sản phẩm

Bố cục ưu tiên conversion.

### Phần đầu

- Breadcrumb.
- Gallery ảnh lớn.
- Thumbnail ảnh.
- Tên sản phẩm.
- Mã sản phẩm.
- Thương hiệu.
- Trạng thái.
- Giá hoặc "Liên hệ".
- CTA:
  - Nhận báo giá.
  - Chat Zalo.
  - Gọi tư vấn.

### Thông tin nhanh

- Loại sản phẩm.
- Chất liệu.
- Màu sắc.
- Kích thước.
- Loại cửa phù hợp.
- Công nghệ mở khóa.
- Xuất xứ.
- Bảo hành.

### Nội dung chi tiết

- Mô tả.
- Tính năng nổi bật.
- Thông số kỹ thuật.
- Ứng dụng.
- Hình ảnh thực tế.
- Video.
- Hướng dẫn lắp đặt.
- Chính sách bảo hành.
- Chính sách giao hàng.
- Bảo dưỡng.

### Cuối trang

- Sản phẩm tương tự.
- Sản phẩm liên quan.
- Bài tư vấn liên quan.
- Form yêu cầu báo giá.

---

# 6. Các trang nội dung khác

## Giới thiệu

- Giới thiệu doanh nghiệp.
- Thương hiệu.
- Năng lực.
- Giá trị.
- Hệ thống phân phối.

## Dự án / Công trình

Mỗi dự án có:

- Tên.
- Hình ảnh.
- Địa điểm.
- Loại công trình.
- Sản phẩm sử dụng.
- Nội dung mô tả.
- Related products.

## Tin tức / Tư vấn

Các nhóm:

- Hướng dẫn chọn khóa.
- So sánh sản phẩm.
- Hướng dẫn sử dụng.
- Bảo dưỡng.
- Xu hướng.
- Tin doanh nghiệp.

## Đại lý

- Chính sách đại lý.
- Đăng ký đại lý.
- Danh sách đại lý.
- Tìm đại lý gần nhất.

## Chính sách

- Bảo hành.
- Giao hàng.
- Đổi trả.
- Thanh toán.
- Bảo mật thông tin.

## Liên hệ

- Hotline.
- Zalo.
- Email.
- Địa chỉ.
- Google Maps.
- Form liên hệ.

---

# 7. Công nghệ

## 7.1. Drupal 11

Drupal 11 đóng vai trò:

> Trung tâm quản trị nội dung và dữ liệu của toàn bộ hệ thống.

Drupal quản lý:

- Products.
- Categories.
- Brands.
- Product specifications.
- News.
- Pages.
- Projects.
- Dealers.
- Branches.
- Policies.
- Leads.
- Banners.
- SEO metadata.
- Users.
- Roles.
- Permissions.

Lợi ích cần nhấn mạnh với khách hàng:

- Phù hợp hệ thống nhiều dữ liệu.
- Quản lý nội dung có cấu trúc tốt.
- Phân quyền chi tiết.
- Có workflow duyệt nội dung.
- API-first.
- Có thể dùng chung dữ liệu cho website, app, CRM và AI.
- Không phụ thuộc giao diện frontend.
- Dễ mở rộng lâu dài.

Không nói Drupal "tốt nhất mọi CMS".

Nên nói:

> Drupal phù hợp hơn với Keybolts khi hệ thống có nhiều loại sản phẩm, nhiều thông số, nhiều nhóm quản trị và cần mở rộng thành một nền tảng số trong tương lai.

---

## 7.2. Vue.js / Nuxt

Vue.js chịu trách nhiệm phần giao diện khách hàng.

Ưu điểm:

- UI hiện đại.
- Tương tác mượt.
- Filter nhanh.
- Search nhanh.
- Gallery ảnh tốt.
- Component reusable.
- Mobile First.
- Dễ xây trải nghiệm giống web app.

Public frontend khuyến nghị dùng:

- Vue.js.
- Nuxt.

Mục đích:

- SSR.
- SSG khi phù hợp.
- SEO tốt.
- HTML render sẵn.
- Crawl dễ hơn.
- Tốc độ tải trang đầu tốt hơn SPA thuần.

Kiến trúc:

Drupal 11  
→ JSON:API / REST / Custom API  
→ Nuxt / Vue  
→ Web Browser.

---

# 8. Tại sao kiến trúc Drupal 11 + Vue/Nuxt hiện đại

Giải thích cho người không rành công nghệ:

Drupal = bộ máy phía sau.  
Vue/Nuxt = showroom phía trước.

Có thể thay đổi showroom mà không phải phá bộ máy dữ liệu.

Có thể thêm:

- Mobile App.
- CRM.
- Dealer Portal.
- AI Assistant.
- E-commerce.

mà vẫn sử dụng nguồn dữ liệu chung.

Architecture:

```text
                         Drupal 11
                    Content / Data Hub
                           |
                    API / JSON:API
                           |
              +------------+------------+
              |                         |
              v                         v
         Vue / Nuxt Web             Future App
              |
       +------+-------+--------+
       |              |        |
       v              v        v
     SEO            GEO      Sales
   Google        AI Search    Leads
```

---

# 9. Bảo mật

Website phải được thiết kế theo security baseline.

## Drupal

- Drupal 11 bản được hỗ trợ.
- Composer-managed dependencies.
- Update security định kỳ.
- Chỉ sử dụng module có maintenance tốt.
- Không cài module không cần thiết.

## Authentication

- Strong password policy.
- Rate limit login.
- Failed login protection.
- Optional 2FA cho admin.
- Session timeout hợp lý.

## Authorization

Tạo roles tối thiểu:

- Administrator.
- Content Manager.
- Product Manager.
- Editor.

Áp dụng least privilege.

## Infrastructure

- HTTPS.
- Firewall.
- Reverse proxy.
- Security headers.
- CSP khi phù hợp.
- Backup.
- Log.
- Monitoring.
- Rate limiting API.
- Protect admin routes.
- Disable unused endpoints.

## Upload

- Validate MIME.
- Validate extension.
- Limit size.
- Không execute file upload.

---

# 10. Performance

Mục tiêu:

Website phải nhanh trên:

- Mobile.
- 4G/5G.
- Desktop.

Áp dụng:

- Drupal Cache.
- API Cache.
- Redis nếu cần.
- CDN.
- Browser Cache.
- HTTP compression.
- WebP / AVIF.
- Responsive Image.
- Lazy Loading.
- JS code splitting.
- CSS optimization.
- Font optimization.
- SSR / SSG.
- Database indexes.
- Query optimization.

Theo dõi Core Web Vitals:

- LCP.
- INP.
- CLS.

Không tối ưu bằng cách hy sinh SEO hoặc UX.

---

# 11. SEO

SEO phải là requirement từ đầu, không làm sau khi code xong.

## Technical SEO

Bắt buộc:

- Semantic HTML.
- SSR.
- Clean URL.
- Canonical.
- XML Sitemap.
- robots.txt.
- Breadcrumb.
- 301 redirect.
- 404 page.
- OpenGraph.
- Twitter/X metadata nếu cần.

## Per content SEO

Mỗi:

- Product.
- Category.
- Article.
- Project.
- Page.

có:

- Meta title.
- Meta description.
- Slug.
- H1.
- Image alt.
- Canonical.

## Structured Data

Áp dụng Schema.org phù hợp:

- Organization.
- Product.
- BreadcrumbList.
- Article.
- FAQPage khi nội dung thực sự là FAQ.
- LocalBusiness khi phù hợp.
- WebSite.

Không tạo structured data giả.

## Internal Linking

Xây relationship:

Category  
↔ Product  
↔ Article  
↔ Project  
↔ Brand.

---

# 12. GEO / AI Search

GEO = Generative Engine Optimization.

Mục tiêu:

Không chỉ để Google crawl được.

Mà để:

- ChatGPT.
- Gemini.
- Google AI Overview.
- Copilot.
- Các AI Search Engine.

dễ hiểu Keybolts và sản phẩm.

## Nguyên tắc

Nội dung phải trả lời rõ:

- Keybolts là ai?
- Bán sản phẩm gì?
- Sản phẩm dành cho ai?
- Dùng cho loại cửa nào?
- Thông số gì?
- Ưu điểm?
- Hạn chế?
- So sánh với sản phẩm khác?
- Bảo hành thế nào?
- Liên hệ ở đâu?

## Product data cần có cấu trúc

Không viết tất cả thông số vào một textarea.

Tách field:

- Brand.
- Product Type.
- Door Type.
- Material.
- Color.
- Unlock Methods.
- Dimensions.
- Warranty.
- Origin.
- Certification.
- Price Range.
- Availability.

Mục đích:

Drupal có dữ liệu máy đọc được.

Frontend có thể render dữ liệu rõ ràng.

AI dễ hiểu entity và relationship hơn.

## Content Strategy

Tạo các content hỗ trợ AI Search:

- FAQ.
- Buying Guide.
- Product Comparison.
- How-to.
- Product Usage.
- Troubleshooting.
- Project Case Study.

Ví dụ:

- Nên chọn khóa vân tay nào cho cửa gỗ?
- Khóa thông minh cho biệt thự cần những tính năng gì?
- Khóa thẻ từ và khóa vân tay khác nhau thế nào?
- Cách chọn khóa cho cửa dày 45 mm.

Không keyword stuffing.

Ưu tiên:

- Nội dung chính xác.
- Có chuyên môn.
- Có cấu trúc.
- Có nguồn/brand rõ ràng.
- Answer-first.

---

# 13. Content Model Drupal 11

Claude cần đề xuất và triển khai content model rõ ràng.

## Product

Fields:

- Title.
- Slug.
- Product Code.
- Images multiple.
- Videos multiple.
- Product Category reference.
- Brand reference.
- Origin.
- Stock Status.
- Price.
- Contact Price boolean.
- Promotion.
- Color.
- Material.
- Dimensions.
- Weight.
- Door Type.
- Product Type.
- Unlock Methods.
- Certification.
- Warranty.
- Short Description.
- Description.
- Features.
- Specifications.
- Installation Content.
- Installation Video.
- Shipping Policy reference.
- Warranty Policy reference.
- Related Products.
- Related Articles.
- SEO fields.
- Featured boolean.
- New Product boolean.
- Sort Order.

Không phải product nào cũng hiển thị toàn bộ field.

---

## Product Category

- Name.
- Slug.
- Parent.
- Image.
- Description.
- SEO.
- Sort Order.

Hỗ trợ hierarchy.

---

## Brand

- Name.
- Logo.
- Description.
- Website.
- SEO.

---

## Article

- Title.
- Category.
- Thumbnail.
- Summary.
- Body.
- Author.
- Publish Date.
- Related Products.
- SEO.

---

## Project

- Title.
- Project Type.
- Location.
- Images.
- Description.
- Products Used.
- Completion Date.
- SEO.

---

## Dealer

- Name.
- Address.
- Province.
- District.
- Phone.
- Email.
- Map.
- Latitude.
- Longitude.
- Active.

---

## Branch

- Name.
- Address.
- Phone.
- Email.
- Map.
- Sort Order.

---

## Lead

Fields:

- Full Name.
- Phone.
- Email.
- Message.
- Product reference.
- Current URL.
- Lead Source.
- Status.
- Assigned To.
- Created At.

Status:

- New.
- Contacted.
- Consulting.
- Quoted.
- Won.
- Lost.

---

## Banner

- Title.
- Desktop Image.
- Mobile Image.
- Description.
- CTA Label.
- CTA URL.
- Position.
- Active.
- Start Date.
- End Date.

---

# 14. Migration dữ liệu website cũ

Không nhập liệu thủ công lại toàn bộ.

Cần:

1. Audit website cũ.
2. Inventory:
   - Products.
   - Categories.
   - Images.
   - Articles.
   - Pages.
   - Brands.
   - Dealers / branches.
3. Mapping field cũ → Drupal 11.
4. Import.
5. Validate.
6. Redirect URL cũ → URL mới.

Yêu cầu:

- Không mất product.
- Không mất image.
- Không mất metadata quan trọng.
- Không phá URL SEO nếu tránh được.
- URL thay đổi phải có 301 redirect.

Cần lưu migration logs.

---

# 15. UX/UI Design Direction

Phong cách:

- Premium.
- Modern.
- Minimal.
- Architectural.
- Clean.
- Product-first.

Không dùng design quá màu mè.

Sản phẩm khóa phải là nhân vật chính.

## Color

Ưu tiên tham khảo brand hiện tại.

Có thể dùng:

- Dark neutral.
- White.
- Metallic accents.
- Brass / gold tone làm accent nếu phù hợp brand.

Không lạm dụng gradient.

## Typography

- Hiện đại.
- Dễ đọc tiếng Việt.
- Heading rõ.
- Body text thoáng.

## Images

- Large product images.
- High resolution.
- Consistent aspect ratio.
- Web optimized.

## Cards

Product card gồm:

- Image.
- Category.
- Product Name.
- Key attribute.
- Price / Liên hệ.
- CTA.

Không nhồi quá nhiều thông số trên card.

---

# 16. Homepage Wireframe

```text
+--------------------------------------------------+
| HEADER                                           |
| Logo | Sản phẩm | Dự án | Tin tức | Đại lý     |
| Search | Zalo | Hotline                          |
+--------------------------------------------------+

+--------------------------------------------------+
| HERO                                             |
| Hình sản phẩm / không gian nội thất              |
| Headline                                         |
| Description                                      |
| [Khám phá sản phẩm] [Nhận tư vấn]                |
+--------------------------------------------------+

+--------------------------------------------------+
| PRODUCT CATEGORIES                               |
| Smart | Fingerprint | Hotel | Brass | Accessory  |
+--------------------------------------------------+

+--------------------------------------------------+
| FEATURED PRODUCTS                                |
+--------------------------------------------------+

+--------------------------------------------------+
| SOLUTIONS BY NEED                                |
| Biệt thự | Căn hộ | Khách sạn | Văn phòng       |
+--------------------------------------------------+

+--------------------------------------------------+
| WHY KEYBOLTS                                     |
+--------------------------------------------------+

+--------------------------------------------------+
| PROJECTS                                         |
+--------------------------------------------------+

+--------------------------------------------------+
| NEW PRODUCTS / TECHNOLOGY                        |
+--------------------------------------------------+

+--------------------------------------------------+
| BUYING GUIDES / NEWS                             |
+--------------------------------------------------+

+--------------------------------------------------+
| DEALER / DISTRIBUTION                            |
+--------------------------------------------------+

+--------------------------------------------------+
| CONSULTATION CTA                                 |
+--------------------------------------------------+

+--------------------------------------------------+
| FOOTER                                           |
+--------------------------------------------------+
```

---

# 17. Product Detail Wireframe

```text
Breadcrumb

+----------------------+----------------------------+
|                      | Product Name               |
|   Large Gallery      | Product Code               |
|                      | Status                     |
|                      | Price / Liên hệ            |
|                      |                            |
| thumbnails           | [Nhận báo giá]             |
|                      | [Zalo] [Gọi ngay]           |
+----------------------+----------------------------+

USP / Warranty / Shipping

Key Specifications

Tabs / Sections:
- Tổng quan
- Tính năng
- Thông số
- Hướng dẫn lắp
- Bảo hành

Real Product Images

Video

Related Products

Related Articles

Lead Form
```

---

# 18. Mobile UX

Mobile First.

Sticky CTA bottom:

```text
[ Gọi ] [ Zalo ] [ Nhận báo giá ]
```

Yêu cầu:

- Menu mobile đơn giản.
- Search dễ mở.
- Filter dùng drawer.
- Product gallery swipe.
- CTA luôn dễ thấy.
- Không có popup che toàn màn hình ngay khi user vừa vào.
- Touch target đủ lớn.

---

# 19. Search

Search tối thiểu theo:

- Product Name.
- Product Code.
- Category.
- Brand.
- Key specifications.

Có autocomplete nếu phù hợp.

Ví dụ:

User nhập:

`P80`

→ Keybolts P80.

User nhập:

`khóa vân tay cửa gỗ`

→ trả về product phù hợp.

Có thể nâng cấp sau bằng semantic search / AI search.

---

# 20. Lead Generation

CTA xuất hiện ở:

- Header.
- Product.
- Category.
- Article.
- Contact.
- Mobile sticky bar.

Form tối giản:

- Họ tên.
- Số điện thoại.
- Email optional.
- Nội dung.
- Product auto-filled khi submit từ product page.

Khi submit:

1. Validate.
2. Save vào Drupal.
3. Gửi email notification.
4. Có thể gửi Zalo/CRM ở phase sau.

---

# 21. API Design

Ưu tiên sử dụng Drupal JSON:API cho CRUD/content đọc phổ biến.

Custom endpoint chỉ khi:

- Business logic.
- Complex search.
- Lead submit.
- Authentication.
- Aggregated frontend response.
- Performance requirement đặc biệt.

Không tạo custom API nếu JSON:API đã đáp ứng.

API cần:

- Pagination.
- Filter.
- Sort.
- Include relationships.
- Cache.
- Access control.

---

# 22. Code Structure Frontend

Khuyến nghị:

```text
frontend/
├── assets/
├── components/
│   ├── common/
│   ├── product/
│   ├── article/
│   ├── navigation/
│   └── forms/
├── composables/
├── layouts/
├── middleware/
├── pages/
├── plugins/
├── public/
├── server/
├── services/
│   ├── drupal/
│   ├── product/
│   ├── article/
│   └── lead/
├── stores/
├── types/
├── utils/
├── nuxt.config.ts
└── package.json
```

Không gọi API trực tiếp rải rác trong component.

Tạo service layer.

---

# 23. Code Quality

Bắt buộc:

- TypeScript nếu dùng Nuxt/Vue project hiện đại.
- ESLint.
- Prettier.
- Environment variables.
- Không hard-code API URL.
- Reusable components.
- Clear naming.
- Error handling.
- Loading state.
- Empty state.
- 404.
- Error page.

Không viết một component hàng nghìn dòng.

---

# 24. Environment

Tách:

- local.
- staging.
- production.

Variables ví dụ:

```env
DRUPAL_BASE_URL=
DRUPAL_API_BASE_URL=
SITE_URL=
```

Không commit secret.

---

# 25. Docker / Local Development

Khuyến nghị:

Drupal:

- DDEV hoặc Docker.
- PHP version tương thích Drupal 11.
- MariaDB/MySQL hoặc PostgreSQL.
- Redis optional.

Frontend:

- Node LTS.
- Nuxt/Vue.
- Có Docker nếu cần deployment thống nhất.

Local flow cần rõ:

```bash
ddev start
```

và frontend:

```bash
npm install
npm run dev
```

hoặc command tương đương theo package manager đã chọn.

---

# 26. Deployment

Production đề xuất:

```text
Internet
   |
 CDN / DNS
   |
 Reverse Proxy / Nginx
   |
 +-----------------------------+
 |                             |
 v                             v
Nuxt Frontend              Drupal 11
                               |
                               v
                           Database
                               |
                               v
                            Redis
```

Có:

- SSL.
- Backup.
- Monitoring.
- Log rotation.
- Health checks.

---

# 27. Monitoring

Theo dõi:

- Uptime.
- Response time.
- 5xx errors.
- API errors.
- Disk.
- CPU.
- RAM.
- Database.
- SSL expiration.

Có thể dùng:

- Uptime Kuma.
- Grafana.
- Prometheus.
- Sentry.
- Hoặc công cụ tương đương.

Không bắt buộc triển khai tất cả ngay phase 1.

---

# 28. Analytics

Tích hợp:

- Google Analytics 4.
- Google Search Console.
- Google Tag Manager nếu cần.

Track:

- Product view.
- Search.
- Filter.
- Click hotline.
- Click Zalo.
- Submit lead.
- Dealer registration.
- CTA click.

Mục tiêu:

Biết website có tạo lead hay không.

---

# 29. Các phase triển khai

## Phase 1 — Foundation

- Audit site cũ.
- Sitemap.
- Content model.
- UX.
- UI Design.
- Drupal 11 setup.
- Vue/Nuxt setup.
- Product.
- Category.
- Article.
- Page.
- Basic SEO.

## Phase 2 — Migration

- Crawl/export dữ liệu.
- Clean dữ liệu.
- Import.
- Images.
- URL redirects.
- QA.

## Phase 3 — Conversion

- Search.
- Filter.
- Lead form.
- Zalo.
- Hotline.
- CTA.
- Dealer.

## Phase 4 — Optimization

- Performance.
- Core Web Vitals.
- SEO.
- Structured Data.
- GEO content structure.
- Analytics.

## Future

- CRM.
- E-commerce.
- Payment.
- Dealer portal.
- Mobile app.
- AI consultant.
- Semantic search.

---

# 30. Deliverables Claude cần tạo

Từ tài liệu này, Claude có thể được yêu cầu tạo một hoặc nhiều output sau.

## 30.1. Proposal gửi khách

Viết ngắn gọn theo:

1. Nỗi đau.
2. Ai hưởng lợi.
3. Giải pháp.
4. Công nghệ.
5. Tối ưu.
6. Khả năng mở rộng.
7. Kêu gọi triển khai.

Ngôn ngữ:

- Không kỹ thuật.
- Dễ hiểu.
- Không sáo rỗng.
- Có business value.

---

## 30.2. Sitemap

Xuất:

- Mermaid.
- Markdown tree.
- Danh sách page.

---

## 30.3. Wireframe

Tạo wireframe cho:

- Homepage.
- Product listing.
- Product detail.
- Article.
- Dealer.
- Contact.

---

## 30.4. UI Design Spec

Tạo:

- Design system.
- Spacing.
- Typography.
- Button.
- Card.
- Form.
- Header.
- Footer.
- Responsive rules.

---

## 30.5. Drupal Architecture

Tạo đầy đủ:

- Content types.
- Taxonomy.
- Fields.
- References.
- Roles.
- Permissions.
- Views.
- API exposure.
- Migration mapping.

---

## 30.6. Vue/Nuxt Architecture

Tạo:

- Routes.
- Layout.
- Components.
- Service layer.
- Types.
- API integration.
- SEO composables.
- Structured data.
- Error handling.

---

## 30.7. Coding

Khi được yêu cầu code:

- Viết code hoàn chỉnh.
- Không viết `...`.
- Không pseudo-code nếu yêu cầu implementation.
- Không bỏ phần import.
- Không bỏ validation.
- Không bỏ error handling.
- Không hard-code credentials.

---

# 31. Acceptance Criteria

Website được coi là đạt khi:

## UX

- Desktop đẹp.
- Tablet tốt.
- Mobile tốt.
- CTA rõ.
- Tìm product dễ.
- Filter dễ.
- Product detail dễ đọc.

## CMS

- Admin tự thêm sản phẩm.
- Admin tự thêm danh mục.
- Admin tự đăng article.
- Admin tự đổi banner.
- Admin tự quản lý dealer.
- Không cần developer cho content daily.

## SEO

- SSR hoạt động.
- Unique metadata.
- Sitemap.
- Canonical.
- Breadcrumb.
- Schema.
- Redirect site cũ.

## GEO

- Product data structured.
- Brand/entity rõ.
- FAQ/guide có cấu trúc.
- Nội dung answer-first.
- HTML crawlable.

## Security

- Drupal updated.
- Role permission đúng.
- Admin protected.
- Upload protected.
- HTTPS.

## Performance

- Images optimized.
- Lazy load.
- Cache.
- Không load JS/CSS thừa.
- Core Web Vitals được kiểm tra.

---

# 32. Thông điệp cuối gửi khách hàng

Không mô tả dự án theo kiểu:

> Thiết kế lại giao diện website Keybolts.

Mà mô tả:

> Giữ lại toàn bộ tài sản dữ liệu và nội dung Keybolts đang có, xây lại trên nền tảng Drupal 11 + Vue.js hiện đại, tối ưu trải nghiệm Mobile, tốc độ, bảo mật, SEO và GEO, đồng thời tạo nền tảng sẵn sàng mở rộng Sales, CRM, đại lý, thương mại điện tử và AI trong tương lai.

Thông điệp quan trọng:

> Không chỉ thay một website cũ bằng một website đẹp hơn.  
> Xây dựng lại Keybolts thành một nền tảng số phục vụ thương hiệu và bán hàng trong nhiều năm tiếp theo.

---

# 33. Prompt thực thi cho Claude

Khi bắt đầu thực hiện dự án, Claude cần:

1. Đọc toàn bộ file này.
2. Audit repository hiện tại nếu đã có source code.
3. Không tự ý thay đổi requirement cốt lõi.
4. Tạo plan theo phase.
5. Tạo sitemap.
6. Tạo content model Drupal.
7. Tạo frontend architecture Vue/Nuxt.
8. Tạo design direction.
9. Tạo migration strategy.
10. Implement theo từng module nhỏ.
11. Test responsive.
12. Test API.
13. Test SEO.
14. Test migration.
15. Test security cơ bản.
16. Document cách chạy local và deploy.

Ưu tiên:

Business Value  
→ UX  
→ Data Structure  
→ SEO/GEO  
→ Performance  
→ Security  
→ Code Quality.

Không over-engineering phase đầu.
