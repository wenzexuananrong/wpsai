<?php

// We need to manually require here coz our autoloader is not executed.
// Remember when uninstalling a plugin means the plugin is inactive, meaning our autoloader is not active.
require_once 'Helpers/Plugin_Constants.php';

use LPFW\Helpers\Plugin_Constants;

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
exit();
}

/**
 * Function that houses the code that cleans up the plugin on un-installation.
 *
 * @since 1.0.0
 */
function lpfw_plugin_cleanup() {

    global $wpdb;

    $constants = Plugin_Constants::get_instance( null );

    // skip if the clean up setting is not enabled.
    if ( 'yes' !== get_option( $constants->OPTION_CLEAN_UP_PLUGIN_OPTIONS, false ) ) {
        return;
    }

    // Delete options.
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'lpfw\_%';" );
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'acfw\_loyalprog\_%';" );

    // Drop point entries table.
    $point_entries_db = $wpdb->prefix . $constants->DB_TABLE_NAME;
    $wpdb->query( "DROP TABLE IF EXISTS {$point_entries_db}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

    // Delete user, product and order metas.
    $wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '%\_loyalprog\_%' OR meta_key LIKE '%\_lpfw\_%'" );
    $wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE '%\_loyalprog\_%' OR meta_key LIKE '%\_lpfw\_%'" );
    $wpdb->query( "DELETE FROM {$wpdb->termmeta} WHERE meta_key LIKE '%\_loyalprog\_%' OR meta_key LIKE '%\_lpfw\_%'" );

    $wc_tables       = $wpdb->get_col( "SHOW TABLES LIKE '{$wpdb->prefix}woocommerce_%'" );
    $order_item_meta = $wpdb->prefix . 'woocommerce_order_itemmeta';

    // Ddelete meta data under the WC order items meta table.
    if ( isset( $wc_tables[ $order_item_meta ] ) ) {
        $wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE '%\_loyalprog\_%' OR meta_key LIKE '%\_lpfw\_%'" );
    }

    // Settings ( Help ).
    delete_option( $constants->OPTION_CLEAN_UP_PLUGIN_OPTIONS );
}

// Execute cleanup function.
if ( function_exists( 'is_multisite' ) && is_multisite() ) {

    global $wpdb;

    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

    foreach ( $blog_ids as $blogid ) {

        switch_to_blog( $blogid );
        lpfw_plugin_cleanup();

    }

    restore_current_blog();

    return;

} else {
    lpfw_plugin_cleanup();
}
