<?php

namespace Drupal\commerce_onepayvn\Exception;

use Exception;

/**
 * Class MissingDataException.
 *
 * @package Drupal\commerce_onepayvn\Exception
 */
class MissingDataException extends Exception {

  /**
   * MissingDataException constructor.
   *
   * @param string $field_name
   *   Field name in OnePay data.
   */
  public function __construct($field_name) {
    parent::__construct($field_name . ' is a require field.');
  }

}
