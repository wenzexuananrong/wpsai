<?php
namespace AGCFW\Models\SLMW;

use AGCFW\Abstracts\Abstract_Main_Plugin_Class;

use AGCFW\Interfaces\Model_Interface;
use AGCFW\Interfaces\Initiable_Interface;

use AGCFW\Helpers\Plugin_Constants;
use AGCFW\Helpers\Helper_Functions;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Model that houses the logic for handling License activation.
 * It houses the logic of handling coupon url.
 * Public Model.
 *
 * @since 1.0
 */
class License implements Model_Interface, Initiable_Interface {
    /*
    |--------------------------------------------------------------------------
    | Class Properties
    |--------------------------------------------------------------------------
    */

    /**
     * Property that holds the single main instance of URL_Coupon.
     *
     * @since 1.0
     * @access private
     * @var License
     */
    private static $_instance;

    /**
     * Model that houses all the plugin constants.
     *
     * @since 1.0
     * @access private
     * @var Plugin_Constants
     */
    private $_constants;

    /**
     * Property that houses all the helper functions of the plugin.
     *
     * @since 1.0
     * @access private
     * @var Helper_Functions
     */
    private $_helper_functions;

    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Class constructor.
     *
     * @since 1.0
     * @access public
     *
     * @param Abstract_Main_Plugin_Class $main_plugin      Main plugin object.
     * @param Plugin_Constants           $constants        Plugin constants object.
     * @param Helper_Functions           $helper_functions Helper functions object.
     */
    public function __construct( Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants, Helper_Functions $helper_functions ) {

        $this->_constants        = $constants;
        $this->_helper_functions = $helper_functions;

        $main_plugin->add_to_all_plugin_models( $this );
    }

    /**
     * Ensure that only one instance of this class is loaded or can be loaded ( Singleton Pattern ).
     *
     * @since 1.0
     * @access public
     *
     * @param Abstract_Main_Plugin_Class $main_plugin      Main plugin object.
     * @param Plugin_Constants           $constants        Plugin constants object.
     * @param Helper_Functions           $helper_functions Helper functions object.
     * @return License
     */
    public static function get_instance( Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants, Helper_Functions $helper_functions ) {

        if ( ! self::$_instance instanceof self ) {
            self::$_instance = new self( $main_plugin, $constants, $helper_functions );
        }

        return self::$_instance;
    }

    /**
     * Activate license notice.
     *
     * @since 1.0
     * @since 2.1 only show notice for at least administrator role.
     * @access public
     */
    public function activate_license_notice() {

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        if ( is_multisite() ) {

            $license_activated = get_site_option( $this->_constants->OPTION_LICENSE_ACTIVATED );
            $notice_dismissed  = get_site_option( 'agcfw_slmw_active_notice_dismissed' );

        } else {

            $license_activated = get_option( $this->_constants->OPTION_LICENSE_ACTIVATED );
            $notice_dismissed  = get_option( 'agcfw_slmw_active_notice_dismissed' );

        }

        if ( 'yes' !== $license_activated && 'yes' !== $notice_dismissed ) {

            global $wp;

            if ( is_multisite() ) {

                $current_url             = add_query_arg( $wp->query_string, '?', network_home_url( $wp->request ) );
                $agcfw_slmw_settings_url = $current_url . 'wp-admin/network/admin.php?page=advanced-coupons&tab=agc_license';

            } else {
                $agcfw_slmw_settings_url = admin_url( 'admin.php?page=acfw-license&tab=AGC' );
            }

            ?>

            <div class="notice notice-error is-dismissible agcfw-activate-license-notice">
                <h4 class="agcfw-activate-license-notice">
                    <?php
                    echo wp_kses_post(
                        sprintf(
                        /* translators: $1$s is the license settings URL */
                            __( 'Please <a href="%1$s">activate</a> your copy of Advanced Gift Cards For WooCommerce to get the latest updates and have access to support.', 'advanced-gift-cards-for-woocommerce' ),
                            $agcfw_slmw_settings_url
                        )
                    );
                    ?>
                </h4>
            </div>
           
            <style>.agcfw-activate-license-notice .notice-dismiss { margin-top: 8px; }</style>
            <script>
                jQuery( document ).ready( function($){
                    $( '.agcfw-activate-license-notice' ).on( 'click' , '.notice-dismiss' , function() {
                        $.post( window.ajaxurl, { action : 'agcfw_slmw_dismiss_activate_notice' } );
                    } );
                });
            </script>

            <?php
        }
    }

