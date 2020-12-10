<?php

namespace Drupal\commerce_onepayvn\OnePay;

use Drupal\commerce_onepayvn\Exception\MissingDataException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DomesticVPC.
 *
 * @package Drupal\commerce_onepayvn
 */
class DomesticVPC extends VirtualPaymentClientBase implements VirtualPaymentClientInterface {

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
  public function setData(array $data) {
    try {
      $this->validateData($data);
    }
    catch (MissingDataException $ex) {
      throw $ex;
    }
    parent::setData($data);
  }

  /**
   * {@inheritdoc}
   */
  public function getTransactionResponse(Request $request, $class = '') {
    return parent::getTransactionResponse($request, '\Drupal\commerce_onepayvn\OnePay\TransactionResponse\DomesticTransactionResponse');
  }

  /**
   * Verify hash string return from payment gateway.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   *
   * @return bool
   *   True|False.
   */
  public function verifySecureHash(Request $request) {
    $vpc_txn_secure_hash = $request->get('vpc_SecureHash');
    $keys = [
      'vpc_AdditionData',
      'vpc_Amount',
      'vpc_Command',
      'vpc_CurrencyCode',
      'vpc_Locale',
      'vpc_MerchTxnRef',
      'vpc_Merchant',
      'vpc_OrderInfo',
      'vpc_TransactionNo',
      'vpc_TxnResponseCode',
      'vpc_Version',
    ];
    $data = [];
    foreach ($keys as $vpc_key) {
      if (-1 != $request->get($vpc_key, -1)) {
        $data[$vpc_key] = $request->get($vpc_key);
      }
    }
    ksort($data);
    $string_hash_data = self::generateStringToHash($data);
    if (strtoupper($vpc_txn_secure_hash) === self::hashThenToUpper($string_hash_data, $this->secretKey)) {
      return TRUE;
    }

    return FALSE;
  }

}
