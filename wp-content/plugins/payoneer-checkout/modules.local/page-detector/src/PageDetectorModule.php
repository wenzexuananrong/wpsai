<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\PageDetector;

use Inpsyde\Modularity\Module\ServiceModule;
use Psr\Container\ContainerInterface;

/**
 * The Page Detector module.
 */
class PageDetectorModule implements ServiceModule
{
    /**
     * @var array<string, callable>
     * @psalm-var array<string, callable(ContainerInterface): mixed>
     */
    protected $services;

    public function __construct()
    {

        $moduleRootDir = dirname(__FILE__, 2);
        $this->services = (require "$moduleRootDir/inc/services.php")();
    }

    /**
     * @inheritDoc
     */
    public function id(): string
    {
        return 'payoneer-page-detector';
    }

    /**
     * @inheritDoc
     */
    public function services(): array
    {
        return $this->services;
    }
}
