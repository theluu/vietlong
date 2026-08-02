<?php

/**
 * @file
 * Seeds the single Chính sách node. Safe to run repeatedly.
 */

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

const KB_POLICIES = [
  [
    'Bảo hành', 'Chính sách 01', 'Chính sách bảo hành',
    'Toàn bộ sản phẩm Keybolts được bảo hành 5–10 năm tùy dòng, áp dụng cho lỗi kỹ thuật từ nhà sản xuất.',
    'Xuất trình phiếu bảo hành hoặc hóa đơn khi yêu cầu bảo hành. Với đơn công trình, Keybolts lưu hồ sơ theo mã dự án.',
    [
      ['Thời hạn', 'Khóa cơ và khóa đồng: 10 năm. Khóa điện tử, khóa vân tay, khóa thẻ từ: 5 năm cho phần cơ, 2 năm cho bo mạch và cụm điện tử.'],
      ['Phạm vi', 'Lỗi vật liệu, lỗi cơ khí, bong tróc lớp mạ trong điều kiện sử dụng bình thường trong nhà.'],
      ['Đổi mới', 'Đổi sản phẩm mới trong 12 tháng đầu nếu lỗi phần cơ không khắc phục được.'],
      ['Không áp dụng', 'Hư hỏng do va đập, phá khóa, tự tháo lắp sai kỹ thuật, ngập nước hoặc dùng hóa chất tẩy mạnh lên bề mặt mạ.'],
      ['Quy trình', 'Gọi 1900 9018 → mô tả lỗi (gửi ảnh/video qua Zalo) → kỹ thuật xác nhận → xử lý tại chỗ hoặc thu hồi về kho.'],
    ],
  ],
  [
    'Giao hàng', 'Chính sách 02', 'Chính sách giao hàng',
    'Keybolts giao hàng toàn quốc từ 5 kho và showroom tại Bắc Ninh, Phú Thọ và Vĩnh Phúc.',
    'Kiểm tra tình trạng thùng hàng trước khi ký nhận. Nếu thùng móp/rách, quay video mở hàng để được hỗ trợ đổi nhanh.',
    [
      ['Thời gian', 'Nội tỉnh Bắc Ninh: trong ngày. Hà Nội và các tỉnh lân cận: 1–2 ngày. Toàn quốc: 2–5 ngày làm việc.'],
      ['Phí vận chuyển', 'Hỗ trợ phí vận chuyển theo đơn hàng và khu vực. Đơn công trình có xe giao riêng theo lịch thi công.'],
      ['Đóng gói', 'Khóa đóng hộp nguyên bộ, bọc chống xước cho phần mạ, chèn xốp chống va đập.'],
      ['Nhận hàng', 'Được kiểm tra hàng trước khi thanh toán với đơn giao qua đối tác vận chuyển.'],
    ],
  ],
  [
    'Đổi trả', 'Chính sách 03', 'Chính sách đổi trả',
    'Đổi trả trong 7 ngày kể từ khi nhận hàng nếu sản phẩm chưa lắp đặt và còn nguyên hộp.',
    'Sản phẩm đã khoét cửa, đã lắp hoặc đã cài đặt vân tay không thuộc diện đổi trả, trừ trường hợp lỗi nhà sản xuất.',
    [
      ['Điều kiện', 'Còn nguyên tem, hộp, chìa và phụ kiện đi kèm; chưa có dấu vết lắp đặt.'],
      ['Đổi sai model', 'Miễn phí đổi nếu Keybolts giao sai model, sai màu hoặc sai size so với đơn xác nhận.'],
      ['Đổi do khách chọn sai', 'Hỗ trợ đổi sang model khác, khách bù chênh lệch giá và phí vận chuyển hai chiều.'],
      ['Hoàn tiền', 'Hoàn trong 3–7 ngày làm việc sau khi kho xác nhận tình trạng sản phẩm.'],
    ],
  ],
  [
    'Thanh toán', 'Chính sách 04', 'Chính sách thanh toán',
    'Keybolts nhận thanh toán tiền mặt, chuyển khoản và thanh toán theo tiến độ với đơn công trình.',
    'Chỉ chuyển khoản vào tài khoản mang tên công ty. Keybolts không nhận thanh toán qua tài khoản cá nhân không được xác nhận bằng văn bản.',
    [
      ['Khách lẻ', 'Thanh toán khi nhận hàng (COD) hoặc chuyển khoản trước khi giao.'],
      ['Đơn công trình', 'Đặt cọc theo hợp đồng, thanh toán phần còn lại theo tiến độ giao hàng.'],
      ['Đại lý', 'Công nợ theo hạn mức được duyệt, đối soát theo tháng.'],
      ['Hóa đơn', 'Xuất hóa đơn VAT cho tất cả đơn hàng khi có yêu cầu.'],
    ],
  ],
  [
    'Bảo mật thông tin', 'Chính sách 05', 'Bảo mật thông tin khách hàng',
    'Thông tin bạn cung cấp chỉ dùng để tư vấn, báo giá, giao hàng và chăm sóc sau bán hàng.',
    'Bạn có thể yêu cầu Keybolts cập nhật hoặc xóa thông tin liên hệ của mình bất kỳ lúc nào qua hotline 1900 9018.',
    [
      ['Dữ liệu thu thập', 'Họ tên, số điện thoại, email, địa chỉ giao hàng và nội dung yêu cầu tư vấn.'],
      ['Mục đích', 'Liên hệ tư vấn, gửi báo giá, giao hàng, xử lý bảo hành và thông báo chính sách liên quan đến đơn hàng.'],
      ['Chia sẻ', 'Chỉ chia sẻ cho đơn vị vận chuyển ở mức cần thiết để giao hàng. Không bán hoặc trao đổi dữ liệu với bên thứ ba.'],
      ['Lưu trữ', 'Lưu trên hệ thống nội bộ, giới hạn quyền truy cập cho nhân sự kinh doanh và chăm sóc khách hàng.'],
    ],
  ],
];

