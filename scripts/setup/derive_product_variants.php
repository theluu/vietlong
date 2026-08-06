<?php

/**
 * @file
 * Suy ra field_family / field_size_key / field_size_label từ mã sản phẩm.
 *
 * Bộ chọn size trên trang chi tiết là kết quả truy vấn các node anh em cùng
 * field_family (xem VariantMatrixBuilder) — không phải dữ liệu nhập tay. Bản
 * demo có bộ chọn này vì dữ liệu seed điền sẵn family; đợt import CSV thay
 * toàn bộ catalogue bằng sản phẩm thật và ba trường đó rỗng 186/186, nên bộ
 * chọn biến mất khỏi mọi trang.
 *
 * Mã sản phẩm đã mang đủ thông tin, chỉ là chưa ai tách ra:
 *
 *   KB 1700-XL-DSF   -> family "KB 1700",   size XL
 *   KB-SUS 201 S11   -> family "KB-SUS 201", size S11
 *
 * Chỉ ghi vào node nào đang trống, nên chạy lại nhiều lần không đè lên dữ
 * liệu ai đó đã sửa tay.
 *
 * Run: ddev drush php:script scripts/setup/derive_product_variants.php
 */

/**
 * Bộ size chuẩn của khóa cửa, lấy đúng nhãn trong file design
 * (design/Keybolts Product Detail.html — DETAIL_SIZES).
 */
const KB_SIZES = [
  'S'   => ['Thông phòng S', 'Cửa phòng ngủ'],
  'M'   => ['Trung M', 'Cửa phòng lớn'],
  'L'   => ['Đại L', 'Cửa chính 1 cánh'],
  'XL'  => ['Đại sảnh XL', 'Cửa 2 cánh lớn'],
  'XXL' => ['Đại sảnh VIP XXL', 'Cửa đại sảnh 900mm'],
];

/**
 * Tách mã thành [family, size_key] hoặc NULL nếu mã không theo mẫu nào.
 */
function kb_parse_code(string $code): ?array {
  $code = trim($code);

  // A. Khóa cửa: "KB 1700-XL-DSF" — phần đuôi là finish, đã có field_finish.
  //    Tiền tố "KB" có lúc thiếu ("1700-S-DSF"), chuẩn hoá lại để hai biến
  //    thể cùng dòng không rơi vào hai family khác nhau.
  if (preg_match('/^(?:KB[\s-]*)?(.+?)[\s-]+(S|M|L|XL|XXL)[\s-]+[A-Za-z0-9]+$/u', $code, $m)) {
    return ['KB ' . trim($m[1]), strtoupper($m[2])];
  }

  // B. Bản lề và phụ kiện: "KB-SUS 201 S11", "KB-SUS 304 N4".
  if (preg_match('/^(KB-SUS\s+\d+)\s+([A-Z]\d+(?:\.\d+)?)$/u', $code, $m)) {
    return [$m[1], strtoupper($m[2])];
  }

  return NULL;
}

$storage = \Drupal::entityTypeManager()->getStorage('node');
$ids = $storage->getQuery()->accessCheck(FALSE)
  ->condition('type', 'product')->condition('status', 1)->execute();

// ---------------------------------------------------------------------------
// Pass 1: phân nhóm
// ---------------------------------------------------------------------------

$parsed = [];
foreach ($storage->loadMultiple($ids) as $node) {
  $code = $node->hasField('field_product_code') ? (string) $node->get('field_product_code')->value : '';
  if ($code === '' || !($hit = kb_parse_code($code))) {
    continue;
  }
  $parsed[$node->id()] = ['node' => $node, 'family' => $hit[0], 'size' => $hit[1], 'code' => $code];
}

$families = [];
foreach ($parsed as $nid => $row) {
  $families[$row['family']][$nid] = $row;
}

// Một family chỉ có đúng một size thì không có gì để chọn — bỏ qua, để trang
// chi tiết không mọc ra bộ chọn chỉ có một ô.
$families = array_filter($families, static fn(array $rows) => count(array_unique(array_column($rows, 'size'))) > 1);

// ---------------------------------------------------------------------------
// Pass 2: nhãn cho size không nằm trong bộ chuẩn
// ---------------------------------------------------------------------------

/**
 * Phần tiêu đề còn lại sau khi bỏ đoạn mở đầu chung của cả family.
 *
 * "Bản Lề Đầu Đồng Cửa Ô Thoáng KB-SUS 201 S0.5" trong nhóm mà mọi tiêu đề
 * đều mở đầu bằng "Bản Lề Đầu Đồng" sẽ còn lại "Cửa Ô Thoáng" — đúng thứ cần
 * hiện làm ghi chú dưới nhãn size.
 */
function kb_notes(array $rows): array {
  $words = [];
  foreach ($rows as $nid => $row) {
    $title = (string) $row['node']->getTitle();
    // Bỏ mã sản phẩm ở đuôi tiêu đề.
    $title = trim(str_ireplace($row['code'], '', $title));
    $title = trim(preg_replace('/\s+/u', ' ', $title));
    $words[$nid] = $title === '' ? [] : explode(' ', $title);
  }
  $lists = array_values($words);
  $common = 0;
  if (count($lists) > 1) {
    $limit = min(array_map('count', $lists));
    while ($common < $limit) {
      $w = mb_strtolower($lists[0][$common]);
      foreach ($lists as $l) {
        if (mb_strtolower($l[$common]) !== $w) {
          break 2;
        }
      }
      $common++;
    }
  }
  $out = [];
  foreach ($words as $nid => $list) {
    $rest = trim(implode(' ', array_slice($list, $common)));
    $out[$nid] = $rest === '' ? '' : mb_strtoupper(mb_substr($rest, 0, 1)) . mb_strtolower(mb_substr($rest, 1));
  }
  return $out;
}

// ---------------------------------------------------------------------------
// Pass 3: ghi
// ---------------------------------------------------------------------------

$written = 0;
$skipped = 0;
foreach ($families as $family => $rows) {
  $notes = kb_notes($rows);
  foreach ($rows as $nid => $row) {
    $node = $row['node'];
    // Không đè lên thứ đã có.
    if ((string) $node->get('field_family')->value !== '') {
      $skipped++;
      continue;
    }
    [$label, $note] = KB_SIZES[$row['size']] ?? [$row['size'], $notes[$nid] ?? ''];

    $node->set('field_family', $family);
    $node->set('field_size_key', $row['size']);
    $node->set('field_size_label', $label);
    if ($node->hasField('field_size_note')) {
      $node->set('field_size_note', $note);
    }
    $node->setNewRevision(FALSE);
    $node->save();
    $written++;
  }
  echo sprintf("  %-16s %d biến thể, sizes: %s\n", $family, count($rows),
    implode(', ', array_unique(array_column($rows, 'size'))));
}

echo sprintf("\nĐã ghi %d sản phẩm trong %d dòng. Bỏ qua %d (đã có family).\n",
  $written, count($families), $skipped);
echo sprintf("Không khớp mẫu mã nào: %d sản phẩm — không có bộ chọn size, giữ nguyên.\n",
  count($ids) - count($parsed));
