<?php
namespace LPFW\Models;

use ACFWF\Models\Objects\Date_Period_Range;
use LPFW\Abstracts\Abstract_Main_Plugin_Class;
use LPFW\Helpers\Helper_Functions;
use LPFW\Helpers\Plugin_Constants;
use LPFW\Interfaces\Model_Interface;
use LPFW\Objects\Customer;
use LPFW\Interfaces\Deactivatable_Interface;

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
class Calculate implements Model_Interface, Deactivatable_Interface {
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
     * @var Calculate
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

    /**
     * Property that holds the points to price ratio value.
     *
     * @since 1.6
     * @access private
     * @var float
     */
    private $_points_to_price_ratio = null;

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
     * @return Calculate
     */
    public static function get_instance( Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants, Helper_Functions $helper_functions ) {
        if ( ! self::$_instance instanceof self ) {
            self::$_instance = new self( $main_plugin, $constants, $helper_functions );
        }

        return self::$_instance;
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate methods
    |--------------------------------------------------------------------------
     */

    /**
     * Get the points to price ratio value.
     *
     * @since 1.6
     * @access public
     *
     * @return float Points to price ratio.
     */
    public function get_points_to_price_ratio() {
        if ( is_null( $this->_points_to_price_ratio ) ) {
            $this->_points_to_price_ratio = abs( $this->_helper_functions->sanitize_price( get_option( $this->_constants->COST_POINTS_RATIO, '1' ) ) );
        }

        return $this->_points_to_price_ratio;
    }

    /**
     * Get the points to price ratio value for a product based on it's categories.
     * If product has multiple categories, then the least ratio value will be used.
     *
     * @since 1.6
     * @access public
     *
     * @param int $product_id Product ID.
     * @return float Points to price ratio value.
     */
    public function get_product_cat_points_to_price_ratio( $product_id ) {
        $categories = get_the_terms( $product_id, 'product_cat' );
        $ratio      = $this->get_points_to_price_ratio();

        if ( is_array( $categories ) && ! is_wp_error( $categories ) ) {

            $cat_ratios = array_map(
                function ( $c ) {
                return abs( $this->_helper_functions->sanitize_price( get_term_meta( $c->term_id, $this->_constants->PRODUCT_CAT_COST_POINTS_RATIO, true ) ) );
                },
                $categories
            );

            $cat_ratios = array_filter(
                $cat_ratios,
                function ( $r ) {
                return $r > 0;
                }
            );

            if ( ! empty( $cat_ratios ) ) {
                $ratio = apply_filters( 'lpfw_product_cat_points_to_price_ratio', min( $cat_ratios ), $cat_ratios, $product_id );
            }
        }

        return $ratio;
    }

    /**
     * Calculate the total points earned with a given cart/order subtotal.
     *
     * @since 1.0
     * @access public
     *
     * @deprecated 1.6 Not used anymore, but might be used by third party plugins.
     *
     * @param double $calc_total Cart/order calculated total.
     * @return int Points equivalent.
     */
    public function calculate_points_earn( $calc_total ) {
        wc_deprecated_function( '\LPFW\Models\Calculate::' . __FUNCTION__, '1.6' );
        return intval( $calc_total * $this->get_points_to_price_ratio() );
    }

    /**
     * Calculate points worth.
     *
     * @since 1.0
     * @access public
     *
     * @param int  $points User points.
     * @param bool $is_filter Flag to apply filters to the return value or not.
     * @return float Amount points worth.
     */
    public function calculate_redeem_points_worth( $points, $is_filter = true ) {
        $ratio = (int) get_option( $this->_constants->REDEEM_POINTS_RATIO, '10' );
        $value = max( 0, $points / $ratio );

        return $is_filter ? apply_filters( 'acfw_filter_amount', $value ) : $value;
    }

    /**
     * Get minimum threshold value.
     *
     * @since 1.0
     * @access public
     *
     * @return float Minimum threshold value.
     */
    public function get_minimum_threshold() {
        $raw = get_option( $this->_constants->MINIMUM_POINTS_THRESHOLD, '0' );
        return abs( $this->_helper_functions->sanitize_price( $raw ) );
    }

    /**
     * Get total based on points calculated options.
     *
     * @since 1.0
     * @access public
     *
     * @param WC_Order | null $order Order object.
     * @return float Total calculated amount.
     */
    public function get_total_based_on_points_calculate_options( $order = null ) {
        $options    = $this->_helper_functions->get_enabled_points_calc_options();
        $total      = is_object( $order ) ? $order->get_total( 'edit' ) : WC()->cart->get_total( 'edit' );
        $subtotal   = is_object( $order ) ? $order->get_subtotal() : WC()->cart->get_subtotal();
        $shipping   = is_object( $order ) ? $order->get_shipping_total( 'edit' ) : WC()->cart->get_shipping_total();
        $calculated = (float) $total;

        // get total fees.
        if ( is_object( $order ) ) {

            $fees_total = 0;
            foreach ( $order->get_fees() as $item ) {

                $fee_total = $item->get_total();

                if ( 0 > $fee_total ) {
                    $max_discount = round( $subtotal + $fees_total + $shipping, wc_get_price_decimals() ) * -1;

                    if ( $fee_total < $max_discount ) {
                        $item->set_total( $max_discount );
                    }
                }

                $fees_total += $item->get_total();
            }
        } else {
            $fees_total = WC()->cart->get_fee_total();
        }

        // get shipping overrides discount.
        $shipping_discount = 0;
        if ( is_object( $order ) ) {
            $shipping_discount = array_reduce(
                $order->get_fees(),
                function ( $c, $f ) {
                    return $this->is_fee_shipping_override( $f ) ? $c + $f->get_total() : $c;
                },
                0
            );
        } else {
            $shipping_discount = array_reduce(
                WC()->cart->get_fees(),
                function ( $c, $f ) {
                return $this->is_fee_shipping_override( $f ) ? $c + $f->total : $c;
                },
                0
            );
        }

        // deduct shipping overrides discount from fees total.
        $fees_total = max( 0, $fees_total - $shipping_discount );

        // discounts.
        if ( ! in_array( 'discounts', $options, true ) ) {
            $discounts   = is_object( $order ) ? $order->get_total_discount() : WC()->cart->get_discount_total();
            $calculated += $discounts;
        }

        // tax.
        if ( ! in_array( 'tax', $options, true ) ) {
            $tax_total   = is_object( $order ) ? $order->get_total_tax( 'edit' ) : WC()->cart->get_total_tax();
            $calculated -= $tax_total;
        }

        // shipping.
        if ( ! in_array( 'shipping', $options, true ) ) {
            $calculated -= $shipping;
        }

        // shipping overrides discount.
        if ( ! in_array( 'discounts', $options, true ) || ! in_array( 'shipping', $options, true ) ) {
            $calculated += abs( $shipping_discount );
        }

        // fees.
        if ( ! in_array( 'fees', $options, true ) ) {
            $calculated -= $fees_total;
        }

        // currency switcher support.
        $calculated = apply_filters( 'acfw_filter_amount', $calculated, true );

        return apply_filters( 'lpfw_calculate_totals', max( 0, $calculated ), $order );
    }

    /*
    |--------------------------------------------------------------------------
    | Cart points related calculations
    |--------------------------------------------------------------------------
     */

    /**
     * Get total points based on the cart items and totals based on the calc options enabled in the settings.
     *
     * @since 1.6
     * @since 1.6.1 Improve calculation for fees and shipping discount.
     * @access public
     *
     * @return int Total points.
     */
    public function get_cart_total_points() {
        $calc_options      = $this->_helper_functions->get_enabled_points_calc_options();
        $product_points    = 0;
        $fees_shipping_tax = 0.0;
        $shipping_discount = 0.0;

        // Cart items.
        foreach ( \WC()->cart->get_cart_contents() as $cart_item ) {
            $product_points += $this->calculate_product_points( $cart_item['data'], $cart_item['quantity'] );
        }

        // Cart fees. Separate shipping overrides discount from cart fees.
        foreach ( \WC()->cart->get_fees() as $fee ) {
            if ( $this->is_fee_shipping_override( $fee ) ) {
                $shipping_discount += abs( $fee->amount ); // fee value is negative so we need to get the absolute equivalent.
            } elseif ( in_array( 'fees', $calc_options, true ) ) {
                $fees_shipping_tax += $fee->amount;
            }
        }

        // Shipping.
        if ( in_array( 'shipping', $calc_options, true ) ) {
            $fees_shipping_tax += \WC()->cart->get_shipping_total();
        }

        // Tax.
        if ( in_array( 'tax', $calc_options, true ) ) {
            $fees_shipping_tax += \WC()->cart->get_total_tax();
        }

        // Discounts.
        if ( in_array( 'discounts', $calc_options, true ) ) {
            $fees_shipping_tax -= \WC()->cart->get_discount_total();

            // Deduct shipping discount when both shipping and discount calc options are enabled.
            if ( in_array( 'shipping', $calc_options, true ) ) {
                $fees_shipping_tax -= $shipping_discount;
            }
        }

        // Deduct store credits discount (after tax type).
        $sc_data = \WC()->session->get( $this->_constants->STORE_CREDITS_SESSION, null );
        if ( is_array( $sc_data ) && isset( $sc_data['amount'] ) ) {
            $fees_shipping_tax -= $sc_data['amount'];
        }

        $fees_shipping_tax = apply_filters( 'acfw_filter_amount', $fees_shipping_tax, true );
        $total_points      = max( 0, intval( $product_points + ( $fees_shipping_tax * $this->get_points_to_price_ratio() ) ) );

        return $this->apply_maximum_allowed_purchase_product_points( $total_points );
    }

    /**
     * Calculate points for a given product.
     *
     * @since 1.6
     * @access public
     *
     * @param WC_Product $product     Product object.
     * @param int        $quantity    Product quantity.
     * @param bool       $include_tax Flag to include tax on price or not.
     * @return int Cart item points.
     */
    public function calculate_product_points( $product, $quantity = 1, $include_tax = false ) {
        // Get the price first before overwriting the $product variable.
        $price = apply_filters( 'acfw_filter_amount', $this->_helper_functions->get_price( $product, $include_tax ), true );

        // Get the parent variable product if the $product is a variation.
        if ( $product instanceof \WC_Product_Variation ) {
            $product = wc_get_product( $product->get_parent_id() );
        }

        if (
            'yes' !== $this->_helper_functions->is_product_allowed_to_earn_points( $product )
            || ! $this->_helper_functions->is_product_categories_allowed_to_earn_points( $product->get_id() )
        ) {
            return 0;
        }

        // return product custom points multiplied by quantity if present.
        $custom_points = $product->get_meta( $this->_constants->PRODUCT_CUSTOM_POINTS, true, 'edit' );
        if ( $custom_points ) {
            return intval( $custom_points ) * $quantity;
        }

        // return normal calculated points.
        return $price * $quantity * $this->get_product_cat_points_to_price_ratio( $product->get_id() );
    }

    /**
     * Apply the maximum allowed points for the "purchasing products" action.
     *
     * @since 1.8.7
     * @access public
     *
     * @param int $total_points Total points.
     * @return int Total points after applying the maximum allowed points.
     */
    public function apply_maximum_allowed_purchase_product_points( $total_points ) {
        $max_allowed_points = $this->_helper_functions->get_option( $this->_constants->BUY_PRODUCT_MAX_ALLOWED_POINTS, 0 );

        // limit the points earned to the maximum allowed points.
        if ( $max_allowed_points > 0 && $total_points > $max_allowed_points ) {
            $total_points = $max_allowed_points;
        }

        return $total_points;
    }

    /*
    |--------------------------------------------------------------------------
    | Order points related calculations
    |--------------------------------------------------------------------------
     */

    /**
     * Get total points based on the order line item totals based on the calc options enabled in the settings.
     *
     * @since 1.6
     * @since 1.6.1 Improve calculation for fees and shipping discount.
     * @access public
     *
     * @param WC_Order $order Order object.
     * @return int Total points.
     */
    public function get_order_total_points( $order ) {
        $calc_options      = $this->_helper_functions->get_enabled_points_calc_options();
        $product_points    = 0;
        $fees_shipping_tax = 0.0;
        $shipping_discount = 0.0;

        // Order items.
        foreach ( $order->get_items( 'line_item' ) as $order_item ) {
            $product_points += $this->_calculate_order_item_points( $order_item );
        }

        // Order fees. Separate shipping overrides discount from cart fees.
        foreach ( $order->get_fees() as $fee ) {
            if ( $this->is_fee_shipping_override( $fee ) ) {
                $shipping_discount += abs( $fee->get_total() ); // fee value is negative so we need to get the absolute equivalent.
            } elseif ( in_array( 'fees', $calc_options, true ) ) {
                $fees_shipping_tax += $fee->get_total();
            }
        }

        // Shipping.
        if ( in_array( 'shipping', $calc_options, true ) ) {
            $fees_shipping_tax = $fees_shipping_tax + $order->get_shipping_total( 'edit' );
        }

        // Tax.
        if ( in_array( 'tax', $calc_options, true ) ) {
            $fees_shipping_tax += $order->get_total_tax( 'edit' );
        }

        // Discounts.
        if ( in_array( 'discounts', $calc_options, true ) ) {
            $fees_shipping_tax -= $order->get_total_discount();

            // Deduct shipping discount when both shipping and discount calc options are enabled.
            if ( in_array( 'shipping', $calc_options, true ) ) {
                $fees_shipping_tax -= $shipping_discount;
            }
        }

        $fees_shipping_tax = apply_filters( 'acfw_filter_amount', $fees_shipping_tax, true );
        $total_points      = max( 0, intval( $product_points + ( $fees_shipping_tax * $this->get_points_to_price_ratio() ) ) );

        return $this->apply_maximum_allowed_purchase_product_points( $total_points );
    }

    /**
     * Calculate points for a given order item.
     *
     * @since 1.6
     * @since 1.8.3 Use order item total to calculate points instead of fetching product price so the price value for the order is concise.
     * @access private
     *
     * @param \WC_Order_Item_Product $order_item Order product line item.
     * @return int Order product line item points.
     */
    public function _calculate_order_item_points( $order_item ) {
        $product = wc_get_product( $order_item->get_product_id() );

        if (
            'yes' !== $this->_helper_functions->is_product_allowed_to_earn_points( $product )
            || ! $this->_helper_functions->is_product_categories_allowed_to_earn_points( $order_item->get_product_id() )
        ) {
            return 0;
        }

        // return product custom points multiplied by quantity if present.
        $custom_points = $product->get_meta( $this->_constants->PRODUCT_CUSTOM_POINTS, true, 'edit' );
        if ( $custom_points ) {
            return intval( $custom_points ) * $order_item->get_quantity();
        }

        $order  = $order_item->get_order();
        $amount = apply_filters( 'acfw_filter_amount', $order_item->get_subtotal( 'edit' ), true, array( 'user_currency' => $order->get_currency() ) );

        // return normal calculated points.
        return $amount * $this->get_product_cat_points_to_price_ratio( $order_item->get_product_id() );
    }

    /*
    |--------------------------------------------------------------------------
    | Additional features calculations
    |--------------------------------------------------------------------------
     */

    /**
     * Calculate high spend points based on subtotal value excluding discounts.
     *
     * @since 1.0
     * @access public
     *
     * @param float $order_total Order/cart total amount.
     * @return int High spend calculated points.
     */
    public function calculate_high_spend_points( $order_total ) {
        $breakpoints = get_option( $this->_constants->EARN_POINTS_BREAKPOINTS, array() );
        $breakpoints = is_array( $breakpoints ) ? $breakpoints : json_decode( $breakpoints, true );
        $order_total = (float) $order_total;
        $points      = 0;

        if ( ! is_array( $breakpoints ) || empty( $breakpoints ) ) {
            return $points;
        }

        // sort breakpoints from highest to lowest to get concise value.
        usort(
            $breakpoints,
            function ( $a, $b ) {
            if ( $a['sanitized'] === $b['sanitized'] ) {
                return 0;
            }

            return ( $a['sanitized'] > $b['sanitized'] ) ? -1 : 1;
            }
        );

        foreach ( $breakpoints as $breakpoint ) {

            $amount = (float) $breakpoint['sanitized'];
            $amount = apply_filters( 'acfw_filter_amount', $amount, true ); // currency switcher support.

            if ( $order_total >= $amount ) {
                $points = absint( $breakpoint['points'] );
                break;
            }
        }

        return $points;
    }

    /**
     * Get matching period points based on order.
     *
     * @since 1.0
     * @access public
     *
     * @param WC_Order $order Order object.
     * @return int Earned points.
     */
    public function get_matching_period_points( $order = null ) {
        $points = 0;
        $data   = get_option( $this->_constants->EARN_POINTS_ORDER_PERIOD, array() );
        $data   = is_array( $data ) ? $data : json_decode( $data, true );

        if ( ! is_array( $data ) || empty( $data ) ) {
            return $points;
        }

        $order_date = $order ? $order->get_date_created() : null;
        $timezone   = $order_date ? $order_date->getTimezone() : new \DateTimeZone( $this->_helper_functions->get_site_current_timezone() );

        if ( ! $order_date ) {
            $order_date = new \WC_DateTime( 'now', $timezone );
        }

        foreach ( $data as $row ) {

            $start_date = \WC_DateTime::createFromFormat( 'm/d/Y g:i A', $row['sdate'] . ' ' . $row['stime'], $timezone );
            $end_date   = \WC_DateTime::createFromFormat( 'm/d/Y g:i A', $row['edate'] . ' ' . $row['etime'], $timezone );

            if ( $order_date >= $start_date && $order_date <= $end_date ) {
                return absint( $row['points'] );
            }
}

        return $points;
    }

    /*
    |--------------------------------------------------------------------------
    | User related calculations
    |--------------------------------------------------------------------------
     */

    /**
     * Get total points of user.
     *
     * @since 1.0
     * @since 1.7.1 Rewrite function to use the new Customer object instead to calculate customer's points.
     * @access private
     *
     * @global wpdb $wpdb Object that contains a set of functions used to interact with a database.
     *
     * @param int $user_id User ID.
     * @return int User total points.
     */
    public function get_user_total_points( $user_id ) {
        $user_id  = $user_id > 0 ? $user_id : get_current_user_id();
        $customer = new Customer( $user_id );

        // This function will calculate fresh points and check if we need to expire customer's points or not.
        $customer->maybe_expire_customer_points();

        return $customer->get_points();
    }

    /**
     * Query user's points balance, worth and expiry.
     *
     * @since 1.7.1
     * @access public
     *
     * @param int $user_id User ID.
     * @return array Current user's balance data.
     */
    public function get_user_points_balance_data( $user_id = 0 ) {
        $user_id  = $user_id > 0 ? $user_id : get_current_user_id();
        $customer = new Customer( $user_id );

        // This function will calculate fresh points and check if we need to expire customer's points or not.
        $customer->maybe_expire_customer_points();

        $points          = $customer->get_points();
        $expiry          = $customer->get_points_expiry();
        $datetime_format = $this->_helper_functions->get_default_datetime_format();

        return array(
            'points' => $points,
            'worth'  => $this->_helper_functions->api_wc_price( $this->calculate_redeem_points_worth( $points ) ),
            'expiry' => $points && is_object( $expiry ) ? $expiry->date_i18n( $datetime_format ) : '',
        );
    }

    /**
     * Get user last active date cached value.
     *
     * @deprecated 1.7.1
     *
     * @since 1.0
     * @access public
     *
     * @return DateTIme
     */
    public function get_last_active() {
        wc_deprecated_function( '\LPFW\Models\Calculate::' . __FUNCTION__, '1.7.1' );
        return null;
    }

    /**
     * Calculate allowed maximum points for redemption. This will return either of the options which has the
     * lowest value: cart subtotal equivalent points, user unclaimed points or max points set on setting.
     *
     * NOTE: We are using displayed subtotal amount to get maximum allowed points based on subtotal. So if the site is
     * displaying price inclusive of tax, then tax is added to the allowed maximum points.
     *
     * @since 1.3
     * @access public
     *
     * @param int  $user_points User points.
     * @param bool $is_checkout Flag if calculation is for checkout or not.
     * @return int Allowed maximum points.
     */
    public function calculate_allowed_max_points( $user_points, $is_checkout = false ) {
        $points_data = array( $user_points );

        if ( $is_checkout ) {
            $subtotal_amount = apply_filters( 'acfw_filter_amount', \WC()->cart->get_displayed_subtotal() );
            $ratio           = (int) get_option( $this->_constants->REDEEM_POINTS_RATIO, '10' );
            $points_data[]   = $subtotal_amount * $ratio;
        }

        $settings_max = (int) $this->_helper_functions->get_option( $this->_constants->MAXIMUM_POINTS_REDEEM, '0' );
        if ( 0 < $settings_max ) {
            $points_data[] = $settings_max;
        }

        return min( $points_data );
    }

    /**
     * Get pending points total for a specific order.
     *
     * @since 1.2
     * @access public
     *
     * @param int $order_id Order ID.
     * @return int Total pending points.
     */
    public function get_order_pending_points_total( $order_id ) {
        global $wpdb;

        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(entry_amount) FROM {$wpdb->acfw_loyalprog_entries} WHERE entry_type = 'pending_earn' AND object_id = %d",
                $order_id
            )
        );
    }

    /**
     * Update user points cache after total points have been changed.
     *
     * @since 1.6
     * @access public
     *
     * @param Point_Entry $point_entry Point entry object.
     */
    public function update_user_points_cache_after_total_changed( $point_entry ) {
        $user_id  = $point_entry->get_prop( 'user_id', 'edit' );
        $customer = new Customer( $user_id );

        update_user_meta( $user_id, $this->_constants->USER_TOTAL_POINTS, $customer->get_points( true ) );
    }

    /*
    |--------------------------------------------------------------------------
    | Cart Session
    |--------------------------------------------------------------------------
     */

    /**
     * Check if the cart session and settings setup is still the same.
     *
     * @since 1.6
     * @access public
     *
     * @return bool True if still the same, false otherwise.
     */
    public function is_same_cart_session() {
        $data          = \WC()->session->get( $this->_constants->CART_POINTS_SESSION );
        $settings_hash = $this->get_settings_hash();

        return $data && $settings_hash === $data['settings_hash'] && \WC()->cart->get_cart_hash() === $data['cart_hash'];
    }

    /**
     * Save calculated cart points value to session.
     *
     * @since 1.6
     * @access public
     *
     * @param int $points Cart points.
     */
    public function save_calculated_cart_points_to_session( $points ) {
        \WC()->session->set(
            $this->_constants->CART_POINTS_SESSION,
            array(
                'points'        => $points,
                'settings_hash' => $this->generate_new_settings_hash(),
                'cart_hash'     => \WC()->cart->get_cart_hash(),
            )
        );
    }

    /**
     * Clear calculated cart points session data.
     *
     * @since 1.8.6
     * @access public
     */
    public function clear_calculated_cart_points_session() {
        \WC()->session->set( $this->_constants->CART_POINTS_SESSION, null );
    }

    /**
     * Clear points session data after the store credits discount (after tax) was applied to the cart.
     *
     * @since 1.8.1
     * @access public
     *
     * @param array $sc_data Store Credits data.
     * @return array Filtered Store Credits data.
     */
    public function clear_points_session_data_after_store_credits_discount_applied( $sc_data ) {
        $is_apply_coupon = 'coupon' === get_option( $this->_constants->STORE_CREDIT_APPLY_TYPE, 'coupon' );

        if ( ! $is_apply_coupon ) {
            $this->clear_calculated_cart_points_session();
        }

        return $sc_data;
    }

    /**
     * Get settings hash value.
     *
     * @since 1.6
     * @access public
     *
     * @return string Settings hash.
     */
    public function get_settings_hash() {
        $settings_hash = get_option( $this->_constants->SETTINGS_HASH );
        return $settings_hash ? $settings_hash : $this->generate_new_settings_hash();
    }

    /**
     * Generate new settings hash.
     *
     * @since 1.6
     * @access public
     *
     * @return string Settings hash.
     */
    public function generate_new_settings_hash() {
        $settings_hash = md5( 'lpfw_' . time() );
        update_option( $this->_constants->SETTINGS_HASH, $settings_hash );

        return $settings_hash;
    }

    /**
     * Calculate statistics for loyalty points earned and used within a given date period range data.
     *
     * @since 1.5.3
     * @access public
     *
     * @param Date_Period_Range $report_period Date period range object.
     * @return array Calculated period statistics.
     */
    public function calculate_loyalty_points_period_statistics( Date_Period_Range $report_period ) {
        $report_period->use_utc_timezone();

        $cache_key   = $report_period->generate_period_cache_key( 'lpfw_loyalty_points_stats::%s::%s' );
        $cached_data = get_transient( $cache_key );

        // return cached data if already present in object cache.
        if ( is_array( $cached_data ) && isset( $cached_data['earned_in_period'] ) && isset( $cached_data['used_in_period'] ) ) {
            $report_period->use_site_timezone(); // reset timezone back to site timezone.
            return $cached_data;
        }

        $period_params = array(
            'start_period' => $report_period->start_period->format( 'Y-m-d H:i:s' ),
            'end_period'   => $report_period->end_period->format( 'Y-m-d H:i:s' ),
        );

        $data = array(
            'earned_in_period' => $this->_get_loyalty_points_sum( array_merge( array( 'type' => 'earn' ), $period_params ) ),
            'used_in_period'   => $this->_get_loyalty_points_sum( array_merge( array( 'type' => 'redeem' ), $period_params ) ),
        );

        // save data to the cache for a maximum of one day.
        set_transient( $cache_key, $data, 'lpfw', DAY_IN_SECONDS );

        // reset timezone back to site timezone.
        $report_period->use_site_timezone();

        return $data;
    }

    /**
     * Get the sum of all loyalty points based on the provided paramaters.
     *
     * @since 1.5.3
     * @access private
     *
     * @param array $params Query parameters.
     * @return int Sum of loyalty points.
     */
    private function _get_loyalty_points_sum( $params = array() ) {
        global $wpdb;

        $params = wp_parse_args(
            $params,
            array(
                'user_id'      => 0,
                'type'         => '',
                'action'       => '',
                'start_period' => '',
                'end_period'   => '',
            )
        );
        // the output of this extract is defined above.
        extract( $params ); // phpcs:ignore

        $user_query   = $user_id ? $wpdb->prepare( 'AND user_id = %d', $user_id ) : '';
        $type_query   = $type ? $wpdb->prepare( 'AND entry_type = %s', $type ) : '';
        $action_query = $action ? $wpdb->prepare( 'AND entry_action = %s', $action ) : '';
        $period_query = $start_period && $end_period ? $wpdb->prepare( 'AND entry_date BETWEEN %s AND %s', $start_period, $end_period ) : '';

        // build query.
        $query = "SELECT SUM(entry_amount) FROM {$wpdb->acfw_loyalprog_entries}
            WHERE 1
            {$user_query} {$type_query} {$action_query} {$period_query}
        ";

        return intval( $wpdb->get_var( $query ) ); // phpcs:ignore
    }

    /**
     * Delete store credits cached data.
     *
     * @since 1.5.3
     * @access public
     */
    public function delete_loyalty_points_cached_data() {
        global $wpdb;
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%lpfw_loyalty_points_stats%'" );
    }

    /**
     * Check if a given cart or order fee is for shipping override feature.
     *
     * @since 1.6.1
     * @access public
     *
     * @param \WC_Order_Item_FEE|object $fee Order item fee instance or cart item fee object.
     * @return bool True if fee is for shipping override, false otherwise.
     */
    public function is_fee_shipping_override( $fee ) {
        $fee_name = $fee instanceof \WC_Order_Item_Fee ? $fee->get_name() : $fee->name;
        $fee_id   = $fee instanceof \WC_Order_Item_Fee ? $fee->get_meta( 'acfw_fee_cart_id' ) : $fee->name;

        return strpos( $fee_name, '[shipping_discount]' ) !== false || strpos( $fee_id, 'acfw-shipping-discount' ) !== false;
    }

    /**
     * Calculate recently imported points.
     *
     * @since 1.8.3
     * @access public
     *
     * @param string $start_period Date to calculate points for.
     * @param string $end_period   Optional end date to calculate points for.
     * @return int Total points imported.
     */
    public function calculate_recently_imported_points( $start_period, $end_period = '' ) {
        $end_period = $end_period ? $end_period : current_time( 'mysql', true );
        $params     = array(
            'action'       => 'imported_points',
            'start_period' => $start_period,
            'end_period'   => $end_period,
        );

        return $this->_get_loyalty_points_sum( $params );
    }

    /*
    |--------------------------------------------------------------------------
    | Fulfill implemented interface contracts
    |--------------------------------------------------------------------------
     */

    /**
     * Contract for deactivate.
     *
     * @since 1.5.3
     * @access public
     * @implements AGCFW\Interfaces\Deactivatable_Interface
     */
    public function deactivate() {
        $this->delete_loyalty_points_cached_data();
    }

    /**
     * Execute Calculate class.
     *
     * @since 1.0
     * @access public
     * @inherit LPFW\Interfaces\Model_Interface
     */
    public function run() {
        add_action( 'lpfw_loyalty_points_total_changed', array( $this, 'update_user_points_cache_after_total_changed' ) );
        add_action( 'lpfw_settings_updated', array( $this, 'generate_new_settings_hash' ) );
        add_action( 'lpfw_loyalty_points_total_changed', array( $this, 'delete_loyalty_points_cached_data' ) );
        add_filter( 'acfw_store_credits_discount_session', array( $this, 'clear_points_session_data_after_store_credits_discount_applied' ) );
        add_action( 'wp_login', array( $this, 'clear_calculated_cart_points_session' ) );
    }
}
