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
use Drupal\paragraphs\Entity\ParagraphsType;

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

/**
 * Creates an unlimited paragraph reference field restricted to given bundles.
 */
function kbp_paragraph_ref(string $bundle, string $name, string $label, array $targets): void {
  kbp_field(
    'node', $bundle, $name, 'entity_reference_revisions', $label, -1,
    ['target_type' => 'paragraph'],
    ['handler' => 'default:paragraph', 'handler_settings' => [
      'target_bundles' => array_combine($targets, $targets),
      'negate' => 0,
    ]],
  );
}

/**
 * Creates a paragraph type when missing.
 */
function kbp_paragraph(string $id, string $label): void {
  if (!ParagraphsType::load($id)) {
    ParagraphsType::create(['id' => $id, 'label' => $label])->save();
    echo "paragraph type: {$id}\n";
  }
}

// Leads from the three site forms. A plain content type so they sit in
// /admin/content with everything else — no bespoke entity, no bespoke
// permission. Saved unpublished so they never surface on the front end.
kbp_node_type('lead', 'Yêu cầu liên hệ');
kbp_field('node', 'lead', 'field_lead_phone', 'string', 'Số điện thoại');
kbp_field('node', 'lead', 'field_lead_email', 'string', 'Email');
kbp_field('node', 'lead', 'field_lead_message', 'string_long', 'Nội dung');
kbp_field('node', 'lead', 'field_lead_source', 'list_string', 'Nguồn', 1, [
  'allowed_values' => [
    'contact' => 'Liên hệ',
    'dealer' => 'Đăng ký đại lý',
    'consult' => 'Tư vấn trang chủ',
  ],
]);
kbp_field('node', 'lead', 'field_lead_ip', 'string', 'IP');
kbp_field('node', 'lead', 'field_lead_recaptcha', 'decimal', 'Điểm reCAPTCHA');

kbp_node_type('branch', 'Cơ sở / Showroom');

kbp_field('node', 'branch', 'field_tag', 'string', 'Nhãn (Bán buôn, Cơ sở 1…)');
kbp_field('node', 'branch', 'field_address', 'string_long', 'Địa chỉ');
kbp_field('node', 'branch', 'field_phone_display', 'string', 'Điện thoại (hiển thị)');
kbp_field('node', 'branch', 'field_phone_tel', 'string', 'Điện thoại (số gọi)');
kbp_field('node', 'branch', 'field_map_url', 'link', 'Link chỉ đường');
kbp_field('node', 'branch', 'field_sort_order', 'integer', 'Thứ tự');

kbp_paragraph('fact', 'Con số');
kbp_field('paragraph', 'fact', 'field_fact_number', 'string', 'Con số');
kbp_field('paragraph', 'fact', 'field_fact_label', 'string', 'Nhãn');

// Serves both the About process steps and the Dealers benefits: same shape.
kbp_paragraph('numbered_item', 'Mục có số thứ tự');
kbp_field('paragraph', 'numbered_item', 'field_item_number', 'string', 'Số');
kbp_field('paragraph', 'numbered_item', 'field_item_title', 'string', 'Tiêu đề');
kbp_field('paragraph', 'numbered_item', 'field_item_desc', 'string_long', 'Mô tả');

kbp_paragraph('segment', 'Nhóm khách hàng');
kbp_field('paragraph', 'segment', 'field_seg_title', 'string', 'Tiêu đề');
kbp_field('paragraph', 'segment', 'field_seg_desc', 'string_long', 'Mô tả');
kbp_field('paragraph', 'segment', 'field_seg_cta', 'link', 'Nút');
kbp_field('paragraph', 'segment', 'field_seg_image', 'image', 'Ảnh');

kbp_paragraph('value_item', 'Cam kết');
kbp_field('paragraph', 'value_item', 'field_value_title', 'string', 'Tiêu đề');
kbp_field('paragraph', 'value_item', 'field_value_desc', 'string_long', 'Mô tả');

kbp_node_type('about_page', 'Trang Giới thiệu');

