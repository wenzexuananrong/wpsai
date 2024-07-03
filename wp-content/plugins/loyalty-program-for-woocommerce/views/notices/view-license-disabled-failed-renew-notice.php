<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} ?>

<div class="notice notice-error <?php echo $is_dismissible ? 'is-dismissible' : ''; ?> lpfw-drm-notice lpfw-license-disabled-failed-renew-notice" data-id="license-disabled-failed-renew">
    <p class="heading">
        <img src="<?php echo esc_url( $this->_constants->IMAGES_ROOT_URL . 'acfw-logo.png' ); ?>" alt="ACFW logo" />
    </p>

    <?php if ( 7 > $days_passed ) : ?>
        <p><strong><?php esc_html_e( 'Oh no! Your Loyalty Program license has failed to renew!', 'loyalty-program-for-woocommerce' ); ?></strong></p>
        <p>
        <?php
            echo wp_kses_post(
                sprintf(
                /* Translators: %s: Expiration extended date. */
                    __( 'Don’t worry, your coupons & orders are completely safe and the loyalty program premium features are still working. We’ve also extended premium feature functionality until %s, at which point functionality will become limited.', 'loyalty-program-for-woocommerce' ),
                    $extended_date
                )
            );
        ?>
        </p>
    <?php elseif ( 7 <= $days_passed ) : ?>
        <p><strong>
            <em><?php esc_html_e( 'Action required!', 'loyalty-program-for-woocommerce' ); ?></em> 
            <?php esc_html_e( 'Your license has failed to renew and is now disabled.', 'loyalty-program-for-woocommerce' ); ?>
        </strong></p>
        <p><?php esc_html_e( 'An active Loyalty Program license is required to continue receiving automatic updates, technical support, and access to the Loyalty Program premium features.', 'loyalty-program-for-woocommerce' ); ?></p>
        <p><?php esc_html_e( 'Login to your Advanced Coupons account to correct this issue and renew your license.', 'loyalty-program-for-woocommerce' ); ?></p>
    <?php endif; ?>

    <div class="lpfw-notice-actions">
        <a href="<?php echo esc_url( $renew_license_url ); ?>" class="button button-primary" rel="noopener" target="_blank"><?php esc_html_e( 'Renew License', 'loyalty-program-for-woocommerce' ); ?></a>
        <a href="<?php echo esc_url( $learn_more_url ); ?>" rel="noopener" target="_blank"><?php echo esc_html_e( 'Learn more', 'loyalty-program-for-woocommerce' ); ?></a>
    </div>
        
</div>

<?php
do_action( 'acfw_after_license_disabled_failed_renew_notice' );
