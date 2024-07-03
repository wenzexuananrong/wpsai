<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} ?>

<div class="bogo-settings-field bogo-auto-add-products-field <?php echo 'specific-products' === $deals_type ? 'show' : ''; ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'acfw_save_bogo_additional_settings' ) ); ?>">
    <label><?php esc_html_e( 'Automatically add deal products to cart:', 'advanced-coupons-for-woocommerce' ); ?></label>
    <input type="checkbox" name="acfw_bogo_auto_add_products" value="yes" <?php checked( $auto_add_products, true ); ?> />
</div>
