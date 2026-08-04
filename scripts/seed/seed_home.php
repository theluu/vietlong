<?php

/**
 * @file
 * Seeds the homepage with exactly what utils/homeContent.ts and the Hero
 * component had hard-coded, so moving the frontend across changes nothing.
 *
 * Run: ddev drush php:script scripts/seed/seed_home.php
 */

use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

/** Downloads a source image once and returns the managed file. */
function kbh_image(string $url): ?File {
  $name = preg_replace('/[^a-z0-9]+/i', '-', pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_FILENAME));
  $dir = 'public://home';
  $dest = "{$dir}/{$name}.webp";
  $fs = \Drupal::service('file_system');
  $fs->prepareDirectory($dir, \Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY);

  $existing = \Drupal::entityTypeManager()->getStorage('file')->loadByProperties(['uri' => $dest]);
  if ($existing) {
    return reset($existing);
  }
  $bytes = @file_get_contents($url);
  if ($bytes === FALSE) {
    echo "  ! download failed: {$url}\n";
    return NULL;
  }
  try {
    $img = new \Imagick();
    $img->readImageBlob($bytes);
    $img->setImageFormat('webp');
    $img->setImageCompressionQuality(82);
    if ($img->getImageWidth() > 1600) {
      $img->resizeImage(1600, 0, \Imagick::FILTER_LANCZOS, 1);
    }
    $img->stripImage();
    $blob = $img->getImageBlob();
    $img->destroy();
  }
  catch (\Throwable $e) {
    echo "  ! convert failed: {$url}\n";
    return NULL;
  }
  $uri = $fs->saveData($blob, $dest, \Drupal\Core\File\FileExists::Replace);
  $file = File::create(['uri' => $uri]);
  $file->setPermanent();
  $file->save();
  echo sprintf("  image %s: %dKB -> %dKB\n", $name, strlen($bytes) / 1024, strlen($blob) / 1024);
  return $file;
}

function kbh_paras(string $type, array $rows): array {
  $out = [];
  foreach ($rows as $values) {
    $p = Paragraph::create(['type' => $type] + $values);
    $p->save();
    $out[] = ['target_id' => $p->id(), 'target_revision_id' => $p->getRevisionId()];
  }
  return $out;
}

$storage = \Drupal::entityTypeManager()->getStorage('node');
$existing = $storage->loadByProperties(['type' => 'home_page']);
$node = $existing ? reset($existing) : Node::create(['type' => 'home_page']);

$node->setTitle('Trang chủ');

// Hero. The asterisks mark the word the design paints with a gold gradient.
$node->set('field_hero_eyebrow', 'Keybolts Collection');
$node->set('field_hero_title', "Khóa cửa\n*đẳng cấp* cho\ntừng công trình");
$node->set('field_hero_subtitle', 'Khóa đồng, khóa vân tay, khóa thẻ từ khách sạn và phụ kiện cửa nhập khẩu — tuyển chọn theo từng loại cửa, bảo hành 5–10 năm.');
$node->set('field_hero_cta1', ['uri' => 'internal:/san-pham', 'title' => 'Xem bộ sưu tập']);
$node->set('field_hero_cta2', ['uri' => 'internal:/lien-he', 'title' => 'Tư vấn miễn phí']);
$node->set('field_hero_stats', kbh_paras('hero_stat', [
  ['field_stat_value' => '15+', 'field_stat_label' => 'năm kinh nghiệm'],
  ['field_stat_value' => '05', 'field_stat_label' => 'showroom & kho'],
  ['field_stat_value' => '10', 'field_stat_label' => 'năm bảo hành'],
]));

$node->set('field_usps', kbh_paras('usp', [
  ['field_usp_title' => 'Bảo hành 5–10 năm', 'field_usp_desc' => 'Chính hãng, có phiếu bảo hành'],
  ['field_usp_title' => 'Giao hàng toàn quốc', 'field_usp_desc' => 'Hỗ trợ vận chuyển công trình'],
  ['field_usp_title' => 'Tư vấn theo loại cửa', 'field_usp_desc' => 'Kỹ thuật hỗ trợ theo hotline'],
  ['field_usp_title' => 'Đạt chuẩn CE-CFF', 'field_usp_desc' => 'Nhập khẩu, kiểm định đầy đủ'],
]));

