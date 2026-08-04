<?php

declare(strict_types=1);

namespace Drupal\keybolts_core\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;

/**
 * One screen that lists the site by its pages.
 *
 * An editor thinks "I want to change the homepage", not "I want to edit node
 * 82 of type home_page". Drupal's own /admin/content is organised the other
 * way round, so this sits in front of it.
 */
final class DashboardController extends ControllerBase {

  /**
   * Singleton pages: bundle => [label, public path, what lives there].
   */
  private const PAGES = [
    'home_page' => ['Trang chủ', '/', 'Hero, dải tin cậy, giải pháp, công nghệ, form tư vấn', 'home'],
    'about_page' => ['Giới thiệu', '/gioi-thieu', 'Câu chuyện, khách hàng, quy trình, cam kết', 'info'],
    'news_page' => ['Tin tức', '/tin-tuc', 'Tiêu đề và mô tả trang danh sách bài viết', 'news'],
    'projects_page' => ['Dự án', '/du-an', 'Tiêu đề và mô tả trang danh sách dự án', 'layers'],
    'dealers_page' => ['Đại lý', '/dai-ly', 'Quyền lợi, điều kiện, form đăng ký', 'handshake'],
    'contact_page' => ['Liên hệ', '/lien-he', 'Kênh liên hệ, form, thông tin công ty', 'phone'],
    'policies_page' => ['Chính sách', '/chinh-sach', 'Bảo hành, đổi trả, giao nhận', 'shield'],
    'site_settings' => ['Cấu hình chung', NULL, 'Hotline, footer, mạng xã hội, SEO — áp dụng mọi trang', 'settings'],
  ];

  /**
   * Collections: bundle => [label, public path, singular noun].
   */
  private const COLLECTIONS = [
    'product' => ['Sản phẩm', '/san-pham', 'Khóa, tay nắm, bản lề và phụ kiện', 'box'],
    'article' => ['Bài viết', '/tin-tuc', 'Hướng dẫn chọn khóa, so sánh, FAQ', 'news'],
    'project' => ['Dự án', '/du-an', 'Công trình đã bàn giao', 'layers'],
    'branch' => ['Cơ sở / Showroom', NULL, 'Địa chỉ hiển thị trên toàn site', 'pin'],
  ];

