<?php
/**
 * Loyalty Program - loyalty points reminder.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/loyalty-point-reminder.php.
 *
 * @version 1.8.4
 */
defined( 'ABSPATH' ) || exit;

if ( ! $email instanceof \WC_Email ) {
    return;
}

// Avoid undefined variable notice.
$balance            = $balance ?? wp_strip_all_tags( $email->get_text( 'balance' ) );
$button_text        = $button_text ?? wp_strip_all_tags( $email->get_text( 'button_text' ) );
$message            = $message ?? wp_strip_all_tags( $email->get_text( 'message' ) );
$email_heading      = $email_heading ?? wp_strip_all_tags( $email->get_text( 'heading' ) );
$additional_content = $additional_content ?? wp_strip_all_tags( wptexturize( $email->get_text( 'additional_content' ) ) );

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo esc_html( $message ) . "\n\n";
echo esc_html( $balance );

echo "\n\n----------------------------------------\n\n";

echo esc_html( sprintf( '%s: %s', $button_text, get_permalink( wc_get_page_id( 'shop' ) ) ) );
echo "\n\n----------------------------------------\n\n";

if ( $additional_content ) {
    echo esc_html( $additional_content );
    echo "\n\n----------------------------------------\n\n";
}

if ( ! apply_filters( 'acfw_use_woocommerce_email_footer', false ) ) {
    esc_html_e( 'Powered by', 'loyalty-program-for-woocommerce' );
    echo ' Advanced Coupons ';
    echo esc_url_raw( 'https://advancedcouponsplugin.com/powered-by/?utm_source=acfwf&utm_medium=sendcouponemail&utm_campaign=sendcouponpoweredby' );
} else {
    echo wp_kses_post( apply_filters( 'acfw_email_footer', get_option( 'woocommerce_email_footer_text' ) ) );
}
