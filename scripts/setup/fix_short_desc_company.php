<?php

/**
 * @file
 * Corrects the company named in every product's meta description.
 * Safe to run repeatedly.
 *
 * The import generated a closing line of "Liên hệ Keybolts: 19009018." on
 * 179 products. Keybolts is a brand, and the catalogue carries two of them —
 * so on a Baltica lock that line credits the wrong maker, in the very text
 * search engines show under the result. The phone number belongs to the
 * company, Việt Long, which sells both.
 *
 * Only the company name changes; the rest of each description, which an
 * editor may have rewritten, is left exactly as it is.
 *
 * Run: ddev drush php:script scripts/setup/fix_short_desc_company.php
 * Preview: ddev drush php:script scripts/setup/fix_short_desc_company.php -- --dry-run
 */

use Drupal\node\Entity\Node;

const KBC_FROM = 'Liên hệ Keybolts';
const KBC_TO = 'Liên hệ Việt Long';

$dry_run = in_array('--dry-run', $_SERVER['argv'] ?? [], TRUE);

$storage = \Drupal::entityTypeManager()->getStorage('node');
$nids = $storage->getQuery()
  ->accessCheck(FALSE)
  ->condition('type', 'product')
  ->condition('field_short_desc', '%' . KBC_FROM . '%', 'LIKE')
  ->execute();

$changed = 0;
foreach ($storage->loadMultiple($nids) as $product) {
  assert($product instanceof Node);
  $was = (string) $product->get('field_short_desc')->value;
  $now = str_replace(KBC_FROM, KBC_TO, $was);
  if ($now === $was) {
    continue;
  }
  if (!$dry_run) {
    $product->set('field_short_desc', $now);
    $product->save();
  }
  $changed++;
  if ($changed <= 3) {
    echo "  {$product->label()}\n    {$now}\n";
  }
}

echo $dry_run ? "\n--- dry run, nothing saved ---\n" : "\n--- applied ---\n";
echo "descriptions updated: {$changed}\n";
if (!$changed) {
  echo "Nothing to do — no description still names a brand as the seller.\n";
}
