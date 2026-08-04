<?php

declare(strict_types=1);

namespace Drupal\Tests\keybolts_core\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\NodeInterface;
use Drupal\node\Entity\NodeType;
use Drupal\keybolts_api\Controller\ContactController;
use Drupal\keybolts_core\Service\RecaptchaVerifier;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Request;

/**
 * The lead form is the site's only write endpoint, so its guards matter.
 */
#[RunTestsInSeparateProcesses]
class LeadSubmissionTest extends KernelTestBase {

  protected static $modules = [
    'system', 'user', 'field', 'text', 'node', 'path_alias', 'options',
    'keybolts_core', 'keybolts_api',
  ];

  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installSchema('node', ['node_access']);
    $this->installSchema('system', ['sequences']);
    $this->installConfig(['node', 'field']);
    NodeType::create(['type' => 'lead', 'name' => 'Lead'])->save();
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
  }

  private function post(array $body): array {
    $request = Request::create('/api/v1/contact', 'POST', [], [], [], [], json_encode($body));
    $controller = ContactController::create($this->container);
    $response = $controller->submit($request);
    return [$response->getStatusCode(), json_decode($response->getContent(), TRUE)];
  }

  private function countSubmissions(): int {
    return (int) $this->container->get('entity_type.manager')
      ->getStorage('node')
      ->getQuery()->accessCheck(FALSE)->condition('type', 'lead')->count()->execute();
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
    $this->assertSame('contact', $this->latest()->get('field_lead_source')->value);
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
    $this->assertSame('0.90', $this->latest()->get('field_lead_recaptcha')->value);
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
    $this->assertNull($this->latest()->get('field_lead_recaptcha')->value);
  }

  public function testEmailIsStoredWhenSupplied(): void {
    $this->post([
      'name' => 'A', 'phone' => '0900000000', 'email' => 'a@example.com',
    ]);
    $this->assertSame('a@example.com', $this->latest()->get('field_lead_email')->value);
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

  /**
   * Offices share one public IP, so the brake must be generous and must say
   * plainly that it fired — a silent failure looks like a broken form.
   */
  public function testFloodLimitReportsItselfDistinctly(): void {
    for ($i = 0; $i < 15; $i++) {
      [$status] = $this->post(['name' => 'A', 'phone' => '090000' . $i]);
      $this->assertSame(201, $status, "submission {$i} should be accepted");
    }
    [$status, $body] = $this->post(['name' => 'A', 'phone' => '0900009999']);
    $this->assertSame(429, $status);
    $this->assertContains('flood', $body['errors']);
    $this->assertSame(15, $this->countSubmissions());
  }

  private function latest(): NodeInterface {
    $storage = $this->container->get('entity_type.manager')->getStorage('node');
    $ids = $storage->getQuery()->accessCheck(FALSE)
      ->condition('type', 'lead')->sort('nid', 'DESC')->range(0, 1)->execute();
    return $storage->load(reset($ids));
  }

}
