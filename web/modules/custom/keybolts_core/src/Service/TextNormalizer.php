<?php

declare(strict_types=1);

namespace Drupal\keybolts_core\Service;

/**
 * Normalises Vietnamese text for diacritic-insensitive matching.
 */
class TextNormalizer {

  /**
   * Vietnamese letters mapped to their ASCII equivalents.
   *
   * Explicit rather than relying on the intl Transliterator, because the
   * transliterator's handling of 'đ' varies across ICU versions and this
   * mapping must stay stable — it is written into stored search text.
   */
  private const MAP = [
    'à' => 'a', 'á' => 'a', 'ạ' => 'a', 'ả' => 'a', 'ã' => 'a',
    'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ậ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a',
    'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ặ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a',
    'è' => 'e', 'é' => 'e', 'ẹ' => 'e', 'ẻ' => 'e', 'ẽ' => 'e',
    'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ệ' => 'e', 'ể' => 'e', 'ễ' => 'e',
    'ì' => 'i', 'í' => 'i', 'ị' => 'i', 'ỉ' => 'i', 'ĩ' => 'i',
    'ò' => 'o', 'ó' => 'o', 'ọ' => 'o', 'ỏ' => 'o', 'õ' => 'o',
    'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ộ' => 'o', 'ổ' => 'o', 'ỗ' => 'o',
    'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ợ' => 'o', 'ở' => 'o', 'ỡ' => 'o',
    'ù' => 'u', 'ú' => 'u', 'ụ' => 'u', 'ủ' => 'u', 'ũ' => 'u',
    'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ự' => 'u', 'ử' => 'u', 'ữ' => 'u',
    'ỳ' => 'y', 'ý' => 'y', 'ỵ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y',
    'đ' => 'd',
  ];

  /**
   * Lowercases, strips diacritics and reduces separators to single spaces.
   */
  public function normalize(string $text): string {
    $text = mb_strtolower($text, 'UTF-8');
    $text = strtr($text, self::MAP);
    // Anything that is not an ASCII letter or digit becomes a separator.
    $text = preg_replace('/[^a-z0-9]+/', ' ', $text) ?? '';
    return trim($text);
  }
}
