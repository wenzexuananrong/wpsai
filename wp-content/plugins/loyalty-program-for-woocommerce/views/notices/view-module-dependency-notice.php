<?php if ( ! defined( 'ABSPATH' ) ) {
exit;} // Exit if accessed directly ?>

<style type="text/css">
    .lpfw-module-dependency-notice p {
        max-width: 1000px;
    }
    .lpfw-module-dependency-notice p:after {
        content: '';
        display: table;
        clear: both;
    }
    .lpfw-module-dependency-notice .heading img {
        float: left;
        margin-right: 15px;
        max-width: 190px;
    }
    .lpfw-module-dependency-notice .heading span {
        float: left;
        margin-top: 8px;
        font-size: 16px;
        font-weight: bold;
        text-transform: uppercase;
        color: #035E6B;
    }
    .lpfw-module-dependency-notice .action-wrap {
        margin-bottom: 15px;
    }
    .lpfw-module-dependency-notice .action-wrap .action-button {
        display: inline-block;
        padding: 8px 23px;
        margin-right: 10px;
        background: #C6CD2E;
        font-weight: bold;
        font-size: 16px;
        text-decoration: none;
        color: #000000;
    }
    .lpfw-module-dependency-notice .action-wrap .action-button.disabled {
        opacity: 0.7 !important;
        pointer-events: none;
    }
    .lpfw-module-dependency-notice .action-wrap .action-button.gray {
        background: #cccccc;
    }
    .lpfw-module-dependency-notice .action-wrap .action-button:hover {
        opacity: 0.8;
    }

    .lpfw-module-dependency-notice .action-wrap span {
        color: #035E6B;
    }
</style>

<div class="notice notice-error lpfw-module-dependency-notice">
    <p class="heading">
        <img src="<?php echo esc_url( $acfw_logo ); ?>">
        <span><?php esc_html_e( 'Action required', 'loyalty-program-for-woocommerce' ); ?></span>
    </p>
    <p><?php esc_html_e( 'Thanks for installing the Loyalty Program add-on for Advanced Coupons. We know you’ll love it!', 'loyalty-program-for-woocommerce' ); ?></p>
    <p><?php esc_html_e( 'This plugin requires the Store Credits module to be enabled. Please click the button below to enable it.', 'loyalty-program-for-woocommerce' ); ?></p>
    <p class="action-wrap">
        <a class="action-button" href="<?php echo esc_url( admin_url( 'admin.php?page=acfw-settings&section=modules_section' ) ); ?>">
            <?php esc_html_e( 'Enable Store Credits', 'loyalty-program-for-woocommerce' ); ?>
        </a>
    </p>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    $('.lpfw-module-dependency-notice').on('click', '.action-button', function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: wpApiSettings.root + 'coupons/v1/settings/acfw_store_credits_module',
            data: {value: "yes", type: "module"},
            dataType: "json",
            headers: { "X-WP-Nonce": wpApiSettings.nonce, "X-ACFW-Context": "admin" }
        }).done(function() {
            window.location.reload();
        });
    });
});
</script>
