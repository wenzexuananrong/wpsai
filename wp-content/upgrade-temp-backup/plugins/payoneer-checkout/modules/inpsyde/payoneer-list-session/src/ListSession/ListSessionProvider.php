<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\ListSession\ListSession;

use Inpsyde\PayoneerForWoocommerce\ListSession\Exception\ListSessionExceptionInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;

interface ListSessionProvider
{
    /**
     * @param ContextInterface $context
     *
     * @return ListInterface
     * @throws ListSessionExceptionInterface
     */
    public function provide(ContextInterface $context): ListInterface;
}
