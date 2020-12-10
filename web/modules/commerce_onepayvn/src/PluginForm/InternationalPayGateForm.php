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
 * Provide international pay gate form for OnePay.vn local card.
 *
 * @package Drupal\commerce_onepayvn\PluginForm
 */
class InternationalPayGateForm extends PaymentOffsiteForm implements ContainerInjectionInterface {

  use PayGateFormTrail;

  /**
   * InternationalPayGateForm constructor.
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
      $container->get('commerce_onepayvn.international')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    /** @var \Drupal\commerce_payment\Entity\PaymentInterface $payment */
    $payment = $this->entity;

    /** @var \Drupal\commerce_onepayvn\OnePay\InternationalVPC $international_pay_gate */
    $international_pay_gate = $this->vpc;
    $international_pay_gate->setPaymentGateway($payment);
    if (!in_array($payment->getAmount()
      ->getCurrencyCode(), $international_pay_gate->getAllowedCurrencies())) {
      throw new InvalidCurrencyException();
    }
    $order = $payment->getOrder();
    $return_url = Url::FromRoute('commerce_payment.checkout.return', [
      'commerce_order' => $order->id(),
      'step' => 'payment',
    ], ['absolute' => TRUE])->toString();
    $txn_ref = $order->id() . '_' . date('YmdHis');
    $data = [
      'merchant_id' => $international_pay_gate->getConfiguration()['merchant_id'],
      'access_code' => $international_pay_gate->getConfiguration()['merchant_key'],
      'transaction_reference' => $txn_ref,
      'order_info' => $order->id(),
      'amount' => $payment->getAmount()->getNumber(),
      'return_url' => $return_url,
    ];
    $international_pay_gate->setData($data);

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
    $international_pay_gate->setAdditionInformation($addition_info);

    /** @var \Drupal\address\AddressInterface $address */
    $address = $order->getBillingProfile()->address->first();
    $billing_data = [];
    if ($address) {
      $billing_data['billing_street'] = $address->getAddressLine1();
      $billing_data['billing_city'] = $address->getLocality();
      $billing_data['billing_state_province'] = $address->getAdministrativeArea();
      $billing_data['billing_postal_code'] = $address->getPostalCode();
      $billing_data['billing_country'] = $address->getCountryCode();
    }
    $this->transliterateData($billing_data);
    $international_pay_gate->setBillingAddress($billing_data);

    $form = $this->buildRedirectForm($form, $form_state, $international_pay_gate->generateRedirectUrl(), [], 'post');

    $redirect_method = $international_pay_gate->getConfiguration()['redirect_method'];
    $remove_js = ($redirect_method == 'post_manual');
    if ($remove_js) {
      // Disable the javascript that auto-clicks the Submit button.
      unset($form['#attached']['library']);
    }
    return $form;
  }

}
