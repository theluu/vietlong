<?php

/**
 * @file
 * Content model for the homepage.
 *
 * Every one of its sections was hard-coded in utils/homeContent.ts, which
 * meant the most important page on the site was the only one an editor could
 * not touch. A singleton node with one tab per section on the page.
 *
 * Safe to run repeatedly.
 *
 * Run: ddev drush php:script scripts/setup/install_home_model.php
 */

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;
use Drupal\paragraphs\Entity\ParagraphsType;

function kbh_node_type(string $id, string $label): void {
  if (!NodeType::load($id)) {
    NodeType::create(['type' => $id, 'name' => $label, 'new_revision' => TRUE])->save();
    echo "node type: {$id}\n";
  }
}

function kbh_paragraph(string $id, string $label): void {
  if (!ParagraphsType::load($id)) {
    ParagraphsType::create(['id' => $id, 'label' => $label])->save();
    echo "paragraph type: {$id}\n";
  }
}

function kbh_field(string $entity_type, string $bundle, string $name, string $type, string $label, int $cardinality = 1, array $settings = [], array $instance = [], string $description = ''): void {
  if (!FieldStorageConfig::loadByName($entity_type, $name)) {
    FieldStorageConfig::create([
      'field_name' => $name, 'entity_type' => $entity_type,
      'type' => $type, 'cardinality' => $cardinality, 'settings' => $settings,
    ])->save();
  }
  if (!FieldConfig::loadByName($entity_type, $bundle, $name)) {
    FieldConfig::create([
      'field_name' => $name, 'entity_type' => $entity_type, 'bundle' => $bundle,
      'label' => $label, 'description' => $description, 'settings' => $instance,
    ])->save();
    echo "  field: {$bundle}.{$name}\n";
  }
}

function kbh_paragraph_ref(string $bundle, string $name, string $label, array $targets, string $description = ''): void {
  kbh_field('node', $bundle, $name, 'entity_reference_revisions', $label, -1,
    ['target_type' => 'paragraph'],
    ['handler' => 'default:paragraph', 'handler_settings' => [
      'target_bundles' => array_combine($targets, $targets), 'negate' => 0,
    ]], $description);
}

// ---------------------------------------------------------------------------
// Paragraph types
// ---------------------------------------------------------------------------

kbh_paragraph('hero_stat', 'Số liệu hero');
kbh_field('paragraph', 'hero_stat', 'field_stat_value', 'string', 'Con số');
kbh_field('paragraph', 'hero_stat', 'field_stat_label', 'string', 'Nhãn');

kbh_paragraph('usp', 'Điểm tin cậy');
kbh_field('paragraph', 'usp', 'field_usp_title', 'string', 'Tiêu đề');
kbh_field('paragraph', 'usp', 'field_usp_desc', 'string', 'Mô tả');

kbh_paragraph('solution', 'Giải pháp theo công trình');
kbh_field('paragraph', 'solution', 'field_sol_title', 'string', 'Tiêu đề');
kbh_field('paragraph', 'solution', 'field_sol_desc', 'string_long', 'Mô tả');
kbh_field('paragraph', 'solution', 'field_sol_image', 'image', 'Ảnh');
kbh_field('paragraph', 'solution', 'field_sol_tags', 'string', 'Thẻ', -1, [], [], 'Mỗi dòng một thẻ, ví dụ: Bảo mật cao');
kbh_field('paragraph', 'solution', 'field_sol_link', 'link', 'Link');

kbh_paragraph('featured_tab', 'Tab sản phẩm nổi bật');
kbh_field('paragraph', 'featured_tab', 'field_tab_key', 'string', 'Mã nhóm', 1, [], [], 'Phải khớp giá trị "Nhóm nổi bật" trên sản phẩm: dong, cremone, hotel, phukien');
kbh_field('paragraph', 'featured_tab', 'field_tab_label', 'string', 'Nhãn hiển thị');

// ---------------------------------------------------------------------------
// The singleton
// ---------------------------------------------------------------------------

kbh_node_type('home_page', 'Trang chủ');

// Hero
kbh_field('node', 'home_page', 'field_hero_eyebrow', 'string', 'Hero — eyebrow');
// string_long, not string: the headline runs across three lines and a
// single-line widget silently strips the newlines on save.
kbh_field('node', 'home_page', 'field_hero_title', 'string_long', 'Hero — tiêu đề', 1, [], [], 'Xuống dòng bằng Enter. Bọc từ cần tô màu vàng gradient trong dấu *, ví dụ: Khóa cửa *đẳng cấp* cho từng công trình');
kbh_field('node', 'home_page', 'field_hero_subtitle', 'string_long', 'Hero — mô tả');
kbh_field('node', 'home_page', 'field_hero_cta1', 'link', 'Hero — nút chính');
kbh_field('node', 'home_page', 'field_hero_cta2', 'link', 'Hero — nút phụ');
kbh_paragraph_ref('home_page', 'field_hero_stats', 'Hero — số liệu', ['hero_stat']);

// Dải tin cậy
kbh_paragraph_ref('home_page', 'field_usps', 'Dải tin cậy', ['usp']);

// Tiêu đề các khối
kbh_field('node', 'home_page', 'field_cat_eyebrow', 'string', 'Danh mục — eyebrow');
kbh_field('node', 'home_page', 'field_cat_title', 'string', 'Danh mục — tiêu đề');
kbh_field('node', 'home_page', 'field_feat_eyebrow', 'string', 'Sản phẩm nổi bật — eyebrow');
kbh_field('node', 'home_page', 'field_feat_title', 'string', 'Sản phẩm nổi bật — tiêu đề');
kbh_paragraph_ref('home_page', 'field_feat_tabs', 'Sản phẩm nổi bật — các tab', ['featured_tab']);

// Giải pháp
kbh_field('node', 'home_page', 'field_sol_eyebrow', 'string', 'Giải pháp — eyebrow');
kbh_field('node', 'home_page', 'field_sol_title', 'string', 'Giải pháp — tiêu đề');
kbh_paragraph_ref('home_page', 'field_solutions', 'Giải pháp — danh sách', ['solution']);

// Công nghệ
kbh_field('node', 'home_page', 'field_tech_eyebrow', 'string', 'Công nghệ — eyebrow');
kbh_field('node', 'home_page', 'field_tech_title', 'string', 'Công nghệ — tiêu đề');
kbh_field('node', 'home_page', 'field_tech_desc', 'string_long', 'Công nghệ — mô tả');
kbh_field('node', 'home_page', 'field_tech_features', 'string', 'Công nghệ — đặc điểm', -1, [], [], 'Mỗi dòng một đặc điểm');
kbh_field('node', 'home_page', 'field_tech_image', 'image', 'Công nghệ — ảnh');
kbh_field('node', 'home_page', 'field_tech_cta', 'link', 'Công nghệ — nút');

// Form tư vấn
kbh_field('node', 'home_page', 'field_consult_eyebrow', 'string', 'Tư vấn — eyebrow');
kbh_field('node', 'home_page', 'field_consult_title', 'string', 'Tư vấn — tiêu đề');
kbh_field('node', 'home_page', 'field_consult_desc', 'string_long', 'Tư vấn — mô tả');

// SEO riêng cho trang chủ
kbh_field('node', 'home_page', 'field_seo_title', 'string', 'SEO — tiêu đề');
kbh_field('node', 'home_page', 'field_seo_desc', 'string_long', 'SEO — mô tả');

echo "\nDone.\n";
