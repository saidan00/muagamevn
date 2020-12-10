<?php

namespace Drupal\commerce_onepayvn\OnePay;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface VirtualPaymentClientInterface.
 *
 * @package Drupal\commerce_onepayvn
 */
interface VirtualPaymentClientInterface {

  /**
   * Generate query string for redirect to gateway.
   *
   * @return string
   *   Query string.
   */
  public function generateRedirectUrl();

  /**
   * Set data for gateway.
   *
   * This function set the data to generate hash string for
   * payment gateway, please see the link below to get more info.
   *
   * @param array $data
   *   Data to set for payment gateway.
   *
   * @throws \Drupal\commerce_onepayvn\Exception\MissingDataException
   *
   * @see https://mtf.onepay.vn/developer/?page=modul_noidia_php
   */
  public function setData(array $data);

  /**
   * Verify hash string return from payment gateway.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   *
   * @return bool
   *   True|False.
   */
  public function verifySecureHash(Request $request);

  /**
   * Set configuration of payment gateway.
   *
   * @param array $configuration
   *   Array of configuration to set.
   */
  public function setConfiguration(array $configuration);

  /**
   * Get transaction response object from request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request redirect from gateway.
   * @param string $class
   *   Class of response.
   *
   * @return \Drupal\commerce_onepayvn\OnePay\TransactionResponse\TransactionResponseBase
   *   Code and message.
   */
  public function getTransactionResponse(Request $request, $class = '');

  /**
   * Get remote id from request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   *
   * @return string
   *   Remote id.
   */
  public function getRemoteId(Request $request);

}
