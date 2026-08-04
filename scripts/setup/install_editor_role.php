<?php

/**
 * @file
 * The "Biên tập viên nội dung" role.
 *
 * Drupal ships a `content_editor` role, but with 14 permissions and none of
 * them touching this site's content types it is useless here. This grants what
 * a real editor needs and deliberately stops short of anything that can break
 * the site: no module install, no field or content-type changes, no user
 * management, no reCAPTCHA keys.
 *
 * Safe to run repeatedly.
 *
 * Run: ddev drush php:script scripts/setup/install_editor_role.php
 */

use Drupal\user\Entity\Role;

const KB_ROLE_ID = 'bien_tap_vien';

/** Bundles an editor may create, edit and delete outright. */
const KB_FULL_TYPES = ['product', 'article', 'project', 'branch'];

/**
 * Singletons. An editor edits them but must not be able to create a second
 * one or delete the only one — either would blank a page on the live site.
 */
const KB_SINGLETONS = [
  'site_settings', 'about_page', 'dealers_page', 'contact_page',
  'policies_page', 'news_page', 'projects_page',
];

$permissions = [
  'access content',
  'view own unpublished content',
  'access content overview',
  'access toolbar',
  'access administration pages',
  'view the administration theme',
  'access files overview',
  'administer menu',
  'access site in maintenance mode',
  // Lets an editor publish a change and see it immediately.
  'flush caches',
  // Media and rich text.
  'create files',
  'access media overview',
  'use text format basic_html',
];

foreach (KB_FULL_TYPES as $type) {
  $permissions[] = "create {$type} content";
  $permissions[] = "edit any {$type} content";
  $permissions[] = "delete any {$type} content";
}

foreach (KB_SINGLETONS as $type) {
  $permissions[] = "edit any {$type} content";
}

// Leads are customer data: readable and removable, never authored by hand.
$permissions[] = 'edit any lead content';
$permissions[] = 'delete any lead content';

$role = Role::load(KB_ROLE_ID) ?? Role::create([
  'id' => KB_ROLE_ID,
  'label' => 'Biên tập viên nội dung',
  'weight' => 3,
]);

$available = array_keys(\Drupal::service('user.permissions')->getPermissions());
$granted = $skipped = [];
foreach ($permissions as $permission) {
  if (in_array($permission, $available, TRUE)) {
    $role->grantPermission($permission);
    $granted[] = $permission;
  }
  else {
    $skipped[] = $permission;
  }
}
$role->save();

printf("role %s: %d quyền\n", KB_ROLE_ID, count($granted));
if ($skipped) {
  echo "  bỏ qua (không tồn tại): " . implode(', ', $skipped) . "\n";
}
