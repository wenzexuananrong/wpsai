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
 * @since 1.8.3
 */
class Import_Sumo extends Abstract_Import_Points_Tool {

    const PLUGIN_ID   = 'sumo';
    const PLUGIN_NAME = 'SUMO Reward Points';

    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Create a new Sumo Import tool object instance.
     *
     * @since 1.8.3
     * @access public
     */
    public function __construct() {
        $this->_data = array(
            'plugin_id'                => self::PLUGIN_ID,
            'imported_points_meta_key' => 'lpfw_imported_points_from_sumo',
            'plugin_basename'          => 'rewardsystem/rewardsystem.php',
        );
    }

    /**
     * Check if the third party plugin is active.
     *
     * @since 1.8.3
     * @access public
     */
    public function is_plugin_active() {
        return parent::is_plugin_active() && class_exists( '\RS_Points_Data' );
    }

    /**
     * Get users with points based on the Sumo Reward Points database table.
     *
     * @since 1.8.3
     * @access protected
     *
     * @return array List of user IDs.
     */
    protected function _get_users_with_points(): array {
        global $wpdb;
        return $wpdb->get_col( "SELECT DISTINCT userid FROM {$wpdb->prefix}rsrecordpoints" );
    }

    /**
     * Get the customer's total points for this 3rd party plugin.
     *
     * @since 1.8.3
     * @access protected
     *
     * @param int $user_id Customer ID.
     * @return int Customer's total points.
     */
    protected function _get_customer_points( $user_id ): int {
        $points_data = new \RS_Points_Data( $user_id );
        return $points_data->total_available_points();
    }
}
