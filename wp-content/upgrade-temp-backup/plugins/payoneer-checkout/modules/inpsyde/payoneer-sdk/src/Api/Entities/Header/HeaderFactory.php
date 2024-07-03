<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Entities\Header;

class HeaderFactory implements HeaderFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createHeader(string $name, string $value): HeaderInterface
    {
        return new Header($name, $value);
    }
}
