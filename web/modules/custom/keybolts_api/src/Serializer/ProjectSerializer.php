<?php

declare(strict_types=1);

namespace Drupal\keybolts_api\Serializer;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** Serializes published projects in editor-controlled order. */
final class ProjectSerializer {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly ImageSerializer $imageSerializer,
  ) {}

  public function all(): array {
    $storage = $this->entityTypeManager->getStorage('node');
    $ids = $storage->getQuery()->accessCheck(TRUE)
      ->condition('type', 'project')->condition('status', 1)
      ->sort('field_sort_order')->sort('nid')->execute();
    return array_values(array_map($this->toArray(...), $storage->loadMultiple($ids)));
  }

  public function one(string $slug): array {
    $storage = $this->entityTypeManager->getStorage('node');
    $ids = $storage->getQuery()->accessCheck(TRUE)
      ->condition('type', 'project')->condition('status', 1)
      ->condition('field_project_slug', $slug)->range(0, 1)->execute();
    if (!$ids || !($node = $storage->load(reset($ids)))) {
      throw new NotFoundHttpException();
    }
    return $this->toArray($node);
  }

  private function toArray(NodeInterface $node): array {
    return [
      'id' => (int) $node->id(),
      'slug' => (string) $node->get('field_project_slug')->value,
      'typeKey' => (string) $node->get('field_project_type_key')->value,
      'type' => (string) $node->get('field_project_type')->value,
      'title' => $node->label(),
      'description' => (string) $node->get('field_project_desc')->value,
      'body' => $this->body($node),
      'products' => (string) $node->get('field_project_products')->value,
      'image' => $this->imageSerializer->fromField($node->get('field_project_image')),
    ];
  }

  /**
   * Editor-written HTML, run through the text format's filters.
   *
   * The detail page renders this with v-html, so filtering here is what keeps
   * a pasted script tag from reaching the browser.
   */
  private function body(NodeInterface $node): string {
    if (!$node->hasField('field_project_body') || $node->get('field_project_body')->isEmpty()) {
      return '';
    }
    $item = $node->get('field_project_body')->first();
    return (string) check_markup(
      (string) $item->value,
      $item->format ?: 'basic_html',
      $node->language()->getId(),
    );
  }

}
