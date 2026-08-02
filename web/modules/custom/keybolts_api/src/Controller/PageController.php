<?php

declare(strict_types=1);

namespace Drupal\keybolts_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\keybolts_api\ApiEnvelope;
use Drupal\keybolts_api\Serializer\BranchSerializer;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Serves the singleton page payloads and the shared branch list.
 */
class PageController extends ControllerBase {

  public function __construct(
    private readonly BranchSerializer $branches,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static($container->get('keybolts_api.branch_serializer'));
  }

  /**
   * GET /api/v1/branches
   */
  public function branches() {
    return ApiEnvelope::make($this->branches->all(), [], [], ['node_list:branch']);
  }
}
