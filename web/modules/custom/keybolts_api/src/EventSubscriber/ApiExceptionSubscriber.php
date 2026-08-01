<?php

declare(strict_types=1);

namespace Drupal\keybolts_api\EventSubscriber;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Renders exceptions as JSON for API requests instead of themed HTML.
 *
 * This backend only ever serves a Nuxt SSR frontend over JSON on paths
 * under /api/v1/. A 404 for a stale or bad slug is routine there, not an
 * edge case, so it must come back as a parseable JSON body — not a full
 * themed Drupal error page. Non-API paths (admin UI, etc.) are left
 * completely untouched.
 */
class ApiExceptionSubscriber implements EventSubscriberInterface {

  public static function getSubscribedEvents(): array {
    return [
      // Priority higher than the core exception HTML subscribers so this
      // wins for API paths before a themed response gets built.
      KernelEvents::EXCEPTION => ['onException', 50],
    ];
  }

  public function onException(ExceptionEvent $event): void {
    $request = $event->getRequest();
    if (!str_starts_with($request->getPathInfo(), '/api/v1/')) {
      return;
    }

    $exception = $event->getThrowable();
    $status = $exception instanceof HttpExceptionInterface
      ? $exception->getStatusCode()
      : 500;

    // Never leak internals for 5xx. For 4xx, prefer the exception's own
    // message when one was set (e.g. a validation error); otherwise fall
    // back to the standard HTTP reason phrase — never the raw exception
    // message, which can carry stack-trace-adjacent detail even on 4xx.
    if ($status >= 500) {
      $message = 'An unexpected error occurred.';
    }
    else {
      $message = $exception->getMessage() !== ''
        ? $exception->getMessage()
        : (Response::$statusTexts[$status] ?? 'Error');
    }

    $response = new JsonResponse([
      'error' => [
        'code' => $status,
        'message' => $message,
      ],
    ], $status);

    if ($exception instanceof HttpExceptionInterface) {
      $headers = $exception->getHeaders();
      if ($headers) {
        $response->headers->add($headers);
      }
    }

    $event->setResponse($response);
  }

}
