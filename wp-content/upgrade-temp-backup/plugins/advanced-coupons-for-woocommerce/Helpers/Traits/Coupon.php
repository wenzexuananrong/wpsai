<?php
namespace ACFWP\Helpers\Traits;

use ACFWP\Models\Objects\Advanced_Coupon;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Trait that houses all the helper functions specifically for Coupon.
 *
 * @since 3.5.7
 */
trait Coupon {
    /**
     * Get list of coupons used by a user.
     *
     * @since 3.5.7
     * @access public
     *
     * @param int $user_id User ID.
     * @return array List of coupons used by a user.
     */
    public function get_coupons_used_by( $user_id = 0 ) {
        // Get the coupon post.
        $coupons = get_posts(
            array(
                'post_type'      => 'shop_coupon',
                'posts_per_page' => -1,
                'meta_query'     => array(
                    'relation' => 'OR',
                    array(
                        'key'   => '_used_by',
                        'value' => $user_id ?? get_current_user_id(),
                    ),
                ),
            )
        );

        // Transform coupons into ACFWP coupon objects.
        foreach ( $coupons as &$coupon ) {
            $coupon = new Advanced_Coupon( $coupon->post_name );
        }

        // Return coupons.
        return $coupons;
    }

    /**
     * Get applied coupon data for Cart & Checkout Block (Store API).
     *
     * @since 3.5.7
     * @access public
     *
     * @return array List of coupons used by a user.
     */
    public function get_applied_coupon_data() {
        $coupons = array();
        foreach ( \WC()->cart->get_applied_coupons() as $coupon_code ) {
            $coupon    = new Advanced_Coupon( $coupon_code );
            $coupons[] = array(
                'code'        => $coupon_code,
                'label'       => $coupon->get_advanced_prop( 'coupon_label' ),
                'force_apply' => \ACFWP()->Force_Apply->is_provided_option_true( $coupon->get_advanced_prop( 'force_apply_url_coupon' ) ),
            );
        }

        return $coupons;
    }
}
