<?php

declare(strict_types=1);

namespace Drupal\keybolts_api;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheableJsonResponse;

/**
 * Builds the single response shape every endpoint returns.
 */
final class ApiEnvelope {

  /**
   * @param array $cache_tags
   *   Tags that invalidate this response, e.g. ['node_list:product'].
   */
  public static function make(
    mixed $data,
    array $meta = [],
    array $facets = [],
    array $cache_tags = ['node_list:product'],
    int $max_age = 600,
  ): CacheableJsonResponse {
    $response = new CacheableJsonResponse([
      'data' => $data,
      'meta' => $meta,
      'facets' => (object) $facets,
    ]);
    $cacheability = (new CacheableMetadata())
      ->setCacheTags($cache_tags)
      ->setCacheContexts(['url.query_args'])
      ->setCacheMaxAge($max_age);
    $response->addCacheableDependency($cacheability);
    return $response;
  }
}
