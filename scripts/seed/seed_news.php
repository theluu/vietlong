<?php

/**
 * @file
 * Seeds the Tin tức singleton and listing cards. Safe to run repeatedly.
 */

use Drupal\node\Entity\Node;

const KB_NEWS_ARTICLES = [
  ['guide', 'Buying Guide', 'Cách chọn khóa theo độ dày cửa', 'Đo đúng độ dày cánh trước khi chọn khóa — hướng dẫn đo và bảng quy đổi size.', '6 phút đọc', 'https://keybolts.com.vn/sites/default/files/6y7a5711_0.jpg'],
  ['compare', 'So sánh', 'Khóa thẻ từ và khóa vân tay khác nhau thế nào?', 'Hai công nghệ, hai bài toán khác nhau: quản lý phòng khách sạn và tiện dụng cho gia đình.', '5 phút đọc', 'https://keybolts.com.vn/sites/default/files/6y7a5709_2.jpg'],
  ['guide', 'Buying Guide', 'Khóa thông minh cho biệt thự cần tính năng gì?', 'Những tiêu chí quan trọng khi chọn khóa cho cửa chính biệt thự và cửa phụ.', '7 phút đọc', 'https://keybolts.com.vn/sites/default/files/6y7a5713_0.jpg'],
  ['howto', 'Hướng dẫn', 'Cách bảo dưỡng khóa đồng mạ PVD', 'Vệ sinh và tra dầu đúng cách để giữ màu và độ êm của bộ khóa trong nhiều năm.', '4 phút đọc', 'https://keybolts.com.vn/sites/default/files/_r3_0152_copy_0.jpg'],
  ['howto', 'Hướng dẫn', 'Khóa bị kẹt chìa — xử lý thế nào?', 'Các nguyên nhân thường gặp và cách xử lý an toàn trước khi gọi kỹ thuật.', '4 phút đọc', 'https://keybolts.com.vn/sites/default/files/_r3_0183_copy.jpg'],
  ['faq', 'FAQ', 'Vì sao Keybolts không niêm yết giá trên website?', 'Giá phụ thuộc size, hoàn thiện và số lượng — cách Keybolts báo giá minh bạch.', '3 phút đọc', 'https://keybolts.com.vn/sites/default/files/kb_1700-xl-pvd.png'],
  ['compare', 'So sánh', 'Khóa đồng và khóa inox — chọn loại nào?', 'Khác biệt về độ bền, cảm giác cầm và chi phí bảo trì giữa hai chất liệu phổ biến nhất.', '5 phút đọc', 'https://keybolts.com.vn/sites/default/files/6y7a5715.jpg'],
  ['guide', 'Buying Guide', 'Chọn bản lề đúng tải trọng cho cửa gỗ', 'Số lượng bản lề và kích thước theo cân nặng cánh cửa — tránh xệ cánh sau vài tháng.', '5 phút đọc', 'https://keybolts.com.vn/sites/default/files/6y7a5717_0.jpg'],
  ['howto', 'Hướng dẫn', 'Cài và xóa vân tay cho thành viên gia đình', 'Quy trình thêm, đổi và xóa vân tay khi có khách ở dài ngày hoặc thay người giúp việc.', '4 phút đọc', 'https://keybolts.com.vn/sites/default/files/6y7a5711_0.jpg'],
  ['faq', 'FAQ', 'Khóa điện tử hết pin thì mở cửa thế nào?', 'Ba phương án dự phòng luôn nên chuẩn bị sẵn cho mọi khóa điện tử.', '3 phút đọc', 'https://keybolts.com.vn/sites/default/files/6y7a5709_2.jpg'],
  ['compare', 'So sánh', 'Chốt Cremone và chốt âm — dùng cho cửa nào?', 'Khi nào nên dùng cremone cho cửa sổ, cửa đi và cửa hai cánh tân cổ điển.', '6 phút đọc', 'https://keybolts.com.vn/sites/default/files/_r3_0183_copy.jpg'],
  ['guide', 'Buying Guide', 'Chọn hoàn thiện khóa hợp phong cách nội thất', 'Vàng PVD, rêu DSF hay inox — cách phối hoàn thiện khóa với tay nắm và bản lề.', '6 phút đọc', 'https://keybolts.com.vn/sites/default/files/_r3_0152_copy_0.jpg'],
];

function kb_news_slug(string $title): string {
  $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', str_replace(['đ', 'Đ'], ['d', 'D'], $title));
  return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($ascii ?: $title)), '-');
}

