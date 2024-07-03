<?php

namespace AGCFW\Abstracts;

use ACFWF\Abstracts\Abstract_Report_Widget;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Gift Cards report widget abstract class.
 *
 * @since 1.3.4
 */
class Abstract_Gift_Cards_Report_Widget extends Abstract_Report_Widget {
    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Query gift cards table purchased quantity and value data for the provided date period range.
     *
     * @since 1.3.4
     * @access protected
     *
     * @return array Coupon table data.
     */
    protected function _query_gift_cards_table_data() {
        global $wpdb;

        $this->report_period->use_utc_timezone();

        $start_period = $this->report_period->start_period->format( 'Y-m-d H:i:s' );
        $end_period   = $this->report_period->end_period->format( 'Y-m-d H:i:s' );
        $cache_key    = sprintf( 'query_gift_cards_table_data::%s::%s', $start_period, $end_period );

        $cached_results = wp_cache_get( $cache_key, 'agc' );

        // return cached data if already present in object cache.
        if ( is_array( $cached_results ) && ! empty( $cached_results ) ) {
            return $cached_results;
        }

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT g.*,p.ID AS product_id,p.post_title AS product_name
                FROM {$wpdb->prefix}acfw_gift_cards AS g 
                INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim ON (oim.order_item_id = g.order_item_id AND oim.meta_key = '_product_id')
                INNER JOIN {$wpdb->posts} AS p ON (p.ID = CONVERT(oim.meta_value, UNSIGNED) AND p.post_type = 'product')
                WHERE CONVERT(date_created, DATETIME) BETWEEN %s AND %s",
                $start_period,
                $end_period
            ),
            ARRAY_A
        );

        $data = array_map(
            function( $r ) {
                return array(
                    'id'           => absint( $r['id'] ),
                    'product_id'   => absint( $r['product_id'] ),
                    'product_name' => esc_html( $r['product_name'] ),
                    'value'        => floatval( $r['value'] ),
                );
            },
            $results
        );

        // save data temporarily to the object cache so other related reports can reuse it.
        // data is set to expire after 30 seconds so it will always be fresh when the page is loaded for installs that has persistent object cache.
        wp_cache_set( $cache_key, $data, 'agc', 30 );

        return $data;
    }

    /*
    |--------------------------------------------------------------------------
    | Utility methods
    |--------------------------------------------------------------------------
    */

    /**
     * Calculate quantity and value per product based on the results of the coupon table data.
     *
     * @since 1.3.4
     * @access protected
     *
     * @param array $results Gift cards table data.
     * @return [array, array] Quantity and value key value pair.
     */
    protected function _calculate_quantity_and_value_per_product( $results ) {
        $quantity = array();
        $value    = array();
        foreach ( $results as $row ) {

            // create product entry for quantities if it doesn't exist yet.
            if ( ! isset( $quantity[ $row['product_id'] ] ) ) {
                $quantity[ $row['product_id'] ] = 0;
            }

            // create product entry for values if it doesn't exist yet.
            if ( ! isset( $value[ $row['product_id'] ] ) ) {
                $value[ $row['product_id'] ] = 0;
            }

            // increment quantity count for product.
            $quantity[ $row['product_id'] ]++;

            $calc_value = wc_add_number_precision( $value[ $row['product_id'] ] ) + wc_add_number_precision( $row['value'] );

            // add value to total for product.
            $value[ $row['product_id'] ] = wc_remove_number_precision( $calc_value );
        }

        return array( $quantity, $value );
    }

    /**
     * Format gift cards table data from raw data.
     *
     * @since 1.3.4
     * @access protected
     */
    protected function _format_report_data() {
        $this->table_data = array_map(
            function( $r ) {
            $d['value'] = \ACFWF()->Helper_Functions->api_wc_price( $r['value'] );
            return $r;
            },
            $this->raw_data
        );
    }
}
