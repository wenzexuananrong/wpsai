<?php

/**
 * Plugin Name: Payoneer Checkout
 * Description: Payoneer Checkout for WooCommerce
 * Version: 1.6.0
 * Author:      Payoneer
 * Requires at least: 5.4
 * Tested up to: 6.1.1
 * WC requires at least: 5.0
 * WC tested up to: 7.2.1
 * Requires PHP: 7.2
 * Author URI:  https://www.payoneer.com/
 * License:     MPL-2.0
 * Text Domain: payoneer-checkout
 * Domain Path: /languages
 * SHA: 13dd32209fee0337771f1ec550f4fc1e7dc3ba0e
 */

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce;

use Inpsyde\Modularity\Package;

if (is_readable(dirname(__FILE__) . '/vendor/autoload.php')) {
    include_once dirname(__FILE__) . '/vendor/autoload.php';
}

/**
 * Provide the plugin instance.
 *
 * @return Package
 *
 * @link https://github.com/inpsyde/modularity#access-from-external
 */
function plugin(): Package
{
    static $package;

    if (!$package) {
        /** @var callable $bootstrap */
        $bootstrap = require __DIR__ . '/inc/bootstrap.php';
        $onError = require __DIR__ . '/inc/error.php';
        $modules = (require __DIR__ . '/inc/modules.php')();
        $modules = apply_filters('payoneer-checkout.modules_list', $modules);

        $package = $bootstrap(
            __FILE__,
            $onError,
            ...$modules
        );
    }

    /** @var Package $package */
    return $package;
}

add_action('plugins_loaded', static function (): void {
    /**
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    $wpIncPath = implode('', [
        ABSPATH,
        WPINC,
    ]);
    if (! function_exists('get_blog_option')) {
        require_once("$wpIncPath/ms-blogs.php");
    }
    if (! function_exists('wp_insert_site')) {
        require_once("$wpIncPath/ms-site.php");
    }
    if (! class_exists(\WP_Site::class)) {
        require_once("$wpIncPath/class-wp-site.php");
    }
    plugin();
});

register_activation_hook(__FILE__, static function (): void {
    add_option('payoneer-checkout_plugin_activated', 1);
});
