<?php

/**
 * @file
 * Seeds the single Đại lý node. Safe to run repeatedly.
 */

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

$storage = \Drupal::entityTypeManager()->getStorage('node');
$existing = $storage->loadByProperties(['type' => 'dealers_page']);
$node = $existing ? reset($existing) : Node::create(['type' => 'dealers_page']);

$node->setTitle('Trở thành đại lý Keybolts');
$node->set('field_eyebrow', 'Hợp tác');
$node->set('field_subtitle', 'Chính sách giá riêng cho đại lý, nhà thầu và cửa hàng vật liệu — hỗ trợ hàng mẫu, catalogue và kỹ thuật bán hàng.');
$node->set('field_criteria', [
  'Cửa hàng vật liệu xây dựng, nội thất hoặc kim khí đang kinh doanh',
  'Nhà thầu, đơn vị thi công cửa và nội thất',
  'Có kho hoặc mặt bằng trưng bày sản phẩm',
  'Cam kết doanh số tối thiểu theo cấp đại lý',
]);
$node->set('field_form_title', 'Đăng ký làm đại lý');
$node->set('field_form_desc', 'Điền thông tin, bộ phận kinh doanh sẽ gửi bảng giá đại lý và chính sách hợp tác.');
$node->set('field_success_title', 'Đã nhận thông tin!');
$node->set('field_success_desc', 'Keybolts sẽ liên hệ trong 24 giờ làm việc.');

$benefits = [
  ['01', 'Giá đại lý theo cấp', 'Chiết khấu theo doanh số và cấp đại lý, có bảng giá riêng cập nhật hàng quý.'],
  ['02', 'Hỗ trợ hàng mẫu', 'Cấp hàng mẫu và kệ trưng bày cho đại lý có showroom.'],
  ['03', 'Bảo vệ khu vực', 'Hạn chế số lượng đại lý trên cùng địa bàn để tránh cạnh tranh giá.'],
  ['04', 'Đào tạo kỹ thuật', 'Hướng dẫn lắp đặt, xử lý bảo hành và tư vấn chọn khóa cho nhân viên bán hàng.'],
];
$values = [];
foreach ($benefits as [$n, $title, $desc]) {
  $p = Paragraph::create([
    'type' => 'numbered_item',
    'field_item_number' => $n,
    'field_item_title' => $title,
    'field_item_desc' => $desc,
  ]);
  $p->save();
  $values[] = ['target_id' => $p->id(), 'target_revision_id' => $p->getRevisionId()];
}
$node->set('field_benefits', $values);

$node->setPublished()->save();
echo ($existing ? 'updated' : 'created') . " dealers_page node {$node->id()}\n";
