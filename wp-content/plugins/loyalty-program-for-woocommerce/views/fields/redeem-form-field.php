<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div id="<?php echo esc_attr( $args['id'] ); ?>" class="lpfw-redeem-loyalty-points-form-field acfw-checkout-form-button-field <?php echo esc_attr( implode( ' ', $args['class'] ) ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'lpfw_redeem_points_for_user' ) ); ?>">
    <p class="form-row form-row-first <?php echo esc_attr( $class_prefix ); ?>-form-control-wrapper <?php echo esc_attr( $class_prefix ); ?>-col-left-half <?php echo esc_attr( $class_prefix ); ?>-input-form">
        <label for="coupon_code" class="<?php echo esc_attr( implode( ' ', $args['label_class'] ) ); ?>"><?php echo esc_html( $args['label'] ); ?></label>
        <input type="number" class="points-field <?php echo esc_attr( implode( ' ', $args['input_class'] ) ); ?>" min="<?php echo esc_attr( $args['min_points'] ); ?>"  max="<?php echo esc_attr( $args['max_points'] ); ?>" value="<?php echo esc_attr( $value ?? '' ); ?>" placeholder="<?php echo esc_html( $args['placeholder'] ); ?>" />
    </p>
    <p class="form-row form-row-last <?php echo esc_attr( $class_prefix ); ?>-col-left-half <?php echo esc_attr( $class_prefix ); ?>_coupon_btn_wrap">
        <label class="<?php echo esc_attr( $class_prefix ); ?>-form-control-label">&nbsp;</label>
        <button type="button" class="trigger-redeem button alt" disabled><?php echo esc_html( $args['button_text'] ); ?></button>
    </p>
</div>
