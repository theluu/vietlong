<?php

declare(strict_types=1);

namespace Drupal\keybolts_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\keybolts_api\ApiEnvelope;
use Drupal\keybolts_api\Serializer\BranchSerializer;
use Drupal\keybolts_api\Serializer\ArticleSerializer;
use Drupal\keybolts_api\Serializer\PageSerializer;
use Drupal\keybolts_api\Serializer\ProjectSerializer;
use Drupal\keybolts_api\Serializer\SiteSerializer;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Serves the singleton page payloads and the shared branch list.
 */
class PageController extends ControllerBase {

  private const PAGE_TYPES = [
    'about' => 'about_page',
    'dealers' => 'dealers_page',
    'contact' => 'contact_page',
    'policies' => 'policies_page',
    'news' => 'news_page',
    'projects' => 'projects_page',
  ];

  public function __construct(
    private readonly BranchSerializer $branches,
    private readonly PageSerializer $pages,
    private readonly ArticleSerializer $articles,
    private readonly ProjectSerializer $projects,
    private readonly SiteSerializer $site,
  ) {}

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('keybolts_api.branch_serializer'),
      $container->get('keybolts_api.page_serializer'),
      $container->get('keybolts_api.article_serializer'),
      $container->get('keybolts_api.project_serializer'),
      $container->get('keybolts_api.site_serializer'),
    );
  }

  /**
   * GET /api/v1/site
   *
   * Every page needs this, so it is one request cached by tag rather than a
   * field on each page payload.
   */
  public function site() {
    return ApiEnvelope::make($this->site->all(), [], [], $this->site->cacheTags());
  }

  /**
   * GET /api/v1/branches
   */
  public function branches() {
    return ApiEnvelope::make($this->branches->all(), [], [], ['node_list:branch']);
  }

  /** GET /api/v1/articles */
  public function articles() {
    return ApiEnvelope::make($this->articles->all(), [], [], ['node_list:article']);
  }

  public function article(string $slug) {
    return ApiEnvelope::make($this->articles->one($slug), [], [], ['node_list:article']);
  }

  public function projects() {
    return ApiEnvelope::make($this->projects->all(), [], [], ['node_list:project']);
  }

  public function project(string $slug) {
    return ApiEnvelope::make($this->projects->one($slug), [], [], ['node_list:project']);
  }

  /**
   * GET /api/v1/page/{key}
   */
  public function page(string $key) {
    if (!isset(self::PAGE_TYPES[$key])) {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }
    $type = self::PAGE_TYPES[$key];
    $nodes = $this->entityTypeManager()->getStorage('node')
      ->loadByProperties(['type' => $type, 'status' => 1]);
    $node = $nodes ? reset($nodes) : NULL;
    if (!$node) {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }
    return ApiEnvelope::make(
      $this->pages->{$key}($node),
      [],
      [],
      ['node_list:' . $type],
    );
  }
}
