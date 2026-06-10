<?php

namespace Drupal\mc_tacjs\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Subscribe to route events to alter tacjs routes.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $routes = [
      'tacjs.manage_dialog',
      'tacjs.add_services',
      'tacjs.edit_texts',
    ];

    foreach ($routes as $route_name) {
      if ($route = $collection->get($route_name)) {
        $route->setRequirement('_access', 'FALSE');
      }
    }
  }
}
