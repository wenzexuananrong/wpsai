<?php
namespace LPFW\Models\REST_API;

use ACFWF\Models\Objects\Store_Credit_Entry;
use LPFW\Abstracts\Abstract_Main_Plugin_Class;
use LPFW\Helpers\Helper_Functions;
use LPFW\Helpers\Plugin_Constants;
use LPFW\Interfaces\Model_Interface;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Model that houses the Settings module logic.
 * Public Model.
 *
 * @since 1.0
 */
class API_My_Points implements Model_Interface {
    /*
    |--------------------------------------------------------------------------
    | Class Properties
    |--------------------------------------------------------------------------
     */

    /**
     * Property that holds the single main instance of URL_Coupon.
     *
     * @since 1.0
     * @access private
     * @var API_My_Points
     */
    private static $_instance;

    /**
     * Model that houses all the plugin constants.
     *
     * @since 1.0
     * @access private
     * @var Plugin_Constants
     */
    private $_constants;

    /**
     * Property that houses all the helper functions of the plugin.
     *
     * @since 1.0
     * @access private
     * @var Helper_Functions
     */
    private $_helper_functions;

    /**
     * Property that houses the ACFW_Settings instance.
     *
     * @since 1.0
     * @access private
     * @var ACFW_Settings
     */
    private $_acfw_settings;

    /**
     * Custom REST API base.
     *
     * @since 1.0
     * @access private
     * @var string
     */
    private $_base = 'mypoints';

    /**
     * Property that holds all settings sections.
     *
     * @since 1.0
     * @access private
     * @var array
     */
    private $_settings_sections;

    /**
     * Property that holds all settings sections options.
     *
     * @since 1.0
     * @access private
     * @var array
     */
    private $_sections_options;

    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
     */

    /**
     * Class constructor.
     *
     * @since 1.0
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
     * @since 1.0
     * @access public
     *
     * @param Abstract_Main_Plugin_Class $main_plugin      Main plugin object.
     * @param Plugin_Constants           $constants        Plugin constants object.
     * @param Helper_Functions           $helper_functions Helper functions object.
     * @return API_My_Points
     */
    public static function get_instance( Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants, Helper_Functions $helper_functions ) {
        if ( ! self::$_instance instanceof self ) {
            self::$_instance = new self( $main_plugin, $constants, $helper_functions );
        }

        return self::$_instance;
    }

    /*
    |--------------------------------------------------------------------------
    | Routes.
    |--------------------------------------------------------------------------
     */

    /**
     * Register settings API routes.
     *
     * @since 1.0
     * @access public
     */
    public function register_routes() {
        \register_rest_route(
            $this->_constants->REST_API_NAMESPACE,
            '/' . $this->_base,
            array(
                array(
                    'methods'             => \WP_REST_Server::READABLE,
                    'permission_callback' => array( $this, 'get_user_permissions_check' ),
                    'callback'            => array( $this, 'get_current_user_points_balance' ),
                ),
            )
        );

        \register_rest_route(
            $this->_constants->REST_API_NAMESPACE,
            '/' . $this->_base . '/coupons',
            array(
                array(
                    'methods'             => \WP_REST_Server::READABLE,
                    'permission_callback' => array( $this, 'get_user_permissions_check' ),
                    'callback'            => array( $this, 'get_current_user_redeemable_coupons' ),
                ),
            )
        );

        \register_rest_route(
            $this->_constants->REST_API_NAMESPACE,
            '/' . $this->_base . '/history',
            array(
                array(
                    'methods'             => \WP_REST_Server::READABLE,
                    'permission_callback' => array( $this, 'get_user_permissions_check' ),
                    'callback'            => array( $this, 'get_current_user_points_history' ),
                ),
            )
        );

        \register_rest_route(
            $this->_constants->REST_API_NAMESPACE,
            '/' . $this->_base . '/redeem',
            array(
                array(
                    'methods'             => \WP_REST_Server::CREATABLE,
                    'permission_callback' => array( $this, 'get_user_permissions_check' ),
                    'callback'            => array( $this, 'redeem_coupon_for_current_user' ),
                ),
            )
        );

        do_action( 'acfw_after_register_routes' );
    }

    /*
    |--------------------------------------------------------------------------
    | Permissions.
    |--------------------------------------------------------------------------
     */

    /**
     * Checks if a given request has access to read list of settings options.
     *
     * @since 1.0
     * @access public
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
     */
    public function get_user_permissions_check( $request ) {
        if ( ! is_user_logged_in() || ! $this->_helper_functions->validate_user_roles() ) {
            return new \WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed access to this endpoint.', 'loyalty-program-for-woocommerce' ), array( 'status' => \rest_authorization_required_code() ) );
        }

        return apply_filters( 'lpfw_get_my_points_user_permissions_check', true, $request );
    }

