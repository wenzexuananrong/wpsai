<?php
namespace AGCFW\Third_Party_Integrations;

use AGCFW\Abstracts\Abstract_Main_Plugin_Class;
use AGCFW\Helpers\Helper_Functions;
use AGCFW\Helpers\Plugin_Constants;
use AGCFW\Interfaces\Model_Interface;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Model that houses the logic of the FunnelKit module.
 *
 * @since 1.3.5
 */
class FunnelKit implements Model_Interface {
    /*
    |--------------------------------------------------------------------------
    | Class Properties
    |--------------------------------------------------------------------------
     */

    /**
     * Property that holds the single main instance of URL_Coupon.
     *
     * @since 1.3.5
     * @access private
     * @var FunnelKit
     */
    private static $_instance;

    /**
     * Model that houses all the plugin constants.
     *
     * @since 1.3.5
     * @access private
     * @var Plugin_Constants
     */
    private $_constants;

    /**
     * Property that houses all the helper functions of the plugin.
     *
     * @since 1.3.5
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
     * @since 1.3.5
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
     * @since 1.3.5
     * @access public
     *
     * @param Abstract_Main_Plugin_Class $main_plugin      Main plugin object.
     * @param Plugin_Constants           $constants        Plugin constants object.
     * @param Helper_Functions           $helper_functions Helper functions object.
     * @return WPML_Support
     */
    public static function get_instance( Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants, Helper_Functions $helper_functions ) {
        if ( ! self::$_instance instanceof self ) {
            self::$_instance = new self( $main_plugin, $constants, $helper_functions );
        }

        return self::$_instance;
    }

    /**
     * Register gift card product type in FunnelKit Cart.
     *
     * @since 1.3.5
     * @access public
     *
     * @param array $product_types Product types.
     * @return array Filtered product types.
     */
    public function register_gift_card_product_type_in_funnelkit_cart( $product_types ) {
        $product_types[] = 'advanced_gift_card';
        return $product_types;
    }

    /**
     * Register custom checkout fields.
     *
     * @since 1.3.5
     * @access public
     *
     * @param array $checkout_fields Checkout fields.
     * @return array Filtered checkout fields.
     */
    public function register_custom_checkout_fields( $checkout_fields ) {

        $checkout_fields['agc_redeem_gift_card'] = array(
            'type'         => 'wfacp_html',
            'field_type'   => 'advanced',
            'id'           => 'agc_redeem_gift_card',
            'default'      => false,
            'class'        => array( 'agc_wfacp_reedem_loyalty_points' ),
            'label'        => __( 'Gift Card Code', 'advanced-gift-cards-for-woocommerce' ),
            'data_label'   => __( 'Gift Card Code', 'advanced-gift-cards-for-woocommerce' ),
            'coupon_style' => 'true',
        );

        return $checkout_fields;
    }

    /**
     * Set the html element class prefix for the gift card redeem form field.
     *
     * @since 1.3.5
     * @access public
     *
     * @param string $prefix Class prefix.
     * @return string Filtered class prefix.
     */
    public function set_redeem_form_field_class_prefix( $prefix ) {
        return $this->is_funnelkit_checkout_enabled() ? 'wfacp' : $prefix;
    }

    /**
     * Display gift card redeem form field.
     *
     * @since 1.3.5
     * @access public
     *
     * @param array  $field Field data.
     * @param string $key Field key.
     * @param array  $args Field args.
     */
    public function display_redeem_form_field( $field, $key, $args ) {

        $instance       = wfacp_template();
        $checkout_field = $instance->get_checkout_fields();

        if ( ! isset( $checkout_field['advanced']['agc_redeem_gift_card'] ) || 'agc_redeem_gift_card' !== $key ) {
            return;
        }

        if ( ! empty( $field ) ) {
            $args = WC()->session->get( 'agc_redeem_gift_card' . \WFACP_Common::get_id(), $field );
        }

        $coupon_cls = $instance->get_template_type() === 'embed_form' ? 'wfacp-col-full' : 'wfacp-col-left-half';
        $classes    = isset( $args['cssready'] ) ? implode( ' ', $args['cssready'] ) : '';
        $labels     = \AGCFW()->Redeem->get_default_redeem_form_template_args();

        include $this->_constants->VIEWS_ROOT_PATH . 'integrations/funnelkit-gift-card-redeem-form.php';
    }

    /**
     * Check if the FunnelKit checkout is enabled or not.
     *
     * @since 1.3.5
     * @access public
     *
     * @return bool True if active, false otherwise.
     */
    public function is_funnelkit_checkout_enabled() {
        if ( ! class_exists( '\WFFN_Common' ) || ! function_exists( 'WFFN_Core' ) ) {
            return false;
        }

        $checkout_id = \WFFN_Common::get_store_checkout_id();
        return (bool) \WFFN_Core()->get_dB()->get_meta( $checkout_id, 'status' );
    }

    /*
    |--------------------------------------------------------------------------
    | Fulfill implemented interface contracts
    |--------------------------------------------------------------------------
     */

    /**
     * Execute FunnelKit class.
     *
     * @since 1.3.5
     * @access public
     * @inherit ACFWP\Interfaces\Model_Interface
     */
    public function run() {
        if ( $this->_helper_functions->is_plugin_active( 'cart-for-woocommerce/plugin.php' ) ) {
            add_filter( 'fkcart_allow_product_types', array( $this, 'register_gift_card_product_type_in_funnelkit_cart' ) );
        }

        if ( $this->_helper_functions->is_plugin_active( 'funnel-builder/funnel-builder.php' ) ) {
            add_filter( 'wfacp_advanced_fields', array( $this, 'register_custom_checkout_fields' ) );
            add_filter( 'wfacp_html_fields_agc_redeem_gift_card', '__return_false' );
            add_filter( 'agc_redeem_form_field_class_prefix', array( $this, 'set_redeem_form_field_class_prefix' ) );
            add_action( 'process_wfacp_html', array( $this, 'display_redeem_form_field' ), 10, 3 );
        }
    }
}
