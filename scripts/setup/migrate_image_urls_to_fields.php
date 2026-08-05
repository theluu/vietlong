<?php

/**
 * @file
 * Chuyển ảnh đại diện từ URL trỏ về site cũ sang image field.
 *
 * Cả chín ảnh đang hotlink đều đã có bản .webp là managed file trong Drupal này
 * (chúng được dùng cho ảnh sản phẩm), nên đây là một phép tra fid chứ không
 * phải tải file. Ánh xạ theo tên file: dấu gạch dưới thành gạch ngang, bỏ đuôi.
 *
 * Cùng một tên file có thể tồn tại dưới nhiều thư mục (home/, products/,
 * about/…) — ảnh sản phẩm được import trước và biết là đúng, nên khi trùng tên
 * script luôn chọn bản trong products/. Nếu không có bản products/, chọn bản
 * đứng đầu khi sắp xếp URI theo abc để việc chọn không phụ thuộc thứ tự
 * DB trả về. Mọi lần trùng tên đều được in ra kèm danh sách ứng viên, để không
 * có lựa chọn nào diễn ra trong im lặng.
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

/** Tên file cũ (không đuôi, gạch dưới) => managed file tương ứng, chọn tất định khi trùng tên. */
$resolve = function (string $url) use ($fileStorage): ?array {
  $base = pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_FILENAME);
  // `_r3_0152_copy_0` => `-r3-0152-copy-0`, khớp cách Drupal đặt tên khi import.
  $candidate = str_replace('_', '-', $base) . '.webp';
  $ids = $fileStorage->getQuery()->accessCheck(FALSE)
    ->condition('uri', '%/' . $candidate, 'LIKE')->execute();
  if (!$ids) {
    return NULL;
  }
  $files = $fileStorage->loadMultiple($ids);
  $uris = [];
  foreach ($files as $fid => $file) {
    $uris[(int) $fid] = $file->getFileUri();
  }
  asort($uris);
  $chosen = NULL;
  foreach ($uris as $fid => $uri) {
    if (str_contains($uri, '/products/')) {
      $chosen = $fid;
      break;
    }
  }
  $chosen ??= array_key_first($uris);
  return ['fid' => $chosen, 'uri' => $uris[$chosen], 'candidates' => $uris];
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
    $result = $resolve($url);
    if ($result === NULL) {
      throw new \RuntimeException(
        "Không tìm được managed file cho $url (node {$node->id()}). " .
        'Import ảnh này vào Drupal trước rồi chạy lại.'
      );
    }
    if (count($result['candidates']) > 1) {
      echo "  Trùng tên cho $url — ứng viên: " . implode(', ', $result['candidates']) .
        " — chọn {$result['uri']}\n";
    }
    $node->set($new, ['target_id' => $result['fid'], 'alt' => $node->label()]);
    $node->save();
    echo "Node {$node->id()} ({$node->label()}): $url -> {$result['uri']}\n";
    $moved++;
  }
}

echo "Đã gán ảnh cho $moved node, bỏ qua $skipped node đã có ảnh.\n";
