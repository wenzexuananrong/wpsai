<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Entities\Header;

class HeaderSerializer implements HeaderSerializerInterface
{
    /**
     * @inheritDoc
     */
    public function serializeHeader(HeaderInterface $header): array
    {
        $name = $header->getName();
        $value = $header->getValue();

        return ['name' => $name, 'value' => $value];
    }
}