    /**
     * AJAX activate license for this site.
     *
     * @since 1.0
     * @since 1.1.2 Add license status in the response.
     * @access public
     */
    public function ajax_activate_license() {

        if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
            $response = array(
				'status'    => 'fail',
				'error_msg' => __( 'Invalid AJAX Operation', 'advanced-gift-cards-for-woocommerce' ),
            );
        } elseif ( ! isset( $_POST['activation-email'] ) || ! isset( $_POST['license-key'] ) || ! isset( $_POST['ajax-nonce'] ) ) {
            $response = array(
				'status'    => 'fail',
				'error_msg' => __( 'Required parameters not supplied', 'advanced-gift-cards-for-woocommerce' ),
            );
        } elseif ( ! check_ajax_referer( 'agcfw_activate_license', 'ajax-nonce', false ) ) {
            $response = array(
				'status'    => 'fail',
				'error_msg' => __( 'Security check failed', 'advanced-gift-cards-for-woocommerce' ),
            );
        } else {

            $activation_email = trim( sanitize_email( wp_unslash( $_POST['activation-email'] ) ) );
            $license_key      = trim( sanitize_text_field( wp_unslash( $_POST['license-key'] ) ) );
            $activation_url   = add_query_arg(
                array(
					'activation_email' => urlencode( $activation_email ), // phpcs:ignore
					'license_key'      => $license_key,
					'site_url'         => home_url(),
					'software_key'     => $this->_constants->SOFTWARE_KEY,
					'multisite'        => is_multisite() ? 1 : 0,
                ),
                apply_filters( 'agcfw_license_activation_url', $this->_constants->LICENSE_ACTIVATION_URL )
            );

            // Store data even if not valid license.
            if ( is_multisite() ) {

                update_site_option( $this->_constants->OPTION_ACTIVATION_EMAIL, $activation_email );
                update_site_option( $this->_constants->OPTION_LICENSE_KEY, $license_key );

            } else {

                update_option( $this->_constants->OPTION_ACTIVATION_EMAIL, $activation_email );
                update_option( $this->_constants->OPTION_LICENSE_KEY, $license_key );

            }

            $option = array(
                'timeout' => 10, // seconds.
                'headers' => array( 'Accept' => 'application/json' ),
            );

            $result = wp_remote_retrieve_body( wp_remote_get( $activation_url, $option ) );

            if ( empty( $result ) ) {
                $response = array(
					'status'    => 'fail',
					'error_msg' => __( 'Failed to activate license. Failed to connect to activation access point. Please contact plugin support.', 'advanced-gift-cards-for-woocommerce' ),
                );
            } else {

                $result = json_decode( $result );

                if ( empty( $result ) || ! property_exists( $result, 'status' ) ) {
                    $response = array(
						'status'    => 'fail',
						'error_msg' => __( 'Failed to activate license. Activation access point return invalid response. Please contact plugin support.', 'advanced-gift-cards-for-woocommerce' ),
                    );
                } elseif ( 'success' === $result->status ) {

                        if ( is_multisite() ) {
                            update_site_option( $this->_constants->OPTION_LICENSE_ACTIVATED, 'yes' );
                        } else {
                            update_option( $this->_constants->OPTION_LICENSE_ACTIVATED, 'yes' );
                        }

                        $response = array(
							'status'      => $result->status,
							'success_msg' => $result->success_msg ?? '',
                        );

                    } else {

                        if ( is_multisite() ) {
                            update_site_option( $this->_constants->OPTION_LICENSE_ACTIVATED, 'no' );
                        } else {
                            update_option( $this->_constants->OPTION_LICENSE_ACTIVATED, 'no' );
                        }

                        $response = array(
							'status'    => $result->status,
							'error_msg' => $result->error_msg,
                        );

                        // Remove any locally stored update data if there are any.
                        $wp_site_transient = get_site_transient( 'update_plugins' );

                        if ( $wp_site_transient ) {

                            $agcfw_plugin_basename = 'advanced-gift-cards-for-woocommerce/advanced-gift-cards-for-woocommerce.php';

                            if ( isset( $wp_site_transient->checked ) && is_array( $wp_site_transient->checked ) && array_key_exists( $agcfw_plugin_basename, $wp_site_transient->checked ) ) {
                                unset( $wp_site_transient->checked[ $agcfw_plugin_basename ] );
                            }

                            if ( isset( $wp_site_transient->response ) && is_array( $wp_site_transient->response ) && array_key_exists( $agcfw_plugin_basename, $wp_site_transient->response ) ) {
                                unset( $wp_site_transient->response[ $agcfw_plugin_basename ] );
                            }

                            set_site_transient( 'update_plugins', $wp_site_transient );

                            wp_update_plugins();

                        }
                }
            }

            // set license status based on response.
            $response['license_status'] = $this->_get_license_status_based_on_slmw_response( (array) $result );
        }

