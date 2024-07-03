<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\EmbeddedPayment\PaymentProcessor;

use Inpsyde\PayoneerForWoocommerce\Checkout\Authentication\TokenGeneratorInterface;
use Inpsyde\PayoneerForWoocommerce\Checkout\MisconfigurationDetector\MisconfigurationDetectorInterface;
use Inpsyde\PayoneerForWoocommerce\Checkout\PaymentProcessor\AbstractPaymentProcessor;
use Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionManager;
use Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionPersistor;
use Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionProvider;
use Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\PaymentContext;
use Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\CommandFactory\WcOrderBasedUpdateCommandFactoryInterface;
use Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\PaymentGateway;
use Inpsyde\PayoneerForWoocommerce\PaymentGateway\PaymentProcessor\PaymentProcessorInterface;
use WC_Order;

/**
 * @psalm-import-type PaymentResult from PaymentProcessorInterface
 */
class EmbeddedPaymentProcessor extends AbstractPaymentProcessor
{
    /**
     * @var PaymentGateway
     */
    protected $paymentGateway;

    /**
     * @var string
     */
    protected $chargeIdFieldName;

    /**
     * @var string
     */
    protected $hostedModeOverrideFlag;

    public function __construct(
        PaymentGateway $paymentGateway,
        WcOrderBasedUpdateCommandFactoryInterface $updateCommandFactory,
        ListSessionProvider $sessionProvider,
        ListSessionPersistor $sessionPersistor,
        TokenGeneratorInterface $tokenGenerator,
        string $tokenKey,
        string $chargeIdFieldName,
        string $transactionIdFieldName,
        string $hostedModeOverrideFlag,
        MisconfigurationDetectorInterface $misconfigurationDetector
    ) {

        parent::__construct(
            $misconfigurationDetector,
            $sessionProvider,
            $sessionPersistor,
            $updateCommandFactory,
            $tokenGenerator,
            $tokenKey,
            $transactionIdFieldName
        );

        $this->paymentGateway = $paymentGateway;
        $this->chargeIdFieldName = $chargeIdFieldName;
        $this->hostedModeOverrideFlag = $hostedModeOverrideFlag;
    }

    public function processPayment(WC_Order $order): array
    {
        /**
         * Transfer the checkout-based LIST to the WC_Order.
         * From there, the parent AbstractPaymentProcessor can take over.
         */
        $list = $this->sessionProvider->provide(ListSessionManager::determineContextFromGlobals($order));
        $this->sessionPersistor->persist($list, new PaymentContext($order));

        $result = parent::processPayment($order);

        $result['redirect'] = add_query_arg(
            [
                $this->hostedModeOverrideFlag => true,
            ],
            $order->get_checkout_payment_url()
        );

        return $result;
    }
}
