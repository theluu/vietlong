<?php

namespace Drupal\Tests\keybolts_core\Kernel;

use Drupal\KernelTests\KernelTestBase;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * @group keybolts
 */
#[RunTestsInSeparateProcesses]
class TextNormalizerTest extends KernelTestBase {

  protected static $modules = ['path_alias', 'keybolts_core'];

  public function testStripsVietnameseDiacritics(): void {
    $normalizer = $this->container->get('keybolts_core.text_normalizer');
    $this->assertSame('khoa van tay', $normalizer->normalize('Khóa Vân Tay'));
  }

  public function testHandlesDWithStroke(): void {
    $normalizer = $this->container->get('keybolts_core.text_normalizer');
    $this->assertSame('khoa dong dai sanh', $normalizer->normalize('Khóa Đồng Đại Sảnh'));
  }

  public function testCollapsesWhitespaceAndPunctuation(): void {
    $normalizer = $this->container->get('keybolts_core.text_normalizer');
    $this->assertSame('kb 1700 xl pvd', $normalizer->normalize('KB 1700-XL-PVD'));
  }

  public function testIsIdempotent(): void {
    $normalizer = $this->container->get('keybolts_core.text_normalizer');
    $once = $normalizer->normalize('Chốt Cremone Đồng');
    $this->assertSame($once, $normalizer->normalize($once));
  }
}
