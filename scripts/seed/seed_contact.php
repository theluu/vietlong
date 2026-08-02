<?php

/**
 * @file
 * Seeds the single Liên hệ node. Safe to run repeatedly.
 */

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

$storage = \Drupal::entityTypeManager()->getStorage('node');
$existing = $storage->loadByProperties(['type' => 'contact_page']);
$node = $existing ? reset($existing) : Node::create(['type' => 'contact_page']);

$node->setTitle('Kết nối với Keybolts');
$node->set('field_eyebrow', 'Liên hệ');
$node->set('field_subtitle', 'Gọi hotline, nhắn Zalo hoặc để lại thông tin — đội kỹ thuật và kinh doanh trực từ 8:00 đến 18:00 tất cả các ngày trong tuần.');
$node->set('field_company_name', 'Công ty TNHH XNK Khóa Cửa Việt Long');
$node->set('field_company_address', 'Trụ sở: Khu phố Lê Hồng Phong, P. Đông Ngàn, TP. Từ Sơn, Bắc Ninh');
$node->set('field_response_title', 'Chúng tôi trả lời trong 24 giờ');
$node->set('field_response_body', 'Nếu bạn đang chọn khóa cho công trình, hãy nêu rõ loại cửa, độ dày cánh và số lượng — Keybolts sẽ gửi phương án và báo giá phù hợp.');
$node->set('field_form_title', 'Form liên hệ');
$node->set('field_form_desc', 'Điền thông tin bên dưới, Keybolts sẽ liên hệ lại theo số điện thoại bạn cung cấp.');
$node->set('field_success_title', 'Đã nhận thông tin!');
$node->set('field_success_desc', 'Keybolts sẽ liên hệ trong 24 giờ làm việc.');

$channels = [
  ['Hotline', '1900 9018', '8:00 – 18:00, cả tuần', 'tel:19009018'],
  ['Zalo', '1900 9018', 'Gửi ảnh cửa để được tư vấn nhanh', 'https://zalo.me/19009018'],
  ['Email', 'khoacuavietlong@gmail.com', 'Báo giá công trình & hợp tác', 'mailto:khoacuavietlong@gmail.com'],
];
$values = [];
foreach ($channels as [$label, $value, $note, $uri]) {
  $p = Paragraph::create([
    'type' => 'contact_channel',
    'field_ch_label' => $label,
    'field_ch_value' => $value,
    'field_ch_note' => $note,
    'field_ch_url' => ['uri' => $uri, 'title' => $value],
  ]);
  $p->save();
  $values[] = ['target_id' => $p->id(), 'target_revision_id' => $p->getRevisionId()];
}
$node->set('field_channels', $values);

$node->setPublished()->save();
echo ($existing ? 'updated' : 'created') . " contact_page node {$node->id()}\n";
