<?php

namespace Drupal\commerce_onepayvn\OnePay\TransactionResponse;

/**
 * Class InternationalTransactionResponse.
 *
 * @package Drupal\commerce_onepayvn\VPC\InternationalTransactionResponse
 */
class InternationalTransactionResponse extends TransactionResponseBase {

  const TRANSACTION_SUCCESSFUL = '0';

  const STATUS_IS_UNKNOWN = '?';

  const BANK_REJECT = '1';

  const BANK_DECLINED = '2';

  const BANK_NO_REPLY = '3';

  const EXPIRED_CARD = '4';

  const INSUFFICIENT_FUNDS = '5';

  const BANK_ERROR_COMMUNICATING = '6';

  const PAYMENT_SERVER_ERROR = '7';

  const TYPE_NOT_SUPPORTED = '8';

  const DO_NOT_CONTACT_BANK = '9';

  const TRANSACTION_ABORTED = 'A';

  const TRANSACTION_CANCELLED = 'C';

  const DEFERRED_TRANSACTION = 'D';

  const SECURE_AUTHENTICATION_FAILED = 'F';

  const CARD_SECURITY_CODE_VERIFICATION_FAILED = 'I';

  const SHOPPING_TRANSACTION_LOCKED = 'L';

  const CARDHOLDER_IS_NOT_ENROLLED = 'N';

  const TRANSACTION_HAS_BEEN_RECEIVED = 'P';

  const TRANSACTION_WAS_NOT_PROCESSED = 'R';

  const DUPLICATE_SESSION_ID_ORDER_INFO = 'S';

  const ADDRESS_VERIFICATION_FAILED = 'T';

  const CARD_SECURITY_CODE_FAILED = 'U';

  const ADDRESS_AND_CARD_SECURITY_CODE_FAILED = 'V';

  const USER_CANCEL = '99';

  /**
   * Set message from code.
   */
  public function setMessageFromCode() {
    switch ($this->code) {
      case InternationalTransactionResponse::TRANSACTION_SUCCESSFUL:
        $message = "Transaction Successful";
        break;

      case InternationalTransactionResponse::STATUS_IS_UNKNOWN:
        $message = "Transaction status is unknown";
        break;

      case InternationalTransactionResponse::BANK_REJECT:
        $message = "Bank system reject";
        break;

      case InternationalTransactionResponse::BANK_DECLINED:
        $message = "Bank Declined Transaction";
        break;

      case InternationalTransactionResponse::BANK_NO_REPLY:
        $message = "No Reply from Bank";
        break;

      case InternationalTransactionResponse::EXPIRED_CARD:
        $message = "Expired Card";
        break;

      case InternationalTransactionResponse::INSUFFICIENT_FUNDS:
        $message = "Insufficient funds";
        break;

      case InternationalTransactionResponse::BANK_ERROR_COMMUNICATING:
        $message = "Error Communicating with Bank";
        break;

      case InternationalTransactionResponse::PAYMENT_SERVER_ERROR:
        $message = "Payment Server System Error";
        break;

      case InternationalTransactionResponse::TYPE_NOT_SUPPORTED:
        $message = "Transaction Type Not Supported";
        break;

      case InternationalTransactionResponse::DO_NOT_CONTACT_BANK:
        $message = "Bank declined transaction (Do not contact Bank)";
        break;

      case InternationalTransactionResponse::TRANSACTION_ABORTED:
        $message = "Transaction Aborted";
        break;

      case InternationalTransactionResponse::TRANSACTION_CANCELLED:
        $message = "Transaction Cancelled";
        break;

      case InternationalTransactionResponse::DEFERRED_TRANSACTION:
        $message = "Deferred transaction has been received and is awaiting processing";
        break;

      case InternationalTransactionResponse::SECURE_AUTHENTICATION_FAILED:
        $message = "3D Secure Authentication failed";
        break;

      case InternationalTransactionResponse::CARD_SECURITY_CODE_VERIFICATION_FAILED:
        $message = "Card Security Code verification failed";
        break;

      case InternationalTransactionResponse::SHOPPING_TRANSACTION_LOCKED:
        $message = "Shopping Transaction Locked (Please try the transaction again later)";
        break;

      case InternationalTransactionResponse::CARDHOLDER_IS_NOT_ENROLLED:
        $message = "Cardholder is not enrolled in Authentication scheme";
        break;

      case InternationalTransactionResponse::TRANSACTION_HAS_BEEN_RECEIVED:
        $message = "Transaction has been received by the Payment Adaptor and is being processed";
        break;

      case InternationalTransactionResponse::TRANSACTION_WAS_NOT_PROCESSED:
        $message = "Transaction was not processed - Reached limit of retry attempts allowed";
        break;

      case InternationalTransactionResponse::DUPLICATE_SESSION_ID_ORDER_INFO:
        $message = "Duplicate SessionID (OrderInfo)";
        break;

      case InternationalTransactionResponse::ADDRESS_VERIFICATION_FAILED:
        $message = "Address Verification Failed";
        break;

      case InternationalTransactionResponse::CARD_SECURITY_CODE_FAILED:
        $message = "Card Security Code Failed";
        break;

      case InternationalTransactionResponse::ADDRESS_AND_CARD_SECURITY_CODE_FAILED:
        $message = "Address Verification and Card Security Code Failed";
        break;

      case InternationalTransactionResponse::USER_CANCEL:
        $message = "User Cancel";
        break;

      default:
        $message = "Unable to be determined";
    }

    $this->message = $message;
  }

}
