<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
define( 'VI_WOOCOMMERCE_COUPON_BOX_DIR', WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "woocommerce-coupon-box" . DIRECTORY_SEPARATOR );
define( 'VI_WOOCOMMERCE_COUPON_BOX_ADMIN', VI_WOOCOMMERCE_COUPON_BOX_DIR . "admin" . DIRECTORY_SEPARATOR );
define( 'VI_WOOCOMMERCE_COUPON_BOX_FRONTEND', VI_WOOCOMMERCE_COUPON_BOX_DIR . "frontend" . DIRECTORY_SEPARATOR );
define( 'VI_WOOCOMMERCE_COUPON_BOX_LANGUAGES', VI_WOOCOMMERCE_COUPON_BOX_DIR . "languages" . DIRECTORY_SEPARATOR );
define( 'VI_WOOCOMMERCE_COUPON_BOX_INCLUDES', VI_WOOCOMMERCE_COUPON_BOX_DIR . "includes" . DIRECTORY_SEPARATOR );
define( 'VI_WOOCOMMERCE_COUPON_BOX_TEMPLATES', VI_WOOCOMMERCE_COUPON_BOX_DIR . "templates" . DIRECTORY_SEPARATOR );
define( 'VI_WOOCOMMERCE_COUPON_BOX_WIDGET', VI_WOOCOMMERCE_COUPON_BOX_DIR . "widget" . DIRECTORY_SEPARATOR );
define( 'VI_WOOCOMMERCE_COUPON_BOX_3RD', VI_WOOCOMMERCE_COUPON_BOX_INCLUDES . "3rd" . DIRECTORY_SEPARATOR );
$plugin_url = plugins_url( 'woocommerce-coupon-box' );
//$plugin_url = plugins_url( '', __FILE__ );
$plugin_url = str_replace( '/includes', '', $plugin_url );
define( 'VI_WOOCOMMERCE_COUPON_BOX_CSS', $plugin_url . "/css/" );
define( 'VI_WOOCOMMERCE_COUPON_BOX_CSS_DIR', VI_WOOCOMMERCE_COUPON_BOX_DIR . "css" . DIRECTORY_SEPARATOR );
define( 'VI_WOOCOMMERCE_COUPON_BOX_JS', $plugin_url . "/js/" );
define( 'VI_WOOCOMMERCE_COUPON_BOX_JS_DIR', VI_WOOCOMMERCE_COUPON_BOX_DIR . "js" . DIRECTORY_SEPARATOR );
define( 'VI_WOOCOMMERCE_COUPON_BOX_IMAGES', $plugin_url . "/images/" );

/*Include functions file*/
if ( is_file( VI_WOOCOMMERCE_COUPON_BOX_INCLUDES . "functions.php" ) ) {
	require_once VI_WOOCOMMERCE_COUPON_BOX_INCLUDES . "functions.php";
}
if ( is_file( VI_WOOCOMMERCE_COUPON_BOX_INCLUDES . "update.php" ) ) {
	require_once VI_WOOCOMMERCE_COUPON_BOX_INCLUDES . "update.php";
}
if ( is_file( VI_WOOCOMMERCE_COUPON_BOX_INCLUDES . "check_update.php" ) ) {
	require_once VI_WOOCOMMERCE_COUPON_BOX_INCLUDES . "check_update.php";
}
if ( is_file( VI_WOOCOMMERCE_COUPON_BOX_INCLUDES . "data.php" ) ) {
	require_once VI_WOOCOMMERCE_COUPON_BOX_INCLUDES . "data.php";
}
if ( is_file( VI_WOOCOMMERCE_COUPON_BOX_INCLUDES . "support.php" ) ) {
	require_once VI_WOOCOMMERCE_COUPON_BOX_INCLUDES . "support.php";
}

if ( is_file( VI_WOOCOMMERCE_COUPON_BOX_INCLUDES . "custom-controls.php" ) ) {
	require_once VI_WOOCOMMERCE_COUPON_BOX_INCLUDES . "custom-controls.php";
}
if ( is_file( VI_WOOCOMMERCE_COUPON_BOX_3RD . "elementor/elementor.php" ) ) {
	require_once VI_WOOCOMMERCE_COUPON_BOX_3RD . "elementor/elementor.php";
}
vi_include_folder( VI_WOOCOMMERCE_COUPON_BOX_ADMIN, 'VI_WOOCOMMERCE_COUPON_BOX_Admin_' );
vi_include_folder( VI_WOOCOMMERCE_COUPON_BOX_FRONTEND, 'VI_WOOCOMMERCE_COUPON_BOX_Frontend_' );
vi_include_folder( VI_WOOCOMMERCE_COUPON_BOX_3RD.'woocommerce-email-template-customizer'. DIRECTORY_SEPARATOR, 'VI_WOOCOMMERCE_COUPON_BOX_3RD_' );
