<?php

declare(strict_types=1);

use Dhii\Collection\MapFactoryInterface;
use Dhii\Collection\MapInterface;
use Dhii\Collection\MutableContainerInterface;
use Dhii\Container\DataStructureBasedFactory;
use Dhii\Package\Version\StringVersionFactoryInterface;
use Dhii\Package\Version\VersionInterface;
use Dhii\Services\Factories\Alias;
use Dhii\Services\Factories\Constructor;
use Dhii\Services\Factories\Value;
use Dhii\Services\Factory;
use Dhii\Validation\ValidatorInterface;
use Dhii\Validator\CallbackValidator;
use Dhii\Validator\CompositeValidator;
use Dhii\Versions\StringVersionFactory;
use Inpsyde\Modularity\Package;
use Inpsyde\Modularity\Properties\PluginProperties;
use Inpsyde\Modularity\Properties\Properties;
use Inpsyde\PayoneerForWoocommerce\AssetCustomizer\AssetProcessorInterface;
use Inpsyde\PayoneerForWoocommerce\AssetCustomizer\CachingFileTemplateAssetProcessorFactoryInterface;
use Inpsyde\PayoneerForWoocommerce\Cache\FileCacheFactoryInterface;
use Inpsyde\PayoneerForWoocommerce\Core\PluginActionLink\PluginActionLink;
use Inpsyde\PayoneerForWoocommerce\Core\PluginActionLink\PluginActionLinkRegistry;
use Inpsyde\PayoneerForWoocommerce\Dictionary\DictionaryFactory;
use Inpsyde\PayoneerForWoocommerce\Environment\WpEnvironmentFactory;
use Inpsyde\PayoneerForWoocommerce\Environment\WpEnvironmentFactoryInterface;
use Inpsyde\PayoneerForWoocommerce\Environment\WpEnvironmentInterface;
use Inpsyde\PayoneerForWoocommerce\Filesystem\PathResolverWrappingUrlResolver;
use Inpsyde\PayoneerForWoocommerce\Filesystem\StringStreamFactoryInterface;
use Inpsyde\PayoneerForWoocommerce\Filesystem\UrlResolverInterface;
use Inpsyde\PayoneerForWoocommerce\Cache\FileCacheInterface;
use Inpsyde\PayoneerForWoocommerce\Filesystem\NativeHashContextFactory;
use Inpsyde\PayoneerForWoocommerce\Filesystem\NativeHasher;
use Inpsyde\PayoneerForWoocommerce\Filesystem\PathResolverInterface;
use Inpsyde\PayoneerForWoocommerce\Filesystem\PrefixMatchingPathResolverFactoryInterface;
use Inpsyde\PayoneerForWoocommerce\PageDetector\PageDetectorInterface;
use Inpsyde\PayoneerForWoocommerce\PageDetector\UrlPageDetectorFactoryInterface;
use Inpsyde\PayoneerForWoocommerce\PaymentGateway\Api\BasicTokenProvider;
use Inpsyde\PayoneerForWoocommerce\PaymentGateway\Api\PayoneerFactory;
use Inpsyde\PayoneerForWoocommerce\PaymentGateway\Api\PayoneerFactoryInterface;
use Inpsyde\PayoneerForWoocommerce\PaymentGateway\Client\ClientFactory;
use Inpsyde\PayoneerForWoocommerce\PaymentGateway\Client\ClientFactoryInterface;
use Inpsyde\PayoneerForWoocommerce\Template\PathTokenTemplateFactory;
use Inpsyde\PayoneerForWoocommerce\Template\StreamingPlaceholderTemplateFactory;
use Inpsyde\PayoneerForWoocommerce\Template\StreamTemplateFactoryInterface;
use Inpsyde\PayoneerForWoocommerce\Template\TemplateInterface;
use Inpsyde\PayoneerSdk\Api\Command\CommandInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Address\AddressFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Callback\CallbackFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Callback\CallbackInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Name\NameFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Phone\PhoneFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Product\ProductDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Product\ProductFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Product\ProductInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Product\ProductType;
use Inpsyde\PayoneerSdk\Api\Entities\Style\StyleFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Style\StyleInterface;
use Inpsyde\PayoneerSdk\Api\PayoneerInterface;
use Inpsyde\PayoneerSdk\Client\TokenAwareInterface;
use Inpsyde\Wp\HttpClient\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

use function Inpsyde\PayoneerForWoocommerce\plugin;

