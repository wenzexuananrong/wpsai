<?php
namespace LPFW\Models;

use LPFW\Abstracts\Abstract_Main_Plugin_Class;
use LPFW\Helpers\Helper_Functions;
use LPFW\Helpers\Plugin_Constants;
use LPFW\Interfaces\Initiable_Interface;
use LPFW\Interfaces\Activatable_Interface;
use LPFW\Interfaces\Model_Interface;
use LPFW\Models\Objects\Advanced_Coupon;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Model that houses the admin notices logic.
 *
 * @since 1.7.1
 */
class Notices implements Model_Interface, Activatable_Interface {
    /*
    |--------------------------------------------------------------------------
    | Class Properties
    |--------------------------------------------------------------------------
     */

    /**
     * Property that holds the single main instance of URL_Coupon.
     *
     * @since 1.7.1
     * @access private
     * @var Notices
     */
    private static $_instance;

    /**
     * Model that houses all the plugin constants.
     *
     * @since 1.7.1
     * @access private
     * @var Plugin_Constants
     */
    private $_constants;

    /**
     * Property that houses all the helper functions of the plugin.
     *
     * @since 1.7.1
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
     * @since 1.7.1
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
     * @since 1.7.1
     * @access public
     *
     * @param Abstract_Main_Plugin_Class $main_plugin      Main plugin object.
     * @param Plugin_Constants           $constants        Plugin constants object.
     * @param Helper_Functions           $helper_functions Helper functions object.
     * @return Notices
     */
    public static function get_instance( Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants, Helper_Functions $helper_functions ) {
        if ( ! self::$_instance instanceof self ) {
            self::$_instance = new self( $main_plugin, $constants, $helper_functions );
        }

        return self::$_instance;
    }

    /**
     * Register LPFW admin notice option ids.
     *
     * @since 1.7.1
     * @access public
     *
     * @param array $notice_options Notice option ids.
     * @return array Filtered notice option ids.
     */
    public function register_lpfw_admin_notice_options( $notice_options ) {
        $priority_notices = array(
            'lpfw_new_update_notice' => $this->_constants->SHOW_NEW_UPDATE_NOTICE,
        );

        return array_merge( $priority_notices, $notice_options );
    }

    /**
     * Register ACFWP admin notices data.
     *
     * @since 1.7.1
     * @access public
     *
     * @param array|null $data       Notice data.
     * @param string     $notice_key Notice key.
     * @return array|null Filtered notice data.
     */
    public function register_lpfw_admin_notices_data( $data, $notice_key ) {
        switch ( $notice_key ) {
            case 'lpfw_new_update_notice':
                $data = $this->_get_new_update_notice_data();
                break;
        }

        return $data;
    }

    /**
     * Get new update notice data.
     *
     * @since 1.7.1
     * @access private
     *
     * @return array New update notice data.
     */
    private function _get_new_update_notice_data() {
        return array(
            'slug'           => 'lpfw_new_update_notice',
            'id'             => $this->_constants->SHOW_NEW_UPDATE_NOTICE,
            'logo_img'       => $this->_constants->IMAGES_ROOT_URL . '/acfw-logo.png',
            'is_dismissable' => is_admin(), // make notice dismissable on other pages except for the dashboard.
            'type'           => 'warning',
            'heading'        => __( 'IMPORTANT INFORMATION', 'loyalty-program-for-woocommerce' ),
            'content'        => array(
                __( 'The next update of <strong>Loyalty Program</strong> (version 1.8) changes the points redemption from coupons to store credits instead.', 'loyalty-program-for-woocommerce' ),
                __( 'You can learn more about this new changes by reading the blog post linked below.', 'loyalty-program-for-woocommerce' ),
            ),
            'actions'        => array(
                array(
                    'key'         => 'primary',
                    'link'        => 'https://advancedcouponsplugin.com/knowledgebase/loyalty-points-redemption-changes-to-store-credit/?utm_source=lpfw&utm_medium=kb&utm_campaign=nextupdatenotice',
                    'text'        => __( 'View Changes', 'loyalty-program-for-woocommerce' ),
                    'is_external' => true,
                ),
            ),
        );
    }

    /**
     * Always show new plugin update notice on the dashboard page.
     *
     * @since 1.7.1
     * @access public
     *
     * @param string $value New update notice option value.
     * @return string Filtered option value.
     */
    public function always_show_new_update_notice_on_dashboard( $value ) {
        if ( did_action( 'acfw_rest_api_context' ) && 'dismissed' === $value ) {
            return 'yes';
        }

        return $value;
    }

    /*
    |--------------------------------------------------------------------------
    | Fulfill implemented interface contracts
    |--------------------------------------------------------------------------
     */

    /**
     * Execute codes that needs to run plugin activation.
     *
     * @since 1.7.1
     * @access public
     * @implements LPFW\Interfaces\Activatable_Interface
     */
    public function activate() {
        /**
         * Display the new plugin update notice upon updating the plugin to version 3.3.2+
         * Force dismiss the new update notice when the version of the plugin is greater or equal to the set upgrade notice version (3.4)
         */
        if ( version_compare( $this->_constants->VERSION, $this->_constants->DISMISS_NEW_UPDATE_NOTICE_VERSION, '>=' ) ) {
            delete_option( $this->_constants->SHOW_NEW_UPDATE_NOTICE );
        } else {
            update_option( $this->_constants->SHOW_NEW_UPDATE_NOTICE, 'yes' );
        }
    }

    /**
     * Execute Notices class.
     *
     * @since 1.7.1
     * @access public
     * @inherit LPFW\Interfaces\Model_Interface
     */
    public function run() {
        add_filter( 'acfw_admin_notice_option_names', array( $this, 'register_lpfw_admin_notice_options' ) );
        add_filter( 'acfw_get_admin_notice_data', array( $this, 'register_lpfw_admin_notices_data' ), 10, 2 );
        add_filter( 'option_' . $this->_constants->SHOW_NEW_UPDATE_NOTICE, array( $this, 'always_show_new_update_notice_on_dashboard' ) );
    }

}
