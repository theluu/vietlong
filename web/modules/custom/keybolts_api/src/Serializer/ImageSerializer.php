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

  /** Style machine name => the width it scales to. Ordered smallest first. */
  private const STYLES = [
    'kb_card_400' => 400,
    'kb_card_800' => 800,
    'kb_hero_1200' => 1200,
    'kb_hero_1600' => 1600,
  ];

  /** What a bare `src` falls back to for browsers that ignore srcset. */
  private const DEFAULT_STYLE = 'kb_card_800';

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
    );
    if (!$usable) {
      $usable = array_slice(self::STYLES, 0, 1, TRUE);
    }

    $srcset = [];
    foreach ($usable as $name => $styleWidth) {
      $style = $storage->load($name);
      if ($style) {
        $srcset[] = $style->buildUrl($uri) . ' ' . $styleWidth . 'w';
      }
    }

    $default = $storage->load(self::DEFAULT_STYLE) ?? $storage->load(array_key_first($usable));

    return [
      'url' => $default
        ? $default->buildUrl($uri)
        : $this->fileUrlGenerator->generateAbsoluteString($uri),
      'srcset' => implode(', ', $srcset),
      'width' => $width,
      'height' => (int) $item->height,
      'alt' => (string) $item->alt,
    ];
  }

}
