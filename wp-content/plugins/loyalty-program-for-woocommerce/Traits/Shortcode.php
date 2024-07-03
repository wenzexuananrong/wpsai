<?php
namespace LPFW\Traits;

use LPFW\Objects\Vite_App;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Trait that houses shortcode function.
 *
 * @since 1.8.6
 */
trait Shortcode {
    /**
     * Display earned points on single product page.
     *
     * @since 1.0
     * @since 1.8.6 Move to traits and add output buffering.
     * @access public
     *
     * @return @return string|null HTML content for the earned points message on single product page, or null if the message should not be displayed.
     */
    public function points_earn_message_single_product() {
        global $product;

        // validate if product is allowed to earn points.
        if (
            ! $product instanceof \WC_Product
            || 'yes' !== $this->_helper_functions->is_product_allowed_to_earn_points( $product )
            || ! $this->_helper_functions->is_product_categories_allowed_to_earn_points( $product->get_id() )
            || get_option( $this->_constants->EARN_ACTION_BUY_PRODUCT, 'yes' ) !== 'yes'
            || ! $this->should_display_points_earn_message()
        ) {
            return;
        }

        // validate if message option is exist.
        $message = $this->get_notice_message_template( 'product' );
        if ( ! $message ) {
            return;
        }

        $multiplier   = \LPFW()->Calculate->get_product_cat_points_to_price_ratio( $product->get_id() );
        $display      = is_a( $product, 'WC_Product_Variable' ) ? 'style="display:none;"' : '';
        $calc_options = $this->_helper_functions->get_enabled_points_calc_options();
        $include_tax  = in_array( 'tax', $calc_options, true );
        $points       = intval( \LPFW()->Calculate->calculate_product_points( $product, 1, $include_tax ) );
        $message      = strpos( $message, '{points}' ) === false ? $message . ' <strong>{points}</strong>' : $message;
        $notice       = str_replace( '{points}', $points, $message );

        // enqueue js file.
        $product_points_notice_vite = new Vite_App(
            'lpfw-product-points-notice',
            'packages/lpfw-product-points-notice/index.ts',
            array( 'jquery' )
        );
        $product_points_notice_vite->enqueue();

        // enqueue js file for variable product page.
        if ( is_a( $product, 'WC_Product_Variable' ) ) {
            wp_localize_script(
                'lpfw-product-points-notice',
                'lpfwVariationArgs',
                array(
                    'message'        => $message,
                    'multiplier'     => $multiplier,
                    'currency_ratio' => apply_filters( 'acfw_filter_amount', 1 ),
                    'includeTaxCalc' => $include_tax ? 'yes' : 'no',
                    'taxDisplay'     => get_option( 'woocommerce_tax_display_shop', 'incl' ),
                    'customPoints'   => $product->get_meta( $this->_constants->PRODUCT_CUSTOM_POINTS, true, 'edit' ),
                )
            );
        }

        // enqueue js file for simple product page.
        wp_localize_script(
            'lpfw-product-points-notice',
            'lpfwProductArgs',
            array(
                'options' => array(
                    'display_multiple_earn_product_message' => apply_filters( 'lpfw_display_multiple_earn_product_message', false ),
                ),
            )
        );

        // print the notice.
        ob_start();
            echo wp_kses_post( "<div class='loyalprog-earn-message' {$display}>" );
            wc_print_notice( $notice, 'notice' );
            echo '</div>';
        return ob_get_clean();
    }

    /**
     * Display earned points on cart page.
     *
     * @since 1.0
     * @since 1.8.6 Move to traits and add output buffering.
     * @access public
     *
     * @return  string|null Earned points message HTML, or null if message should not be displayed.
     */
    public function points_earn_message_in_cart() {
        if ( ! $this->should_display_points_earn_message() ) {
            return;
        }

        $message     = $this->_get_points_earn_message_preview( $this->get_notice_message_template( 'cart' ) );
        $notice_html = str_replace( 'woocommerce-info', 'woocommerce-info acfw-notice lpfw-points-to-earn-message', $message );

        return wp_kses_post( $notice_html );
    }
}
