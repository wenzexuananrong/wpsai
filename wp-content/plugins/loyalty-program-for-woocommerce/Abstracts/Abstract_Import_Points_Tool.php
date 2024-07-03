<?php
namespace LPFW\Abstracts;

use LPFW\Objects\Point_Entry;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Model that houses the data model of a loyalty points earned report widget object.
 *
 * @since 1.8.3
 */
abstract class Abstract_Import_Points_Tool {
    /*
    |--------------------------------------------------------------------------
    | Class Properties
    |--------------------------------------------------------------------------
     */

    /**
     * Property that houses the data model of the object.
     *
     * @since 1.8.3
     * @access protected
     * @var array
     */
    protected $_data = array(
        'plugin_id'                => '',
        'imported_points_meta_key' => '',
        'plugin_basename'          => '',
    );

    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Magic property getter.
     * We use this magic method to automatically access data from the _data property so
     * we do not need to create individual methods to expose each of the object's properties.
     *
     * @since 1.8.3
     * @access public
     *
     * @throws \Exception Error message.
     * @param string $prop The name of the data property to access.
     * @return mixed Data property value.
     */
    public function __get( $prop ) {
        if ( array_key_exists( $prop, $this->_data ) ) {
            return $this->_data[ $prop ];
        } else {
            throw new \Exception( 'Trying to access unknown property' );
        }
    }

    /**
     * Create schedules via the action scheduler to trigger importing of customer points.
     *
     * @since 1.8.3
     * @access public
     *
     * @return string[]|\WP_Error Schedule action IDs on success, error object on failure.
     */
    public function create_import_schedules() {

        $user_ids = $this->_get_users_with_points();
        $batches  = array_chunk( $user_ids, 50 );

        // Skip if there are no points data to be imported.
        if ( empty( $user_ids ) || empty( $batches ) ) {
            return new \WP_Error(
                'lpfw_no_points_data',
                __( 'There are no customer points data to be imported.', 'loyalty-program-for-woocommerce' ),
                array( 'status' => 400 )
            );
        }

        $schedules = array();
        foreach ( $batches as $ids ) {
            $schedules[] = \WC()->queue()->schedule_single(
                time(),
                \LPFW()->Plugin_Constants->IMPORT_POINTS_SCHEDULE_HOOK,
                array( $this->plugin_id, $ids, 'import_points_' . $this->plugin_id ),
                'lpfw'
            );
        }

        return $schedules;
    }

    /**
     * Import points for a single customer.
     *
     * @since 1.8.3
     * @access public
     *
     * @param int $user_id Customer ID.
     * @return bool True on success, false on failure.
     */
    public function import_points_for_customer( $user_id ) {
        $total_points    = intval( $this->_get_customer_points( $user_id ) );
        $imported_points = intval( get_user_meta( $user_id, $this->imported_points_meta_key, true ) );

        // Skip if customer's total points is zero or less or points are already imported.
        if ( 1 > $total_points || $imported_points >= $total_points ) {
            return false;
        }

        $point_entry = new Point_Entry();

        $point_entry->set_prop( 'user_id', absint( $user_id ) );
        $point_entry->set_prop( 'type', 'earn' );
        $point_entry->set_prop( 'action', 'imported_points' );
        $point_entry->set_prop( 'points', intval( $total_points - $imported_points ) );

        $check = $point_entry->save();

        if ( is_wp_error( $check ) ) {
            return false;
        }

        update_user_meta( $user_id, $this->imported_points_meta_key, $imported_points + $point_entry->get_prop( 'points', true ) );

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Utilities
    |--------------------------------------------------------------------------
     */

    /**
     * Check if the third party plugin is active.
     *
     * @since 1.8.3
     * @access public
     */
    public function is_plugin_active() {
        return \LPFW()->Helper_Functions->is_plugin_active( $this->plugin_basename );
    }

    /**
     * Deactivate the third aprty plugin.
     *
     * @since 1.8.3
     * @access public
     */
    public function deactivate_plugin() {
        deactivate_plugins( $this->plugin_basename );
    }

    /**
     * Get users with points based on the WCPR database table.
     *
     * @since 1.8.3
     * @access protected
     *
     * @return array List of user IDs.
     */
    abstract protected function _get_users_with_points(): array;

    /**
     * Get the customer's total points for this 3rd party plugin.
     *
     * @since 1.8.3
     * @access protected
     *
     * @param int $user_id Customer ID.
     * @return int Customer's total points.
     */
    abstract protected function _get_customer_points( $user_id ): int;

    /**
     * Get default API_Settings options.
     *
     * @since 1.8.4
     * @access public
     *
     * @return array Default API_Settings options.
     */
    public function get_default_api_setting_options() {
        return array(
            'key'   => static::PLUGIN_ID,
            'label' => static::PLUGIN_NAME,
        );
    }
}
