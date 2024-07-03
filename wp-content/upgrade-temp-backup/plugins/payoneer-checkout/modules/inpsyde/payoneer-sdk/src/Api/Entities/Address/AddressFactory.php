<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Entities\Address;

use Inpsyde\PayoneerSdk\Api\Entities\Name\NameInterface;

class AddressFactory implements AddressFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createAddress(
        string $country,
        string $city,
        string $street,
        string $postalCode,
        NameInterface $name = null,
        string $state = null
    ): AddressInterface {

        return new Address(
            $country,
            $city,
            $street,
            $postalCode,
            $name,
            $state
        );
    }
}
