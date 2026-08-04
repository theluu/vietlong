<?php

/**
 * @file
 * Moves leads off the bespoke contact_submission entity onto `lead` nodes,
 * then removes the entity type.
 *
 * Leads were originally a custom content entity to keep them out of
 * /admin/content. That cost a bespoke list builder, a bespoke permission and
 * a second place to look — for records an editor wants beside everything else.
 *
 * Idempotent: matched on the original id, so re-running imports nothing twice.
 *
 * Run: ddev drush php:script scripts/setup/migrate_leads_to_nodes.php
 */

use Drupal\node\Entity\Node;

$etm = \Drupal::entityTypeManager();
$db = \Drupal::database();

if (!$db->schema()->tableExists('contact_submission')) {
  echo "contact_submission table already gone — nothing to migrate\n";
  return;
}

// Read straight from the table: the entity type may already be uninstalled.
$rows = $db->select('contact_submission', 'c')->fields('c')->execute()->fetchAll();
echo 'found ' . count($rows) . " submissions\n";

$existing = $etm->getStorage('node')->getQuery()
  ->accessCheck(FALSE)->condition('type', 'lead')->execute();
$seen = [];
foreach ($etm->getStorage('node')->loadMultiple($existing) as $node) {
  $seen[(string) $node->get('field_lead_ip')->value . '|' . $node->getCreatedTime() . '|' . $node->label()] = TRUE;
}

$imported = 0;
foreach ($rows as $row) {
  $key = (string) $row->ip . '|' . (int) $row->created . '|' . (string) $row->name;
  if (isset($seen[$key])) {
    continue;
  }
  $node = Node::create([
    'type' => 'lead',
    'title' => mb_substr((string) $row->name, 0, 255) ?: '(không tên)',
    'status' => 0,
    'created' => (int) $row->created,
    'field_lead_phone' => (string) $row->phone,
    'field_lead_email' => (string) ($row->email ?? ''),
    'field_lead_message' => (string) $row->message,
    'field_lead_source' => (string) ($row->source ?: 'contact'),
    'field_lead_ip' => (string) $row->ip,
    'field_lead_recaptcha' => $row->recaptcha_score,
  ]);
  $node->save();
  $imported++;
}
echo "imported {$imported} leads as nodes\n";

// Drop the entity type now that its rows live on as nodes.
$udm = \Drupal::entityDefinitionUpdateManager();
if ($definition = $udm->getEntityType('contact_submission')) {
  $udm->uninstallEntityType($definition);
  echo "uninstalled contact_submission entity type\n";
}
