<?php

declare(strict_types=1);

namespace Drupal\Tests\keybolts_core\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\keybolts_api\Controller\ContactController;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Request;

/**
 * The lead form is the site's only write endpoint, so its guards matter.
 */
#[RunTestsInSeparateProcesses]
class ContactSubmissionTest extends KernelTestBase {

  protected static $modules = [
    'system', 'user', 'field', 'text', 'node', 'path_alias', 'options',
    'keybolts_core', 'keybolts_api',
  ];

  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('contact_submission');
    $this->installSchema('system', ['sequences']);
  }

  private function post(array $body): array {
    $request = Request::create('/api/v1/contact', 'POST', [], [], [], [], json_encode($body));
    $controller = ContactController::create($this->container);
    $response = $controller->submit($request);
    return [$response->getStatusCode(), json_decode($response->getContent(), TRUE)];
  }

  private function countSubmissions(): int {
    return (int) $this->container->get('entity_type.manager')
      ->getStorage('contact_submission')
      ->getQuery()->accessCheck(FALSE)->count()->execute();
  }

  public function testValidSubmissionIsStored(): void {
    [$status] = $this->post([
      'name' => 'Nguyễn Văn A',
      'phone' => '0912411309',
      'message' => 'Cần báo giá',
      'source' => 'dealer',
    ]);
    $this->assertSame(201, $status);
    $this->assertSame(1, $this->countSubmissions());
  }

  public function testMissingFieldsReturn422AndStoreNothing(): void {
    [$status, $body] = $this->post(['name' => '', 'phone' => '']);
    $this->assertSame(422, $status);
    $this->assertContains('name', $body['errors']);
    $this->assertContains('phone', $body['errors']);
    $this->assertSame(0, $this->countSubmissions());
  }

  /**
   * A bot told "you failed" learns how to pass. Answer 201 and drop it.
   */
  public function testHoneypotLooksSuccessfulButStoresNothing(): void {
    [$status] = $this->post([
      'name' => 'Bot',
      'phone' => '0900000000',
      'website' => 'http://spam.example',
    ]);
    $this->assertSame(201, $status);
    $this->assertSame(0, $this->countSubmissions());
  }

  public function testUnknownSourceFallsBackToContact(): void {
    $this->post(['name' => 'A', 'phone' => '1', 'source' => 'nonsense']);
    $ids = $this->container->get('entity_type.manager')
      ->getStorage('contact_submission')
      ->getQuery()->accessCheck(FALSE)->execute();
    $entity = $this->container->get('entity_type.manager')
      ->getStorage('contact_submission')->load(reset($ids));
    $this->assertSame('contact', $entity->get('source')->value);
  }
}
