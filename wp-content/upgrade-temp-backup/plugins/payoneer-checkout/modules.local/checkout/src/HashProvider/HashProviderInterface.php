<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Checkout\HashProvider;

use Inpsyde\PayoneerForWoocommerce\Checkout\CheckoutExceptionInterface;

/**
 * A service able to provide hash of the current checkout data.
 */
interface HashProviderInterface
{
    /**
     * Provide hash of the current checkout data.
     *
     * @return string Checkout hash.
     *
     * @throws CheckoutExceptionInterface If failed to provide checkout hash.
     */
    public function provideHash(): string;
}
