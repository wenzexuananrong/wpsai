<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Entities\Payment;

class UniqidInvoiceIdProvider implements InvoiceIdProviderInterface
{
    public function provide(): string
    {
        return uniqid('inv_', true);
    }
}
