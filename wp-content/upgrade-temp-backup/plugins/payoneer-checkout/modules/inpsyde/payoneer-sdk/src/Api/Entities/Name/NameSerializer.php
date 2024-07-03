<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Entities\Name;

class NameSerializer implements NameSerializerInterface
{
    /**
     * @inheritDoc
     */
    public function serializeName(NameInterface $name): array
    {
        return [
            'firstName' => $name->getFirstName(),
            'lastName' => $name->getLastName(),
        ];
    }
}
