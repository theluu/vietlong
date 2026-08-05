<?php

declare(strict_types=1);

namespace Drupal\keybolts_api\Serializer;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\image\Plugin\Field\FieldType\ImageItem;

/**
 * Turns one image field into the shape an <img srcset> needs.
 *
 * Knows nothing about articles, projects or products — it takes a field and
 * gives back widths, which is why all three serializers can share it.
 */
final class ImageSerializer {

  /** Width => the style name of each format. Smallest first. */
  private const STYLES = [
    400 => ['avif' => 'kb_card_400_avif', 'webp' => 'kb_card_400_webp'],
    800 => ['avif' => 'kb_card_800_avif', 'webp' => 'kb_card_800_webp'],
    1200 => ['avif' => 'kb_hero_1200_avif', 'webp' => 'kb_hero_1200_webp'],
    1600 => ['avif' => 'kb_hero_1600_avif', 'webp' => 'kb_hero_1600_webp'],
  ];

  /** Bare `src` for browsers that ignore srcset. WebP, because it runs everywhere. */
  private const DEFAULT_STYLE = 'kb_card_800_webp';

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly FileUrlGeneratorInterface $fileUrlGenerator,
  ) {}

  /** Ảnh đầu tiên của một trường. Trường nhiều ảnh thì lặp và gọi fromItem(). */
  public function fromField(FieldItemListInterface $list): ?array {
    $item = $list->first();
    return $item instanceof ImageItem ? $this->fromItem($item) : NULL;
  }

  public function fromItem(?ImageItem $item): ?array {
    if (!$item || !$item->entity) {
      return NULL;
    }
    $uri = $item->entity->getFileUri();
    $width = (int) $item->width;
    $storage = $this->entityTypeManager->getStorage('image_style');

    // `upscale: false` means a style wider than the original silently returns
    // the original's size. Advertising `1600w` for an 800px file would make the
    // browser download it believing it is bigger than it is, and then not
    // download the one it actually needed. So only offer styles that fit —
    // keeping the smallest regardless, or a tiny original would have no src.
    $usable = array_filter(
      self::STYLES,
      static fn (int $styleWidth): bool => $width === 0 || $styleWidth <= $width,
      ARRAY_FILTER_USE_KEY,
    );
    if (!$usable) {
      $usable = array_slice(self::STYLES, 0, 1, TRUE);
    }

    // Both formats are built from the same $usable set in one pass, so the
    // "which sizes fit" rule can't drift between them if someone edits only
    // one branch later.
    $srcset = [];
    $srcsetAvif = [];
    foreach ($usable as $styleWidth => $names) {
      $webp = $storage->load($names['webp']);
      if ($webp) {
        $srcset[] = $webp->buildUrl($uri) . ' ' . $styleWidth . 'w';
      }
      $avif = $storage->load($names['avif']);
      if ($avif) {
        $srcsetAvif[] = $avif->buildUrl($uri) . ' ' . $styleWidth . 'w';
      }
    }

    $default = $storage->load(self::DEFAULT_STYLE) ?? $storage->load(reset($usable)['webp']);

    return [
      'url' => $default
        ? $default->buildUrl($uri)
        : $this->fileUrlGenerator->generateAbsoluteString($uri),
      'srcset' => implode(', ', $srcset),
      'srcsetAvif' => implode(', ', $srcsetAvif),
      'width' => $width,
      'height' => (int) $item->height,
      'alt' => (string) $item->alt,
    ];
  }

}
