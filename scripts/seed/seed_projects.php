<?php

/** Seeds the Projects singleton and cards. Safe to run repeatedly. */

use Drupal\node\Entity\Node;

const KB_PROJECTS = [
  ['biet-thu', 'Biệt thự', 'Biệt thự đơn lập — bộ khóa đồng đồng bộ', 'Cửa đại sảnh dùng khóa đồng full size, cửa phòng dùng bộ thông phòng cùng hoàn thiện vàng PVD.', 'KB 1700 series', 'https://keybolts.com.vn/sites/default/files/6y7a5717_0.jpg'],
  ['khach-san', 'Khách sạn', 'Khách sạn 40 phòng — khóa thẻ từ', 'Khóa thẻ từ đồng bộ toàn bộ tầng phòng, quản lý thẻ tại lễ tân, thẻ master cho buồng phòng.', 'KB-9008 PVD', 'https://keybolts.com.vn/sites/default/files/6y7a5711_0.jpg'],
  ['can-ho', 'Căn hộ', 'Căn hộ cao tầng — khóa vân tay', 'Khóa vân tay cửa chính, mở bằng vân tay hoặc mã số, phù hợp cửa thép chống cháy.', 'Khóa vân tay', 'https://keybolts.com.vn/sites/default/files/6y7a5709_2.jpg'],
  ['van-phong', 'Văn phòng', 'Văn phòng cho thuê — kiểm soát ra vào', 'Khóa tay gạt bền cơ khí cho khu làm việc, khóa thẻ cho phòng máy chủ và kho tài liệu.', 'Khóa tay gạt', 'https://keybolts.com.vn/sites/default/files/6y7a5713_0.jpg'],
  ['biet-thu', 'Biệt thự', 'Nhà phố 4 tầng — cửa gỗ tự nhiên', 'Đồng bộ khóa và bản lề cho 12 cánh cửa gỗ, hoàn thiện rêu DSF theo phong cách tân cổ điển.', '9310-LC DSF', 'https://keybolts.com.vn/sites/default/files/_r3_0152_copy_0.jpg'],
  ['khach-san', 'Khách sạn', 'Homestay — cửa kính thủy lực', 'Khóa vân tay chuyên dụng cho cửa kính thủy lực khu vực lễ tân và lối vào chung.', 'KB 8150', 'https://keybolts.com.vn/sites/default/files/_r3_0183_copy.jpg'],
  ['can-ho', 'Căn hộ', 'Chung cư 18 tầng — bàn giao đồng bộ', 'Khóa thẻ từ cho toàn bộ căn hộ bàn giao, cấp thẻ theo danh sách cư dân.', 'KB9005 INOX', 'https://keybolts.com.vn/sites/default/files/kb_1700-xl-pvd.png'],
  ['van-phong', 'Văn phòng', 'Toà văn phòng — cửa kính mặt tiền', 'Khóa sàn và khóa vân tay cho cửa kính thủy lực khu vực sảnh chính.', 'KB 8150', 'https://keybolts.com.vn/sites/default/files/6y7a5715.jpg'],
  ['biet-thu', 'Biệt thự', 'Biệt thự song lập — cửa chính 2 cánh', 'Bộ khóa đại sảnh XL cho cửa 2 cánh, tay nắm dài đồng bộ hoàn thiện vàng PVD.', 'KB 1700-XL-PVD', 'https://keybolts.com.vn/sites/default/files/6y7a5711_0.jpg'],
  ['khach-san', 'Khách sạn', 'Resort nghỉ dưỡng — bungalow', 'Khóa thẻ từ chống ăn mòn cho khu vực ven biển, có phương án chìa cơ dự phòng.', 'KB-9008 PVD', 'https://keybolts.com.vn/sites/default/files/6y7a5709_2.jpg'],
  ['can-ho', 'Căn hộ', 'Căn hộ dịch vụ — cho thuê ngắn hạn', 'Khóa mã số đổi mã theo lượt khách, không cần bàn giao chìa vật lý.', 'Khóa mã số', 'https://keybolts.com.vn/sites/default/files/6y7a5713_0.jpg'],
  ['van-phong', 'Văn phòng', 'Nhà xưởng — cửa kho và văn phòng điều hành', 'Khóa chống trộm cho cửa kho, khóa tay gạt cho khu văn phòng điều hành.', 'Khóa chống trộm', 'https://keybolts.com.vn/sites/default/files/_r3_0152_copy_0.jpg'],
];

function kb_project_slug(string $title): string {
  $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', str_replace(['đ', 'Đ'], ['d', 'D'], $title));
  return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($ascii ?: $title)), '-');
}

$storage = \Drupal::entityTypeManager()->getStorage('node');
$pages = $storage->loadByProperties(['type' => 'projects_page']);
$page = $pages ? reset($pages) : Node::create(['type' => 'projects_page']);
$page->setTitle('Dự án & công trình');
$page->set('field_eyebrow', 'Ứng dụng thực tế');
$page->set('field_subtitle', 'Keybolts đồng hành cùng biệt thự, khách sạn, căn hộ và công trình thương mại — mỗi loại công trình cần một cấu hình khóa khác nhau.');
$page->setPublished()->save();

foreach (KB_PROJECTS as $i => [$key, $type, $title, $desc, $products, $image]) {
  $existing = $storage->loadByProperties(['type' => 'project', 'title' => $title]);
  $node = $existing ? reset($existing) : Node::create(['type' => 'project']);
  $node->setTitle($title);
  $node->set('field_project_type_key', $key);
  $node->set('field_project_type', $type);
  $node->set('field_project_desc', $desc);
  $node->set('field_project_products', $products);
  $node->set('field_project_image_url', $image);
  $node->set('field_project_slug', kb_project_slug($title));
  $node->set('field_sort_order', $i + 1);
  $node->setPublished()->save();
}

echo "projects_page={$page->id()} projects=" . count(KB_PROJECTS) . "\n";
