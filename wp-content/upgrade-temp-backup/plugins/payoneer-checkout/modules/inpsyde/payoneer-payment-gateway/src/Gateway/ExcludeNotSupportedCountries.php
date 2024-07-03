<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway;

use WC_Customer;

/**
 * Make payment gateway not available if customer's country is in excluded list.
 */
class ExcludeNotSupportedCountries
{
    /**
     * @var string[]
     */
    protected $notSupportedCountries;

    /**
     * @param string[] $notSupportedCountries
     */
    public function __construct(array $notSupportedCountries)
    {

        $this->notSupportedCountries = $notSupportedCountries;
    }

    public function __invoke(): void
    {
        add_filter('payoneer-checkout.payment_gateway_is_available', function (
            bool $previous
        ): bool {

            if (! is_checkout()) {
                return $previous;
            }

            /** @var WC_Customer|null $customer */
            $customer = wc()->customer;
            if (! $customer) {
                return $previous;
            }

            if ($previous === false) {
                return false;
            }

            $country = $customer->get_billing_country();

            return ! in_array($country, $this->notSupportedCountries, true);
        });
    }
}
