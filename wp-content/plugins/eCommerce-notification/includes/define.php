<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
define( 'ECOMMERCE_NOTIFICATION_DIR', WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "ecommerce-notification" . DIRECTORY_SEPARATOR );
define( 'ECOMMERCE_NOTIFICATION_ADMIN', ECOMMERCE_NOTIFICATION_DIR . "admin" . DIRECTORY_SEPARATOR );
define( 'ECOMMERCE_NOTIFICATION_FRONTEND', ECOMMERCE_NOTIFICATION_DIR . "frontend" . DIRECTORY_SEPARATOR );
define( 'ECOMMERCE_NOTIFICATION_LANGUAGES', ECOMMERCE_NOTIFICATION_DIR . "languages" . DIRECTORY_SEPARATOR );
define( 'ECOMMERCE_NOTIFICATION_INCLUDES', ECOMMERCE_NOTIFICATION_DIR . "includes" . DIRECTORY_SEPARATOR );
define( 'ECOMMERCE_NOTIFICATION_TEMPLATES', ECOMMERCE_NOTIFICATION_DIR . "templates" . DIRECTORY_SEPARATOR );
define( 'ECOMMERCE_NOTIFICATION_CACHE', WP_CONTENT_DIR . "/cache/ecommerce-notification/" );
//$plugin_url = plugins_url( 'ecommerce-notification' );
$plugin_url = plugins_url( '', __FILE__ );
$plugin_url = str_replace( '/includes', '', $plugin_url );
define( 'ECOMMERCE_NOTIFICATION_SOUNDS', ECOMMERCE_NOTIFICATION_DIR . "sounds" . DIRECTORY_SEPARATOR );
define( 'ECOMMERCE_NOTIFICATION_SOUNDS_URL', $plugin_url . "/sounds/" );

define( 'ECOMMERCE_NOTIFICATION_CSS', $plugin_url . "/css/" );
define( 'ECOMMERCE_NOTIFICATION_CSS_DIR', ECOMMERCE_NOTIFICATION_DIR . "css" . DIRECTORY_SEPARATOR );
define( 'ECOMMERCE_NOTIFICATION_JS', $plugin_url . "/js/" );
define( 'ECOMMERCE_NOTIFICATION_JS_DIR', ECOMMERCE_NOTIFICATION_DIR . "js" . DIRECTORY_SEPARATOR );
define( 'ECOMMERCE_NOTIFICATION_IMAGES', $plugin_url . "/images/" );
define( 'VI_ECOMMERCE_NOTIFICATION_BACKGROUND_IMAGES', $plugin_url . "/images/background/" );


/*Include functions file*/
if ( is_file( ECOMMERCE_NOTIFICATION_INCLUDES . "check_update.php" ) ) {
	require_once ECOMMERCE_NOTIFICATION_INCLUDES . "check_update.php";
}
if ( is_file( ECOMMERCE_NOTIFICATION_INCLUDES . "update.php" ) ) {
	require_once ECOMMERCE_NOTIFICATION_INCLUDES . "update.php";
}
if ( is_file( ECOMMERCE_NOTIFICATION_INCLUDES . "functions.php" ) ) {
	require_once ECOMMERCE_NOTIFICATION_INCLUDES . "functions.php";
}
if ( is_file( ECOMMERCE_NOTIFICATION_INCLUDES . "support.php" ) ) {
	require_once ECOMMERCE_NOTIFICATION_INCLUDES . "support.php";
}
/*Include functions file*/
if ( is_file( ECOMMERCE_NOTIFICATION_INCLUDES . "mobile_detect.php" ) ) {
	require_once ECOMMERCE_NOTIFICATION_INCLUDES . "mobile_detect.php";
}

vi_include_folder( ECOMMERCE_NOTIFICATION_ADMIN, 'ECOMMERCE_NOTIFICATION_Admin_' );
vi_include_folder( ECOMMERCE_NOTIFICATION_FRONTEND, 'ECOMMERCE_NOTIFICATION_Frontend_' );
