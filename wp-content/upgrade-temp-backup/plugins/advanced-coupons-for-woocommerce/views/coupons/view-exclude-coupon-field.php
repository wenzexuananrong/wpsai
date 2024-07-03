<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}  ?>

<div class="options_group">
    <p class="form-field acfw_exclude_coupons_field">
        <label for="acfw_exclude_coupons"><?php esc_html_e( 'Exclude coupons', 'advanced-coupons-for-woocommerce' ); ?></label>
        <select class="wc-product-search" multiple style="width: 50%;" name="_acfw_exclude_coupon_ids[]"
            data-placeholder="<?php esc_attr_e( 'Search coupons and/or coupon categories&hellip;', 'advanced-coupons-for-woocommerce' ); ?>"
            data-action="acfw_search_coupons"
            data-exclude="<?php echo esc_attr( wp_json_encode( array( $coupon_id ) ) ); ?>"
            data-include="true">
            <?php foreach ( $options as $key => $label ) : ?>
                <option value="<?php echo esc_attr( $key ); ?>" selected><?php echo esc_html( $label ); ?></option>
            <?php endforeach ?>
        </select>
        <?php echo wp_kses_post( wc_help_tip( __( 'This is the advanced version of the "Individual use only" field. Coupons listed here or coupons under the categories listed cannot be used in conjunction with this coupon.', 'advanced-coupons-for-woocommerce' ) ) ); ?>
    </p>
</div>
