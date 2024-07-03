<?php
namespace LPFW\Models\Third_Party_Integrations;

use LPFW\Abstracts\Abstract_Main_Plugin_Class;
use LPFW\Abstracts\Base_Model;
use LPFW\Helpers\Helper_Functions;
use LPFW\Helpers\Plugin_Constants;
use LPFW\Interfaces\Model_Interface;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Model that houses the logic of the FunnelKit module.
 *
 * @since 1.8.4
 */
class FunnelKit extends Base_Model implements Model_Interface {
    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
     */

    /**
     * Class constructor.
     *
     * @since 1.8.4
     * @access public
     *
     * @param Abstract_Main_Plugin_Class $main_plugin      Main plugin object.
     * @param Plugin_Constants           $constants        Plugin constants object.
     * @param Helper_Functions           $helper_functions Helper functions object.
     */
    public function __construct( Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants, Helper_Functions $helper_functions ) {
        parent::__construct( $main_plugin, $constants, $helper_functions );

        $main_plugin->add_to_all_plugin_models( $this );
        $main_plugin->add_to_public_models( $this );
    }

    /**
     * Register custom checkout fields.
     *
     * @since 1.8.4
     * @access public
     *
     * @param array $checkout_fields Checkout fields.
     * @return array Filtered checkout fields.
     */
    public function register_custom_checkout_fields( $checkout_fields ) {

        $checkout_fields['lpfw_reedem_loyalty_points'] = array(
            'type'         => 'wfacp_html',
            'field_type'   => 'advanced',
            'id'           => 'lpfw_reedem_loyalty_points',
            'default'      => false,
            'class'        => array( 'lpfw_wfacp_reedem_loyalty_points' ),
            'label'        => __( 'Loyalty Points', 'loyalty-program-for-woocommerce' ),
            'data_label'   => __( 'Loyalty Points', 'loyalty-program-for-woocommerce' ),
            'coupon_style' => 'true',
            '',
        );

        return $checkout_fields;
    }

    /**
     * Set the html element class prefix for the store credits redeem form field.
     *
     * @since 1.8.4
     * @access public
     *
     * @param string $prefix Class prefix.
     * @return string Filtered class prefix.
     */
    public function set_redeem_form_field_class_prefix( $prefix ) {
        return $this->is_funnelkit_checkout_enabled() ? 'wfacp' : $prefix;
    }

    /**
     * Display store credit redeem form field.
     *
     * @since 1.8.4
     * @access public
     *
     * @param array  $field Field data.
     * @param string $key Field key.
     * @param array  $args Field args.
     */
    public function display_redeem_form_field( $field, $key, $args ) {
        if ( ! LPFW()->User_Points->is_show_checkout_redeem_form() ) {
            return;
        }

        $instance       = wfacp_template();
        $checkout_field = $instance->get_checkout_fields();

        if ( ! isset( $checkout_field['advanced']['lpfw_reedem_loyalty_points'] ) || 'lpfw_reedem_loyalty_points' !== $key ) {
            return;
        }

        if ( ! empty( $field ) ) {
            $args = WC()->session->get( 'lpfw_reedem_loyalty_points' . \WFACP_Common::get_id(), $field );
        }

        $coupon_cls   = $instance->get_template_type() === 'embed_form' ? 'wfacp-col-full' : 'wfacp-col-left-half';
        $classes      = isset( $args['cssready'] ) ? implode( ' ', $args['cssready'] ) : '';
        $user_points  = LPFW()->Calculate->get_user_total_points( get_current_user_id() );
        $min_points   = (int) $this->_helper_functions->get_option( $this->_constants->MINIMUM_POINTS_REDEEM, '0' );
        $points_name  = $this->_helper_functions->get_points_name();
        $points_worth = $this->_helper_functions->api_wc_price( LPFW()->Calculate->calculate_redeem_points_worth( $user_points ) );
        $max_points   = LPFW()->Calculate->calculate_allowed_max_points( $user_points, true );
        $labels       = LPFW()->User_Points->get_loyalty_points_redeem_form_labels();

        include $this->_constants->VIEWS_ROOT_PATH . 'integrations/funnelkit-loyalty-points-redeem-form.php';
    }

    /**
     * Check if the FunnelKit checkout is enabled or not.
     *
     * @since 1.8.4
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
     * @since 1.8.4
     * @access public
     * @inherit ACFWP\Interfaces\Model_Interface
     */
    public function run() {
        if ( ! $this->_helper_functions->is_plugin_active( 'funnel-builder/funnel-builder.php' ) ) {
            return;
        }

        add_filter( 'wfacp_advanced_fields', array( $this, 'register_custom_checkout_fields' ) );
        add_filter( 'wfacp_html_fields_lpfw_reedem_loyalty_points', '__return_false' );
        add_filter( 'lpfw_redeem_form_field_class_prefix', array( $this, 'set_redeem_form_field_class_prefix' ) );
        add_action( 'process_wfacp_html', array( $this, 'display_redeem_form_field' ), 10, 3 );
    }
}
