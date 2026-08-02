<?php

declare(strict_types=1);

namespace Drupal\keybolts_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Flood\FloodInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * The only write endpoint on the site.
 */
class ContactController extends ControllerBase {

  private const ALLOWED_SOURCES = ['contact', 'dealer', 'consult'];
  private const FLOOD_EVENT = 'keybolts_api.contact';
  private const FLOOD_LIMIT = 5;
  private const FLOOD_WINDOW = 600;

  public function __construct(
    private readonly FloodInterface $flood,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static($container->get('flood'));
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
      return $this->noStore(['error' => 'Quá nhiều yêu cầu. Vui lòng thử lại sau.'], 429);
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

    $this->entityTypeManager()->getStorage('contact_submission')->create([
      'name' => mb_substr($name, 0, 255),
      'phone' => mb_substr($phone, 0, 60),
      'message' => mb_substr(trim((string) ($data['message'] ?? '')), 0, 4000),
      'source' => $source,
      'ip' => $ip,
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
