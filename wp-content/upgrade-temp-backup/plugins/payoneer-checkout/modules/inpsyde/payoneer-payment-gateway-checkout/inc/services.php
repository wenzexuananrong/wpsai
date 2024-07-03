<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Checkout;

use Dhii\Services\Factories\Constructor;
use Dhii\Services\Factories\StringService;
use Dhii\Services\Factories\Value;
use Dhii\Services\Factory;
use Dhii\Services\Service;
use Inpsyde\PayoneerForWoocommerce\Checkout\Authentication\TokenGenerator;
use Inpsyde\PayoneerForWoocommerce\Checkout\GatewayIconsRenderer\GatewayIconsRenderer;
use Inpsyde\PayoneerForWoocommerce\Checkout\HashProvider\CheckoutHashProvider;
use Inpsyde\PayoneerForWoocommerce\Checkout\MisconfigurationDetector\MisconfigurationDetector;
use Inpsyde\PayoneerForWoocommerce\Checkout\PaymentFieldsRenderer\CompoundPaymentFieldsRenderer;
use Inpsyde\PayoneerForWoocommerce\Checkout\ProductTaxCodeProvider\ProductTaxCodeProvider;
use Inpsyde\PayoneerForWoocommerce\Checkout\StateProvider\StateProvider;
use Inpsyde\PayoneerForWoocommerce\Checkout\TransactionIdGenerator\TransactionIdGenerator;
use Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\GatewayIconsRenderer\GatewayIconsRendererInterface;
use Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\PaymentFieldsRenderer\PaymentFieldsRendererInterface;
use Inpsyde\PayoneerSdk\Api\Entities\System\System;
use Inpsyde\PayoneerSdk\Api\Entities\System\SystemInterface;
use Psr\Container\ContainerInterface;
use WC_Session;

