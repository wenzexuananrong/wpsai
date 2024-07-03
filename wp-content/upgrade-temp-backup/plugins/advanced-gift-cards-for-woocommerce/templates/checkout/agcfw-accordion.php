<?php
/**
 * My Account: Redeem gift card form.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/agcfw-accordion.php.
 */
defined( 'ABSPATH' ) || exit; ?>

<p class="agc-redeem-description">
    <?php echo esc_html( $labels['description'] ); ?>
    <a
        class="agcfw-tooltip"
        href="javascript:void(0);"
        data-title="<?php echo esc_attr( $labels['tooltip_title'] ); ?>"
        data-content="<?php echo esc_attr( $labels['tooltip_content'] ); ?>"
    ><?php echo esc_html( $labels['tooltip_link_text'] ); ?></a>
</p>

<?php
if ( is_user_logged_in() ) :
    woocommerce_form_field(
        'agc_gift_card_redeem',
        array(
            'id'          => 'agc_gift_card_redeem',
            'type'        => 'agc_gift_card_redeem',
            'value'       => '',
            'label'       => '',
            'placeholder' => $labels['input_placeholder'],
            'button_text' => $labels['button_text'],
        )
    );
else :
?>

<p><?php echo wp_kses_post( $labels['guest_content'] ); ?></p>

<?php
endif;
