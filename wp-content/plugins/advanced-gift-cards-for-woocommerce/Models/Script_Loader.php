<?php
namespace AGCFW\Models;

use AGCFW\Abstracts\Abstract_Main_Plugin_Class;
use AGCFW\Helpers\Helper_Functions;
use AGCFW\Helpers\Plugin_Constants;
use AGCFW\Interfaces\Model_Interface;
use AGCFW\Objects\Vite_App;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Model that houses the logic of the Script_loader module.
 *
 * @since 1.0
 */
class Script_Loader implements Model_Interface {
    /*
    |--------------------------------------------------------------------------
    | Class Properties
    |--------------------------------------------------------------------------
     */

    /**
     * Property that holds the single main instance of Bootstrap.
     *
     * @since 1.0.0
     * @access private
     * @var Bootstrap
     */
    private static $_instance;

    /**
     * Model that houses all the plugin constants.
     *
     * @since 1.0.0
     * @access private
     * @var Plugin_Constants
     */
    private $_constants;

    /**
     * Property that houses all the helper functions of the plugin.
     *
     * @since 1.0.0
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
     * @since 1.0.0
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
     * @since 1.0.0
     * @access public
     *
     * @param Abstract_Main_Plugin_Class $main_plugin      Main plugin object.
     * @param Plugin_Constants           $constants        Plugin constants object.
     * @param Helper_Functions           $helper_functions Helper functions object.
     * @return Bootstrap
     */
    public static function get_instance( Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants, Helper_Functions $helper_functions ) {
        if ( ! self::$_instance instanceof self ) {
            self::$_instance = new self( $main_plugin, $constants, $helper_functions );
        }

        return self::$_instance;
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
        $screen    = get_current_screen();
        $post_type = get_post_type();
        // phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        if ( ! $post_type && isset( $_GET['post_type'] ) ) {
            $post_type = wp_unslash( $_GET['post_type'] );
        }
        // phpcs:enable

        if ( 'post' === $screen->base && 'product' === $screen->id ) {
            $edit_gift_card_vite = new Vite_App(
                'agc-edit-gift-card',
                'packages/agc-edit-gift-card/index.ts',
                array( 'jquery', 'vex' ),
                array( 'woocommerce_admin_styles', 'vex', 'vex-theme-plain' )
            );
            $edit_gift_card_vite->enqueue();
        }

