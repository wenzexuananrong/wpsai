<?php

declare (strict_types=1);
namespace Syde\Vendor;

use Syde\Vendor\Inpsyde\Logger\LoggerModule;
use Syde\Vendor\Inpsyde\Modularity\Module\Module;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Analytics\AnalyticsModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Cache\CacheModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\CheckoutModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Core\CoreModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\AssetCustomizer\AssetCustomizerModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\EmbeddedPayment\EmbeddedPaymentModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Environment\EnvironmentModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\HostedPayment\HostedPaymentModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSessionModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Migration\MigrationModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Mor\MorModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Taxes\TaxesModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ThirdPartyCompat\ThirdPartyCompatModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\PaymentGatewayModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem\FilesystemModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PageDetector\PageDetectorModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Template\TemplateModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Webhooks\WebhooksModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\WebSdk\WebSdkModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Wp\WpModule;
use Syde\Vendor\Inpsyde\PayoneerSdk\SdkModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\StatusReport\StatusReportModule;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\AdminBanner\AdminBannerModule;
return static function () : iterable {
    $modules = [new EnvironmentModule(), new WpModule(), new FilesystemModule(), new PageDetectorModule(), new LoggerModule(), new CacheModule(), new TemplateModule(), new StatusReportModule(), new SdkModule(), new AssetCustomizerModule(), new CoreModule(), new PaymentGatewayModule(), new ListSessionModule(), new HostedPaymentModule(), new CheckoutModule(), new TaxesModule(), new EmbeddedPaymentModule(), new WebhooksModule(), new WebSdkModule(), new MigrationModule(), new ThirdPartyCompatModule(), new AdminBannerModule(), new AnalyticsModule()];
    if (\apply_filters('inpsyde.feature-flags.payoneer-checkout.mor_enabled', \getenv('PN_MOR_ENABLED') === '1')) {
        $modules[] = new MorModule();
    }
    return $modules;
};
