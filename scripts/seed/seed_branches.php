<?php

/**
 * @file
 * Seeds the five Keybolts branches. Safe to run repeatedly — matches on title.
 *
 * Run: ddev drush php:script scripts/seed/seed_branches.php
 */

use Drupal\node\Entity\Node;

const KB_BRANCHES = [
  ['Văn phòng bán buôn', 'Bán buôn', 'Khu phố Lê Hồng Phong, P. Đông Ngàn, TP. Từ Sơn, Bắc Ninh', '0912.411.309', '0912411309', 1],
  ['Showroom Từ Sơn', 'Cơ sở 1', '217-219 Trần Phú, P. Đông Ngàn, TP. Từ Sơn, Bắc Ninh', '0968.689.112', '0968689112', 2],
  ['Kho Võ Cường', 'Cơ sở 2', 'Cụm CN Võ Cường, P. Võ Cường, TP. Bắc Ninh', '0981.255.215', '0981255215', 3],
  ['Showroom Việt Trì', 'Cơ sở 3', '1308 Đại Lộ Hùng Vương, P. Tiên Cát, TP. Việt Trì, Phú Thọ', '0984.84.6655', '0984846655', 4],
  ['Showroom Vĩnh Yên', 'Cơ sở 4', '531 Đường Mê Linh, P. Khai Quang, TP. Vĩnh Yên, Vĩnh Phúc', '0984.84.6622', '0984846622', 5],
];

$storage = \Drupal::entityTypeManager()->getStorage('node');
foreach (KB_BRANCHES as [$name, $tag, $addr, $display, $tel, $weight]) {
  $existing = $storage->loadByProperties(['type' => 'branch', 'title' => $name]);
  $node = $existing ? reset($existing) : Node::create(['type' => 'branch', 'title' => $name]);
  $node->set('field_tag', $tag);
  $node->set('field_address', $addr);
  $node->set('field_phone_display', $display);
  $node->set('field_phone_tel', $tel);
  $node->set('field_sort_order', $weight);
  $node->setPublished()->save();
  echo ($existing ? 'updated: ' : 'created: ') . $name . "\n";
}
