<?php
namespace AGCFW\Models;

use AGCFW\Abstracts\Abstract_Main_Plugin_Class;
use AGCFW\Helpers\Helper_Functions;
use AGCFW\Helpers\Plugin_Constants;
use AGCFW\Interfaces\Model_Interface;
use AGCFW\Objects\Advanced_Gift_Card;
use AGCFW\Objects\Product;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Model that houses the logic of the Purchasing module.
 *
 * @since 1.0
 */
class Purchasing implements Model_Interface {
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
     * @var Purchasing
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
     * @return Purchasing
     */
    public static function get_instance( Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants, Helper_Functions $helper_functions ) {
        if ( ! self::$_instance instanceof self ) {
            self::$_instance = new self( $main_plugin, $constants, $helper_functions );
        }

        return self::$_instance;
    }

    /**
     * Process advanced gift card add to cart action.
     *
     * @since 1.0
     * @since 1.2 Add delivery date field support.
     * @access public
     */
    public function process_gift_card_add_to_cart() {
        $post_data         = $_REQUEST; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $product_id        = absint( wp_unslash( $post_data['add-to-cart'] ) );
        $quantity          = empty( $post_data['quantity'] ) ? 1 : wc_stock_amount( wp_unslash( $post_data['quantity'] ) );
        $passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity ); // we are intentionally using the WC hook here.
        $send_to           = isset( $post_data['send_to'] ) ? sanitize_text_field( $post_data['send_to'] ) : 'me';
        $customer_timezone = isset( $post_data['timezone'] ) ? sanitize_text_field( $post_data['timezone'] ) : '';

        if ( 'friend' === $send_to ) {
            $data = array(
                'agcfw_data' => array(
                    'send_to'         => 'friend',
                    'recipient_name'  => isset( $post_data['recipient_name'] ) ? sanitize_text_field( $post_data['recipient_name'] ) : '',
                    'recipient_email' => isset( $post_data['recipient_email'] ) ? sanitize_text_field( $post_data['recipient_email'] ) : '',
                    'short_message'   => isset( $post_data['short_message'] ) ? sanitize_text_field( $post_data['short_message'] ) : '',
                ),
            );

            if ( isset( $post_data['delivery_date'] ) && $post_data['delivery_date'] ) {
                $data['agcfw_data']['delivery_date'] = $this->_sanitize_delivery_date_value( $post_data['delivery_date'], $customer_timezone );
            }
        } else {
            $data = array(
                'agcfw_data' => array(
                    'send_to' => 'me',
                ),
            );
        }

        if ( $passed_validation && false !== $this->_add_to_cart( $product_id, $quantity, $data ) ) {
            wc_add_to_cart_message( array( $product_id => $quantity ), true );
            return true;
        }

