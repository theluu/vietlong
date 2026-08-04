<?php

declare(strict_types=1);

namespace Drupal\keybolts_core\Controller;

use Drupal\Core\Controller\ControllerBase;
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
    'home_page' => ['Trang chủ', '/', 'Hero, dải tin cậy, giải pháp, công nghệ, form tư vấn'],
    'about_page' => ['Giới thiệu', '/gioi-thieu', 'Hero, câu chuyện, khách hàng, quy trình, cam kết'],
    'news_page' => ['Tin tức', '/tin-tuc', 'Tiêu đề và mô tả trang danh sách'],
    'projects_page' => ['Dự án', '/du-an', 'Tiêu đề và mô tả trang danh sách'],
    'dealers_page' => ['Đại lý', '/dai-ly', 'Quyền lợi, điều kiện, form đăng ký'],
    'contact_page' => ['Liên hệ', '/lien-he', 'Kênh liên hệ, form, thông tin công ty'],
    'policies_page' => ['Chính sách', '/chinh-sach', 'Các mục chính sách'],
    'site_settings' => ['Cấu hình chung', NULL, 'Hotline, footer, mạng xã hội, SEO — áp dụng mọi trang'],
  ];

  /**
   * Collections: bundle => [label, public path, singular noun].
   */
  private const COLLECTIONS = [
    'product' => ['Sản phẩm', '/san-pham', 'sản phẩm'],
    'article' => ['Bài viết', '/tin-tuc', 'bài viết'],
    'project' => ['Dự án', '/du-an', 'dự án'],
    'branch' => ['Cơ sở / Showroom', NULL, 'cơ sở'],
  ];

  public function build(): array {
    $name = $this->currentUser()->getDisplayName();
    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['kb-dash']],
      '#attached' => ['library' => ['keybolts_core/dashboard']],
      'hero' => [
        '#markup' => '<div class="kb-dash-hero">'
        . '<span class="kb-dash-eyebrow">Keybolts</span>'
        . '<h2>Xin chào, ' . htmlspecialchars($name, ENT_QUOTES) . '</h2>'
        . '<p>Chọn trang bạn muốn sửa. Mỗi biểu mẫu được chia tab theo đúng thứ tự các khối hiển thị trên website, '
        . 'nên bạn sửa ở đâu là biết nó nằm chỗ nào ngoài trang. Lưu xong nội dung lên ngay, không cần thao tác gì thêm.</p>'
        . '</div>',
      ],
      'pages' => $this->section('Nội dung từng trang', $this->pageCards()),
      'collections' => $this->section('Danh mục nội dung', $this->collectionCards()),
      'leads' => $this->section('Yêu cầu liên hệ', $this->leadCards()),
      'settings' => $this->section('Cấu hình hệ thống', $this->systemCards()),
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
    foreach (self::PAGES as $bundle => [$label, $path, $desc]) {
      $node = $this->singleton($bundle);
      if (!$node) {
        continue;
      }
      $cards[] = $this->card(
        $label,
        $desc,
        Url::fromRoute('entity.node.edit_form', ['node' => $node->id()]),
        $path,
      );
    }
    return $cards;
  }

  private function collectionCards(): array {
    $cards = [];
    foreach (self::COLLECTIONS as $bundle => [$label, $path, $noun]) {
      $count = (int) $this->entityTypeManager()->getStorage('node')->getQuery()
        ->accessCheck(FALSE)->condition('type', $bundle)->count()->execute();
      $cards[] = $this->card(
        $label,
        "{$count} {$noun}",
        Url::fromRoute('system.admin_content', [], ['query' => ['type' => $bundle]]),
        $path,
        Url::fromRoute('node.add', ['node_type' => $bundle]),
        (string) $count,
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
        "{$week} yêu cầu mới trong 7 ngày qua",
        Url::fromRoute('system.admin_content', [], ['query' => ['type' => 'lead']]),
        NULL,
        NULL,
        (string) $total,
      ),
    ];
  }

  private function systemCards(): array {
    $cards = [
      $this->card('Menu chính', 'Thêm, sửa, sắp xếp các mục trên thanh điều hướng',
        Url::fromRoute('entity.menu.edit_form', ['menu' => 'main'])),
    ];
    // reCAPTCHA holds a secret key, so it stays with administrators.
    if ($this->currentUser()->hasPermission('administer site configuration')) {
      $cards[] = $this->card('reCAPTCHA v3', 'Bật/tắt, khoá, ngưỡng điểm và kiểm tra kết nối',
        Url::fromRoute('keybolts_core.recaptcha_settings'));
    }
    return $cards;
  }

  private function card(string $title, string $desc, Url $edit, ?string $view = NULL, ?Url $add = NULL, ?string $count = NULL): array {
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
      'count' => $count === NULL ? [] : ['#markup' => '<span class="kb-dash-count">' . $count . '</span>'],
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

}
