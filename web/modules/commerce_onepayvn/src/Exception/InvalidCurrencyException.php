<?php

namespace Drupal\commerce_onepayvn\Exception;

/**
 * Class InvalidCurrencyException.
 *
 * @package Drupal\commerce_onepayvn\Exception
 */
class InvalidCurrencyException extends \Exception {

  /**
   * InvalidCurrencyException constructor.
   */
  public function __construct() {
    parent::__construct('Invalid currency use on this payment gateway.');
  }

}
