<?php
/**
 * Loyalty Program - loyalty points reminder.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/loyalty-point-reminder.php.
 *
 * @version 1.8.4
 */
defined( 'ABSPATH' ) || exit;

$base      = get_option( 'woocommerce_email_base_color' );
$base_text = wc_light_or_dark( $base, '#202020', '#ffffff' );
if ( ! $email instanceof \WC_Email ) {
    return;
}

// Avoid undefined variable notice.
$balance            = $balance ?? $email->get_text( 'balance', false );
$balance            = $email->format_string( $balance );
$button_text        = $button_text ?? $email->get_text( 'button_text' );
$message            = $message ?? $email->get_text( 'message' );
$email_heading      = $email_heading ?? $email->get_text( 'heading' );
$additional_content = $additional_content ?? $email->get_text( 'additional_content' );

do_action( 'acfw_email_header', $email_heading, $email );?>

<div class="lpfw-loyalty-points-reminder-template">

    <p style="text-align: center;"><?php echo wp_kses_post( $message ); ?></p>
    <p style="text-align: center;">
        <span style="font-weight:600;">
            <?php echo wp_kses_post( $balance ); ?>
        </span>
    </p>

    <?php if ( $additional_content ) : ?>
        <div style="text-align: center;">
            <?php echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) ); ?>
        </div>
    <?php endif; ?>

    <p style="text-align:center;">
        <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" style="cursor: pointer;display: inline-block;padding: 0.6em 3.5em;text-decoration: none;background-color: <?php echo esc_attr( $base ); ?>;border-color: <?php echo esc_attr( $base_text ); ?>;color: #ffffff;font-size: 1.2em; border-radius:.25rem;">
            <?php echo esc_html( $button_text ); ?>
        </a>
    </p>

</div>

<?php
do_action( 'acfw_email_footer', $email );
