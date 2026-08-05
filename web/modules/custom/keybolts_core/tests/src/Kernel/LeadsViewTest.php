<?php

declare(strict_types=1);

namespace Drupal\Tests\keybolts_core\Kernel;

use Drupal\Core\Session\AnonymousUserSession;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;
use Drupal\views\Entity\View;
use Drupal\views\Views;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Yaml\Yaml;

/**
 * Leads arrive unpublished and owned by the anonymous user, which is exactly
 * the combination /admin/content refuses to show: its `status_extra` filter
 * only lets an editor past for unpublished nodes they own themselves. So the
 * editor saw an empty page while the dashboard counted every lead. This view
 * is the replacement, and these tests pin the two things that made the old
 * page useless — the rows must appear, and only for someone allowed to.
 */
#[RunTestsInSeparateProcesses]
class LeadsViewTest extends KernelTestBase {

  protected static $modules = [
    'system', 'user', 'field', 'text', 'filter', 'node', 'views',
    'path_alias', 'options', 'taxonomy', 'keybolts_core',
  ];

  /** The editor: may work on leads, but cannot bypass node access. */
  private User $editor;

  /** Someone with the run of the admin pages but no business reading leads. */
  private User $stranger;

  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    // The exposed filters build a form, and building a form resolves the
    // current path through the alias manager.
    $this->installEntitySchema('path_alias');
    $this->installSchema('node', ['node_access']);
    // `system` brings the date formats the "Nhận lúc" column renders with.
    $this->installConfig(['system', 'node', 'field', 'filter']);

    NodeType::create(['type' => 'lead', 'name' => 'Yêu cầu liên hệ'])->save();
    NodeType::create(['type' => 'article', 'name' => 'Article'])->save();
    foreach ([
      'field_lead_phone' => 'string',
      'field_lead_email' => 'string',
      'field_lead_message' => 'string_long',
      'field_lead_source' => 'string',
      'field_lead_ip' => 'string',
      'field_lead_recaptcha' => 'decimal',
    ] as $name => $type) {
      FieldStorageConfig::create(['field_name' => $name, 'entity_type' => 'node', 'type' => $type])->save();
      FieldConfig::create(['field_name' => $name, 'entity_type' => 'node', 'bundle' => 'lead', 'label' => $name])->save();
    }

    // The view ships in config/sync and is deployed with `drush cim`, so the
    // test reads that exact file rather than a copy that could drift from it.
    View::create(Yaml::parseFile($this->root . '/../config/sync/views.view.leads.yml'))->save();

    User::create(['uid' => 0, 'name' => ''])->save();
    $editor = Role::create(['id' => 'bien_tap_vien', 'label' => 'Biên tập viên']);
    // The two permissions the live role actually holds for leads.
    $editor->grantPermission('edit any lead content');
    $editor->grantPermission('view own unpublished content');
    $editor->save();
    $this->editor = User::create(['name' => 'bien_tap', 'status' => 1, 'roles' => ['bien_tap_vien']]);
    $this->editor->save();

    $other = Role::create(['id' => 'nguoi_khac', 'label' => 'Người khác']);
    $other->grantPermission('access content overview');
    $other->save();
    $this->stranger = User::create(['name' => 'nguoi_khac', 'status' => 1, 'roles' => ['nguoi_khac']]);
    $this->stranger->save();
  }

  /** A lead as the contact endpoint writes one: unpublished, owned by nobody. */
  private function lead(string $name, string $phone, string $source, int $created): Node {
    $node = Node::create([
      'type' => 'lead',
      'title' => $name,
      'status' => 0,
      'uid' => 0,
      'created' => $created,
      'field_lead_phone' => $phone,
      'field_lead_source' => $source,
      'field_lead_message' => 'Xin báo giá',
    ]);
    $node->save();
    return $node;
  }

  /** Runs the page display as $account and returns the titles it listed. */
  private function titlesSeenBy(User $account): array {
    $this->container->get('current_user')->setAccount($account);
    $view = Views::getView('leads');
    $view->setDisplay('page_1');
    $view->execute();
    return array_map(
      static fn ($row) => $row->_entity->label(),
      $view->result,
    );
  }

  public function testEditorSeesLeadsDespiteThemBeingUnpublishedAndUnowned(): void {
    $this->lead('Trần B', '0900000001', 'contact', 1785681360);
    $this->lead('Lưu Xuân Thế', '0900000002', 'dealer', 1785836794);

    $this->assertSame(['Lưu Xuân Thế', 'Trần B'], $this->titlesSeenBy($this->editor));
  }

  public function testNewestLeadComesFirst(): void {
    $this->lead('Cũ nhất', '0900000001', 'contact', 1000);
    $this->lead('Mới nhất', '0900000002', 'contact', 3000);
    $this->lead('Ở giữa', '0900000003', 'contact', 2000);

    $this->assertSame(['Mới nhất', 'Ở giữa', 'Cũ nhất'], $this->titlesSeenBy($this->editor));
  }

  public function testOnlyLeadsAreListed(): void {
    $this->lead('Khách hàng', '0900000001', 'contact', 2000);
    Node::create(['type' => 'article', 'title' => 'Bài viết', 'status' => 1, 'uid' => 0])->save();

    $this->assertSame(['Khách hàng'], $this->titlesSeenBy($this->editor));
  }

  /**
   * Leads carry a phone number, an email and a message — the whole reason for
   * a separate page. A field id that does not resolve leaves a broken handler
   * that renders nothing, so assert on the rendered output, not the rows.
   */
  public function testTheRowCarriesTheDetailsAnEditorNeedsToCallBack(): void {
    $this->lead('Trần B', '0912345678', 'dealer', 2000);
    $this->container->get('current_user')->setAccount($this->editor);

    $view = Views::getView('leads');
    $view->setDisplay('page_1');
    $output = (string) $this->container->get('renderer')
      ->renderRoot($view->buildRenderable('page_1'));

    $this->assertStringContainsString('0912345678', $output);
    $this->assertStringContainsString('Xin báo giá', $output);
    $this->assertStringContainsString('dealer', $output);
  }

  /**
   * A lead holds a customer's name, phone and email. Reaching the admin pages
   * must not be enough to read them.
   */
  public function testThePageIsClosedToAnyoneNotTrustedWithLeads(): void {
    $view = Views::getView('leads');

    $this->assertTrue($view->access(['page_1'], $this->editor));
    $this->assertFalse($view->access(['page_1'], $this->stranger));
    $this->assertFalse($view->access(['page_1'], new AnonymousUserSession()));
  }

}
