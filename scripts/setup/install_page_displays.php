<?php

/**
 * @file
 * Form displays and tab groups for the static-page content types.
 *
 * Without an entity_form_display a Drupal edit form renders none of its
 * fields — the same gap that left the product form empty.
 *
 * Run: ddev drush php:script scripts/setup/install_page_displays.php
 */

const KB_PAGE_FORMS = [
  'branch' => [
    'Thông tin' => ['field_tag' => 1, 'field_address' => 2, 'field_sort_order' => 3],
    'Liên hệ' => ['field_phone_display' => 1, 'field_phone_tel' => 2, 'field_map_url' => 3],
  ],
  'about_page' => [
    'Hero' => ['field_eyebrow' => 1, 'field_subtitle' => 2, 'field_hero_image' => 3, 'field_hero_caption' => 4, 'field_cta_primary' => 5, 'field_cta_secondary' => 6],
    'Con số' => ['field_facts' => 1],
    'Câu chuyện' => ['field_story_eyebrow' => 1, 'field_story_title' => 2, 'field_story_body' => 3, 'field_credentials' => 4],
    'Khách hàng' => ['field_segments' => 1],
    'Quy trình' => ['field_steps' => 1],
    'Cam kết' => ['field_values' => 1],
  ],
  'dealers_page' => [
    'Hero' => ['field_eyebrow' => 1, 'field_subtitle' => 2],
    'Quyền lợi' => ['field_benefits' => 1],
    'Điều kiện' => ['field_criteria' => 1],
    'Form' => ['field_form_title' => 1, 'field_form_desc' => 2, 'field_success_title' => 3, 'field_success_desc' => 4],
  ],
  'contact_page' => [
    'Hero' => ['field_eyebrow' => 1, 'field_subtitle' => 2],
    'Kênh liên hệ' => ['field_channels' => 1],
    'Công ty' => ['field_company_name' => 1, 'field_company_address' => 2, 'field_response_title' => 3, 'field_response_body' => 4],
    'Form' => ['field_form_title' => 1, 'field_form_desc' => 2, 'field_success_title' => 3, 'field_success_desc' => 4],
  ],
  'policies_page' => [
    'Hero' => ['field_eyebrow' => 1, 'field_subtitle' => 2],
    'Mục chính sách' => ['field_sections' => 1],
    'Hỗ trợ' => ['field_support_title' => 1, 'field_support_note' => 2],
  ],
];

/** Picks a sensible widget from the field's type. */
function kbp_widget(string $type): string {
  return match ($type) {
    'string_long' => 'string_textarea',
    'text_long' => 'text_textarea',
    'image' => 'image_image',
    'link' => 'link_default',
    'integer' => 'number',
    'boolean' => 'boolean_checkbox',
    'entity_reference_revisions' => 'paragraphs',
    default => 'string_textfield',
  };
}

$etm = \Drupal::entityTypeManager();
$field_manager = \Drupal::service('entity_field.manager');
$has_group = \Drupal::moduleHandler()->moduleExists('field_group');

foreach (KB_PAGE_FORMS as $bundle => $groups) {
  $id = "node.{$bundle}.default";
  $display = $etm->getStorage('entity_form_display')->load($id)
    ?: $etm->getStorage('entity_form_display')->create([
      'targetEntityType' => 'node', 'bundle' => $bundle, 'mode' => 'default', 'status' => TRUE,
    ]);

  $defs = $field_manager->getFieldDefinitions('node', $bundle);
  $display->setComponent('title', ['type' => 'string_textfield', 'weight' => 0]);
  $display->setComponent('status', ['type' => 'boolean_checkbox', 'weight' => 90]);

  $group_weight = 1;
  foreach ($groups as $label => $fields) {
    $children = [];
    foreach ($fields as $field => $weight) {
      if (!isset($defs[$field])) {
        echo "  SKIP missing {$bundle}.{$field}\n";
        continue;
      }
      $display->setComponent($field, [
        'type' => kbp_widget($defs[$field]->getType()),
        'weight' => $weight,
      ]);
      $children[] = $field;
    }
    if ($has_group && $children) {
      $group_name = 'group_' . preg_replace('/[^a-z0-9]+/', '_', mb_strtolower($label));
      $display->setThirdPartySetting('field_group', $group_name, [
        'children' => $children,
        'label' => $label,
        'parent_name' => '',
        'region' => 'content',
        'weight' => $group_weight++,
        'format_type' => 'tab',
        'format_settings' => ['formatter' => 'closed', 'description' => ''],
      ]);
    }
  }
  $display->save();
  echo "form display: {$bundle}" . ($has_group ? ' (tabs)' : ' (flat — field_group missing)') . "\n";
}

echo "\nDone.\n";
