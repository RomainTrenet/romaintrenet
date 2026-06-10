<?php

namespace Drupal\mc_tacjs\Helper;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Routing\AdminContext;
use Drupal\path_alias\AliasManagerInterface;

class McTacjsHelper {

  protected ConfigFactoryInterface $configFactory;
  protected $adminContext;
  protected $currentPath;
  protected $aliasManager;

  public function __construct(
    ConfigFactoryInterface $config_factory,
    AdminContext $admin_context,
    CurrentPathStack $current_path,
    AliasManagerInterface $alias_manager
  ) {
    $this->configFactory = $config_factory;
    $this->adminContext = $admin_context;
    $this->currentPath = $current_path;
    $this->aliasManager = $alias_manager;
  }

  /**
   * Generate "Tarteaucitron" script url.
   *
   * @return string|null
   *   The URL ou NULL.
   */
  public function getTacJsUrl(): ?string {
    $config = $this->configFactory->get('mc_tacjs.admin_settings');

    $script = $config->get('script');
    $domain = $config->get('domain');
    $uuid = $config->get('uuid');

    if ($script && $domain && $uuid) {
      return $script . '?domain=' . $domain . '&uuid=' . $uuid;
    }

    return NULL;
  }

  /**
   * Check if the script needs to be loaded.
   *
   * @return bool
   */
  public function shouldLoadScript(): bool {
    $config = $this->configFactory->get('mc_tacjs.admin_settings');

    // Excluse admin route or not.
    if (
      $config->get('exclude_admin') &&
      $this->adminContext->isAdminRoute()
    ) {
      return FALSE;
    }

    // Get pages config.
    $pages = $config->get('pages');

    // Ensure $pages is defined (even empty).
    if (is_null($pages)) {
      return FALSE;
    }

    // If not page, check if mode is "exclude", as excluding nothing would result in "true", including nothing in "false".
    $pages_mode = $config->get('pages_mode') ?? 'exclude';
    if (
      is_string($pages) &&
      empty(trim($pages))
    ) {
      return $pages_mode === 'exclude';
    }

    // Compare the current path to path list.
    $path = $this->currentPath->getPath();
    $path_alias = $this->aliasManager->getAliasByPath($path);
    $path_list = preg_split('/\r\n|[\r\n]/', $pages);

    // Check if one of the paths matches.
    $match = FALSE;
    foreach ($path_list as $pattern) {
      $pattern = trim($pattern);
      if ($pattern === '') {
        continue;
      }

      // Replace * by .*
      $regex = '@^' . str_replace('*', '.*', preg_quote($pattern, '@')) . '$@';
      if (preg_match($regex, $path_alias)) {
        $match = TRUE;
        break;
      }
    }

    // If mode is include check matches.
    return ($pages_mode === 'include') ? $match : !$match;
  }

}
