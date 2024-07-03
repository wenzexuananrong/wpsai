<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk;

use Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Inpsyde\Modularity\Module\ServiceModule;

/**
 * The main module class.
 */
class SdkModule implements ServiceModule
{
    use ModuleClassNameIdTrait;

    /**
     * @inheritDoc
     */
    public function services(): array
    {

        static $services;

        if ($services === null) {
            $services = require_once dirname(__DIR__) . '/inc/services.php';
        }

        /** @var callable(): array<string, callable(\Psr\Container\ContainerInterface $container):mixed> $services */
        return $services();
    }
}
