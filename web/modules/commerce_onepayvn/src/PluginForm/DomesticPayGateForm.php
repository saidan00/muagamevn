<?php

namespace Drupal\commerce_onepayvn\PluginForm;

use Drupal\commerce_onepayvn\Exception\InvalidCurrencyException;
use Drupal\commerce_onepayvn\OnePay\VirtualPaymentClientInterface;
use Drupal\commerce_payment\PluginForm\PaymentOffsiteForm;
use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provide payment form ofr OnePay.vn local card.
 *
 * @package Drupal\commerce_onepayvn\PluginForm
 */
class DomesticPayGateForm extends PaymentOffsiteForm implements ContainerInjectionInterface {

  use PayGateFormTrail;

  /**
   * DomesticPayGateForm constructor.
   *
   * @param \Drupal\Component\Transliteration\TransliterationInterface $transliteration
   *   Transliteration.
   * @param \Drupal\commerce_onepayvn\OnePay\VirtualPaymentClientInterface $virtualPaymentClient
   *   VirtualPaymentClientInterface.
   */
  public function __construct(TransliterationInterface $transliteration,
                              VirtualPaymentClientInterface $virtualPaymentClient) {
    $this->transliteration = $transliteration;
    $this->vpc = $virtualPaymentClient;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('transliteration'),
      $container->get('commerce_onepayvn.domestic')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    /** @var \Drupal\commerce_payment\Entity\PaymentInterface $payment */
    $payment = $this->entity;

    /** @var \Drupal\commerce_onepayvn\OnePay\DomesticVPC $domestic */
    $domestic = $this->vpc;
    $domestic->setPaymentGateway($payment);
    if (!in_array($payment->getAmount()
      ->getCurrencyCode(), $domestic->getAllowedCurrencies())) {
      throw new InvalidCurrencyException();
    }
    $order = $payment->getOrder();
    $return_url = Url::FromRoute('commerce_payment.checkout.return', [
      'commerce_order' => $order->id(),
      'step' => 'payment',
    ], ['absolute' => TRUE])->toString();
    $txn_ref = $order->id() . '_' . date('YmdHis');
    $data = [
      'merchant_id' => $domestic->getConfiguration()['merchant_id'],
      'access_code' => $domestic->getConfiguration()['merchant_key'],
      'transaction_reference' => $txn_ref,
      'order_info' => $order->id(),
      'amount' => $payment->getAmount()->getNumber(),
      'return_url' => $return_url,
    ];
    $domestic->setData($data);

    $addition_info = [];
    $addition_info['customer_email'] = $order->getEmail();

    $shipping_profile = $this->getShippingProfile($order);
    /** @var \Drupal\address\AddressInterface|null $shipping_address */
    $shipping_address = $shipping_profile && $shipping_profile->hasField('address') && !$shipping_profile->get('address')
      ->isEmpty() ? $shipping_profile->address->first() : NULL;
    if ($shipping_address) {
      $addition_info['ship_street'] = $shipping_address->getAddressLine1();
      $addition_info['ship_city'] = $shipping_address->getLocality();
      $addition_info['ship_country'] = $shipping_address->getCountryCode();
    }
    $this->transliterateData($addition_info);
    $domestic->setAdditionInformation($addition_info);

    $form = $this->buildRedirectForm($form, $form_state, $domestic->generateRedirectUrl(), [], 'post');

    $redirect_method = $domestic->getConfiguration()['redirect_method'];
    $remove_js = ($redirect_method == 'post_manual');
    if ($remove_js) {
      // Disable the javascript that auto-clicks the Submit button.
      unset($form['#attached']['library']);
    }
    return $form;
  }

}
