<?php
namespace LPFW\Models;

use LPFW\Abstracts\Abstract_Main_Plugin_Class;
use LPFW\Abstracts\Base_Model;
use LPFW\Helpers\Helper_Functions;
use LPFW\Helpers\Plugin_Constants;
use LPFW\Interfaces\Model_Interface;
use LPFW\Objects\Vite_App;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Model that houses the script loading of the plugin.
 *
 * @since 1.0
 */
class Script_Loader extends Base_Model implements Model_Interface {
    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
     */

    /**
     * Class constructor.
     *
     * @since 1.0.0
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
     * Load backend js and css scripts.
     *
     * @since 1.0.0
     * @access public
     *
     * @param string $handle Unique identifier of the current backend page.
     */
    public function load_backend_scripts( $handle ) {
        $screen = get_current_screen();

        $post_type = get_post_type();
        // phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        if ( ! $post_type && isset( $_GET['post_type'] ) ) {
            $post_type = wp_unslash( $_GET['post_type'] );
        }

        $tab     = isset( $_GET['tab'] ) ? wp_unslash( $_GET['tab'] ) : '';
        $section = isset( $_GET['section'] ) ? wp_unslash( $_GET['section'] ) : '';
        // phpcs:enable

        // enqueue scripts for product page.
        if ( 'product' === $screen->id && 'product' === $post_type ) {
            $edit_product_vite = new Vite_App(
                'lpfw-edit-product',
                'packages/lpfw-edit-product/index.ts',
                array( 'jquery' ),
            );
            $edit_product_vite->enqueue();
        }