kbp_field('node', 'about_page', 'field_eyebrow', 'string', 'Eyebrow');
kbp_field('node', 'about_page', 'field_subtitle', 'string_long', 'Mô tả ngắn');
kbp_field('node', 'about_page', 'field_hero_image', 'image', 'Ảnh hero');
kbp_field('node', 'about_page', 'field_hero_caption', 'string', 'Chú thích ảnh');
kbp_field('node', 'about_page', 'field_cta_primary', 'link', 'Nút chính');
kbp_field('node', 'about_page', 'field_cta_secondary', 'link', 'Nút phụ');

kbp_field('node', 'about_page', 'field_story_eyebrow', 'string', 'Câu chuyện — eyebrow');
kbp_field('node', 'about_page', 'field_story_title', 'string', 'Câu chuyện — tiêu đề');
kbp_field('node', 'about_page', 'field_story_body', 'text_long', 'Câu chuyện — nội dung');
kbp_field('node', 'about_page', 'field_credentials', 'string', 'Chứng nhận', -1);

kbp_paragraph_ref('about_page', 'field_facts', 'Con số', ['fact']);
kbp_paragraph_ref('about_page', 'field_segments', 'Nhóm khách hàng', ['segment']);
kbp_paragraph_ref('about_page', 'field_steps', 'Quy trình', ['numbered_item']);
kbp_paragraph_ref('about_page', 'field_values', 'Cam kết', ['value_item']);

kbp_node_type('dealers_page', 'Trang Đại lý');
kbp_field('node', 'dealers_page', 'field_eyebrow', 'string', 'Eyebrow');
kbp_field('node', 'dealers_page', 'field_subtitle', 'string_long', 'Mô tả ngắn');
kbp_field('node', 'dealers_page', 'field_criteria', 'string', 'Điều kiện', -1);
kbp_field('node', 'dealers_page', 'field_form_title', 'string', 'Form — tiêu đề');
kbp_field('node', 'dealers_page', 'field_form_desc', 'string_long', 'Form — mô tả');
kbp_field('node', 'dealers_page', 'field_success_title', 'string', 'Form — tiêu đề thành công');
kbp_field('node', 'dealers_page', 'field_success_desc', 'string_long', 'Form — mô tả thành công');
kbp_paragraph_ref('dealers_page', 'field_benefits', 'Quyền lợi', ['numbered_item']);

kbp_paragraph('contact_channel', 'Kênh liên hệ');
kbp_field('paragraph', 'contact_channel', 'field_ch_label', 'string', 'Nhãn');
kbp_field('paragraph', 'contact_channel', 'field_ch_value', 'string', 'Giá trị');
kbp_field('paragraph', 'contact_channel', 'field_ch_note', 'string', 'Ghi chú');
kbp_field('paragraph', 'contact_channel', 'field_ch_url', 'link', 'Liên kết');

kbp_node_type('contact_page', 'Trang Liên hệ');
kbp_field('node', 'contact_page', 'field_eyebrow', 'string', 'Eyebrow');
kbp_field('node', 'contact_page', 'field_subtitle', 'string_long', 'Mô tả ngắn');
kbp_field('node', 'contact_page', 'field_company_name', 'string', 'Tên công ty');
kbp_field('node', 'contact_page', 'field_company_address', 'string_long', 'Địa chỉ trụ sở');
kbp_field('node', 'contact_page', 'field_response_title', 'string', 'Khối phản hồi — tiêu đề');
kbp_field('node', 'contact_page', 'field_response_body', 'string_long', 'Khối phản hồi — nội dung');
kbp_field('node', 'contact_page', 'field_form_title', 'string', 'Form — tiêu đề');
kbp_field('node', 'contact_page', 'field_form_desc', 'string_long', 'Form — mô tả');
kbp_field('node', 'contact_page', 'field_success_title', 'string', 'Form — tiêu đề thành công');
kbp_field('node', 'contact_page', 'field_success_desc', 'string_long', 'Form — mô tả thành công');
kbp_paragraph_ref('contact_page', 'field_channels', 'Kênh liên hệ', ['contact_channel']);

kbp_paragraph('policy_item', 'Khoản mục chính sách');
kbp_field('paragraph', 'policy_item', 'field_pol_key', 'string', 'Tiêu đề khoản');
kbp_field('paragraph', 'policy_item', 'field_pol_value', 'string_long', 'Nội dung khoản');

