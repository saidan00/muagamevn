<?php

namespace Drupal\commerce_onepayvn\OnePay;

use Drupal\commerce_onepayvn\Exception\MissingDataException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class InternationalVPC.
 *
 * @package Drupal\commerce_onepayvn
 */
class InternationalVPC extends VirtualPaymentClientBase implements VirtualPaymentClientInterface {

  /**
   * {@inheritdoc}
   */
  public function setData(array $data) {
    try {
      $this->validateData($data);
    }
    catch (MissingDataException $ex) {
      throw $ex;
    }
    parent::setData($data);
    $this->data['AgainLink'] = urlencode($_SERVER['HTTP_REFERER']);
  }

  /**
   * {@inheritdoc}
   */
  public function verifySecureHash(Request $request) {
    $vpc_txn_secure_hash = $request->get('vpc_SecureHash');
    $keys = [
      'vpc_OrderInfo',
      'vpc_3DSECI',
      'vpc_AVS_Street01',
      'vpc_Merchant',
      'vpc_Card',
      'vpc_AcqResponseCode',
      'AgainLink',
      'vpc_AVS_Country',
      'vpc_AuthorizeId',
      'vpc_3DSenrolled',
      'vpc_RiskOverallResult',
      'vpc_ReceiptNo',
      'vpc_TransactionNo',
      'vpc_AVS_StateProv',
      'vpc_Locale',
      'vpc_TxnResponseCode',
      'vpc_VerToken',
      'vpc_Amount',
      'vpc_BatchNo',
      'vpc_Version',
      'vpc_AVSResultCode',
      'vpc_VerStatus',
      'vpc_Command',
      'vpc_Message',
      'Title',
      'vpc_3DSstatus',
      'vpc_CardNum',
      'vpc_AVS_PostCode',
      'vpc_CSCResultCode',
      'vpc_MerchTxnRef',
      'vpc_VerType',
      'vpc_VerSecurityLevel',
      'vpc_3DSXID',
      'vpc_AVS_City',
      'vpc_CommercialCardIndicator',
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

  /**
   * {@inheritdoc}
   */
  public function getTransactionResponse(Request $request, $class = '') {
    return parent::getTransactionResponse($request, '\Drupal\commerce_onepayvn\OnePay\TransactionResponse\InternationalTransactionResponse');
  }

  /**
   * Set billing address for international pay gate.
   *
   * @param array $data
   *   Data to set.
   */
  public function setBillingAddress(array $data) {
    if (!empty($data['billing_street'])) {
      $this->data['AVS_Street01'] = $data['billing_street'];
    }
    if (!empty($data['billing_city'])) {
      $this->data['AVS_City'] = $data['billing_city'];
    }
    if (!empty($data['billing_state_province'])) {
      $this->data['AVS_StateProv'] = $data['billing_state_province'];
    }
    if (!empty($data['billing_postcode'])) {
      $this->data['AVS_PostCode'] = $data['billing_postcode'];
    }
    if (!empty($data['billing_country'])) {
      $this->data['AVS_Country'] = $data['billing_country'];
    }
    if (!empty($data['display'])) {
      $this->data['display'] = $data['display'];
    }

  }

}
