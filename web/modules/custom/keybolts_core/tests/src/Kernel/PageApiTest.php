<?php

declare(strict_types=1);

namespace Drupal\Tests\keybolts_core\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\keybolts_api\Controller\PageController;
use Drupal\node\Entity\NodeType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * A singleton page that has not been created yet must 404, not 500.
 */
class PageApiTest extends KernelTestBase {

  protected static $modules = [
    'system', 'user', 'field', 'text', 'node', 'link', 'image', 'path_alias', 'options',
    'paragraphs', 'entity_reference_revisions', 'file',
    'keybolts_core', 'keybolts_api',
  ];

  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installEntitySchema('paragraph');
    $this->installEntitySchema('file');
    $this->installSchema('node', ['node_access']);
    $this->installConfig(['node', 'field']);
    NodeType::create(['type' => 'about_page', 'name' => 'About'])->save();
  }

  public function testUnknownKeyIs404(): void {
    $this->expectException(NotFoundHttpException::class);
    PageController::create($this->container)->page('nonsense');
  }

  public function testMissingSingletonIs404(): void {
    // The type exists but no node has been created yet.
    $this->expectException(NotFoundHttpException::class);
    PageController::create($this->container)->page('about');
  }
}
