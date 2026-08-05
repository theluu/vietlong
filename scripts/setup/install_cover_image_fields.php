<?php

/**
 * @file
 * Ảnh đại diện của bài viết và dự án, dưới dạng image field.
 *
 * Trước đây hai loại này giữ ảnh trong một trường text chứa URL, và các URL đó
 * trỏ về site cũ keybolts.com.vn — ảnh gốc máy ảnh, tấm nặng nhất 12,4 MB.
 * Drupal không xử lý được ảnh nằm sau một URL ngoài, nên image style chưa từng
 * chạm được vào chúng. Đưa ảnh vào image field là điều kiện để cắt cỡ.
 *
 * Trường URL cũ được giữ nguyên, xóa ở một bước riêng sau khi đã verify.
 *
 * Safe to run repeatedly.
 *
 * Run: ddev drush php:script scripts/setup/install_cover_image_fields.php
 */

use Symfony\Component\Yaml\Yaml;

$sync = dirname(__DIR__, 2) . '/config/sync';

/** Tạo hoặc cập nhật một config entity từ file YAML, giữ nguyên uuid đang có. */
$apply = function (string $entityTypeId, string $file) use ($sync): void {
  $data = Yaml::parseFile("$sync/$file");
  $storage = \Drupal::entityTypeManager()->getStorage($entityTypeId);
  $existing = $storage->load($data['id'] ?? $data['name']);
  if ($existing === NULL) {
    $storage->create($data)->save();
    echo "Created $file\n";
    return;
  }
  foreach ($data as $key => $value) {
    if ($key !== 'uuid') {
      $existing->set($key, $value);
    }
  }
  $existing->save();
  echo "Updated $file\n";
};

foreach (['kb_card_400', 'kb_card_800', 'kb_hero_1200', 'kb_hero_1600'] as $style) {
  $apply('image_style', "image.style.$style.yml");
}

foreach (['article', 'project'] as $bundle) {
  $apply('field_storage_config', "field.storage.node.field_{$bundle}_image.yml");
  $apply('field_config', "field.field.node.$bundle.field_{$bundle}_image.yml");
}

echo "Xong. Chạy tiếp migrate_image_urls_to_fields.php.\n";
