<?php

namespace AGCFW\Models;

use ACFWF\Models\Objects\Date_Period_Range;
use AGCFW\Abstracts\Abstract_Main_Plugin_Class;
use AGCFW\Helpers\Helper_Functions;
use AGCFW\Helpers\Plugin_Constants;
use AGCFW\Interfaces\Model_Interface;
use AGCFW\Interfaces\Deactivatable_Interface;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Model that houses the logic of the Calculate module.
 *
 * @since 1.1.1
 */
class Calculate implements Model_Interface, Deactivatable_Interface {
    /*
    |--------------------------------------------------------------------------
    | Class Properties
    |--------------------------------------------------------------------------
     */

    /**
     * Property that holds the single main instance of URL_Coupon.
     *
     * @since 1.1.1
     * @access private
     * @var Calculate
     */
    private static $_instance;

    /**
     * Model that houses all the plugin constants.
     *
     * @since 1.1.1
     * @access private
     * @var Plugin_Constants
     */
    private $_constants;

    /**
     * Property that houses all the helper functions of the plugin.
     *
     * @since 1.1.1
     * @access private
     * @var Helper_Functions
     */
    private $_helper_functions;

    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
     */

    /**
     * Class constructor.
     *
     * @since 1.1.1
     * @access public
     *
     * @param Abstract_Main_Plugin_Class $main_plugin      Main plugin object.
     * @param Plugin_Constants           $constants        Plugin constants object.
     * @param Helper_Functions           $helper_functions Helper functions object.
     */
    public function __construct( Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants, Helper_Functions $helper_functions ) {
        $this->_constants        = $constants;
        $this->_helper_functions = $helper_functions;

        $main_plugin->add_to_all_plugin_models( $this );
        $main_plugin->add_to_public_models( $this );
    }

    /**
     * Ensure that only one instance of this class is loaded or can be loaded ( Singleton Pattern ).
     *
     * @since 1.1.1
     * @access public
     *
     * @param Abstract_Main_Plugin_Class $main_plugin      Main plugin object.
     * @param Plugin_Constants           $constants        Plugin constants object.
     * @param Helper_Functions           $helper_functions Helper functions object.
     * @return Calculate
     */
    public static function get_instance( Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants, Helper_Functions $helper_functions ) {
        if ( ! self::$_instance instanceof self ) {
            self::$_instance = new self( $main_plugin, $constants, $helper_functions );
        }

        return self::$_instance;
    }

    /**
     * Calculate statistics for gift cards sold and claimed within a given date period range data.
     *
     * @since 1.1.1
     * @access public
     *
     * @param Date_Period_Range $report_period Date period range object (from ACFWF).
     * @return array Calculated period statistics.
     */
    public function calculate_gift_cards_period_statistics( Date_Period_Range $report_period ) {
        $report_period->use_utc_timezone();

        $cache_key   = $report_period->generate_period_cache_key( 'agcfw_gift_cards_stats::%s::%s' );
        $cached_data = get_transient( $cache_key );

        // return cached data if already present in object cache.
        if ( is_array( $cached_data ) && isset( $cached_data['sold_in_period'] ) && isset( $cached_data['claimed_in_period'] ) ) {
            $report_period->use_site_timezone(); // reset timezone back to site timezone.
            return $cached_data;
        }

        $period_params = array(
            'start_period' => $report_period->start_period->format( $this->_constants->DB_DATE_FORMAT ),
            'end_period'   => $report_period->end_period->format( $this->_constants->DB_DATE_FORMAT ),
        );

        $data = array(
            'sold_in_period_count'    => $this->_get_gift_card_values_sum(
                array_merge(
                    array(
                        'query_type' => 'count',
                    ),
                    $period_params
                )
            ),
            'claimed_in_period_count' => $this->_get_gift_card_values_sum(
                array_merge(
                    array(
                        'query_type' => 'count',
                        'status'     => 'used',
                    ),
                    $period_params
                )
            ),
            'sold_in_period'          => $this->_get_gift_card_values_sum( $period_params ),
            'claimed_in_period'       => $this->_get_gift_card_values_sum( array_merge( array( 'status' => 'used' ), $period_params ) ),
        );

        // save data to the cache for a maximum of one day.
        set_transient( $cache_key, $data, DAY_IN_SECONDS );

        // reset timezone back to site timezone.
        $report_period->use_site_timezone();

        return $data;
    }

