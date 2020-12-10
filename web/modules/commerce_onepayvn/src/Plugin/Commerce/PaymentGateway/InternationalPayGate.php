<?php

namespace Drupal\commerce_onepayvn\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_onepayvn\OnePay\TransactionResponse\InternationalTransactionResponse;
use Drupal\commerce_onepayvn\OnePay\VirtualPaymentClientInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\Exception\PaymentGatewayException;
use Drupal\commerce_payment\PaymentMethodTypeManager;
use Drupal\commerce_payment\PaymentTypeManager;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides International Pay Gate for OnePAY.
 *
 * @CommercePaymentGateway(
 *    id = "onepay_international_paygate",
 *    label = @Translation("OnePAY (International Pay Gate)"),
 *    display_label = @Translation("OnePAY (International Pay Gate)"),
 *    forms = {
 *      "offsite-payment" =
 *   "Drupal\commerce_onepayvn\PluginForm\InternationalPayGateForm",
 *    },
 *   payment_type = "onepayvn",
 *   payment_method_types = {"credit_card"},
 *   credit_card_types = {
 *     "amex", "jcb", "mastercard", "visa",
 *   },
 * )
 */
class InternationalPayGate extends DomesticPayGate {

  /**
   * InternationalPayGate constructor.
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
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $payment_type_manager, $payment_method_type_manager, $time, $messenger, $vpc);
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
      $container->get('commerce_onepayvn.international')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function onReturn(OrderInterface $order, Request $request) {
    $this->vpc->setConfiguration($this->getConfiguration());

    /** @var \Drupal\commerce_onepayvn\OnePay\TransactionResponse\InternationalTransactionResponse $tran_response */
    $tran_response = $this->vpc->getTransactionResponse($request);
    if ($tran_response->getCode() != InternationalTransactionResponse::TRANSACTION_SUCCESSFUL) {
      $this->messenger->addError($tran_response->getMessage());
      throw new PaymentGatewayException($tran_response->getMessage());
    }
    \Drupal::logger('onepay')->debug(var_export($request->query->all(), TRUE));
    \Drupal::logger('onepay')->debug(var_export($this->vpc, TRUE));

    if (!$this->vpc->verifySecureHash($request)) {
      $this->messenger
        ->addError(t('Illegal Tampering detected. Hash string verification failed.'));
      throw new PaymentGatewayException(t('Illegal Tampering detected. Hash string verification failed.'));
    }

    if ($tran_response->getCode() === InternationalTransactionResponse::TRANSACTION_SUCCESSFUL) {
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
      $this->messenger
        ->addMessage($this->t('Your payment was successful with Order id : @orderid and Transaction id : @transaction_id has been received at : @date', [
          '@orderid' => $order->id(),
          '@transaction_id' => $remote_id,
          '@date' => date("d-m-Y H:i:s", $request_time),
        ]));
    }
  }

}
