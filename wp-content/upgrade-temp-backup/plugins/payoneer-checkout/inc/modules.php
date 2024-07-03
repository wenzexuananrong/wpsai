<?php

declare(strict_types=1);

use Inpsyde\Logger\LoggerModule;
use Inpsyde\Modularity\Module\Module;
use Inpsyde\PayoneerForWoocommerce\Analytics\AnalyticsModule;
use Inpsyde\PayoneerForWoocommerce\Cache\CacheModule;
use Inpsyde\PayoneerForWoocommerce\Checkout\CheckoutModule;
use Inpsyde\PayoneerForWoocommerce\Core\CoreModule;
use Inpsyde\PayoneerForWoocommerce\AssetCustomizer\AssetCustomizerModule;
use Inpsyde\PayoneerForWoocommerce\EmbeddedPayment\EmbeddedPaymentModule;
use Inpsyde\PayoneerForWoocommerce\Environment\EnvironmentModule;
use Inpsyde\PayoneerForWoocommerce\HostedPayment\HostedPaymentModule;
use Inpsyde\PayoneerForWoocommerce\ListSession\ListSessionModule;
use Inpsyde\PayoneerForWoocommerce\Migration\MigrationModule;
use Inpsyde\PayoneerForWoocommerce\Mor\MorModule;
use Inpsyde\PayoneerForWoocommerce\Taxes\TaxesModule;
use Inpsyde\PayoneerForWoocommerce\ThirdPartyCompat\ThirdPartyCompatModule;
use Inpsyde\PayoneerForWoocommerce\PaymentGateway\PaymentGatewayModule;
use Inpsyde\PayoneerForWoocommerce\Filesystem\FilesystemModule;
use Inpsyde\PayoneerForWoocommerce\PageDetector\PageDetectorModule;
use Inpsyde\PayoneerForWoocommerce\Template\TemplateModule;
use Inpsyde\PayoneerForWoocommerce\Webhooks\WebhooksModule;
use Inpsyde\PayoneerForWoocommerce\WebSdk\WebSdkModule;
use Inpsyde\PayoneerForWoocommerce\Wp\WpModule;
use Inpsyde\PayoneerSdk\SdkModule;
use Inpsyde\PayoneerForWoocommerce\AdminBanner\AdminBannerModule;

return
    /**
     * @return iterable<Module>
     */
    static function (): iterable {
        $modules =  [
            new EnvironmentModule(),
            new WpModule(),
            new FilesystemModule(),
            new PageDetectorModule(),
            new LoggerModule(),
            new CacheModule(),
            new TemplateModule(),
            new SdkModule(),
            new AssetCustomizerModule(),
            new CoreModule(),
            new PaymentGatewayModule(),
            new ListSessionModule(),
            new HostedPaymentModule(),
            new CheckoutModule(),
            new TaxesModule(),
            new EmbeddedPaymentModule(),
            new WebhooksModule(),
            new WebSdkModule(),
            new MigrationModule(),
            new ThirdPartyCompatModule(),
            new AdminBannerModule(),
            new AnalyticsModule(),
        ];
        if (
            apply_filters(
                'inpsyde.feature-flags.payoneer-checkout.mor_enabled',
                getenv('PN_MOR_ENABLED') === '1'
            )
        ) {
            $modules[] = new MorModule();
        }

        return $modules;
    };
