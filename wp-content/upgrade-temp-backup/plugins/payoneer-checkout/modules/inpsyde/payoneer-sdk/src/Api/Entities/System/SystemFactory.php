<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Entities\System;

class SystemFactory implements SystemFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createSystem(string $type, string $code, string $version): SystemInterface
    {
        return new System($type, $code, $version);
    }
}
