<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Migration;

interface MigratorInterface
{
    /**
     * Do migration to the current plugin version.
     */
    public function migrate(): void;
}
