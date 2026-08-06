<?php

/**
 * @file
 * Ghi chú hướng dẫn cho 4 trường dựng bộ chọn size trên trang sản phẩm.
 *
 * Bốn trường này đứng tên rất giống nhau và đều rỗng phần mô tả, nên form sửa
 * một sản phẩm đọc lên như thể nó chứa toàn bộ danh sách size — trong khi
 * thực tế mỗi node chỉ mang size của chính nó, và bộ chọn ngoài trang là kết
 * quả gom các node cùng "Dòng sản phẩm" (xem VariantMatrixBuilder).
 *
 * Hai ô "Mã size" và "Tên size" cùng hiện một giá trị giống hệt nhau càng dễ
 * bị đọc nhầm thành "sản phẩm này có 2 size".
 *
 * Safe to run repeatedly.
 *
 * Run: ddev drush php:script scripts/setup/describe_variant_fields.php
 */

use Drupal\field\Entity\FieldConfig;

$descriptions = [
  'field_family' => 'Khoá gom biến thể. Mọi sản phẩm điền GIỐNG HỆT ô này sẽ hiện thành các lựa chọn size/màu của nhau trên trang chi tiết. Ví dụ: KB 1700, KB-SUS 201. Để trống = sản phẩm đứng một mình, không có bộ chọn.',
  'field_size_key' => 'Size của RIÊNG sản phẩm này, không phải danh sách size. Dùng để so khớp giữa các biến thể nên phải viết thống nhất trong cùng một dòng. Ví dụ: S, M, L, XL, XXL, hoặc S0.5, S11.',
  'field_size_label' => 'Chữ hiện trên nút chọn size. Ví dụ: "Đại sảnh XL", "S11".',
  'field_size_note' => 'Dòng chữ nhỏ dưới nút chọn size, nói size này hợp với cửa nào. Ví dụ: "Cửa 2 cánh lớn", "Cửa đi cỡ đại".',
];

foreach ($descriptions as $name => $text) {
  $field = FieldConfig::loadByName('node', 'product', $name);
  if (!$field) {
    echo "  thiếu trường: {$name}\n";
    continue;
  }
  if ($field->getDescription() === $text) {
    echo "  {$name}: đã có\n";
    continue;
  }
  $field->setDescription($text)->save();
  echo "  {$name}: đã ghi mô tả\n";
}

echo "\nXong. Mở lại form sửa sản phẩm để thấy hướng dẫn dưới mỗi ô.\n";
