<?php if ( ! defined( 'ABSPATH' ) ) {
exit;} // Exit if accessed directly ?>

<style type="text/css">
    .agc-plugin-dependency-notice p {
        max-width: 1000px;
    }
    .agc-plugin-dependency-notice p:after {
        content: '';
        display: table;
        clear: both;
    }
    .agc-plugin-dependency-notice .heading img {
        float: left;
        margin-right: 15px;
        max-width: 190px;
    }
    .agc-plugin-dependency-notice .heading span {
        float: left;
        margin-top: 8px;
        font-size: 16px;
        font-weight: bold;
        text-transform: uppercase;
        color: #035E6B;
    }
    .agc-plugin-dependency-notice .action-wrap {
        margin-bottom: 15px;
    }
    .agc-plugin-dependency-notice .action-wrap .action-button {
        display: inline-block;
        padding: 8px 23px;
        margin-right: 10px;
        background: #C6CD2E;
        font-weight: bold;
        font-size: 16px;
        text-decoration: none;
        color: #000000;
    }
    .agc-plugin-dependency-notice .action-wrap .action-button.disabled {
        opacity: 0.7 !important;
        pointer-events: none;
    }
    .agc-plugin-dependency-notice .action-wrap .action-button.gray {
        background: #cccccc;
    }
    .agc-plugin-dependency-notice .action-wrap .action-button:hover {
        opacity: 0.8;
    }

    .agc-plugin-dependency-notice .action-wrap span {
        color: #035E6B;
    }
</style>

<?php if ( $acfwf_dependency ) : ?>
<div class="notice notice-error agc-plugin-dependency-notice acfwf">
    <p class="heading">
        <img src="<?php echo esc_url( $acfw_logo ); ?>">
        <span><?php esc_html_e( 'Important - please update Advanced Coupons free plugin', 'advanced-gift-cards-for-woocommerce' ); ?></span>
    </p>
    <p><?php esc_html_e( 'Thanks for installing Advanced Gift Cards. We know you’ll love it!', 'advanced-gift-cards-for-woocommerce' ); ?></p>
    <p><?php esc_html_e( "Advanced Gift Cards extends the free Advanced Coupons plugin with new features but we detected you're using an outdated version. Please update the Advanced Coupons free plugin to the latest version by clicking the button below.", 'advanced-gift-cards-for-woocommerce' ); ?></p>
    <p class="action-wrap">
        <a class="action-button" href="<?php echo esc_url( htmlspecialchars_decode( wp_nonce_url( 'update.php?action=upgrade-plugin&plugin=' . $acfwf_dependency['plugin-base-name'], 'upgrade-plugin_' . $acfwf_dependency['plugin-base-name'] ) ) ); ?>">
            <?php esc_html_e( 'Update Plugin', 'advanced-gift-cards-for-woocommerce' ); ?>
        </a>
    </p>
</div>
<?php endif; ?>

<?php if ( $admin_notice_msg ) : ?>
    <div class="notice notice-error agc-plugin-dependency-notice">
        <p class="heading">
            <img src="<?php echo esc_url( $acfw_logo ); ?>">
            <span><?php esc_html_e( 'Action required', 'advanced-gift-cards-for-woocommerce' ); ?></span>
        </p>
        <p><?php echo wp_kses_post( __( '<strong>Advanced Gift Cards for WooCommerce</strong> plugin invalid dependency version.', 'advanced-gift-cards-for-woocommerce' ) ); ?></p>
        <p><?php echo wp_kses_post( $admin_notice_msg ); ?></p>
    </div>
<?php endif; ?>
