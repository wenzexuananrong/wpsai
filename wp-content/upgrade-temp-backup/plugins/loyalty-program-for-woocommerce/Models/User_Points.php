<?php
namespace LPFW\Models;

use ACFWF\Models\Objects\Store_Credit_Entry;
use LPFW\Abstracts\Abstract_Main_Plugin_Class;
use LPFW\Abstracts\Base_Model;
use LPFW\Helpers\Helper_Functions;
use LPFW\Helpers\Plugin_Constants;
use LPFW\Interfaces\Initiable_Interface;
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
class User_Points extends Base_Model implements Model_Interface, Initiable_Interface {
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
    | Redeem methods
    |--------------------------------------------------------------------------
     */

    /**
     * Redeem points for user by converting points to a coupon only usable by the user.
     *
     * @since 1.0
     * @since 1.3 add checkout flag and calculate max points.
     * @since 1.8 Redeem points as store credits and apply store credits directly as payment on checkout page.
     * @access public
     *
     * @param int  $points  Points to redeem.
     * @param int  $user_id User ID.
     * @param bool $is_checkout Flag if redemption is done on checkout.
     * @return Store_Credit_Entry|WP_Error
     */
    public function redeem_points_for_user( $points, $user_id, $is_checkout = false ) {
        $user_points = \LPFW()->Calculate->get_user_total_points( $user_id );
        $min_points  = (int) $this->_helper_functions->get_option( $this->_constants->MINIMUM_POINTS_REDEEM, '0' );
        $max_points  = LPFW()->Calculate->calculate_allowed_max_points( $user_points, $is_checkout );

        if ( ! $points || $points < $min_points || $points > $max_points ) {
            return new \WP_Error(
                'lpfw_invalid_points',
                __( 'Redemption failed. Please make sure that you have sufficient points or that the points redeemed is above the set minimum.', 'loyalty-program-for-woocommerce' ),
                array( 'points' => $points )
            );
        }

        $store_credit_entry = $this->_create_redeem_store_credit_entry( $points, $user_id );

        // update the object ID relationship so it points to the loyalty entry ID.
        if ( $store_credit_entry instanceof Store_Credit_Entry ) {
            $loyalty_entry_id = \LPFW()->Entries->decrease_points( $user_id, $points, 'store_credits', $store_credit_entry->get_id() );

            if ( is_wp_error( $loyalty_entry_id ) ) {
                return $loyalty_entry_id;
            }

            $store_credit_entry->set_prop( 'object_id', absint( $loyalty_entry_id ) );
            $update_check = $store_credit_entry->save();

            if ( is_wp_error( $update_check ) ) {
                return $update_check;
            }

            // Apply the store credits amount directly as payment in checkout.
            $sc_check = $is_checkout ? \ACFWF()->Store_Credits_Checkout->redeem_store_credits( $user_id, $store_credit_entry->get_prop( 'amount' ) ) : null;

            if ( is_wp_error( $sc_check ) ) {
                return $sc_check;
            }
        }

        return $store_credit_entry;
    }

    /**
     * Create loyalty points redemption store credit entry.
     *
     * @since 1.8
     * @access private
     *
     * @param int $points  Points amount.
     * @param int $user_id User ID.
     * @return Store_Credit_Entry|WP_Error Store credit entry object on success, WP error object on failure.
     */
    private function _create_redeem_store_credit_entry( $points, $user_id ) {
        $store_credit_entry = new Store_Credit_Entry();
        $amount             = \LPFW()->Calculate->calculate_redeem_points_worth( $points, false );

        $store_credit_entry->set_prop( 'amount', (float) $amount );
        $store_credit_entry->set_prop( 'user_id', $user_id );
        $store_credit_entry->set_prop( 'type', 'increase' );
        $store_credit_entry->set_prop( 'action', 'loyalty_points' );

        $check = $store_credit_entry->save();

        return ! is_wp_error( $check ) ? $store_credit_entry : $check;
    }

