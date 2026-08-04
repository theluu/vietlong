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
  'home_page' => [
    'Hero' => ['field_hero_eyebrow' => 1, 'field_hero_title' => 2, 'field_hero_subtitle' => 3, 'field_hero_cta1' => 4, 'field_hero_cta2' => 5, 'field_hero_stats' => 6],
    'Dải tin cậy' => ['field_usps' => 1],
    'Danh mục' => ['field_cat_eyebrow' => 1, 'field_cat_title' => 2],
    'Sản phẩm nổi bật' => ['field_feat_eyebrow' => 1, 'field_feat_title' => 2, 'field_feat_tabs' => 3],
    'Giải pháp' => ['field_sol_eyebrow' => 1, 'field_sol_title' => 2, 'field_solutions' => 3],
    'Công nghệ' => ['field_tech_eyebrow' => 1, 'field_tech_title' => 2, 'field_tech_desc' => 3, 'field_tech_features' => 4, 'field_tech_image' => 5, 'field_tech_cta' => 6],
    'Form tư vấn' => ['field_consult_eyebrow' => 1, 'field_consult_title' => 2, 'field_consult_desc' => 3],
    'SEO' => ['field_seo_title' => 1, 'field_seo_desc' => 2],
  ],
  'site_settings' => [
    'Liên hệ' => ['field_hotline' => 1, 'field_hotline_tel' => 2, 'field_email' => 3, 'field_address' => 4, 'field_working_hours' => 5],
    'Công ty' => ['field_company_name' => 1, 'field_company_short' => 2, 'field_copyright' => 3],
    'Thanh trên & header' => ['field_topbar_text' => 1, 'field_topbar_badges' => 2, 'field_header_tagline' => 3, 'field_header_cta' => 4],
    'Footer' => ['field_footer_desc' => 1, 'field_footer_columns' => 2],
    'Mạng xã hội' => ['field_social' => 1],
    'SEO' => ['field_seo_title' => 1, 'field_seo_desc' => 2],
  ],
  'lead' => [
    'Khách hàng' => ['field_lead_phone' => 1, 'field_lead_email' => 2, 'field_lead_message' => 3],
    'Nguồn' => ['field_lead_source' => 1, 'field_lead_recaptcha' => 2, 'field_lead_ip' => 3],
  ],
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
  'news_page' => [
    'Hero' => ['field_eyebrow' => 1, 'field_subtitle' => 2],
  ],
  'article' => [
    'Nội dung thẻ' => ['field_article_category' => 1, 'field_article_category_key' => 2, 'field_article_summary' => 3, 'field_article_read_time' => 4, 'field_article_image_url' => 5, 'field_article_slug' => 6, 'field_sort_order' => 7],
    'Nội dung chi tiết' => ['field_article_author' => 1, 'field_article_updated' => 2, 'field_article_quick_answer' => 3, 'field_article_sections' => 4, 'field_article_compare' => 5, 'field_article_faqs' => 6, 'field_article_products' => 7],
  ],
  'projects_page' => [
    'Hero' => ['field_eyebrow' => 1, 'field_subtitle' => 2],
  ],
  'project' => [
    'Nội dung' => ['field_project_type' => 1, 'field_project_type_key' => 2, 'field_project_desc' => 3, 'field_project_products' => 4, 'field_project_image_url' => 5, 'field_project_slug' => 6, 'field_sort_order' => 7],
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

  // Drop grouping from an earlier run so renamed tabs do not linger.
  foreach (array_keys($display->getThirdPartySettings('field_group')) as $stale) {
    $display->unsetThirdPartySetting('field_group', $stale);
  }

  $group_weight = 1;
  $tab_names = [];
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
        'parent_name' => 'group_content_tabs',
        'region' => 'content',
        'weight' => $group_weight++,
        'format_type' => 'tab',
        'format_settings' => ['formatter' => 'closed', 'description' => '', 'required_fields' => TRUE],
      ]);
      $tab_names[] = $group_name;
    }
  }

  // A `tab` group only renders as a tab when it hangs off a `tabs` parent.
  // Without this wrapper field_group falls back to stacked collapsed details,
  // which is what these forms were doing.
  if ($has_group && $tab_names) {
    $display->setThirdPartySetting('field_group', 'group_content_tabs', [
      'children' => $tab_names,
      'label' => 'Nội dung',
      'parent_name' => '',
      'region' => 'content',
      'weight' => 0,
      'format_type' => 'tabs',
      'format_settings' => ['direction' => 'horizontal', 'width_breakpoint' => 640],
    ]);
  }
  $display->save();
  echo "form display: {$bundle}" . ($has_group ? ' (tabs)' : ' (flat — field_group missing)') . "\n";
}

echo "\nDone.\n";

/**
 * Paragraph forms for the site-wide chrome. Without these the rows render
 * empty, the same trap the product paragraphs hit.
 */
$paragraph_forms = [
  'hero_stat' => ['field_stat_value' => 1, 'field_stat_label' => 2],
  'usp' => ['field_usp_title' => 1, 'field_usp_desc' => 2],
  'solution' => ['field_sol_title' => 1, 'field_sol_desc' => 2, 'field_sol_image' => 3, 'field_sol_tags' => 4, 'field_sol_link' => 5],
  'featured_tab' => ['field_tab_key' => 1, 'field_tab_label' => 2],
  'social_link' => ['field_social_label' => 1, 'field_social_icon' => 2, 'field_social_url' => 3],
  'footer_link' => ['field_flink_label' => 1, 'field_flink_url' => 2],
  'footer_column' => ['field_fcol_title' => 1, 'field_fcol_links' => 2],
];

foreach ($paragraph_forms as $bundle => $fields) {
  $storage = $etm->getStorage('entity_form_display');
  $id = "paragraph.{$bundle}.default";
  $display = $storage->load($id) ?: $storage->create([
    'targetEntityType' => 'paragraph', 'bundle' => $bundle, 'mode' => 'default', 'status' => TRUE,
  ]);
  $defs = $field_manager->getFieldDefinitions('paragraph', $bundle);
  foreach ($fields as $field => $weight) {
    if (!isset($defs[$field])) {
      continue;
    }
    $display->setComponent($field, [
      'type' => kbp_widget($defs[$field]->getType()),
      'weight' => $weight,
    ]);
  }
  $display->save();
  echo "paragraph form display: {$bundle}\n";
}
