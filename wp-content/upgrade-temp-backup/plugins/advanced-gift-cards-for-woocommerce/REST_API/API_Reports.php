<?php
namespace AGCFW\REST_API;

use ACFWF\Abstracts\Abstract_Report_Widget;
use ACFWF\Models\Objects\Report_Widgets\Section_Title;
use ACFWF\Models\Objects\Date_Period_Range;
use AGCFW\Abstracts\Abstract_Main_Plugin_Class;
use AGCFW\Helpers\Helper_Functions;
use AGCFW\Helpers\Plugin_Constants;
use AGCFW\Interfaces\Model_Interface;
use AGCFW\Objects\Report_Widgets\Total_Unclaimed_Gift_Cards;
use AGCFW\Objects\Report_Widgets\Total_Unclaimed_Gift_Cards_Count;
use AGCFW\Objects\Report_Widgets\Total_Claimed_Gift_Cards;
use AGCFW\Objects\Report_Widgets\Total_Claimed_Gift_Cards_Count;
use AGCFW\Objects\Report_Widgets\Gift_Cards_Claimed;
use AGCFW\Objects\Report_Widgets\Gift_Cards_Claimed_Count;
use AGCFW\Objects\Report_Widgets\Gift_Cards_Sold;
use AGCFW\Objects\Report_Widgets\Gift_Cards_Sold_Count;
use AGCFW\Objects\Report_Widgets\Top_Gift_Card_Products;
use AGCFW\Objects\Report_Widgets\Recent_Gift_Card_Products;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Model that houses the Advanced Gift Cards REST API logic.
 * Public Model.
 *
 * @since 1.3.4
 */
class API_Reports implements Model_Interface {
    /*
    |--------------------------------------------------------------------------
    | Class Properties
    |--------------------------------------------------------------------------
     */

    /**
     * Property that holds the single main instance of Bootstrap.
     *
     * @since 1.3.4
     * @access private
     * @var Bootstrap
     */
    private static $_instance;

    /**
     * Model that houses all the plugin constants.
     *
     * @since 1.3.4
     * @access private
     * @var Plugin_Constants
     */
    private $_constants;

    /**
     * Property that houses all the helper functions of the plugin.
     *
     * @since 1.3.4
     * @access private
     * @var Helper_Functions
     */
    private $_helper_functions;

    /**
     * Custom REST API base.
     *
     * @since 1.3.4
     * @access private
     * @var string
     */
    private $_base = 'reports/gift-cards';

    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
     */

    /**
     * Class constructor.
     *
     * @since 1.3.4
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
    }

    /**
     * Ensure that only one instance of this class is loaded or can be loaded ( Singleton Pattern ).
     *
     * @since 1.3.4
     * @access public
     *
     * @param Abstract_Main_Plugin_Class $main_plugin      Main plugin object.
     * @param Plugin_Constants           $constants        Plugin constants object.
     * @param Helper_Functions           $helper_functions Helper functions object.
     * @return Bootstrap
     */
    public static function get_instance( Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants, Helper_Functions $helper_functions ) {
        if ( ! self::$_instance instanceof self ) {
            self::$_instance = new self( $main_plugin, $constants, $helper_functions );
        }

        return self::$_instance;
    }

    /*
    |--------------------------------------------------------------------------
    | Register routes
    |--------------------------------------------------------------------------
     */

