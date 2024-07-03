<?php

namespace Inpsyde\PayoneerForWoocommerce\ListSession\ListSession;

use Inpsyde\PayoneerForWoocommerce\ListSession\Exception\ListSessionExceptionInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;

interface ListSessionPersistor
{
    /**
     * Persist a LIST session if passed.
     * Passing null should instruct implementations to clear a previously persisted object
     *
     * @param ?ListInterface $list
     * @param ContextInterface $context
     *
     * @return bool
     * @throws ListSessionExceptionInterface
     */
    public function persist(?ListInterface $list, ContextInterface $context): bool;
}
