<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Environment;

/**
 * Service able to create WpEnvironmentInterface instance.
 */
interface WpEnvironmentFactoryInterface
{
    /**
     * Create WpEnvironmentInterface instance from available globals.
     *
     * @return WpEnvironmentInterface
     */
    public function createFromGlobals(): WpEnvironmentInterface;
}
