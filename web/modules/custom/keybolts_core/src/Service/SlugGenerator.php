<?php

declare(strict_types=1);

namespace Drupal\keybolts_core\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

/**
 * Derives a node's slug from its title.
 *
 * The slug is not decoration: the API looks articles and projects up by it, so
 * a wrong one does not make a page ugly, it makes the page unreachable. It
 * reuses TextNormalizer rather than Drupal's transliteration service because
 * that class deliberately pins its own Vietnamese mapping — ICU's handling of
 * 'đ' has moved between versions, and this string is stored, not recomputed.
 */
final class SlugGenerator {

  private const MAX_LENGTH = 150;

  public function __construct(
    private readonly TextNormalizer $normalizer,
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * The slug for a node, unique among published and unpublished siblings.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node being saved. It excludes itself from the uniqueness check —
   *   without that, every re-save of an unchanged article would walk its own
   *   slug one suffix further and break the link that worked a moment ago.
   * @param string $field
   *   The slug field to check against.
   */
  public function forNode(NodeInterface $node, string $field): string {
    $base = $this->fromTitle((string) $node->label());
    // A title of pure punctuation normalises to nothing, and an empty slug
    // 404s forever. Fall back to the bundle name and let the suffix loop below
    // make it unique rather than writing a second special case.
    if ($base === '') {
      $base = $node->bundle();
    }

    $candidate = $base;
    $n = 1;
    while ($this->taken($candidate, $node, $field)) {
      $n++;
      $candidate = $base . '-' . $n;
    }
    return $candidate;
  }

  /** Lowercase ASCII words joined by hyphens, matching the pathauto config. */
  public function fromTitle(string $title): string {
    $words = $this->normalizer->normalize($title);
    return mb_substr(str_replace(' ', '-', $words), 0, self::MAX_LENGTH);
  }

  private function taken(string $slug, NodeInterface $node, string $field): bool {
    $query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', $node->bundle())
      ->condition($field, $slug)
      ->range(0, 1);
    if (!$node->isNew()) {
      $query->condition('nid', $node->id(), '<>');
    }
    return (bool) $query->execute();
  }

}
