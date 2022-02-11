<?php

namespace Drupal\brood\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


class BroodForm extends FormBase{

    public function getFormId()
    {
        return 'broodform';
    }

    public function buildForm(array $form, FormStateInterface $form_state) {
        $form['title'] = array(
            '#type' => 'item',
            '#title' => $this->t('Bestelformulier'),
            '#attributes' => array('class' => array('broodform_title')),
        );

        $form['first_name'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Voornaam:'),
            '#required' => true,
            '#attributes' => array('class' => array('broodform')),
        ];

        $form['last_name'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Achternaam:'),
            '#required' => true,
            '#attributes' => array('class' => array('broodform')),
        ];

        $form['phone'] = [
            '#type' => 'tel',
            '#title' => $this->t('Telefoonnummer:'),
            '#required' => true,
            '#attributes' => array('class' => array('broodform')),
        ];
    
        $form['select_brood'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Bestel brood:'),
            '#attributes' => array('class' => array('broodform_checkbox')),
        ];
        
        $form['select_brood_type'] = [
            '#type' => 'radios',
            '#title' => $this->t('Broodsoort:'),
            '#options' => [],
            '#attributes' => array('class' => array('broodform_options')),
            '#states' => [
                'visible' => [
                    ':input[name="select_brood"]' => [
                        'checked' => true,
                    ],
                ],
            ],
        ];

        $dbBrood = \Drupal::database()->query("SELECT items FROM brood_types WHERE order_type = :order_type", [":order_type" => 'brood'])->fetchAll();

        foreach ($dbBrood as $value) {
            $form['select_brood_type']['#options'][$value->items] = $value->items;
        }

        $form['select_koek'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Bestel koffiekoeken:'),
            '#attributes' => array('class' => array('broodform_checkbox')),
        ];

        $form['select_koek_type'] = [
            '#type' => 'checkboxes',
            '#title' => $this->t('Soort koffiekoeken:'),
            '#options' =>[],
            '#attributes' => array('class' => array('broodform_options')),
            '#states' => [
                'visible' => [
                    ':input[name="select_koek"]' => [
                        'checked' => true,
                    ],
                ],
            ],
        ];

        $dbKoek = \Drupal::database()->query("SELECT items FROM brood_types WHERE order_type = :order_type", [":order_type" => 'koek'])->fetchAll();
        foreach ($dbKoek as $value) {
            $form['select_koek_type']['#options'][$value->items] = $value->items;
        }

        $form['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Verzenden'),
            '#attributes' => array('class' => array('broodform_submit')),
        ];

        return $form;
      }

      public function submitForm(array &$form, FormStateInterface $form_state){
        $ordered_brood = $form_state->getValue('select_brood_type');
        $ordered_koek_array = $form_state->getValue('select_koek_type');
        $ordered_koek = "";
        
        foreach ($ordered_koek_array as $koek) {
            $ordered_koek .= $koek . ",";
        }

        if ($form_state->getValue('select_brood') == 1 && $form_state->getValue('select_koek') == 1) {
            $order_type = "both";
            $orders = "type brood: " . $ordered_brood . "\n type koffiekoeken: " . $ordered_koek;
        } elseif ($form_state->getValue('select_brood') == 1 && $form_state->getValue('select_brood_type') == true) {
            $order_type = "brood";
            $orders = "type brood: " . $ordered_brood;
        } elseif ($form_state->getValue('select_koek') == 1 && $form_state->getValue('select_koek_type') == true) {
            $order_type = "koek";
            $orders = "type koffiekoeken: " . $ordered_koek;
        } else {
            $order_type = "empty";
        }

        if ($order_type === "both" || $order_type === "brood" || $order_type === "koek") {
            $order_number = \Drupal::database()->insert('brood_orders')
                ->fields([
                    'first_name' => $form_state->getValue('first_name'),
                    'last_name' => $form_state->getValue('last_name'),
                    'phone' => $form_state->getValue('phone'),
                    'order_type' => $order_type,
                    'order_items' => $orders,
                    'order_date' => time()
                ])
                ->execute();
           
            $query = \Drupal::database()->query("SELECT order_date FROM brood_orders WHERE id = :id", [':id' => $order_number])->fetchField();

            $timestamp = date('d/m/Y H:i:s', $query);
            $weekday = date('N', $query);

            if ($weekday == 2) {
                $addedTime = strtotime('+2 day', $query);
                $order_ready = date('d/m/Y', $addedTime);
            } else { 
                $addedTime = strtotime('+1 day', $query);
                $order_ready = date('d/m/Y', $addedTime);
            }

            \Drupal::messenger()->addMessage('Je bestelling wordt doorgegeven, je bestelnummer is: 0X' . $order_number . " en werd geplaatst op " . $timestamp . ". Je kan ze komen afhalen op " . $order_ready, 'success');
        } else {
            \Drupal::messenger()->addMessage('Je hebt geen items aangeduid om te bestellen', 'error');
        }
      }

}
