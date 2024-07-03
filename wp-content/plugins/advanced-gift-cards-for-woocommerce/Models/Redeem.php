<?php

namespace AGCFW\Models;

use ACFWF\Models\Objects\Store_Credit_Entry;
use AGCFW\Abstracts\Abstract_Main_Plugin_Class;
use AGCFW\Helpers\Helper_Functions;
use AGCFW\Helpers\Plugin_Constants;
use AGCFW\Interfaces\Initiable_Interface;
use AGCFW\Interfaces\Model_Interface;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Model that houses the logic of the Redeem module.
 *
 * @since 1.0
 */
class Redeem implements Model_Interface, Initiable_Interface {
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
     * @var Redeem
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
        $main_plugin->add_to_public_models( $this );
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
     * @return Redeem
     */
    public static function get_instance( Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants, Helper_Functions $helper_functions ) {
        if ( ! self::$_instance instanceof self ) {
            self::$_instance = new self( $main_plugin, $constants, $helper_functions );
        }

        return self::$_instance;
    }

    /*
    |--------------------------------------------------------------------------
    | My Account UI
    |--------------------------------------------------------------------------
     */

    /**
     * Display my account gift card redeem form.
     *
     * @since 1.0
     * @access public
     */
    public function display_my_account_gift_card_redeem_form() {
        $args = $this->get_default_redeem_form_template_args( array( 'agcfw-myaccount-redeem' ) );

        $this->_helper_functions->load_template(
            'agcfw-redeem-gift-card.php',
            $args
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Checkout UI
    |--------------------------------------------------------------------------
     */

    /**
     * Display checkout gift card redeem form.
     *
     * @deprecated 1.3.5
     *
     * @since 1.0
     * @access public
     */
    public function display_checkout_gift_card_redeem_form() {
        wc_deprecated_function( __METHOD__, '1.3.5' );
    }

    /**
     * Register gift card redeem form field in checkout page.
     *
     * @since 1.3.5
     * @access public
     *
     * @param string $field Field HTML.
     * @param string $key Field key.
     * @param array  $args Field args.
     * @param string $value Field value.
     * @return string Filtered field HTML.
     */
    public function register_gift_card_redeem_form_field( $field, $key, $args, $value ) {
        $class_prefix = apply_filters( 'agc_redeem_form_field_class_prefix', 'agc' );

        ob_start();
        include $this->_constants->VIEWS_ROOT_PATH . 'fields/redeem-form-field.php';

        return ob_get_clean();
    }

    /**
     * Register the gift cards checkout accordion.
     *
     * @since 1.3.5
     * @access public
     *
     * @param array $accordions Accordions data.
     * @return array Accordions data.
     */
    public function register_gift_card_checkout_accordion( $accordions ) {
        if ( 'yes' === get_option( $this->_constants->DISPLAY_CHECKOUT_GIFT_CARD_REDEEM_FORM, 'yes' ) ) {
            $labels                  = $this->get_default_redeem_form_template_args();
            $accordions['gift_card'] = array(
                'key'       => 'gift_card',
                'label'     => __( 'Gift cards redeem form', 'advanced-gift-cards-for-woocommerce' ),
                'title'     => $labels['title'],
                'classname' => 'agc-gift-cards-checkout-ui',
            );
        }

        return $accordions;
    }

    /**
     * Display the store credits form in the checkout accordion.
     *
     * @since 1.3.5
     * @access public
     *
     * @param array $data Accordion data.
     */
    public function display_store_credits_form_checkout_accordion( $data ) {
        if ( ! isset( $data['key'] ) || 'gift_card' !== $data['key'] ) {
            return;
        }

        $labels = $this->get_default_redeem_form_template_args();

        $this->_helper_functions->load_template(
            'checkout/agcfw-accordion.php',
            array(
                'labels' => $labels,
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Render gutenberg block / shortcode
    |--------------------------------------------------------------------------
     */

    /**
     * Render redeem form block.
     *
     * @since 1.0
     * @access private
     *
     * @param array $attributes Block attributes.
     * @return string HTML markup of block.
     */
    public function render_redeem_form_block( $attributes = array() ) {

        // fallback enqueue of styles and scripts when it's somehow not enqueued in the script loader.
        wp_enqueue_style( 'agcfw-redeem-gift-card' );
        wp_enqueue_script( 'agcfw-redeem-gift-card' );

        $classnames = array( 'agcfw-block-redeem' );

        if ( isset( $attributes['className'] ) && $attributes['className'] ) {
            $classnames[] = $attributes['className'];
        }

        $args = wp_parse_args( $attributes, $this->get_default_redeem_form_template_args( $classnames ) );

        ob_start();

        $this->_helper_functions->load_template(
            'agcfw-redeem-gift-card.php',
            $args
        );

        return ob_get_clean();
    }

    /**
     * Render redeem form via shortcode and enqueueing scripts and styles.
     *
     * @since 1.0
     * @access public
     *
     * @deprecated 1.3
     *
     * @param array $attributes Shortcode attributes.
     * @return string HTML markup of redeem form.
     */
    public function render_redeem_form_shortcode( $attributes = array() ) {
        return $this->render_redeem_form_block( $attributes );
    }

    /*
    |--------------------------------------------------------------------------
    | Redeem Implementation
    |--------------------------------------------------------------------------
     */

    /**
     * Validate gift card.
     *
     * @since 1.0
     * @access private
     *
     * @param Advanced_Gift_Card $gift_card Gift card object.
     * @return bool|WP_Error true when valid, error object on failure.
     */
    private function _validate_gift_card( $gift_card ) {
        if ( ! $gift_card || ! $gift_card->get_id() || 'pending' !== $gift_card->get_prop( 'status' ) ) {
            return new \WP_Error(
                'agcfw_gift_card_invalid',
                __( "The provided gift card doesn't exist or is invalid", 'advanced-gift-cards-for-woocommerce' ),
                array(
					'status'    => 400,
					'gift_card' => $gift_card,
                )
            );
        }

        // validate gift card expiry.
        if ( $gift_card->get_date( 'date_expire' ) ) {
            $now = new \WC_DateTime( 'now', new \DateTimeZone( $this->_helper_functions->get_site_current_timezone() ) );

            if ( $now > $gift_card->get_date( 'date_expire' ) ) {
                return new \WP_Error(
                    'agcfw_gift_card_expired',
                    __( 'The gift card has already expired', 'advanced-gift-cards-for-woocommerce' ),
                    array(
						'status'    => 400,
						'gift_card' => $gift_card,
                    )
                );
            }
        }

        return true;
    }

    /**
     * Redeem gift card as store credits.
     *
     * @since 1.0
     * @access private
     *
     * @param Advanced_Gift_Card $gift_card Gift card object.
     * @param int                $user_id   User ID.
     */
    private function _redeem_gift_card( $gift_card, $user_id = 0 ) {
        // validate gift card before proceeding.
        $check = $this->_validate_gift_card( $gift_card );
        if ( is_wp_error( $check ) ) {
            return $check;
        }

        $store_credit_entry = new Store_Credit_Entry();
        $user_id            = $user_id ? $user_id : get_current_user_id();

        // try catch is added here to prevent fatal errors when fetching invalid order item IDs.
        try {
            $order_item = new \WC_Order_Item_Product( $gift_card->get_prop( 'order_item_id' ) );
            $order_id   = $order_item->get_order_id();
        } catch ( \Exception $e ) {
            $order_id = 0;
        }

        $store_credit_entry->set_prop( 'amount', $gift_card->get_prop( 'value' ) );
        $store_credit_entry->set_prop( 'user_id', $user_id );
        $store_credit_entry->set_prop( 'type', 'increase' );
        $store_credit_entry->set_prop( 'action', 'gift_card' );
        $store_credit_entry->set_prop( 'object_id', $order_id );

        // save/create store credit entry.
        $entry_id = $store_credit_entry->save();

        if ( is_wp_error( $entry_id ) ) {
            return $entry_id;
        }

        $gift_card->set_prop( 'user_id', $user_id );
        $gift_card->set_prop( 'status', 'used' );
        $gift_card->save();

        // add order note when gift card is redeemed.
        if ( $order_id ) {
            $this->add_gift_card_redeem_order_note( $gift_card, $order_id );
        }

        return $entry_id;
    }

    /**
     * Maybe apply gift card store credits discount.
     *
     * @since 1.3.7
     * @access public
     *
     * @param Advanced_Gift_Card $gift_card Gift card object.
     * @return bool|WP_Error true when valid, error object on failure.
     */
    public function maybe_apply_gift_card_store_credits_discount( $gift_card ) {

        $apply_value = apply_filters( 'acfw_filter_amount', $gift_card->get_prop( 'value' ) );

        // Get the currently applied store credits amount.
        $is_apply_coupon = 'coupon' === get_option( $this->_constants->STORE_CREDIT_APPLY_TYPE, 'coupon' );
        $session_name    = $is_apply_coupon ? $this->_constants->STORE_CREDITS_COUPON_SESSION : $this->_constants->STORE_CREDITS_SESSION;
        $sc_data         = \WC()->session->get( $session_name, null );

        // Append the gift card value to the already applied store credits discount.
        if ( is_array( $sc_data ) && isset( $sc_data['amount'] ) ) {
            $apply_value = wc_remove_number_precision( wc_add_number_precision( $apply_value ) + wc_add_number_precision( $sc_data['amount'] ) );
        }

        return \ACFWF()->Store_Credits_Checkout->redeem_store_credits( get_current_user_id(), $apply_value );
    }

    /**
     * Add order note when gift card is redeemed.
     *
     * @since 1.3.7
     * @access public
     *
     * @param Advanced_Gift_Card $gift_card Gift card object.
     * @param int|\WC_Order      $order Order ID or order object.
     */
    public function add_gift_card_redeem_order_note( $gift_card, $order ) {
        $order           = $order instanceof \WC_Order ? $order : wc_get_order( $order );
        $gift_card_value = apply_filters( 'acfw_filter_amount', $gift_card->get_value(), array( 'user_currency' => $order->get_currency() ) );

        // return early if order is not valid.
        if ( ! $order ) {
            return;
        }

        $order->add_order_note(
            sprintf(
                /* translators: %1$s: gift card code, %2$s: Gift card value */
                __( 'Gift card %1$s was redeemed for %2$s store credits.', 'advanced-gift-cards-for-woocommerce' ),
                $gift_card->get_code(),
                \ACFWF()->Helper_Functions->api_wc_price( $gift_card_value, array( 'currency' => $order->get_currency() ) ),
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX Functions
    |--------------------------------------------------------------------------
     */

    /**
     * AJAX redeem gift card to store credits.
     *
     * @since 1.0
     * @access public
     */
    public function ajax_redeem_gift_card() {
        if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
            $response = array(
				'status'  => 'error',
				'message' => __( 'Invalid AJAX call', 'advanced-gift-cards-for-woocommerce' ),
            );
        } elseif ( ! is_user_logged_in() || ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'agcfw_redeem_gift_card' ) ) {
            $response = array(
				'status'  => 'error',
				'message' => __( 'You are not allowed to do this', 'advanced-gift-cards-for-woocommerce' ),
            );
        } elseif ( ! isset( $_POST['gift_card_code'] ) ) {
            $response = array(
				'status'  => 'error',
				'message' => __( 'Missing required post data', 'advanced-gift-cards-for-woocommerce' ),
            );
        } else {
            $gift_card_code = sanitize_text_field( wp_unslash( $_POST['gift_card_code'] ) );
            $gift_card      = $this->_helper_functions->get_gift_card_by_code( $gift_card_code );

            $check = $this->_redeem_gift_card( $gift_card );

            if ( is_wp_error( $check ) ) {
                $response = array(
					'status'  => 'error',
					'message' => $check->get_error_message(),
                );
            } else {

                $this->maybe_apply_gift_card_store_credits_discount( $gift_card );

                $response = array(
					'status'  => 'success',
					'message' => __( 'Gift card was redeemed successfully!', 'advanced-gift-cards-for-woocommerce' ),
                );
            }
        }

        // Don't display notice on WC Checkout block.
        if ( ! isset( $_POST['is_cart_checkout_block'] ) ) {
            wc_add_notice( $response['message'], 'error' === $response['status'] ? 'error' : 'success' );
        }

        @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) ); // phpcs:ignore
        echo wp_json_encode( $response );
        wp_die();
    }

    /*
    |--------------------------------------------------------------------------
    | Utility Functions
    |--------------------------------------------------------------------------
     */

    /**
     * Get default redeem form template arguments.
     *
     * @since 1.0
     * @since 1.3.5 Set method as public.
     * @access public
     *
     * @param array $classnames List of classnames.
     * @return array Redeem form template args.
     */
    public function get_default_redeem_form_template_args( $classnames = array() ) {
        if ( is_checkout() ) {
            $classnames[] = 'agcfw-toggle-redeem-form';
        }

        return apply_filters(
            'agcfw_default_redeem_form_template_args',
            array(
				'id'                => 'agcfw-redeem-gift-card',
				'classnames'        => $classnames,
				'title'             => __( 'Redeem a gift card?', 'advanced-gift-cards-for-woocommerce' ),
				'description'       => __( 'Enter your gift card claim code.', 'advanced-gift-cards-for-woocommerce' ),
				'caret_img_src'     => is_checkout() ? $this->_constants->IMAGES_ROOT_URL . 'caret.svg' : '',
				'tooltip_link_text' => __( 'How do I find the claim code?', 'advanced-gift-cards-for-woocommerce' ),
				'tooltip_title'     => __( 'Gift Card Claim Code', 'advanced-gift-cards-for-woocommerce' ),
				'tooltip_content'   => __( 'Your gift card claim code is found inside the email sent from the store when the gift card was purchased.', 'advanced-gift-cards-for-woocommerce' ),
				'label_text'        => __( 'Gift card code', 'advanced-gift-cards-for-woocommerce' ),
				'input_placeholder' => __( 'Enter code', 'advanced-gift-cards-for-woocommerce' ),
				'button_text'       => __( 'Redeem', 'advanced-gift-cards-for-woocommerce' ),
                /* translators: %s: My account page URL */
                'guest_content'     => sprintf( __( '<a href="%s">Login or sign up</a> to redeem your gift card.', 'advanced-gift-cards-for-woocommerce' ), wc_get_page_permalink( 'myaccount' ) ),
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Fulfill implemented interface contracts
    |--------------------------------------------------------------------------
     */

    /**
     * Execute codes that needs to run plugin init.
     *
     * @since 1.0
     * @access public
     * @implements ACFWF\Interfaces\Initializable_Interface
     */
    public function initialize() {
        add_action( 'wp_ajax_agcfw_redeem_gift_card', array( $this, 'ajax_redeem_gift_card' ) );
    }

    /**
     * Execute Redeem class.
     *
     * @since 1.0
     * @access public
     * @inherit AGCFW\Interfaces\Model_Interface
     */
    public function run() {
        add_action( 'woocommerce_form_field_agc_gift_card_redeem', array( $this, 'register_gift_card_redeem_form_field' ), 10, 4 );
        add_filter( 'acfw_checkout_accordions_data', array( $this, 'register_gift_card_checkout_accordion' ), 30 );
        add_action( 'acfw_checkout_accordion_content', array( $this, 'display_store_credits_form_checkout_accordion' ) );
        add_action( 'acfw_store_credits_my_account_after', array( $this, 'display_my_account_gift_card_redeem_form' ), 90 );
        add_shortcode( 'agcfw_gift_card_redeem_form', array( $this, 'render_redeem_form_block' ) );
    }
}
