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
  $node->setPublished()->save();
}

echo "news_page={$page->id()} articles=" . count(KB_NEWS_ARTICLES) . "\n";
