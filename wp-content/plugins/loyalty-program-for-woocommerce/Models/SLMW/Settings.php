<?php
namespace LPFW\Models\SLMW;

use LPFW\Abstracts\Abstract_Main_Plugin_Class;
use LPFW\Helpers\Helper_Functions;
use LPFW\Helpers\Plugin_Constants;
use LPFW\Interfaces\Model_Interface;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Model that houses the logic of extending the coupon system of woocommerce.
 * It houses the logic of handling coupon url.
 * Public Model.
 *
 * @since 1.0
 */
class Settings implements Model_Interface {
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
     * @var Settings
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
     * @return Settings
     */
    public static function get_instance( Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants, Helper_Functions $helper_functions ) {
        if ( ! self::$_instance instanceof self ) {
            self::$_instance = new self( $main_plugin, $constants, $helper_functions );
        }

        return self::$_instance;
    }

    /**
     * Register slmw settings menu.
     *
     * @since 1.8
     * @access public
     */
    public function register_slmw_settings_menu() {
        add_menu_page(
            __( 'Loyalty Program License', 'loyalty-program-for-woocommerce' ),
            __( 'Loyalty Program License', 'loyalty-program-for-woocommerce' ),
            'manage_sites',
            'lpfw-ms-license-settings',
            array( $this, 'generate_slmw_settings_page' )
        );
    }

    /**
     * Register slmw settings page.
     *
     * @since 1.8
     * @access public
     */
    public function generate_slmw_settings_page() {
        $license_activated = get_site_option( $this->_constants->OPTION_LICENSE_ACTIVATED );
        $activation_email  = get_site_option( $this->_constants->OPTION_ACTIVATION_EMAIL );
        $license_key       = get_site_option( $this->_constants->OPTION_LICENSE_KEY );
        $constants         = $this->_constants;

        include $this->_constants->VIEWS_ROOT_PATH . 'slmw' . DIRECTORY_SEPARATOR . 'view-license-settings-page.php';
    }

    /**
     * Register slmw settings section.
     *
     * @since 1.0.0
     * @access public
     *
     * @param array $settings_sections Array of settings sections.
     * @return array Filtered array of settings sections.
     */
    public function register_slmw_settings_section( $settings_sections ) {
        if ( array_key_exists( 'lpfw_slmw_settings_section', $settings_sections ) ) {
            return $settings_sections;
        }

        $settings_sections['lpfw_slmw_settings_section'] = __( 'License', 'loyalty-program-for-woocommerce' );

        return $settings_sections;
    }

    /**
     * Register slmw settings section options.
     *
     * @since 1.0.0
     * @access public
     *
     * @param array $settings Array of options per settings sections.
     * @param array $current_settings_section Current settings section.
     * @return array Filtered array of options per settings sections.
     */
    public function register_slmw_settings_section_options( $settings, $current_settings_section ) {
        if ( 'lpfw_slmw_settings_section' !== $current_settings_section ) {
            return $settings;
        }

        return array(
            array(
                'title' => __( 'License', 'loyalty-program-for-woocommerce' ),
                'type'  => 'title',
                'desc'  => sprintf(
                    /* Translators: %1$s: Advanced coupons site my account URL. */
                    __( 'Enter the activation email and the license key given to you after purchasing Loyalty Program for WooCommerce. You can find this information by logging into your <a href="%1$s" target="_blank">My Account</a> on our website or in the purchase confirmation email sent to your email address.', 'loyalty-program-for-woocommerce' ),
                    $this->_constants->PLUGIN_SITE_URL . '/my-account'
                ),
                'id'    => 'lpfw_license_main_title',
            ),
            array(
                'id'    => $this->_constants->OPTION_ACTIVATION_EMAIL,
                'title' => __( 'Activation Email', 'loyalty-program-for-woocommerce' ),
                'desc'  => '',
                'type'  => 'text',
            ),
            array(
                'id'    => $this->_constants->OPTION_LICENSE_KEY,
                'title' => __( 'License Key', 'loyalty-program-for-woocommerce' ),
                'desc'  => '',
                'type'  => 'password',
                'css'   => 'width: 400px; padding: 6px;',
            ),
            array(
                'type' => 'sectionend',
                'id'   => 'lpfw_license_sectionend',
            ),
        );
    }

    /**
     * Register network license page.
     *
     * @since 1.8.1
     * @access public
     *
     * @param array $plugins List of advanced coupon plugins.
     * @return array Filtered list of advanced coupon plugins.
     */
    public function register_network_license_page( $plugins ) {
        $plugins['LPFW'] = array(
            'key'  => 'lpfw_license',
            'name' => __( 'Loyalty Program', 'loyalty-program-for-woocommerce' ),
            'url'  => network_admin_url( 'admin.php?page=advanced-coupons&tab=lpfw_license' ),
        );

        return $plugins;
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
        // Add SLMW Settings In Multi-Site Environment.
        if ( is_multisite() ) {
            add_action( 'acfw_network_menu_lpfw_license_content', array( $this, 'generate_slmw_settings_page' ) );
            add_filter( 'acfw_network_menu_page_plugins', array( $this, 'register_network_license_page' ) );
            return;
        }

        add_filter( 'woocommerce_get_sections_lpfw_settings', array( $this, 'register_slmw_settings_section' ) );
        add_filter( 'woocommerce_get_settings_lpfw_settings', array( $this, 'register_slmw_settings_section_options' ), 10, 2 );
    }
}
