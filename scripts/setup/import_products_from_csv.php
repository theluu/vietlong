<?php

/**
 * @file
 * Nhập catalogue sản phẩm từ docs/products.csv.
 *
 * CSV có 192 mã, gấp bảy lần 26 sản phẩm đang có, nên đây là catalogue thật
 * chứ không phải dữ liệu mẫu. Đổi lại nó thiếu gần hết phần mô tả: không thông
 * số, không FAQ, không biến thể, và 152/192 mã không có ảnh nào.
 *
 * Ảnh được TẢI VỀ Drupal chứ không trỏ sang keybolts.com.vn. Trỏ sang đó là
 * đúng thứ vừa mất cả buổi để gỡ: ảnh gốc máy ảnh chưa xử lý, nằm trên một
 * server không ai kiểm soát, và image style không chạm được vào.
 *
 * Danh mục CSV gộp vào 8 danh mục sẵn có của site chứ không tạo thêm — 8 mục
 * đó là kiến trúc thông tin trang chủ và menu đang dựa vào.
 *
 * Safe to run repeatedly: khoá theo mã sản phẩm, mã nào đã có thì cập nhật.
 *
 * Run: ddev drush php:script scripts/setup/import_products_from_csv.php
 */

use Drupal\file\Entity\File;
use Drupal\taxonomy\Entity\Term;

/** Danh mục trong CSV => tên term trong vocabulary product_category. */
const KB_CATEGORY_MAP = [
  'BẢN LỀ' => 'Bản lề & tay co',
  'CHỐT CỬA CREMONE ĐỒNG' => 'Chốt Cremone',
  'CHỐT CỬA CREMONE INOX' => 'Chốt Cremone',
  'KHÓA THÔNG MINH' => 'Khóa thông minh',
  'KHÓA VÂN TAY' => 'Khóa vân tay',
  'KHOÁ THẺ TỪ KHÁCH SẠN' => 'Khóa khách sạn',
  'KHÓA TAY GẠT ĐỒNG ĐẠI SẢNH' => 'Khóa tay gạt',
  'KHÓA TAY GẠT ĐỒNG THÔNG PHÒNG' => 'Khóa tay gạt',
  'KHÓA TAY GẠT ĐỒNG ĐẠI' => 'Khóa tay gạt',
  'KHÓA TAY GẠT ĐỒNG TRUNG' => 'Khóa tay gạt',
  'KHÓA TAY GẠT INOX' => 'Khóa tay gạt',
  'KHOÁ ĐỒNG ĐẠI SẢNH FULL SIZE' => 'Khóa đồng',
  'KHÓA ÂM' => 'Khóa đồng',
  'PHỤ KIỆN CỬA GỖ' => 'Phụ kiện cửa',
  'PHỤ KIỆN TỦ-BẾP' => 'Phụ kiện cửa',
];

$etm = \Drupal::entityTypeManager();
$nodeStorage = $etm->getStorage('node');
$termStorage = $etm->getStorage('taxonomy_term');
$fileSystem = \Drupal::service('file_system');

/** Term theo tên, tạo mới nếu chưa có. Nhớ lại trong phiên để khỏi tra lặp. */
$terms = [];
$term = function (string $vocabulary, string $name) use (&$terms, $termStorage): ?int {
  $name = trim($name);
  if ($name === '') {
    return NULL;
  }
  $key = "$vocabulary:$name";
  if (isset($terms[$key])) {
    return $terms[$key];
  }
  $found = $termStorage->loadByProperties(['vid' => $vocabulary, 'name' => $name]);
  $entity = $found ? reset($found) : Term::create(['vid' => $vocabulary, 'name' => $name]);
  if (!$found) {
    $entity->save();
  }
  return $terms[$key] = (int) $entity->id();
};

/**
 * Tải một ảnh về public:// và trả fid.
 *
 * Cùng một URL xuất hiện ở nhiều dòng, và chạy lại script không nên tải lại —
 * nên tra managed file theo tên đích trước khi đụng tới mạng.
 */
