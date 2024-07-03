<?php
namespace ACFWP\Models\Third_Party_Integrations\WC;

use ACFWP\Abstracts\Abstract_Main_Plugin_Class;

use ACFWP\Abstracts\Base_Model;
use ACFWP\Interfaces\Model_Interface;

use ACFWP\Models\Objects\Advanced_Coupon;

use ACFWP\Helpers\Plugin_Constants;
use ACFWP\Helpers\Helper_Functions;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Model that houses the logic of extending the coupon system of woocommerce.
 * It houses the logic of handling coupon url.
 * Public Model.
 *
 * @since 2.4.2
 */
class WC_Subscriptions extends Base_Model implements Model_Interface {
    /**
     * Property that holds the URL of the JS files.
     *
     * @since 3.5.8
     * @access private
     * @var string
     */
    private $_js_url;

    /*
    |--------------------------------------------------------------------------
    | Class Properties
    |--------------------------------------------------------------------------
    */

    const E_PRODUCT_SEARCH_ACTION = 'acfwp_add_products_search';

    /**
	 * Subscription coupon types.
	 *
     * @since 2.4.2
     * @access private
	 * @var array
	 */
	private static $recurring_coupons = array(
		'recurring_fee'     => 1,
		'recurring_percent' => 1,
    );

    /**
     * Property that holds the list of subscription product types.
     *
     * @since 2.4.2
     * @access private
     * @var array
     */
    private $_product_types = array(
        'subscription',
        'subscription_variation',
    );

    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Class constructor.
     *
     * @since 2.4.2
     * @access public
     *
     * @param Abstract_Main_Plugin_Class $main_plugin      Main plugin object.
     * @param Plugin_Constants           $constants        Plugin constants object.
     * @param Helper_Functions           $helper_functions Helper functions object.
     */
    public function __construct( Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants, Helper_Functions $helper_functions ) {
        parent::__construct( $main_plugin, $constants, $helper_functions );
        $this->_js_url = $this->_constants->THIRD_PARTY_URL . 'WC/js/';
        $main_plugin->add_to_all_plugin_models( $this );
    }

    /*
    |--------------------------------------------------------------------------
    | WC Subscriptions implementation
    |--------------------------------------------------------------------------
    */

    /**
     * Filter subscription IDs from a list of WC_Product objects.
     *
     * @since 2.4.1
     * @access public
     *
     * @param WC_Product[] $products List of product objects.
     * @return int[] List of subscription ids.
     */
    public function filter_subscription_ids_from_products( $products ) {

        $sub_products = array_reduce(
            $products,
            function ( $c, $p ) {

            if ( in_array( $p->get_type(), $this->_product_types, true ) ) {
                $c[] = $p->get_id();
            }

            return $c;
            },
            array()
        );

        return $sub_products;
    }

    /**
     * Append product type info in products search response data.
     *
     * @since 2.4.2
     * @access public
     *
     * @param array $response Product ID and name pairs.
     * @param array $products Array of WC_Product objects.
     * @param array $params   Raw search parameters.
     * @return array Filtered response data.
     */
    public function append_product_type_in_search_response( $response, $products, $params ) {

        if ( isset( $params['action'] ) && self::E_PRODUCT_SEARCH_ACTION === $params['action'] ) {

            // get all subscription product ids from search response.
            $sub_products = $this->filter_subscription_ids_from_products( $products );

            @header( 'X-Subscription-IDs: ' . implode( ',', $sub_products ) ); // phpcs:ignore
        }

        return $response;
    }

    /**
     * Populate subscription IDs to the Add Products panel data attributes.
     *
     * @since 2.4.2
     * @access public
     *
     * @param array $atts Panel attributes.
     * @param array $add_products Add products data.
     */
    public function populate_subscription_ids_panel_data_atts( $atts, $add_products ) {

        $products = array_map(
            function ( $a ) {
            return wc_get_product( $a['product_id'] );
            },
            $add_products
        );

        $atts['subscription_ids']            = $this->filter_subscription_ids_from_products( $products );
        $atts['subscription_discount_error'] = __( 'Custom discounts for subscription products are not supported for this feature.', 'advanced-coupons-for-woocommerce' );

        return $atts;
    }

    /**
     * Loop through each Add Products item and ensure subscription products have "nodiscount" discount type.
     *
     * @since 2.4.2
     * @access public
     *
     * @param array $add_products Add products data.
     * @return array Filtered add products data.
     */
    public function set_subscription_discount_type_in_add_products( $add_products ) {

        $add_products = array_map(
            function ( $a ) {

            $product = wc_get_product( $a['product_id'] );

            if ( in_array( $product->get_type(), $this->_product_types, true ) ) {
                $a['discount_type']  = 'nodiscount';
                $a['discount_value'] = 0;
            }

            return $a;
            },
            $add_products
        );

        return $add_products;
    }

    /**
     * Uset the remove coupon method for virtual coupons during WC Subscriptions coupon validation process which is run
     * when the `woocommerce_before_calculate_totals` action hook is called.
     *
     * @since 3.5.3
     * @access public
     */
    public function unset_virtual_coupon_remove_coupon_method() {
        remove_action( 'woocommerce_removed_coupon', array( \ACFWP()->Virtual_Coupon_Frontend, 'remove_unused_virtual_coupons_from_session' ) );
    }

    /**
     * Set the remove coupon method for virtual coupons on a later priority of the `woocommerce_before_calculate_totals` action hook.
     *
     * @since 3.5.3
     * @access public
     */
    public function set_virtual_coupon_remove_coupon_method() {
        add_action( 'woocommerce_removed_coupon', array( \ACFWP()->Virtual_Coupon_Frontend, 'remove_unused_virtual_coupons_from_session' ) );
    }

    /*
    |--------------------------------------------------------------------------
    | Fulfill implemented interface contracts
    |--------------------------------------------------------------------------
    */

    /**
     * Execute WC_Subscriptions class.
     *
     * @since 2.4.2
     * @access public
     * @inherit ACFWP\Interfaces\Model_Interface
     */
    public function run() {

        if ( ! $this->_helper_functions->is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
            return;
        }

        add_action( 'acfw_json_search_products_response', array( $this, 'append_product_type_in_search_response' ), 10, 3 );
        add_action( 'acfwp_add_products_panel_data_atts', array( $this, 'populate_subscription_ids_panel_data_atts' ), 10, 2 );
        add_filter( 'acfwp_sanitize_add_products_data', array( $this, 'set_subscription_discount_type_in_add_products' ) );
        add_filter( 'acfwp_coupon_get_add_products_data', array( $this, 'set_subscription_discount_type_in_add_products' ) );

        add_action( 'woocommerce_before_calculate_totals', array( $this, 'unset_virtual_coupon_remove_coupon_method' ), 1 );
        add_action( 'woocommerce_before_calculate_totals', array( $this, 'set_virtual_coupon_remove_coupon_method' ), 11 );
    }
}
