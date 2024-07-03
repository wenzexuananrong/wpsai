<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\ListSession\ListSession;

use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;

class CallbackPersistorMiddleware implements ListSessionPersistorMiddleware
{
    /**
     * @var callable(?ListInterface,ContextInterface, ListSessionPersistor):bool
     */
    private $callback;

    /**
     * @param callable(?ListInterface,ContextInterface, ListSessionPersistor):bool $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function persist(
        ?ListInterface $list,
        ContextInterface $context,
        ListSessionPersistor $next
    ): bool {

        return ($this->callback)($list, $context, $next);
    }
}
