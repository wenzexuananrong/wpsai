<?php

namespace Inpsyde\PayoneerForWoocommerce\ListSession\Factory\ListSession;

use Inpsyde\PayoneerForWoocommerce\ListSession\Factory\FactoryExceptionInterface;
use Inpsyde\PayoneerSdk\Api\Command\UpdateListCommandInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Identification\IdentificationInterface;
use WC_Cart;
use WC_Customer;

interface WcBasedUpdateCommandFactoryInterface
{
    /**
     * @throws FactoryExceptionInterface
     */
    public function createUpdateCommand(
        IdentificationInterface $listSessionIdentification,
        WC_Customer $wcCustomer,
        WC_Cart $cart
    ): UpdateListCommandInterface;
}
