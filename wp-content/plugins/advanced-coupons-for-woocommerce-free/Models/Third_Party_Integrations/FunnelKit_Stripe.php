<?php
namespace ACFWF\Models\Third_Party_Integrations;

use ACFWF\Abstracts\Abstract_Main_Plugin_Class;
use ACFWF\Abstracts\Base_Model;
use ACFWF\Helpers\Helper_Functions;
use ACFWF\Helpers\Plugin_Constants;
use ACFWF\Interfaces\Model_Interface;
use ACFWF\Interfaces\Activatable_Interface;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Model that houses the logic of the FunnelKit_Stripe module.
 *
 * @since 4.6.1
 */
class FunnelKit_Stripe extends Base_Model implements Model_Interface, Activatable_Interface {
    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
     */

    /**
     * Class constructor.
     *
     * @since 4.6.1
     * @access public
     *
     * @param Abstract_Main_Plugin_Class $main_plugin      Main plugin object.
     * @param Plugin_Constants           $constants        Plugin constants object.
     * @param Helper_Functions           $helper_functions Helper functions object.
     */
    public function __construct( Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants, Helper_Functions $helper_functions ) {
        parent::__construct( $main_plugin, $constants, $helper_functions );
        $main_plugin->add_to_all_plugin_models( $this );
    }

    /**
     * Register funnelkit stripe promote notice.
     *
     * @since 4.6.1
     * @access public
     *
     * @param array $notice_options List of notice options.
     * @return array Filtered list of notice options.
     */
    public function register_funnelkit_stripe_promote_notice( $notice_options ) {
        $notice_options['promote_funnelkit_stripe'] = Plugin_Constants::SHOW_FUNNELKIT_STRIPE_PROMOTE_NOTICE;
        return $notice_options;
    }

    /**
     * Register funnelkit stripe replacement notice data.
     *
     * @since 4.6.1
     * @access public
     *
     * @param array|null $notice_data Notice data.
     * @param string     $notice_key  Notice key.
     * @return array|null Filtered notice data.
     */
    public function register_funnelkit_stripe_promote_notice_data( $notice_data, $notice_key ) {
        if ( 'promote_funnelkit_stripe' === $notice_key ) {

            $basename = plugin_basename( 'funnelkit-stripe-woo-payment-gateway/funnelkit-stripe-woo-payment-gateway.php' );

            // If funnelkit stripe plugin is already active then return null.
            if ( $this->_helper_functions->is_plugin_active( $basename ) ) {
                return null;
            }

            $heading = __( 'ACCEPT PAYMENTS VIA STRIPE', 'advanced-coupons-for-woocommerce-free' );
            $content = array(
                __( 'The free <strong>Stripe Payment Gateway for WooCommerce plugin</strong> by FunnelKit is available and will let you accept credit card, Google Pay, and Apple Pay payments on your store. It also supports product upselling during checkout via FunnelKit\'s Funnel Builder plugin. This plugin has been tested and works well with Advanced Coupons - click here to install FunnelKit\'s Stripe plugin and start accepting payments now.', 'advanced-coupons-for-woocommerce-free' ),
            );

            // If WooCommerce Stripe Gateway plugin is active then override the heading and content notice data.
            if ( $this->_helper_functions->is_plugin_active( 'woocommerce-gateway-stripe/woocommerce-gateway-stripe.php' ) ) {
                $heading = __( 'STRIPE REPLACEMENT AVAILABLE', 'advanced-coupons-for-woocommerce-free' );
                $content = array(
                    __( 'We detected that you are using the <strong>WooCommerce Stripe Gateway plugin</strong>. A drop in replacement plugin, <strong>Stripe Payment Gateway for WooCommerce</strong> plugin by FunnelKit is available. This new Stripe plugin has better express payment support, accepts all cards, Google Pay, and Apple Pay. It supports product upselling during checkout via FunnelKit\'s Funnel Builder plugin. This plugin has been tested and works well with Advanced Coupons - click here to install now.', 'advanced-coupons-for-woocommerce-free' ),
                );
            }

            $query       = http_build_query(
                array(
                    'action'      => 'acfw_install_activate_plugin',
                    'plugin_slug' => 'funnelkit-stripe-woo-payment-gateway',
                    'nonce'       => wp_create_nonce( 'acfw_install_plugin' ),
                    'silent'      => true,
                    'redirect'    => 1,
                ),
            );
            $notice_link = admin_url( 'admin-ajax.php?' . $query );

            $notice_data = array(
                'slug'           => 'promote_funnelkit_stripe',
                'id'             => Plugin_Constants::SHOW_FUNNELKIT_STRIPE_PROMOTE_NOTICE,
                'logo_img'       => $this->_constants->IMAGES_ROOT_URL . '/acfw-logo.png',
                'is_dismissable' => true,
                'type'           => 'success',
                'heading'        => $heading,
                'content'        => $content,
                'actions'        => array(
                    array(
                        'key'  => 'primary',
                        'link' => $notice_link,
                        'text' => __( 'Install & Activate Free Plugin →', 'advanced-coupons-for-woocommerce-free' ),
                    ),
                ),
            );
        }

        return $notice_data;
    }

