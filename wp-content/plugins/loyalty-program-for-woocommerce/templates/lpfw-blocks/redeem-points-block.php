<?php
/**
 * Redeem loyalty points checkout row template.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/lpfw-blocks/redeem-points-block.php.
 *
 * @version 1.8.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

do_action( 'lpfw_before_redeem_points_block' ); ?>

<div class="<?php echo esc_attr( implode( ' ', $classnames ) ); ?>">
    <?php if ( $points_summary ) : ?>
        <p class="points-summary">
            <?php echo wp_kses_post( $points_summary ); ?>
        </p>
    <?php endif; ?>
    <?php if ( $points_description ) : ?>
        <p class="points-description">
            <?php echo wp_kses_post( $points_description ); ?>
        </p>
    <?php endif; ?>
    <div class="points-redeem-form" data-nonce="<?php echo esc_attr( wp_create_nonce( 'lpfw_redeem_points_for_user' ) ); ?>">
        <input type="number" class="points-field" min="<?php echo esc_attr( $min_points ); ?>" style="min-width:150px;" max="<?php echo esc_attr( $max_points ); ?>" placeholder="<?php echo esc_attr( $input_placeholder ); ?>" />
        <button type="button" class="alt trigger-redeem" disabled><?php echo esc_html( $button_text ); ?></button>
    </div>
</div>

<?php
do_action( 'lpfw_after_redeem_points_block' );
