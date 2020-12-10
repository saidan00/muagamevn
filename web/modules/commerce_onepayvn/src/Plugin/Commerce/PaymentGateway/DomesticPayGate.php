<?php

namespace Drupal\commerce_onepayvn\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_onepayvn\OnePay\TransactionResponse\DomesticTransactionResponse;
use Drupal\commerce_onepayvn\OnePay\VirtualPaymentClientInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\Exception\PaymentGatewayException;
use Drupal\commerce_payment\PaymentMethodTypeManager;
use Drupal\commerce_payment\PaymentTypeManager;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayBase;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides Domestic Pay Gate  for OnePAY.
 *
 * @CommercePaymentGateway(
 *    id = "onepay_domestic_paygate",
 *    label = @Translation("OnePAY (Domestic Pay Gate)"),
 *    display_label = @Translation("OnePAY (Domestic Pay Gate)"),
 *    forms = {
 *      "offsite-payment" =
 *   "Drupal\commerce_onepayvn\PluginForm\DomesticPayGateForm",
 *    },
 *    payment_type = "onepayvn",
 * )
 */
class DomesticPayGate extends OffsitePaymentGatewayBase {

  protected $vpc;

  /**
   * DomesticPayGate constructor.
   *
   * @param array $configuration
   *   Plugin configuration.
   * @param string $plugin_id
   *   Plugin Id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\commerce_payment\PaymentTypeManager $payment_type_manager
   *   PaymentTypeManager.
   * @param \Drupal\commerce_payment\PaymentMethodTypeManager $payment_method_type_manager
   *   PaymentMethodTypeManager.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   TimeInterface.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   MessengerInterface.
   * @param \Drupal\commerce_onepayvn\OnePay\VirtualPaymentClientInterface $vpc
   *   VirtualPaymentClientInterface.
   */
  public function __construct(array $configuration,
                                 $plugin_id,
                                 $plugin_definition,
                                 EntityTypeManagerInterface $entity_type_manager,
                                 PaymentTypeManager $payment_type_manager,
                                 PaymentMethodTypeManager $payment_method_type_manager,
                                 TimeInterface $time,
                                 MessengerInterface $messenger,
                                 VirtualPaymentClientInterface $vpc) {
    $this->messenger = $messenger;
    $this->vpc = $vpc;
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $payment_type_manager, $payment_method_type_manager, $time);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.commerce_payment_type'),
      $container->get('plugin.manager.commerce_payment_method_type'),
      $container->get('datetime.time'),
      $container->get('messenger'),
      $container->get('commerce_onepayvn.domestic')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $configuration = [
      'service_url' => '',
      'merchant_id' => '',
      'merchant_key' => '',
      'secret_key' => '',
      'redirect_method' => 'post',
    ];
    return $configuration + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['merchant_id'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Merchant Id'),
      '#default_value' => $this->configuration['merchant_id'] ? $this->configuration['merchant_id'] : '',
    ];
    $form['merchant_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Access Code'),
      '#default_value' => $this->configuration['merchant_key'] ? $this->configuration['merchant_key'] : '',
      '#required' => TRUE,
    ];
    $form['secret_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Hashcode'),
      '#default_value' => $this->configuration['secret_key'] ? $this->configuration['secret_key'] : '',
      '#required' => TRUE,
    ];
    $form['service_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Virtual Payment Client URL'),
      '#default_value' => $this->configuration['service_url'] ? $this->configuration['service_url'] : '',
      '#required' => TRUE,
      '#description' => t('Change to Dev (Test) / Production (Live) VPC URL as per mode selected above.'),
    ];
    $form['redirect_method'] = [
      '#type' => 'radios',
      '#title' => $this->t('Redirect method'),
      '#options' => [
        'post' => $this->t('Redirect via POST (automatic)'),
        'post_manual' => $this->t('Redirect via POST (manual)'),
      ],
      '#default_value' => $this->configuration['redirect_method'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
      $this->configuration['service_url'] = $values['service_url'];
      $this->configuration['merchant_id'] = $values['merchant_id'];
      $this->configuration['merchant_key'] = $values['merchant_key'];
      $this->configuration['secret_key'] = $values['secret_key'];
      $this->configuration['redirect_method'] = $values['redirect_method'];

    }
  }

  /**
   * {@inheritdoc}
   */
  public function onReturn(OrderInterface $order, Request $request) {
    $this->vpc->setConfiguration($this->getConfiguration());

    /** @var \Drupal\commerce_onepayvn\OnePay\TransactionResponse\DomesticTransactionResponse $tran_response */
    $tran_response = $this->vpc->getTransactionResponse($request);
    if ($tran_response->getCode() != DomesticTransactionResponse::APPROVED_RESPONSE) {
      $this->messenger->addError($tran_response->getMessage());
      throw new PaymentGatewayException($tran_response->getMessage());
    }

    if (!$this->vpc->verifySecureHash($request)) {
      $this->messenger->addError(t('Illegal Tampering detected. Hash string verification failed.'));
      throw new PaymentGatewayException(t('Illegal Tampering detected. Hash string verification failed.'));
    }

    if ($tran_response->getCode() === DomesticTransactionResponse::APPROVED_RESPONSE) {
      $remote_id = $this->vpc->getRemoteId($request);
      $payment_storage = $this->entityTypeManager->getStorage('commerce_payment');
      $request_time = $this->time->getRequestTime();
      $payment = $payment_storage->create([
        'state' => 'authorization',
        'amount' => $order->getTotalPrice(),
        'payment_gateway' => $this->entityId,
        'order_id' => $order->id(),
        'test' => $this->getMode() == 'test',
        'remote_id' => $remote_id,
        'remote_state' => $tran_response->getMessage(),
        'authorized' => $request_time,
      ]);
      $payment->save();
      $this->messenger->addMessage($this->t('Your payment was successful with Order id : @orderid and Transaction id : @transaction_id has been received at : @date', [
        '@orderid' => $order->id(),
        '@transaction_id' => $remote_id,
        '@date' => date("d-m-Y H:i:s", $request_time),
      ]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onCancel(OrderInterface $order, Request $request) {
    $this->messenger->addError($this->t('You have canceled checkout at @gateway but may resume the checkout process here when you are ready.', [
      '@gateway' => $this->getDisplayLabel(),
    ]), 'error');
  }

}
