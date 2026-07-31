<?php

/**
 * @file
 * Seeds Keybolts taxonomy terms and product nodes. Safe to run repeatedly.
 *
 * Run: ddev drush php:script scripts/seed/seed_products.php
 */

use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\taxonomy\Entity\Term;

$data = json_decode(file_get_contents(dirname(__DIR__, 2) . '/scripts/seed/catalog.json'), TRUE);
$fs = \Drupal::service('file_system');
$dir = 'public://products';
$fs->prepareDirectory($dir, \Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY);

/**
 * Loads a term by name in a vocabulary, creating it when missing.
 */
function kb_term(string $vid, string $name, array $fields = []): Term {
  $existing = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
    ->loadByProperties(['vid' => $vid, 'name' => $name]);
  if ($existing) {
    return reset($existing);
  }
  $term = Term::create(['vid' => $vid, 'name' => $name] + $fields);
  $term->save();
  return $term;
}

/**
 * Downloads a remote image and re-encodes it as WebP.
 *
 * The originals are multi-megabyte; storing them unconverted would make the
 * frontend unusable.
 */
function kb_image(string $url, string $dir): ?File {
  $name = preg_replace('/[^a-z0-9]+/i', '-', pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_FILENAME));
  $dest = "{$dir}/{$name}.webp";
  $fs = \Drupal::service('file_system');

  $existing = \Drupal::entityTypeManager()->getStorage('file')
    ->loadByProperties(['uri' => $dest]);
  if ($existing) {
    return reset($existing);
  }

  $bytes = @file_get_contents($url);
  if ($bytes === FALSE) {
    echo "  ! download failed: {$url}\n";
    return NULL;
  }

  try {
    $img = new \Imagick();
    $img->readImageBlob($bytes);
    $img->setImageFormat('webp');
    $img->setImageCompressionQuality(82);
    if ($img->getImageWidth() > 1600) {
      $img->resizeImage(1600, 0, \Imagick::FILTER_LANCZOS, 1);
    }
    $img->stripImage();
    $blob = $img->getImageBlob();
    $img->destroy();
  }
  catch (\Throwable $e) {
    echo "  ! convert failed: {$url} ({$e->getMessage()})\n";
    return NULL;
  }

  $uri = $fs->saveData($blob, $dest, \Drupal\Core\File\FileExists::Replace);
  $file = File::create(['uri' => $uri]);
  $file->setPermanent();
  $file->save();
  echo sprintf("  image %s: %dKB -> %dKB\n", $name, strlen($bytes) / 1024, strlen($blob) / 1024);
  return $file;
}

/**
 * Creates paragraph entities and returns them for a reference field.
 */
function kb_paragraphs(array $rows, string $type, array $map): array {
  $out = [];
  foreach ($rows as $row) {
    $values = ['type' => $type];
    foreach ($map as $source => $field) {
      $values[$field] = $row[$source] ?? '';
    }
    $p = Paragraph::create($values);
    $p->save();
    $out[] = ['target_id' => $p->id(), 'target_revision_id' => $p->getRevisionId()];
  }
  return $out;
}

// --- Terms ------------------------------------------------------------------
$brands = $cats = $finishes = [];
foreach ($data['brands'] as $b) {
  $brands[$b['key']] = kb_term('brand', $b['name'], [
    'description' => $b['desc'] ?? '',
    'field_tag' => $b['tag'] ?? '',
    'field_cta_label' => $b['cta'] ?? '',
  ]);
}
foreach ($data['categories'] as $i => $c) {
  $cats[$c['key']] = kb_term('product_category', $c['label'], [
    'field_number' => sprintf('%02d', $i + 1),
  ]);
}
foreach ($data['finishes'] as $f) {
  $finishes[$f['key']] = kb_term('finish', $f['label'], [
    'field_swatch' => $f['swatch'] ?? '',
    'field_suffix' => strtoupper($f['key']),
  ]);
}
echo sprintf("terms: %d brands, %d categories, %d finishes\n",
  count($brands), count($cats), count($finishes));

// --- Sizes ------------------------------------------------------------------
// Size is encoded in the model string, e.g. `KB 1700-XL-PVD` -> XL.
$sizes = [
  'XL' => ['Đại sảnh XL', 'Cửa 2 cánh lớn'],
  'L'  => ['Đại L', 'Cửa chính 1 cánh'],
  'M'  => ['Trung M', 'Cửa phòng lớn'],
  'S'  => ['Thông phòng S', 'Cửa phòng ngủ'],
];

// --- Products ---------------------------------------------------------------
$created = 0;
foreach ($data['products'] as $p) {
  $existing = \Drupal::entityTypeManager()->getStorage('node')
    ->loadByProperties(['type' => 'product', 'field_product_code' => $p['model']]);
  if ($existing) {
    continue;
  }

  $size_key = '';
  $size_label = '';
  $size_note = '';
  foreach ($sizes as $key => [$label, $note]) {
    if (preg_match('/-' . $key . '(-|$)/', $p['model'])) {
      $size_key = strtolower($key);
      $size_label = $label;
      $size_note = $note;
      break;
    }
  }

  $values = [
    'type' => 'product',
    'title' => $p['name'],
    'status' => 1,
    'field_product_code' => $p['model'],
    'field_family' => $p['family'],
    'field_size_key' => $size_key,
    'field_size_label' => $size_label,
    'field_size_note' => $size_note,
    'field_brand' => $brands[$p['brand']] ?? NULL,
    'field_category' => $cats[$p['cat']] ?? NULL,
    'field_finish' => $finishes[$p['finish']] ?? NULL,
    'field_short_desc' => sprintf('%s — mã %s.', $p['name'], $p['model']),
    'field_stock_status' => 'con-hang',
    'field_contact_price' => TRUE,
    'field_warranty' => '5–10 năm',
    'field_certification' => ['CE-CFF'],
    'field_sort_order' => 0,
  ];
  if (!empty($p['badge'])) {
    $values['field_badge'] = match ($p['badge']) {
      'Bán chạy' => 'ban-chay',
      'Mới' => 'moi',
      'Cao cấp' => 'cao-cap',
      default => NULL,
    };
  }

  $specs = array_map(
    fn(array $r) => $r['k'] === 'Mã sản phẩm' ? ['k' => $r['k'], 'v' => $p['model']] : $r,
    $data['specs']
  );
  $values['field_specifications'] = kb_paragraphs($specs, 'spec_item',
    ['k' => 'field_spec_key', 'v' => 'field_spec_value']);
  $values['field_faqs'] = kb_paragraphs($data['faqs'], 'faq_item',
    ['q' => 'field_faq_question', 'a' => 'field_faq_answer']);
  $values['field_policy_cards'] = kb_paragraphs($data['policies'], 'policy_card',
    ['title' => 'field_card_title', 'desc' => 'field_card_desc']);

  echo "product: {$p['name']} ({$p['model']})\n";
  if (!empty($p['img']) && ($file = kb_image($p['img'], $dir))) {
    $values['field_images'] = [
      ['target_id' => $file->id(), 'alt' => $p['name'] . ' — ' . $p['model']],
    ];
  }

  Node::create($values)->save();
  $created++;
}

echo "created {$created} products\n";
