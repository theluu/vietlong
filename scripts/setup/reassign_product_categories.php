<?php

/**
 * @file
 * Files every imported product under the leaf term it belongs to, after
 * restructure_product_categories.php has built the tree. Safe to run
 * repeatedly.
 *
 * The CSV import filed products against the old flat vocabulary, so four of
 * the old terms now hold a mix of things that the redesign splits apart. The
 * source data carries no field for the distinction, so each split is decided
 * from the product title — the only place the information exists.
 *
 * Run: ddev drush php:script scripts/setup/reassign_product_categories.php
 * Preview only: ddev drush php:script scripts/setup/reassign_product_categories.php -- --dry-run
 */

use Drupal\node\Entity\Node;

$dry_run = in_array('--dry-run', $_SERVER['argv'] ?? [], TRUE);

/**
 * Case-insensitive substring test that respects Vietnamese diacritics.
 */
$has = static fn(string $haystack, string $needle): bool
  => mb_stripos($haystack, $needle) !== FALSE;

/**
 * Where each product under a source term should end up.
 *
 * Read as: for every product currently on the source term, walk the rules in
 * order and take the first whose test passes. The last rule of each list is
 * the catch-all, so no product is ever left behind.
 *
 * Targets are named, not numbered — restructure_product_categories.php has
 * just created several of them and their ids differ per environment.
 */
$rules = [
  // 17 hinges: KB-SUS bodies are stainless, the KB 81x range is solid brass.
  22 => [
    ['Bản lề inox', static fn(string $t): bool => $has($t, 'KB-SUS')],
    ['Bản lề đồng', static fn(string $t): bool => TRUE],
  ],
  // 31 door accessories, split by what the product actually is.
  23 => [
    // 'Chăn' is a typo in the source data for 'Chặn'; both appear.
    ['Chặn cửa / Hít cửa', static fn(string $t): bool => $has($t, 'Chặn') || $has($t, 'Chăn')],
    ['Tay co', static fn(string $t): bool => $has($t, 'Tay co')],
    ['Mắt thần', static fn(string $t): bool => $has($t, 'Mắt thần')],
    // Chốt hé, núm, trùy — real products with no group of their own yet.
    ['Phụ kiện cửa khác', static fn(string $t): bool => TRUE],
  ],
  // 12 products the old vocabulary called 'Khóa vân tay': eleven villa locks
  // for wooden main doors, and one lock made for a hydraulic glass door.
  6 => [
    ['Khóa thông minh cửa nhôm kính', static fn(string $t): bool => $has($t, 'Cửa kính')],
    ['Khóa thông minh cửa gỗ', static fn(string $t): bool => TRUE],
  ],
  // ASSUMPTION, flagged in the plan: nothing in the imported data says which
  // door these 18 smart locks fit. They default to the wooden-door branch and
  // are meant to be corrected by hand at /admin/content?type=product.
  5 => [
    ['Khóa thông minh cửa gỗ', static fn(string $t): bool => TRUE],
  ],
];

$term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
$node_storage = \Drupal::entityTypeManager()->getStorage('node');

/**
 * Resolves a target term name to its id, refusing to guess.
 */
$target = static function (string $name) use ($term_storage): int {
  $found = $term_storage->loadByProperties(['vid' => 'product_category', 'name' => $name]);
  if (!$found) {
    throw new RuntimeException("No term named '{$name}'. Run restructure_product_categories.php first.");
  }
  return (int) reset($found)->id();
};

$moved = 0;
$unchanged = 0;
$tally = [];

foreach ($rules as $source => $branches) {
  $nids = $node_storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', 'product')
    ->condition('field_category', $source)
    ->execute();

  foreach ($node_storage->loadMultiple($nids) as $product) {
    assert($product instanceof Node);
    $title = (string) $product->label();

    $name = NULL;
    foreach ($branches as [$candidate, $test]) {
      if ($test($title)) {
        $name = $candidate;
        break;
      }
    }
    // Unreachable while every rule list ends in a catch-all, but a silently
    // skipped product would be worse than a loud failure.
    if ($name === NULL) {
      throw new RuntimeException("No rule matched '{$title}' under term {$source}.");
    }

    $tid = $target($name);
    $tally[$name] = ($tally[$name] ?? 0) + 1;

    if ((int) $product->get('field_category')->target_id === $tid) {
      $unchanged++;
      continue;
    }

    if (!$dry_run) {
      $product->set('field_category', $tid);
      $product->save();
    }
    $moved++;
  }
}

echo $dry_run ? "--- dry run, nothing saved ---\n\n" : "--- applied ---\n\n";
ksort($tally);
foreach ($tally as $name => $count) {
  echo str_pad((string) $count, 5, ' ', STR_PAD_LEFT) . "  {$name}\n";
}
echo "\nmoved      {$moved}\n";
echo "unchanged  {$unchanged}\n";
echo "total      " . ($moved + $unchanged) . "\n";
