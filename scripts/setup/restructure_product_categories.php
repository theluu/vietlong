<?php

/**
 * @file
 * Rebuilds product_category as the three-level tree the catalogue was
 * redesigned around. Safe to run repeatedly.
 *
 * Terms are content, not config, so this file — not config/sync — is the
 * source of truth for the tree's shape on every environment.
 *
 * Nothing is ever deleted. All eight of the old top-level terms are reused as
 * second-level terms, which keeps every indexed /danh-muc/<tid> URL alive.
 *
 * Products are not touched here; scripts/setup/reassign_product_categories.php
 * does that, and depends on the terms this file creates.
 *
 * Run: ddev drush php:script scripts/setup/restructure_product_categories.php
 */

use Drupal\taxonomy\Entity\Term;

const KB_VID = 'product_category';

/**
 * The tree, exactly as the catalogue is meant to read.
 *
 * 'tid' names an existing term to reuse — renaming and re-parenting it in
 * place. A missing 'tid' means the term is looked up by name and created when
 * absent, so a second run adopts what the first run made rather than
 * duplicating it.
 *
 * 'number' and 'desc' feed the homepage tile grid and belong to roots only.
 */
const KB_TREE = [
  [
    'tid' => 5,
    'name' => 'Khóa thông minh',
    'number' => '01',
    'desc' => 'Điều khiển tiện lợi, bảo mật hiện đại.',
    'children' => [
      ['tid' => 6, 'name' => 'Khóa thông minh cửa gỗ'],
      ['name' => 'Khóa thông minh cửa nhôm kính'],
      ['name' => 'Khóa thông minh cửa cổng'],
      [
        'tid' => 7,
        'name' => 'Khóa khách sạn',
        'children' => [
          ['tid' => 29, 'name' => 'Khoá thẻ từ khách sạn'],
        ],
      ],
    ],
  ],
  [
    'name' => 'Khóa cơ',
    'number' => '02',
    'desc' => 'Khóa đồng và inox cơ khí, bền bỉ cho mọi loại cửa.',
    'children' => [
      [
        'tid' => 3,
        'name' => 'Khóa đồng',
        'children' => [
          ['tid' => 34, 'name' => 'Khoá đồng đại sảnh full size'],
          ['tid' => 33, 'name' => 'Khóa tay gạt đồng đại sảnh'],
          ['tid' => 32, 'name' => 'Khóa tay gạt đồng đại'],
          ['tid' => 31, 'name' => 'Khóa tay gạt đồng trung'],
          ['tid' => 30, 'name' => 'Khóa tay gạt đồng thông phòng'],
          ['tid' => 27, 'name' => 'Khóa âm'],
        ],
      ],
      [
        'tid' => 4,
        'name' => 'Khóa inox',
        'children' => [
          ['tid' => 28, 'name' => 'Khóa tay gạt inox'],
        ],
      ],
    ],
  ],
  [
    'name' => 'Chốt cửa & Bản lề',
    'number' => '03',
    'desc' => 'Chốt Cremone và bản lề đồng, inox chịu tải cao.',
    'children' => [
      [
        'tid' => 8,
        'name' => 'Chốt cửa Cremone',
        'children' => [
          ['tid' => 24, 'name' => 'Chốt cửa Cremone đồng'],
          ['tid' => 25, 'name' => 'Chốt cửa Cremone inox'],
        ],
      ],
      [
        'tid' => 9,
        'name' => 'Bản lề cửa',
        'children' => [
          ['tid' => 22, 'name' => 'Bản lề inox'],
          ['name' => 'Bản lề đồng'],
        ],
      ],
    ],
  ],
  [
    'tid' => 10,
    'name' => 'Phụ kiện cửa & Nội thất',
    'number' => '04',
    'desc' => 'Chốt, tay nắm và phụ kiện đi kèm đầy đủ.',
    'children' => [
      [
        'tid' => 23,
        'name' => 'Phụ kiện cửa gỗ',
        'children' => [
          ['name' => 'Chặn cửa / Hít cửa'],
          ['name' => 'Tay co'],
          ['name' => 'Mắt thần'],
          ['name' => 'Phụ kiện cửa khác'],
        ],
      ],
      // Left flat on purpose: all 21 products here are kitchen racks and
      // baskets. The tay-nắm-tủ / bản-lề-bật / ray-trượt groups the redesign
      // sketched have no stock yet, and empty categories are menu noise.
      ['tid' => 26, 'name' => 'Phụ kiện tủ & bếp'],
    ],
  ],
];

