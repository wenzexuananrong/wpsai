<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\HostedPayment\PaymentProcessor;

use Automattic\WooCommerce\Blocks\Payments\PaymentResult;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\Authentication\TokenGeneratorInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\CheckoutExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\MisconfigurationDetector\MisconfigurationDetectorInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\PaymentProcessor\AbstractPaymentProcessor;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\ListSession\OrderBasedListCommandFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionPersistor;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionProvider;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\PaymentContext;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\CommandFactory\WcOrderBasedUpdateCommandFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\PaymentProcessor\PaymentProcessorInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use WC_Order;
/**
 * @psalm-import-type PaymentResult from PaymentProcessorInterface
 */
class HostedPaymentProcessor extends AbstractPaymentProcessor
{
    /**
     * @var OrderBasedListCommandFactoryInterface
     */
    protected $listCommandFactory;
    /**
     * @var ListSessionPersistor
     */
    protected $listSessionPersistor;
    /**
     * @var bool
     */
    protected $fallbackToHostedModeFlag;
    public function __construct(OrderBasedListCommandFactoryInterface $listCommandFactory, ListSessionPersistor $listSessionPersistor, string $transactionIdFieldName, MisconfigurationDetectorInterface $misconfigurationDetector, ListSessionProvider $sessionProvider, WcOrderBasedUpdateCommandFactoryInterface $updateCommandFactory, TokenGeneratorInterface $tokenGenerator, string $tokenKey, bool $fallbackToHostedModeFlag)
    {
        parent::__construct($misconfigurationDetector, $sessionProvider, $listSessionPersistor, $updateCommandFactory, $tokenGenerator, $tokenKey, $transactionIdFieldName);
        $this->listCommandFactory = $listCommandFactory;
        $this->listSessionPersistor = $listSessionPersistor;
        $this->fallbackToHostedModeFlag = $fallbackToHostedModeFlag;
    }
    /**
     * @inheritDoc
     */
    public function processPayment(WC_Order $order) : array
    {
        $this->clearOutdatedListInOrder($order);
        parent::processPayment($order);
        $list = $this->sessionProvider->provide(new PaymentContext($order));
        $redirectUrl = $this->createRedirectUrl($list);
        $note = __('The customer is being redirected to an external payment page.', 'payoneer-checkout');
        $order->update_status('on-hold', $note);
        return ['result' => 'success', 'redirect' => $redirectUrl];
    }
    /**
     * If fallback to HPP flag is set, we need to clear saved LIST. It may be created for embedded
     * flow, so we cannot use it.
     *
     * @param WC_Order $order
     *
     * @throws CheckoutExceptionInterface
     */
    protected function clearOutdatedListInOrder(WC_Order $order) : void
    {
        if ($this->fallbackToHostedModeFlag) {
            $this->listSessionPersistor->persist(null, new PaymentContext($order));
        }
    }
    /**
     * If the LIST response contains a redirect object, craft a compatible URL
     * out of the given URL and its parameters. If none is found, use our own return URL
     * as a fallback
     *
     * @param ListInterface $list
     *
     * @return string
     * @throws ApiExceptionInterface
     */
    protected function createRedirectUrl(ListInterface $list) : string
    {
        $redirect = $list->getRedirect();
        $baseUrl = $redirect->getUrl();
        $parameters = $redirect->getParameters();
        $parameterDict = [];
        array_walk($parameters, static function (array $param) use(&$parameterDict) {
            /** @psalm-suppress MixedArrayAssignment * */
            $parameterDict[(string) $param['name']] = urlencode((string) $param['value']);
        });
        return add_query_arg($parameterDict, $baseUrl);
    }
    /**
     * @param \Throwable|\WP_Error|string|null $error
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
     *
     * @return string[]
     * @psalm-return PaymentResult
     */
    protected function handleError($error = null) : array
    {
        $fallback = __('An error occurred during payment processing', 'payoneer-checkout');
        switch (\true) {
            case $error instanceof \Throwable:
                $error = $this->produceErrorMessageFromException($error, $fallback);
                break;
            case $error instanceof \WP_Error:
                $error = $error->get_error_message();
                break;
            case is_string($error):
                break;
            default:
                $error = $fallback;
        }
        wc_add_notice($error, 'error');
        return ['result' => 'failure', 'redirect' => ''];
    }
    /**
     * Store the LIST's generated trx-ID on the order itself.
     * This is required so we can match the order during webhook processing
     *
     * @throws \WC_Data_Exception
     */
    protected function storeTransactionId(WC_Order $order, ListInterface $list) : void
    {
        $identification = $list->getIdentification();
        $order->update_meta_data($this->transactionIdFieldName, $identification->getTransactionId());
        $order->set_transaction_id($identification->getLongId());
        $order->save();
    }
}
