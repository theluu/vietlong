<?php

/**
 * @file
 * Creates the Keybolts product content model. Safe to run repeatedly.
 *
 * Run: ddev drush php:script scripts/setup/install_product_model.php
 */

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;
use Drupal\paragraphs\Entity\ParagraphsType;
use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Creates a vocabulary when it is missing.
 */
function kb_vocabulary(string $id, string $label): void {
  if (!Vocabulary::load($id)) {
    Vocabulary::create(['vid' => $id, 'name' => $label])->save();
    echo "vocabulary: {$id}\n";
  }
}

/**
 * Creates a field storage + instance when missing.
 *
 * @param array $settings
 *   Storage settings, e.g. target_type for entity_reference.
 * @param array $instance
 *   Instance settings, e.g. handler_settings for entity_reference.
 */
function kb_field(
  string $entity_type,
  string $bundle,
  string $name,
  string $type,
  string $label,
  int $cardinality = 1,
  array $settings = [],
  array $instance = [],
  bool $required = FALSE,
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
      'required' => $required,
      'settings' => $instance,
    ])->save();
    echo "field: {$bundle}.{$name}\n";
  }
}

// --- Vocabularies -----------------------------------------------------------
kb_vocabulary('brand', 'Thương hiệu');
kb_vocabulary('product_category', 'Danh mục sản phẩm');
kb_vocabulary('finish', 'Hoàn thiện');

// Vocabulary fields.
kb_field('taxonomy_term', 'brand', 'field_tag', 'string', 'Tag');
kb_field('taxonomy_term', 'brand', 'field_cta_label', 'string', 'Nhãn CTA');
kb_field('taxonomy_term', 'product_category', 'field_number', 'string', 'Số thứ tự');
kb_field('taxonomy_term', 'product_category', 'field_short_desc', 'string_long', 'Mô tả ngắn');
kb_field('taxonomy_term', 'product_category', 'field_image', 'image', 'Ảnh');
kb_field('taxonomy_term', 'finish', 'field_swatch', 'string', 'Mã màu');
kb_field('taxonomy_term', 'finish', 'field_suffix', 'string', 'Hậu tố mã');

// --- Content type -----------------------------------------------------------
if (!NodeType::load('product')) {
  NodeType::create(['type' => 'product', 'name' => 'Sản phẩm'])->save();
  echo "content type: product\n";
}

$ref = fn(string $vid) => [
  'handler' => 'default:taxonomy_term',
  'handler_settings' => ['target_bundles' => [$vid => $vid]],
];

// Identity and grouping.
kb_field('node', 'product', 'field_product_code', 'string', 'Mã sản phẩm', 1, [], [], TRUE);
kb_field('node', 'product', 'field_family', 'string', 'Dòng sản phẩm');
kb_field('node', 'product', 'field_size_key', 'string', 'Mã size');
kb_field('node', 'product', 'field_size_label', 'string', 'Tên size');
kb_field('node', 'product', 'field_size_note', 'string', 'Ghi chú size');
kb_field('node', 'product', 'field_search_text', 'string_long', 'Chuỗi tìm kiếm');

// Classification.
kb_field('node', 'product', 'field_brand', 'entity_reference', 'Thương hiệu', 1, ['target_type' => 'taxonomy_term'], $ref('brand'), TRUE);
kb_field('node', 'product', 'field_category', 'entity_reference', 'Danh mục', 1, ['target_type' => 'taxonomy_term'], $ref('product_category'), TRUE);
kb_field('node', 'product', 'field_finish', 'entity_reference', 'Hoàn thiện', 1, ['target_type' => 'taxonomy_term'], $ref('finish'));

// Media.
kb_field('node', 'product', 'field_images', 'image', 'Ảnh sản phẩm', 12);

