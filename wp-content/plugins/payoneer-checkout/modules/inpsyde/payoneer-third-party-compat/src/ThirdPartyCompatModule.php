<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ThirdPartyCompat;

use Automattic\WooCommerce\Utilities\FeaturesUtil;
use Syde\Vendor\Inpsyde\Modularity\Module\ExecutableModule;
use Syde\Vendor\Inpsyde\Modularity\Module\ExtendingModule;
use Syde\Vendor\Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Syde\Vendor\Inpsyde\Modularity\Module\ServiceModule;
use Syde\Vendor\Psr\Container\ContainerInterface;
class ThirdPartyCompatModule implements ExecutableModule, ServiceModule, ExtendingModule
{
    use ModuleClassNameIdTrait;
    /**
     * @inheritDoc
     */
    public function run(ContainerInterface $container) : bool
    {
        //PN-381
        add_filter('sgo_js_minify_exclude', static function (array $scripts) {
            $scripts[] = 'op-payment-widget';
            $scripts[] = 'payoneer-checkout';
            return $scripts;
        });
        $mainPluginFile = (string) $container->get('core.main_plugin_file');
        /**
         * WooCommerce High Performance Order Storage
         * @psalm-suppress UnusedVariable
         */
        add_action('before_woocommerce_init', static function () use($mainPluginFile) {
            if (!class_exists(FeaturesUtil::class)) {
                return;
            }
            FeaturesUtil::declare_compatibility('custom_order_tables', $mainPluginFile, \true);
        });
        //CheckoutWC compatibility
        add_filter('payoneer-checkout.place_order_button.classes', static function (array $classes) : array {
            $cfwClasses = ['cfw-primary-btn', 'cfw-next-tab validate'];
            return array_merge($classes, $cfwClasses);
        });
        $this->setUpFluidCheckoutCompatibility($container);
        return \true;
    }
    /**
     * @inheritDoc
     */
    public function services() : array
    {
        static $services;
        if ($services === null) {
            $services = (require_once dirname(__DIR__) . '/inc/services.php');
        }
        /** @var callable(): array<string, callable(ContainerInterface $container):mixed> $services */
        return $services();
    }
    /**
     * @inheritDoc
     */
    public function extensions() : array
    {
        static $extensions;
        if ($extensions === null) {
            $extensions = (require_once dirname(__DIR__) . '/inc/extensions.php');
        }
        /** @var callable(): array<string, callable(mixed $service, \Psr\Container\ContainerInterface $container):mixed> $extensions */
        return $extensions();
    }
    protected function setUpFluidCheckoutCompatibility(ContainerInterface $container) : void
    {
        add_filter('woocommerce_order_button_html', static function ($buttonHtml) use($container) {
            if (!is_plugin_active('fluid-checkout/fluid-checkout.php')) {
                return $buttonHtml;
            }
            //It should be string, but we have no guarantee
            if (!is_string($buttonHtml)) {
                return $buttonHtml;
            }
            // Don't execute on Pay Order page
            if ($container->get('wc.is_checkout_pay_page')) {
                return $buttonHtml;
            }
            $paymentFlow = (string) $container->get('checkout.selected_payment_flow');
            $orderButtonText = $paymentFlow === 'embedded' ? __('Pay', 'payoneer-checkout') : __('Place order', 'payoneer-checkout');
            $buttonHtml = str_replace('Place order', esc_html($orderButtonText), $buttonHtml);
            if ($paymentFlow === 'embedded') {
                $buttonHtml .= '<script>jQuery(".fc-place-order").first().hide();</script>';
            }
            return $buttonHtml;
        });
    }
}
