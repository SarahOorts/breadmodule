<?php

namespace Drupal\brood\Plugin\Block;

use Drupal\Core\Block\Blockbase;

/**
 * Provides a "brood" block.
 * 
 * @Block(
 *   id = "brood_block",
 *   admin_label = @Translation("brood block")
 * )
 */

class broodBlock extends BlockBase {
    public function build(){
        $form = \Drupal::formBuilder()->getForm('Drupal\brood\Form\BroodForm');
        return [
            $form,
            '#attached' => ['library' => ['brood/brood']]
        ];
    }
}