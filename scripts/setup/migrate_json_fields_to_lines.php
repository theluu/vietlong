<?php

/**
 * @file
 * Chuyển bảng so sánh, FAQ và danh sách sản phẩm từ JSON sang format dòng.
 *
 * Biên tập viên trước nay phải gõ [{"door":"Gỗ tự nhiên",...}] vào một ô
 * textarea. Chỉ cần quên một dấu ngoặc là cả khối biến mất mà không báo gì.
 * Format mới: mỗi dòng một hàng, các cột ngăn bởi `|`. Một dòng thì hoặc tách
 * được hoặc không, không có trạng thái nào ở giữa.
 *
 * Safe to run repeatedly — giá trị nào không còn là JSON thì bỏ qua.
 *
 * Run: ddev drush php:script scripts/setup/migrate_json_fields_to_lines.php
 */

/** field => cột theo thứ tự, hoặc NULL nếu là danh sách một cột. */
const KB_FIELDS = [
  'field_article_compare' => ['door', 'thickness', 'lock', 'backup'],
  'field_article_faqs' => ['question', 'answer'],
  'field_article_products' => NULL,
];

/** Mô tả hiện dưới ô nhập, để biên tập viên biết gõ thế nào. */
const KB_HELP = [
  'field_article_compare' => 'Mỗi dòng một hàng của bảng. Bốn cột ngăn bởi dấu | theo thứ tự: Loại cửa | Độ dày | Loại khóa | Phương án dự phòng. Ví dụ:<br>Gỗ tự nhiên | 45–55 mm | Thân dài | Chìa cơ',
  'field_article_faqs' => 'Mỗi dòng một câu hỏi. Câu hỏi và câu trả lời ngăn bởi dấu |. Ví dụ:<br>Khóa hết pin thì mở thế nào? | Dùng sạc dự phòng qua cổng USB dưới thân khóa.',
  'field_article_products' => 'Mỗi dòng một đường dẫn sản phẩm, không kèm tên miền. Ví dụ:<br>khoa-van-tay-cua-go',
];

$storage = \Drupal::entityTypeManager()->getStorage('node');
$ids = $storage->getQuery()->accessCheck(FALSE)->condition('type', 'article')->execute();

$converted = 0;
$skipped = 0;
foreach ($storage->loadMultiple($ids) as $node) {
  $changed = FALSE;
  foreach (KB_FIELDS as $field => $columns) {
    $value = trim((string) $node->get($field)->value);
    if ($value === '' || !str_starts_with($value, '[')) {
      $skipped++;
      continue;
    }
    $decoded = json_decode($value, TRUE);
    if (!is_array($decoded)) {
      throw new \RuntimeException("JSON hỏng ở $field của node {$node->id()} — sửa tay trước khi chạy lại.");
    }

    $lines = [];
    foreach ($decoded as $row) {
      $lines[] = $columns === NULL
        ? (string) $row
        : implode(' | ', array_map(static fn(string $c): string => trim((string) ($row[$c] ?? '')), $columns));
    }
    $node->set($field, implode("\n", $lines));
    $changed = TRUE;
    $converted++;
  }
  if ($changed) {
    $node->save();
  }
}

// Mô tả trường là nơi duy nhất biên tập viên nhìn thấy cú pháp.
foreach (KB_HELP as $field => $help) {
  $config = \Drupal\field\Entity\FieldConfig::loadByName('node', 'article', $field);
  if ($config && $config->getDescription() !== $help) {
    $config->setDescription($help)->save();
    echo "  mô tả $field đã cập nhật\n";
  }
}

echo "Đã chuyển $converted trường sang format dòng, bỏ qua $skipped trường đã chuyển hoặc rỗng.\n";
