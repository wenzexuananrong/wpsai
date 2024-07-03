<?php

namespace LPFW\Objects\Tools;

use LPFW\Abstracts\Abstract_Import_Points_Tool;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Model that houses the data model of a loyalty points earned report widget object.
 *
 * @since 1.8.2
 */
class Import_WCPR extends Abstract_Import_Points_Tool {

    const PLUGIN_ID   = 'wcpr';
    const PLUGIN_NAME = 'WooCommerce Points and Rewards';

    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Create a new WCPR Import tool object instance.
     *
     * @since 1.8.2
     * @access public
     */
    public function __construct() {
        $this->_data = array(
            'plugin_id'                => self::PLUGIN_ID,
            'imported_points_meta_key' => 'lpfw_imported_points_from_wcpr',
            'plugin_basename'          => 'woocommerce-points-and-rewards/woocommerce-points-and-rewards.php',
        );
    }

    /**
     * Get users with points based on the WCPR database table.
     *
     * @since 1.8.2
     * @access protected
     *
     * @return array List of user IDs.
     */
    protected function _get_users_with_points(): array {
        global $wpdb;
        return $wpdb->get_col( "SELECT DISTINCT user_id FROM {$wpdb->prefix}wc_points_rewards_user_points" );
    }

    /**
     * Get the customer's total points for this 3rd party plugin.
     *
     * @since 1.8.2
     * @access protected
     *
     * @param int $user_id Customer ID.
     * @return int Customer's total points.
     */
    protected function _get_customer_points( $user_id ): int {
        global $wpdb;

        return $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(points_balance) FROM {$wpdb->prefix}wc_points_rewards_user_points WHERE user_id=%d",
                $user_id
            )
        );
    }
}
