<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Entities\Customer;

use Inpsyde\PayoneerSdk\Api\Entities\Name\NameInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Registration\RegistrationInterface;

class CustomerFactory implements CustomerFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createCustomer(
        string $number,
        array $phones = null,
        array $addresses = null,
        string $email = null,
        string $deliveryEmail = null,
        RegistrationInterface $registration = null,
        NameInterface $name = null
    ): CustomerInterface {

        return new Customer(
            $number,
            $phones,
            $addresses,
            $email,
            $deliveryEmail,
            $registration,
            $name
        );
    }
}
