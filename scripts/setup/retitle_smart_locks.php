<?php

/**
 * @file
 * Renames the smart locks that were titled by the room they were sold for.
 * Safe to run repeatedly.
 *
 * The handover deck rules out "Khóa biệt thự thông minh P70" and its kin: a
 * name that pins a model to one position tells the customer it is only for
 * that position, when the same lock fits a front door, a bedroom door or a
 * study door depending on the leaf. The imported catalogue carried eleven
 * "Khóa Biệt Thự" titles, which is exactly that.
 *
 * They take the wording the other eighteen smart locks already use, which
 * names the product and leaves the position to the "Vị trí phù hợp" field.
 *
 * The redirect module is on with auto_redirect, so the old alias keeps
 * resolving; no indexed URL is lost.
 *
 * Run: ddev drush php:script scripts/setup/retitle_smart_locks.php
 */

use Drupal\node\Entity\Node;

const KBR_FROM = 'Khóa Biệt Thự KeyBolts';
const KBR_TO = 'Khóa Thông Minh Keybolts';

$nids = \Drupal::entityTypeManager()->getStorage('node')->getQuery()
  ->accessCheck(FALSE)
  ->condition('type', 'product')
  ->condition('title', KBR_FROM, 'CONTAINS')
  ->execute();

$renamed = 0;
foreach (\Drupal::entityTypeManager()->getStorage('node')->loadMultiple($nids) as $product) {
  assert($product instanceof Node);
  $was = (string) $product->label();
  $now = str_replace(KBR_FROM, KBR_TO, $was);
  if ($now === $was) {
    continue;
  }
  $product->setTitle($now);
  $product->save();
  echo "renamed  {$was}\n      -> {$now}\n";
  $renamed++;
}

echo "\nrenamed {$renamed}\n";
if (!$renamed) {
  echo "Nothing to do — no title still names a house type.\n";
}
