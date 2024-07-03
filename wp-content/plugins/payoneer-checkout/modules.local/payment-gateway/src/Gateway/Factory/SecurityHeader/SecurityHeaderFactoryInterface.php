<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Factory\SecurityHeader;

use Inpsyde\PayoneerSdk\Api\Entities\Header\HeaderInterface;

interface SecurityHeaderFactoryInterface
{
    /**
     * Create a security header from provided header value.
     *
     * @param string $headerValue
     *
     * @return HeaderInterface
     */
    public function createSecurityHeader(string $headerValue): HeaderInterface;
}
