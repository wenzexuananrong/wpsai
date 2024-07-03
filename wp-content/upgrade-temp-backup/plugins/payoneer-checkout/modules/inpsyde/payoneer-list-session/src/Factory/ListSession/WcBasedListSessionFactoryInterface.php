<?php

namespace Inpsyde\PayoneerForWoocommerce\ListSession\Factory\ListSession;

use Inpsyde\PayoneerForWoocommerce\ListSession\Factory\FactoryExceptionInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use Inpsyde\PayoneerSdk\Api\PayoneerIntegrationTypes;
use WC_Cart;
use WC_Customer;

interface WcBasedListSessionFactoryInterface
{
    /**
     * @psalm-param PayoneerIntegrationTypes::* $integrationType,
     *
     * @throws FactoryExceptionInterface
     */
    public function createList(
        WC_Customer $customer,
        WC_Cart $cart,
        string $integrationType,
        string $hostedVersion = null
    ): ListInterface;
}
