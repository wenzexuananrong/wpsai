<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\PaymentProcessor;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\PaymentGateway;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Merchant\MerchantInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\PaymentProcessor\PaymentProcessorInterface;
use WC_Order;
class MetadataSavingProcessorDecorator implements PaymentProcessorInterface
{
    /**
     * @var PaymentProcessorInterface
     */
    protected $inner;
    /**
     * @var PaymentGateway
     */
    protected $paymentGateway;
    /**
     * @var MerchantInterface
     */
    protected $merchant;
    /**
     * @var string
     */
    protected $merchantIdFieldName;
    public function __construct(PaymentProcessorInterface $inner, PaymentGateway $paymentGateway, MerchantInterface $merchant, string $merchantIdFieldName)
    {
        $this->inner = $inner;
        $this->paymentGateway = $paymentGateway;
        $this->merchant = $merchant;
        $this->merchantIdFieldName = $merchantIdFieldName;
    }
    public function processPayment(WC_Order $order) : array
    {
        $this->addMetaDataToOrder($order);
        return $this->inner->processPayment($order);
    }
    /**
     * Add meta fields to order.
     *
     * @param WC_Order $order Order to add meta fields to.
     */
    protected function addMetaDataToOrder(WC_Order $order) : void
    {
        /**
         * Store Merchant ID
         */
        $merchantId = $this->merchant->getId();
        $order->update_meta_data($this->merchantIdFieldName, (string) $merchantId);
        /**
         * Store transaction ID
         */
        $transactionUrlTemplate = $this->merchant->getTransactionUrlTemplate();
        $order->update_meta_data($this->paymentGateway->getTransactionUrlFieldName(), $transactionUrlTemplate);
        $order->save();
    }
}
