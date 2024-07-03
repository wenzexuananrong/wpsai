<?php
namespace LPFW\Models\REST_API;

use LPFW\Helpers\Plugin_Constants;
use Automattic\WooCommerce\StoreApi\StoreApi;
use Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CartSchema;

/**
 * WooCommerce Extend Store API for Cart Endpoint.
 *
 * @since 1.8.5
 */
class Store_API_Extend_Endpoint {
    /**
     * Stores Rest Extending instance.
     *
     * @since 1.8.5
     * @var ExtendSchema
     */
    private static $extend;

    /**
     * Plugin Identifier.
     *
     * @since 1.8.5
     * @var string
     */
    const IDENTIFIER = 'lpfw_block';

    /**
     * Bootstraps the class and hooks required data.
     *
     * @since 1.8.5
     * @access public
     */
    public static function init() {
        self::$extend = StoreApi::container()->get( ExtendSchema::class );
        self::extend_store();
    }

    /**
     * Registers the actual data into each endpoint.
     * - To see available endpoints to extend please go to : https://github.com/woocommerce/woocommerce-blocks/blob/trunk/docs/third-party-developers/extensibility/rest-api/available-endpoints-to-extend.md
     *
     * @since 1.8.5
     * @access public
     */
    public static function extend_store() {
        // Register into `cart`.
        if ( is_callable( array( self::$extend, 'register_endpoint_data' ) ) ) {
            self::$extend->register_endpoint_data(
                array(
                    'endpoint'      => CartSchema::IDENTIFIER,
                    'namespace'     => self::IDENTIFIER,
                    'data_callback' => array( 'LPFW\Models\REST_API\Store_API_Extend_Endpoint', 'extend_data' ),
                    'schema_type'   => ARRAY_A,
                )
            );
        }
    }

    /**
     * Extend endpoint data.
     * - This data will be available in Redux Data Store `cartData.lpfw_block.extension`.
     * - To learn more you can visit : https://github.com/woocommerce/woocommerce-blocks/blob/trunk/docs/third-party-developers/extensibility/rest-api/extend-rest-api-add-data.md
     *
     * @since 1.8.5
     * @access public
     *
     * @return array $item_data Registered data or empty array if condition is not satisfied.
     */
    public static function extend_data() {
        // Return data.
        $points = self::_extend_data_points();
        return array(
            'loyalty_points' => array(
                'points'  => $points,
                'labels'  => self::_extend_data_labels( $points ),
                'notices' => self::_extend_notices(),
            ),
        );
    }

    /**
     * Extend points data.
     *
     * @since 1.8.6
     * @access private
     *
     * @return array
     */
    private static function _extend_data_points() {
        $points                 = array();
        $points['user_points']  = \LPFW()->Calculate->get_user_total_points( get_current_user_id() );
        $points['min_points']   = (int) \LPFW()->Helper_Functions->get_option( \LPFW()->Plugin_Constants->MINIMUM_POINTS_REDEEM, '0' );
        $points['points_name']  = \LPFW()->Helper_Functions->get_points_name();
        $points['points_worth'] = \LPFW()->Helper_Functions->api_wc_price( \LPFW()->Calculate->calculate_redeem_points_worth( $points['user_points'] ) );
        $points['max_points']   = \LPFW()->Calculate->calculate_allowed_max_points( $points['user_points'], true );

        return $points;
    }

    /**
     * Extend data labels.
     *
     * @since 1.8.6
     * @access private
     *
     * @param array $points Points data.
     * @return array
     */
    private static function _extend_data_labels( $points ) {
        $labels                 = \LPFW()->User_Points->get_loyalty_points_redeem_form_labels();
        $labels['placeholder']  = sprintf( $labels['placeholder'], strtolower( $points['points_name'] ) );
        $labels['balance_text'] = sprintf(
            $labels['balance_text'],
            sprintf( '<strong>%s</strong>', $points['user_points'] ),
            strtolower( $points['points_name'] ),
            sprintf( '<strong>%s</strong>', $points['points_worth'] )
        );
        $labels['instructions'] = sprintf(
            $labels['instructions'],
            sprintf( '<strong>%s</strong>', $points['max_points'] ),
            strtolower( $points['points_name'] ),
        );

        return $labels;
    }

    /**
     * Extend data notices.
     *
     * @since 1.8.6
     * @access private
     *
     * @return array
     */
    private static function _extend_notices() {
        $notices                = array();
        $notices['show_notice'] = \LPFW()->Messages->should_display_points_earn_message();
        $message_template       = \LPFW()->Messages->get_notice_message_template( \LPFW()->Helper_Functions->is_cart_block() ? 'cart' : 'checkout' ); // Get the notice message template based on type cart or block.
        $notices['message']     = \LPFW()->Messages->get_points_earn_message_text( $message_template ); // Get the notice message text.

        return $notices;
    }
}
