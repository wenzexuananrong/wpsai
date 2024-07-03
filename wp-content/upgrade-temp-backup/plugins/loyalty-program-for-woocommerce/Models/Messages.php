<?php
namespace LPFW\Models;

use LPFW\Abstracts\Base_Model;
use LPFW\Abstracts\Abstract_Main_Plugin_Class;
use LPFW\Helpers\Helper_Functions;
use LPFW\Helpers\Plugin_Constants;
use LPFW\Interfaces\Model_Interface;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Model that houses the logic of extending the coupon system of woocommerce.
 * It houses the logic of handling coupon url.
 * Public Model.
 *
 * @since 1.0
 */
class Messages extends Base_Model implements Model_Interface {
    /*
    |--------------------------------------------------------------------------
    | Traits
    |--------------------------------------------------------------------------
     */
    use \LPFW\Traits\Shortcode;

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
        parent::__construct( $main_plugin, $constants, $helper_functions );
        $main_plugin->add_to_all_plugin_models( $this );
        $main_plugin->add_to_public_models( $this );
    }

    /*
    |--------------------------------------------------------------------------
    | Cart / Checkout message
    |--------------------------------------------------------------------------
     */

    /**
     * Get total points to be earned for cart/checkout preview.
     *
     * @since 1.0
     * @since 1.6  Save calculated points to session so calculations are not repeated until the cart data has been changed or any of the settings have been changed.
     * @access private
     *
     * @return int Points to earn.
     */
    private function _get_cart_points_earn_preview() {
        // if session data is valid and available, then skip calculation and return value from session.
        if ( \LPFW()->Calculate->is_same_cart_session() ) {
            $data = \WC()->session->get( $this->_constants->CART_POINTS_SESSION );
            return $data['points'];
        }

        $points = 0;

        // purchasing products.
        if ( get_option( $this->_constants->EARN_ACTION_BUY_PRODUCT, 'yes' ) === 'yes' ) {
            $points += \LPFW()->Calculate->get_cart_total_points();
        }

        // high spend.
        if ( get_option( $this->_constants->EARN_ACTION_BREAKPOINTS, 'yes' ) === 'yes' ) {
            $calc_total = \LPFW()->Calculate->get_total_based_on_points_calculate_options();
            $points    += \LPFW()->Calculate->calculate_high_spend_points( $calc_total );
        }

        // extra during period.
        if ( get_option( $this->_constants->EARN_ACTION_ORDER_PERIOD, 'yes' ) === 'yes' ) {
            $points += \LPFW()->Calculate->get_matching_period_points();
        }

        // registering as a customer.
        if ( get_option( $this->_constants->EARN_ACTION_USER_REGISTER, 'yes' ) === 'yes' && $this->_validate_user_for_register_points() ) {
            $points += (int) $this->_helper_functions->get_option( $this->_constants->EARN_POINTS_USER_REGISTER, 10 );
        }

        // customer first order.
        if ( get_option( $this->_constants->EARN_ACTION_FIRST_ORDER, 'yes' ) === 'yes' && $this->_validate_user_for_first_order_points() ) {
            $points += (int) $this->_helper_functions->get_option( $this->_constants->EARN_POINTS_FIRST_ORDER, 10 );
        }

        // save calculated points data to session.
        \LPFW()->Calculate->save_calculated_cart_points_to_session( $points );

        return $points;
    }

    /**
     * Validate if guest can earn points after registering as customer.
     *
     * @since 1.0
     * @access private
     *
     * @return boolean True if valid, false otherwise.
     */
    private function _validate_user_for_register_points() {
        return ! is_user_logged_in() && $this->_helper_functions->is_role_valid( 'customer' );
    }

    /**
     * Validate if user or guest is allowed to earn points for first order action.
     *
     * @since 1.0
     * @access private
     *
     * @return boolean True if valid, false otherwise.
     */
    private function _validate_user_for_first_order_points() {
        if ( is_user_logged_in() ) {
            return $this->_helper_functions->validate_user_roles() && ! get_user_meta( get_current_user_id(), $this->_constants->FIRST_ORDER_ENTRY_ID_META, true );
        } else {
            return $this->_helper_functions->is_role_valid( 'customer' );
        }
    }

    /**
     * Get points earn message text
     *
     * @since 1.8.6
     * @access public
     *
     * @param string $message Message template.
     * @return string
     */
    public function get_points_earn_message_text( $message = '' ) {
        // Skip if the WC()->cart is not available.
        if ( ! WC()->cart instanceof \WC_Cart ) {
            return '';
        }

        // Validate if message option is exist and total points is valid.
        $calc_total = \LPFW()->Calculate->get_total_based_on_points_calculate_options();
        if ( ! $message || $calc_total < \LPFW()->Calculate->get_minimum_threshold() ) {
            return '';
        }

        // Validate points.
        $points = $this->_get_cart_points_earn_preview();
        if ( ! $points ) {
            return '';
        }

        // Transform message.
        $message = strpos( $message, '{points}' ) === false ? $message . ' <strong>{points}</strong>' : $message;
        $message = str_replace( '{points}', $points, $message );

        return $message;
    }

    /**
     * Get points earned preview message.
     *
     * @since 1.0
     * @access private
     *
     * @param string $message Message template.
     * @return string Points preview message.
     */
    private function _get_points_earn_message_preview( $message = '' ) {
        ob_start();
            wc_print_notice( sprintf( '<span class="acfw-notice-text">%s</span>', $this->get_points_earn_message_text( $message ) ), 'notice' );
        return ob_get_clean();
    }

    /**
     * Display earned points on cart page.
     * due to the nature of the cart page, we need to echo the message directly.
     *
     * @since 1.8.6
     * @access public
     */
    public function show_point_earn_message_in_cart() {
        echo do_shortcode( '[lpfw_points_earned_message]' );
    }

    /**
     * Display earned points on checkout page.
     *
     * @since 1.0
     * @access public
     */
    public function points_earn_message_in_checkout() {
        echo '<div class="acfw-loyalprog-notice-checkout"></div>';
    }

    /**
     * Append updated points earned message for checkout in WC order review fragments.
     *
     * @since 1.0
     * @since 1.8.1 Make sure the notice fragment is always updated by WC.
     * @access public
     *
     * @param array $fragments Order review fragments.
     * @return array Filtered order review fragments.
     */
    public function points_earn_message_checkout_fragments( $fragments ) {
        if ( ! $this->should_display_points_earn_message() ) {
            return $fragments;
        }

        $selector = '.acfw-loyalprog-notice-checkout';
        $message  = $this->get_notice_message_template( 'checkout' );
        $random   = time(); // The value here is used to make the notice unique so WC will be forced to update the fragment everytime.

        $fragments[ $selector ] = sprintf( '<div class="acfw-loyalprog-notice-checkout" data-key="%s">%s</div>', $random, $this->_get_points_earn_message_preview( $message ) );

        return $fragments;
    }

    /*
    |--------------------------------------------------------------------------
    | Products message
    |--------------------------------------------------------------------------
     */

    /**
     * Get single product preview price with WWP/P support.
     *
     * @since 1.0
     * @access private
     *
     * @param WC_Product $product     Product object.
     * @param string     $include_tax Include tax check (yes|no).
     * @return float Relative roduct price.
     */
    private function _get_single_product_preview_price( $product, $include_tax ) {
        $tax_display = get_option( 'woocommerce_tax_display_shop', 'incl' );
        $price       = -1;

        // get wholesale price.
        if ( class_exists( 'WWP_Wholesale_Prices' ) && method_exists( 'WWP_Wholesale_Prices', 'get_product_wholesale_price_on_shop_v3' ) ) {

            $wwp_roles_obj = \WWP_Wholesale_Roles::getInstance();
            $wholesa_roles = $wwp_roles_obj->getUserWholesaleRole();

            if ( ! empty( $wholesa_roles ) ) {

                $wholesale_prices = \WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v3( $product->get_id(), $wholesa_roles );
                $price            = isset( $wholesale_prices['wholesale_price_raw'] ) ? (float) $wholesale_prices['wholesale_price_raw'] : $price;
            }
        }

        // if there's no wholesale price detected, then we get the normal price.
        if ( 0 > $price ) {
            $price = $product->get_price();
        }

        if ( 'yes' === $include_tax ) {
            return wc_get_price_including_tax(
                $product,
                array(
                    'qty'   => 1,
                    'price' => $price,
                )
            );
        } else {
            return wc_get_price_excluding_tax(
                $product,
                array(
                    'qty'   => 1,
                    'price' => $price,
                )
            );
        }
    }

    /**
     * Add the display price without tax to the variation data on the single product page form.
     *
     * @since 1.0
     * @access public
     *
     * @param array                $data      Variation data.
     * @param WC_Product_Variable  $parent_product    Parent variable product object.
     * @param WC_Product_Variation $variation Variation product object.
     */
    public function add_price_without_tax_to_variation_data( $data, $parent_product, $variation ) {
        $tax_display = get_option( 'woocommerce_tax_display_shop', 'incl' );

        $data['display_price_no_tax']   = (float) wc_get_price_excluding_tax(
            $variation,
            array(
                'qty' => 1,
                $variation->get_price(),
            )
        );
        $data['display_price_with_tax'] = (float) wc_get_price_including_tax(
            $variation,
            array(
                'qty' => 1,
                $variation->get_price(),
            )
        );
        return $data;
    }

    /**
     * Get notice message template for cart, checkout and product.
     *
     * @since 1.2
     * @since 1.8.6 Expose the function to public.
     * @access public
     *
     * @param string $option_key Option key.
     * @return string Notice message.
     */
    public function get_notice_message_template( $option_key ) {
        // if user is not logged in, then we try to display alternative guest message.
        if ( ! is_user_logged_in() ) {

            $guest_notices = array(
                'cart'     => $this->_constants->POINTS_EARN_CART_MESSAGE_GUEST,
                'checkout' => $this->_constants->POINTS_EARN_CHECKOUT_MESSAGE_GUEST,
                'product'  => $this->_constants->POINTS_EARN_PRODUCT_MESSAGE_GUEST,
            );

            $option_name = isset( $guest_notices[ $option_key ] ) ? $guest_notices[ $option_key ] : '';
            $message     = $option_name ? get_option( $option_name ) : '';

            // return early if guest message is present.
            if ( $message ) {
                return apply_filters( 'acfw_string_option', $message, $option_name ); // filter for WPML Support.
            }
        }

        $logged_in_notices = array(
            'cart'     => $this->_constants->POINTS_EARN_CART_MESSAGE,
            'checkout' => $this->_constants->POINTS_EARN_CHECKOUT_MESSAGE,
            'product'  => $this->_constants->POINTS_EARN_PRODUCT_MESSAGE,
        );

        $option_name = isset( $logged_in_notices[ $option_key ] ) ? $logged_in_notices[ $option_key ] : '';
        /* translators: %s: Points amount */
        $default = in_array( $option_key, array( 'cart', 'checkout' ), true ) ? sprintf( __( 'This order will earn %s points.', 'loyalty-program-for-woocommerce' ), '{points}' ) : false;
        $message = $option_name ? get_option( $option_name, $default ) : '';

        return apply_filters( 'acfw_string_option', $message, $option_name ); // filter for WPML Support.
    }

    /*
    |--------------------------------------------------------------------------
    | Custom Labels
    |--------------------------------------------------------------------------
     */

    /**
     * Apply custom label for applied loyalty coupons in the cart.
     *
     * @since 1.4
     * @access public
     *
     * @param string    $label  Coupon label.
     * @param WC_Coupon $coupon Coupon object.
     * @return string Filtered coupon label.
     */
    public function apply_custom_labels_for_loyalty_coupon( $label, $coupon ) {
        // validate if coupon is a loyalty coupon.
        $user_id = $coupon->get_meta( $this->_constants->META_PREFIX . 'loyalty_program_user' );
        if ( $user_id ) {
            $custom_label = get_option( $this->_constants->CUSTOM_COUPON_LABEL );
            $label        = $custom_label ? str_replace( '{coupon_code}', $coupon->get_code(), $custom_label ) : $label;
        }

        return $label;
    }

    /*
    |--------------------------------------------------------------------------
    | Utilities
    |--------------------------------------------------------------------------
     */

    /**
     * Check if we should display points earn message for the currently logged in user.
     *
     * @since 1.5
     * @since 1.8 Add check for store credits discount applied.
     * @since 1.8.6 Set the function to public.
     * @access public
     *
     * @return bool True if allowed, false otherwise.
     */
    public function should_display_points_earn_message() {
        $user_id = get_current_user_id();

        // check if message should be hidden for guests.
        if ( ! $user_id ) {
            $hide_messages_guest = get_option( $this->_constants->HIDE_POINTS_MESSAGE_GUESTS, 'no' );
            return 'yes' !== $hide_messages_guest;
        }

        // validate customer roles.
        if ( ! $this->_helper_functions->validate_user_roles( $user_id ) ) {
            return false;
        }

        // invalidate if a loyalty coupon is applied.
        if ( 'yes' === get_option( $this->_constants->DISALLOW_EARN_POINTS_COUPON_APPLIED, 'no' ) ) {
            foreach ( \WC()->cart->get_coupons() as $coupon ) {
                if ( $coupon->get_meta( $this->_constants->COUPON_USER ) ) {
                    return false;
                }
            }
        }

        // invalidate if store credit discount is applied.
        if ( 'yes' === get_option( $this->_constants->DISALLOW_EARN_POINTS_STORE_CREDITS_APPLIED, 'no' ) ) {

            $is_apply_coupon = 'coupon' === get_option( $this->_constants->STORE_CREDIT_APPLY_TYPE, 'coupon' );
            $session_name    = $is_apply_coupon ? $this->_constants->STORE_CREDITS_COUPON_SESSION : $this->_constants->STORE_CREDITS_SESSION;
            $sc_data         = \WC()->session->get( $session_name, null );

            if ( is_array( $sc_data ) && isset( $sc_data['amount'] ) ) {
                return false;
            }
        }

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Fulfill implemented interface contracts
    |--------------------------------------------------------------------------
     */

    /**
     * Execute Messages class.
     *
     * @since 1.0
     * @access public
     * @inherit LPFW\Interfaces\Model_Interface
     */
    public function run() {
        add_action( 'woocommerce_before_cart', array( $this, 'show_point_earn_message_in_cart' ), 5 );
        add_action( 'woocommerce_before_checkout_form', array( $this, 'points_earn_message_in_checkout' ), 30 );
        add_filter( 'woocommerce_update_order_review_fragments', array( $this, 'points_earn_message_checkout_fragments' ) );
        add_filter( 'woocommerce_available_variation', array( $this, 'add_price_without_tax_to_variation_data' ), 10, 3 );
        add_filter( 'woocommerce_cart_totals_coupon_label', array( $this, 'apply_custom_labels_for_loyalty_coupon' ), 10, 2 );
        add_action( 'woocommerce_single_product_summary', array( $this, 'points_earn_message_single_product' ), 35 );
        add_shortcode( 'lpfw_single_products_points_earned_message', array( $this, 'points_earn_message_single_product' ) );
        add_shortcode( 'lpfw_points_earned_message', array( $this, 'points_earn_message_in_cart' ) );
    }
}