return
    /**
     * @return array<string, callable>
     * @psalm-return array<string, callable>
     */
    static function (): array {
        return [
            'checkout.module_root_path' => static function (): string {
                return dirname(__DIR__);
            },

            'checkout.module_directory_name' => static function (): string {
                return 'checkout';
            },

            'checkout.ajax_library_path' => static function (): string {
                return '';
            },

            'checkout.ajax_library_url' => static function (): string {
                return '';
            },

            'checkout.list_session_manager.cache_key.salt.option_name' =>
                new Factory([
                    'checkout.plugin.version_string',
                ], static function (string $pluginVersion): string {
                    $versionHash = md5($pluginVersion);
                    $versionShortHash = (string) substr($versionHash, 0, 8);

                    return sprintf('payoneer_list_session_cache_key_salt_%1$s', $versionShortHash);
                }),

            'checkout.list_session_manager.cache_key.salt' =>
                new Factory(
                    [
                    'checkout.list_session_manager.cache_key.salt.option_name',
                    ],
                    static function (string $optionName): string {
                        $salt = (string) get_option($optionName);

                        if (! $salt) {
                            $salt = wp_generate_password();
                            update_option($optionName, $salt);
                        }

                        return $salt;
                    }
                ),

            'checkout.list_session_manager.cache_key.checkout' => new StringService(
                'payoneer_list_checkout_{0}',
                ['checkout.list_session_manager.cache_key.salt']
            ),
            'checkout.list_session_manager.cache_key.payment' => new StringService(
                'payoneer_list_pay_{0}_{1}',
                [
                    'wc.pay_for_order_id',
                    'checkout.list_session_manager.cache_key.salt',
                ]
            ),
            'checkout.list_session_manager.cache_key' => static function (
                ContainerInterface $container
            ): string {
                $isPayment = (bool)$container->get('wc.is_checkout_pay_page');

                $key = $isPayment
                    ? $container->get('checkout.list_session_manager.cache_key.payment')
                    : $container->get('checkout.list_session_manager.cache_key.checkout');

                return (string)$key;
            },

            'list_session.list_session_system' => new Factory(
                [
                    'checkout.plugin.version_string',
                ],
                static function (string $version): SystemInterface {
                    return new System(
                        'SHOP_PLATFORM',
                        'WOOCOMMERCE',
                        $version
                    );
                }
            ),

            'checkout.security_token' => new Factory([
                'wc.session',
                'checkout.order.security_header_field_name',
            ], static function (
                WC_Session $wcSession,
                string $tokenKey
            ): string {
                $token = $wcSession->get($tokenKey);
                if (! is_string($token)) {
                    throw new \RuntimeException(
                        sprintf(
                            "Invalid value for WC_Session key %s. Expected string, got %s",
                            $tokenKey,
                            print_r($token, true)
                        )
                    );
                }

                return $token;
            }),

            'checkout.js_extension' => static function (): string {
                //todo: use min.js when script debug is enabled
                return '.js';
            },

            'checkout.ajax_library_filename' => static function (
                ContainerInterface $container
            ): string {
                $jsExtension = (string)$container->get('checkout.js_extension');

                return sprintf('op-payment-widget%1$s', $jsExtension);
            },

            'checkout.checkout_script_filename' => static function (
                ContainerInterface $container
            ): string {
                $jsExtension = (string)$container->get('checkout.js_extension');

                return sprintf('payoneer-checkout%1$s', $jsExtension);
            },

            'checkout.success_url' => new Value(''),

            'checkout.failure_url' => new Value(''),

            'checkout.script_debug' => new Value(false),

            'checkout.css_assets_relative_path' => new Factory(
                [
                    'core.local_modules_directory_name',
                    'checkout.module_directory_name',
                ],
                static function (
                    string $modulesDirectoryRelativePath,
                    string $moduleDirectoryName
                ): string {
                    $moduleRelativePath = sprintf(
                        '%1$s/%2$s',
                        $modulesDirectoryRelativePath,
                        $moduleDirectoryName
                    );

                    return sprintf('%1$s/assets/css', $moduleRelativePath);
                }
            ),

            'checkout.list_url_container_attribute_name' => static function (): string {
                return 'data-payoneer-list-url';
            },

            'checkout.session_hash_key' => new Value('_payoneer_checkout_hash'),

            'checkout.list_hash_container_id' => new Value('data-payoneer-list-hash'),

            'checkout.module_name' => new Value('checkout'),

            'checkout.templates_dir_virtual_path' => new StringService(
                '{module_name}/static/templates',
                ['module_name' => 'checkout.module_name']
            ),

            'checkout.module_path' => new Value(dirname(__FILE__, 2)),

            'checkout.templates_dir_local_path' => new StringService(
                '{module_path}/static/templates',
                ['module_path' => 'checkout.module_path']
            ),

            'checkout.security_token_generator' => new Constructor(
                TokenGenerator::class
            ),

            'checkout.transaction_id_generator' =>
                new Constructor(TransactionIdGenerator::class),

            'checkout.checkout_hash_provider' =>
                new Constructor(CheckoutHashProvider::class, ['wc']),

            'checkout.misconfiguration_detector' =>
                new Constructor(MisconfigurationDetector::class),

            'checkout.gateway_icon_elements_css' => new Value(
                <<<CSS
.payoneer-payment-method-title{
    flex-grow: 1;
    padding-left: 3px;
}
#payment > ul > li.wc_payment_method.payment_method_payoneer-checkout{
    white-space: nowrap;
}

#payment > ul > li.wc_payment_method.payment_method_payoneer-checkout div{
    white-space: normal;
}
#payment > ul > li.wc_payment_method.payment_method_payoneer-checkout > label{
    display: flex;
    flex-wrap: wrap;
}
#gateway-icons-payoneer{
    white-space: nowrap;
    width: max-content;
    display: flex;
    vertical-align: middle;
}
#gateway-icons-payoneer span{
    display:flex;
}
CSS
            ),

            'checkout.gateway_icon_elements_filenames' =>
                new Factory(
                    [
                        'checkout.amex_icon_enabled',
                        'checkout.jcb_icon_enabled',
                    ],
                    static function (bool $amexEnabled, bool $jcbEnabled): array {
                        $icons = [
                            'visa.svg',
                            'mastercard.svg',
                        ];

                        if ($amexEnabled) {
                            $icons = array_merge($icons, ['amex.svg']);
                        }

                        if ($jcbEnabled) {
                            $icons = array_merge($icons, ['jcb.svg']);
                        }

                        return $icons;
                    }
                ),

            'checkout.amex_settings_field' => Service::fromFile(
                __DIR__ . '/amex_icon_setting_field.php'
            ),

            'checkout.jcb_settings_field' => Service::fromFile(
                __DIR__ . '/jcb_icon_setting_field.php'
            ),

            'checkout.gateway_icon_elements' => static function (
                ContainerInterface $container
            ): array {
                $moduleRootDir = (string)$container->get('checkout.module_root_path');
                $iconElementsList = [];
                $basePath = "$moduleRootDir/assets/img";
                $imgFiles = $container->get('checkout.gateway_icon_elements_filenames');
                foreach ($imgFiles as $file) {
                    assert(is_string($file));
                    $imgUrl = plugins_url('img/' . $file, $basePath);

                    $iconElementsList[] = $imgUrl;
                }

                return $iconElementsList;
            },
            'checkout.gateway_icons_renderer' => static function (
                ContainerInterface $container
            ): GatewayIconsRendererInterface {

                $iconElements = $container->get('checkout.gateway_icon_elements');

                /** @var string[] $iconElements */
                return new GatewayIconsRenderer($iconElements);
            },
            'checkout.settings.general_settings_fields' => Service::fromFile(
                __DIR__ . "/general_settings_fields.php"
            ),

            'checkout.settings.appearance_settings_fields' => Service::fromFile(
                __DIR__ . "/appearance_settings_fields.php"
            ),

            'checkout.on_error_refresh_fragment_flag' =>
                new Value('payoneer_refresh_fragment_onError'),
            'checkout.is_on_error_refresh_fragment_flag' => new Factory([
                'checkout.on_error_refresh_fragment_flag',
            ], static function (
                string $onErrorFlag
            ): bool {
                /**
                 * We can force refresh if a special flag is added
                 */
                $postData = [];
                $data = filter_input(INPUT_POST, 'post_data') ?? ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
                assert(is_string($data));
                parse_str($data, $postData);
                if (isset($postData[$onErrorFlag]) && $postData[$onErrorFlag] === 'true') {
                    return true;
                }

                return false;
            }),
            'checkout.flow_options' => new Value([]),
            'checkout.flow_options_description' => static function (): string {
                return __(
                    'Select the payment flow for every transaction.',
                    'payoneer-checkout'
                );
            },
            'checkout.payment_flow_override_flag' => new Value('payoneer_force_hosted_flow'),
            'checkout.payment_flow_override_flag.is_set' => new Factory([
                'checkout.payment_flow_override_flag',
            ], static function (string $forceHostedFlowFlag): bool {
                return filter_input(INPUT_GET, $forceHostedFlowFlag, (int)FILTER_VALIDATE_BOOL)
                       || filter_input(INPUT_POST, $forceHostedFlowFlag, (int)FILTER_VALIDATE_BOOL);
            }),
            'checkout.selected_payment_flow' => new Factory([
                'inpsyde_payment_gateway.options',
                'checkout.payment_flow_override_flag.is_set',
            ], static function (
                ContainerInterface $options,
                bool $forceHostedFlowFlagIsSet
            ): string {

                if ($forceHostedFlowFlagIsSet) {
                    return 'hosted';
                }

                try {
                    return (string)$options->get('payment_flow');
                } catch (\Throwable $exc) {
                    return "embedded"; // default
                }
            }),
            /**
             * Provide the default implementation for checkout fields. A renderer
             * that prints a list of sub-renderers that can be dynamically extended according
             * to the chosen payment flow
             *
             */
            'inpsyde_payment_gateway.payment_fields_renderer' => new Factory(
                [
                    'checkout.payment_field_renderers',
                ],
                static function (array $renderers): CompoundPaymentFieldsRenderer {
                    /**
                     * @var PaymentFieldsRendererInterface[] $renderers
                     */
                    return new CompoundPaymentFieldsRenderer(...$renderers);
                }
            ),
            'checkout.payment_field_renderers' => new Value([]),
            'checkout.http_request_timeout' => new Value(70),
            'checkout.notification_received' =>
                new Factory([
                    'checkout.notification_received.option_name',
                ], static function (string $optionName): bool {
                    return get_option($optionName) === 'yes';
                }),
            'checkout.notification_received.option_name' =>
                new Value('payoneer-checkout-notification-received'),
            'checkout.state_provider' => new Constructor(
                StateProvider::class,
                [
                    'checkout.wc.countries',
                ]
            ),
            'inpsyde_payment_gateway.is_live_mode' =>
                new Factory([
                    'inpsyde_payment_gateway.options',
                ], static function (ContainerInterface $options): bool {
                    $optionValue = $options->get('live_mode');
                    $optionValue = $optionValue !== 'no';

                    return $optionValue;
                }),
            'checkout.amex_icon_enabled' =>
                new Factory([
                    'checkout.payment_gateway_options',
                ], static function (ContainerInterface $options): bool {
                    if (! $options->has('show_amex_icon')) {
                        return true; //Show icon by default even if options wasn't saved yet.
                    }

                    $enabled = $options->get('show_amex_icon') !== 'no';

                    return $enabled;
                }),
            'checkout.product_tax_code_field_name' =>
                new Value('_payoneer-checkout_tax-code'),

            'checkout.default_product_tax_code' =>
                new Value(null),

            'checkout.product_tax_code_provider' =>
                new Constructor(ProductTaxCodeProvider::class, [
                    'checkout.product_tax_code_field_name',
                    'checkout.default_product_tax_code',
                ]),

            'checkout.jcb_icon_enabled' =>
                new Factory([
                    'checkout.payment_gateway_options',
                ], static function (ContainerInterface $options): bool {
                    if (! $options->has('show_jcb_icon')) {
                        return false; //Hide icon by default if options wasn't saved yet.
                    }

                    return $options->get('show_jcb_icon') !== 'no';
                }),
        ];
    };
