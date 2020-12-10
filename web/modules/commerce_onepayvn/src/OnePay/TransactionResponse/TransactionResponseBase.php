<?php

namespace Drupal\commerce_onepayvn\OnePay\TransactionResponse;

/**
 * Class TransactionResponseBase.
 *
 * @package Drupal\commerce_onepayvn\VPC\TransactionResponse
 */
abstract class TransactionResponseBase {

  protected $code;

  protected $message;

  /**
   * TransactionResponse constructor.
   *
   * @param string $code
   *   Response code from request.
   */
  public function __construct($code) {
    $this->code = $code;
    $this->setMessageFromCode();
  }

  /**
   * Set message from code.
   */
  public function setMessageFromCode() {

  }

  /**
   * Get message.
   *
   * @return string
   *   Return the message.
   */
  public function getMessage() {
    return $this->message;
  }

  /**
   * Get code.
   *
   * @return string
   *   Return the response code.
   */
  public function getCode() {
    return $this->code;
  }

}
