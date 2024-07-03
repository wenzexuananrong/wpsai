<?php
/**
 * Plugin Name: Sales Countdown Timer Premium for WooCommerce and WordPress
 * Plugin URI: https://villatheme.com/extensions/sales-countdown-timer/
 * Description: Create a sense of urgency with a countdown to the beginning or end of sales, store launch or other events for higher conversions.
 * Version: 1.1.3
 * Author: VillaTheme
 * Author URI: http://villatheme.com
 * Text Domain: sales-countdown-timer
 * Domain Path: /languages
 * Copyright 2019-2024 VillaTheme.com. All rights reserved.
 * Requires PHP: 7.0
 * Requires at least: 5.0
 * Tested up to: 6.5
 * WC requires at least: 7.0
 * WC tested up to: 8.9
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
define( 'VI_SCT_SALES_COUNTDOWN_TIMER_VERSION', '1.1.3' );
/**
 * Detect plugin. For use on Front End only.
 */

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
define( 'VI_SCT_SALES_COUNTDOWN_TIMER_DIR', plugin_dir_path( __FILE__ ) );
define( 'VI_SCT_SALES_COUNTDOWN_TIMER_INCLUDES', VI_SCT_SALES_COUNTDOWN_TIMER_DIR . "includes" . DIRECTORY_SEPARATOR );

/**
 * Class VI_SCT_SALES_COUNTDOWN_TIMER
 */
class VI_SCT_SALES_COUNTDOWN_TIMER {
	public $plugin_name = 'Sales Countdown Timer Premium for WooCommerce and WordPress';

	public function __construct() {
//		register_activation_hook( __FILE__, array( $this, 'install' ) );
		add_action( 'plugins_loaded', array( $this, 'init' ) );

		//Compatible with High-Performance order storage (COT)
		add_action( 'before_woocommerce_init', array( $this, 'before_woocommerce_init' ) );
	}

	public function init() {
		if ( ! class_exists( 'VillaTheme_Require_Environment' ) ) {
			require_once WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "sctv-sales-countdown-timer/includes/support.php";
		}

		$environment = new VillaTheme_Require_Environment( [
				'plugin_name'     => $this->plugin_name,
				'php_version'     => '7.0',
				'wp_version'      => '5.0',
			]
		);

		if ( $environment->has_error() ) {
			return;
		}

		$init_file = VI_SCT_SALES_COUNTDOWN_TIMER_INCLUDES . "define.php";
		require_once $init_file;
	}

	public function before_woocommerce_init() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
		}
	}

	/**
	 * When active plugin Function will be call
	 */
	public function install() {
		global $wp_version;
		if ( version_compare( $wp_version, "5.0", "<" ) ) {
			deactivate_plugins( basename( __FILE__ ) ); // Deactivate our plugin
			wp_die( "This plugin requires WordPress version 5.0 or higher." );
		}
	}

}

new VI_SCT_SALES_COUNTDOWN_TIMER();