<?php if ( ! defined( 'ABSPATH' ) ) {
exit;} // Exit if accessed directly ?>

<style type="text/css">
    .lpfw-plugin-dependency-notice p {
        max-width: 1000px;
    }
    .lpfw-plugin-dependency-notice p:after {
        content: '';
        display: table;
        clear: both;
    }
    .lpfw-plugin-dependency-notice .heading img {
        float: left;
        margin-right: 15px;
        max-width: 190px;
    }
    .lpfw-plugin-dependency-notice .heading span {
        float: left;
        margin-top: 8px;
        font-size: 16px;
        font-weight: bold;
        text-transform: uppercase;
        color: #035E6B;
    }
    .lpfw-plugin-dependency-notice .action-wrap {
        margin-bottom: 15px;
    }
    .lpfw-plugin-dependency-notice .action-wrap .action-button {
        display: inline-block;
        padding: 8px 23px;
        margin-right: 10px;
        background: #C6CD2E;
        font-weight: bold;
        font-size: 16px;
        text-decoration: none;
        color: #000000;
    }
    .lpfw-plugin-dependency-notice .action-wrap .action-button.disabled {
        opacity: 0.7 !important;
        pointer-events: none;
    }
    .lpfw-plugin-dependency-notice .action-wrap .action-button.gray {
        background: #cccccc;
    }
    .lpfw-plugin-dependency-notice .action-wrap .action-button:hover {
        opacity: 0.8;
    }

    .lpfw-plugin-dependency-notice .action-wrap span {
        color: #035E6B;
    }
</style>

<?php if ( $acfwf_dependency ) : ?>
<div class="notice notice-error lpfw-plugin-dependency-notice acfwf">
    <p class="heading">
        <img src="<?php echo esc_url( $acfw_logo ); ?>">
        <span><?php esc_html_e( 'Important - please update Advanced Coupons free plugin', 'loyalty-program-for-woocommerce' ); ?></span>
    </p>
    <p><?php esc_html_e( 'Thanks for installing Loyalty Program for WooCommerce. We know you’ll love it!', 'loyalty-program-for-woocommerce' ); ?></p>
    <p><?php esc_html_e( "Loyalty Program extends the free Advanced Coupons plugin with loyalty features but we detected you're using an outdated version. Please update the Advanced Coupons free plugin to the latest version by clicking the button below.", 'loyalty-program-for-woocommerce' ); ?></p>
    <p class="action-wrap">
    <a class="action-button" href="<?php echo esc_url( htmlspecialchars_decode( $this->Helper_Functions->get_plugin_dependency_install_url( $acfwf_dependency['plugin-base-name'], true ) ) ); ?>">
            <?php esc_html_e( 'Update Plugin', 'loyalty-program-for-woocommerce' ); ?>
        </a>
    </p>
</div>
<?php endif; ?>

<?php if ( $admin_notice_msg ) : ?>
    <div class="notice notice-error lpfw-plugin-dependency-notice">
        <p class="heading">
            <img src="<?php echo esc_url( $acfw_logo ); ?>">
            <span><?php esc_html_e( 'Action required', 'loyalty-program-for-woocommerce' ); ?></span>
        </p>
        <p><?php echo wp_kses_post( __( '<strong>Loyalty Program for WooCommerce</strong> plugin invalid dependency version.', 'loyalty-program-for-woocommerce' ) ); ?></p>
        <p><?php echo wp_kses_post( $admin_notice_msg ); ?></p>
    </div>
<?php endif; ?>