$node->set('field_cat_eyebrow', 'Danh mục');
$node->set('field_cat_title', 'Khóa & phụ kiện theo nhóm');
$node->set('field_feat_eyebrow', 'Nổi bật');
$node->set('field_feat_title', 'Sản phẩm bán chạy');
$node->set('field_feat_tabs', kbh_paras('featured_tab', [
  ['field_tab_key' => 'dong', 'field_tab_label' => 'Khoá đồng nhập khẩu'],
  ['field_tab_key' => 'cremone', 'field_tab_label' => 'CREMONE chốt khoá'],
  ['field_tab_key' => 'hotel', 'field_tab_label' => 'Khoá khách sạn'],
  ['field_tab_key' => 'phukien', 'field_tab_label' => 'Phụ kiện khác'],
]));

$node->set('field_sol_eyebrow', 'Giải pháp');
$node->set('field_sol_title', 'Chọn theo loại công trình');
$solutions = [
  ['Biệt thự', 'Khóa cao cấp kết hợp bảo mật, thiết kế và trải nghiệm sử dụng cho không gian sống đẳng cấp.', ['Bảo mật cao', 'Thiết kế đẹp', 'Khóa cao cấp'], 'https://keybolts.com.vn/sites/default/files/_r3_0183_copy.jpg'],
  ['Căn hộ', 'Mở khóa nhanh gọn bằng vân tay hoặc mật khẩu, phù hợp căn hộ và nhà phố hiện đại.', ['Tiện dụng', 'Vân tay', 'Mật khẩu'], 'https://keybolts.com.vn/sites/default/files/6y7a5713_0.jpg'],
  ['Khách sạn', 'Khóa thẻ từ và giải pháp quản lý cửa cho khách sạn, căn hộ dịch vụ quy mô lớn.', ['Thẻ từ', 'Quản lý phòng', 'Số lượng lớn'], 'https://keybolts.com.vn/sites/default/files/6y7a5715.jpg'],
  ['Văn phòng', 'Kiểm soát ra vào và độ bền cao cho cửa văn phòng, toà nhà thương mại.', ['Kiểm soát ra vào', 'Độ bền cao'], NULL],
];
$solution_rows = [];
foreach ($solutions as [$title, $desc, $tags, $image_url]) {
  $row = [
    'field_sol_title' => $title,
    'field_sol_desc' => $desc,
    'field_sol_tags' => $tags,
    'field_sol_link' => ['uri' => 'internal:/san-pham', 'title' => 'Xem sản phẩm'],
  ];
  if ($image_url && ($file = kbh_image($image_url))) {
    $row['field_sol_image'] = ['target_id' => $file->id(), 'alt' => $title];
  }
  $solution_rows[] = $row;
}
$node->set('field_solutions', kbh_paras('solution', $solution_rows));

$node->set('field_tech_eyebrow', 'Công nghệ');
$node->set('field_tech_title', 'Khóa thông minh Keybolts');
$node->set('field_tech_desc', 'Mở khóa bằng vân tay, mã số hoặc thẻ từ, quản lý người dùng ngay trên điện thoại.');
$node->set('field_tech_features', [
  'Mở khóa bằng vân tay, mã số hoặc thẻ từ',
  'Điều khiển và giám sát qua ứng dụng điện thoại',
  'Cảnh báo khi có thao tác bất thường',
  'Lưu nhiều phương thức mở khóa cho từng thành viên',
]);
$node->set('field_tech_cta', ['uri' => 'internal:/san-pham', 'title' => 'Xem dòng khóa thông minh']);
if ($tech = kbh_image('https://keybolts.com.vn/sites/default/files/khoa_thong_minh_t28_0.png')) {
  $node->set('field_tech_image', ['target_id' => $tech->id(), 'alt' => 'Khóa thông minh Keybolts']);
}

$node->set('field_consult_eyebrow', 'Tư vấn');
$node->set('field_consult_title', 'Chưa biết chọn model nào?');
$node->set('field_consult_desc', 'Để lại thông tin — kỹ thuật Keybolts sẽ gọi lại và tư vấn theo đúng loại cửa, độ dày và nhu cầu sử dụng của bạn.');

$node->set('field_seo_title', 'Keybolts — Khóa cửa & phụ kiện cao cấp');
$node->set('field_seo_desc', 'Khóa đồng, khóa vân tay, khóa thông minh, khóa thẻ từ khách sạn và phụ kiện cửa nhập khẩu. Đạt chứng nhận CE-CFF, bảo hành 5–10 năm.');

$node->setPublished()->save();
echo ($existing ? 'updated' : 'created') . " home_page node {$node->id()}\n";
