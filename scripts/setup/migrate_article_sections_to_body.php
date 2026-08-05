<?php

/**
 * @file
 * Chuyển nội dung bài viết từ JSON sang HTML soạn thảo được.
 *
 * field_article_sections là JSON gõ tay trong một ô textarea — cấu trúc đúng
 * cho máy đọc, nhưng không biên tập viên nào nhập nổi. Ánh xạ sang HTML không
 * mất dữ liệu: title thành h2 giữ nguyên id để mục lục vẫn neo được,
 * paragraphs thành p, list thành ul, note thành blockquote.
 *
 * field_article_compare và field_article_faqs KHÔNG đụng tới — chúng render
 * thành bảng so sánh và khối FAQ có cấu trúc riêng, không phải văn xuôi.
 *
 * Safe to run repeatedly — node nào đã có body thì bỏ qua.
 *
 * Run: ddev drush php:script scripts/setup/migrate_article_sections_to_body.php
 */

$storage = \Drupal::entityTypeManager()->getStorage('node');
$ids = $storage->getQuery()->accessCheck(FALSE)->condition('type', 'article')->execute();

$moved = 0;
$skipped = 0;
foreach ($storage->loadMultiple($ids) as $node) {
  if (!$node->get('field_article_body')->isEmpty()) {
    $skipped++;
    continue;
  }
  $sections = json_decode((string) $node->get('field_article_sections')->value, TRUE);
  if (!is_array($sections) || !$sections) {
    continue;
  }

  $html = '';
  foreach ($sections as $section) {
    $id = (string) ($section['id'] ?? '');
    $title = (string) ($section['title'] ?? '');
    if ($title !== '') {
      $html .= $id !== ''
        ? '<h2 id="' . htmlspecialchars($id, ENT_QUOTES) . '">' . htmlspecialchars($title) . "</h2>\n"
        : '<h2>' . htmlspecialchars($title) . "</h2>\n";
    }
    foreach ((array) ($section['paragraphs'] ?? []) as $p) {
      $html .= '<p>' . htmlspecialchars((string) $p) . "</p>\n";
    }
    $list = (array) ($section['list'] ?? []);
    if ($list) {
      $html .= "<ul>\n";
      foreach ($list as $li) {
        $html .= '<li>' . htmlspecialchars((string) $li) . "</li>\n";
      }
      $html .= "</ul>\n";
    }
    $note = (string) ($section['note'] ?? '');
    if ($note !== '') {
      $html .= '<blockquote><p>' . htmlspecialchars($note) . "</p></blockquote>\n";
    }
  }

  $node->set('field_article_body', ['value' => $html, 'format' => 'basic_html']);
  $node->save();
  printf("%s: %d phần -> %d ký tự HTML%s", $node->label(), count($sections), strlen($html), PHP_EOL);
  $moved++;
}

echo "Đã chuyển $moved bài, bỏ qua $skipped bài đã có nội dung.\n";
