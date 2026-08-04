<?php

declare(strict_types=1);

namespace Drupal\keybolts_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Flood\FloodInterface;
use Drupal\keybolts_core\Service\RecaptchaVerifier;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * The only write endpoint on the site.
 */
class ContactController extends ControllerBase {

  private const ALLOWED_SOURCES = ['contact', 'dealer', 'consult'];
  private const FLOOD_EVENT = 'keybolts_api.contact';
  // Offices and shops in Vietnam routinely share one public IP, so a tight
  // per-IP limit locks out colleagues rather than bots. reCAPTCHA and the
  // honeypot are the real spam gates; this is only a stampede brake.
  private const FLOOD_LIMIT = 15;
  private const FLOOD_WINDOW = 600;

  public function __construct(
    private readonly FloodInterface $flood,
    private readonly RecaptchaVerifier $recaptcha,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('flood'),
      $container->get('keybolts_core.recaptcha'),
    );
  }

  /**
   * POST /api/v1/contact
   */
  public function submit(Request $request): JsonResponse {
    $data = json_decode((string) $request->getContent(), TRUE) ?: [];

    // Honeypot: a hidden field only a bot fills in. Answer as if accepted —
    // telling it that it failed just teaches it how to pass.
    if (!empty($data['website'])) {
      return $this->noStore(['ok' => TRUE], 201);
    }

    $ip = (string) $request->getClientIp();
    if (!$this->flood->isAllowed(self::FLOOD_EVENT, self::FLOOD_LIMIT, self::FLOOD_WINDOW, $ip)) {
      return $this->noStore([
        'errors' => ['flood'],
        'error' => 'Quá nhiều yêu cầu. Vui lòng thử lại sau ít phút.',
      ], 429);
    }

    $name = trim((string) ($data['name'] ?? ''));
    $phone = trim((string) ($data['phone'] ?? ''));
    $errors = [];
    if ($name === '') {
      $errors[] = 'name';
    }
    if ($phone === '') {
      $errors[] = 'phone';
    }
    if ($errors) {
      return $this->noStore(['errors' => $errors], 422);
    }

    $source = (string) ($data['source'] ?? 'contact');
    if (!in_array($source, self::ALLOWED_SOURCES, TRUE)) {
      $source = 'contact';
    }

    // reCAPTCHA is the second gate, after the honeypot. Only a score we
    // actually received and that came back low is rejected — an unknown answer
    // (no key configured, Google down) must never cost a real lead.
    $score = $this->recaptcha->verify(
      (string) ($data['recaptchaToken'] ?? ''),
      (string) ($data['recaptchaAction'] ?? $source),
    );
    if ($score !== NULL && $score < $this->recaptcha->threshold()) {
      return $this->noStore(['errors' => ['recaptcha']], 422);
    }

    // Unpublished: a lead is an internal record and must never be reachable
    // as a page, even though it is an ordinary node.
    $this->entityTypeManager()->getStorage('node')->create([
      'type' => 'lead',
      'title' => mb_substr($name, 0, 255),
      'status' => 0,
      'field_lead_phone' => mb_substr($phone, 0, 60),
      'field_lead_email' => mb_substr(trim((string) ($data['email'] ?? '')), 0, 254),
      'field_lead_message' => mb_substr(trim((string) ($data['message'] ?? '')), 0, 4000),
      'field_lead_source' => $source,
      'field_lead_recaptcha' => $score,
      'field_lead_ip' => $ip,
    ])->save();

    $this->flood->register(self::FLOOD_EVENT, self::FLOOD_WINDOW, $ip);

    return $this->noStore(['ok' => TRUE], 201);
  }

  /**
   * This endpoint writes, so no layer may ever cache its response.
   */
  private function noStore(array $payload, int $status): JsonResponse {
    $response = new JsonResponse($payload, $status);
    $response->headers->set('Cache-Control', 'no-store');
    return $response;
  }
}
