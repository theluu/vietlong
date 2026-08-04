<?php

declare(strict_types=1);

namespace Drupal\Tests\keybolts_core\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\keybolts_api\Controller\ContactController;
use Drupal\keybolts_core\Entity\ContactSubmission;
use Drupal\keybolts_core\Service\RecaptchaVerifier;
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
    $this->assertSame('contact', $this->latest()->get('source')->value);
  }

  public function testLowRecaptchaScoreIsRejectedAndStoresNothing(): void {
    $this->setVerifier(0.1);
    [$status, $body] = $this->post([
      'name' => 'Bot', 'phone' => '0900000000', 'recaptchaToken' => 'tok',
    ]);
    $this->assertSame(422, $status);
    $this->assertContains('recaptcha', $body['errors']);
    $this->assertSame(0, $this->countSubmissions());
  }

  public function testGoodRecaptchaScoreIsStoredAlongsideTheLead(): void {
    $this->setVerifier(0.9);
    [$status] = $this->post([
      'name' => 'A', 'phone' => '0900000000', 'recaptchaToken' => 'tok',
    ]);
    $this->assertSame(201, $status);
    $this->assertSame('0.90', $this->latest()->get('recaptcha_score')->value);
  }

  /**
   * Google unreachable, or no key configured: fail open. Losing a real
   * customer's enquiry is worse than storing one unscored lead.
   */
  public function testUnverifiableSubmissionIsStillAccepted(): void {
    $this->setVerifier(NULL);
    [$status] = $this->post([
      'name' => 'A', 'phone' => '0900000000', 'recaptchaToken' => 'tok',
    ]);
    $this->assertSame(201, $status);
    $this->assertNull($this->latest()->get('recaptcha_score')->value);
  }

  public function testEmailIsStoredWhenSupplied(): void {
    $this->post([
      'name' => 'A', 'phone' => '0900000000', 'email' => 'a@example.com',
    ]);
    $this->assertSame('a@example.com', $this->latest()->get('email')->value);
  }

  /** Swaps in a verifier that answers with a fixed score. */
  private function setVerifier(?float $score): void {
    $this->container->set('keybolts_core.recaptcha', new class($score) extends RecaptchaVerifier {

      public function __construct(private readonly ?float $score) {}

      public function isEnabled(): bool {
        return TRUE;
      }

      public function threshold(): float {
        return 0.5;
      }

      public function verify(string $token, string $action): ?float {
        return $this->score;
      }

    });
  }

  private function latest(): ContactSubmission {
    $storage = $this->container->get('entity_type.manager')->getStorage('contact_submission');
    $ids = $storage->getQuery()->accessCheck(FALSE)->sort('id', 'DESC')->range(0, 1)->execute();
    return $storage->load(reset($ids));
  }

}
