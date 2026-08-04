<?php

declare(strict_types=1);

namespace Drupal\keybolts_core\Service;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Site\Settings;
use GuzzleHttp\ClientInterface;

/**
 * Verifies a reCAPTCHA v3 token with Google.
 *
 * Returns NULL — not a score — whenever the answer is unknown: no secret
 * configured, or Google unreachable. Callers treat NULL as "let it through".
 * A real customer must never lose a lead because Google was down.
 */
class RecaptchaVerifier {

  private const ENDPOINT = 'https://www.google.com/recaptcha/api/siteverify';

  public function __construct(
    private readonly ClientInterface $httpClient,
    private readonly Settings $settings,
    private readonly LoggerChannelFactoryInterface $loggerFactory,
  ) {}

  /**
   * FALSE until a secret key is present, so dev and staging stay unblocked.
   */
  public function isEnabled(): bool {
    return $this->secret() !== '';
  }

  public function threshold(): float {
    return (float) $this->settings->get('keybolts_recaptcha_threshold', 0.5);
  }

  /**
   * @return float|null
   *   The 0..1 score, or NULL when verification could not be performed.
   */
  public function verify(string $token, string $action): ?float {
    if (!$this->isEnabled() || $token === '') {
      return NULL;
    }
    try {
      $response = $this->httpClient->request('POST', self::ENDPOINT, [
        'form_params' => ['secret' => $this->secret(), 'response' => $token],
        'timeout' => 5,
      ]);
      $body = json_decode((string) $response->getBody(), TRUE) ?: [];
    }
    catch (\Throwable $e) {
      $this->loggerFactory->get('keybolts')
        ->warning('reCAPTCHA unreachable: @message', ['@message' => $e->getMessage()]);
      return NULL;
    }

    if (empty($body['success'])) {
      return 0.0;
    }
    // A token minted for a different form is as suspect as a bot token.
    if (isset($body['action']) && $body['action'] !== $action) {
      return 0.0;
    }
    return isset($body['score']) ? (float) $body['score'] : NULL;
  }

  private function secret(): string {
    return trim((string) $this->settings->get('keybolts_recaptcha_secret', ''));
  }

}