$downloaded = [];
$image = function (string $url) use (&$downloaded, $fileSystem, $etm): ?int {
  $url = trim($url);
  if ($url === '') {
    return NULL;
  }
  if (isset($downloaded[$url])) {
    return $downloaded[$url];
  }

  $name = basename(parse_url($url, PHP_URL_PATH) ?? '');
  if ($name === '') {
    return NULL;
  }
  $uri = 'public://catalogue/' . $name;

  $fileStorage = $etm->getStorage('file');
  $ids = $fileStorage->getQuery()->accessCheck(FALSE)->condition('uri', $uri)->range(0, 1)->execute();
  if ($ids) {
    return $downloaded[$url] = (int) reset($ids);
  }

  $bytes = @file_get_contents($url);
  if ($bytes === FALSE || strlen($bytes) < 512) {
    echo "  ! không tải được $url\n";
    return $downloaded[$url] = NULL;
  }
  // prepareDirectory() takes its argument by reference, so it needs a variable.
  $dir = 'public://catalogue';
  $fileSystem->prepareDirectory($dir, \Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY);
  $fileSystem->saveData($bytes, $uri, \Drupal\Core\File\FileExists::Replace);
  $file = File::create(['uri' => $uri, 'status' => 1]);
  $file->save();
  return $downloaded[$url] = (int) $file->id();
};

$path = dirname(__DIR__, 2) . '/docs/products.csv';
$handle = fopen($path, 'r');
$header = fgetcsv($handle);
// Excel để lại BOM ở ô đầu, và nó biến 'stt' thành một khoá không ai tra được.
$header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);

$created = 0;
$updated = 0;
$noImage = 0;
while (($row = fgetcsv($handle)) !== FALSE) {
  $r = array_combine($header, array_pad($row, count($header), ''));
  $code = trim($r['ma_san_pham']);
  $title = trim($r['ten_san_pham']);
  if ($title === '') {
    continue;
  }

  // Mã sản phẩm là khoá tự nhiên; vài dòng để trống thì lùi về tiêu đề.
  $existing = $code !== ''
    ? $nodeStorage->loadByProperties(['type' => 'product', 'field_product_code' => $code])
    : $nodeStorage->loadByProperties(['type' => 'product', 'title' => $title]);
  $node = $existing ? reset($existing) : $nodeStorage->create(['type' => 'product']);

  $category = KB_CATEGORY_MAP[trim($r['danh_muc'])] ?? NULL;
  if ($category === NULL && trim($r['danh_muc']) !== '') {
    throw new \RuntimeException("Danh mục lạ: {$r['danh_muc']} — bổ sung vào KB_CATEGORY_MAP rồi chạy lại.");
  }

  $node->set('title', $title);
  $node->set('status', 1);
  $node->set('field_product_code', $code);
  $node->set('field_sort_order', (int) ($r['stt'] ?: 99));
  // Bảng giá không nằm trên website — mọi dòng CSV đều ghi "Liên hệ".
  $node->set('field_contact_price', TRUE);
  $node->set('field_short_desc', trim($r['meta_description_seo']));
  $node->set('field_certification', trim($r['chung_nhan_chat_luong']));
  if ($category) {
    $node->set('field_category', $term('product_category', $category));
  }
  if ($finish = $term('finish', $r['mau_sac'])) {
    $node->set('field_finish', $finish);
  }

  $images = [];
  foreach (explode(',', $r['image_urls']) as $url) {
    if ($fid = $image($url)) {
      $images[] = ['target_id' => $fid, 'alt' => $title];
    }
  }
  if ($images) {
    $node->set('field_images', $images);
  }
  else {
    $noImage++;
  }

  $node->save();
  $existing ? $updated++ : $created++;
}
fclose($handle);

printf("Tạo %d, cập nhật %d. %d sản phẩm không có ảnh trong CSV.%s", $created, $updated, $noImage, PHP_EOL);
