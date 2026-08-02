<?php

/**
 * @file
 * Seeds the single Giới thiệu node. Safe to run repeatedly.
 *
 * Run: ddev drush php:script scripts/seed/seed_about.php
 */

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Builds paragraphs from rows, replacing whatever was there.
 */
function kbp_paras(string $type, array $rows, array $map): array {
  $out = [];
  foreach ($rows as $row) {
    $values = ['type' => $type];
    foreach ($map as $i => $field) {
      $values[$field] = $row[$i];
    }
    $p = Paragraph::create($values);
    $p->save();
    $out[] = ['target_id' => $p->id(), 'target_revision_id' => $p->getRevisionId()];
  }
  return $out;
}

$storage = \Drupal::entityTypeManager()->getStorage('node');
$existing = $storage->loadByProperties(['type' => 'about_page']);
$node = $existing ? reset($existing) : Node::create(['type' => 'about_page']);

$node->setTitle('Nhà nhập khẩu khóa cửa cao cấp phục vụ công trình toàn quốc');
$node->set('field_eyebrow', 'Về Keybolts');
$node->set('field_subtitle', 'Từ 2014, Keybolts — thương hiệu của Công ty TNHH XNK Khóa Cửa Việt Long — nhập khẩu và phân phối khóa cửa, khóa thông minh, khóa khách sạn và phụ kiện cửa cao cấp. Hàng chính hãng, chứng nhận CE-CFF, bảo hành 5–10 năm, giao toàn quốc từ 5 kho miền Bắc.');
$node->set('field_hero_caption', 'Khóa đồng đại sảnh — hoàn thiện vàng bóng PVD');
$node->set('field_cta_primary', ['uri' => 'tel:19009018', 'title' => 'Gọi 1900 9018']);
$node->set('field_cta_secondary', ['uri' => 'internal:/san-pham', 'title' => 'Xem catalogue']);

$node->set('field_story_eyebrow', 'Câu chuyện');
$node->set('field_story_title', 'Ổ khóa không chỉ để an toàn');
$node->set('field_story_body', [
  'value' => '<p>Với Keybolts, bộ khóa trên cánh cửa là thứ khách nhìn thấy đầu tiên và chạm vào mỗi ngày — vừa là lớp bảo vệ, vừa là chi tiết nói lên gu thẩm mỹ của ngôi nhà. Vì vậy chúng tôi chỉ nhập những dòng khóa đạt cả hai: cơ khí chắc chắn và hoàn thiện đẹp.</p><p>Mỗi lô hàng về kho đều được kiểm tra cơ khí và lớp mạ trước khi nhập kho. Chúng tôi từ chối những dòng khóa rẻ nhưng nhanh xuống màu — vì bảo hành 10 năm chỉ có nghĩa khi sản phẩm thực sự trụ được 10 năm.</p>',
  'format' => 'basic_html',
]);
$node->set('field_credentials', [
  'Chứng nhận CE-CFF quốc tế',
  'Hóa đơn VAT cho mọi đơn hàng',
  'Kiểm tra từng lô trước nhập kho',
  'Sẵn linh kiện thay thế tại kho',
]);

$node->set('field_facts', kbp_paras('fact', [
  ['2014', 'Năm thành lập'],
  ['5', 'Showroom & kho'],
  ['200+', 'Mã sản phẩm'],
  ['10', 'Năm bảo hành'],
  ['CE-CFF', 'Chứng nhận'],
], ['field_fact_number', 'field_fact_label']));

$node->set('field_steps', kbp_paras('numbered_item', [
  ['01', 'Tiếp nhận nhu cầu', 'Gọi hotline hoặc gửi ảnh cửa qua Zalo — không cần biết trước tên model.'],
  ['02', 'Khảo sát kỹ thuật', 'Xác định loại cửa, độ dày cánh, chiều mở và phong cách hoàn thiện.'],
  ['03', 'Báo giá & hợp đồng', 'Gửi phương án kèm giá trong 24 giờ làm việc, xuất hóa đơn VAT.'],
  ['04', 'Giao hàng & lắp đặt', 'Giao 2–5 ngày toàn quốc, hỗ trợ kỹ thuật lắp đặt và bàn giao.'],
  ['05', 'Bảo hành 5–10 năm', 'Phiếu bảo hành theo bộ, sẵn linh kiện thay thế tại 5 cơ sở.'],
], ['field_item_number', 'field_item_title', 'field_item_desc']));

$node->set('field_values', kbp_paras('value_item', [
  ['Hàng chính hãng', 'Nhập khẩu nguyên bộ, có chứng từ và tem chống giả — không bán hàng trôi nổi.'],
  ['Tư vấn đúng kỹ thuật', 'Chọn khóa theo độ dày cửa, chiều mở và loại cánh — không bán sai model.'],
  ['Hậu mãi rõ ràng', 'Phiếu bảo hành theo bộ, hỗ trợ kỹ thuật và thay linh kiện suốt thời gian bảo hành.'],
], ['field_value_title', 'field_value_desc']));

$segments = [
  ['Chủ nhà & biệt thự', 'Chọn bộ khóa đồng bộ cho toàn bộ cánh cửa trong nhà, hợp phong cách nội thất.', 'Xem khóa đồng'],
  ['Khách sạn & resort', 'Khóa thẻ từ số lượng lớn, cấp thẻ master, phương án chìa cơ dự phòng.', 'Xem khóa khách sạn'],
  ['Nhà thầu & thi công', 'Báo giá theo hồ sơ dự án, giao theo tiến độ thi công, hỗ trợ kỹ thuật tại công trình.', 'Xem dự án'],
  ['Đại lý & cửa hàng', 'Giá đại lý theo cấp, hàng mẫu trưng bày, bảo vệ khu vực kinh doanh.', 'Chính sách đại lý'],
];
$seg_values = [];
foreach ($segments as [$title, $desc, $cta]) {
  $p = Paragraph::create([
    'type' => 'segment',
    'field_seg_title' => $title,
    'field_seg_desc' => $desc,
    'field_seg_cta' => ['uri' => 'internal:/san-pham', 'title' => $cta],
  ]);
  $p->save();
  $seg_values[] = ['target_id' => $p->id(), 'target_revision_id' => $p->getRevisionId()];
}
$node->set('field_segments', $seg_values);

$node->setPublished()->save();
echo ($existing ? 'updated' : 'created') . " about_page node {$node->id()}\n";
