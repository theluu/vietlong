<?php

/**
 * @file
 * Content model for the site-wide chrome: the bar above the header, the
 * hotline, the footer and the social links.
 *
 * All of it used to be hard-coded in the Nuxt app, which meant changing a
 * phone number needed a developer and a deploy. It is a singleton node so an
 * editor edits one page rather than hunting through configuration forms.
 *
 * The main menu is deliberately NOT here — Drupal's own menu system already
 * does that job and editors know it.
 *
 * Safe to run repeatedly.
 *
 * Run: ddev drush php:script scripts/setup/install_site_settings.php
 */

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;
use Drupal\paragraphs\Entity\ParagraphsType;

function kbs_node_type(string $id, string $label): void {
  if (!NodeType::load($id)) {
    NodeType::create(['type' => $id, 'name' => $label, 'new_revision' => TRUE])->save();
    echo "node type: {$id}\n";
  }
}

function kbs_paragraph(string $id, string $label): void {
  if (!ParagraphsType::load($id)) {
    ParagraphsType::create(['id' => $id, 'label' => $label])->save();
    echo "paragraph type: {$id}\n";
  }
}

function kbs_field(
  string $entity_type,
  string $bundle,
  string $name,
  string $type,
  string $label,
  int $cardinality = 1,
  array $settings = [],
  array $instance = [],
  string $description = '',
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
      'description' => $description,
      'settings' => $instance,
    ])->save();
    echo "  field: {$bundle}.{$name}\n";
  }
}

function kbs_paragraph_ref(string $bundle, string $name, string $label, array $targets, string $description = ''): void {
  kbs_field(
    'node', $bundle, $name, 'entity_reference_revisions', $label, -1,
    ['target_type' => 'paragraph'],
    ['handler' => 'default:paragraph', 'handler_settings' => [
      'target_bundles' => array_combine($targets, $targets),
      'negate' => 0,
    ]],
    $description,
  );
}

// ---------------------------------------------------------------------------
// Paragraph types
// ---------------------------------------------------------------------------

kbs_paragraph('social_link', 'Mạng xã hội');
kbs_field('paragraph', 'social_link', 'field_social_label', 'string', 'Tên hiển thị');
kbs_field('paragraph', 'social_link', 'field_social_url', 'link', 'Đường dẫn');
kbs_field('paragraph', 'social_link', 'field_social_icon', 'list_string', 'Biểu tượng', 1, [
  'allowed_values' => [
    'facebook' => 'Facebook',
    'youtube' => 'YouTube',
    'zalo' => 'Zalo',
    'tiktok' => 'TikTok',
    'instagram' => 'Instagram',
    'linkedin' => 'LinkedIn',
  ],
]);

kbs_paragraph('footer_link', 'Link ở footer');
kbs_field('paragraph', 'footer_link', 'field_flink_label', 'string', 'Nhãn');
kbs_field('paragraph', 'footer_link', 'field_flink_url', 'link', 'Đường dẫn');

kbs_paragraph('footer_column', 'Cột footer');
kbs_field('paragraph', 'footer_column', 'field_fcol_title', 'string', 'Tiêu đề cột');
kbs_field(
  'paragraph', 'footer_column', 'field_fcol_links', 'entity_reference_revisions', 'Các link', -1,
  ['target_type' => 'paragraph'],
  ['handler' => 'default:paragraph', 'handler_settings' => ['target_bundles' => ['footer_link' => 'footer_link'], 'negate' => 0]],
);

// ---------------------------------------------------------------------------
// The singleton
// ---------------------------------------------------------------------------

kbs_node_type('site_settings', 'Cấu hình chung');

// Thanh trên cùng
kbs_field('node', 'site_settings', 'field_topbar_text', 'string', 'Thanh trên — dòng chữ trái', 1, [], [], 'Ví dụ: Nhà nhập khẩu & phân phối khóa cửa cao cấp — Bắc Ninh · Phú Thọ · Vĩnh Phúc');
kbs_field('node', 'site_settings', 'field_topbar_badges', 'string', 'Thanh trên — các nhãn phải', -1, [], [], 'Mỗi dòng một nhãn. Ví dụ: Chứng nhận CE-CFF');

// Liên hệ dùng chung toàn site
kbs_field('node', 'site_settings', 'field_hotline', 'string', 'Hotline (hiển thị)', 1, [], [], 'Ví dụ: 1900 9018');
kbs_field('node', 'site_settings', 'field_hotline_tel', 'string', 'Hotline (số gọi)', 1, [], [], 'Chỉ chữ số, dùng cho nút gọi. Ví dụ: 19009018');
kbs_field('node', 'site_settings', 'field_email', 'string', 'Email');
kbs_field('node', 'site_settings', 'field_company_name', 'string', 'Tên công ty (pháp nhân)');
kbs_field('node', 'site_settings', 'field_company_short', 'string', 'Tên rút gọn');
kbs_field('node', 'site_settings', 'field_address', 'string_long', 'Địa chỉ');
kbs_field('node', 'site_settings', 'field_working_hours', 'string', 'Giờ làm việc', -1, [], [], 'Mỗi dòng một mục. Ví dụ: Thứ 2 – Thứ 7: 8:00 – 18:00');

// Header
kbs_field('node', 'site_settings', 'field_header_tagline', 'string', 'Header — dòng mô tả dưới logo');
kbs_field('node', 'site_settings', 'field_header_cta', 'link', 'Header — nút CTA');

// Footer
kbs_field('node', 'site_settings', 'field_footer_desc', 'string_long', 'Footer — mô tả công ty');
kbs_field('node', 'site_settings', 'field_copyright', 'string', 'Dòng bản quyền');
kbs_paragraph_ref('site_settings', 'field_footer_columns', 'Footer — các cột link', ['footer_column']);
kbs_paragraph_ref('site_settings', 'field_social', 'Mạng xã hội', ['social_link']);

// SEO mặc định
kbs_field('node', 'site_settings', 'field_seo_title', 'string', 'SEO — tiêu đề mặc định');
kbs_field('node', 'site_settings', 'field_seo_desc', 'string_long', 'SEO — mô tả mặc định');

echo "\nDone.\n";