return static function (string $rootPath): array {
    return [
        'core.plugin_instance' => static function (): Package {

            /**
             * @var Package
             *
             * @psalm-suppress UndefinedFunction
             */
            return plugin();
        },

        'core.plugin.version' =>
            new Factory([
                'core.plugin.version_string',
                'core.string_version_factory',
            ], static function (
                string $pluginVersion,
                StringVersionFactoryInterface $versionFactory
            ): VersionInterface {
                $product = $versionFactory
                    ->createVersionFromString($pluginVersion);
                return $product;
            }),

        'core.plugin.version_string' => new Factory(
            [
                Package::PROPERTIES,
            ],
            static function (PluginProperties $properties): string {
                return $properties->version();
            }
        ),

        'core.plugin.plugin_action_links' =>
            new Factory([
                'core.http.settings_url',
            ], static function (UriInterface $settingsUrl): array {
                return [
                    new PluginActionLink(
                        'settings',
                        __('Settings', 'payoneer-checkout'),
                        $settingsUrl
                    ),
                ];
            }),

        'core.plugin.plugin_action_links.registry' =>
            new Factory([
                'core.main_plugin_file',
                'core.plugin.plugin_action_links',
            ], static function (
                string $mainFilePath,
                array $pluginActionLinks
            ): PluginActionLinkRegistry {
                /** @var PluginActionLink[] $pluginActionLinks */
                return new PluginActionLinkRegistry(
                    plugin_basename($mainFilePath),
                    ...$pluginActionLinks
                );
            }),

        'core.module.root_path' =>
            new Factory([
            ], static function () use ($rootPath): string {
                return $rootPath;
            }),

        'core.module.name' =>
            new Value('core'),

        'core.module.root_url' =>
            new Factory([
                'core.module.root_path',
                'core.uri.factory',
            ], static function (
                string $rootPath,
                UriFactoryInterface $uriFactory
            ): UriInterface {
                $urlString = plugins_url('', "$rootPath/payoneer-checkout.php");

                $url = $uriFactory->createUri($urlString);

                return $url;
            }),

        'core.user_id' =>
            new Alias('wp.user_id'),

        'core.assets.dirname' =>
            new Value('assets'),

        'core.assets.path' =>
            new Factory([
                'core.module.root_path',
                'core.assets.dirname',
            ], static function (
                string $rootPath,
                string $dirname
            ): string {
                return "{$rootPath}/{$dirname}";
            }),

        'core.assets.url' =>
            new Factory([
                'core.module.root_url',
                'core.uri.factory',
                'core.assets.dirname',
            ], static function (
                UriInterface $rootUrl,
                UriFactoryInterface $uriFactory,
                string $dirname
            ): UriInterface {
                return $uriFactory->createUri("{$rootUrl}/{$dirname}");
            }),

        'core.assets.generated.dirname' =>
            new Value('generated'),

        'core.assets.generated.path' =>
            new Factory([
                'core.assets.path',
                'core.assets.generated.dirname',
            ], static function (
                string $rootPath,
                string $dirname
            ): string {
                return "{$rootPath}/{$dirname}";
            }),

        'core.assets.css.websdk.url' =>
            new Alias('websdk.assets.css.websdk.url'),

        'core.assets.js.websdk.url' =>
            new Alias('websdk.assets.js.websdk.url'),

        'core.assets.css.widget.css' =>
            new Alias('websdk.assets.css.widget.url'),

        //'core.list_session_remover.wc_order' =>
        //    new Alias('checkout.list_session_remover.wc_order'),

        'core.payment_flow_override_flag.is_set' =>
            new Alias('checkout.payment_flow_override_flag.is_set'),

        'core.assets.generated.url' =>
            new Factory([
                'core.assets.url',
                'core.uri.factory',
                'core.assets.generated.dirname',
            ], static function (
                UriInterface $rootUrl,
                UriFactoryInterface $uriFactory,
                string $dirname
            ): UriInterface {
                return $uriFactory->createUri("{$rootUrl}/{$dirname}");
            }),

        'core.assets.version' =>
            new Factory([
                Package::PROPERTIES,
            ], static function (
                Properties $properties
            ): string {
                return $properties->version();
            }),

        'core.http.current_url' =>
            new Factory([
                'core.uri.factory',
            ], static function (UriFactoryInterface $factory): UriInterface {
                $protocol = isset($_SERVER['HTTP']) && $_SERVER['HTTP'] === 'on'
                    ? 'https'
                    : 'http';
                /**
                 * @psalm-suppress MixedArgument
                 * @psalm-suppress PossiblyInvalidCast
                 */
                $host = esc_url_raw($protocol . "://" . (wp_unslash($_SERVER['HTTP_HOST'] ?? '')));
                /**
                 * @psalm-suppress MixedArgument
                 * @psalm-suppress PossiblyInvalidCast
                 */
                $trail = esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'] ?? ''));
                $uri = "{$host}{$trail}";
                $product = $factory->createUri($uri);

                return $product;
            }),

        'core.http.base_url' =>
            new Factory([
                'core.uri.factory',
            ], static function (
                UriFactoryInterface $factory
            ): UriInterface {
                $url = get_home_url();
                $product = $factory->createUri($url);

                return $product;
            }),

        'core.http.base_path' =>
            new Factory([
                'core.http.base_url',
            ], static function (
                UriInterface $baseUrl
            ): string {
                $baseUrl = (string) $baseUrl;
                $urlSegments = parse_url($baseUrl);
                $path = $urlSegments['path'] ?? '';
                $path = trim($path, " \t\n\r\0\x0B/");

                return $path;
            }),

        'core.http.settings_url' =>
            new Factory([
                'core.uri.factory',
            ], static function (
                UriFactoryInterface $factory
            ): UriInterface {
                $product = $factory->createUri(
                    sprintf(
                        '%s?%s',
                        admin_url('admin.php'),
                        http_build_query(
                            [
                                'page' => 'wc-settings',
                                'tab' => 'checkout',
                                'section' => 'payoneer-checkout',
                            ]
                        )
                    )
                );
                return $product;
            }),

        'core.wc.price_include_tax' =>
            new Alias('wc.settings.price_include_tax'),

        'core.event_name_application_boot_initialized' => static function (
            ContainerInterface $container
        ): string {
            /** @var Package $plugin */
            $plugin = $container->get('core.plugin_instance');
            return $plugin->hookName((string) $plugin::ACTION_INIT);
        },
        'core.event_name_application_boot_ready' => static function (
            ContainerInterface $container
        ): string {
            /** @var Package $plugin */
            $plugin = $container->get('core.plugin_instance');
            return $plugin->hookName((string) $plugin::ACTION_READY);
        },
        'core.event_name_application_boot_failed' => static function (
            ContainerInterface $container
        ): string {
            /** @var Package $plugin */
            $plugin = $container->get('core.plugin_instance');
            return $plugin->hookName((string) $plugin::ACTION_FAILED_BOOT);
        },

        'core.string_version_factory' => static function (): StringVersionFactoryInterface {
            return new StringVersionFactory();
        },

        'core.wp_environment_factory' => static function (
            ContainerInterface $container
        ): WpEnvironmentFactoryInterface {

            /** @var StringVersionFactoryInterface $versionFactory */
            $versionFactory = $container->get('core.string_version_factory');

            /** @var string $eventNameEnvironmentValidationFailed */
            $eventNameEnvironmentValidationFailed = $container->get('core.event_name_environment_validation_failed');

            return new WpEnvironmentFactory($versionFactory, $eventNameEnvironmentValidationFailed);
        },

        'core.wp_environment' => static function (
            ContainerInterface $container
        ): WpEnvironmentInterface {
            /** @var WpEnvironmentFactoryInterface $environmentFactory */
            $environmentFactory = $container->get('core.wp_environment_factory');

            return $environmentFactory->createFromGlobals();
        },

        'core.php_version_validator' => static function (
            ContainerInterface $container
        ): ValidatorInterface {

            /** @var Properties $pluginProperties */
            $pluginProperties = $container->get('properties');

            return new CallbackValidator(static function (
                WpEnvironmentInterface $environment
            ) use ($pluginProperties): ?string {
                if (version_compare($environment->getPhpVersion(), (string) $pluginProperties->requiresPhp(), '>=')) {
                    return null;
                }

                return sprintf(
                    'Required PHP version is %1$s, but the current one is %2$s',
                    (string) $pluginProperties->requiresPhp(),
                    $environment->getPhpVersion()
                );
            });
        },

        'core.wp_version_validator' => static function (
            ContainerInterface $container
        ): ValidatorInterface {

            /** @var Properties $pluginProperties */
            $pluginProperties = $container->get('properties');

            return new CallbackValidator(static function (
                WpEnvironmentInterface $environment
            ) use ($pluginProperties): ?string {
                if (version_compare($environment->getWpVersion(), (string) $pluginProperties->requiresWp(), '>=')) {
                    return null;
                }

                return sprintf(
                    'Required WordPress version is %1$s, but the current one is %2$s',
                    (string) $pluginProperties->requiresWp(),
                    $environment->getWpVersion()
                );
            });
        },

        'core.wc_version_validator' => static function (
            ContainerInterface $container
        ): ValidatorInterface {

            /** @var Properties $pluginProperties */
            $pluginProperties = $container->get('properties');

            $requiredWcVersion = (string) $pluginProperties->get('WC requires at least');

            return new CallbackValidator(static function (
                WpEnvironmentInterface $environment
            ) use ($requiredWcVersion): ?string {
                if (
                    version_compare(
                        $environment->getWcVersion(),
                        $requiredWcVersion,
                        '>='
                    )
                ) {
                    return null;
                }

                return sprintf(
                    'Required WooCommerce version is %1$s, but the current one is %2$s',
                    $requiredWcVersion,
                    $environment->getWcVersion()
                );
            });
        },

        'core.wc_active_validator' => static function (
            ContainerInterface $container
        ): ValidatorInterface {

            /** @var Properties $pluginProperties */
            $pluginProperties = $container->get('properties');
            $pluginName = $pluginProperties->name();
            return new CallbackValidator(static function (
                WpEnvironmentInterface $environment
            ) use ($pluginName): ?string {
                if ($environment->getWcActive()) {
                    return null;
                }

                return sprintf(
                    '%1$s requires WooCommerce to be active.',
                    $pluginName
                );
            });
        },

        'core.environment_validator' => static function (
            ContainerInterface $container
        ): ValidatorInterface {
            /** @var ValidatorInterface $phpVersionValidator */
            $phpVersionValidator = $container->get('core.php_version_validator');
            /** @var ValidatorInterface $wpVersionValidator */
            $wpVersionValidator = $container->get('core.wp_version_validator');
            /** @var ValidatorInterface $wcVersionValidator */
            $wcVersionValidator = $container->get('core.wc_version_validator');
            /** @var ValidatorInterface $wcActiveValidator */
            $wcActiveValidator = $container->get('core.wc_active_validator');

            return new CompositeValidator(
                [
                    $phpVersionValidator,
                    $wpVersionValidator,
                    $wcVersionValidator,
                    $wcActiveValidator,
                ]
            );
        },

        'core.event_name_environment_validation_failed' => static function (): string {
            return 'payoneer-checkout.environment_validation_failed';
        },

        'core.customer_registration_id_field_name' =>
            new Value('_payoneer_customer_registration_id'),

        'core.merchant_division' =>
            new Alias('inpsyde_payment_gateway.merchant_division'),

        'core.list_hash_container_id' =>
            new Alias('checkout.list_hash_container_id'),

        'core.checkout_hash_provider' =>
            new Alias('checkout.checkout_hash_provider'),

        'core.is_debug' =>
            new Factory(
                [Package::PROPERTIES],
                static function (PluginProperties $properties): bool {
                    return $properties->isDebug();
                }
            ),

        'core.order_item_types_for_product' =>
            new Alias('wc.order_item_types_for_product'),

        'core.quantity_normalizer' =>
            new Alias('list_session.quantity_normalizer'),

        'core.embedded_payment.custom_css.default' =>
            new Alias('embedded_payment.settings.checkout_css_custom_css.default'),

        'core.settings_option_key' =>
            new Alias('inpsyde_payment_gateway.settings_option_key'),

        'core.options' =>
            new Alias('inpsyde_payment_gateway.options'),

        'core.misconfiguration_detector' =>
            new Alias('checkout.misconfiguration_detector'),

        'core.http.wp_http_object' =>
            new Alias('wp.http.wp_http_object'),

        'core.http.request_factory' =>
            new Alias('payoneer_sdk.request_factory'),

        'core.http.response_factory' =>
            new Alias('payoneer_sdk.response_factory'),

        'core.stream_factory' =>
            new Alias('payoneer_sdk.stream_factory'),

        'core.wc_shop_url' =>
            new  Alias('wc.shop_url'),

        'core.admin_url' =>
            new Alias('wp.admin_url'),

        'core.is_checkout_pay_page' =>
            new Alias('wc.is_checkout_pay_page'),

        'core.is_checkout' =>
            new Alias('wc.is_checkout'),

        'core.is_ajax' =>
            new Alias('wp.is_ajax'),

        'core.is_live_mode' =>
            new Alias('inpsyde_payment_gateway.is_live_mode'),
        'core.product_tax_code_provider' =>
            new Alias('checkout.product_tax_code_provider'),

        'core.product_tax_code_field_name' =>
            new Alias('checkout.product_tax_code_field_name'),

        'core.selected_payment_flow' =>
            new Alias('checkout.selected_payment_flow'),

        'core.list_session_manager' =>
            new Alias('list_session.manager'),

        'core.order_based_update_command_factory' =>
            new Alias('inpsyde_payment_gateway.update_command_factory'),

        'core.list.hosted_version' =>
            new Alias('list_session.hosted_version'),

        'core.fallback_country' =>
            new Factory([
                'inpsyde_payment_gateway.store_country',
            ], static function (string $storeCountry): string {
                return $storeCountry ?: 'US';
            }),

        # Essential factories
        # =================================================================

        'core.file_stream.factory' =>
            new Alias('filesystem.file_stream.factory'),

        'core.string_stream.factory' =>
            new Alias('filesystem.string_stream.factory'),

        'core.uri.factory' =>
            new Alias('filesystem.uri.factory'),

        'core.file_cache.factory' =>
            new Alias('cache.base_dir_file_cache.factory'),

        'core.style_factory' =>
            new Alias('payoneer_sdk.style_factory'),

        'core.wc_order_based_callback_factory' =>
            new Alias('inpsyde_payment_gateway.wc_order_based_callback_factory'),

        'core.wc_order_based_customer_factory' =>
            new Alias('inpsyde_payment_gateway.wc_order_based_customer_factory'),

        'core.wc_order_based_products_factory' =>
            new Alias('inpsyde_payment_gateway.wc_order_based_products_factory'),

        'core.wc_order_based_payment_factory' =>
            new Alias('inpsyde_payment_gateway.wc_order_based_payment_factory'),

        'core.registration_factory' =>
            new Alias('payoneer_sdk.registration_factory'),

        'core.registration_deserializer' =>
            new Alias('payoneer_sdk.registration_deserializer'),

        'core.security_header_factory' =>
            new Alias('inpsyde_payment_gateway.security_header_factory'),

        'core.wc.countries' =>
            new Alias('wc.countries'),

        'core.state_provider' =>
            new Alias('checkout.state_provider'),

        # core.path_resolver
        # =================================================================

        'core.path_resolver.factory' =>
            new Alias('filesystem.prefix_matching_path_resolver.factory'),

        'core.path_resolver' =>
            new Factory([
                'core.path_resolver.factory',
                'core.path_resolver.mappings',
                'core.path_resolver.base_path',
            ], static function (
                PrefixMatchingPathResolverFactoryInterface $factory,
                array $mappings,
                string $basePath
            ): PathResolverInterface {
                /** @var array<string, string> $mappings */
                $product = $factory->createPathResolverFromMappings($mappings, $basePath);

                return $product;
            }),

        'core.path_resolver.mappings' =>
            new Factory([
                'core.module.name',
                'core.module.root_path',
            ], static function (
                string $moduleName,
                string $moduleRootPath
            ): array {
                return [
                    "$moduleName/" => "{$moduleRootPath}/",
                ];
            }),

        'core.path_resolver.base_path' =>
            new Value(''),

        # core.url_resolver
        # =================================================================

        'core.url_resolver.mappings' =>
            new Factory([
                'core.module.root_path',
                'core.module.root_url',
            ], static function (
                string $rootPath,
                UriInterface $rootUrl
            ): array {
                return [
                    "{$rootPath}/" => "{$rootUrl}/",
                ];
            }),

        'core.url_resolver.path_resolver.factory' =>
            new Alias('core.path_resolver.factory'),

        'core.url_resolver.path_resolver.mappings' =>
            new Alias('core.url_resolver.mappings'),

        'core.url_resolver.path_resolver.base_dir' =>
            new Value(''),

        'core.url_resolver.path_resolver' =>
            new Factory([
                'core.url_resolver.path_resolver.factory',
                'core.url_resolver.path_resolver.mappings',
                'core.url_resolver.path_resolver.base_dir',
            ], static function (
                PrefixMatchingPathResolverFactoryInterface $factory,
                array $mappings,
                string $baseDir
            ): PathResolverInterface {
                /** @var array<string, string> $mappings */
                $product = $factory->createPathResolverFromMappings($mappings, $baseDir);

                return $product;
            }),

        'core.url_resolver.uri_factory' =>
            new Alias('core.uri.factory'),

        'core.uri_resolver.default_protocol' =>
            new Value('file'),

        'core.url_resolver' =>
            new Constructor(PathResolverWrappingUrlResolver::class, [
                'core.url_resolver.path_resolver',
                'core.url_resolver.uri_factory',
                'core.uri_resolver.default_protocol',
            ]),

        'core.api_sandbox_url' => static function (): string {
            return 'https://api.sandbox.oscato.com/api';
        },

        'core.api_live_url' => static function (): string {
            return 'https://api.live.oscato.com/api';
        },

        'core.order_under_payment.id' =>
            new Alias('wc.order_under_payment'),

        # core.page_detector
        # =================================================================

        'core.page_detector' =>
            new Factory([
                'core.page_detector.factory',
                'core.http.current_url',
                'core.http.base_path',
            ], static function (
                UrlPageDetectorFactoryInterface $factory,
                UriInterface $currentUrl,
                string $basePath
            ): PageDetectorInterface {
                $product = $factory->createPageDetectorForBaseUrl((string) $currentUrl, $basePath);

                return $product;
            }),

        # core.refund
        # =================================================================

        'core.refund.refund_finder' =>
            new Alias('webhooks.refund_finder'),

        # core.data
        # =================================================================

        'core.data.dictionary_factory' =>
            new Constructor(DictionaryFactory::class, [
            ]),

        'core.data.structure_based_factory' =>
            new Constructor(DataStructureBasedFactory::class, [
                'core.data.dictionary_factory',
            ]),

        ## payoneer_sdk
        # --------------------------

        'payoneer_sdk.request_factory' => static function (): RequestFactoryInterface {
            return new Psr17Factory();
        },

        'payoneer_sdk.response_factory' => static function (
            ContainerInterface $container
        ): ResponseFactoryInterface {
            /** @var ResponseFactoryInterface */
            return $container->get('payoneer_sdk.request_factory');
        },

        'payoneer_sdk.stream_factory' => static function (
            ContainerInterface $container
        ): StreamFactoryInterface {
            /** @var StreamFactoryInterface */
            return $container->get('payoneer_sdk.request_factory');
        },

        'payoneer_sdk.uri_factory' => static function (
            ContainerInterface $container
        ): UriFactoryInterface {
            /** @var UriFactoryInterface */
            return $container->get('payoneer_sdk.request_factory');
        },

        'payoneer_sdk.http_client' => static function (
            ContainerInterface $container
        ): ClientInterface {
            /** @var WP_Http $wpHttp */
            $wpHttp = $container->get('wp.http.wp_http_object');
            /** @var RequestFactoryInterface $requestFactory */
            $requestFactory = $container->get('payoneer_sdk.request_factory');
            /** @var ResponseFactoryInterface $responseFactory */
            $responseFactory = $container->get('payoneer_sdk.response_factory');
            /** @var StreamFactoryInterface $streamFactory */
            $streamFactory = $container->get('payoneer_sdk.stream_factory');

            return new Client(
                $wpHttp,
                $requestFactory,
                $responseFactory,
                $streamFactory,
                []
            );
        },

        'payoneer_sdk.remote_api_url.base_string.sandbox' =>
            new Alias('core.api_sandbox_url'),

        'payoneer_sdk.remote_api_url.base_string.live' =>
            new Alias('core.api_live_url'),

        'payoneer_sdk.fallback_country' =>
            new Alias('core.fallback_country'),

        ## inpsyde_payment_gateway
        # --------------------------

        'inpsyde_payment_gateway.module_url' =>
            new Factory([
                'core.module.root_url',
                'core.local_modules_directory_name',
                'core.uri.factory',
            ], static function (
                UriInterface $rootUrl,
                string $modulesDirName,
                UriFactoryInterface $factory
            ): UriInterface {
                $url = sprintf('%1$s/%2$s/payment-gateway', (string) $rootUrl, $modulesDirName);
                $product = $factory->createUri($url);

                return $product;
            }),

        'inpsyde_payment_gateway.options' =>
            new Factory(
                [
                    'wp.sites.current.options',
                    'inpsyde_payment_gateway.default_options',
                    'inpsyde_payment_gateway.settings_option_key',
                    'core.data.structure_based_factory',
                ],
                /** @psalm-suppress InvalidCatch */
                static function (
                    MutableContainerInterface $siteOptions,
                    array $defaults,
                    string $optionKey,
                    MapFactoryInterface $datastructureBasedFactory
                ): MapInterface {
                    try {
                        $value = $siteOptions->get($optionKey);
                    } catch (ContainerExceptionInterface $exception) {
                        $value = [];
                    }

                    if (!is_array($value)) {
                        throw new UnexpectedValueException(sprintf(
                            'Gateway options for key "%1$s" must be an array',
                            $optionKey
                        ));
                    }
                    /** @var array<string, mixed> $value */
                    $value += $defaults;
                    $product = $datastructureBasedFactory->createContainerFromArray($value);

                    return $product;
                }
            ),

        'inpsyde_payment_gateway.live_merchant_id' =>
            new Value(1),

        'inpsyde_payment_gateway.sandbox_merchant_id' =>
            new Value(2),

        'inpsyde_payment_gateway.default_options' =>
            new Factory([
                'payoneer_sdk.remote_api_url.base_string.live',
                'payoneer_sdk.remote_api_url.base_string.sandbox',
                'inpsyde_payment_gateway.live_merchant_id',
                'inpsyde_payment_gateway.sandbox_merchant_id',
            ], static function (
                string $liveUrl,
                string $sandboxUrl,
                int $liveMerchantId,
                int $sandboxMerchantId
            ): array {
                return [
                    'live_mode' => 'no',
                    'merchant_id' => $liveMerchantId,
                    'base_url' => $liveUrl,
                    'label' => __('live', 'payoneer-checkout'),
                    'sandbox_merchant_id' => $sandboxMerchantId,
                    'sandbox_base_url' => $sandboxUrl,
                    'sandbox_label' => __('sandbox', 'payoneer-checkout'),
                ];
            }),

        'inpsyde_payment_gateway.dummy_callback' => static function (
            ContainerInterface $container
        ): CallbackInterface {
            /** @var CallbackFactoryInterface $callbackFactory */
            $callbackFactory = $container->get('payoneer_sdk.callback_factory');
            $notificationUrl = $container->get('core.webhooks.notification_url');

            $shopUrl = get_permalink(wc_get_page_id('shop'));
            $shopUrl = is_string($shopUrl) ? $shopUrl : get_site_url(get_current_blog_id());

            return $callbackFactory->createCallback($shopUrl, $shopUrl, $shopUrl, (string) $notificationUrl, []);
        },

        'inpsyde_payment_gateway.dummy_payment' => static function (
            ContainerInterface $container
        ): PaymentInterface {
            /** @var PaymentFactoryInterface $paymentFactory */
            $paymentFactory = $container->get('payoneer_sdk.payment_factory');

            $dummyProduct = $container->get('inpsyde_payment_gateway.dummy_product');
            assert($dummyProduct instanceof ProductInterface);

            return $paymentFactory->createPayment(
                'Test payment to validate credentials',
                $dummyProduct->getAmount(),
                $dummyProduct->getTaxAmount(),
                $dummyProduct->getNetAmount(),
                $dummyProduct->getCurrency()
            );
        },

        'inpsyde_payment_gateway.dummy_product' => new Factory([
            'core.product_factory',
        ], static function (
            ProductFactoryInterface $productFactory
        ): ProductInterface {
            return $productFactory->createProduct(
                ProductType::PHYSICAL,
                'test-123',
                'Test product for credentials validation',
                1.0,
                'USD',
                1,
                1.0,
                0.0
            );
        }),

        'inpsyde_payment_gateway.dummy_customer' => static function (
            ContainerInterface $container
        ): CustomerInterface {
            /** @var CustomerFactoryInterface $customerFactory */
            $customerFactory = $container->get('payoneer_sdk.customer_factory');
            /** @var PhoneFactoryInterface $phoneFactory */
            $phoneFactory = $container->get('core.phone_factory');
            /** @var AddressFactoryInterface $addressFactory */
            $addressFactory = $container->get('core.address_factory');
            /** @var NameFactoryInterface $nameFactory */
            $nameFactory = $container->get('core.name_factory');

            // Dummy phone number taken from the example in the Payoneer API docs.
            $dummyPhone = '+1 123 456 7890';
            $mobilePhone = $phoneFactory->createPhone($dummyPhone);
            $country = 'US';
            $city = 'Alpharetta';
            $street = 'North Point Pkwy';
            $postalCode = '30022';
            $name = $nameFactory->createName('John', 'Doe');
            $dummyAddress = $addressFactory->createAddress(
                $country,
                $city,
                $street,
                $postalCode,
                $name
            );
            $addresses = [
                'billing' => $dummyAddress,
                'shipping' => $dummyAddress,
            ];

            return $customerFactory->createCustomer(
                '0',
                ['mobile' => $mobilePhone],
                $addresses,
                'john.doe@example.com',
                'john.doe@example.com'
            );
        },

        'inpsyde_payment_gateway.dummy_style' =>
            new Factory(
                ['payoneer_sdk.style_factory'],
                static function (StyleFactoryInterface $styleFactory): StyleInterface {
                    return $styleFactory->createStyle('en_GB');
                }
            ),

        'inpsyde_payment_gateway.site.title' =>
            new Alias('wp.site.title'),

        'core.store_country' => static function (): string {
            return wc()->countries->get_base_country();
        },

        'core.store_currency' => new Factory(
            [],
            static function (): string {
                return get_woocommerce_currency();
            }
        ),

        'inpsyde_payment_gateway.store_country' => static function (
            ContainerInterface $container
        ): string {
            return (string) $container->get('core.store_country');
        },
        'inpsyde_payment_gateway.charge_command' => static function (
            ContainerInterface $container
        ): CommandInterface {
            /** @var CommandInterface */
            return $container->get('payoneer_sdk.commands.charge');
        },

        'inpsyde_payment_gateway.update_command' =>
            new Alias('payoneer_sdk.commands.update'),

        'inpsyde_payment_gateway.payment_factory' => static function (
            ContainerInterface $container
        ): PaymentFactoryInterface {
            /** @var PaymentFactoryInterface */
            return $container->get('payoneer_sdk.payment_factory');
        },

        'inpsyde_payment_gateway.customer_factory' => static function (
            ContainerInterface $container
        ): CustomerFactoryInterface {
            /** @var CustomerFactoryInterface */
            return $container->get('payoneer_sdk.customer_factory');
        },

        'inpsyde_payment_gateway.phone_factory' =>
            new Alias('core.phone_factory'),

        'inpsyde_payment_gateway.registration_factory' =>
            new Alias('core.registration_factory'),

        'inpsyde_payment_gateway.list_session_field_name' => static function (): string {
            return '_payoneer_list_session';
        },

        'inpsyde_payment_gateway.order_item_types_for_product' =>
            new Alias('core.order_item_types_for_product'),

        'inpsyde_payment_gateway.quantity_normalizer' =>
            new Alias('core.quantity_normalizer'),

        'inpsyde_payment_gateway.fallback_country' =>
            new Alias('core.fallback_country'),

        'inpsyde_payment_gateway.settings_page_url' =>
            new Alias('core.http.settings_url'),

        'inpsyde_payment_gateway.state_provider' =>
            new Alias('core.state_provider'),

        'inpsyde_logger.native_wc_logger' => static function (): WC_Logger_Interface {
            return wc_get_logger();
        },

        'inpsyde_logger.is_debug' =>
            new Alias('core.is_debug'),

        'core.payout_id_field_name' =>
            new Value('_payoneer_payout_id'),

        'core.webhook_received_field_name' =>
            new Value('_payoneer_webhooks_received'),

        'core.token_provider' =>
            new Factory([
                'inpsyde_payment_gateway.merchant_code',
                'inpsyde_payment_gateway.merchant_token',
            ], static function (?string $username, ?string $password): TokenAwareInterface {
                if (is_null($username) || is_null($password)) {
                    return new class () implements TokenAwareInterface
                    {
                        //phpcs:ignore Inpsyde.CodeQuality.ReturnTypeDeclaration.MissingReturn
                        public function getToken(): string
                        {
                            throw new RuntimeException(
                                'Both username and password required to provide token'
                            );
                        }
                    };
                }

                return new BasicTokenProvider($username, $password);
            }),

        'core.token_provider_callback' =>
            new Factory([
                'core.token_provider',
            ], static function (
                TokenAwareInterface $provider
            ): callable {
                return static function () use ($provider): string {
                    return $provider->getToken();
                };
            }),

        'inpsyde_payment_gateway.refund.refund_finder' =>
            new Alias('core.refund.refund_finder'),

        'checkout.order_list_session_field_name' => static function (
            ContainerInterface $container
        ): string {
            /** @var string */
            return $container->get('inpsyde_payment_gateway.list_session_field_name');
        },

        'core.list_serializer' => static function (
            ContainerInterface $container
        ): ListSerializerInterface {
            /** @var ListSerializerInterface */
            return $container->get('payoneer_sdk.list_serializer');
        },

        'core.list_deserializer' => static function (
            ContainerInterface $container
        ): ListDeserializerInterface {
            /** @var ListDeserializerInterface */
            return $container->get('payoneer_sdk.list_deserializer');
        },

        'core.list_url_container_attribute_name' => static function (
            ContainerInterface $container
        ): string {
            /** @var string */
            return $container->get('checkout.list_url_container_attribute_name');
        },

        'core.local_modules_directory_name' => static function (): string {
            return 'modules/inpsyde';
        },

        'core.main_plugin_file' => static function (
            ContainerInterface $container
        ): string {
            /** @var PluginProperties $properties */
            $properties = $container->get(Package::PROPERTIES);

            return sprintf(
                '%1$s/%2$s.php',
                $properties->basePath(),
                $properties->baseName()
            );
        },

        'core.payment_fields_container_id' => static function (
            ContainerInterface $container
        ): string {
            return (string) $container
                ->get('inpsyde_payment_gateway.payment_fields_container_id');
        },

        'core.payoneer.client.factory' =>
            new Constructor(ClientFactory::class, [
                'payoneer_sdk.http_client',
                'payoneer_sdk.request_factory',
                'payoneer_sdk.stream_factory',
            ]),

        'core.payoneer.factory' =>
            new Constructor(PayoneerFactory::class, [
                'payoneer_sdk.list_deserializer',
                'payoneer_sdk.customer_serializer',
                'payoneer_sdk.payment_serializer',
                'payoneer_sdk.callback_serializer',
                'payoneer_sdk.style_serializer',
                'payoneer_sdk.product_serializer',
                'payoneer_sdk.system_serializer',
                'payoneer_sdk.default_request_headers',
                'payoneer_sdk.commands.create',
                'payoneer_sdk.commands.update',
                'payoneer_sdk.commands.charge',
                'payoneer_sdk.commands.payout',
                'payoneer_sdk.integration',
            ]),

        'core.payoneer' =>
            new Factory([
                'core.payoneer.factory',
                'core.payoneer.client.factory',
                'payoneer_sdk.remote_api_url.base',
                'payoneer_sdk.token_provider',
            ], static function (
                PayoneerFactoryInterface $payoneerFactory,
                ClientFactoryInterface $clientFactory,
                UriInterface $baseUrl,
                TokenAwareInterface $tokenProvider
            ): PayoneerInterface {
                $client = $clientFactory->createClientForApi($baseUrl, $tokenProvider);
                $product = $payoneerFactory->createPayoneerForApi($client);

                return $product;
            }),

        'core.customer_factory' => static function (
            ContainerInterface $container
        ): CustomerFactoryInterface {
            /** @var CustomerFactoryInterface */
            return $container->get('payoneer_sdk.customer_factory');
        },

        'core.callback_factory' => static function (
            ContainerInterface $container
        ): CallbackFactoryInterface {
            /** @var CallbackFactoryInterface */
            return $container->get('payoneer_sdk.callback_factory');
        },

        'core.phone_factory' =>
            new Alias('payoneer_sdk.phone_factory'),

        'core.address_factory' =>
            new Alias('payoneer_sdk.address_factory'),

        'core.name_factory' =>
            new Alias('payoneer_sdk.name_factory'),

        'core.product_factory' =>
            new Alias('payoneer_sdk.product_factory'),

        'core.header_factory' =>
            new Alias('payoneer_sdk.header_factory'),

        'core.payment_factory' => static function (
            ContainerInterface $container
        ): PaymentFactoryInterface {
            /** @var PaymentFactoryInterface */
            return $container->get('payoneer_sdk.payment_factory');
        },

        'core.payment_gateway' => static function (
            ContainerInterface $container
        ): WC_Payment_Gateway {
            /** @var WC_Payment_Gateway */
            return $container->get('inpsyde_payment_gateway.gateway');
        },

        'core.wc_ajax_url' =>
            new Alias('wc.ajax_url'),

        'core.wc_product_serializer' =>
            new Alias('inpsyde_payment_gateway.wc_product_serializer'),

        'checkout.payment_gateway' => new Alias('core.payment_gateway'),

        'core.list_url_container_id' => static function (
            ContainerInterface $container
        ): string {
            return (string) $container->get('inpsyde_payment_gateway.list_url_container_id');
        },

        'core.product_deserializer' => static function (
            ContainerInterface $container
        ): ProductDeserializerInterface {
            /** @var ProductDeserializerInterface */
            return $container->get('payoneer_sdk.product_deserializer');
        },

        'core.security_token_generator' =>
            new Alias('checkout.security_token_generator'),

        'inpsyde_payment_gateway.product_deserializer' =>
            new Alias('core.product_deserializer'),

        'inpsyde_payment_gateway.price_include_tax' =>
            new Alias('core.wc.price_include_tax'),

        # core.hasher
        # =================================================================

        'core.hasher.factory.max_buffer_size' =>
            new Value(1 * 1000), // 1MB

        'core.hasher' =>
            new Constructor(NativeHasher::class, [
                'core.hasher.context.factory',
                'core.hasher.factory.max_buffer_size',
            ]),

        'core.hasher.factory.algo' =>
            new Value('sha1'),

        'core.hasher.context.factory' =>
            new Constructor(NativeHashContextFactory::class, [
                'core.hasher.factory.algo',
            ]),

        # core.css_template
        # =================================================================

        'core.css_template.token_start' =>
            new Value('/*{{'),

        'core.css_template.token_end' =>
            new Value('}}*/'),

        'core.css_template.token_default' =>
            new Value('initial'),

        # core.css_stream_template
        # =================================================================

        'core.css_stream_template.string_stream.factory' =>
            new Alias('core.string_stream.factory'),

        'core.css_stream_template.token_start' =>
            new Alias('core.css_template.token_start'),

        'core.css_stream_template.token_end' =>
            new Alias('core.css_template.token_end'),

        'core.css_stream_template.token_default' =>
            new Alias('core.css_template.token_default'),

        'core.css_stream_template.factory' =>
           new Alias('core.stream_template.factory'),

        'core.stream_template.factory' =>
            new Constructor(StreamingPlaceholderTemplateFactory::class, [
                //todo: alias to more generic services
                'core.css_stream_template.token_start',
                'core.css_stream_template.token_end',
                'core.css_stream_template.token_default',
                'core.css_stream_template.string_stream.factory',
            ]),

        # core.css_path_template
        # =================================================================

        'core.css_path_template.factory.file_stream_factory' =>
            new Alias('core.file_stream.factory'),

        'core.css_path_template.factory.stream_template_factory' =>
            new Alias('core.css_stream_template.factory'),

        'core.css_path_template.factory' =>
            new Constructor(PathTokenTemplateFactory::class, [
                'core.css_path_template.factory.file_stream_factory',
                'core.css_path_template.factory.stream_template_factory',
            ]),

        # core.css_file_processor
        # =================================================================

        'core.css_file_processor.factory' =>
            new Alias('core.asset.template.processor.factory'),

        'core.css_file_processor.path_resolver' =>
            new Alias('core.path_resolver'),

        'core.css_file_processor.url_resolver' =>
            new Alias('core.url_resolver'),

        'core.css_file_processor.file_cache.factory' =>
            new Alias('core.file_cache.factory'),

        'core.css_file_processor.file_cache.base_dir' =>
            new Alias('core.assets.generated.path'),

        'core.css_file_processor.file_cache' =>
            new Factory([
                'core.css_file_processor.file_cache.factory',
                'core.css_file_processor.file_cache.base_dir',
            ], static function (
                FileCacheFactoryInterface $factory,
                string $baseDir
            ): FileCacheInterface {
                $product = $factory->createFileCacheFromBaseDir($baseDir);

                return $product;
            }),

        'core.css_file_processor' =>
            new Factory([
                'core.css_file_processor.factory',
                'core.css_file_processor.path_resolver',
                'core.css_file_processor.url_resolver',
                'core.css_file_processor.file_cache',
            ], static function (
                CachingFileTemplateAssetProcessorFactoryInterface $factory,
                PathResolverInterface $pathResolver,
                UrlResolverInterface $urlResolver,
                FileCacheInterface $fileCache
            ): AssetProcessorInterface {
                $product = $factory->createFileAssetProcessor(
                    $pathResolver,
                    $urlResolver,
                    $fileCache
                );

                return $product;
            }),
        # core.webhooks
        # =================================================================

        'core.webhooks.notification_url' =>
            new Factory(
                [
                    'webhooks.namespace',
                    'webhooks.rest_route',
                    'core.uri.factory',
                ],
                static function (
                    string $restNamespace,
                    string $restRoute,
                    UriFactoryInterface $uriFactory
                ): UriInterface {
                    $blogId = get_current_blog_id();
                    $path = $restNamespace . $restRoute;
                    $restUrl = get_rest_url($blogId, $path);

                    return $uriFactory->createUri($restUrl);
                }
            ),

        'core.webhooks.namespace' => new Value('inpsyde/payoneer-checkout'),

        'core.webhooks.route' => new Value('/listener/notifications'),

        'core.webhooks.params.query.order_id' => new Value('wcOrderId'),

        'core.webhooks.security_header_name' =>
            new Alias('webhooks.security_header_name'),

        # core.payment_gateway
        # =================================================================
        'core.payment_gateway.order.charge_id_field_name' =>
            new Value('_payoneer_payment_charge_id'),

        'core.payment_gateway.order.merchant_id_field_name' =>
            new Value('_payoneer_merchant_id'),

        'core.payment_gateway.order.transaction_id_field_name' =>
            new Value('_payoneer_payment_transaction_id'),

        'core.payment_gateway.order.security_header_field_name' =>
            new Value('_payoneer_security_header_value'),

        'core.payment_gateway.not_supported_countries' => new Value(
            [
                'AF', //Afghanistan
                'CU', //Cuba
                'IR', //Iran
                'IQ', //Iraq
                'KP', //North Korea
                'SO', //Somalia
                'SS', //South Sudan
                'SD', //Sudan
                'SY', //Syria
                'YE', //Yemen
            ]
        ),

        'core.payment_gateway.live_transaction_url_template' =>
            new Value('https://apps.live.oscato.com/transactions/detail/%1$s'),

        'core.payment_gateway.transaction_url_template' =>
            new Factory(
                [
                    'inpsyde_payment_gateway.string_stream.factory',
                    'inpsyde_payment_gateway.stream_template.factory',
                ],
                static function (
                    StringStreamFactoryInterface $streamFactory,
                    StreamTemplateFactoryInterface $templateFactory
                ): TemplateInterface {
                    $stream = $streamFactory->createStreamFromString('https:///*{{domain}}*//transactions/detail/%1s');
                    return $templateFactory->createTemplateFromStream($stream);
                }
            ),

        'core.payment_gateway.sandbox_transaction_url_template' =>
            new Value('https://apps.sandbox.oscato.com/transactions/detail/%1$s'),

        'core.payment_gateway.checkout_transaction_url_template' =>
            new Value('https://myaccount.payoneer.com/ma/checkout/transactions'),

        'core.payment_gateway.is_enabled' =>
            new Alias('inpsyde_payment_gateway.is_enabled'),

        # Module wiring
        # =================================================================

        ## cache
        # --------------------------

        'cache.hasher' =>
            new Alias('core.hasher'),

        'cache.string_stream.factory' =>
            new Alias('core.string_stream.factory'),

        'cache.file_stream.factory' =>
            new Alias('core.file_stream.factory'),

        ## assets
        # --------------------------

        'assets.hasher' =>
            new Alias('core.hasher'),

        'assets.file_stream.factory' =>
            new Alias('core.file_stream.factory'),

        'assets.path_template.factory' =>
            new Alias('core.css_path_template.factory'),

        'core.asset.template.processor.factory' =>
            new Alias('assets.caching_file_template.processor.factory'),

        ## checkout
        # --------------------------

        'checkout.asset_processor' =>
            new Alias('core.css_file_processor'),

        'checkout.notification_url' =>
            new Alias('core.webhooks.notification_url'),

        'checkout.order.security_header_field_name' =>
            new Alias('core.payment_gateway.order.security_header_field_name'),

        'checkout.header_factory' =>
            new Alias('payoneer_sdk.header_factory'),

        'checkout.style_factory' =>
            new Alias('core.style_factory'),

        'checkout.payment_gateway_options' =>
            new Alias('inpsyde_payment_gateway.options'),

        'checkout.payoneer' =>
            new Alias('core.payoneer'),

        'checkout.wc_order_based_callback_factory' =>
            new Alias('core.wc_order_based_callback_factory'),

        'checkout.wc_order_based_customer_factory' =>
            new Alias('core.wc_order_based_customer_factory'),

        'checkout.wc_order_based_products_factory' =>
            new Alias('core.wc_order_based_products_factory'),

        'checkout.merchant_division' =>
            new Alias('core.merchant_division'),

        'checkout.payment_factory' =>
            new Alias('core.payment_factory'),

        'checkout.wc_order_based_payment_factory' =>
            new Alias('core.wc_order_based_payment_factory'),

        'checkout.is_debug' =>
            new Alias('core.is_debug'),

        'checkout.customer_registration_id_field_name' =>
            new Alias('core.customer_registration_id_field_name'),

        'checkout.security_header_factory' =>
            new Alias('core.security_header_factory'),

        'checkout.payment_gateway.is_enabled' =>
            new Alias('core.payment_gateway.is_enabled'),

        'checkout.plugin.version_string' =>
            new Alias('core.plugin.version_string'),

       'checkout.list_session_manager.cache_key.salt.update_on_events' =>
            new Factory([
                'inpsyde_payment_gateway.gateway.id',
            ], static function (string $gatewayId): array {
                return [
                    sprintf('woocommerce_update_options_payment_gateways_%1$s', $gatewayId),
                ];
            }),

        'checkout.product_factory' =>
            new Alias('core.product_factory'),

        'checkout.store_currency' =>
            new Alias('core.store_currency'),

        'checkout.price_include_tax' =>
            new Alias('core.wc.price_include_tax'),

        'checkout.wc.countries' =>
            new Alias('core.wc.countries'),

        'checkout.is_frontend_request' =>
            new Alias('wp.is_frontend_request'),

        'checkout.list.hosted_version' =>
            new Alias('core.list.hosted_version'),

        'checkout.order_under_payment' =>
            new Alias('core.order_under_payment'),
        ## webhooks
        # --------------------------

        'webhooks.params.query.order_id' =>
            new Alias('core.webhooks.params.query.order_id'),

        'webhooks.order.charge_id_field_name' =>
            new Alias('core.payment_gateway.order.charge_id_field_name'),

        'webhooks.order.transaction_id_field_name' =>
            new Alias('core.payment_gateway.order.transaction_id_field_name'),

        'webhooks.order.security_header_field_name' =>
            new Alias('core.payment_gateway.order.security_header_field_name'),

        'webhooks.order_refund.payout_id_field_name' =>
            new Alias('core.payout_id_field_name'),

        'webhooks.order.processed_id_field_name' =>
            new Alias('core.webhook_received_field_name'),

        'webhooks.payment_gateway_options' =>
            new Alias('inpsyde_payment_gateway.options'),

        'webhooks.notification_url' =>
            new Alias('core.webhooks.notification_url'),

        'webhooks.customer_registration_id_field_name' =>
            new Alias('core.customer_registration_id_field_name'),

        ## inpsyde_payment_gateway
        # --------------------------

        'inpsyde_payment_gateway.storage' =>
            new Alias('wp.sites.current.options'),

       'inpsyde_payment_gateway.charge_id_field_name' =>
            new Alias('core.payment_gateway.order.charge_id_field_name'),

       'inpsyde_payment_gateway.merchant_id_field_name' =>
            new Alias('core.payment_gateway.order.merchant_id_field_name'),

        'inpsyde_payment_gateway.payout_id_field_name' =>
            new Alias('core.payout_id_field_name'),

       'inpsyde_payment_gateway.transaction_id_field_name' =>
            new Alias('core.payment_gateway.order.transaction_id_field_name'),

        'inpsyde_payment_gateway.order.security_header_field_name' =>
            new Alias('core.payment_gateway.order.security_header_field_name'),

        'inpsyde_payment_gateway.not_supported_countries' =>
            new Alias('core.payment_gateway.not_supported_countries'),

        'inpsyde_payment_gateway.order.live_transactions_url_template' =>
            new Alias('core.payment_gateway.live_transaction_url_template'),

        'inpsyde_payment_gateway.order.sandbox_transactions_url_template' =>
            new Alias('core.payment_gateway.sandbox_transaction_url_template'),

        'inpsyde_payment_gateway.order.checkout_transactions_url_template' =>
            new Alias('core.payment_gateway.checkout_transaction_url_template'),

        'inpsyde_payment_gateway.payoneer' =>
            new Alias('core.payoneer'),

        'inpsyde_payment_gateway.payoneer.client.factory' =>
            new Alias('core.payoneer.client.factory'),

        'inpsyde_payment_gateway.payoneer.factory' =>
            new Alias('core.payoneer.factory'),

        'inpsyde_payment_gateway.uri_factory' =>
            new Alias('payoneer_sdk.uri_factory'),

        'inpsyde_payment_gateway.page_detector' =>
            new Alias('core.page_detector'),

        'inpsyde_payment_gateway.assets.version' =>
            new Alias('core.assets.version'),

        'inpsyde_payment_gateway.string_stream.factory' =>
            new Alias('filesystem.string_stream.factory'),

        'inpsyde_payment_gateway.stream_template.factory' =>
            new Alias('core.stream_template.factory'),

        'payment_gateway.transaction_url_template' =>
            new Alias('core.payment_gateway.transaction_url_template'),

        'inpsyde_payment_gateway.security_token_generator' =>
            new Alias('core.security_token_generator'),

        'inpsyde_payment_gateway.callback_factory' =>
            new Alias('core.callback_factory'),

        'inpsyde_payment_gateway.notification_url' =>
            new Alias('core.webhooks.notification_url'),

        'inpsyde_payment_gateway.list_security_token' =>
            new Alias('checkout.security_token'),

        'inpsyde_payment_gateway.header_factory' =>
            new Alias('core.header_factory'),

        'inpsyde_payment_gateway.address_factory' =>
            new Alias('core.address_factory'),

        'inpsyde_payment_gateway.name_factory' =>
            new Alias('core.name_factory'),

        'inpsyde_payment_gateway.order_finder' =>
            new Alias('webhooks.order_finder'),

        'inpsyde_payment_gateway.product_factory' =>
            new Alias('core.product_factory'),

        'inpsyde_payment_gateway.list_hash_container_id' =>
            new Alias('core.list_hash_container_id'),

        'inpsyde_payment_gateway.checkout_hash_provider' =>
            new Alias('core.checkout_hash_provider'),

        'inpsyde_payment_gateway.customer_registration_id_field_name' =>
            new Alias('core.customer_registration_id_field_name'),

        'inpsyde_payment_gateway.webhooks.security_header_name' =>
            new Alias('core.webhooks.security_header_name'),

        'inpsyde_payment_gateway.plugin.version_string' =>
            new Alias('core.plugin.version_string'),

        'inpsyde_payment_gateway.is_debug' =>
            new Alias('core.is_debug'),

        'inpsyde_payment_gateway.price_decimals' =>
            new Alias('wc.price_decimals'),

        'inpsyde_payment_gateway.wc.countries' =>
            new Alias('core.wc.countries'),

        'inpsyde_payment_gateway.shop_url_string' =>
            new Alias('core.wc_shop_url'),

        'inpsyde_payment_gateway.product_tax_code_provider' =>
            new Alias('core.product_tax_code_provider'),

        ## core.page_detector
        # --------------------------

        'core.page_detector.factory' =>
            new Alias('http.page_detector.factory'),

        ## embedded_payment
        # --------------------------
        'embedded_payment.assets.css.websdk.url' =>
            new Alias('core.assets.css.websdk.url'),

        'embedded_payment.assets.js.websdk.url' =>
            new Alias('core.assets.js.websdk.url'),

        'embedded_payment.assets.widget.css_url' =>
            new Alias('core.assets.css.widget.css'),

        'embedded_payment.gateway.gateway' =>
            new Alias('core.payment_gateway'),

        'embedded_payment.ajax_url' =>
            new Alias('core.wc_ajax_url'),

        'embedded_payment.misconfiguration_detector' =>
            new Alias('core.misconfiguration_detector'),

        ## hosted_payment
        # --------------------------
        'hosted_payment.misconfiguration_detector' =>
            new Alias('core.misconfiguration_detector'),

        ## websdk
        # --------------------------

        'websdk.main_plugin_file' =>
            new Alias('core.main_plugin_file'),

        'websdk.local_modules_directory_name' =>
            new Alias('core.local_modules_directory_name'),

        ## migration
        # --------------------------

        'migration.string_version_factory' =>
            new Alias('core.string_version_factory'),

        'migration.payment_gateway' =>
            new Alias('core.payment_gateway'),

        'migration.embedded_payment.custom_css.default' =>
            new Alias('core.embedded_payment.custom_css.default'),

        ## admin_banner
        # --------------------------

        'admin_banner.main_plugin_file' =>
            new Alias('core.main_plugin_file'),

        'admin_banner.local_modules_directory_name' =>
            new Alias('core.local_modules_directory_name'),

        'admin_banner.configure_url' => new Alias('core.http.settings_url'),

        ## analytics
        # --------------------------

        'analytics.options' =>
            new Alias('core.options'),

        'analytics.analytics_enabled' => new Factory([
            'analytics.options',
        ], static function (MapInterface $options): bool {
            //If not set, consider analytics enabled.
            //Settings weren't saved yet and by default we have analytics on.
            return ! $options->has('analytics_enabled') ||
                   $options->get('analytics_enabled') === 'yes';
        }),

        'analytics.http.wp_http_object' =>
            new Alias('core.http.wp_http_object'),

        'analytics.http.request_factory' =>
            new Alias('core.http.request_factory'),

        'analytics.http.response_factory' =>
            new Alias('core.http.response_factory'),

        'analytics.stream_factory' =>
            new Alias('core.stream_factory'),

        'analytics.user_id' =>
            new Alias('core.user_id'),

        'analytics.main_plugin_file' =>
            new Alias('core.main_plugin_file'),

        'analytics.plugin_version_string' =>
            new Alias('core.plugin.version_string'),

        'analytics.wc_shop_url' =>
            new Alias('core.wc_shop_url'),

        'analytics.is_checkout_pay_page' =>
            new Alias('core.is_checkout_pay_page'),

        'analytics.is_checkout' =>
            new Alias('core.is_checkout'),

        'analytics.store_currency' =>
            new Alias('core.store_currency'),

        'analytics.http.current_url' =>
            new Alias('core.http.current_url'),

        'analytics.admin_url' =>
            new Alias('core.admin_url'),

        'analytics.order_under_payment.id' =>
            new Alias('core.order_under_payment.id'),

        'analytics.wc.session' =>
            new Alias('wc.session'),

        'analytics.is_ajax' =>
            new Alias('core.is_ajax'),

        'analytics.is_live_mode' =>
            new Alias('core.is_live_mode'),

        'analytics.merchant_division' =>
            new Alias('core.merchant_division'),

        'hosted_payment.list_session_manager' =>
            new Alias('core.list_session_manager'),

        'hosted_payment.order_based_update_command_factory' =>
            new Alias('core.order_based_update_command_factory'),

        'hosted_payment.list_session_remover.wc_order' =>
            new Alias('core.list_session_remover.wc_order'),

        'hosted_payment.payment_flow_override_flag.is_set' =>
            new Alias('core.payment_flow_override_flag.is_set'),

        ## list_session
        # --------------------------
        'list_session.selected_payment_flow' =>
            new Alias('core.selected_payment_flow'),

        'list_session.product_tax_code_provider' =>
            new Alias('core.product_tax_code_provider'),

        'list_session.list_session_remover.wc_order' =>
            new Alias('core.list_session_remover.wc_order'),

        'list_session.fallback_country' =>
            new Alias('core.fallback_country'),

        ## mor
        # --------------------------
        'mor.product_tax_code_field_name' =>
            new Alias('core.product_tax_code_field_name'),

        'mor.product_tax_code_format' =>
            new Value('[A-Za-z0-9]{2,20}'),

        'mor.product_tax_code_field_title' =>
            new Value(
                __(
                    'Tax code must contain 2-20 characters. Only latin letters and digits are allowed.',
                    'payoneer-checkout'
                )
            ),

        ## taxes
        # --------------------------
        'taxes.is_checkout' =>
            new Alias('core.is_checkout'),
    ];
};
