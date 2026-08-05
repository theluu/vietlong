<?php

/**
 * @file
 * Chuyển ảnh đại diện từ URL trỏ về site cũ sang image field.
 *
 * Cả chín ảnh đang hotlink đều đã có bản .webp là managed file trong Drupal này
 * (chúng được dùng cho ảnh sản phẩm), nên đây là một phép tra fid chứ không
 * phải tải file. Ánh xạ theo tên file: dấu gạch dưới thành gạch ngang, bỏ đuôi.
 *
 * Không đoán: URL nào không khớp một managed file thì script DỪNG và báo tên,
 * vì bỏ qua im lặng sẽ để lại một node không ảnh mà không ai biết.
 *
 * Safe to run repeatedly — node nào đã có ảnh thì bỏ qua.
 *
 * Run: ddev drush php:script scripts/setup/migrate_image_urls_to_fields.php
 */

$fileStorage = \Drupal::entityTypeManager()->getStorage('file');
$nodeStorage = \Drupal::entityTypeManager()->getStorage('node');

/** Tên file cũ (không đuôi, gạch dưới) => managed file tương ứng. */
$resolve = function (string $url) use ($fileStorage): ?int {
  $base = pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_FILENAME);
  // `_r3_0152_copy_0` => `-r3-0152-copy-0`, khớp cách Drupal đặt tên khi import.
  $candidate = str_replace('_', '-', $base) . '.webp';
  $ids = $fileStorage->getQuery()->accessCheck(FALSE)
    ->condition('uri', '%/' . $candidate, 'LIKE')->range(0, 1)->execute();
  return $ids ? (int) reset($ids) : NULL;
};

$moved = 0;
$skipped = 0;
foreach ([['article', 'field_article_image_url', 'field_article_image'],
          ['project', 'field_project_image_url', 'field_project_image']] as [$bundle, $old, $new]) {
  $ids = $nodeStorage->getQuery()->accessCheck(FALSE)->condition('type', $bundle)->execute();
  foreach ($nodeStorage->loadMultiple($ids) as $node) {
    if (!$node->get($new)->isEmpty()) {
      $skipped++;
      continue;
    }
    $url = trim((string) $node->get($old)->value);
    if ($url === '') {
      continue;
    }
    $fid = $resolve($url);
    if ($fid === NULL) {
      throw new \RuntimeException(
        "Không tìm được managed file cho $url (node {$node->id()}). " .
        'Import ảnh này vào Drupal trước rồi chạy lại.'
      );
    }
    $node->set($new, ['target_id' => $fid, 'alt' => $node->label()]);
    $node->save();
    $moved++;
  }
}

echo "Đã gán ảnh cho $moved node, bỏ qua $skipped node đã có ảnh.\n";
