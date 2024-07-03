<?php
namespace LPFW\Models;

use LPFW\Abstracts\Base_Model;
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
 * @since 1.8.7
 */
class Coupon_Restrictions extends Base_Model implements Model_Interface {
    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
     */

    /**
     * Class constructor.
     *
     * @since 1.8.7
     * @access public
     *
     * @param Abstract_Main_Plugin_Class $main_plugin      Main plugin object.
     * @param Plugin_Constants           $constants        Plugin constants object.
     * @param Helper_Functions           $helper_functions Helper functions object.
     */
    public function __construct( Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants, Helper_Functions $helper_functions ) {
        parent::__construct( $main_plugin, $constants, $helper_functions );
        $main_plugin->add_to_all_plugin_models( $this );
    }

    /*
    |--------------------------------------------------------------------------
    | Admin Methods
    |--------------------------------------------------------------------------
     */

    /**
     * Display the disallow earning points when coupon applied field.
     *
     * @since 1.8.7
     * @access public
     *
     * @param int        $coupon_id Coupon ID.
     * @param \WC_Coupon $coupon    Coupon object.
     */
    public function display_disallow_earning_points_when_coupon_applied_field( $coupon_id, $coupon ) {

        $value = $coupon->get_meta( $this->_constants->COUPON_DISALLOW_EARN_POINTS, true );

        echo '<div class="options_group">';

        // Individual use.
        woocommerce_wp_checkbox(
            array(
                'id'          => $this->_constants->COUPON_DISALLOW_EARN_POINTS,
                'label'       => __( 'Disallow earning loyalty points', 'loyalty-program-for-woocommerce' ),
                'description' => __( 'Check this box if the customer should not earn any loyalty points when the coupon is applied in the cart.', 'loyalty-program-for-woocommerce' ),
                'value'       => $value,
            )
        );

        echo '</div>';
    }

    /**
     * Save the disallow earning points when coupon applied field.
     *
     * @since 1.8.7
     * @access public
     *
     * @param int                                   $coupon_id Coupon ID.
     * @param \ACFWF\Models\Objects\Advanced_Coupon $coupon Advanced coupon object.
     */
    public function save_disallow_earning_points_when_coupon_applied_field( $coupon_id, $coupon ) {
        // skip if the field data is not set.
        if ( ! isset( $_POST[ $this->_constants->COUPON_DISALLOW_EARN_POINTS ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
            $coupon->delete_meta_data( $this->_constants->COUPON_DISALLOW_EARN_POINTS );
            return;
        }

        $coupon->update_meta_data( $this->_constants->COUPON_DISALLOW_EARN_POINTS, 'yes' );
    }

    /*
    |--------------------------------------------------------------------------
    | Frontend implementation
    |--------------------------------------------------------------------------
     */

    /**
     * Validate if the customer should earn points when the coupon is applied.
     *
     * @since 1.8.7
     * @access public
     *
     * @param bool           $is_allowed Is allowed to earn points.
     * @param int            $user_id    User ID.
     * @param \WC_Order|null $order      Order object.
     * @return bool
     */
    public function validate_applied_coupons_should_allow_earn_points( $is_allowed, $user_id, $order ) {
        // Validate for orders.
        if ( $order instanceof \WC_Order ) {
            foreach ( $order->get_coupon_codes() as $coupon_code ) {
                $coupon = new \WC_Coupon( $coupon_code );

                if ( 'yes' === $coupon->get_meta( $this->_constants->COUPON_DISALLOW_EARN_POINTS, true ) ) {
                    $is_allowed = false;
                    break;
                }
            }

            return $is_allowed;
        }

        // Validate for the cart.
        if ( \WC()->cart instanceof \WC_Cart ) {
            foreach ( \WC()->cart->get_coupons() as $coupon ) {
                if ( 'yes' === $coupon->get_meta( $this->_constants->COUPON_DISALLOW_EARN_POINTS, true ) ) {
                    $is_allowed = false;
                    break;
                }
            }
        }

        return $is_allowed;
    }


    /*
    |--------------------------------------------------------------------------
    | Fulfill implemented interface contracts
    |--------------------------------------------------------------------------
     */

    /**
     * Execute Messages class.
     *
     * @since 1.8.7
     * @access public
     * @inherit LPFW\Interfaces\Model_Interface
     */
    public function run() {
        // Admin.
        add_action( 'woocommerce_coupon_options_usage_restriction', array( $this, 'display_disallow_earning_points_when_coupon_applied_field' ), 5, 2 );
        add_action( 'acfw_before_save_coupon', array( $this, 'save_disallow_earning_points_when_coupon_applied_field' ), 10, 2 );

        // Frontend implementation.
        add_filter( 'lpfw_should_customer_earn_points', array( $this, 'validate_applied_coupons_should_allow_earn_points' ), 10, 3 );
    }
}