// Merchandising.
kb_field('node', 'product', 'field_badge', 'list_string', 'Nhãn', 1, [
  'allowed_values' => ['ban-chay' => 'Bán chạy', 'moi' => 'Mới', 'cao-cap' => 'Cao cấp'],
]);
kb_field('node', 'product', 'field_stock_status', 'list_string', 'Tình trạng', 1, [
  'allowed_values' => [
    'con-hang' => 'Còn hàng — giao 2–5 ngày',
    'het-hang' => 'Hết hàng',
    'dat-truoc' => 'Đặt trước',
  ],
]);
kb_field('node', 'product', 'field_featured', 'boolean', 'Nổi bật');
kb_field('node', 'product', 'field_featured_group', 'list_string', 'Nhóm nổi bật', 1, [
  'allowed_values' => [
    'dong' => 'Khoá đồng nhập khẩu',
    'cremone' => 'CREMONE chốt khoá',
    'hotel' => 'Khoá khách sạn',
    'phukien' => 'Phụ kiện khác',
  ],
]);
kb_field('node', 'product', 'field_is_new', 'boolean', 'Sản phẩm mới');
kb_field('node', 'product', 'field_sort_order', 'integer', 'Thứ tự');
kb_field('node', 'product', 'field_contact_price', 'boolean', 'Liên hệ để biết giá');

// Descriptive content.
kb_field('node', 'product', 'field_short_desc', 'string_long', 'Mô tả ngắn', 1, [], [], TRUE);
kb_field('node', 'product', 'field_desc_heading', 'string', 'Tiêu đề mô tả');
kb_field('node', 'product', 'field_description', 'text_long', 'Mô tả chi tiết');
kb_field('node', 'product', 'field_highlights', 'string_long', 'Điểm nổi bật', -1);

// Technical attributes.
kb_field('node', 'product', 'field_door_thickness', 'string', 'Độ dày cửa');
kb_field('node', 'product', 'field_origin', 'string', 'Xuất xứ');
kb_field('node', 'product', 'field_certification', 'string', 'Chứng nhận', -1);
kb_field('node', 'product', 'field_warranty', 'string', 'Bảo hành');

// Relations.
kb_field('node', 'product', 'field_related_products', 'entity_reference', 'Sản phẩm liên quan', -1,
  ['target_type' => 'node'],
  ['handler' => 'default:node', 'handler_settings' => ['target_bundles' => ['product' => 'product']]]
);

// --- Paragraph types for the detail-page tabs --------------------------------
// The design's detail page has four tabs; "Thông số kỹ thuật" and "Hỏi đáp"
// need repeatable key/value and question/answer rows.
foreach ([
  'spec_item' => ['label' => 'Thông số', 'fields' => ['field_spec_key' => 'Tên', 'field_spec_value' => 'Giá trị']],
  'faq_item' => ['label' => 'Hỏi đáp', 'fields' => ['field_faq_question' => 'Câu hỏi', 'field_faq_answer' => 'Trả lời']],
  'policy_card' => ['label' => 'Thẻ chính sách', 'fields' => ['field_card_title' => 'Tiêu đề', 'field_card_desc' => 'Mô tả']],
] as $id => $info) {
  if (!ParagraphsType::load($id)) {
    ParagraphsType::create(['id' => $id, 'label' => $info['label']])->save();
    echo "paragraph type: {$id}\n";
  }
  foreach ($info['fields'] as $field => $label) {
    // Answers and descriptions are long-form; keys and titles are single-line.
    $type = str_contains($field, 'answer') || str_contains($field, 'desc') ? 'string_long' : 'string';
    kb_field('paragraph', $id, $field, $type, $label);
  }
}

$para_ref = fn(string $bundle) => [
  'handler' => 'default:paragraph',
  'handler_settings' => [
    'target_bundles' => [$bundle => $bundle],
    'negate' => 0,
  ],
];

kb_field('node', 'product', 'field_specifications', 'entity_reference_revisions', 'Thông số kỹ thuật', -1,
  ['target_type' => 'paragraph'], $para_ref('spec_item'));
kb_field('node', 'product', 'field_faqs', 'entity_reference_revisions', 'Hỏi đáp', -1,
  ['target_type' => 'paragraph'], $para_ref('faq_item'));
kb_field('node', 'product', 'field_policy_cards', 'entity_reference_revisions', 'Thẻ chính sách', -1,
  ['target_type' => 'paragraph'], $para_ref('policy_card'));

echo "done\n";
