<?php

declare(strict_types=1);

namespace Drupal\keybolts_core\Theme;

use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;

/**
 * Gives editors Gin and leaves everyone else on the site's admin theme.
 *
 * The client's editors get a polished, branded admin; administrators keep
 * whatever Drupal is configured with, so upgrading or debugging never depends
 * on a contributed theme rendering correctly.
 */
final class EditorThemeNegotiator implements ThemeNegotiatorInterface {

  private const ROLE = 'bien_tap_vien';
  private const THEME = 'gin';

  public function __construct(
    private readonly AccountInterface $currentUser,
    private readonly AdminContext $adminContext,
  ) {}

  public function applies(RouteMatchInterface $route_match): bool {
    $route = $route_match->getRouteObject();
    if (!$route || !$this->adminContext->isAdminRoute($route)) {
      return FALSE;
    }
    return in_array(self::ROLE, $this->currentUser->getRoles(), TRUE);
  }

  public function determineActiveTheme(RouteMatchInterface $route_match): ?string {
    return self::THEME;
  }

}
