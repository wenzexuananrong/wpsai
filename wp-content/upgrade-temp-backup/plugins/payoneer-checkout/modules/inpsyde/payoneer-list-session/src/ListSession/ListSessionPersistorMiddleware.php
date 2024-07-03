<?php

namespace Inpsyde\PayoneerForWoocommerce\ListSession\ListSession;

use Inpsyde\PayoneerForWoocommerce\ListSession\Exception\ListSessionExceptionInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;

interface ListSessionPersistorMiddleware extends ListSessionMiddleware
{
    /**
     * @param ListInterface|null $list
     * @param ContextInterface $context
     * @param ListSessionPersistor $next
     *
     * @return bool
     * @throws ListSessionExceptionInterface
     */
    public function persist(
        ?ListInterface $list,
        ContextInterface $context,
        ListSessionPersistor $next
    ): bool;
}
