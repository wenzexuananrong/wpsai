<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} ?>

<style type="text/css">
.agc-no-license-notice .activate-license-form {
    display: flex;
    flex-wrap: wrap;
    flex-flow: row;
    margin: 1em 0;
}

.agc-no-license-notice .activate-license-form .agc-form-field {
    display: flex;
    flex-flow: column;
    margin-right: 1em;
    width: 200px;
}

.agc-no-license-notice .activate-license-form .agc-form-field label {
    font-weight: bold;
    font-size: 0.9em;
}
.agc-no-license-notice .activate-license-form .agc-form-field input {
    padding: 0.3em 0.5em;
    height: auto;
    border-radius: 0;
}
.agc-no-license-notice .activate-license-form .agc-form-field button {
    padding: 0.3em 0.5em;
    height: auto;
    border-radius: 0;
    background: #1594a8;
    border-color: #097788;
    transition: all 0.3s ease;
}
.agc-no-license-notice .activate-license-form .agc-form-field button:hover {
    background: #097788;
    border-color: #097788;
}
.agc-extra-form-actions {
    margin-bottom: 1em;
}
.agc-extra-form-actions .agc-login-link {
    display: inline-block;
    font-style: italic;
    font-size: 0.9em;
}
</style>

<div class="notice notice-error <?php echo $is_dismissible ? 'is-dismissible' : ''; ?> agc-drm-notice agc-no-license-notice" data-id="no_license">
    <p class="heading">
        <img src="<?php echo esc_url( $this->_constants->IMAGES_ROOT_URL . 'acfw-logo.png' ); ?>" alt="ACFW logo" />
    </p>

    <?php if ( 7 > $days_passed ) : ?>
        <p><strong><?php esc_html_e( 'Oops! Did you forget to enter your license for Advanced Gift Cards?', 'advanced-gift-cards-for-woocommerce' ); ?></strong></p>
        <p><?php esc_html_e( 'Enter your license to fully activate Advanced Gift Cards and gain access to automatic updates, technical support and premium features.', 'advanced-gift-cards-for-woocommerce' ); ?></p>
    <?php else : ?>
        <p><strong>
            <em><?php esc_html_e( 'Action required!', 'advanced-gift-cards-for-woocommerce' ); ?></em> 
            <?php esc_html_e( 'Enter your license for Advanced Gift Cards to continue.', 'advanced-gift-cards-for-woocommerce' ); ?>
        </strong></p>
        <p>
        <?php
        echo wp_kses_post(
            sprintf(
                /* Translators: %1$s: opening link tag. $2$s: closing link tag. */
                __( 'Don’t worry, your customers’ gift card codes are completely safe and the advanced gift card features are still working. But you will need to enter a license key to continue using Advanced Gift Cards. If you don’t have a license, %1$splease purchase one to proceed.%2$s', 'advanced-gift-cards-for-woocommerce' ),
                '<a href="' . esc_url( $purchase_url ) . '" rel="noopener" target="_blank">',
                '</a>'
            )
        );
            ?>
            </p>
    <?php endif; ?>

    <form method="get" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
        <div class="activate-license-form">
            <div class="agc-form-field license-key-field">
                <label><?php esc_html_e( 'License Key:', 'advanced-gift-cards-for-woocommerce' ); ?></label>
                <input type="password" name="license-key" placeholder="<?php esc_attr_e( 'Enter your license key', 'advanced-gift-cards-for-woocommerce' ); ?>" required />
            </div>
            <div class="agc-form-field activation-email-field">
                <label><?php esc_html_e( 'License Email:', 'advanced-gift-cards-for-woocommerce' ); ?></label>
                <input type="email"  name="activation-email" placeholder="<?php esc_attr_e( 'Activate License', 'advanced-gift-cards-for-woocommerce' ); ?>" required />
            </div>
            <div class="agc-form-field license-form-item">
                <label>&nbsp;</label>
                <button class="button-primary" type="submit"><?php esc_html_e( 'Activate Key', 'advanced-gift-cards-for-woocommerce' ); ?></button>
            </div>
        </div>
        <div class="agc-extra-form-actions">
            <span class="agc-login-link">
            <?php
            echo wp_kses_post(
                sprintf(
                    '%1$s <a href="%2$s" target="_blank">%3$s</a>',
                    __( 'Can’t find your license key?', 'advanced-gift-cards-for-woocommerce' ),
                    $login_account_url,
                    __( 'Login to your account', 'advanced-gift-cards-for-woocommerce' )
                )
            );
            ?>
            </span>
        </div>
        <input type="hidden" name="action" value="agcfw_activate_license" />
        <input type="hidden" name="is_notice" value="1" />
        <?php wp_nonce_field( 'agcfw_activate_license', 'ajax-nonce' ); ?>
    </form>
</div>

<?php

do_action( 'acfw_after_no_license_notice' );
