<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} ?>

<div class="notice notice-error <?php echo $is_dismissible ? 'is-dismissible' : ''; ?> agc-drm-notice agc-license-disabled-failed-renew-notice" data-id="license-disabled-failed-renew">
    <p class="heading">
        <img src="<?php echo esc_url( $this->_constants->IMAGES_ROOT_URL . 'acfw-logo.png' ); ?>" alt="ACFW logo" />
    </p>

    <?php if ( 7 > $days_passed ) : ?>
        <p><strong><?php esc_html_e( 'Oh no! Your Advanced Gift Cards license has failed to renew!', 'advanced-gift-cards-for-woocommerce' ); ?></strong></p>
        <p>
        <?php
            echo wp_kses_post(
                sprintf(
                /* Translators: %s: Expiration extended date. */
                    __( 'Don’t worry, your customer’s gift card codes and the gift card premium features are still working. We’ve also extended premium feature functionality until %s, at which point functionality will become limited.', 'advanced-gift-cards-for-woocommerce' ),
                    $extended_date
                )
            );
        ?>
        </p>
    <?php elseif ( 7 <= $days_passed ) : ?>
        <p><strong>
            <em><?php esc_html_e( 'Action required!', 'advanced-gift-cards-for-woocommerce' ); ?></em> 
            <?php esc_html_e( 'Your license has failed to renew and is now disabled.', 'advanced-gift-cards-for-woocommerce' ); ?>
        </strong></p>
        <p><?php esc_html_e( 'An active Advanced Gift Cards license is required to continue receiving automatic updates, technical support, and access to the Advanced Gift Cards premium features.', 'advanced-gift-cards-for-woocommerce' ); ?></p>
        <p><?php esc_html_e( 'Login to your Advanced Coupons account to correct this issue and renew your license.', 'advanced-gift-cards-for-woocommerce' ); ?></p>
    <?php endif; ?>

    <div class="agc-notice-actions">
        <a href="<?php echo esc_url( $renew_license_url ); ?>" class="button button-primary" rel="noopener" target="_blank"><?php esc_html_e( 'Renew License', 'advanced-gift-cards-for-woocommerce' ); ?></a>
        <a href="<?php echo esc_url( $learn_more_url ); ?>" rel="noopener" target="_blank"><?php echo esc_html_e( 'Learn more', 'advanced-gift-cards-for-woocommerce' ); ?></a>
    </div>
        
</div>

<?php
do_action( 'acfw_after_license_disabled_failed_renew_notice' );
