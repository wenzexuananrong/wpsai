<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\ListSession\ListSession;

use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;

class CallbackProviderMiddleware implements ListSessionProviderMiddleware
{
    /**
     * @var callable(ContextInterface, ListSessionProvider):ListInterface
     */
    private $callback;

    /**
     * @param callable(ContextInterface, ListSessionProvider):ListInterface $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function provide(ContextInterface $context, ListSessionProvider $next): ListInterface
    {
        return ($this->callback)($context, $next);
    }
}