    /*
    |--------------------------------------------------------------------------
    | Getter methods.
    |--------------------------------------------------------------------------
     */

    /**
     * Get current user's points balance, worth and expiry.
     *
     * @since 1.0
     * @access public
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_current_user_points_balance( $request ) {
        $response = \rest_ensure_response( \LPFW()->Calculate->get_user_points_balance_data() );

        return apply_filters( 'lpfw_current_user_points_balance', $response );
    }

    /**
     * Get current user's reedemable coupons.
     *
     * @since 1.0
     * @access public
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_current_user_redeemable_coupons( $request ) {
        $page         = absint( $request->get_param( 'page' ) );
        $user_coupons = \LPFW()->User_Points->get_user_redeemed_coupons( get_current_user_id(), $page );
        $response     = \rest_ensure_response( array_map( array( $this, 'prepare_user_coupon' ), $user_coupons ) );

        if ( ! $page || 1 === $page ) {
            $response->header( 'X-TOTAL', \LPFW()->User_Points->get_user_redeem_coupons_total( get_current_user_id() ) );
        }

        return apply_filters( 'lpfw_current_user_redeemable_coupons', $response );
    }

    /**
     * Get current user's reedemable coupons.
     *
     * @since 1.0
     * @access public
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_current_user_points_history( $request ) {
        $page     = absint( $request->get_param( 'page' ) );
        $history  = \LPFW()->API_Customers->query_customer_points_history( get_current_user_id(), $page );
        $history  = ! empty( $history ) ? array_map( array( $this, 'prepare_history_entry' ), $history ) : $history;
        $response = \rest_ensure_response( $history );

        if ( ! $page || 1 === $page ) {
            $response->header( 'X-TOTAL', \LPFW()->API_Customers->query_total_customer_points_history( get_current_user_id() ) );
        }

        return apply_filters( 'lpfw_current_user_points_history', $response );
    }

    /**
     * Redeem coupon for current user.
     *
     * @since 1.0
     * @since 1.6.2 Add support for setting's datetime format.
     * @access public
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function redeem_coupon_for_current_user( $request ) {
        $points             = intval( $request->get_param( 'points' ) );
        $store_credit_entry = \LPFW()->User_Points->redeem_points_for_user( $points, get_current_user_id() );

        if ( ! $store_credit_entry instanceof Store_Credit_Entry ) {
            return new \WP_Error(
                'allowed_points_invalid',
                __( 'Insufficient or invalid points for redemption.', 'loyalty-program-for-woocommerce' ),
                array(
                    'status' => 400,
                    'data'   => array(
                        'points' => $points,
                    ),
                )
            );
        }

        $response = \rest_ensure_response(
            array(
                'user_coupon' => array(),
                'balance'     => \LPFW()->Calculate->get_user_points_balance_data(),
                'message'     => __( 'Points redeemed successfully!', 'loyalty-program-for-woocommerce' ),
            )
        );

        return apply_filters( 'lpfw_current_user_points_history', $response );
    }

    /*
    |--------------------------------------------------------------------------
    | Utilities
    |--------------------------------------------------------------------------
     */

    /**
     * Prepare user coupon data.
     *
     * @since 1.0
     * @since 1.6.2 Add support for setting's datetime format.
     * @access public
     *
     * @param object $user_coupon Raw user coupon.
     * @return array Prepared user coupon.
     */
    public function prepare_user_coupon( $user_coupon ) {
        $datetime_format = $this->_helper_functions->get_default_datetime_format();
        return array(
            'id'           => absint( $user_coupon->ID ),
            'code'         => $user_coupon->code,
            'amount'       => $this->_helper_functions->api_wc_price( apply_filters( 'acfw_filter_amount', $user_coupon->amount ) ),
            'date_created' => wp_date( $datetime_format, strtotime( $user_coupon->date ) ),
            'date_expire'  => $user_coupon->date_expire ? wp_date( $datetime_format, $user_coupon->date_expire ) : '',
            'points'       => intval( $user_coupon->points ),
        );
    }

    /**
     * Prepare single history entry.
     *
     * @since 1.0
     * @since 1.4 Removed related link and label formatting. Moved to Point_Entry object.
     * @access public
     *
     * @param array $entry Raw history entry.
     * @return array Prepared history entry.
     */
    public function prepare_history_entry( $entry ) {
        // remove unneeded data in frontend.
        unset( $entry['object_id'] );

        return $entry;
    }

    /*
    |--------------------------------------------------------------------------
    | Fulfill implemented interface contracts
    |--------------------------------------------------------------------------
     */

    /**
     * Execute Settings class.
     *
     * @since 1.0
     * @access public
     * @inherit LPFW\Interfaces\Model_Interface
     */
    public function run() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }
}
