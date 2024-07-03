<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\ListSession\ListSession;

use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;

class NoopListSessionPersistor implements ListSessionPersistor
{
    public function persist(?ListInterface $list, ContextInterface $context): bool
    {
        return true;
    }
}
