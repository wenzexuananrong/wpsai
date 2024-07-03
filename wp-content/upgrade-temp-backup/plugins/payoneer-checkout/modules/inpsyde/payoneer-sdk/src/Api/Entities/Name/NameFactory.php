<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Entities\Name;

class NameFactory implements NameFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createName(string $firstName, string $lastName): NameInterface
    {
        return new Name($firstName, $lastName);
    }
}