kbp_paragraph('policy_section', 'Mục chính sách');
kbp_field('paragraph', 'policy_section', 'field_pol_label', 'string', 'Nhãn tab');
kbp_field('paragraph', 'policy_section', 'field_pol_eyebrow', 'string', 'Eyebrow');
kbp_field('paragraph', 'policy_section', 'field_pol_title', 'string', 'Tiêu đề');
kbp_field('paragraph', 'policy_section', 'field_pol_intro', 'string_long', 'Dẫn nhập');
kbp_field('paragraph', 'policy_section', 'field_pol_note', 'string_long', 'Ghi chú');
kbp_field(
  'paragraph', 'policy_section', 'field_pol_items', 'entity_reference_revisions', 'Khoản mục', -1,
  ['target_type' => 'paragraph'],
  ['handler' => 'default:paragraph', 'handler_settings' => [
    'target_bundles' => ['policy_item' => 'policy_item'], 'negate' => 0,
  ]],
);

kbp_node_type('policies_page', 'Trang Chính sách');
kbp_field('node', 'policies_page', 'field_eyebrow', 'string', 'Eyebrow');
kbp_field('node', 'policies_page', 'field_subtitle', 'string_long', 'Mô tả ngắn');
kbp_field('node', 'policies_page', 'field_support_title', 'string', 'Hộp hỗ trợ — tiêu đề');
kbp_field('node', 'policies_page', 'field_support_note', 'string', 'Hộp hỗ trợ — ghi chú');
kbp_paragraph_ref('policies_page', 'field_sections', 'Mục chính sách', ['policy_section']);

kbp_node_type('news_page', 'Trang Tin tức');
kbp_field('node', 'news_page', 'field_eyebrow', 'string', 'Eyebrow');
kbp_field('node', 'news_page', 'field_subtitle', 'string_long', 'Mô tả ngắn');

kbp_node_type('article', 'Bài viết');
kbp_field('node', 'article', 'field_article_category', 'string', 'Chuyên mục');
kbp_field('node', 'article', 'field_article_category_key', 'string', 'Mã chuyên mục');
kbp_field('node', 'article', 'field_article_summary', 'string_long', 'Tóm tắt');
kbp_field('node', 'article', 'field_article_read_time', 'string', 'Thời gian đọc');
kbp_field('node', 'article', 'field_article_image_url', 'string', 'URL ảnh');
kbp_field('node', 'article', 'field_article_slug', 'string', 'Slug');
kbp_field('node', 'article', 'field_sort_order', 'integer', 'Thứ tự');
kbp_field('node', 'article', 'field_article_author', 'string', 'Tác giả');
kbp_field('node', 'article', 'field_article_updated', 'string', 'Ngày cập nhật');
kbp_field('node', 'article', 'field_article_quick_answer', 'string_long', 'Trả lời nhanh');
kbp_field('node', 'article', 'field_article_sections', 'string_long', 'Các mục nội dung (JSON)');
kbp_field('node', 'article', 'field_article_compare', 'string_long', 'Bảng so sánh (JSON)');
kbp_field('node', 'article', 'field_article_faqs', 'string_long', 'Câu hỏi thường gặp (JSON)');
kbp_field('node', 'article', 'field_article_products', 'string_long', 'Sản phẩm nhắc đến (JSON slug)');

kbp_node_type('projects_page', 'Trang Dự án');
kbp_field('node', 'projects_page', 'field_eyebrow', 'string', 'Eyebrow');
kbp_field('node', 'projects_page', 'field_subtitle', 'string_long', 'Mô tả ngắn');

kbp_node_type('project', 'Dự án');
kbp_field('node', 'project', 'field_project_type', 'string', 'Loại công trình');
kbp_field('node', 'project', 'field_project_type_key', 'string', 'Mã loại công trình');
kbp_field('node', 'project', 'field_project_desc', 'string_long', 'Mô tả');
kbp_field('node', 'project', 'field_project_products', 'string', 'Sản phẩm / giải pháp');
kbp_field('node', 'project', 'field_project_image_url', 'string', 'URL ảnh');
kbp_field('node', 'project', 'field_project_slug', 'string', 'Slug');
kbp_field('node', 'project', 'field_sort_order', 'integer', 'Thứ tự');

echo "Done.\n";
