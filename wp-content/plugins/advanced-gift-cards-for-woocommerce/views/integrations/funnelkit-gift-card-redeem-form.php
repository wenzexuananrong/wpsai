<?php if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
?>

<div class="wfacp_woocommerce_form_coupon wfacp-form-control-wrapper agc-gift-card-redeem-form-field <?php echo esc_attr( $classes ); ?>">
    <div class="wfacp-coupon-section wfacp_custom_row_wrap clearfix">
        <div class="wfacp-coupon-page">
            <div class="woocommerce-form-coupon-toggle">
                <?php wc_print_notice( sprintf( '<a class="wfacp_showcoupon">%s</a>', $labels['title'] ), 'notice' ); ?>
            </div>
            <div class="wfacp-row wfacp_coupon_field_box" style="display:none">
                <p class="form-row wfacp-form-control-wrapper agc-redeem-description">
                    <?php echo wp_kses_post( $labels['description'] ); ?>
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
                        'lpfw_redeem_loyalty_points',
                        array(
                            'id'          => 'agc_gift_card_redeem',
                            'type'        => 'agc_gift_card_redeem',
                            'value'       => '',
                            'label'       => $labels['input_placeholder'],
                            'placeholder' => $labels['input_placeholder'],
                            'label_class' => array( 'wfacp-form-control-label' ),
                            'input_class' => array( 'wfacp-form-control' ),
                            'button_text' => $labels['button_text'],
                        )
                    );
                else :
                ?>
                <p><?php echo wp_kses_post( $labels['guest_content'] ); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
</div>
