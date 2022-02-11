<?php
namespace Drupal\brood\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides route responses for the Example module.
 */
class BroodoverviewController extends ControllerBase {

    public function orderOverviewPage() {
      return [
        '#theme' => 'orderOverview',
        '#orders' => BroodoverviewController::getOrders()
      ];
    }

    public function itemOverviewPage()
    {
        return [
            '#theme' => 'itemOverview',
            '#items' => BroodoverviewController::getItems()
        ];
    }

    public static function getOrders()
    {
        $query = \Drupal::database()->query("SELECT * FROM brood_orders")->fetchAll();
        return $query;
    }

    public static function getItems()
    {
        $query = \Drupal::database()->query("SELECT * FROM brood_types")->fetchAll();
        return $query;
    }
}