        if ( 'toplevel_page_advanced-coupons-network' === $screen->base && in_array( $tab, array( 'lpfw_license' ), true ) ) {

            $slmw_vite = new Vite_App(
                'lpfw_slmw',
                'packages/lpfw-slmw-license/index.ts',
                array( 'jquery', 'vex' ),
                array( 'vex', 'vex-theme-plain' )
            );
            $slmw_vite->enqueue();

            wp_localize_script(
                'lpfw_slmw',
                'slmw_args',
                array(
                    'lpfw_slmw_activation_email'        => get_option( 'lpfw_slmw_activation_email' ),
                    'lpfw_slmw_license_key'             => get_option( 'lpfw_slmw_license_key' ),
                    'nonce_activate_license'            => wp_create_nonce( 'lpfw_activate_license' ),
                    'i18n_activate_license'             => __( 'Activate License', 'loyalty-program-for-woocommerce' ),
                    'i18n_activating_license'           => __( 'Activating. Please wait...', 'loyalty-program-for-woocommerce' ),
                    'i18n_please_fill_activation_creds' => __( 'Please fill in activation email and license key', 'loyalty-program-for-woocommerce' ),
                    'i18n_failed_to_activate_license'   => __( 'Failed to activated license. Server error occurred on ajax request. Please contact support.', 'loyalty-program-for-woocommerce' ),
                    'i18n_license_activated'            => __( 'License is Active', 'loyalty-program-for-woocommerce' ),
                    'i18n_license_not_active'           => __( 'Not Activated Yet', 'loyalty-program-for-woocommerce' ),
                )
            );

        }
    }

    /**
     * Get my account page URL for router.
     * This will provide the permalink of my account page without escaping the URL value. This is required as the router
     * in My Points app needs a URL value that is not escaped to properly support other languages with special characters.
     *
     * @since 1.2
     * access private
     *
     * @return string My account page URL.
     */
    private function _get_my_account_page_url_for_router() {
        $my_account_post = get_post( wc_get_page_id( 'myaccount' ) );
        $editable_slug   = apply_filters( 'editable_slug', $my_account_post->post_name, $my_account_post );

        $page_url = str_replace( $my_account_post->post_name, $editable_slug, get_permalink( $my_account_post ) );
        $parse    = \wp_parse_url( $page_url );

        return sprintf( '%s://%s%s', $parse['scheme'], $parse['host'], $parse['path'] );
    }

    /**
     * Load frontend js and css scripts.
     *
     * @since 1.0.0
     * @access public
     */
    public function load_frontend_scripts() {
        global $post, $wp, $wp_query;

        $is_endpoint = isset( $wp_query->query_vars[ $this->_constants->my_points_endpoint() ] );

        if ( $is_endpoint && ! is_admin() && is_account_page() ) {

            // Important: Must enqueue this script in order to use WP REST API via JS.
            wp_enqueue_script( 'wp-api' );

            $points_name            = $this->_helper_functions->get_points_name();
            $points_name            = apply_filters( 'acfw_string_option', $points_name, $this->_constants->POINTS_NAME ); // WPML support.
            $points_name_html       = sprintf( '<span class="points-name">%s</span>', $points_name );
            $coupon_expire_period   = get_option( $this->_constants->COUPON_EXPIRE_PERIOD, 365 );
            $minimum_points_redeem  = (int) $this->_helper_functions->get_option( $this->_constants->MINIMUM_POINTS_REDEEM, '0' );
            $maximum_points_redeem  = (int) $this->_helper_functions->get_option( $this->_constants->MAXIMUM_POINTS_REDEEM, '0' );
            $inactive_expire_period = (int) get_option( $this->_constants->INACTIVE_DAYS_POINTS_EXPIRE, 365 );
            $additional_info_txt    = get_option(
                $this->_constants->POINTS_REDEEM_ADDITIONAL_INFO,
                sprintf(
                    /* Translators: %s: Points name. */
                    __( 'This action will redeem %s as store credits that you can use on a future order.', 'loyalty-program-for-woocommerce' ),
                    '<span class="points-name">' . strtolower( $points_name ) . '</span>'
                )
            );

            wp_localize_script(
                'wp-api',
                'lpfwMyPoints',
                apply_filters(
                    'lpfw_my_points_localized_data',
                    array(
                        'page_endpoint'         => $this->_constants->my_points_endpoint(),
                        'app_lang'              => $this->_helper_functions->get_app_language(),
                        'page_url'              => $this->_get_my_account_page_url_for_router(),
                        'store_credits'         => apply_filters( 'acfw_store_credits_endpoint', \ACFWF\Helpers\Plugin_Constants::STORE_CREDITS_ENDPOINT ),
                        'cart_url'              => wc_get_cart_url(),
                        'redeem_ratio'          => (int) get_option( $this->_constants->REDEEM_POINTS_RATIO, '10' ),
                        'currency_ratio'        => apply_filters( 'acfw_filter_amount', 1 ),
                        'currency_symbol'       => get_woocommerce_currency_symbol(),
                        'decimal_separator'     => wc_get_price_decimal_separator(),
                        'thousand_separator'    => wc_get_price_thousand_separator(),
                        'decimals'              => wc_get_price_decimals(),
                        'coupon_expire_period'  => $coupon_expire_period,
                        'minimum_points_redeem' => $minimum_points_redeem,
                        'maximum_points_redeem' => $maximum_points_redeem,
                        'points_expiry_note'    => get_option( $this->_constants->POINTS_EXPIRY_MESSAGE, __( 'Points are valid until {date_expire}. Redeem or earn more points to extend validity.', 'loyalty-program-for-woocommerce' ) ),
                        'labels'                => array(
                            'apply'              => __( 'Apply', 'loyalty-program-for-woocommerce' ),
                            /* Translators: %s: Points name. */
                            'points_balance'     => sprintf( __( '%s Balance', 'loyalty-program-for-woocommerce' ), $points_name_html ),
                            /* Translators: %s: Points name. */
                            'points_history'     => sprintf( __( '%s History', 'loyalty-program-for-woocommerce' ), $points_name_html ),
                            /* Translators: %s: Points name. */
                            'redeem_points'      => sprintf( __( 'Redeem %s', 'loyalty-program-for-woocommerce' ), $points_name_html ),
                            /* Translators: %1$s: Points balance, %2$s: Points name, %3$s: Points worth */
                            'points_worth'       => sprintf( __( 'You have %1$s %2$s (worth %3$s).', 'loyalty-program-for-woocommerce' ), '<strong>{p}</strong>', strtolower( $points_name_html ), '<strong>{w}</strong>' ),
                            'reward_coupons'     => __( 'Reward Coupons', 'loyalty-program-for-woocommerce' ),
                            'no_coupons_found'   => __( 'You don’t have any reward coupons yet.', 'loyalty-program-for-woocommerce' ),
                            'coupon_code'        => __( 'Coupon Code', 'loyalty-program-for-woocommerce' ),
                            'amount'             => __( 'Amount', 'loyalty-program-for-woocommerce' ),
                            'redeem_date'        => __( 'Redeemed Date', 'loyalty-program-for-woocommerce' ),
                            'expire_date'        => __( 'Expires', 'loyalty-program-for-woocommerce' ),
                            'action'             => __( 'Action', 'loyalty-program-for-woocommerce' ),
                            'apply_coupon'       => __( 'Apply Coupon', 'loyalty-program-for-woocommerce' ),
                            'date'               => __( 'Date', 'loyalty-program-for-woocommerce' ),
                            'customer'           => __( 'Customer', 'loyalty-program-for-woocommerce' ),
                            'activity'           => __( 'Activity', 'loyalty-program-for-woocommerce' ),
                            'points'             => $points_name,
                            'notes'              => __( 'Notes', 'loyalty-program-for-woocommerce' ),
                            'related'            => __( 'Related', 'loyalty-program-for-woocommerce' ),
                            /* Translators: %s: Points name. */
                            'redeem_desc'        => sprintf( __( 'Redeem %s as store credits. How much would you like to redeem?', 'loyalty-program-for-woocommerce' ), strtolower( $points_name ) ),
                            /* Translators: %s: Points name. */
                            'enter_points'       => sprintf( __( 'Enter %s', 'loyalty-program-for-woocommerce' ), $points_name_html ),
                            'enter_amount'       => __( 'Enter Amount', 'loyalty-program-for-woocommerce' ),
                            /* Translators: %s: Points name. */
                            'redeem_button'      => sprintf( __( 'Redeem %s as Store Credits', 'loyalty-program-for-woocommerce' ), $points_name ),
                            'min'                => __( 'Min', 'loyalty-program-for-woocommerce' ),
                            'max'                => __( 'Max', 'loyalty-program-for-woocommerce' ),
                            'view_store_credits' => __( 'View store credits', 'loyalty-program-for-woocommerce' ),
                            'additional_info'    => str_replace(
                                array( '{min_points}', '{max_points}', '{inactive_expiry_days}', '{coupon_expiry_days}' ),
                                array( $minimum_points_redeem, $maximum_points_redeem, $inactive_expire_period, $coupon_expire_period ),
                                $additional_info_txt
                            ),
                        ),
                    )
                )
            );

            $my_points_vite = new Vite_App(
                'lpfw-my-points',
                'packages/lpfw-my-points/index.tsx',
                array( 'wp-api', 'lodash' ),
            );
            $my_points_vite->enqueue();
        }

        // enqueue styles and script for checkout page.
        if ( is_checkout() ) {
            $checkout_vite = new Vite_App(
                'lpfw-checkout-redeem',
                'packages/lpfw-checkout-redeem/index.ts',
                array( 'jquery', 'wc-checkout' ),
            );
            $checkout_vite->enqueue();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Admin App
    |--------------------------------------------------------------------------
     */

    /**
     * Enqueue admin app scripts/styles.
     *
     * @since 1.0
     * @access public
     */
    public function enqueue_admin_app_scripts() {
        $admin_app_vite = new Vite_App(
            'lpfw_admin_app',
            'packages/lpfw-admin-app/index.tsx',
            array( 'moment' )
        );
        $admin_app_vite->enqueue();

        wp_localize_script(
            'lpfw_admin_app',
            'lpfwAdminApp',
            apply_filters(
                'lpfw_admin_app_localized',
                array(
                    'decimalPoint'   => wc_get_price_decimal_separator(),
                    'decimals'       => wc_get_price_decimals(),
                    'currencySymbol' => html_entity_decode( get_woocommerce_currency_symbol() ),
                    'homeUrl'        => home_url(),
                    'adminUrl'       => admin_url(),
                )
            )
        );
    }

    /**
     * Admin app localized data.
     *
     * @since 1.0
     * @access public
     *
     * @param array $data Localized data object.
     * @return array $data Localized data object.
     */
    public function admin_app_localized_data( $data ) {
        $data['loyalty_program'] = array(
            'title'          => __( 'Loyalty Program', 'loyalty-program-for-woocommerce' ),
            'tabs'           => array(
                array(
                    'slug'  => 'dashboard',
                    'label' => __( 'Dashboard', 'loyalty-program-for-woocommerce' ),
                ),
                array(
                    'slug'  => 'customers',
                    'label' => __( 'Customers', 'loyalty-program-for-woocommerce' ),
                ),
                array(
                    'slug'  => 'settings',
                    'label' => __( 'Settings', 'loyalty-program-for-woocommerce' ),
                    'desc'  => __( 'Adjust the settings options for your store’s Loyalty Program.', 'loyalty-program-for-woocommerce' ),
                ),
            ),
            'labels'         => array(
                'points_status'     => __( 'Points Status', 'loyalty-program-for-woocommerce' ),
                'points_sources'    => __( 'Points Sources', 'loyalty-program-for-woocommerce' ),
                'points_history'    => __( 'Points History', 'loyalty-program-for-woocommerce' ),
                'top_customers'     => __( 'Top Earning Customers', 'loyalty-program-for-woocommerce' ),
                'information'       => __( 'Information', 'loyalty-program-for-woocommerce' ),
                'points'            => __( 'Points', 'loyalty-program-for-woocommerce' ),
                'value'             => __( 'Value', 'loyalty-program-for-woocommerce' ),
                'source'            => __( 'Source', 'loyalty-program-for-woocommerce' ),
                'customer'          => __( 'Customer', 'loyalty-program-for-woocommerce' ),
                'breakpoint'        => __( 'Breakpoint', 'loyalty-program-for-woocommerce' ),
                'date'              => __( 'Date', 'loyalty-program-for-woocommerce' ),
                'activity'          => __( 'Activity', 'loyalty-program-for-woocommerce' ),
                'related'           => __( 'Related', 'loyalty-program-for-woocommerce' ),
                'points_earned'     => __( 'Points Earned', 'loyalty-program-for-woocommerce' ),
                'start_datetime'    => __( 'Start Date/Time', 'loyalty-program-for-woocommerce' ),
                'end_datetime'      => __( 'Start Date/Time', 'loyalty-program-for-woocommerce' ),
                'date_range'        => __( 'Date/Time Range', 'loyalty-program-for-woocommerce' ),
                'search_customers'  => __( 'Search Customers', 'loyalty-program-for-woocommerce' ),
                'name_or_email'     => __( 'Search by name or email', 'loyalty-program-for-woocommerce' ),
                'adjust_points'     => __( 'Adjust Points', 'loyalty-program-for-woocommerce' ),
                'adjust_for_user'   => __( 'Adjust points for this user', 'loyalty-program-for-woocommerce' ),
                'add_note'          => __( 'Add note', 'loyalty-program-for-woocommerce' ),
                'adjust'            => __( 'Adjust', 'loyalty-program-for-woocommerce' ),
                'increase_points'   => __( 'Increase Points', 'loyalty-program-for-woocommerce' ),
                'decrease_points'   => __( 'Decrease Points', 'loyalty-program-for-woocommerce' ),
                'proceed'           => __( 'Proceed', 'loyalty-program-for-woocommerce' ),
                'cancel'            => __( 'Cancel', 'loyalty-program-for-woocommerce' ),
                'uc_increase'       => __( 'INCREASE', 'loyalty-program-for-woocommerce' ),
                'uc_decrease'       => __( 'DECREASE', 'loyalty-program-for-woocommerce' ),
                'invalid_points'    => __( 'Please provide a valid points value.', 'loyalty-program-for-woocommerce' ),
                /* Translators: %s: maximum points. */
                'invalid_maxpoints' => sprintf( __( 'Please provide points equal or lesser than %s', 'loyalty-program-for-woocommerce' ), '{maxpoints}' ),
                /* Translators: %1$s: Adjustment type, %2$s: Points value. */
                'adjust_confirm'    => sprintf( __( 'This adjustments will %1$s this users points by %2$s.', 'loyalty-program-for-woocommerce' ), '{type}', '{points}' ),
                'customers_list'    => __( 'Customers List', 'loyalty-program-for-woocommerce' ),
                'customer_name'     => __( 'Name', 'loyalty-program-for-woocommerce' ),
                'email'             => __( 'Email', 'loyalty-program-for-woocommerce' ),
                'points_expiry'     => __( 'Points expiration date', 'loyalty-program-for-woocommerce' ),
                'customer_info'     => __( 'Customer Info', 'loyalty-program-for-woocommerce' ),
            ),
            'period_options' => array(
                array(
                    'label' => __( 'Week to Date', 'loyalty-program-for-woocommerce' ),
                    'value' => 'week_to_date',
                ),
                array(
                    'label' => __( 'Month to Date', 'loyalty-program-for-woocommerce' ),
                    'value' => 'month_to_date',
                ),
                array(
                    'label' => __( 'Quarter to Date', 'loyalty-program-for-woocommerce' ),
                    'value' => 'quarter_to_date',
                ),
                array(
                    'label' => __( 'Year to Date', 'loyalty-program-for-woocommerce' ),
                    'value' => 'year_to_date',
                ),
                array(
                    'label' => __( 'Last Week', 'loyalty-program-for-woocommerce' ),
                    'value' => 'last_week',
                ),
                array(
                    'label' => __( 'Last Month', 'loyalty-program-for-woocommerce' ),
                    'value' => 'last_month',
                ),
                array(
                    'label' => __( 'Last Quarter', 'loyalty-program-for-woocommerce' ),
                    'value' => 'last_quarter',
                ),
                array(
                    'label' => __( 'Last Year', 'loyalty-program-for-woocommerce' ),
                    'value' => 'last_year',
                ),
                array(
                    'label' => __( 'Custom Range', 'loyalty-program-for-woocommerce' ),
                    'value' => 'custom',
                ),
            ),
            'license'        => $this->_get_license_localized_data(),
        );

        // append license tab.
        if (
            ( is_multisite() && current_user_can( 'manage_sites' ) )
            || ( ! is_multisite() && current_user_can( 'manage_woocommerce' ) )
        ) {
            $data['license_tabs'][] = array(
                'key'   => 'LPFW',
                'label' => __( 'Loyalty Program', 'loyalty-program-for-woocommerce' ),
            );
        }

        $data['validation']['price']       = __( 'Please enter a valid price amount.', 'loyalty-program-for-woocommerce' );
        $data['validation']['breakpoints'] = __( 'Please enter valid breakpoint amounts and/or points.', 'loyalty-program-for-woocommerce' );

        return $data;
    }

    /**
     * Get the localized data for the license page.
     *
     * @since 1.0
     * @access private
     *
     * @return array License localized data.
     */
    private function _get_license_localized_data() {
        if ( is_multisite() ) {

            return array(
                'is_multisite' => true,
                'license_page' => network_admin_url( 'admin.php?page=lpfw-ms-license-settings' ),
            );

        } else {
            return array(
                'title'          => __( 'Loyalty Program License Activation', 'loyalty-program-for-woocommerce' ),
                'is_multisite'   => false,
                'license_status' => __( 'Your current license for Loyalty Program:', 'loyalty-program-for-woocommerce' ),
                'activated'      => __( 'License is Active', 'loyalty-program-for-woocommerce' ),
                'not_activated'  => __( 'Not Activated Yet', 'loyalty-program-for-woocommerce' ),
                'description'    => __( 'You are currently using Loyalty Program for WooCommerce by Advanced Coupons. In order to get future updates, bug fixes, and security patches automatically you will need to activate your license. This also allows you to claim support from our support team. Please enter your license details and activate your key.', 'loyalty-program-for-woocommerce' ),
                'plan_label'     => __( 'Plan', 'loyalty-program-for-woocommerce' ),
                'version_label'  => __( 'Version', 'loyalty-program-for-woocommerce' ),
                'plan_value'     => __( 'Loyalty Program', 'loyalty-program-for-woocommerce' ),
                'version_value'  => $this->_constants->VERSION,
                'license_key'    => __( 'License Key:', 'loyalty-program-for-woocommerce' ),
                'license_email'  => __( 'Activation Email:', 'loyalty-program-for-woocommerce' ),
                'activate_btn'   => __( 'Activate Key', 'loyalty-program-for-woocommerce' ),
                'help'           => array(
                    'text'  => __( 'Can’t find your key?', 'loyalty-program-for-woocommerce' ),
                    'link'  => 'https://advancedcouponsplugin.com/my-account/?utm_source=lpfw&utm_medium=license&utm_campaign=findkey',
                    'login' => __( 'Login to your account', 'loyalty-program-for-woocommerce' ),
                ),
                '_formNonce'     => wp_create_nonce( 'lpfw_activate_license' ),
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Gutenberg scripts
    |--------------------------------------------------------------------------
     */

    /**
     * Load gutenberg custom blocks scripts in the post editor.
     *
     * @since 1.8.1
     * @access public
     */
    public function load_gutenberg_editor_scripts() {
        $edit_blocks_vite = new Vite_App(
            'lpfw-blocks-edit',
            'packages/lpfw-blocks/index.tsx',
            array( 'wp-block-editor', 'wp-blocks', 'wp-components', 'wp-element', 'wp-server-side-render' )
        );
        $edit_blocks_vite->enqueue();

        $attributes = \LPFW()->Editor_Blocks->get_redeem_points_default_atts();

        wp_localize_script(
            'lpfw-blocks-edit',
            'lpfwBlocksi18n',
            array(
                'pointsRedeemFormTexts' => array(
                    'title'       => __( 'Loyalty Points Redeem Form', 'loyalty-program-for-woocommerce' ),
                    'description' => __( 'Display the redeem form for loyalty points.', 'loyalty-program-for-woocommerce' ),
                    'attributes'  => $attributes,
                    'labels'      => array(
                        'form_texts'         => __( 'Form texts', 'loyalty-program-for-woocommerce' ),
                        /* Translators: %s: list of placeholder tags. */
                        'help_text'          => sprintf( __( 'Available placeholders: %s', 'loyalty-program-for-woocommerce' ), '{points}, {points_worth}, {min_points}, {max_points}' ),
                        'points_summary'     => __( 'Points summary text', 'loyalty-program-for-woocommerce' ),
                        'points_description' => __( 'Points description text', 'loyalty-program-for-woocommerce' ),
                        'input_placeholder'  => __( 'Input placeholder text', 'loyalty-program-for-woocommerce' ),
                        'button_text'        => __( 'Button text', 'loyalty-program-for-woocommerce' ),
                    ),
                ),
            )
        );
    }

    /**
     * Execute plugin script loader.
     *
     * @since 1.0.0
     * @access public
     */
    public function run() {
        add_action( 'admin_enqueue_scripts', array( $this, 'load_backend_scripts' ), 10, 1 );
        add_action( 'wp_enqueue_scripts', array( $this, 'load_frontend_scripts' ) );

        add_action( 'acfw_admin_app_enqueue_scripts_after', array( $this, 'enqueue_admin_app_scripts' ) );
        add_filter( 'acfwf_admin_app_localized', array( $this, 'admin_app_localized_data' ) );
        add_action( 'enqueue_block_editor_assets', array( $this, 'load_gutenberg_editor_scripts' ) );
    }
}
