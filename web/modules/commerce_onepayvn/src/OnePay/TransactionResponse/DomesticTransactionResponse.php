<?php

namespace Drupal\commerce_onepayvn\OnePay\TransactionResponse;

/**
 * Class DomesticDomesticTransactionResponse.
 *
 * @package Drupal\commerce_onepayvn\VPC\DomesticTransactionResponse
 */
class DomesticTransactionResponse extends TransactionResponseBase {

  const APPROVED_RESPONSE = "0";

  const BANK_DECLINED_RESPONSE = "1";

  const MERCHANT_NOT_EXIST_RESPONSE = "3";

  const INVALID_ACCESS_CODE_RESPONSE = "4";

  const INVALID_AMOUNT_RESPONSE = "5";

  const INVALID_CURRENCY_CODE_RESPONSE = "6";

  const UNSPECIFIED_FAILURE_RESPONSE = "7";

  const INVALID_CARD_NUMBER_RESPONSE = "8";

  const INVALID_CARD_NAME_RESPONSE = "9";

  const EXPIRED_CARD_RESPONSE = "10";

  const CARD_NOT_REGISTED_SERVICE_RESPONSE = "11";

  const INVALID_CARD_DATE_RESPONSE = "12";

  const EXIST_AMOUNT_RESPONSE = "13";

  const INSUFFICIENT_FUND_RESPONSE = "21";

  const USER_CANCEL_RESPONSE = "99";

  /**
   * Set message from code.
   */
  public function setMessageFromCode() {
    switch ($this->code) {
      case DomesticTransactionResponse::APPROVED_RESPONSE:
        $message = "Giao dịch thành công - Approved";
        break;

      case DomesticTransactionResponse::BANK_DECLINED_RESPONSE:
        $message = "Ngân hàng từ chối giao dịch - Bank Declined";
        break;

      case DomesticTransactionResponse::MERCHANT_NOT_EXIST_RESPONSE:
        $message = "Mã đơn vị không tồn tại - Merchant not exist";
        break;

      case DomesticTransactionResponse::INVALID_ACCESS_CODE_RESPONSE:
        $message = "Không đúng access code - Invalid access code";
        break;

      case DomesticTransactionResponse::INVALID_AMOUNT_RESPONSE:
        $message = "Số tiền không hợp lệ - Invalid amount";
        break;

      case DomesticTransactionResponse::INVALID_CURRENCY_CODE_RESPONSE:
        $message = "Mã tiền tệ không tồn tại - Invalid currency code";
        break;

      case DomesticTransactionResponse::UNSPECIFIED_FAILURE_RESPONSE:
        $message = "Lỗi không xác định - Unspecified Failure ";
        break;

      case DomesticTransactionResponse::INVALID_CARD_NUMBER_RESPONSE:
        $message = "Số thẻ không đúng - Invalid card Number";
        break;

      case DomesticTransactionResponse::INVALID_CARD_NAME_RESPONSE:
        $message = "Tên chủ thẻ không đúng - Invalid card name";
        break;

      case DomesticTransactionResponse::EXPIRED_CARD_RESPONSE:
        $message = "Thẻ hết hạn/Thẻ bị khóa - Expired Card";
        break;

      case DomesticTransactionResponse::CARD_NOT_REGISTED_SERVICE_RESPONSE:
        $message = "Thẻ chưa đăng ký sử dụng dịch vụ - Card Not Registed Service(internet banking)";
        break;

      case DomesticTransactionResponse::INVALID_CARD_DATE_RESPONSE:
        $message = "Ngày phát hành/Hết hạn không đúng - Invalid card date";
        break;

      case DomesticTransactionResponse::EXIST_AMOUNT_RESPONSE:
        $message = "Vượt quá hạn mức thanh toán - Exist Amount";
        break;

      case DomesticTransactionResponse::INSUFFICIENT_FUND_RESPONSE:
        $message = "Số tiền không đủ để thanh toán - Insufficient fund";
        break;

      case DomesticTransactionResponse::USER_CANCEL_RESPONSE:
        $message = "Người sủ dụng hủy giao dịch - User cancel";
        break;

      default:
        $message = "Giao dịch thất bại - Failured";
    }

    $this->message = $message;
  }

}
