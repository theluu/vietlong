<?php

/**
 * @file
 * Configures the editor-facing form displays for the product model.
 *
 * install_product_model.php creates the fields but never configured a form
 * display, so `node.product.default` did not exist and the product edit form
 * rendered without any of its 29 fields. Drupal only shows a field in a form
 * when the display lists it as a component.
 *
 * Also sets a minimal view display: the site is headless, so nothing renders
 * these node pages, but a missing view display makes admin previews warn.
 *
 * Safe to run repeatedly.
 *
 * Run: ddev drush php:script scripts/setup/install_product_displays.php
 */

/**
 * Field order and widget for the product edit form, grouped the way an editor
 * fills the page in: identity first, then taxonomy, then the detail-page copy.
 *
 * weight => [field_name, widget_id, extra settings]
 */
const KB_PRODUCT_FORM = [
  // Identity
  'field_product_code'    => ['string_textfield', 1],
  'field_family'          => ['string_textfield', 2],
  'field_size_key'        => ['string_textfield', 3],
  'field_size_label'      => ['string_textfield', 4],
  'field_size_note'       => ['string_textfield', 5],

  // Taxonomy
  'field_brand'           => ['entity_reference_autocomplete', 10],
  'field_category'        => ['entity_reference_autocomplete', 11],
  'field_finish'          => ['entity_reference_autocomplete', 12],

  // Merchandising
  'field_badge'           => ['options_select', 20],
  'field_stock_status'    => ['options_select', 21],
  'field_featured'        => ['boolean_checkbox', 22],
  'field_featured_group'  => ['options_select', 23],
  'field_is_new'          => ['boolean_checkbox', 24],
  'field_contact_price'   => ['boolean_checkbox', 25],
  'field_sort_order'      => ['number', 26],

  // Media
  'field_images'          => ['image_image', 30],

  // Detail-page copy
  'field_short_desc'      => ['string_textarea', 40],
  'field_desc_heading'    => ['string_textfield', 41],
  'field_description'     => ['text_textarea', 42],
  'field_highlights'      => ['string_textarea', 43],
  'field_certification'   => ['string_textfield', 44],
  'field_warranty'        => ['string_textfield', 45],
  'field_door_thickness'  => ['string_textfield', 46],
  'field_origin'          => ['string_textfield', 47],

  // Paragraph tables
  'field_specifications'  => ['paragraphs', 50],
  'field_faqs'            => ['paragraphs', 51],
  'field_policy_cards'    => ['paragraphs', 52],

  // Relations
  'field_related_products' => ['entity_reference_autocomplete', 60],
];

/**
 * field_search_text is written by hook_node_presave, never by hand. Showing it
 * would invite an editor to type a value that the next save overwrites.
 */
const KB_PRODUCT_FORM_HIDDEN = ['field_search_text'];

const KB_PARAGRAPH_FORMS = [
  'spec_item'   => ['field_spec_key' => 1, 'field_spec_value' => 2],
  'faq_item'    => ['field_faq_question' => 1, 'field_faq_answer' => 2],
  'policy_card' => ['field_card_title' => 1, 'field_card_desc' => 2],
];

/**
 * Loads or creates a display config entity.
 */
function kb_display(string $storage, string $entity_type, string $bundle, string $mode = 'default') {
  $id = "{$entity_type}.{$bundle}.{$mode}";
  $display = \Drupal::entityTypeManager()->getStorage($storage)->load($id);
  if (!$display) {
    $display = \Drupal::entityTypeManager()->getStorage($storage)->create([
      'targetEntityType' => $entity_type,
      'bundle' => $bundle,
      'mode' => $mode,
      'status' => TRUE,
    ]);
    echo "created {$storage}: {$id}\n";
  }
  return $display;
}

// ---------------------------------------------------------------------------
// Product form display
// ---------------------------------------------------------------------------

$form = kb_display('entity_form_display', 'node', 'product');
$definitions = \Drupal::service('entity_field.manager')->getFieldDefinitions('node', 'product');

// Core fields the editor still needs.
$form->setComponent('title', ['type' => 'string_textfield', 'weight' => 0]);
$form->setComponent('status', ['type' => 'boolean_checkbox', 'weight' => 90]);

$applied = 0;
foreach (KB_PRODUCT_FORM as $field => [$widget, $weight]) {
  if (!isset($definitions[$field])) {
    echo "  SKIP (missing field): {$field}\n";
    continue;
  }
  $form->setComponent($field, ['type' => $widget, 'weight' => $weight]);
  $applied++;
}

foreach (KB_PRODUCT_FORM_HIDDEN as $field) {
  $form->removeComponent($field);
}

$form->save();
echo "product form display: {$applied} fields visible, "
  . count(KB_PRODUCT_FORM_HIDDEN) . " hidden\n";

// ---------------------------------------------------------------------------
// Paragraph form displays — without these the paragraph rows render empty too
// ---------------------------------------------------------------------------

foreach (KB_PARAGRAPH_FORMS as $bundle => $fields) {
  $display = kb_display('entity_form_display', 'paragraph', $bundle);
  $defs = \Drupal::service('entity_field.manager')->getFieldDefinitions('paragraph', $bundle);
  foreach ($fields as $field => $weight) {
    if (!isset($defs[$field])) {
      echo "  SKIP (missing field): {$bundle}.{$field}\n";
      continue;
    }
    // The long answer/description fields deserve a textarea.
    $type = in_array($field, ['field_faq_answer', 'field_card_desc'], TRUE)
      ? 'string_textarea'
      : 'string_textfield';
    $display->setComponent($field, ['type' => $type, 'weight' => $weight]);
  }
  $display->save();
  echo "paragraph form display: {$bundle} (" . count($fields) . " fields)\n";
}

// ---------------------------------------------------------------------------
// Minimal view displays — headless, but their absence warns in admin previews
// ---------------------------------------------------------------------------

$view = kb_display('entity_view_display', 'node', 'product');
foreach (['field_product_code' => 1, 'field_short_desc' => 2, 'field_images' => 3] as $field => $weight) {
  if (isset($definitions[$field])) {
    $view->setComponent($field, ['label' => 'above', 'weight' => $weight]);
  }
}
$view->save();
echo "product view display: saved\n";

foreach (KB_PARAGRAPH_FORMS as $bundle => $fields) {
  $display = kb_display('entity_view_display', 'paragraph', $bundle);
  $defs = \Drupal::service('entity_field.manager')->getFieldDefinitions('paragraph', $bundle);
  foreach ($fields as $field => $weight) {
    if (isset($defs[$field])) {
      $display->setComponent($field, ['label' => 'hidden', 'weight' => $weight]);
    }
  }
  $display->save();
}
echo "paragraph view displays: saved\n";

echo "\nDone. Edit a product at /node/add/product to confirm.\n";