    /**
     * Calculate the total statistics data for gift cards sold and claimed.
     *
     * @since 1.3.4
     * @access public
     *
     * @return array Calculated total statistics.
     */
    public function calculate_gift_cards_total_statistics() {

        $cache_key   = 'agcfw_gift_cards_total_stats';
        $cached_data = get_transient( $cache_key );

        // return cached data if already present in object cache.
        if ( is_array( $cached_data ) && isset( $cached_data['sold_in_period'] ) && isset( $cached_data['claimed_in_period'] ) ) {
            return $cached_data;
        }

        $data = array(
            'total_unclaimed_count' => $this->_get_gift_card_values_sum(
                array(
                    'query_type' => 'count',
                    'status'     => 'pending',
                )
            ),
            'total_claimed_count'   => $this->_get_gift_card_values_sum(
                array(
                    'query_type' => 'count',
                    'status'     => 'used',
                )
            ),
            'total_unclaimed'       => $this->_get_gift_card_values_sum( array( 'status' => 'pending' ) ),
            'total_claimed'         => $this->_get_gift_card_values_sum( array( 'status' => 'used' ) ),
        );

        // save data to the cache for a maximum of one day.
        set_transient( $cache_key, $data, DAY_IN_SECONDS );

        return $data;
    }

    /**
     * Get sum of all gift card values based on the provided parameters.
     *
     * @since 1.1.1
     * @access private
     *
     * @param array $params Query parameters.
     * @return float Sum of queried gift card values.
     */
    private function _get_gift_card_values_sum( $params = array() ) {
        global $wpdb;

        $params = wp_parse_args(
            $params,
            array(
                'query_type'   => 'value',
                'user_id'      => 0,
                'status'       => '',
                'start_period' => '',
                'end_period'   => '',
                'precision'    => \ACFWF()->Store_Credits_Calculate->get_decimal_precision(),
                'decimals'     => wc_get_price_decimals(),
            )
        );
        extract( $params ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

        if ( 'count' === $query_type ) {
            $select_query = 'SELECT COUNT(id)';
        } else {
            $select_query = $wpdb->prepare( 'SELECT SUM(CONVERT(value, DECIMAL(%d, %d)))', $precision, $decimals );
        }

        $user_query   = $user_id ? $wpdb->prepare( 'AND user_id = %d', $user_id ) : '';
        $status_query = $status ? $wpdb->prepare( 'AND status = %s', $status ) : '';
        $period_query = $start_period && $end_period ? $wpdb->prepare( 'AND date_created BETWEEN %s AND %s', $start_period, $end_period ) : '';
        $agc_db       = $wpdb->prefix . $this->_constants->DB_TABLE_NAME;

        // build query.
            $query = "{$select_query}
            FROM {$agc_db}
            WHERE 1
            {$user_query} {$status_query} {$period_query}
        ";

        return (float) $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    }

    /**
     * Delete store credits cached data.
     *
     * @since 1.1.1
     * @access public
     */
    public function delete_gift_cards_cached_data() {
        global $wpdb;
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%agcfw_gift_cards_stats%'" );
    }

    /*
    |--------------------------------------------------------------------------
    | Fulfill implemented interface contracts
    |--------------------------------------------------------------------------
     */

    /**
     * Contract for deactivate.
     *
     * @since 1.1.1
     * @access public
     * @implements AGCFW\Interfaces\Deactivatable_Interface
     */
    public function deactivate() {
        $this->delete_gift_cards_cached_data();
    }

    /**
     * Execute Calculate class.
     *
     * @since 1.1.1
     * @access public
     * @inherit AGCFW\Interfaces\Model_Interface
     */
    public function run() {
        add_action( 'agcfw_gift_cards_total_changed', array( $this, 'delete_gift_cards_cached_data' ) );
    }
}