    /**
     * Update funnelkit partner key after install and activate plugin.
     *
     * @since 4.6.1
     * @access public
     *
     * @param string $plugin_slug Filtered plugin slug.
     * @param string $result Filtered result install activate plugin.
     */
    public function update_funnelkit_stripe_promote_after_install_activate_plugin( $plugin_slug, $result ) {
        // If plugin is not funnel kit stripe, then return.
        if ( 'funnelkit-stripe-woo-payment-gateway' !== $plugin_slug ) {
            return;
        }

        // Set partner key if the result is true. We must check if the result is boolean, because it could be that the result is an array.
        if ( is_bool( $result ) && true === $result ) {
            update_option( 'fkwcs_wp_stripe', '51c012eccfe7b12df7e51be418fab892', false );
            update_option( Plugin_Constants::SHOW_FUNNELKIT_STRIPE_PROMOTE_NOTICE, 'dismissed' );
        }
    }

    /**
     * Schedule funnelkit stripe promote cron notice.
     *
     * @since 4.6.1
     * @access private
     */
    public function _schedule_funnelkit_stripe_promote() {
        // Skip if the funnelkit stripe promote is already scheduled or funnelkit stripe is already installed.
        if ( \WC()->queue()->get_next( Plugin_Constants::SHOW_FUNNELKIT_STRIPE_PROMOTE_NOTICE, array(), Plugin_Constants::SHOW_FUNNELKIT_STRIPE_PROMOTE_NOTICE ) instanceof \WC_DateTime
            || $this->_helper_functions->is_plugin_active( Plugin_Constants::FUNNELKIT_STRIPE ) ) {
            return;
        }

        // Schedule the funnelkit stripe prmote for 45 days.
        \WC()->queue()->schedule_single(
            time() + ( 45 * DAY_IN_SECONDS ),
            Plugin_Constants::SHOW_FUNNELKIT_STRIPE_PROMOTE_NOTICE,
            array(),
            Plugin_Constants::SHOW_FUNNELKIT_STRIPE_PROMOTE_NOTICE
        );
    }

    /**
     * Show funnelkit stripe promote notice.
     *
     * @since 4.6.1
     * @access public
     */
    public function show_funnelkit_stripe_promote_notice() {
        update_option( Plugin_Constants::SHOW_FUNNELKIT_STRIPE_PROMOTE_NOTICE, 'yes' );
    }

    /*
    |--------------------------------------------------------------------------
    | Fulfill implemented interface contracts
    |--------------------------------------------------------------------------
     */

    /**
     * Execute codes that needs to run plugin activation.
     *
     * @since 4.6.1
     * @access public
     * @implements ACFWF\Interfaces\Activatable_Interface
     */
    public function activate() {
        $this->_schedule_funnelkit_stripe_promote();
    }

    /**
     * Execute FunnelKit_Stripe class.
     *
     * @since 4.6.1
     * @access public
     * @inherit ACFWF\Interfaces\Model_Interface
     */
    public function run() {
        add_filter( 'acfw_admin_notice_option_names', array( $this, 'register_funnelkit_stripe_promote_notice' ) );
        add_filter( 'acfw_get_admin_notice_data', array( $this, 'register_funnelkit_stripe_promote_notice_data' ), 10, 2 );
        add_action( Plugin_Constants::SHOW_FUNNELKIT_STRIPE_PROMOTE_NOTICE, array( $this, 'show_funnelkit_stripe_promote_notice' ) );
        add_action( 'acfw_after_install_activate_plugin', array( $this, 'update_funnelkit_stripe_promote_after_install_activate_plugin' ), 10, 2 );
    }
}
