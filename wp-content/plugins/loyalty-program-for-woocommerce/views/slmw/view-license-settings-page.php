<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div id="license-placeholder" class="acfwf-license-placeholder-settings-block">

    <div class="overview">
        <h2><?php esc_html_e( 'Loyalty Program', 'loyalty-program-for-woocommerce' ); ?></h2>
    </div>

    <div class="license-info">

        <div class="heading">
            <div class="left">
                <span><?php esc_html_e( 'Your current license for Loyalty Program:', 'loyalty-program-for-woocommerce' ); ?></span>
            </div>
            <div class="right">
                <?php if ( 'yes' === $license_activated ) : ?>
                    <span class="action-button active-indicator no-hover license-active dashicons-before dashicons-yes-alt"><?php esc_html_e( 'License is Active', 'loyalty-program-for-woocommerce' ); ?></span>
                <?php else : ?>
                    <span class="action-button active-indicator no-hover"><?php esc_html_e( 'Not Activated Yet', 'loyalty-program-for-woocommerce' ); ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="content">

            <p><?php esc_html_e( 'You are currently using Loyalty Program for WooCommerce by Advanced Coupons. In order to get future updates, bug fixes, and security patches automatically you will need to activate your license. This also allows you to claim support from our support team. Please enter your license details and activate your key.', 'loyalty-program-for-woocommerce' ); ?></p>

            <table class="license-specs">
                <tr>
                    <th><?php esc_html_e( 'Plan', 'loyalty-program-for-woocommerce' ); ?></th>
                    <th><?php esc_html_e( 'Version', 'loyalty-program-for-woocommerce' ); ?></th>
                </tr>
                <tr>
                    <td><?php esc_html_e( 'Loyalty Program', 'loyalty-program-for-woocommerce' ); ?></td>
                    <td><?php echo esc_html( $constants->VERSION ); ?></td>
                </tr>
            </table>
        </div>

        <div class="form">
            <div class="flex">
                <div class="form-field">
                    <label for="<?php echo esc_attr( $constants->OPTION_LICENSE_KEY ); ?>"><?php esc_html_e( 'License Key:', 'loyalty-program-for-woocommerce' ); ?></label>
                    <input class="regular-text ltr" type="password" id="<?php echo esc_attr( $constants->OPTION_LICENSE_KEY ); ?>" name="<?php echo esc_attr( $constants->OPTION_LICENSE_KEY ); ?>" value="<?php echo esc_attr( $license_key ); ?>" />
                </div>
                <div class="form-field">
                    <label for="<?php echo esc_attr( $constants->OPTION_ACTIVATION_EMAIL ); ?>"><?php esc_html_e( 'Activation Email:', 'loyalty-program-for-woocommerce' ); ?></label>
                    <input class="regular-text ltr" type="email" id="<?php echo esc_attr( $constants->OPTION_ACTIVATION_EMAIL ); ?>" name="<?php echo esc_attr( $constants->OPTION_ACTIVATION_EMAIL ); ?>" value="<?php echo esc_attr( $activation_email ); ?>" />
                </div>
                <div class="form-field action">
                    <button class="action-button <?php echo 'yes' === $license_activated ? 'grayed' : ''; ?>" type="submit" name="save" value="<?php esc_attr_e( 'Activate Key', 'loyalty-program-for-woocommerce' ); ?>"><?php esc_html_e( 'Activate Key', 'loyalty-program-for-woocommerce' ); ?></button>
                </div>
            </div>
            <div class="help-row">
                <?php
                echo wp_kses_post(
                    sprintf(
                        /* Translators: %s: Advanced coupons account page login URL. */
                        __( 'Can’t find your key? <a href="%s" target="_blank">Login to your account</a>.', 'loyalty-program-for-woocommerce' ),
                        'https://advancedcouponsplugin.com/my-account/?utm_source=lpfw&utm_medium=license&utm_campaign=findkey'
                    )
                );
                ?>
            </div>
        </div>

        <div class="overlay"><img src="<?php echo esc_url( $constants->IMAGES_ROOT_URL . 'spinner-2x.gif' ); ?>" alt="spinner" /></div>
    </div>

</div>
