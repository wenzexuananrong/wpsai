<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Entities\Registration;

class RegistrationFactory implements RegistrationFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createRegistration(string $id, string $password = null): RegistrationInterface
    {
        return new Registration($id, $password);
    }
}
