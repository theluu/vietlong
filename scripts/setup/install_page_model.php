<?php

/**
 * @file
 * Creates the content model for the static pages. Safe to run repeatedly.
 *
 * Run: ddev drush php:script scripts/setup/install_page_model.php
 */

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;

/**
 * Creates a node type when missing.
 */
function kbp_node_type(string $id, string $label): void {
  if (!NodeType::load($id)) {
    NodeType::create(['type' => $id, 'name' => $label, 'new_revision' => TRUE])->save();
    echo "node type: {$id}\n";
  }
}

/**
 * Creates a field storage + instance when missing.
 */
function kbp_field(
  string $entity_type,
  string $bundle,
  string $name,
  string $type,
  string $label,
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
  }
  if (!FieldConfig::loadByName($entity_type, $bundle, $name)) {
    FieldConfig::create([
      'field_name' => $name,
      'entity_type' => $entity_type,
      'bundle' => $bundle,
      'label' => $label,
      'settings' => $instance,
    ])->save();
    echo "  field: {$bundle}.{$name}\n";
  }
}

kbp_node_type('branch', 'Cơ sở / Showroom');

kbp_field('node', 'branch', 'field_tag', 'string', 'Nhãn (Bán buôn, Cơ sở 1…)');
kbp_field('node', 'branch', 'field_address', 'string_long', 'Địa chỉ');
kbp_field('node', 'branch', 'field_phone_display', 'string', 'Điện thoại (hiển thị)');
kbp_field('node', 'branch', 'field_phone_tel', 'string', 'Điện thoại (số gọi)');
kbp_field('node', 'branch', 'field_map_url', 'link', 'Link chỉ đường');
kbp_field('node', 'branch', 'field_sort_order', 'integer', 'Thứ tự');

echo "Done.\n";