    /**
     * Apply redeemed coupon to cart.
     *
     * @since 1.0
     * @access public
     */
    public function apply_redeemed_coupon_to_cart() {
        if ( ( ! is_cart() && ! is_checkout() ) || ! isset( $_GET['lpfw_coupon'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            return;
        }

        $coupon = sanitize_text_field( wp_unslash( $_GET['lpfw_coupon'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        // Initialize cart session.
        WC()->session->set_customer_session_cookie( true );

        // Apply coupon to cart.
        WC()->cart->apply_coupon( $coupon );

        wp_safe_redirect( wc_get_cart_url() );
        exit();
    }

    /**
     * Assign already used loyalty coupon to the set default used category.
     *
     * @since 1.4
     * @access public
     *
     * @param int      $order_id    Order ID.
     * @param array    $posted_data Array of data.
     * @param WC_Order $order       Order object.
     */
    public function assign_used_coupon_to_category_after_checkout( $order_id, $posted_data, $order ) {
        $used_category = (int) get_option( $this->_constants->DEFAULT_USED_COUPON_CAT );

        // skip if term doesn't exist.
        if ( ! $used_category || ! term_exists( $used_category, $this->_constants->COUPON_CAT_TAXONOMY ) ) {
            return;
        }

        foreach ( $order->get_items( 'coupon' ) as $coupon_item ) {
            $coupon = new \WC_Coupon( $coupon_item->get_code() );
            if ( $coupon->get_meta( $this->_constants->META_PREFIX . 'loyalty_program_user' ) ) {
                wp_set_post_terms( $coupon->get_id(), $used_category, $this->_constants->COUPON_CAT_TAXONOMY, true );
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | User related methods
    |--------------------------------------------------------------------------
     */

    /**
     * Get user redeemed coupons
     *
     * @since 1.0
     * @since 1.3 Add coupons per page parameter.
     * @access public
     *
     * @global wpdb $wpdb Object that contains a set of functions used to interact with a database.
     *
     * @param int $user_id          User ID.
     * @param int $page             Page number.
     * @param int $coupons_per_page Number of coupons per page.
     * @return array User redeemed coupons.
     */
    public function get_user_redeemed_coupons( $user_id, $page = 1, $coupons_per_page = 10 ) {
        global $wpdb;

        $timezone = new \DateTimeZone( $this->_helper_functions->get_site_current_timezone() );
        $datetime = new \DateTime( 'today', $timezone );
        $today    = $datetime->format( 'U' );

        $user_id          = absint( esc_sql( $user_id ) );
        $coupons_per_page = intval( $coupons_per_page ); // make sure value is integer.
        $offset           = $page ? ( $page - 1 ) * $coupons_per_page : 0;

        $data = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT object_id AS ID, posts.post_title AS code, amount.meta_value AS amount,
                posts.post_date_gmt AS date, entry_amount AS points, coupon_expire.meta_value AS date_expire
                FROM {$wpdb->acfw_loyalprog_entries}
                INNER JOIN {$wpdb->posts} AS posts ON ( posts.ID = object_id )
                INNER JOIN {$wpdb->postmeta} AS amount ON ( amount.post_id = object_id AND amount.meta_key = 'coupon_amount' )
                INNER JOIN {$wpdb->postmeta} AS usage_count ON ( usage_count.post_id = object_id AND usage_count.meta_key = 'usage_count' )
                INNER JOIN {$wpdb->postmeta} AS coupon_expire ON ( coupon_expire.post_id = object_id AND coupon_expire.meta_key = 'date_expires' )
                WHERE user_id = %d
                    AND entry_type = 'redeem'
                    AND posts.post_status = 'publish'
                    AND posts.post_type = 'shop_coupon'
                    AND usage_count.meta_value = 0
                    AND ( coupon_expire.meta_value = '' OR coupon_expire.meta_value IS NULL OR coupon_expire.meta_value > %d )
                    GROUP BY object_id
                    ORDER BY posts.post_date DESC
                    LIMIT %d, %d
                ",
                $user_id,
                $today,
                $offset,
                $coupons_per_page
            )
        );

        return $data;
    }

    /**
     * Get user total number of redemeed coupons.
     *
     * @since 1.0
     * @access public
     *
     * @param int $user_id User ID.
     * @return in Number of coupons.
     */
    public function get_user_redeem_coupons_total( $user_id ) {
        global $wpdb;

        $timezone = new \DateTimeZone( $this->_helper_functions->get_site_current_timezone() );
        $datetime = new \DateTime( 'today', $timezone );
        $today    = $datetime->format( 'U' );
        $user_id  = absint( esc_sql( $user_id ) );

        $results = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT entry_id FROM {$wpdb->acfw_loyalprog_entries}
                INNER JOIN {$wpdb->posts} AS posts ON ( posts.ID = object_id )
                INNER JOIN {$wpdb->postmeta} AS amount ON ( amount.post_id = object_id AND amount.meta_key = 'coupon_amount' )
                INNER JOIN {$wpdb->postmeta} AS usage_count ON ( usage_count.post_id = object_id AND usage_count.meta_key = 'usage_count' )
                INNER JOIN {$wpdb->postmeta} AS coupon_expire ON ( coupon_expire.post_id = object_id AND coupon_expire.meta_key = 'date_expires' )
                WHERE user_id = %d
                    AND entry_type = 'redeem'
                    AND posts.post_status = 'publish'
                    AND posts.post_type = 'shop_coupon'
                    AND usage_count.meta_value = 0
                    AND ( coupon_expire.meta_value = '' OR coupon_expire.meta_value IS NULL OR coupon_expire.meta_value > %d )
                    GROUP BY object_id
                    ORDER BY posts.post_date DESC
                ",
                $user_id,
                $today
            )
        );

        return count( $results );
    }

    /*
    |--------------------------------------------------------------------------
    | Order revoke points
    |--------------------------------------------------------------------------
     */

    /**
     * Revoke the points earned by a customer from an order when the order status is changed from 'completed'.
     *
     * @since 1.2
     * @access public
     *
     * @param int       $order_id    Order ID.
     * @param string    $prev_status Previous order status.
     * @param string    $new_status  New order status.
     * @param \WC_Order $order       Order object.
     */
    public function revoke_user_points_earned_from_order( $order_id, $prev_status, $new_status, $order ) {
        // skip if points already revoked, previous status was not 'completed' or when new status is not for revoke.
        if (
            $order->get_meta( $this->_constants->ORDER_POINTS_REVOKE_ENTRY_ID_META, true )
            || ! in_array( $prev_status, $this->_constants->get_allowed_earn_points_order_statuses(), true )
            || ! \LPFW()->Entries->is_order_new_status_for_revoke( $new_status )
        ) {
            return;
        }

        \LPFW()->Entries->revoke_points_from_order( $order );
    }

    /*
    |--------------------------------------------------------------------------
    | Checkout redeem form
    --------------------------------------------------------------------------
     */

    /**
     * Display checkout redeem form.
     *
     * @deprecated 1.8.4
     *
     * @since 1.3
     * @access public
     */
    public function display_checkout_redeem_form() {
        wc_deprecated_function( __METHOD__, '1.8.4' );
    }

    /**
     * Register redeem loyalty points form field in checkout page.
     *
     * @since 1.8.4
     * @access public
     *
     * @param string $field Field HTML.
     * @param string $key Field key.
     * @param array  $args Field args.
     * @param string $value Field value.
     * @return string Filtered field HTML.
     */
    public function register_redeem_loyalty_points_form_field( $field, $key, $args, $value ) {
        $args = wp_parse_args(
            $args,
            array(
				'min_points'  => 0,
				'max_points'  => 0,
				'placeholder' => '',
                'button_text' => __( 'Apply', 'loyalty-program-for-woocommerce' ),
            )
        );

        $class_prefix = apply_filters( 'lpfw_redeem_form_field_class_prefix', 'acfw' );

        ob_start();
        include $this->_constants->VIEWS_ROOT_PATH . 'fields/redeem-form-field.php';

        return ob_get_clean();
    }

    /**
     * Register the loyalty program checkout accordion.
     *
     * @since 1.8.4
     * @access public
     *
     * @param array $accordions Accordions data.
     * @return array Accordions data.
     */
    public function register_loyalty_program_checkout_accordion( $accordions ) {
        if ( 'yes' === get_option( $this->_constants->DISPLAY_CHECKOUT_POINTS_REDEEM_FORM, 'yes' ) && $this->is_show_checkout_redeem_form() ) {
            $labels       = $this->get_loyalty_points_redeem_form_labels();
            $accordions[] = array(
                'key'       => 'loyalty_program',
                'label'     => __( 'Loyalty points redeem form', 'loyalty-program-for-woocommerce' ),
                'title'     => $labels['toggle_text'],
                'classname' => 'acfw-loyalty-program-checkout-ui',
            );
        }

        return $accordions;
    }

    /**
     * Display the loyalty program form in the checkout accordion.
     *
     * @since 1.8.4
     * @access public
     *
     * @param array $data Accordion data.
     */
    public function display_loyalty_program_form_checkout_accordion( $data ) {
        if ( ! isset( $data['key'] ) || 'loyalty_program' !== $data['key'] ) {
            return;
        }

        $user_points  = LPFW()->Calculate->get_user_total_points( get_current_user_id() );
        $min_points   = (int) $this->_helper_functions->get_option( $this->_constants->MINIMUM_POINTS_REDEEM, '0' );
        $points_name  = $this->_helper_functions->get_points_name();
        $points_worth = $this->_helper_functions->api_wc_price( LPFW()->Calculate->calculate_redeem_points_worth( $user_points ) );
        $max_points   = LPFW()->Calculate->calculate_allowed_max_points( $user_points, true );

        $this->_helper_functions->load_template(
            'checkout/lpfw-accordion.php',
            array(
                'user_points'  => $user_points,
                'min_points'   => $min_points,
                'points_name'  => $points_name,
                'points_worth' => $points_worth,
                'max_points'   => $max_points,
                'labels'       => $this->get_loyalty_points_redeem_form_labels(),
            )
        );
    }

    /**
     * Get the loyalty points redeem form labels.
     *
     * @since 1.8.4
     * @access public
     *
     * @return array Loyalty points redeem form labels.
     */
    public function get_loyalty_points_redeem_form_labels() {
        return apply_filters(
            'lpfw_loyalty_points_reedem_form_labels',
            array(
                'toggle_text'  => __( 'Apply loyalty discounts?', 'loyalty-program-for-woocommerce' ),
                /* Translators: %s: points name */
                'placeholder'  => __( 'Enter %s', 'loyalty-program-for-woocommerce' ),
                /* Translators: %1$s: user points balance, %2$s: points name, %3$s: Point's worth in currency */
                'balance_text' => __( 'You have %1$s %2$s (worth %3$s)', 'loyalty-program-for-woocommerce' ),
                /* Translators: %1$s: Maximum allowed points value to redeem, %2$s: points name */
                'instructions' => __( 'You may redeem up to %1$s %2$s to get a discount for your order.', 'loyalty-program-for-woocommerce' ),
            )
        );
    }

    /**
     * Update the user points displayed value everytime the checkout order review is reloaded.
     *
     * @since 1.8.4
     * @access public
     *
     * @param array $fragments Order review fragments.
     * @return array Filtered order review fragments.
     */
    public function update_user_points_on_checkout_refresh( $fragments ) {
        $selector     = '.acfw-loyalty-program-checkout-ui .lpfw-user-points-summary';
        $user_points  = LPFW()->Calculate->get_user_total_points( get_current_user_id() );
        $points_name  = $this->_helper_functions->get_points_name();
        $points_worth = $this->_helper_functions->api_wc_price( LPFW()->Calculate->calculate_redeem_points_worth( $user_points ) );

        $content = wp_kses_post(
            sprintf(
                /* Translators: %1$s: user points balance, %2$s: points name, %3$s: Point's worth in currency */
                __( 'You have %1$s %2$s (worth %3$s)', 'loyalty-program-for-woocommerce' ),
                '<strong>' . $user_points . '</strong>',
                '<span class="points-name">' . strtolower( $points_name ) . '</span>',
                '<strong>' . $points_worth . '</strong>'
            )
        );

        $fragments[ $selector ] = sprintf( '<p class="lpfw-user-points-summary">%s</p>', $content );

        return $fragments;
    }

    /**
     * Check if we should show the checkout redeem form.
     * We only show the redeem form when user is logged in and not restricted, and no loyalty coupons have been applied yet.
     *
     * @since 1.3
     * @access public
     *
     * @return bool True if show, false otherwise.
     */
    public function is_show_checkout_redeem_form() {
        // return false if user not logged in or user's role is restricted.
        if ( ! is_user_logged_in() || ! $this->_helper_functions->validate_user_roles( get_current_user_id() ) ) {
            return false;
        }

        $applied_coupons = WC()->cart->get_coupons();

        // return false if a loyalty coupon is already applied on checkout.
        foreach ( $applied_coupons as $coupon ) {
            $meta_value = (int) $coupon->get_meta( $this->_constants->COUPON_USER );
            if ( get_current_user_id() === $meta_value ) {
                return false;
            }
        }

        $user_points = LPFW()->Calculate->get_user_total_points( get_current_user_id() );
        $min_points  = (int) $this->_helper_functions->get_option( $this->_constants->MINIMUM_POINTS_REDEEM, '0' );

        return $this->_is_user_points_valid_for_redeem( $user_points, $min_points ) && apply_filters( 'lpfw_checkout_show_redeem_form', true );
    }

    /**
     * Check if user's points are valid for redeeming.
     *
     * @since 1.6
     * @access private
     *
     * @param int $user_points User points.
     * @param int $min_points  Minimum points allowed for redeem.
     * @return bool True if allowed, false otherwise.
     */
    private function _is_user_points_valid_for_redeem( $user_points, $min_points = false ) {
        if ( false === $min_points ) {
            $min_points = (int) $this->_helper_functions->get_option( $this->_constants->MINIMUM_POINTS_REDEEM, '0' );
        }

        // if hide checkout form setting is turned off, then we should always show the form regardless of user's points.
        if ( 'yes' !== get_option( $this->_constants->HIDE_CHECKOUT_FORM_NOT_ENOUGH_POINTS, 'yes' ) ) {
            return true;
        }

        return $user_points > 0 && $user_points >= $min_points;
    }

    /*
    |--------------------------------------------------------------------------
    | Coupon Frontend Implementation
    |--------------------------------------------------------------------------
     */

    /**
     * Validate coupon to make sure only the redeemer can apply it.
     *
     * @since 1.1.3
     * @access public
     *
     * @throws \Exception Coupon error message.
     * @param bool       $value Filter return value.
     * @param \WC_Coupon $coupon WC_Coupon object.
     * @return bool True if valid, false otherwise.
     */
    public function validate_coupon_user( $value, $coupon ) {
        $current_user = wp_get_current_user();
        $coupon_user  = absint( $coupon->get_meta( $this->_constants->META_PREFIX . 'loyalty_program_user', true ) );

        if ( $coupon_user && $coupon_user !== $current_user->ID ) {
            throw new \Exception( esc_html( $coupon->get_coupon_error( \WC_Coupon::E_WC_COUPON_INVALID_FILTERED ) ) );
        }

        return $value;
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX Functions
    |--------------------------------------------------------------------------
     */

    /**
     * AJAX Redeem points for user.
     *
     * @since 1.0
     * @since 1.8   Redeem points as store credits.
     * @since 1.8.1 Add support for the new redeem points form block.
     * @access public
     */
    public function ajax_redeem_points_for_user() {
        $nonce       = isset( $_POST['wpnonce'] ) ? sanitize_key( wp_unslash( $_POST['wpnonce'] ) ) : '';
        $is_checkout = isset( $_POST['is_checkout'] ) ? (bool) intval( $_POST['is_checkout'] ) : false;
        $is_block    = isset( $_POST['is_block'] ) ? (bool) intval( $_POST['is_block'] ) : false;

        if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
            $response = array(
                'status'  => 'error',
                'message' => __( 'Invalid AJAX call', 'loyalty-program-for-woocommerce' ),
            );
        } elseif (
            ! isset( $_POST['wpnonce'] ) ||
            ! wp_verify_nonce( $nonce, 'lpfw_redeem_points_for_user' )
        ) {
            $response = array(
                'status'  => 'error',
                'message' => __( 'You are not allowed to do this', 'loyalty-program-for-woocommerce' ),
            );
        } else {
            $points             = isset( $_POST['redeem_points'] ) ? intval( $_POST['redeem_points'] ) : 0;
            $user               = wp_get_current_user();
            $store_credit_entry = 0 < $points ? $this->redeem_points_for_user( $points, $user->ID, $is_checkout ) : null;

            if ( $store_credit_entry instanceof Store_Credit_Entry ) {

                $user_points = (int) \LPFW()->Calculate->get_user_total_points( $user->ID );
                $response    = array(
                    'status'  => 'success',
                    'message' => __( 'Points successfully redeemed.', 'loyalty-program-for-woocommerce' ),
                );

            } elseif ( is_wp_error( $store_credit_entry ) ) {
                $response = array(
                    'status'  => 'error',
                    'message' => $store_credit_entry->get_error_message(),
                );
            } else {
                $response = array(
                    'status'  => 'error',
                    'message' => __( 'Redemption failed. Please make sure that you have sufficient points or that the points redeemed is above the set minimum.', 'loyalty-program-for-woocommerce' ),
                );
            }
        }

        // Store notice.
        $store_notices = array();

        // display success or error notice for redeem points form block.
        if ( $is_block ) {
            $store_notices[] = array(
                'type'    => $response['status'],
                'message' => $response['message'],
            );
        }

        // display error as a notice on checkout.
        if ( $is_checkout && 'error' === $response['status'] ) {
            $store_notices[] = array(
                'type'    => 'error',
                'message' => $response['message'],
            );
        }

        /**
         * Display store notice message, only if it exists and on regular cart and checkout page only.
         * - We validate is_cart_checkout_block via POST due to `global $post;` is not available in AJAX environment.
         */
        $is_cart_checkout_block = isset( $_POST['is_cart_checkout_block'] ) ? sanitize_text_field( wp_unslash( $_POST['is_cart_checkout_block'] ) ) : false;
        if ( ! empty( $store_notices ) && ! $is_cart_checkout_block ) {
            foreach ( $store_notices as $notice ) {
                wc_add_notice( $notice['message'], $notice['type'] );
            }
        }

        // return response.
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
     * @implements LPFW\Interfaces\Initializable_Interface
     */
    public function initialize() {
        add_action( 'wp_ajax_lpfw_redeem_points_for_user', array( $this, 'ajax_redeem_points_for_user' ) );
    }

    /**
     * Execute User_Points class.
     *
     * @since 1.0
     * @access public
     * @inherit LPFW\Interfaces\Model_Interface
     */
    public function run() {
        add_action( 'template_redirect', array( $this, 'apply_redeemed_coupon_to_cart' ) );
        add_action( 'woocommerce_order_status_changed', array( $this, 'revoke_user_points_earned_from_order' ), 10, 4 );
        add_action( 'woocommerce_coupon_is_valid', array( $this, 'validate_coupon_user' ), 10, 2 );
        add_action( 'woocommerce_checkout_order_processed', array( $this, 'assign_used_coupon_to_category_after_checkout' ), 10, 3 );
        add_filter( 'woocommerce_form_field_lpfw_redeem_loyalty_points', array( $this, 'register_redeem_loyalty_points_form_field' ), 10, 4 );
        add_filter( 'acfw_checkout_accordions_data', array( $this, 'register_loyalty_program_checkout_accordion' ) );
        add_action( 'acfw_checkout_accordion_content', array( $this, 'display_loyalty_program_form_checkout_accordion' ) );
        add_filter( 'woocommerce_update_order_review_fragments', array( $this, 'update_user_points_on_checkout_refresh' ) );
    }
}
