<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\EmbeddedPayment;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\CheckoutContext;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionProvider;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Exception\PaymentGatewayException;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\PaymentGateway;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\PaymentRequestValidatorInterface;
class ListUrlPaymentRequestValidator implements PaymentRequestValidatorInterface
{
    /**
     * @var string
     */
    protected $inputName;
    /**
     * @var ?PaymentRequestValidatorInterface
     */
    protected $validator = null;
    /**
     * @var ListSessionProvider
     */
    protected $listSessionProvider;
    public function __construct(string $inputName, ListSessionProvider $listSessionProvider, PaymentRequestValidatorInterface $validator = null)
    {
        $this->inputName = $inputName;
        $this->listSessionProvider = $listSessionProvider;
        $this->validator = $validator;
    }
    public function assertIsValid(\WC_Order $wcOrder, PaymentGateway $gateway) : void
    {
        $postedListUrl = filter_input(\INPUT_POST, $this->inputName, \FILTER_SANITIZE_URL);
        $currentListUrl = $this->listSessionProvider->provide(new CheckoutContext())->getLinks()['self'];
        if ($postedListUrl !== $currentListUrl) {
            throw new PaymentGatewayException(__('List URL mismatch. This could mean your payment session has expired. Please try again', 'payoneer-checkout'));
        }
        $this->validator && $this->validator->assertIsValid($wcOrder, $gateway);
    }
}
