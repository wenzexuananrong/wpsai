<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Exception\ListSessionExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
interface ListSessionProvider
{
    /**
     * @param ContextInterface $context
     *
     * @return ListInterface
     * @throws ListSessionExceptionInterface
     */
    public function provide(ContextInterface $context) : ListInterface;
}