$storage = \Drupal::entityTypeManager()->getStorage('node');
$existing = $storage->loadByProperties(['type' => 'policies_page']);
$node = $existing ? reset($existing) : Node::create(['type' => 'policies_page']);

$node->setTitle('Chính sách Keybolts');
$node->set('field_eyebrow', 'Cam kết');
$node->set('field_subtitle', 'Bảo hành, giao hàng, đổi trả, thanh toán và bảo mật thông tin — rõ ràng bằng văn bản, áp dụng cho cả khách lẻ và đơn công trình.');
$node->set('field_support_title', 'Cần hỗ trợ?');
$node->set('field_support_note', 'Bộ phận bảo hành trực 8:00 – 18:00');

$sections = [];
foreach (KB_POLICIES as [$label, $eyebrow, $title, $intro, $note, $items]) {
  $item_values = [];
  foreach ($items as [$k, $v]) {
    $item = Paragraph::create([
      'type' => 'policy_item',
      'field_pol_key' => $k,
      'field_pol_value' => $v,
    ]);
    $item->save();
    $item_values[] = ['target_id' => $item->id(), 'target_revision_id' => $item->getRevisionId()];
  }
  $section = Paragraph::create([
    'type' => 'policy_section',
    'field_pol_label' => $label,
    'field_pol_eyebrow' => $eyebrow,
    'field_pol_title' => $title,
    'field_pol_intro' => $intro,
    'field_pol_note' => $note,
    'field_pol_items' => $item_values,
  ]);
  $section->save();
  $sections[] = ['target_id' => $section->id(), 'target_revision_id' => $section->getRevisionId()];
}
$node->set('field_sections', $sections);

$node->setPublished()->save();
echo ($existing ? 'updated' : 'created') . " policies_page node {$node->id()}\n";
