<?php

/**
 * @file
 * Configures the editor-facing form displays for the product model.
 *
 * The product form has 36 components. As one flat column it is unusable: the
 * fields an editor touches on every product sit below ones they touch once a
 * year, and there is no visual break between identity, merchandising and
 * detail-page copy. They are grouped into horizontal tabs here, ordered the
 * way a product actually gets filled in.
 *
 * Also sets a minimal view display: the site is headless, so nothing renders
 * these node pages, but a missing view display makes admin previews warn.
 *
 * Safe to run repeatedly.
 *
 * Run: ddev drush php:script scripts/setup/install_product_displays.php
 */

/**
 * Tabs, in the order a product gets filled in.
 *
 * label => [field_name => [widget_id, settings]]
 *
 * The first tab is what an editor needs for a product to exist and look right
 * in a listing. Everything after it is progressive detail.
 */
const KB_PRODUCT_TABS = [
  'Cơ bản' => [
    'field_product_code'  => ['string_textfield', []],
    'field_family'        => ['string_textfield', []],
    'field_short_desc'    => ['string_textarea', ['rows' => 3]],
    'field_images'        => ['image_image', ['preview_image_style' => 'thumbnail']],
  ],
  'Phân loại' => [
    'field_brand'         => ['entity_reference_autocomplete', []],
    'field_category'      => ['entity_reference_autocomplete', []],
    'field_finish'        => ['entity_reference_autocomplete', []],
    'field_size_key'      => ['string_textfield', []],
    'field_size_label'    => ['string_textfield', []],
    'field_size_note'     => ['string_textfield', []],
  ],
  'Hiển thị & kho' => [
    'field_badge'         => ['options_select', []],
    'field_stock_status'  => ['options_select', []],
    'field_contact_price' => ['boolean_checkbox', []],
    'field_featured'      => ['boolean_checkbox', []],
    'field_featured_group' => ['options_select', []],
    'field_is_new'        => ['boolean_checkbox', []],
    'field_sort_order'    => ['number', []],
  ],
  'Mô tả chi tiết' => [
    'field_desc_heading'  => ['string_textfield', []],
    'field_description'   => ['text_textarea', ['rows' => 12]],
    'field_highlights'    => ['string_textarea', ['rows' => 4]],
  ],
  'Thông số' => [
    'field_specifications' => ['paragraphs', []],
    'field_door_thickness' => ['string_textfield', []],
    'field_origin'         => ['string_textfield', []],
    'field_warranty'       => ['string_textfield', []],
    'field_certification'  => ['string_textfield', []],
  ],
  'FAQ & chính sách' => [
    'field_faqs'          => ['paragraphs', []],
    'field_policy_cards'  => ['paragraphs', []],
  ],
  'Sản phẩm liên quan' => [
    'field_related_products' => ['entity_reference_autocomplete', []],
  ],
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
$has_group = \Drupal::moduleHandler()->moduleExists('field_group');

// Title and the published flag stay outside the tabs: they apply to the whole
// node, and hunting for "Published" inside a tab is a known way to lose edits.
$form->setComponent('title', ['type' => 'string_textfield', 'weight' => -5]);
$form->setComponent('status', ['type' => 'boolean_checkbox', 'weight' => 100]);

// Clear any grouping from an earlier run so renamed tabs do not linger.
foreach (array_keys($form->getThirdPartySettings('field_group')) as $stale) {
  $form->unsetThirdPartySetting('field_group', $stale);
}

$applied = 0;
$tab_weight = 0;
$tab_names = [];

foreach (KB_PRODUCT_TABS as $label => $fields) {
  $children = [];
  $weight = 0;
  foreach ($fields as $field => [$widget, $settings]) {
    if (!isset($definitions[$field])) {
      echo "  SKIP (missing field): {$field}\n";
      continue;
    }
    $component = ['type' => $widget, 'weight' => $weight++];
    if ($settings) {
      $component['settings'] = $settings;
    }
    // Long paragraph lists are far easier to scan collapsed to one summary
    // line each than as a column of expanded sub-forms.
    if ($widget === 'paragraphs') {
      $component['settings'] = [
        'edit_mode' => 'closed',
        'closed_mode' => 'summary',
        'add_mode' => 'button',
        'form_display_mode' => 'default',
      ];
    }
    $form->setComponent($field, $component);
    $children[] = $field;
    $applied++;
  }
  if (!$children) {
    continue;
  }
  if ($has_group) {
    $name = 'group_' . preg_replace('/[^a-z0-9]+/', '_', mb_strtolower($label));
    $name = rtrim($name, '_');
    $form->setThirdPartySetting('field_group', $name, [
      'children' => $children,
      'label' => $label,
      'parent_name' => 'group_product_tabs',
      'region' => 'content',
      'weight' => $tab_weight++,
      'format_type' => 'tab',
      'format_settings' => ['formatter' => 'closed', 'description' => '', 'required_fields' => TRUE],
    ]);
    $tab_names[] = $name;
  }
}

// A `tab` group only renders as a tab when it hangs off a `tabs` parent;
// without this wrapper field_group falls back to stacked collapsed details.
if ($has_group && $tab_names) {
  $form->setThirdPartySetting('field_group', 'group_product_tabs', [
    'children' => $tab_names,
    'label' => 'Nội dung sản phẩm',
    'parent_name' => '',
    'region' => 'content',
    'weight' => 0,
    'format_type' => 'tabs',
    'format_settings' => ['direction' => 'horizontal', 'width_breakpoint' => 640],
  ]);
}

foreach (KB_PRODUCT_FORM_HIDDEN as $field) {
  $form->removeComponent($field);
}

$form->save();
echo 'product form display: ' . $applied . ' fields in '
  . ($has_group ? count($tab_names) . ' tabs' : 'a flat list (field_group missing)') . "\n";

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