/**
 * Tallies one action, or returns the whole tally when called with no argument.
 *
 * A static holds the counts because drush php:script runs this file inside a
 * function scope, where `global` reaches a different variable than the one
 * declared here.
 *
 * @return array<string, int>
 *   The running tally.
 */
function kb_tally(?string $action = NULL): array {
  static $log = ['created' => 0, 'renamed' => 0, 'moved' => 0, 'fields' => 0, 'skipped' => 0];
  if ($action !== NULL) {
    $log[$action]++;
  }
  return $log;
}

/**
 * Loads the term a node of the tree refers to, creating it when it is new.
 */
function kb_term(array $node): Term {
  if (isset($node['tid'])) {
    $term = Term::load($node['tid']);
    if (!$term) {
      throw new RuntimeException("Term {$node['tid']} ({$node['name']}) is gone; the tree cannot be rebuilt in place.");
    }
    return $term;
  }

  $found = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
    ->loadByProperties(['vid' => KB_VID, 'name' => $node['name']]);
  if ($found) {
    return reset($found);
  }

  $term = Term::create(['vid' => KB_VID, 'name' => $node['name']]);
  $term->save();
  kb_tally('created');
  echo "created  {$node['name']} (tid {$term->id()})\n";
  return $term;
}

/**
 * Applies one node of the tree, then recurses into its children.
 *
 * @param int $parent
 *   Target parent term id; 0 for a root.
 * @param int $weight
 *   Sibling order, so the admin overview and loadTree() agree with the tree
 *   as written above.
 */
function kb_apply(array $node, int $parent, int $weight): void {
  $term = kb_term($node);
  $touched = FALSE;

  if ($term->label() !== $node['name']) {
    echo "renamed  {$term->label()} -> {$node['name']} (tid {$term->id()})\n";
    $term->setName($node['name']);
    kb_tally('renamed');
    $touched = TRUE;
  }

  $current = (int) ($term->get('parent')->target_id ?? 0);
  if ($current !== $parent) {
    echo "moved    {$node['name']} (tid {$term->id()}): parent {$current} -> {$parent}\n";
    $term->set('parent', [$parent]);
    kb_tally('moved');
    $touched = TRUE;
  }

  if ((int) $term->getWeight() !== $weight) {
    $term->setWeight($weight);
    $touched = TRUE;
  }

  // Only roots are tiles on the homepage. Demoted terms keep stale numbers
  // otherwise, and the grid sorts by that number.
  $number = $node['number'] ?? '';
  $desc = $node['desc'] ?? '';
  foreach (['field_number' => $number, 'field_short_desc' => $desc] as $field => $value) {
    if (!$term->hasField($field)) {
      continue;
    }
    $was = (string) ($term->get($field)->value ?? '');
    // A root without an explicit value keeps whatever an editor wrote; only
    // non-roots are actively cleared.
    if ($parent !== 0 && $was !== '') {
      $term->set($field, NULL);
      echo "cleared  {$field} on {$node['name']} (tid {$term->id()})\n";
      kb_tally('fields');
      $touched = TRUE;
    }
    elseif ($parent === 0 && $value !== '' && $was !== $value) {
      $term->set($field, $value);
      echo "field    {$field} = '{$value}' on {$node['name']} (tid {$term->id()})\n";
      kb_tally('fields');
      $touched = TRUE;
    }
  }

  if ($touched) {
    $term->save();
  }
  else {
    kb_tally('skipped');
  }

  foreach (array_values($node['children'] ?? []) as $i => $child) {
    kb_apply($child, (int) $term->id(), $i);
  }
}

foreach (array_values(KB_TREE) as $i => $root) {
  kb_apply($root, 0, $i);
}

echo "\n--- summary ---\n";
foreach (kb_tally() as $key => $count) {
  echo str_pad($key, 9) . $count . "\n";
}
echo "\nRun scripts/setup/reassign_product_categories.php next to file products under the new terms.\n";
