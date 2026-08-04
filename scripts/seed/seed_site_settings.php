<?php

/**
 * @file
 * Seeds the site-wide chrome with exactly what the Nuxt app had hard-coded,
 * so switching the frontend over changes nothing visible.
 *
 * Run: ddev drush php:script scripts/seed/seed_site_settings.php
 */

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

/** Creates paragraphs and returns reference values. */
function kbs_paras(string $type, array $rows): array {
  $out = [];
  foreach ($rows as $values) {
    $p = Paragraph::create(['type' => $type] + $values);
    $p->save();
    $out[] = ['target_id' => $p->id(), 'target_revision_id' => $p->getRevisionId()];
  }
  return $out;
}

$storage = \Drupal::entityTypeManager()->getStorage('node');
$existing = $storage->loadByProperties(['type' => 'site_settings']);
$node = $existing ? reset($existing) : Node::create(['type' => 'site_settings']);

$node->setTitle('Cấu hình chung');

$node->set('field_topbar_text', 'Nhà nhập khẩu & phân phối khóa cửa cao cấp — Bắc Ninh · Phú Thọ · Vĩnh Phúc');
$node->set('field_topbar_badges', ['Chứng nhận CE-CFF', 'Bảo hành 5–10 năm']);

$node->set('field_hotline', '1900 9018');
$node->set('field_hotline_tel', '19009018');
$node->set('field_email', 'khoacuavietlong@gmail.com');
$node->set('field_company_name', 'Công ty TNHH XNK Khóa Cửa Việt Long');
$node->set('field_company_short', 'Khóa Cửa Việt Long');
$node->set('field_address', 'Khu phố Lê Hồng Phong, P. Đông Ngàn, TP. Từ Sơn, Bắc Ninh');
$node->set('field_working_hours', ['T2 – T7: 8:00 – 17:30']);

$node->set('field_header_tagline', 'Khóa cửa & phụ kiện nhập khẩu');
$node->set('field_header_cta', ['uri' => 'internal:/lien-he', 'title' => 'Nhận tư vấn']);

$node->set('field_footer_desc', 'Nhập khẩu & phân phối khóa cửa, khóa thông minh và phụ kiện cửa cao cấp cho nhà ở, biệt thự, khách sạn và công trình trên toàn quốc.');
$node->set('field_copyright', '© 2026 Công ty TNHH XNK Khóa Cửa Việt Long — Keybolts. Bảo lưu mọi quyền.');

$node->set('field_seo_title', 'Keybolts — Khóa cửa & phụ kiện cao cấp');
$node->set('field_seo_desc', 'Nhà nhập khẩu và phân phối khóa cửa, khóa thông minh, khóa khách sạn và phụ kiện cửa cao cấp. Chứng nhận CE-CFF, bảo hành 5–10 năm.');

$node->set('field_social', kbs_paras('social_link', [
  ['field_social_label' => 'Facebook', 'field_social_icon' => 'facebook', 'field_social_url' => ['uri' => 'https://www.facebook.com/khoacuacaocapvietlong/', 'title' => 'Facebook']],
  ['field_social_label' => 'YouTube', 'field_social_icon' => 'youtube', 'field_social_url' => ['uri' => 'https://www.youtube.com', 'title' => 'YouTube']],
  ['field_social_label' => 'Zalo', 'field_social_icon' => 'zalo', 'field_social_url' => ['uri' => 'https://zalo.me/19009018', 'title' => 'Zalo']],
]));

$columns = [
  ['Sản phẩm', [
    ['Khóa thông minh', '/san-pham'],
    ['Khóa vân tay', '/san-pham'],
    ['Khóa khách sạn', '/san-pham'],
    ['Khóa đồng nhập khẩu', '/san-pham'],
    ['Bản lề & phụ kiện', '/san-pham'],
    ['Chốt cửa Cremone', '/san-pham'],
  ]],
  ['Hỗ trợ', [
    ['Cam kết chất lượng', '/chinh-sach'],
    ['Chính sách bảo hành', '/chinh-sach'],
    ['Giao nhận hàng', '/chinh-sach'],
    ['Đổi trả hàng', '/chinh-sach'],
    ['Câu hỏi thường gặp', '/chinh-sach'],
    ['Trở thành đại lý', '/dai-ly'],
  ]],
];
$column_values = [];
foreach ($columns as [$title, $links]) {
  $link_rows = [];
  foreach ($links as [$label, $path]) {
    $link_rows[] = [
      'field_flink_label' => $label,
      'field_flink_url' => ['uri' => 'internal:' . $path, 'title' => $label],
    ];
  }
  $col = Paragraph::create([
    'type' => 'footer_column',
    'field_fcol_title' => $title,
    'field_fcol_links' => kbs_paras('footer_link', $link_rows),
  ]);
  $col->save();
  $column_values[] = ['target_id' => $col->id(), 'target_revision_id' => $col->getRevisionId()];
}
$node->set('field_footer_columns', $column_values);

$node->setPublished()->save();
echo ($existing ? 'updated' : 'created') . " site_settings node {$node->id()}\n";

/**
 * The main menu. Drupal's own menu system rather than a bespoke field: an
 * editor already knows /admin/structure/menu, it supports sub-items and
 * reordering by drag, and it survives a rename of any of these pages.
 */
$menu_storage = \Drupal::entityTypeManager()->getStorage('menu_link_content');
foreach ($menu_storage->loadByProperties(['menu_name' => 'main']) as $link) {
  $link->delete();
}
// Drupal ships a static "Home" link that is not an entity, so deleting
// menu_link_content leaves it behind. The site's logo already goes home.
\Drupal::service('plugin.manager.menu.link')->updateDefinition('standard.front_page', ['enabled' => 0]);
$items = [
  ['Sản phẩm', '/san-pham'],
  ['Giới thiệu', '/gioi-thieu'],
  ['Dự án', '/du-an'],
  ['Tin tức', '/tin-tuc'],
  ['Đại lý', '/dai-ly'],
  ['Liên hệ', '/lien-he'],
];
foreach ($items as $i => [$title, $path]) {
  $menu_storage->create([
    'title' => $title,
    'link' => ['uri' => 'internal:' . $path],
    'menu_name' => 'main',
    'weight' => $i,
    'expanded' => TRUE,
  ])->save();
}
echo 'main menu: ' . count($items) . " links\n";
