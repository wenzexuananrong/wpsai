<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\ListSession\ListSession;

class PaymentContext extends AbstractContext
{
    /**
     * @var \WC_Order
     */
    private $order;

    public function __construct(
        \WC_Order $order
    ) {

        $this->order = $order;
    }

    public function getOrder(): \WC_Order
    {
        return $this->order;
    }
}
