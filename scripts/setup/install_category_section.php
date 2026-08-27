<?php

/**
 * @file
 * Seeds the homepage "Khám phá sản phẩm" block on an environment that already
 * has content.
 *
 * The block's wording used to be hardcoded in CategoryGrid.vue while
 * field_cat_title held an older, unrelated string nobody could see. The moment
 * the frontend starts reading the field, that stale value becomes the visible
 * heading — so any environment upgrading to this code needs its fields brought
 * in line with what the page was actually showing before.
 *
 * Only fills what is empty or still carries the stale value, so it is safe to
 * run repeatedly and it never overwrites wording an editor has since chosen.
 *
 * Run after importing the config, before rebuilding the frontend:
 *   vendor/bin/drush php:script scripts/setup/install_category_section.php
 */

/** What the block displayed while it was hardcoded. */
const KB_CAT_EYEBROW = 'Danh mục';
const KB_CAT_TITLE = 'Khám phá sản phẩm';
const KB_CAT_DESC = 'Chọn theo loại cửa và nhu cầu sử dụng — mỗi dòng sản phẩm có nhiều kích thước và màu hoàn thiện.';

/** The value the field carried while nothing rendered it. */
const KB_CAT_STALE_TITLE = 'Khóa & phụ kiện theo nhóm';

$nodes = \Drupal::entityTypeManager()->getStorage('node')
  ->loadByProperties(['type' => 'home_page']);
if (!$nodes) {
  print "Không tìm thấy node trang chủ — bỏ qua.\n";
  return;
}
$node = reset($nodes);

$wanted = [
  'field_cat_eyebrow' => KB_CAT_EYEBROW,
  'field_cat_title' => KB_CAT_TITLE,
  'field_cat_desc' => KB_CAT_DESC,
];

$changed = [];
foreach ($wanted as $field => $value) {
  if (!$node->hasField($field)) {
    print "Thiếu $field — hãy import config trước.\n";
    continue;
  }
  $current = (string) $node->get($field)->value;
  // An editor's own wording wins; only a blank or the stale title is replaced.
  if ($current !== '' && !($field === 'field_cat_title' && $current === KB_CAT_STALE_TITLE)) {
    continue;
  }
  $node->set($field, $value);
  $changed[] = $field;
}

if (!$changed) {
  print "Không có gì để đặt — các ô đã có nội dung.\n";
  return;
}
$node->save();
print "Đã đặt: " . implode(', ', $changed) . "\n";