  public function build(): array {
    $name = $this->currentUser()->getDisplayName();
    // Logout needs the CSRF token Drupal puts on the route.
    $logout = Url::fromRoute('user.logout')->toString();
    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['kb-dash']],
      '#attached' => ['library' => ['keybolts_core/dashboard']],
      'hero' => [
        '#markup' => Markup::create('<div class="kb-dash-hero">'
        . '<span class="kb-dash-eyebrow">Keybolts</span>'
        . '<h2>Xin chào, ' . htmlspecialchars($name, ENT_QUOTES) . '</h2>'
        . '<p>Chọn trang bạn muốn sửa. Mỗi biểu mẫu được chia tab theo đúng thứ tự các khối hiển thị trên website, '
        . 'nên bạn sửa ở đâu là biết nó nằm chỗ nào ngoài trang. Lưu xong nội dung lên ngay, không cần thao tác gì thêm.</p>'
        . '<div class="kb-dash-heroactions">'
        . '<a class="kb-dash-herobtn" href="/" target="_blank" rel="noopener">'
        . '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" '
        . 'stroke-linejoin="round"><path d="M15 3h6v6"/><path d="M10 14 21 3"/>'
        . '<path d="M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5"/></svg>Xem website</a>'
        . '<a class="kb-dash-herobtn kb-dash-herobtn--out" href="' . $logout . '">'
        . '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" '
        . 'stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>'
        . '<path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>Đăng xuất</a>'
        . '</div>'
        . '</div>'),
      ],
      'pages' => $this->section('Nội dung từng trang', $this->pageCards()),
      'collections' => $this->section('Danh mục nội dung', $this->collectionCards()),
      // Leads and system settings were one card each, which read as two
      // stranded blocks at the bottom of the page. One row instead.
      'other' => $this->section(
        'Yêu cầu khách gửi & cấu hình',
        array_merge($this->leadCards(), $this->systemCards()),
      ),
    ];
  }

  /**
   * Children have to sit as direct keys on the container; nesting them under
   * one key renders the wrapper and drops everything inside it.
   */
  private function section(string $title, array $cards): array {
    $grid = ['#type' => 'container', '#attributes' => ['class' => ['kb-dash-grid']]];
    foreach ($cards as $i => $card) {
      $grid["card_{$i}"] = $card;
    }
    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['kb-dash-section']],
      'title' => ['#markup' => '<h2 class="kb-dash-heading">' . $title . '</h2>'],
      'cards' => $grid,
    ];
  }

  private function pageCards(): array {
    $cards = [];
    foreach (self::PAGES as $bundle => [$label, $path, $desc, $icon]) {
      $node = $this->singleton($bundle);
      if (!$node) {
        continue;
      }
      $cards[] = $this->card(
        $label,
        $desc,
        Url::fromRoute('entity.node.edit_form', ['node' => $node->id()]),
        $path,
        NULL,
        NULL,
        $icon,
      );
    }
    return $cards;
  }

  private function collectionCards(): array {
    $cards = [];
    foreach (self::COLLECTIONS as $bundle => [$label, $path, $desc, $icon]) {
      $count = (int) $this->entityTypeManager()->getStorage('node')->getQuery()
        ->accessCheck(FALSE)->condition('type', $bundle)->count()->execute();
      $cards[] = $this->card(
        $label,
        $desc,
        Url::fromRoute('system.admin_content', [], ['query' => ['type' => $bundle]]),
        $path,
        Url::fromRoute('node.add', ['node_type' => $bundle]),
        (string) $count,
        $icon,
      );
    }
    return $cards;
  }

  private function leadCards(): array {
    $storage = $this->entityTypeManager()->getStorage('node');
    $total = (int) $storage->getQuery()->accessCheck(FALSE)
      ->condition('type', 'lead')->count()->execute();
    $week = (int) $storage->getQuery()->accessCheck(FALSE)
      ->condition('type', 'lead')
      ->condition('created', \Drupal::time()->getRequestTime() - 604800, '>')
      ->count()->execute();
    return [
      $this->card(
        'Yêu cầu liên hệ',
        $week > 0
          ? "Tổng {$total} yêu cầu · {$week} mới trong 7 ngày qua"
          : "Tổng {$total} yêu cầu · chưa có yêu cầu mới tuần này",
        Url::fromRoute('system.admin_content', [], ['query' => ['type' => 'lead']]),
        NULL,
        NULL,
        (string) $total,
        'inbox',
      ),
    ];
  }

  private function systemCards(): array {
    $cards = [
      $this->card('Menu chính', 'Thêm, sửa, sắp xếp các mục trên thanh điều hướng',
        Url::fromRoute('entity.menu.edit_form', ['menu' => 'main']), NULL, NULL, NULL, 'menu'),
    ];
    // reCAPTCHA holds a secret key, so it stays with administrators.
    if ($this->currentUser()->hasPermission('administer site configuration')) {
      $cards[] = $this->card('reCAPTCHA v3', 'Bật/tắt, khoá, ngưỡng điểm và kiểm tra kết nối',
        Url::fromRoute('keybolts_core.recaptcha_settings'), NULL, NULL, NULL, 'lock');
    }
    return $cards;
  }

  private function card(string $title, string $desc, Url $edit, ?string $view = NULL, ?Url $add = NULL, ?string $count = NULL, string $icon = 'page'): array {
    $links = [];
    $links[] = ['#type' => 'link', '#title' => 'Chỉnh sửa', '#url' => $edit, '#attributes' => ['class' => ['kb-dash-btn', 'kb-dash-btn--primary']]];
    if ($add) {
      $links[] = ['#type' => 'link', '#title' => 'Thêm mới', '#url' => $add, '#attributes' => ['class' => ['kb-dash-btn']]];
    }
    if ($view) {
      $links[] = ['#type' => 'link', '#title' => 'Xem trang', '#url' => Url::fromUri('base:' . ltrim($view, '/')), '#attributes' => ['class' => ['kb-dash-btn'], 'target' => '_blank']];
    }
    $actions = ['#type' => 'container', '#attributes' => ['class' => ['kb-dash-actions']]];
    foreach ($links as $i => $link) {
      $actions["link_{$i}"] = $link;
    }
    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['kb-dash-card']],
      'head' => [
        '#markup' => Markup::create('<div class="kb-dash-cardhead">'
        . '<span class="kb-dash-icon">' . self::icon($icon) . '</span>'
        . ($count === NULL ? '' : '<span class="kb-dash-count">' . $count . '</span>')
        . '</div>'),
      ],
      'title' => ['#markup' => '<h3>' . $title . '</h3>'],
      'desc' => ['#markup' => '<p>' . $desc . '</p>'],
      'links' => $actions,
    ];
  }

  private function singleton(string $bundle) {
    $nodes = $this->entityTypeManager()->getStorage('node')
      ->loadByProperties(['type' => $bundle]);
    return $nodes ? reset($nodes) : NULL;
  }


  /**
   * Inline so the dashboard needs no icon font or sprite request.
   */
  private static function icon(string $key): string {
    $paths = [
      'home' => '<path d="M3 10.2 12 3l9 7.2V21H3z"/><path d="M9 21v-7h6v7"/>',
      'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><path d="M12 8h.01"/>',
      'news' => '<path d="M4 5h13v14H4z"/><path d="M17 8h3v9a2 2 0 0 1-4 0"/><path d="M7 9h7M7 13h7"/>',
      'layers' => '<path d="m12 3 9 5-9 5-9-5z"/><path d="m3 13 9 5 9-5"/>',
      'handshake' => '<path d="m8 12 3 3 5-5"/><path d="M3 8h4l3-3h4l3 3h4v8h-4l-3 3h-4l-3-3H3z"/>',
      'phone' => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.1 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>',
      'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
      'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-2.9 1.2V21a2 2 0 1 1-4 0v-.1A1.7 1.7 0 0 0 7 19.4a1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1A1.7 1.7 0 0 0 3 15a1.7 1.7 0 0 0-1.6-1H1a2 2 0 1 1 0-4h.1A1.7 1.7 0 0 0 3 8.6a1.7 1.7 0 0 0-.3-1.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1A1.7 1.7 0 0 0 9 3V1a2 2 0 1 1 4 0v.1A1.7 1.7 0 0 0 15 3a1.7 1.7 0 0 0 1.9-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1A1.7 1.7 0 0 0 21 9h.1a2 2 0 1 1 0 4H21a1.7 1.7 0 0 0-1.6 1z"/>',
      'box' => '<path d="m21 8-9-5-9 5v8l9 5 9-5z"/><path d="m3 8 9 5 9-5"/><path d="M12 13v8"/>',
      'pin' => '<path d="M12 21s7-6 7-11a7 7 0 1 0-14 0c0 5 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>',
      'inbox' => '<path d="M3 12h5l2 3h4l2-3h5"/><path d="m5 5 -2 7v7h18v-7l-2-7z"/>',
      'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
      'lock' => '<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
      'page' => '<path d="M6 3h8l4 4v14H6z"/><path d="M14 3v4h4"/>',
    ];
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" '
      . 'stroke-linecap="round" stroke-linejoin="round">' . ($paths[$key] ?? $paths['page']) . '</svg>';
  }

}
