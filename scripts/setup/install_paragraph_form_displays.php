<?php

/**
 * @file
 * Form displays for the paragraph types.
 *
 * Same gap as install_page_displays.php, one level down: a paragraph type
 * without an entity_form_display renders its subform with zero widgets. In the
 * Paragraphs widget that shows up as a row with a title and a Collapse link and
 * nothing inside — the editor sees the item but cannot touch a single field.
 *
 * Seven types shipped without one, contact_channel among them, which is why the
 * Hotline / Zalo / Email cards on /lien-he were uneditable for everybody,
 * administrators included.
 *
 * Only adds what is missing, so it is safe to run repeatedly and it leaves
 * hand-tuned widgets on the types that already have a display alone.
 *
 * Run: ddev drush php:script scripts/setup/install_paragraph_form_displays.php
 */

/**
 * Field order per type, for the ones a reader expects in a fixed sequence.
 * Types left out here fall back to the order the fields were created in.
 */
const KB_PARA_ORDER = [
  'contact_channel' => ['field_ch_label', 'field_ch_value', 'field_ch_note', 'field_ch_url'],
  'fact' => ['field_fact_number', 'field_fact_label'],
  'numbered_item' => ['field_item_number', 'field_item_title', 'field_item_desc'],
  'policy_item' => ['field_pol_key', 'field_pol_value'],
  'policy_section' => ['field_pol_eyebrow', 'field_pol_title', 'field_pol_label', 'field_pol_intro', 'field_pol_items', 'field_pol_note'],
  'segment' => ['field_seg_title', 'field_seg_desc', 'field_seg_image', 'field_seg_cta'],
  'value_item' => ['field_value_title', 'field_value_desc'],
];

/** Picks a sensible widget from the field's type. */
function kbpara_widget(string $type): string {
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
$storage = $etm->getStorage('entity_form_display');

foreach ($etm->getStorage('paragraphs_type')->loadMultiple() as $bundle => $type) {
  $display = $storage->load("paragraph.{$bundle}.default")
    ?: $storage->create([
      'targetEntityType' => 'paragraph', 'bundle' => $bundle, 'mode' => 'default', 'status' => TRUE,
    ]);

  // Configurable fields only: the base fields (created, status, parent_id …)
  // stay out of the subform, exactly as the existing displays have them.
  $fields = array_filter(
    $field_manager->getFieldDefinitions('paragraph', $bundle),
    fn($definition) => $definition instanceof \Drupal\field\FieldConfigInterface,
  );

  $order = KB_PARA_ORDER[$bundle] ?? [];
  $names = array_merge(
    array_values(array_intersect($order, array_keys($fields))),
    array_diff(array_keys($fields), $order),
  );

  $added = [];
  $weight = 0;
  foreach ($names as $name) {
    $weight++;
    // An existing component is somebody's deliberate choice — reweight it to
    // keep the order, but do not overwrite the widget or its settings.
    if ($component = $display->getComponent($name)) {
      $component['weight'] = $weight;
      $display->setComponent($name, $component);
      continue;
    }
    $display->setComponent($name, [
      'type' => kbpara_widget($fields[$name]->getType()),
      'weight' => $weight,
      'region' => 'content',
    ]);
    $added[] = $name;
  }

  foreach (['created', 'status'] as $base) {
    $display->removeComponent($base);
  }
  $display->save();

  printf(
    "%-18s %d trường%s\n",
    $bundle,
    count($names),
    $added ? ' — thêm mới: ' . implode(', ', $added) : ' — đã đủ',
  );
}

echo "\nXong. Chạy `drush cr` rồi mở lại form để kiểm tra.\n";
