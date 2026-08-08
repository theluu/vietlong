<?php

/**
 * @file
 * Files every product under the brand that made it. Safe to run repeatedly.
 *
 * The catalogue carries two brands and the field to hold them already
 * existed, but the CSV import left it empty on all 186 products — so the
 * brand chip on every product card stayed hidden and the brand filter on the
 * listing had nothing to offer.
 *
 * Baltica is named in the product title; nowhere else in the imported data
 * mentions it, and no third brand appears anywhere. Everything else is
 * Keybolts, which is the house brand this catalogue is built around and the
 * only other term in the vocabulary. An editor can correct any single
 * product at /admin/content?type=product — this file does not overwrite a
 * brand somebody has already set.
 *
 * Run: ddev drush php:script scripts/setup/assign_product_brands.php
 * Preview: ddev drush php:script scripts/setup/assign_product_brands.php -- --dry-run
 */

use Drupal\node\Entity\Node;

/** Brand name => a needle that identifies it in the product title. */
const KBB_BY_TITLE = ['BALTICA' => 'baltica'];

/** Everything the needles above do not claim. */
const KBB_DEFAULT = 'KEYBOLTS';

$dry_run = in_array('--dry-run', $_SERVER['argv'] ?? [], TRUE);

$term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
$node_storage = \Drupal::entityTypeManager()->getStorage('node');

/**
 * Resolves a brand name to its term id, refusing to invent one.
 */
$brand = static function (string $name) use ($term_storage): int {
  $found = $term_storage->loadByProperties(['vid' => 'brand', 'name' => $name]);
  if (!$found) {
    throw new RuntimeException("No brand term named '{$name}'.");
  }
  return (int) reset($found)->id();
};

$nids = $node_storage->getQuery()
  ->accessCheck(FALSE)
  ->condition('type', 'product')
  ->execute();

$tally = [];
$assigned = 0;
$kept = 0;

foreach ($node_storage->loadMultiple($nids) as $product) {
  assert($product instanceof Node);

  // An editor's own answer outranks anything inferred from a title.
  if (!$product->get('field_brand')->isEmpty()) {
    $kept++;
    continue;
  }

  $title = mb_strtolower((string) $product->label());
  $name = KBB_DEFAULT;
  foreach (KBB_BY_TITLE as $candidate => $needle) {
    if (str_contains($title, $needle)) {
      $name = $candidate;
      break;
    }
  }

  $tally[$name] = ($tally[$name] ?? 0) + 1;
  if (!$dry_run) {
    $product->set('field_brand', $brand($name));
    $product->save();
  }
  $assigned++;
}

echo $dry_run ? "--- dry run, nothing saved ---\n\n" : "--- applied ---\n\n";
ksort($tally);
foreach ($tally as $name => $count) {
  echo str_pad((string) $count, 5, ' ', STR_PAD_LEFT) . "  {$name}\n";
}
echo "\nassigned  {$assigned}\n";
echo "kept      {$kept}  (already had a brand an editor chose)\n";