        // if license status is not set, then we set it as empty value.
        if ( ! isset( $response['license_status'] ) ) {
            $response['license_status'] = '';
        }

        @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) ); // phpcs:ignore
        echo wp_json_encode( $response );
        wp_die();
    }

    /**
     * Get license status based on SLMW response
     *
     * @since 1.1.2
     * @access private
     *
     * @param array $result SLMW response.
     * @return string License status.
     */
    private function _get_license_status_based_on_slmw_response( $result ) {
        // return empty if the response data has no status prop.
        if ( ! is_array( $result ) || ! isset( $result['status'] ) ) {
            return '';
        }

        // handle failed response.
        if ( 'fail' === $result['status'] ) {

            // return as expired when the expiration keys are present.
            if ( isset( $result['expiration_timestamp'] ) || isset( $result['expired_date'] ) ) {
                return 'expired';
            }

            return 'inactive';
        }

        return 'active';
    }

    /**
     * AJAX dismiss activate notice.
     *
     * @since 1.0
     * @since 1.8 Refactor and improve support for multisite setup.
     * @access public
     */
    public function ajax_dismiss_activate_notice() {

        if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
            $response = array(
				'status'    => 'fail',
				'error_msg' => __( 'Invalid AJAX Operation', 'advanced-gift-cards-for-woocommerce' ),
            );
        } else {

            if ( is_multisite() ) {
                update_site_option( 'agcfw_slmw_active_notice_dismissed', 'yes' );
            } else {
                update_option( 'agcfw_slmw_active_notice_dismissed', 'yes' );
            }

            $response = array( 'status' => 'success' );

        }

        @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) ); // phpcs:ignore
        echo wp_json_encode( $response );
        wp_die();
    }

    /**
     * AJAX get license details.
     *
     * @since 2.2
     * @access public
     */
    public function ajax_get_license_details() {

        if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
            $response = array(
				'status'    => 'fail',
				'error_msg' => __( 'Invalid AJAX Operation', 'advanced-gift-cards-for-woocommerce' ),
            );
        } else {

            $response = array(
                'status'      => 'success',
                'license_key' => get_option( $this->_constants->OPTION_LICENSE_KEY ),
                'email'       => get_option( $this->_constants->OPTION_ACTIVATION_EMAIL ),
                'is_active'   => get_option( $this->_constants->OPTION_LICENSE_ACTIVATED ),
            );
        }

        @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) ); // phpcs:ignore
        echo wp_json_encode( $response );
        wp_die();
    }




    /*
    |--------------------------------------------------------------------------
    | Fulfill implemented interface contracts
    |--------------------------------------------------------------------------
    */

    /**
     * Execute codes that needs to run plugin activation.
     *
     * @since 1.0
     * @access public
     * @implements AGCFW\Interfaces\Initializable_Interface
     */
    public function initialize() {

        add_action( 'wp_ajax_agcfw_get_license_details', array( $this, 'ajax_get_license_details' ) );
        add_action( 'wp_ajax_agcfw_activate_license', array( $this, 'ajax_activate_license' ) );
        add_action( 'wp_ajax_agcfw_slmw_dismiss_activate_notice', array( $this, 'ajax_dismiss_activate_notice' ) );
    }

    /**
     * Execute License class.
     *
     * @since 1.0
     * @access public
     * @inherit AGCFW\Interfaces\Model_Interface
     */
    public function run() {

        if ( is_multisite() ) {

            add_action( 'network_admin_notices', array( $this, 'activate_license_notice' ) );

        } else {

            add_action( 'admin_notices', array( $this, 'activate_license_notice' ) );

        }
    }
}
