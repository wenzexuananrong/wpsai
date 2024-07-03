<?php
add_action('woocommerce_checkout_before_customer_details', 'orca_checkout_before_customer_details_cutdown');
function orca_checkout_before_customer_details_cutdown(){
    function clear(){
        WC()->session->__unset( 'sctv_checkout_countdown_time' );
        WC()->session->__unset( 'sctv_checkout_countdown_details' );
        WC()->session->__unset( 'sctv_checkout_countdown_details_before' );
        WC()->session->__unset( 'sctv_checkout_countdown_details_default' );
        WC()->session->__unset( 'sctv_checkout_countdown_check' );
    }

    //clear();

    $settings                         = new VI_SCT_SALES_COUNTDOWN_TIMER_Data();
    $language                         = VI_SCT_SALES_COUNTDOWN_TIMER_Frontend_Shortcode::get_language();
    $session_countdown_check          = WC()->session->get( 'sctv_checkout_countdown_check' );
    $session_countdown_time           = WC()->session->get( 'sctv_checkout_countdown_time' );
    $session_countdown_details        = WC()->session->get( 'sctv_checkout_countdown_details' );
    $session_countdown_details_before = WC()->session->get( 'sctv_checkout_countdown_details_before' );
    $session_countdown_end            = is_array( $session_countdown_time ) && isset( $session_countdown_time['end'] ) ? (int) $session_countdown_time['end'] : '';
    if ( $session_countdown_check === 'yes' && $session_countdown_time ) {
        if ( $session_countdown_details_before ) {
            $checkout_message = $settings->get_params( 'checkout_countdown_message_checkout_page_missing', '_' . $language );
        } else {
            $checkout_message = '{cutdown_before} Place your order within {countdown_timer} {cutdown_after}';
        }
        $sale_countdown_id = $settings->get_params( 'checkout_countdown_id_on_checkout_page' );

        $checkout_shortcode = '[sctv_checkout_countdown_timer sale_countdown_id="' . $sale_countdown_id . '"  time_end ="' . $session_countdown_end . '" checkout_inline ="" message="' .  $checkout_message . '" ]';
    }
    $checkout_countdown_class = 'woo-sctr-checkout-countdown-checkout-page-wrap';

    if ( isset( $checkout_shortcode ) ) {
        $checkout_countdown_discount_amount = $settings->get_params( 'checkout_countdown_discount_amount' );

        echo '<div class="woo-sctr-checkout-countdown-wrap-wrap '.$checkout_countdown_class.'">';  
        $html = do_shortcode( $checkout_shortcode );
        $cutdown_before = '<div style=" color: black; font-weight: bold;text-align: center; margin: 0 auto;">Quick Bird Disount</div>';
        $html = str_replace("{cutdown_before}", $cutdown_before, $html);
        $cutdown_after = '<div style="margin: 0 auto;">to save <span style="color: orange;">$9.99</span>. You are getting <span style="color: orange;">5% off</span> and additional <span style="color: orange;">'.$checkout_countdown_discount_amount.'% off</span></div>';
        $html = str_replace("{cutdown_after}", $cutdown_after, $html);
        echo ent2ncr( $html );
        echo '</div>';
    }
}