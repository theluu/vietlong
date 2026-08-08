<?php

/**
 * @file
 * Adds the smart-lock attribute layer: the two features that vary between
 * models, and the door positions a model suits. Safe to run repeatedly.
 *
 * The handover deck is explicit that none of these become categories. A lock
 * has fingerprint, keypad, card and a mechanical key by default — those four
 * never divide the catalogue — while FaceID and app unlocking vary, and the
 * position a lock suits is a way of searching, not a place a product lives.
 * So they are attributes on the one record, and the listing queries them.
 *
 * Run: ddev drush php:script scripts/setup/install_product_feature_fields.php
 */

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Creates a field storage and its bundle instance when either is missing.
 */
function kbf_field(
  string $entity_type,
  string $bundle,
  string $name,
  string $type,
  string $label,
  string $description = '',
  int $cardinality = 1,
  array $settings = [],
  array $instance = [],
): void {
  if (!FieldStorageConfig::loadByName($entity_type, $name)) {
    FieldStorageConfig::create([
      'field_name' => $name,
      'entity_type' => $entity_type,
      'type' => $type,
      'cardinality' => $cardinality,
      'settings' => $settings,
    ])->save();
    echo "storage: {$name}\n";
  }
  if (!FieldConfig::loadByName($entity_type, $bundle, $name)) {
    FieldConfig::create([
      'field_name' => $name,
      'entity_type' => $entity_type,
      'bundle' => $bundle,
      'label' => $label,
      'description' => $description,
      'settings' => $instance,
    ])->save();
    echo "field:   {$bundle}.{$name}\n";
  }
}

// --- Vocabulary of door positions -------------------------------------------
if (!Vocabulary::load('door_position')) {
  Vocabulary::create(['vid' => 'door_position', 'name' => 'Vị trí phù hợp'])->save();
  echo "vocabulary: door_position\n";
}

// Ordered as a customer thinks about their home, not alphabetically.
foreach (['Cửa chính', 'Cửa phòng', 'Cửa làm việc', 'Vị trí khác trong nhà'] as $weight => $name) {
  $found = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
    ->loadByProperties(['vid' => 'door_position', 'name' => $name]);
  if (!$found) {
    Term::create(['vid' => 'door_position', 'name' => $name, 'weight' => $weight])->save();
    echo "term:    {$name}\n";
  }
}

// --- Product fields ---------------------------------------------------------

// Unlimited: a lock that suits the front door usually suits a bedroom door
// too, and the deck forbids duplicating the model to say so.
kbf_field(
  'node', 'product', 'field_door_position', 'entity_reference',
  'Vị trí phù hợp',
  'Những vị trí cửa model này dùng được. Chọn nhiều nếu phù hợp nhiều vị trí — không tạo sản phẩm riêng cho từng vị trí.',
  FieldStorageConfig::CARDINALITY_UNLIMITED,
  ['target_type' => 'taxonomy_term'],
  ['handler' => 'default:taxonomy_term', 'handler_settings' => ['target_bundles' => ['door_position' => 'door_position']]],
);

kbf_field(
  'node', 'product', 'field_faceid', 'boolean',
  'FaceID',
  'Chỉ một số mẫu có. Hiện thành badge trên thẻ sản phẩm.',
);

kbf_field(
  'node', 'product', 'field_remote_app', 'boolean',
  'Mở cửa từ xa qua app',
  'Là tính năng chung của dòng cửa nhôm kính; với cửa gỗ chỉ một số mẫu có.',
);

// --- Editor form ------------------------------------------------------------
// They belong beside brand, category and finish: the editor is classifying
// the product, not describing it.
$display = \Drupal::entityTypeManager()->getStorage('entity_form_display')
  ->load('node.product.default');
if ($display) {
  $weight = 6;
  foreach ([
    'field_door_position' => ['type' => 'options_buttons', 'settings' => []],
    'field_faceid' => ['type' => 'boolean_checkbox', 'settings' => ['display_label' => TRUE]],
    'field_remote_app' => ['type' => 'boolean_checkbox', 'settings' => ['display_label' => TRUE]],
  ] as $field => $widget) {
    if (!$display->getComponent($field)) {
      $display->setComponent($field, $widget + ['weight' => $weight++, 'region' => 'content']);
      echo "form:    {$field}\n";
    }
  }

  // field_group stores its children in third-party settings, so a field added
  // to the display alone would render outside every tab.
  $groups = $display->getThirdPartySettings('field_group');
  if (isset($groups['group_ph_n_lo_i'])) {
    $children = $groups['group_ph_n_lo_i']['children'];
    foreach (['field_door_position', 'field_faceid', 'field_remote_app'] as $field) {
      if (!in_array($field, $children, TRUE)) {
        $children[] = $field;
        echo "group:   {$field} -> Phân loại\n";
      }
    }
    $groups['group_ph_n_lo_i']['children'] = $children;
    $display->setThirdPartySetting('field_group', 'group_ph_n_lo_i', $groups['group_ph_n_lo_i']);
  }
  $display->save();
}

echo "\nDone. Export with: ddev drush cex -y\n";
