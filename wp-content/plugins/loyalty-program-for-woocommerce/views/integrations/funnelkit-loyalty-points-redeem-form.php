<?php if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
$placeholder = sprintf( $labels['placeholder'], strtolower( $points_name ) );
?>

<div class="wfacp_woocommerce_form_coupon wfacp-form-control-wrapper lpfw-loyalty-pointss-redeem-form-field <?php echo esc_attr( $classes ); ?>">
    <div class="wfacp-coupon-section wfacp_custom_row_wrap clearfix">
        <div class="wfacp-coupon-page">
            <div class="woocommerce-form-coupon-toggle">
                <?php wc_print_notice( sprintf( '<a class="wfacp_showcoupon">%s</a>', $labels['toggle_text'] ), 'notice' ); ?>
            </div>
            <div class="wfacp-row wfacp_coupon_field_box" style="display:none">
                <p class="form-row wfacp-form-control-wrapper lpfw-loyalty-points-user-balance">
                <?php
                    echo wp_kses_post(
                        sprintf(
                            $labels['balance_text'],
                            '<strong>' . $user_points . '</strong>',
                            '<span class="points-name">' . strtolower( $points_name ) . '</span>',
                            '<strong>' . $points_worth . '</strong>'
                        )
                    );
                ?>
                </p>
                <p class="form-row wfacp-form-control-wrapper lpfw-loyalty-points-instructions">
                <?php
                    echo wp_kses_post(
                        sprintf(
                            $labels['instructions'],
                            '<span class="max-points">' . $max_points . '</span>',
                            '<span class="points-name">' . strtolower( $points_name ) . '</span>',
                        )
                    );
                ?>
                </p>
                <?php
                    woocommerce_form_field(
                        'lpfw_redeem_loyalty_points',
                        array(
                            'id'          => 'lpfw_redeem_loyalty_points',
                            'type'        => 'lpfw_redeem_loyalty_points',
                            'value'       => '',
                            'label'       => $placeholder,
                            'placeholder' => $placeholder,
                            'label_class' => array( 'wfacp-form-control-label' ),
                            'input_class' => array( 'wfacp-form-control' ),
                            'min_points'  => $min_points,
							'max_points'  => $max_points,
                        )
                    );
                ?>
            </div>
        </div>
    </div>
    
</div>
