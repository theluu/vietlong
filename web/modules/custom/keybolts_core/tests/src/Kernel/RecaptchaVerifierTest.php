<?php

declare(strict_types=1);

namespace Drupal\Tests\keybolts_core\Kernel;

use Drupal\Core\Site\Settings;
use Drupal\KernelTests\KernelTestBase;
use Drupal\keybolts_core\Service\RecaptchaVerifier;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/** The verifier decides whether a lead is a bot, so its edge cases matter. */
#[RunTestsInSeparateProcesses]
final class RecaptchaVerifierTest extends KernelTestBase {

  protected static $modules = ['system', 'user', 'path_alias', 'keybolts_core'];

  public function testDisabledWhenSecretMissing(): void {
    $verifier = $this->container->get('keybolts_core.recaptcha');
    $this->assertFalse($verifier->isEnabled());
    $this->assertNull($verifier->verify('tok', 'contact_form'));
  }

  public function testScoreIsReturned(): void {
    $verifier = $this->verifierReturning(
      new Response(200, [], json_encode(['success' => TRUE, 'score' => 0.9, 'action' => 'contact_form']))
    );
    $this->assertTrue($verifier->isEnabled());
    $this->assertSame(0.9, $verifier->verify('tok', 'contact_form'));
  }

  public function testFailedVerificationScoresZero(): void {
    $verifier = $this->verifierReturning(
      new Response(200, [], json_encode(['success' => FALSE, 'error-codes' => ['invalid-input-response']]))
    );
    $this->assertSame(0.0, $verifier->verify('tok', 'contact_form'));
  }

  /** A token minted for another form must not unlock this one. */
  public function testMismatchedActionScoresZero(): void {
    $verifier = $this->verifierReturning(
      new Response(200, [], json_encode(['success' => TRUE, 'score' => 0.9, 'action' => 'dealer_form']))
    );
    $this->assertSame(0.0, $verifier->verify('tok', 'contact_form'));
  }

  public function testNetworkFailureReturnsNull(): void {
    $verifier = $this->verifierReturning(
      new RequestException('down', new GuzzleRequest('POST', '/'))
    );
    $this->assertNull($verifier->verify('tok', 'contact_form'));
  }

  public function testEmptyTokenSkipsTheCall(): void {
    // MockHandler with no queued response throws if the client is ever called.
    $verifier = $this->verifier(new MockHandler([]));
    $this->assertNull($verifier->verify('', 'contact_form'));
  }

  public function testThresholdIsConfigurable(): void {
    $verifier = new RecaptchaVerifier(
      new Client(),
      new Settings(['keybolts_recaptcha_threshold' => 0.8]),
      $this->container->get('logger.factory'),
    );
    $this->assertSame(0.8, $verifier->threshold());
  }

  private function verifierReturning(Response|RequestException $result): RecaptchaVerifier {
    return $this->verifier(new MockHandler([$result]));
  }

  private function verifier(MockHandler $handler): RecaptchaVerifier {
    return new RecaptchaVerifier(
      new Client(['handler' => HandlerStack::create($handler)]),
      new Settings(['keybolts_recaptcha_secret' => 'shh']),
      $this->container->get('logger.factory'),
    );
  }

}
