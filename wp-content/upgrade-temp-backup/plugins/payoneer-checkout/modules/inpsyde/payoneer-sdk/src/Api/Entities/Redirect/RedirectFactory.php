<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Entities\Redirect;

class RedirectFactory implements RedirectFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createRedirect(string $url, string $method, string $type, array $parameters): RedirectInterface
    {
        return new Redirect($url, $method, $type, $parameters);
    }
}
