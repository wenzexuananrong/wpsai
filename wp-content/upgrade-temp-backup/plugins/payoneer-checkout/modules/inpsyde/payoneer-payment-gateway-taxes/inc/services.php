<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Checkout;

use Dhii\Services\Factories\Constructor;
use Dhii\Services\Factories\StringService;
use Dhii\Services\Factory;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;
use Inpsyde\PayoneerForWoocommerce\Taxes\Modifier\DiscountTaxModifier;
use Inpsyde\PayoneerForWoocommerce\Taxes\Modifier\FeeTaxModifier;
use Inpsyde\PayoneerForWoocommerce\Taxes\Modifier\LineItemTaxModifier;
use Inpsyde\PayoneerForWoocommerce\Taxes\Modifier\ShippingTaxModifier;
use Inpsyde\PayoneerForWoocommerce\Taxes\TaxConfigurationChecker;

return
    /**
     * @return array<string, callable>
     * @psalm-return array<string, callable>
     */
    static function (): array {
        return [
            'taxes.path.css' => new StringService(
                '{0}/css/',
                ['taxes.path.assets']
            ),
            'taxes.path.js' => new StringService(
                '{0}/js/',
                ['taxes.path.assets']
            ),
            'taxes.path.assets' => new Factory(
                [
                    'core.local_modules_directory_name',
                ],
                static function (
                    string $modulesDirectoryRelativePath
                ): string {
                    $moduleRelativePath = sprintf(
                        '%1$s/%2$s',
                        $modulesDirectoryRelativePath,
                        'payoneer-payment-gateway-taxes'
                    );

                    return sprintf('%1$s/assets', $moduleRelativePath);
                }
            ),
            'taxes.assets.css' => new Factory([
                'core.main_plugin_file',
                'taxes.path.css',
                'core.is_checkout',
            ], static function (
                string $mainPluginFile,
                string $cssPath,
                bool $isCheckout
            ): Style {
                $url = plugins_url(
                    $cssPath . 'taxes.css',
                    $mainPluginFile
                );
                $style = new Style('taxes', $url);
                $style->canEnqueue($isCheckout);

                return $style;
            }),
            'taxes.assets.js' => new Factory([
                'core.main_plugin_file',
                'taxes.path.js',
                'core.is_checkout',
            ], static function (
                string $mainPluginFile,
                string $jsPath,
                bool $isCheckout
            ): Script {
                $url = plugins_url(
                    $jsPath . 'taxes.js',
                    $mainPluginFile
                );
                $script = new Script('payoneer-payment-gateway-taxes', $url);
                $script->canEnqueue($isCheckout);

                return $script;
            }),
            'taxes.assets' => new Factory(
                [
                    'taxes.assets.js',
                    'taxes.assets.css',
                ],
                static function (
                    Script $taxesJs,
                    Style $taxesCSS
                ): array {
                    return [$taxesJs, $taxesCSS];
                }
            ),
            'wc.shipping.packages' => static function () {
                return WC()->shipping()->packages;
            },
            'inpsyde_payment_gateway.line_item_tax_modifier' => new Factory(
                [
                    'wc.cart',
                ],
                static function (\WC_Cart $cart) {
                    return new LineItemTaxModifier($cart);
                }
            ),
            'inpsyde_payment_gateway.fee_tax_modifier' => new Factory(
                [
                    'wc.cart',
                ],
                static function (\WC_Cart $cart) {
                    return new FeeTaxModifier($cart);
                }
            ),
            'inpsyde_payment_gateway.shipping_tax_modifier' => new Factory(
                ['wc.cart', 'wc.shipping.packages'],
                static function (\WC_Cart $cart, array $shippingPackages) {
                    $shippingTaxModifier = new ShippingTaxModifier($cart);
                    $shippingTaxModifier->setShippingPackages($shippingPackages);
                    return $shippingTaxModifier;
                }
            ),

            'inpsyde_payment_gateway.discount_tax_modifier' => new Factory(
                ['wc.cart'],
                static function (\WC_Cart $cart): DiscountTaxModifier {
                    return new DiscountTaxModifier($cart);
                }
            ),

            'inpsyde_payment_gateway.tax_configuration_checker' => new Constructor(
                TaxConfigurationChecker::class
            ),
        ];
    };
