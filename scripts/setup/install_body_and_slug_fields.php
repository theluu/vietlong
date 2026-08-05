<?php

/**
 * @file
 * Nội dung chi tiết cho bài viết và dự án, và ô tự sinh slug.
 *
 * Dự án trước nay không có trường nội dung dài nào cả — trang chi tiết in ra
 * một đoạn văn hardcode giống hệt nhau cho mọi công trình. Bài viết thì có,
 * nhưng dưới dạng JSON gõ tay trong textarea, thứ không ai nhập nổi.
 *
 * Slug cũng vậy: là trường text biên tập viên phải tự gõ, trong khi sản phẩm
 * trên cùng site lại tự sinh qua pathauto. Ô tích ở đây đưa hai loại về chung
 * một hành vi mà vẫn cho gõ tay khi cần giữ URL cũ.
 *
 * Safe to run repeatedly.
 *
 * Run: ddev drush php:script scripts/setup/install_body_and_slug_fields.php
 */

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/** bundle => [nhãn nội dung, nhãn ô tích] */
const KB_BUNDLES = [
  'article' => ['Nội dung bài viết', 'Tự sinh slug từ tiêu đề'],
  'project' => ['Nội dung dự án', 'Tự sinh slug từ tiêu đề'],
];

$ensureStorage = function (string $name, string $type): void {
  if (FieldStorageConfig::loadByName('node', $name)) {
    echo "  storage $name đã có\n";
    return;
  }
  FieldStorageConfig::create([
    'field_name' => $name,
    'entity_type' => 'node',
    'type' => $type,
    'cardinality' => 1,
  ])->save();
  echo "  storage $name đã tạo\n";
};

$ensureField = function (string $bundle, string $name, string $label, array $settings = [], $default = NULL): void {
  if (FieldConfig::loadByName('node', $bundle, $name)) {
    echo "  $bundle.$name đã có\n";
    return;
  }
  $values = [
    'field_name' => $name,
    'entity_type' => 'node',
    'bundle' => $bundle,
    'label' => $label,
    'required' => FALSE,
    'settings' => $settings,
  ];
  if ($default !== NULL) {
    $values['default_value'] = $default;
  }
  FieldConfig::create($values)->save();
  echo "  $bundle.$name đã tạo\n";
};

foreach (KB_BUNDLES as $bundle => [$bodyLabel, $autoLabel]) {
  echo "$bundle:\n";

  $ensureStorage("field_{$bundle}_body", 'text_long');
  $ensureField($bundle, "field_{$bundle}_body", $bodyLabel);

  // Mặc định bật: nội dung mới nên tự sinh slug, còn bài cũ đã có slug đúng
  // nên bật cho chúng cũng không đổi gì (thuật toán sinh ra đúng chuỗi đang lưu).
  $ensureStorage("field_{$bundle}_slug_auto", 'boolean');
  $ensureField($bundle, "field_{$bundle}_slug_auto", $autoLabel, [
    'on_label' => 'Bật',
    'off_label' => 'Tắt',
  ], [['value' => 1]]);
}

// Node đã tồn tại chưa có giá trị cho ô tích mới; NULL không phải TRUE, nên
// nếu không điền thì presave sẽ bỏ qua chúng mãi mãi.
$storage = \Drupal::entityTypeManager()->getStorage('node');
$filled = 0;
foreach (array_keys(KB_BUNDLES) as $bundle) {
  $ids = $storage->getQuery()->accessCheck(FALSE)->condition('type', $bundle)->execute();
  foreach ($storage->loadMultiple($ids) as $node) {
    if ($node->get("field_{$bundle}_slug_auto")->isEmpty()) {
      $node->set("field_{$bundle}_slug_auto", TRUE);
      $node->save();
      $filled++;
    }
  }
}
echo "Đã bật ô tự sinh slug cho $filled node có sẵn.\n";
