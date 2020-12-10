<?php

namespace Drupal\commerce_onepayvn\OnePay;

use Drupal\commerce_onepayvn\Exception\MissingDataException;
use Drupal\commerce_payment\Entity\PaymentInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class VirtualPaymentClientBase.
 *
 * @package Drupal\commerce_onepayvn
 */
abstract class VirtualPaymentClientBase {

  protected $data;

  protected $vpcUrl;

  protected $secretKey;

  protected $allowedCurrencies = ['VND'];

  protected $configuration;

  protected $paymentGateway;

  /**
   * Set payment gateway, payment object, configuration for plugin.
   *
   * @param \Drupal\commerce_payment\Entity\PaymentInterface $payment
   *   PaymentInterface.
   */
  public function setPaymentGateway(PaymentInterface $payment) {
    /** @var \Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayInterface $payment_gateway_plugin */
    $payment_gateway_plugin = $payment->getPaymentGateway()->getPlugin();
    $this->paymentGateway = $payment_gateway_plugin;
    $this->setConfiguration($payment_gateway_plugin->getConfiguration());
  }

  /**
   * Set configuration.
   *
   * @param array $configuration
   *   Configuration array.
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
    $this->secretKey = $configuration['secret_key'];
    $this->vpcUrl = $configuration['service_url'];
  }

  /**
   * Return configuration.
   *
   * @return mixed
   *   Return configuration.
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * Get transaction response code and message from request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   * @param string $class
   *   Transaction response class to return.
   *
   * @return \Drupal\commerce_onepayvn\OnePay\TransactionResponse\TransactionResponseBase
   *   Code and message.
   */
  public function getTransactionResponse(Request $request, $class = '\Drupal\commerce_onepayvn\OnePay\TransactionResponse\TransactionResponseBase') {
    $code = $request->get('vpc_TxnResponseCode');
    return new $class($code);
  }

  /**
   * Get remote id.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   *
   * @return string
   *   Remote id.
   */
  public function getRemoteId(Request $request) {
    return $request->get('vpc_TransactionNo');
  }

  /**
   * Generate query string for redirect to gateway.
   *
   * @return string
   *   Query string.
   *
   * @throws \Drupal\commerce_onepayvn\Exception\MissingDataException
   */
  public function generateRedirectUrl() {
    if (strlen($this->secretKey) <= 0) {
      throw new MissingDataException('secretKey');
    }
    $url_query_string = '';
    $data = $this->data;
    ksort($data);
    foreach ($data as $key => $value) {
      if (strlen($value) > 0) {
        $url_query_string .= (!empty($url_query_string) ? '&' : '') . urlencode($key) . '=' . urlencode($value);
      }
    }
    $string_to_hash = self::generateStringToHash($data);
    $url_query_string .= '&vpc_SecureHash=' . self::hashThenToUpper($string_to_hash, $this->secretKey);
    return $this->vpcUrl . '?' . $url_query_string;
  }

  /**
   * Generate a query string from array data.
   *
   * @param array $data
   *   Data to generate.
   *
   * @return string
   *   The generated string.
   */
  public static function generateStringToHash(array $data) {
    $string_to_hash = '';
    foreach ($data as $key => $value) {
      if ((strlen($value) > 0) && ((substr($key, 0, 4) == "vpc_") || (substr($key, 0, 5) == "user_"))) {
        $string_to_hash .= (!empty($string_to_hash) ? '&' : '') . $key . "=" . $value;
      }
    }
    return $string_to_hash;
  }

  /**
   * Hash a string then convert to uppercase.
   *
   * @param string $string_to_hash
   *   This string need to hash.
   * @param string $key
   *   Secret key.
   *
   * @return string
   *   Hash string will be return.
   */
  public static function hashThenToUpper($string_to_hash, $key) {
    return strtoupper(hash_hmac('SHA256', $string_to_hash, pack('H*', $key)));
  }

  /**
   * Set data for gateway.
   *
   * This function set the data to generate hash string for
   * payment gateway, please see the link below to get more info.
   *
   * @param array $data
   *   Data to set for payment gateway.
   *
   * @see https://mtf.onepay.vn/developer/?page=modul_noidia_php
   */
  public function setData(array $data) {
    $this->data['vpc_Merchant'] = $data['merchant_id'];
    $this->data['vpc_AccessCode'] = $data['access_code'];
    // Version (fixed).
    $this->data['vpc_Version'] = '2';
    // Command Type(fixed).
    $this->data['vpc_Command'] = 'pay';
    // @todo: use current language of website
    // //Language use on gateway (vn/en).
    $this->data['vpc_Locale'] = 'vn';
    // Currency (VND).
    $this->data['vpc_Currency'] = 'VND';
    $this->data['Title'] = 'VPC 3-Party';
    // ID Transaction - (unique per transaction) - (max 40 char).
    $this->data['vpc_MerchTxnRef'] = $data['transaction_reference'];
    // Order Name will show on payment gateway (max 34 char).
    $this->data['vpc_OrderInfo'] = $data['order_info'];
    // Amount,Multiplied with 100, Ex: 100=1VND.
    $this->data['vpc_Amount'] = (integer) $data['amount'] * 100;
    // URL for receiving payment result from gateway.
    $this->data['vpc_ReturnURL'] = $data['return_url'];
  }

  /**
   * Validate input data.
   *
   * @param array $data
   *   Data to set for payment gateway.
   *
   * @return bool
   *   Return TRUE if valid.
   *
   * @throws \Drupal\commerce_onepayvn\Exception\MissingDataException
   */
  public function validateData(array $data) {
    if (empty($data['merchant_id'])) {
      throw new MissingDataException('merchant_id');
    }
    if (empty($data['access_code'])) {
      throw new MissingDataException('access_code');
    }
    if (empty($data['transaction_reference'])) {
      throw new MissingDataException('transaction_reference');
    }
    if (empty($data['order_info'])) {
      throw new MissingDataException('order_info');
    }
    if (empty($data['amount'])) {
      throw new MissingDataException('amount');
    }
    if (empty($data['return_url'])) {
      throw new MissingDataException('return_url');
    }
    return TRUE;
  }

  /**
   * Set addition data for payment gateway.
   *
   * @param array $data
   *   Addition data.
   */
  public function setAdditionInformation(array $data) {
    if (!empty($data['ticket_no'])) {
      $this->data['vpc_TicketNo'] = $data['ticket_no'];
    }
    if (!empty($data['ship_street'])) {
      $this->data['vpc_SHIP_Street01'] = $data['ship_street'];
    }
    if (!empty($data['ship_province'])) {
      // Bad word from API.
      $this->data['vpc_SHIP_Provice'] = $data['ship_province'];
    }
    if (!empty($data['ship_city'])) {
      $this->data['vpc_SHIP_City'] = $data['ship_city'];
    }
    if (!empty($data['ship_country'])) {
      $this->data['vpc_SHIP_Country'] = $data['ship_country'];
    }
    if (!empty($data['customer_phone'])) {
      $this->data['vpc_Customer_Phone'] = $data['customer_phone'];
    }
    if (!empty($data['customer_email'])) {
      $this->data['vpc_Customer_Email'] = $data['customer_email'];
    }
    if (!empty($data['customer_id'])) {
      $this->data['vpc_Customer_Id'] = $data['customer_id'];
    }
  }

  /**
   * Return the list of currency code allow to pay.
   *
   * @return array
   *   Currencies code.
   */
  public function getAllowedCurrencies() {
    return $this->allowedCurrencies;
  }

}