/** Writes the detail body onto an article, clearing anything not supplied. */
function kb_news_detail(\Drupal\node\NodeInterface $node, array $detail): void {
  $node->set('field_article_quick_answer', $detail['quick'] ?? '');
  foreach ([
    'field_article_sections' => 'sections',
    'field_article_compare' => 'compare',
    'field_article_faqs' => 'faqs',
    'field_article_products' => 'products',
  ] as $field => $key) {
    $value = $detail[$key] ?? [];
    $node->set($field, $value ? json_encode($value, JSON_UNESCAPED_UNICODE) : '');
  }
}

$details = require __DIR__ . '/article_details.php';

$storage = \Drupal::entityTypeManager()->getStorage('node');
$pages = $storage->loadByProperties(['type' => 'news_page']);
$page = $pages ? reset($pages) : Node::create(['type' => 'news_page']);
$page->setTitle('Tin tức & kiến thức');
$page->set('field_eyebrow', 'Thư viện');
$page->set('field_subtitle', 'Hướng dẫn chọn khóa, so sánh công nghệ và kinh nghiệm sử dụng — kiến thức thực tế từ đội ngũ Keybolts.');
$page->setPublished()->save();

foreach (KB_NEWS_ARTICLES as $i => [$key, $category, $title, $summary, $read_time, $image]) {
  $existing = $storage->loadByProperties(['type' => 'article', 'title' => $title]);
  $node = $existing ? reset($existing) : Node::create(['type' => 'article']);
  $node->setTitle($title);
  $node->set('field_article_category_key', $key);
  $node->set('field_article_category', $category);
  $node->set('field_article_summary', $summary);
  $node->set('field_article_read_time', $read_time);
  $node->set('field_article_image_url', $image);
  $node->set('field_article_slug', kb_news_slug($title));
  $node->set('field_sort_order', $i + 1);
  $node->set('field_article_author', 'Đội kỹ thuật Keybolts');
  $node->set('field_article_updated', 'Cập nhật 07/2026');
  kb_news_detail($node, $details[$title] ?? []);
  $node->setPublished()->save();
}

