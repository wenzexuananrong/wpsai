<?php

// We need to manually require here coz our autoloader is not executed
// Remember when uninstalling a plugin means the plugin is inactive, meaning our autoloader is not active.
require_once 'Helpers/Plugin_Constants.php';

use AGCFW\Helpers\Plugin_Constants;

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

/**
 * Function that houses the code that cleans up the plugin on un-installation.
 *
 * @since 1.0.0
 */
function agcfw_plugin_cleanup() {
    $constants = Plugin_Constants::get_instance( null );

    if ( get_option( $constants->OPTION_CLEAN_UP_PLUGIN_OPTIONS, false ) === 'yes' ) {

        // Settings ( Help ).
        delete_option( $constants->OPTION_CLEAN_UP_PLUGIN_OPTIONS );

    }

}

if ( function_exists( 'is_multisite' ) && is_multisite() ) {

    global $wpdb;

    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

    foreach ( $blog_ids as $blogid ) {

        switch_to_blog( $blogid );
        agcfw_plugin_cleanup();

    }

    restore_current_blog();

    return;

} else {
    agcfw_plugin_cleanup();
}
