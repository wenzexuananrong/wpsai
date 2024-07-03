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
                'force_apply' => \ACFWP()->Force_Apply->is_coupon_force_apply( $coupon ),
            );
        }

        return $coupons;
    }

    /**
     * Prepare the provided coupon IDs as select options by getting the appropriate coupon code for each ID, or the category name for a given category slug.
     *
     * @since 3.6.0
     * @access public
     *
     * @param array $coupon_ids List of coupon IDs and/or maybe category slugs.
     * @return array Coupon select options.
     */
    public function prepare_coupon_select_options( $coupon_ids ) {
        $options = array();

        // Skip if the provided coupon IDs are not an array or empty.
        if ( ! is_array( $coupon_ids ) || empty( $coupon_ids ) ) {
            return $options;
        }

        foreach ( $coupon_ids as $coupon_id ) {
            if ( strpos( $coupon_id, 'cat_' ) !== false ) {
                $category = get_term_by( 'slug', substr( $coupon_id, 4 ), $this->_constants->COUPON_CAT_TAXONOMY );
                /* Translators: %s: Category name. */
                $options[ $coupon_id ] = sprintf( __( 'Category: %s', 'advanced-coupons-for-woocommerce' ), $category->name );
            } else {
                $options[ $coupon_id ] = wc_get_coupon_code_by_id( $coupon_id );
            }
        }

        return $options;
    }

    /**
     * Get coupon IDs for select coupons field value.
     * NOTE: this field is used in both the "Exclude Coupons" and the "Coupons Applied In Cart" cart condition feature.
     *
     * @since 3.6.0
     * @access public
     *
     * @param array $field_value Field value.
     * @param array $exclude_coupon_ids List of coupon IDs to exclude.
     * @return array List of coupon IDs.
     */
    public function get_coupon_ids_for_select_coupons_field_value( $field_value, $exclude_coupon_ids = array() ) {
        // get coupon categories only from the selected options.
        $cat_slugs = array_filter(
            $field_value,
            function ( $i ) {
            return strpos( $i, 'cat_' ) !== false;
            }
        );

        // skip if there are no categories in selection.
        if ( empty( $cat_slugs ) ) {
            return array_map( 'absint', $field_value );
        }

        $field_value = array_diff( $field_value, $cat_slugs );

        // remove the "cat_" string prepended for each category.
        $cat_slugs = array_map(
            function ( $c ) {
            return substr( $c, 4 );
            },
            $cat_slugs
        );

        $query = new \WP_Query(
            array(
                'post_type'      => 'shop_coupon',
                'status'         => 'publish',
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'post_status'    => 'publish',
                'tax_query'      => array(
                    'relation' => 'OR',
                    array(
                        'taxonomy' => $this->_constants->COUPON_CAT_TAXONOMY,
                        'field'    => 'slug',
                        'terms'    => $cat_slugs,
                    ),
                ),
                'post__not_in'   => $exclude_coupon_ids,
            )
        );

        if ( is_array( $query->posts ) && ! empty( $query->posts ) ) {
            $field_value = array_unique( array_merge( $field_value, $query->posts ) );
        }

        return array_map( 'absint', $field_value );
    }

    /**
     * Get coupon IDs applied in cart and remove the invalid coupon IDs (0).
     *
     * @since 3.6.0
     * @access public
     *
     * @return array List of coupon IDs applied in cart.
     */
    public function get_coupon_ids_applied_in_cart() {
        $applied_coupons = array_map( 'wc_get_coupon_id_by_code', \WC()->cart->get_applied_coupons() );
        $applied_coupons = array_filter( $applied_coupons );

        return $applied_coupons;
    }
}
