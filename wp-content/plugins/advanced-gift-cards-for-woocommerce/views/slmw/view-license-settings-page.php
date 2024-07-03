<?php if ( ! defined( 'ABSPATH' ) ) {
    exit;} // Exit if accessed directly ?>

<div id="license-placeholder" class="agcfw-license-placeholder-settings-block">

    <div class="overview">
        <h2><?php esc_html_e( 'Advanced Gift Cards', 'advanced-gift-cards-for-woocommerce' ); ?></h2>
    </div>

    <div class="license-info">

        <div class="heading">
            <div class="left">
                <span><?php esc_html_e( 'Your current license for Advanced Gift Cards', 'advanced-gift-cards-for-woocommerce' ); ?></span>
            </div>
            <div class="right">
                <?php if ( 'yes' === $license_activated ) : ?>
                    <span class="action-button active-indicator no-hover license-active dashicons-before dashicons-yes-alt"><?php esc_html_e( 'License is Active', 'advanced-gift-cards-for-woocommerce' ); ?></span>
                <?php else : ?>
                    <span class="action-button active-indicator no-hover"><?php esc_html_e( 'Not Activated Yet', 'advanced-gift-cards-for-woocommerce' ); ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="content">
            <h2><?php esc_html_e( 'Premium Version', 'advanced-gift-cards-for-woocommerce' ); ?></h2>
            <p><?php esc_html_e( 'Advanced Gift Cards lets you sell redeemable digital gift cards on your WooCommerce store via a simple product listing. Gift Cards can then be redeemed for store credit that your customers can use towards orders. Activate your license key to enable continued support & updates for Advanced Gift Cards as well as access to premium features.', 'advanced-gift-cards-for-woocommerce' ); ?></p>

            <table class="license-specs">
                <tr>
                    <th><?php esc_html_e( 'Plan', 'advanced-gift-cards-for-woocommerce' ); ?></th>
                    <th><?php esc_html_e( 'Version', 'advanced-gift-cards-for-woocommerce' ); ?></th>
                </tr>
                <tr>
                    <td><?php esc_html_e( 'Advanced Gift Cards', 'advanced-gift-cards-for-woocommerce' ); ?></td>
                    <td><?php echo esc_html( $constants->VERSION ); ?></td>
                </tr>
            </table>
        </div>
       
        <div class="form">
            <div class="flex">
                <div class="form-field">
                    <label for="<?php echo esc_attr( $constants->OPTION_LICENSE_KEY ); ?>"><?php esc_html_e( 'License Key:', 'advanced-gift-cards-for-woocommerce' ); ?></label>
                    <input class="regular-text ltr" type="password" id="<?php echo esc_html( $constants->OPTION_LICENSE_KEY ); ?>" name="<?php echo esc_attr( $constants->OPTION_LICENSE_KEY ); ?>" value="<?php echo esc_attr( $license_key ); ?>" />
                </div>
                <div class="form-field">
                    <label for="<?php echo esc_attr( $constants->OPTION_ACTIVATION_EMAIL ); ?>"><?php esc_html_e( 'Activation Email:', 'advanced-gift-cards-for-woocommerce' ); ?></label>
                    <input class="regular-text ltr" type="email" id="<?php echo esc_attr( $constants->OPTION_ACTIVATION_EMAIL ); ?>" name="<?php echo esc_attr( $constants->OPTION_ACTIVATION_EMAIL ); ?>" value="<?php echo esc_attr( $activation_email ); ?>" />
                </div>
                <div class="form-field action">
                    <button class="action-button <?php echo 'yes' === $license_activated ? 'grayed' : ''; ?>" type="submit" name="save" value="<?php esc_attr_e( 'Activate Key', 'advanced-gift-cards-for-woocommerce' ); ?>"><?php esc_html_e( 'Activate Key', 'advanced-gift-cards-for-woocommerce' ); ?></button>
                </div>
            </div>
            <div class="help-row">
                <?php
                    /* translators: %s: Advanced Coupons site login URL */
                    echo wp_kses_post( sprintf( __( 'Can’t find your key? <a href="%s" target="_blank">Login to your account</a>.', 'advanced-gift-cards-for-woocommerce' ), 'https://advancedcouponsplugin.com/my-account/?utm_source=agcfw&utm_medium=license&utm_campaign=findkey' ) );
                ?>
            </div>
        </div>

        <div class="overlay"><img src="<?php echo esc_url( $constants->IMAGES_ROOT_URL . 'spinner-2x.gif' ); ?>" alt="spinner" /></div>
    </div>

</div>
