<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Entities\Phone;

class PhoneFactory implements PhoneFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createPhone(string $unstructuredNumber): PhoneInterface
    {
        return new Phone($unstructuredNumber);
    }
}
