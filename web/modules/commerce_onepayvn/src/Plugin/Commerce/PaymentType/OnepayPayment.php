<?php

namespace Drupal\commerce_onepayvn\Plugin\Commerce\PaymentType;

use Drupal\commerce_payment\Plugin\Commerce\PaymentType\PaymentTypeBase;

/**
 * Provides the OnePAY payment type.
 *
 * @CommercePaymentType(
 *   id = "onepayvn",
 *   label = @Translation("OnePay.vn"),
 * )
 */
class OnepayPayment extends PaymentTypeBase {

  /**
   * {@inheritdoc}
   */
  public function buildFieldDefinitions() {
    return [];
  }

}