    /**
     * Register settings API routes.
     *
     * @since 1.3.4
     * @access public
     */
    public function register_routes() {
        \register_rest_route(
            $this->_constants->REST_API_NAMESPACE,
            '/' . $this->_base,
            array(
                array(
                    'methods'             => \WP_REST_Server::READABLE,
                    'permission_callback' => array( $this, 'get_admin_permissions_check' ),
                    'callback'            => array( $this, 'get_total_reports_data' ),
                ),
            ),
        );

        \register_rest_route(
            $this->_constants->REST_API_NAMESPACE,
            '/' . $this->_base . '/activity',
            array(
                array(
                    'methods'             => \WP_REST_Server::READABLE,
                    'permission_callback' => array( $this, 'get_admin_permissions_check' ),
                    'callback'            => array( $this, 'get_gift_cards_activity_data' ),
                ),
            ),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Permissions.
    |--------------------------------------------------------------------------
     */

    /**
     * Checks if a given request has manage woocommerce permission.
     *
     * @since 1.3.4
     * @access public
     *
     * @param \WP_REST_Request $request Full details about the request.
     * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
     */
    public function get_admin_permissions_check( $request ) {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return new \WP_Error(
                'rest_forbidden_context',
                __( 'Sorry, you are not allowed access to this endpoint.', 'advanced-gift-cards-for-woocommerce' ),
                array( 'status' => \rest_authorization_required_code() )
            );
        }

        return apply_filters( 'agcfw_get_store_credits_admin_permissions_check', true, $request );
    }

    /*
    |--------------------------------------------------------------------------
    | REST API callback methods.
    |--------------------------------------------------------------------------
     */

    /**
     * Get total reports data.
     *
     * @since 1.3.4
     * @access public
     *
     * @param \WP_REST_Request $request Full details about the request.
     * @return \WP_REST_Response|\WP_Error
     */
    public function get_total_reports_data( $request ) {
        do_action( 'acfw_rest_api_context', $request );

        $response = \rest_ensure_response( $this->prepare_total_reports_data() );

        return apply_filters( 'agc_get_total_reports_data', $response, $request );
    }

    /**
     * Get gift cards activity data.
     *
     * @since 1.3.4
     * @access public
     *
     * @param \WP_REST_Request $request Full details about the request.
     * @return \WP_REST_Response|\WP_Error
     */
    public function get_gift_cards_activity_data( $request ) {
        do_action( 'acfw_rest_api_context', $request );

        $report_period = new Date_Period_Range( $request->get_param( 'startPeriod' ), $request->get_param( 'endPeriod' ) );
        $response      = \rest_ensure_response( $this->prepare_gift_cards_activity_data( $report_period ) );

        return apply_filters( 'agc_get_gift_cards_activity_data', $response, $report_period );
    }

    /*
    |--------------------------------------------------------------------------
    | Prepare report data
    |--------------------------------------------------------------------------
     */

    /**
     * Prepare total reports data.
     *
     * @since 1.3.4
     * @access public
     *
     * @return array Total reports data.
     */
    public function prepare_total_reports_data() {

        // NOTE: this date values are not really used, but are just passed as a requirement for the report widgets.
        $today_date    = gmdate( 'Y-m-d H:i:s', strtotime( 'today' ) );
        $report_period = new Date_Period_Range( $today_date, $today_date );

        $report_widgets = apply_filters(
            'agc_register_total_report_widgets',
            array(
                new Total_Unclaimed_Gift_Cards_Count( $report_period ),
                new Total_Unclaimed_Gift_Cards( $report_period ),
                new Total_Claimed_Gift_Cards_Count( $report_period ),
                new Total_Claimed_Gift_Cards( $report_period ),
            )
        );

        $data = array();
        foreach ( $report_widgets as $widget ) {
            if ( $widget->is_valid() ) {
                $data[] = $this->_get_data_from_report_widget( $widget );
            }
        }

        return $data;
    }

    /**
     * Prepare gift cards activity data.
     *
     * @since 1.3.4
     * @access public
     *
     * @param Date_Period_Range $report_period Report period.
     * @return array Gift cards activity data.
     */
    public function prepare_gift_cards_activity_data( Date_Period_Range $report_period ) {
        $report_period->use_utc_timezone();

        $report_widgets = apply_filters(
            'agc_register_gift_cards_activity_report_widgets',
            array(
                new Gift_Cards_Sold_Count( $report_period ),
                new Gift_Cards_Claimed_Count( $report_period ),
                new Gift_Cards_Sold( $report_period ),
                new Gift_Cards_Claimed( $report_period ),
                new Top_Gift_Card_Products( $report_period ),
                new Recent_Gift_Card_Products( $report_period ),
            )
        );

        $data = array();
        foreach ( $report_widgets as $widget ) {
            if ( $widget->is_valid() ) {
                $data[] = $this->_get_data_from_report_widget( $widget );
            }
        }

        return $data;
    }

    /**
     * Get data from report widget.
     *
     * @since 1.3.4
     * @access private
     *
     * @param Abstract_Report_Widget $widget Report widget.
     * @return array Report widget data.
     */
    private function _get_data_from_report_widget( Abstract_Report_Widget $widget ) {

        if ( $widget instanceof Section_Title ) {
            return $widget->get_data();
        }

        return $widget->get_api_response();
    }


    /*
    |--------------------------------------------------------------------------
    | Fulfill implemented interface contracts
    |--------------------------------------------------------------------------
     */

    /**
     * Execute Settings class.
     *
     * @since 1.3.4
     * @access public
     * @inherit AGCFW\Interfaces\Model_Interface
     */
    public function run() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }
}