        return false;
    }

    /**
     * Appending missing cart item data for gift cards.
     * This is for when products are added to cart directly via AJAX.
     *
     * @since 1.0
     * @access public
     *
     * @param array $cart_item_data Cart item data.
     * @param int   $product_id     Product ID.
     * @return array Filtered cart item data.
     */
    public function append_missing_gift_card_data( $cart_item_data, $product_id ) {
        if ( ! isset( $cart_item_data['agcfw_data'] ) ) {
            $product = wc_get_product( $product_id );

            if ( $product instanceof Product ) {
                $cart_item_data['agcfw_data'] = array(
                    'send_to' => 'me',
                );
            }
        }

        return $cart_item_data;
    }

    /**
     * Append gift card recipient data when "send to" is set to "friend" to the displayed item data list in the cart item row.
     *
     * @since 1.0
     * @since 1.2 Add delivery date field support.
     * @access public
     *
     * @param array $item_data Displayed cart item data.
     * @param array $cart_item Cart item data.
     * @return array Filtered Displayed cart item data.
     */
    public function append_gift_card_cart_item_recipient_data( $item_data, $cart_item ) {
        if ( isset( $cart_item['agcfw_data'] ) && isset( $cart_item['agcfw_data']['send_to'] ) && 'friend' === $cart_item['agcfw_data']['send_to'] ) {

            $item_data[] = array(
				'key'   => __( 'Recipient name', 'advanced-gift-cards-for-woocommerce' ),
				'value' => wp_unslash( $cart_item['agcfw_data']['recipient_name'] ),
            );

            $item_data[] = array(
				'key'   => __( 'Recipient email', 'advanced-gift-cards-for-woocommerce' ),
				'value' => wp_unslash( $cart_item['agcfw_data']['recipient_email'] ),
            );

            if ( isset( $cart_item['agcfw_data']['short_message'] ) && $cart_item['agcfw_data']['short_message'] ) {
                $item_data[] = array(
                    'key'   => __( 'Short message', 'advanced-gift-cards-for-woocommerce' ),
                    'value' => wp_unslash( $cart_item['agcfw_data']['short_message'] ),
                );
            }

            if ( isset( $cart_item['agcfw_data']['delivery_date'] ) && $cart_item['agcfw_data']['delivery_date'] instanceof \WC_DateTime ) {
                $item_data[] = array(
                    'key'   => __( 'Delivery date', 'advanced-gift-cards-for-woocommerce' ),
                    'value' => $cart_item['agcfw_data']['delivery_date']->date_i18n( $this->_helper_functions->get_wp_datetime_format() . ' e' ),
                );
            }
        }

        return $item_data;
    }

    /**
     * Save gift card data as order line item meta data during checkout process.
     *
     * @since 1.0
     * @since 1.2 Add delivery date field support.
     * @access public
     *
     * @param WC_Order_Item_Product $item          Order item object.
     * @param string                $cart_item_key Cart item key.
     * @param array                 $cart_item     Cart item data.
     */
    public function save_gift_card_data_as_order_line_item_meta( $item, $cart_item_key, $cart_item ) {
        // skip if item is not a gift card.
        if ( ! isset( $cart_item['agcfw_data'] ) || ! $cart_item['data'] instanceof Product ) {
            return;
        }

        $send_to = sanitize_text_field( $cart_item['agcfw_data']['send_to'] );

        $item->add_meta_data( $this->_constants->GIFT_CARD_SEND_TO_META, $send_to );
        $item->add_meta_data( $this->_constants->GIFT_CARD_DATA, $cart_item['data']->get_gift_card_data() );

        if ( 'friend' === $send_to ) {
            $item->add_meta_data( $this->_constants->GIFT_CARD_RECIPIENT_NAME_META, sanitize_text_field( $cart_item['agcfw_data']['recipient_name'] ) );
            $item->add_meta_data( $this->_constants->GIFT_CARD_RECIPIENT_EMAIL_META, sanitize_text_field( $cart_item['agcfw_data']['recipient_email'] ) );

            if ( isset( $cart_item['agcfw_data']['short_message'] ) ) {
                $item->add_meta_data( $this->_constants->GIFT_CARD_SHORT_MESSAGE_META, sanitize_text_field( $cart_item['agcfw_data']['short_message'] ) );
            }

            if ( isset( $cart_item['agcfw_data']['delivery_date'] ) && $cart_item['agcfw_data']['delivery_date'] instanceof \WC_DateTime ) {
                // get customer timezone from datetime object.
                $customer_timezone = $cart_item['agcfw_data']['delivery_date']->format( 'e' );

                // set timezone to UTC.
                $cart_item['agcfw_data']['delivery_date']->setTimezone( new \DateTimeZone( 'UTC' ) );

                $delivery_timestamp = $cart_item['agcfw_data']['delivery_date']->getTimestamp();
                $current_timestamp  = time();

                // when delivery date is somehow older than the current time, then we just use the current time value as delivery date.
                $delivery_timestamp = $delivery_timestamp > $current_timestamp ? $delivery_timestamp : $current_timestamp;

                // save timestamp value to order item meta.
                $item->add_meta_data( $this->_constants->GIFT_CARD_DELIVERY_DATE_META, $delivery_timestamp );

                // save customer timezone value on a separate order item meta.
                $item->add_meta_data( $this->_constants->GIFT_CARD_CUSTOMER_TIMEZONE_META, $customer_timezone );
            }
        }
    }

    /**
     * Create gift card for order when order status is changed to either "processing" or "completed".
     *
     * @since 1.0
     * @since 1.2 Gift card creation is now done via scheduler.
     * @access public
     *
     * @param int      $order_id    Order ID.
     * @param string   $prev_status Previous status.
     * @param string   $new_status  New status.
     * @param WC_Order $order       Order object.
     */
    public function create_gift_card_for_order( $order_id, $prev_status, $new_status, $order ) {
        // skip if status is not processing or completed.
        if ( ! in_array( $new_status, wc_get_is_paid_statuses(), true ) ) {
            return;
        }

        foreach ( $order->get_items( 'line_item' ) as $item ) {

            // skip if item is not a gift card, or when gift card was already created for the item.
            if ( ! $item->get_meta( $this->_constants->GIFT_CARD_SEND_TO_META ) ) {
                continue;
            }

            $gift_card_id = $item->get_meta( $this->_constants->GIFT_CARD_ENTRY_ID_META );
            if ( $gift_card_id ) {

                $gift_card = new Advanced_Gift_Card( $gift_card_id );

                // Only update gift cards that have been invalidated.
                if ( 'invalid' === $gift_card->get_prop( 'status' ) ) {
                    $gift_card->set_prop( 'status', 'pending' );
                    $gift_card->save();
                }
            } else {

                $search_key   = sprintf( 'agc_item_%s', $item->get_id() );
                $is_scheduled = \WC()->queue()->get_next( $this->_constants->CREATE_GIFT_CARD_ACTION_SCHEDULE, array( $item->get_id(), $search_key ), 'AGC' );

                if ( is_null( $is_scheduled ) ) {

                    // get delivery schedule timestamp. timestamp value should always be in UTC timezone.
                    $timestamp = $item->get_meta( $this->_constants->GIFT_CARD_DELIVERY_DATE_META );
                    $timestamp = $timestamp ? $timestamp : time();

                    // schedule gift card email delivery based on the set timestamp.
                    \WC()->queue()->schedule_single( $timestamp, $this->_constants->CREATE_GIFT_CARD_ACTION_SCHEDULE, array( $item->get_id(), $search_key ), 'AGC' );
                }
            }
        }
    }

    /**
     * Create gift card for a given order item.
     * This function is triggered via action scheduler.
     *
     * @since 1.2
     * @access public
     *
     * @param int $order_item_id Order item ID.
     */
    public function create_gift_card_item( $order_item_id ) {
        $order_item = new \WC_Order_Item_Product( $order_item_id );
        $order      = $order_item->get_id() ? $order_item->get_order() : null;
        $data       = $order_item->get_id() ? $order_item->get_meta( $this->_constants->GIFT_CARD_DATA ) : null;

        // skip if data value is not available or when order is not valid.
        if ( ! $order instanceof \WC_Order || ! is_array( $data ) || ! isset( $data['value'] ) ) {
            return;
        }

        $gift_card = new Advanced_Gift_Card();
        $gift_card->set_prop( 'order_item_id', $order_item->get_id() );
        $gift_card->set_prop( 'value', (float) $data['value'] );
        $gift_card->set_prop( 'status', 'pending' );

        // set gift card expiry.
        if ( isset( $data['expiry'] ) ) {
            $gift_card->set_date_expire_by_interval( $data['expiry'] );
        }

        // create gift card entry.
        $check = $gift_card->save();

        // save gift card ID to order item meta.
        $order_item->add_meta_data( $this->_constants->GIFT_CARD_ENTRY_ID_META, $gift_card->get_id() );
        $order_item->save_meta_data();

        // Load WC mailer instance as for some reason the WC doesn't load the mailer instance on cron.
        \WC()->mailer();

        do_action( 'agcfw_after_create_gift_card_for_order', $gift_card, $order_item, $order );
    }

    /**
     * Invalidate gift card when order status is changed from either "processing" or "completed" to either "cancelled", "refunded", or "failed.
     *
     * @since 1.0
     * @access public
     *
     * @param int      $order_id    Order ID.
     * @param string   $prev_status Previous status.
     * @param string   $new_status  New status.
     * @param WC_Order $order       Order object.
     */
    public function invalidate_gift_card_on_order_status_change( $order_id, $prev_status, $new_status, $order ) {
        if ( ! in_array( $prev_status, wc_get_is_paid_statuses(), true ) || ! in_array( $new_status, array( 'cancelled', 'refunded', 'failed' ), true ) ) {
            return;
        }

        foreach ( $order->get_items( 'line_item' ) as $item ) {

            $gift_card_id = $item->get_meta( $this->_constants->GIFT_CARD_ENTRY_ID_META );
            if ( $gift_card_id ) {

                $gift_card = new Advanced_Gift_Card( $gift_card_id );

                // Only invalidate gift cards that have not been yet used.
                if ( 'pending' === $gift_card->get_prop( 'status' ) ) {
                    $gift_card->set_prop( 'status', 'invalid' );
                    $gift_card->save();
                }
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Utility functions.
    |--------------------------------------------------------------------------
     */

    /**
     * Add advanced gift card to cart.
     *
     * @since 1.0
     * @access private
     *
     * @param int   $product_id Product ID.
     * @param int   $quantity   Item quantity.
     * @param array $data       Gift card data.
     * @return bool True if successfully added to cart, false otherwise.
     */
    private function _add_to_cart( $product_id, $quantity, $data = array() ) {
        return \WC()->cart->add_to_cart(
            $product_id,
            $quantity,
            0,
            array(),
            $data
        );
    }

    /**
     * Sanitize delivery date value from flatpickr date field.
     *
     * @since 1.2
     * @access private
     *
     * @param string $raw_delivery_date Raw delivery date value.
     * @param string $customer_timezone Customer timezone.
     * @return string Sanitized delivery date value (formatted based on WP date/time format setting).
     */
    private function _sanitize_delivery_date_value( $raw_delivery_date, $customer_timezone ) {
        // try to get customer's timezone if it is valid. when invalid, then we'll default to the site's timezone instead.
        try {
            $datetimezone = \timezone_open( $customer_timezone );
        } catch ( \Exception $e ) {
            $datetimezone = \timezone_open( $this->_helper_functions->get_site_current_timezone() );
        }

        $datetime = \DateTime::createFromFormat( $this->_constants->DELIVERY_DATE_FIELD_FORMAT, $raw_delivery_date, $datetimezone );

        return $datetime ? new \WC_DateTime( $datetime->format( $this->_constants->DB_DATE_FORMAT ), $datetimezone ) : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Fulfill implemented interface contracts
    |--------------------------------------------------------------------------
     */

    /**
     * Execute Purchasing class.
     *
     * @since 1.0
     * @access public
     * @inherit AGCFW\Interfaces\Model_Interface
     */
    public function run() {
        add_action( 'woocommerce_add_to_cart_handler_advanced_gift_card', array( $this, 'process_gift_card_add_to_cart' ) );
        add_filter( 'woocommerce_add_cart_item_data', array( $this, 'append_missing_gift_card_data' ), 10, 2 );
        add_filter( 'woocommerce_get_item_data', array( $this, 'append_gift_card_cart_item_recipient_data' ), 10, 2 );
        add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'save_gift_card_data_as_order_line_item_meta' ), 10, 3 );
        add_action( 'woocommerce_order_status_changed', array( $this, 'create_gift_card_for_order' ), 10, 4 );
        add_action( 'woocommerce_order_status_changed', array( $this, 'invalidate_gift_card_on_order_status_change' ), 10, 4 );
        add_action( $this->_constants->CREATE_GIFT_CARD_ACTION_SCHEDULE, array( $this, 'create_gift_card_item' ) );
    }
}
