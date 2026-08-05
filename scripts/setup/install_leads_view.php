<?php

/**
 * @file
 * The "Yêu cầu liên hệ" listing at /admin/content/lead.
 *
 * /admin/content cannot show leads to an editor and never will. The contact
 * endpoint stores every lead unpublished — an internal record must not be
 * reachable as a page — and owned by the anonymous visitor who sent it. The
 * content view's `status_extra` filter lets a user past an unpublished row
 * only when they own it or hold `bypass node access`, so an editor holding
 * neither matched nothing and read "chưa có nội dung" while the dashboard
 * counted eighteen leads.
 *
 * This view answers that with a listing of its own: gated on
 * `edit any lead content` rather than the widely-granted
 * `access content overview`, with SQL rewriting off so node access cannot
 * empty it again, and with the phone, email and message columns an editor
 * needs to call the customer back.
 *
 * The definition itself lives in config/sync/views.view.leads.yml so that a
 * later `drush cex` re-exports the same bytes. This script only puts it on a
 * site that is already installed.
 *
 * Safe to run repeatedly.
 *
 * Run: ddev drush php:script scripts/setup/install_leads_view.php
 */

use Symfony\Component\Yaml\Yaml;

$source = dirname(__DIR__, 2) . '/config/sync/views.view.leads.yml';
$data = Yaml::parseFile($source);

$storage = \Drupal::entityTypeManager()->getStorage('view');
$view = $storage->load($data['id']);

if ($view === NULL) {
  $storage->create($data)->save();
  echo "Created view {$data['id']}.\n";
}
else {
  // Keep the existing UUID: changing it makes Drupal treat the view as a
  // different entity and the next config import would delete and recreate it.
  foreach ($data as $key => $value) {
    if ($key !== 'uuid') {
      $view->set($key, $value);
    }
  }
  $view->save();
  echo "Updated view {$data['id']}.\n";
}

// The page display defines a route and a tab under /admin/content, and
// neither exists until the router is rebuilt.
\Drupal::service('router.builder')->rebuild();
echo "Router rebuilt. The listing is at /admin/content/lead.\n";