        do_action( 'agcfw_after_load_backend_scripts', $screen, $post_type );
    }

    /**
     * Enqueue admin app scripts.
     *
     * @since 1.0
     * @access public
     */
    public function enqueue_admin_app_scripts() {
        $admin_app_vite = new Vite_App(
            'agcfw_admin_app',
            'packages/agc-admin-app/index.tsx',
            array( 'acfwf-admin-app', 'moment' ),
            array( 'acfwf-admin-app' ),
        );
        $admin_app_vite->enqueue();

        wp_localize_script(
            'agcfw_admin_app',
            'agcfwAdminApp',
            array(
				'homeUrl'      => home_url(),
                'adminUrl'     => admin_url(),
                'main_page'    => array(
                    'title'          => __( 'Advanced Gift Cards', 'advanced-gift-cards-for-woocommerce' ),
                    'tabs'           => array(
                        array(
                            'slug'  => 'dashboard',
                            'label' => __( 'Dashboard', 'advanced-gift-cards-for-woocommerce' ),
                        ),
                        array(
                            'slug'  => 'manage-gift-cards',
                            'label' => __( 'Manage Gift Cards', 'advanced-gift-cards-for-woocommerce' ),
                        ),
                    ),
                    'labels'         => array(
                        'gift_card_activity'        => __( 'Gift Card Activity', 'advanced-gift-cards-for-woocommerce' ),
                        'top_gift_card_products'    => __( 'Top Gift Card Products', 'advanced-gift-cards-for-woocommerce' ),
                        'recent_gift_card_products' => __( 'Recently Created Gift Card Products', 'advanced-gift-cards-for-woocommerce' ),
                        'gift_card_product'         => __( 'Gift Card Product', 'advanced-gift-cards-for-woocommerce' ),
                        'quantity'                  => __( 'Quantity', 'advanced-gift-cards-for-woocommerce' ),
                        'value'                     => __( 'Value', 'advanced-gift-cards-for-woocommerce' ),
                        'gift_cards'                => __( 'Gift Cards', 'advanced-gift-cards-for-woocommerce' ),
                        'add_new_gift_card'         => __( 'Add new gift card', 'advanced-gift-cards-for-woocommerce' ),
                        'create_gift_card'          => __( 'Create gift card', 'advanced-gift-cards-for-woocommerce' ),
                        'search_placeholder'        => __( 'Search...', 'advanced-gift-cards-for-woocommerce' ),
                        'search'                    => __( 'Search', 'advanced-gift-cards-for-woocommerce' ),
                        'select_status'             => __( 'Select Status', 'advanced-gift-cards-for-woocommerce' ),
                        'gift_card_code'            => __( 'Gift Card Code', 'advanced-gift-cards-for-woocommerce' ),
                        'status'                    => __( 'Status', 'advanced-gift-cards-for-woocommerce' ),
                        'product'                   => __( 'Product', 'advanced-gift-cards-for-woocommerce' ),
                        'amount'                    => __( 'Amount', 'advanced-gift-cards-for-woocommerce' ),
                        'date_created'              => __( 'Date Created', 'advanced-gift-cards-for-woocommerce' ),
                        'date_expire'               => __( 'Date Expire', 'advanced-gift-cards-for-woocommerce' ),
                        /* Translators: %s: Field name. */
                        'invalid_field_message'     => sprintf( __( '%s is required', 'advanced-gift-cards-for-woocommerce' ), '{field}' ),
                        'view_order'                => __( 'View Order', 'advanced-gift-cards-for-woocommerce' ),
                        'send_to'                   => __( 'Send to', 'advanced-gift-cards-for-woocommerce' ),
                        'friend'                    => __( 'friend', 'advanced-gift-cards-for-woocommerce' ),
                        'me'                        => __( 'me', 'advanced-gift-cards-for-woocommerce' ),
                        'recipient_name'            => __( 'Recipient name', 'advanced-gift-cards-for-woocommerce' ),
                        'recipient_email'           => __( 'Recipient email', 'advanced-gift-cards-for-woocommerce' ),
                        'short_message'             => __( 'Short message', 'advanced-gift-cards-for-woocommerce' ),
                        'reset'                     => __( 'Reset', 'advanced-gift-cards-for-woocommerce' ),
                        'save_changes'              => __( 'Save Changes', 'advanced-gift-cards-for-woocommerce' ),
                        'invalid_price'             => __( 'Invalid price', 'advanced-gift-cards-for-woocommerce' ),
                        'invalid_email'             => __( 'Invalid email', 'advanced-gift-cards-for-woocommerce' ),
                        'confirm_delete_gift_card'  => __( 'Are you sure you want to delete this gift card?', 'advanced-gift-cards-for-woocommerce' ),
                        'yes'                       => __( 'Yes', 'advanced-gift-cards-for-woocommerce' ),
                        'cancel'                    => __( 'Cancel', 'advanced-gift-cards-for-woocommerce' ),
                        'manual_gift_card_info'     => __( 'An email will be sent out after creating the gift card when the recipient details are provided.', 'advanced-gift-cards-for-woocommerce' ),
                    ),
                    'status_options' => $this->_constants->get_gift_card_status_options(),
                ),
				'license_page' => array(
					'title'          => __( 'Advanced Gift Cards License Activation', 'advanced-gift-cards-for-woocommerce' ),
					'license_status' => __( 'Your current license for Advanced Gift Cards', 'advanced-gift-cards-for-woocommerce' ),
					'about_content'  => __( 'Advanced Gift Cards lets you sell redeemable digital gift cards on your WooCommerce store via a simple product listing. Gift Cards can then be redeemed for store credit that your customers can use towards orders. Activate your license key to enable continued support & updates for Advanced Gift Cards as well as access to premium features.', 'advanced-gift-cards-for-woocommerce' ),
					'indicator'      => array(
						'active'   => __( 'License is Active', 'advanced-gift-cards-for-woocommerce' ),
						'inactive' => __( 'Not Activated Yet', 'advanced-gift-cards-for-woocommerce' ),
					),
					'specs'          => array(
						array(
							'label' => __( 'Plan', 'advanced-gift-cards-for-woocommerce' ),
							'value' => __( 'Advanced Gift Cards', 'advanced-gift-cards-for-woocommerce' ),
						),
						array(
							'label' => __( 'Version', 'advanced-gift-cards-for-woocommerce' ),
							'value' => $this->_constants->VERSION,
						),
					),
					'formlabels'     => array(
						'license_key' => __( 'License Key:', 'advanced-gift-cards-for-woocommerce' ),
						'email'       => __( 'Activation Email:', 'advanced-gift-cards-for-woocommerce' ),
						'button'      => __( 'Activate Key', 'advanced-gift-cards-for-woocommerce' ),
						'help'        => array(
							'text'  => __( 'Can’t find your key?', 'advanced-gift-cards-for-woocommerce' ),
							'link'  => 'https://advancedcouponsplugin.com/my-account/?utm_source=agcfw&utm_medium=license&utm_campaign=findkey',
							'login' => __( 'Login to your account', 'advanced-gift-cards-for-woocommerce' ),
						),
					),
					'spinner_img'    => $this->_constants->IMAGES_ROOT_URL . 'spinner-2x.gif',
					'_formNonce'     => wp_create_nonce( 'agcfw_activate_license' ),
				),
            )
        );
    }

    /**
     * Load frontend js and css scripts.
     *
     * @since 1.0.0
     * @access public
     */
    public function load_frontend_scripts() {
        global $post, $wp, $wp_query;

        $product = $post && 'product' === $post->post_type ? wc_get_product( $post->ID ) : null;

        // register styles and scripts.
        wp_register_style( 'flatpickr', ACFWF()->Plugin_Constants->JS_ROOT_URL . 'lib/flatpickr/flatpickr.min.css', array(), \ACFWF\Helpers\Plugin_Constants::VERSION, 'all' );
        wp_register_script( 'flatpickr', ACFWF()->Plugin_Constants->JS_ROOT_URL . 'lib/flatpickr/flatpickr.min.js', array(), \ACFWF\Helpers\Plugin_Constants::VERSION, true );
        $redeem_gift_card_vite = new Vite_App(
            'agcfw-redeem-gift-card',
            'packages/agc-redeem-gift-card/index.ts',
            array( 'jquery', 'jquery-webui-popover' ),
            array( 'jquery-webui-popover' )
        );
        $redeem_gift_card_vite->register();

        if ( $product && 'advanced_gift_card' === $product->get_type() ) {
            $single_product_vite = new Vite_App(
                'agcfw-single-product',
                'packages/agc-single-product/index.ts',
                array( 'jquery', 'jquery-webui-popover', 'flatpickr' ),
                array( 'jquery-webui-popover', 'flatpickr' )
            );
            $single_product_vite->enqueue();

            wp_localize_script(
                'agcfw-single-product',
                'agcfwSingleProduct',
                array(
					'max_delivery_timestamp' => $this->_helper_functions->get_max_delivery_date_timestamp(),
                )
            );
        }

        // enqueue redeem gift card form assets when either the block or shortcode is present in the content.
        if ( $this->_is_redeem_block_present_in_post( $post ) ) {
            wp_enqueue_style( 'agcfw-redeem-gift-card' );
            wp_enqueue_script( 'agcfw-redeem-gift-card' );
        }

        if ( ( is_account_page() && isset( $wp_query->query['store-credit'] ) ) ) {
            wp_enqueue_style( 'agcfw-redeem-gift-card' );
            wp_enqueue_script( 'agcfw-redeem-gift-card' );
        }

        if ( is_checkout() ) {
            $checkout_vite = new Vite_App(
                'agc-checkout',
                'packages/agc-checkout/index.ts',
                array( 'jquery', 'jquery-webui-popover' ),
                array( 'jquery-webui-popover' )
            );
            $checkout_vite->enqueue();
        }
    }

    /**
     * Admin app localized data.
     *
     * @since 1.3.5
     * @access public
     *
     * @param array $data Localized data object.
     * @return array $data Localized data object.
     */
    public function acfw_admin_app_localized_data( $data ) {
        $data['license_tabs'][] = array(
            'key'   => 'AGC',
            'label' => __( 'Advanced Gift Cards', 'advanced-gift-cards-for-woocommerce' ),
        );

        return $data;
    }

    /**
     * Check if the redeem block or shortcode is present in the post's content.
     *
     * @since 1.3
     * @access private
     *
     * @param \WP_Post $post Post object.
     * @return bool True if present, false otherwise.
     */
    private function _is_redeem_block_present_in_post( $post ) {
        if ( ! $post instanceof \WP_Post ) {
            return false;
        }

        // check if redeem form gutenberg block or shortcode is present.
        if ( false !== strpos( $post->post_content, '<!-- wp:acfw/gift-card-redeem-form' )
            || false !== strpos( $post->post_content, '[agcfw_gift_card_redeem_form' ) ) {
            return true;
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Gutenberg scripts
    |--------------------------------------------------------------------------
     */

    /**
     * Load script and styles for gutenberg editor.
     *
     * @since 1.0
     * @access public
     */
    public function load_gutenberg_editor_scripts() {
        $blocks_vite = new Vite_App(
            'agc-blocks-edit',
            'packages/agc-blocks/index.tsx',
            array( 'wp-block-editor', 'wp-blocks', 'wp-components', 'wp-element', 'wp-server-side-render' ),
        );
        $blocks_vite->enqueue();

        wp_localize_script(
            'agc-blocks-edit',
            'agcfwBlocksi18n',
            array(
				'redeemFormBlockTexts' => array(
					'title'       => __( 'Advanced Gift Cards Redeem Form', 'advanced-gift-cards-for-woocommerce' ),
					'description' => __( 'Display the redeem form for advanced gift cards.', 'advanced-gift-cards-for-woocommerce' ),
					'defaults'    => array(
						'title'             => __( 'Redeem a gift card?', 'advanced-gift-cards-for-woocommerce' ),
						'description'       => __( 'Enter your gift card claim code.', 'advanced-gift-cards-for-woocommerce' ),
						'tooltip_link_text' => __( 'How do I find the claim code?', 'advanced-gift-cards-for-woocommerce' ),
						'tooltip_title'     => __( 'Gift Card Claim Code', 'advanced-gift-cards-for-woocommerce' ),
						'tooltip_content'   => __( 'Your gift card claim code is found inside the email sent from the store when the gift card was purchased.', 'advanced-gift-cards-for-woocommerce' ),
						'input_placeholder' => __( 'Enter code', 'advanced-gift-cards-for-woocommerce' ),
						'button_text'       => __( 'Redeem', 'advanced-gift-cards-for-woocommerce' ),
                        /* translators: %s: My account page URL */
                        'guest_content'     => sprintf( __( '<a href="%s">Login or sign up</a> to redeem your gift card.', 'advanced-gift-cards-for-woocommerce' ), wc_get_page_permalink( 'myaccount' ) ),
					),
					'labels'      => array(
						'main'              => __( 'Main', 'advanced-gift-cards-for-woocommerce' ),
						'title'             => __( 'Title', 'advanced-gift-cards-for-woocommerce' ),
						'description'       => __( 'Description', 'advanced-gift-cards-for-woocommerce' ),
						'tooltip_content'   => __( 'Tooltip content', 'advanced-gift-cards-for-woocommerce' ),
						'link_text'         => __( 'Button/Link text', 'advanced-gift-cards-for-woocommerce' ),
						'content'           => __( 'Content', 'advanced-gift-cards-for-woocommerce' ),
						'form_fields'       => __( 'Form fields', 'advanced-gift-cards-for-woocommerce' ),
						'input_placeholder' => __( 'Input placeholder', 'advanced-gift-cards-for-woocommerce' ),
						'button_text'       => __( 'Button text', 'advanced-gift-cards-for-woocommerce' ),
                        'guest_panel'       => __( 'Guests', 'advanced-gift-cards-for-woocommerce' ),
                        'guest_content'     => __( 'Content to display for guests', 'advanced-gift-cards-for-woocommerce' ),
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
        add_action( 'acfw_admin_app_enqueue_scripts_after', array( $this, 'enqueue_admin_app_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'load_frontend_scripts' ) );
        add_action( 'enqueue_block_editor_assets', array( $this, 'load_gutenberg_editor_scripts' ) );
        add_filter( 'acfwf_admin_app_localized', array( $this, 'acfw_admin_app_localized_data' ), 20 );
    }
}
