<?php
/**
 * Plugin Name: WooCommerce Coupon Box Premium
 * Plugin URI: https://villatheme.com/extensions/woo-coupon-box/
 * Description: The easiest way to share your coupon code and grow your social followers at the same time. With WooCommerce Coupon Box, your customers will see a popup that motivates them to follow your social profiles to get coupon code.
 * Version: 2.1.1
 * Author: VillaTheme
 * Author URI: https://villatheme.com
 * Text Domain: woocommerce-coupon-box
 * Domain Path: /languages
 * Copyright 2018-2023 VillaTheme.com. All rights reserved.
 * Requires at least: 5.0
 * Tested up to: 6.3
 * WC requires at least: 5.0
 * WC tested up to: 8.1
 * Requires PHP: 7.0
 **/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VI_WOOCOMMERCE_COUPON_BOX_VERSION', '2.1.1' );
/**
 * Detect plugin. For use on Front End only.
 */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

class VI_WOO_COUPON_BOX {
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );

		//Compatible with High-Performance order storage (COT)
		add_action( 'before_woocommerce_init', array( $this, 'before_woocommerce_init' ) );

	}

	function init() {
		if ( ! class_exists( 'VillaTheme_Require_Environment' ) ) {
			require_once WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "woocommerce-coupon-box/includes/support.php";
		}

		$environment = new VillaTheme_Require_Environment( [
				'plugin_name'     => 'WooCommerce Coupon Box Premium',
				'php_version'     => '7.0',
				'wp_version'      => '5.0',
				'wc_version'      => '5.0',
				'require_plugins' => [
					[
						'slug' => 'woocommerce',
						'name' => 'WooCommerce',
					],
				]
			]
		);

		if ( $environment->has_error() ) {
			return;
		}

		$init_file = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "woocommerce-coupon-box" . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "define.php";
		require_once $init_file;
	}

	public function before_woocommerce_init() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
}

new VI_WOO_COUPON_BOX;