<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} ?>

<div class="notice notice-error <?php echo $is_dismissible ? 'is-dismissible' : ''; ?> agc-drm-notice agc-license-post-expire-notice" data-id="license_expired">
    <p class="heading">
        <img src="<?php echo esc_url( $this->_constants->IMAGES_ROOT_URL . 'acfw-logo.png' ); ?>" alt="ACFW logo" />
    </p>
    <p><strong><?php esc_html_e( 'Oh no! Your Advanced Gift Cards license has expired!', 'advanced-gift-cards-for-woocommerce' ); ?></strong></p>
    <p>
    <?php
    echo wp_kses_post(
        sprintf(
            /* Translators: %s: Expiry extended date. */
            __( 'Don’t worry, your customers’ points are completely safe and the advanced gift card features are still working. We’ve also extended premium feature functionality until %s, at which point functionality will become limited.', 'advanced-gift-cards-for-woocommerce' ),
            $expiry_extended_date->date_i18n( get_option( 'date_format', 'F j, Y' ) )
        )
    );
    ?>
    </p>
    <p><?php esc_html_e( 'Renew your Advanced Gift Cards license now to continue receiving automatic updates, technical support, and access to the Advanced Gift Cards features.', 'advanced-gift-cards-for-woocommerce' ); ?></p>

    <div class="agc-notice-actions">
        <a href="<?php echo esc_url( $renew_license_url ); ?>" class="button button-primary" rel="noopener" target="_blank"><?php esc_html_e( 'Renew License', 'advanced-gift-cards-for-woocommerce' ); ?></a>
        <a href="<?php echo esc_url( $learn_more_url ); ?>" rel="noopener" target="_blank"><?php echo esc_html_e( 'Learn more', 'advanced-gift-cards-for-woocommerce' ); ?></a>
    </div>
</div>

<?php

do_action( 'acfw_after_license_post_expired_notice' );
