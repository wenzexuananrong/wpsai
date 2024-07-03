<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Entities\Customer;

use Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Address\AddressInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Name\NameInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Phone\PhoneInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Registration\RegistrationInterface;

/**
 * Service able to create Customer instance.
 */
interface CustomerFactoryInterface
{
    /**
     * Create a new Customer instance.
     *
     * @param string $number Customer identifier provided by merchant.
     * @param array{mobile: PhoneInterface}|null $phones Map of customer phones.
     * @param array{billing: AddressInterface, shipping?: AddressInterface}|null $addresses Addresses.
     * @param string|null $email Customer email address.
     * @param string|null $deliveryEmail Customer email address for digital delivery.
     * @param RegistrationInterface|null $registration Object with info about customer registration.
     * @param NameInterface|null $name Object with customer name.
     *
     * @return CustomerInterface A new Customer instance.
     *
     * @throws ApiExceptionInterface If failed to create customer object.
     */
    public function createCustomer(
        string $number,
        array $phones = null,
        array $addresses = null,
        string $email = null,
        string $deliveryEmail = null,
        RegistrationInterface $registration = null,
        NameInterface $name = null
    ): CustomerInterface;
}
