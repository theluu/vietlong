<?php

/**
 * @file
 * Nhiều ảnh cho banner đầu trang chủ, thay cho một ảnh tĩnh.
 *
 * Trước đây khung ảnh bên phải hero mượn tạm ảnh của sản phẩm nổi bật đầu
 * tiên — biên tập viên không có cách nào đổi nó. Trường này cho phép up nhiều
 * ảnh và kéo thả đổi thứ tự; frontend chạy thành slider gạt ngang.
 *
 * Để trống thì hero vẫn mượn ảnh sản phẩm như cũ, nên chạy script này không
 * làm đổi giao diện cho tới khi có người up ảnh.
 *
 * Safe to run repeatedly.
 *
 * Run: ddev drush php:script scripts/setup/install_hero_slider.php
 */

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

const KB_HERO_IMAGES = 'field_hero_images';

// ---------------------------------------------------------------------------
// Field
// ---------------------------------------------------------------------------

if (!FieldStorageConfig::loadByName('node', KB_HERO_IMAGES)) {
  FieldStorageConfig::create([
    'field_name' => KB_HERO_IMAGES,
    'entity_type' => 'node',
    'type' => 'image',
    // Unlimited: the slider is however many slides the editor uploads.
    'cardinality' => -1,
  ])->save();
  echo "field storage: node." . KB_HERO_IMAGES . "\n";
}

if (!FieldConfig::loadByName('node', 'home_page', KB_HERO_IMAGES)) {
  FieldConfig::create([
    'field_name' => KB_HERO_IMAGES,
    'entity_type' => 'node',
    'bundle' => 'home_page',
    'label' => 'Hero — ảnh banner',
    'description' => 'Up nhiều ảnh để banner chạy thành slider. Kéo thả để đổi thứ tự. Ảnh ngang, tối thiểu 1200px chiều rộng. Để trống thì banner tự lấy ảnh sản phẩm nổi bật.',
    'settings' => [
      'file_directory' => 'hero',
      'alt_field' => TRUE,
      'alt_field_required' => FALSE,
      'max_resolution' => '2400x2400',
      'min_resolution' => '800x600',
    ],
  ])->save();
  echo "  field: home_page." . KB_HERO_IMAGES . "\n";
}

// ---------------------------------------------------------------------------
// Form display
// ---------------------------------------------------------------------------
// A field created in code is not placed on the form by itself, and the Hero
// tab is a field_group — a field missing from its children list renders
// outside the tabs, where an editor will never look for it.

$display = \Drupal::service('entity_display.repository')
  ->getFormDisplay('node', 'home_page', 'default');

if (!$display->getComponent(KB_HERO_IMAGES)) {
  $display->setComponent(KB_HERO_IMAGES, [
    'type' => 'image_image',
    // Right under the headline fields, above the CTAs.
    'weight' => 3,
    'region' => 'content',
    'settings' => ['progress_indicator' => 'throbber', 'preview_image_style' => 'thumbnail'],
  ]);
  echo "  form display: widget added\n";
}

$groups = $display->getThirdPartySettings('field_group');
if (isset($groups['group_hero']) && !in_array(KB_HERO_IMAGES, $groups['group_hero']['children'], TRUE)) {
  // After field_hero_subtitle so the tab reads: chữ → ảnh → nút.
  $children = $groups['group_hero']['children'];
  $at = array_search('field_hero_subtitle', $children, TRUE);
  array_splice($children, $at === FALSE ? count($children) : $at + 1, 0, KB_HERO_IMAGES);
  $groups['group_hero']['children'] = $children;
  $display->setThirdPartySetting('field_group', 'group_hero', $groups['group_hero']);
  echo "  form display: added to the Hero tab\n";
}

$display->save();

echo "\nDone. Trường 'Hero — ảnh banner' đã có trong tab Hero của Trang chủ.\n";
