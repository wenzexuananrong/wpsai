<?php

namespace LPFW\Objects\Tools;

use LPFW\Abstracts\Abstract_Import_Points_Tool;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Model that houses import tooling for the YITH Points and Rewards plugin.
 *
 * @since 1.8.4
 */
class Import_YITH_WPCR extends Abstract_Import_Points_Tool {

    const PLUGIN_ID   = 'yith-wpcr';
    const PLUGIN_NAME = 'YITH WooCommerce Points and Rewards';

    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Create a new WCPR Import tool object instance.
     *
     * @since 1.8.4
     * @access public
     */
    public function __construct() {
        $this->_data = array(
            'plugin_id'                => self::PLUGIN_ID,
            'imported_points_meta_key' => 'lpfw_imported_points_from_yith_wpcr',
            'plugin_basename'          => 'yith-woocommerce-points-and-rewards-premium/init.php',
        );
    }

    /**
     * Get users with points based on the WCPR database table.
     *
     * @since 1.8.4
     * @access protected
     *
     * @return array List of user IDs.
     */
    protected function _get_users_with_points(): array {
        global $wpdb;
        return $wpdb->get_col( "SELECT DISTINCT user_id FROM {$wpdb->prefix}yith_ywpar_points_log" );
    }

    /**
     * Get the customer's total points for this 3rd party plugin.
     *
     * @since 1.8.4
     * @access protected
     *
     * @param int $user_id Customer ID.
     * @return int Customer's total points.
     */
    protected function _get_customer_points( $user_id ): int {
        global $wpdb;

        return $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(amount) FROM {$wpdb->prefix}yith_ywpar_points_log WHERE user_id=%d",
                $user_id
            )
        );
    }
}
