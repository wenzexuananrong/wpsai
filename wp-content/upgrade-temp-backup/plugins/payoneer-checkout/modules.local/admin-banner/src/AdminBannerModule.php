<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\AdminBanner;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\AssetManager;
use Inpsyde\Modularity\Module\ExecutableModule;
use Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Inpsyde\Modularity\Module\ServiceModule;
use Psr\Container\ContainerInterface;

class AdminBannerModule implements ServiceModule, ExecutableModule
{
    use ModuleClassNameIdTrait;

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
    public function run(ContainerInterface $container): bool
    {
        $container->get('admin_banner.assets.css.banner.url');

        add_action(AssetManager::ACTION_SETUP, static function (
            AssetManager $assetManager
        ) use ($container): void {
            $adminStyle = $container->get('admin_banner.assets.css.banner');
            assert($adminStyle instanceof Asset);

            $assetManager->register($adminStyle);
        });

        add_action('all_admin_notices', static function () use ($container): void {
            $shouldDisplay = $container->get('admin_banner.should_display');

            if ($shouldDisplay) {
                $renderer = $container->get('admin_banner.banner_renderer');
                assert($renderer instanceof AdminBannerRendererInterface);

                $renderer->renderBanner();
            }
        });

        return true;
    }

    /**
     * @inheritDoce
     */
    public function services(): array
    {
        return $this->services;
    }
}
