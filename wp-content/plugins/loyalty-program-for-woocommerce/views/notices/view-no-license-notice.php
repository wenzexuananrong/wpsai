<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} ?>

<style type="text/css">
.lpfw-no-license-notice .activate-license-form {
    display: flex;
    flex-wrap: wrap;
    flex-flow: row;
    margin: 1em 0;
}

.lpfw-no-license-notice .activate-license-form .lpfw-form-field {
    display: flex;
    flex-flow: column;
    margin-right: 1em;
    width: 200px;
}

.lpfw-no-license-notice .activate-license-form .lpfw-form-field label {
    font-weight: bold;
    font-size: 0.9em;
}
.lpfw-no-license-notice .activate-license-form .lpfw-form-field input {
    padding: 0.3em 0.5em;
    height: auto;
    border-radius: 0;
}
.lpfw-no-license-notice .activate-license-form .lpfw-form-field button {
    padding: 0.3em 0.5em;
    height: auto;
    border-radius: 0;
    background: #1594a8;
    border-color: #097788;
    transition: all 0.3s ease;
}
.lpfw-no-license-notice .activate-license-form .lpfw-form-field button:hover {
    background: #097788;
    border-color: #097788;
}
.lpfw-extra-form-actions {
    margin-bottom: 1em;
}
.lpfw-extra-form-actions .lpfw-login-link {
    display: inline-block;
    font-style: italic;
    font-size: 0.9em;
}
</style>

<div class="notice notice-error <?php echo $is_dismissible ? 'is-dismissible' : ''; ?> lpfw-drm-notice lpfw-no-license-notice" data-id="no_license">
    <p class="heading">
        <img src="<?php echo esc_url( $this->_constants->IMAGES_ROOT_URL . 'acfw-logo.png' ); ?>" alt="ACFW logo" />
    </p>

    <?php if ( 7 > $days_passed ) : ?>
        <p><strong><?php esc_html_e( 'Oops! Did you forget to enter your license for Loyalty Program?', 'loyalty-program-for-woocommerce' ); ?></strong></p>
        <p><?php esc_html_e( 'Enter your license to fully activate Loyalty Program and gain access to automatic updates, technical support and premium features.', 'loyalty-program-for-woocommerce' ); ?></p>
    <?php else : ?>
        <p><strong>
            <em><?php esc_html_e( 'Action required!', 'loyalty-program-for-woocommerce' ); ?></em> 
            <?php esc_html_e( 'Enter your license for Loyalty Program to continue.', 'loyalty-program-for-woocommerce' ); ?>
        </strong></p>
        <p>
        <?php
        echo wp_kses_post(
            sprintf(
                /* Translators: %1$s: opening link tag. $2$s: closing link tag. */
                __( 'Don’t worry, your customers’ points are completely safe and the loyalty program features are still working. But you will need to enter a license key to continue using Loyalty Program. If you don’t have a license, %1$splease purchase one to proceed.%2$s', 'loyalty-program-for-woocommerce' ),
                '<a href="' . esc_url( $purchase_url ) . '" rel="noopener" target="_blank">',
                '</a>'
            )
        );
            ?>
            </p>
    <?php endif; ?>

    <form method="get" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
        <div class="activate-license-form">
            <div class="lpfw-form-field license-key-field">
                <label><?php esc_html_e( 'License Key:', 'loyalty-program-for-woocommerce' ); ?></label>
                <input type="password" name="license-key" placeholder="<?php esc_attr_e( 'Enter your license key', 'loyalty-program-for-woocommerce' ); ?>" required />
            </div>
            <div class="lpfw-form-field activation-email-field">
                <label><?php esc_html_e( 'License Email:', 'loyalty-program-for-woocommerce' ); ?></label>
                <input type="email"  name="activation-email" placeholder="<?php esc_attr_e( 'Activate License', 'loyalty-program-for-woocommerce' ); ?>" required />
            </div>
            <div class="lpfw-form-field license-form-item">
                <label>&nbsp;</label>
                <button class="button-primary" type="submit"><?php esc_html_e( 'Activate Key', 'loyalty-program-for-woocommerce' ); ?></button>
            </div>
        </div>
        <div class="lpfw-extra-form-actions">
            <span class="lpfw-login-link">
            <?php
            echo wp_kses_post(
                sprintf(
                    '%1$s <a href="%2$s" target="_blank">%3$s</a>',
                    __( 'Can’t find your license key?', 'loyalty-program-for-woocommerce' ),
                    $login_account_url,
                    __( 'Login to your account', 'loyalty-program-for-woocommerce' )
                )
            );
            ?>
            </span>
        </div>
        <input type="hidden" name="action" value="lpfw_activate_license" />
        <input type="hidden" name="is_notice" value="1" />
        <?php wp_nonce_field( 'lpfw_activate_license', 'ajax-nonce' ); ?>
    </form>
</div>

<?php

do_action( 'acfw_after_no_license_notice' );
