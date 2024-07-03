<?php
/**
 * Loyalty Program - earned points notification.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/earned-points-notification.php.
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
$button_text                        = $button_text ?? $email->get_text( 'button_text' );
$message                            = $message ?? $email->get_text( 'message' );
$email_heading                      = $email_heading ?? $email->get_text( 'heading' );
$additional_content                 = $additional_content ?? $email->get_text( 'additional_content' );
$customer_earned_points_action_list = $email->get_customer_earned_points_action_list();

do_action( 'acfw_email_header', $email_heading, $email );?>

<div class="lpfw-earned-points-notification-template">

    <p style="text-align: center;"><?php echo wp_kses_post( $message ); ?></p>

    <?php if ( $additional_content ) : ?>
        <div style="text-align: center;">
            <?php echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) ); ?>
        </div>
    <?php endif; ?>

    <?php if ( $customer_earned_points_action_list ) : ?>
        <h3><?php echo esc_html_e( 'More ways to earn', 'loyalty-program-for-woocommerce' ); ?></h3>
        <div style="background: #f2f5fa; padding: 1rem 0; margin-bottom: 1rem;">
            <?php foreach ( $customer_earned_points_action_list as $action ) : // phpcs:ignore ?>
                <?php if ( $action['info'] ) : ?>
                    <div style="padding: .5rem 2rem;">
                        <strong><?php echo esc_html( $action['name'] ); ?></strong>
                        <?php foreach ( $action['info'] as $info ) : ?>
                            <p style="margin: 0;"><?php echo esc_html( $info ); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
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
