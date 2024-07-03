<?php

namespace Inpsyde\PayoneerSdk\Api\Entities\Payment;

/**
 * A service
 */
interface InvoiceIdProviderInterface
{
    public function provide(): string;
}
