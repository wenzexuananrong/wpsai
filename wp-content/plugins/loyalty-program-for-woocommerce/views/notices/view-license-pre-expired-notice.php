<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} ?>

<div class="notice notice-error <?php echo $is_dismissible ? 'is-dismissible' : ''; ?> lpfw-drm-notice lpfw-license-pre-expire-notice" data-id="license_pre_expired">
    <p class="heading">
        <img src="<?php echo esc_url( $this->_constants->IMAGES_ROOT_URL . 'acfw-logo.png' ); ?>" alt="ACFW logo" />
    </p>
    <p><strong>
        <em><?php esc_html_e( 'Action Required!', 'loyalty-program-for-woocommerce' ); ?></em>
        <?php
        echo wp_kses_post(
            sprintf(
                /* Translators: %s: days left for expiry */
                __( 'Your Loyalty Program license is about to expire in %s.', 'loyalty-program-for-woocommerce' ),
                /* Translators: %s: number of days */
                sprintf( '<em>%s</em> %s', $days_left, _n( 'day', 'days', $days_left, 'loyalty-program-for-woocommerce' ) )
            )
        );
        ?>
    </strong></p>
    <p>
    <?php
    echo wp_kses_post(
        sprintf(
            /* Translators: %1$s: days left for expiry. %2$s:  */
            __( 'Your Loyalty Program license is about to expire in %1$s and automatic renewals are turned off. The current license will expire on %2$s. Once expired, you won’t have access to the loyalty program features, plugin updates, or support. To avoid interruptions simply reactivate your subscription.', 'loyalty-program-for-woocommerce' ),
            /* Translators: %s: number of days */
            sprintf( _n( '%s day', '%s days', $days_left, 'loyalty-program-for-woocommerce' ), $days_left ),
            $expire_date
        )
    );
    ?>
    </p>

    <div class="lpfw-notice-actions">
        <a href="<?php echo esc_url( $login_reactivate_url ); ?>" class="button button-primary" rel="noopener" target="_blank"><?php esc_html_e( 'Login & Reactivate', 'loyalty-program-for-woocommerce' ); ?></a>
        <a href="<?php echo esc_url( $learn_more_url ); ?>" rel="noopener" target="_blank"><?php echo esc_html_e( 'Learn more', 'loyalty-program-for-woocommerce' ); ?></a>
    </div>
</div>

<?php

do_action( 'acfw_after_license_pre_expired_notice' );
