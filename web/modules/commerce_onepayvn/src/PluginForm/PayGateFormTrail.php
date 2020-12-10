<?php

namespace Drupal\commerce_onepayvn\PluginForm;

use Drupal\commerce_order\Entity\OrderInterface;

/**
 * Trait PayGateFormTrail.
 *
 * @package Drupal\commerce_onepayvn\PluginForm
 */
trait PayGateFormTrail {

  /**
   * Drupal\Component\Transliteration\TransliterationInterface.
   *
   * @var \Drupal\Component\Transliteration\TransliterationInterface
   */
  protected $transliteration;

  /**
   * Drupal\commerce_onepayvn\OnePay\VirtualPaymentClientInterface.
   *
   * @var \Drupal\commerce_onepayvn\OnePay\VirtualPaymentClientInterface
   */
  protected $vpc;

  /**
   * Gets the shipping profile, if exists.
   *
   * The function safely checks for the existence of the 'shipments' field,
   * which is installed by commerce_shipping. If the field does not exist or is
   * empty, NULL will be returned.
   *
   * The shipping profile is assumed to be the same for all shipments.
   * Therefore, it is taken from the first found shipment, or created from
   * scratch if no shipments were found.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order entity.
   *
   * @return \Drupal\profile\Entity\ProfileInterface|null
   *   The shipping profile.
   */
  protected function getShippingProfile(OrderInterface $order) {
    if ($order->hasField('shipments')) {
      /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
      foreach ($order->shipments->referencedEntities() as $shipment) {
        return $shipment->getShippingProfile();
      }
    }
    return NULL;
  }

  /**
   * This function to transliterate data before set.
   *
   * OnePAY payment don't allow vietnamese with sign.
   *
   * @param array $data
   *   Data to set.
   */
  public function transliterateData(array &$data) {
    foreach ($data as $key => &$value) {
      if (is_string($value)) {
        $value = $this->transliteration->transliterate($data[$key]);
      }
    }
  }

}