$featured_title = 'Nên chọn khóa vân tay nào cho cửa gỗ?';
$existing = $storage->loadByProperties(['type' => 'article', 'title' => $featured_title]);
$featured = $existing ? reset($existing) : Node::create(['type' => 'article']);
$featured->setTitle($featured_title);
$featured->set('field_article_category_key', 'guide');
$featured->set('field_article_category', 'Buying Guide');
$featured->set('field_article_summary', 'Hướng dẫn chọn khóa vân tay theo độ dày cánh, chiều mở và phương thức mở khóa dự phòng — viết cho cửa gỗ tự nhiên và cửa công nghiệp.');
$featured->set('field_article_read_time', '8 phút đọc');
$featured->set('field_article_image_url', 'https://keybolts.com.vn/sites/default/files/6y7a5717_0.jpg');
$featured->set('field_article_slug', kb_news_slug($featured_title));
$featured->set('field_sort_order', 99);
$featured->set('field_article_author', 'Đội kỹ thuật Keybolts');
$featured->set('field_article_updated', 'Cập nhật 07/2026');
$featured->set('field_article_quick_answer', 'Với cửa gỗ dày 40–55mm, chọn khóa vân tay thân dài, mặt khóa liền, có sẵn cả mã số và chìa cơ dự phòng. Đo độ dày cánh và xác định chiều mở trước khi đặt hàng — đây là hai thông số quyết định lắp được hay không.');
$featured->set('field_article_sections', json_encode([
  ['id' => 'do-day', 'title' => '1. Đo độ dày cánh cửa', 'paragraphs' => ['Đây là thông số quyết định. Đo tại mép cánh, không đo tại khuôn bao. Cửa gỗ tự nhiên thường 45–55mm, cửa công nghiệp 35–45mm. Chọn sai độ dày thì thân khóa không ăn khớp, phải khoét lại cánh — tốn thời gian và làm yếu kết cấu cửa.'], 'list' => ['Dùng thước kẹp hoặc thước lá, đo ở ba vị trí trên cùng một cánh', 'Lấy số lớn nhất làm chuẩn khi đặt hàng', 'Ghi rõ độ dày khi gửi yêu cầu báo giá'], 'note' => 'Nếu cửa của bạn nằm ngoài khoảng 40–55mm, hãy gọi hotline — Keybolts có model thân ngắn và thân dài cho các trường hợp đặc biệt.'],
  ['id' => 'chieu-mo', 'title' => '2. Xác định chiều mở cánh', 'paragraphs' => ['Đứng ngoài nhìn vào: bản lề bên trái là cửa trái, bên phải là cửa phải. Đa số khóa vân tay Keybolts đảo chiều được, nhưng cần khai báo trước khi giao để kỹ thuật cài sẵn, tránh phải tháo ra chỉnh lại sau khi đã lắp.']],
  ['id' => 'phuong-thuc', 'title' => '3. Chọn phương thức mở khóa', 'paragraphs' => ['Vân tay là chính, nhưng luôn cần phương án dự phòng: mã số, thẻ từ và chìa cơ. Với nhà có người lớn tuổi, ưu tiên bộ có cả thẻ từ vì vân tay người cao tuổi đôi khi khó nhận do da tay mỏng và khô.', 'Với nhà cho thuê hoặc căn hộ dịch vụ, mã số dùng một lần là lựa chọn tiện nhất — không phải bàn giao chìa vật lý cho từng lượt khách.']],
  ['id' => 'lap-dat', 'title' => '4. Lưu ý khi lắp trên cửa gỗ', 'paragraphs' => ['Cửa gỗ co ngót theo mùa. Sau 3–6 tháng nên kiểm tra lại độ khít của chốt; nếu chốt bị cấn, chỉnh bản lề trước khi chỉnh khóa. Việc khoét cửa nên do thợ có kinh nghiệm thực hiện, vì lỗ khoét sai không thể hoàn nguyên.']],
  ['id' => 'bao-hanh', 'title' => '5. Kiểm tra bảo hành và linh kiện thay thế', 'paragraphs' => ['Khóa điện tử có pin và bo mạch — hãy chọn nhà phân phối có sẵn linh kiện thay thế. Sản phẩm Keybolts bảo hành 5–10 năm tùy dòng, hỗ trợ thay bo và cụm vân tay tại 5 cơ sở miền Bắc.']],
], JSON_UNESCAPED_UNICODE));
$featured->set('field_article_compare', json_encode([
  ['door' => 'Gỗ tự nhiên', 'thickness' => '45–55 mm', 'lock' => 'Vân tay thân dài', 'backup' => 'Chìa cơ'],
  ['door' => 'Gỗ công nghiệp', 'thickness' => '35–45 mm', 'lock' => 'Vân tay thân tiêu chuẩn', 'backup' => 'Mã số'],
  ['door' => 'Cửa thép chống cháy', 'thickness' => '50–70 mm', 'lock' => 'Vân tay chuyên dụng', 'backup' => 'Thẻ từ + chìa'],
  ['door' => 'Cửa kính thủy lực', 'thickness' => '10–12 mm', 'lock' => 'Khóa kính KB 8150', 'backup' => 'Chìa cơ'],
], JSON_UNESCAPED_UNICODE));
$featured->set('field_article_faqs', json_encode([
  ['question' => 'Khóa vân tay có mở được khi mất điện không?', 'answer' => 'Có. Khóa dùng pin riêng, không phụ thuộc điện lưới. Khi pin yếu, khóa cảnh báo trước nhiều ngày; nếu hết pin hoàn toàn vẫn mở được bằng chìa cơ hoặc cấp nguồn tạm qua cổng sạc dự phòng.'],
  ['question' => 'Vân tay trẻ nhỏ có đăng ký được không?', 'answer' => 'Được với trẻ từ khoảng 6 tuổi trở lên. Trẻ nhỏ hơn có vân tay chưa rõ nét, nên dùng thẻ từ hoặc mã số riêng cho bé.'],
  ['question' => 'Cửa gỗ đã lắp khóa cũ thì thay được không?', 'answer' => 'Phần lớn trường hợp thay được nếu lỗ khoét cũ nhỏ hơn hoặc bằng thân khóa mới. Gửi ảnh lỗ khoét hiện tại qua Zalo để kỹ thuật xác nhận trước khi đặt hàng.'],
  ['question' => 'Bảo hành có bao gồm cụm vân tay không?', 'answer' => 'Có. Cụm cảm biến vân tay và bo mạch được bảo hành 2 năm, phần cơ khí 5 năm; Keybolts có sẵn linh kiện thay thế tại kho.'],
], JSON_UNESCAPED_UNICODE));
$featured->set('field_article_products', json_encode([
  'khoa-van-tay-cua-go',
  'khoa-van-tay-cua-kinh-thuy-luc',
  'khoa-thong-minh-k42',
], JSON_UNESCAPED_UNICODE));
$featured->setPublished()->save();

echo "news_page={$page->id()} articles=" . (count(KB_NEWS_ARTICLES) + 1) . "\n";
