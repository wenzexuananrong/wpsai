<?php
namespace AGCFW\REST_API;

use AGCFW\Abstracts\Abstract_Main_Plugin_Class;
use AGCFW\Helpers\Helper_Functions;
use AGCFW\Helpers\Plugin_Constants;
use AGCFW\Interfaces\Model_Interface;
use AGCFW\Objects\Advanced_Gift_Card;
use AGCFW\Objects\Email;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Model that houses the Advanced Gift Cards REST API logic.
 * Public Model.
 *
 * @since 1.0
 */
class API_Advanced_Gift_Cards implements Model_Interface {
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

    /**
     * Custom REST API base.
     *
     * @since 1.0
     * @access private
     * @var string
     */
    private $_base = 'gift-cards';

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
     * @since 1.0
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

    /*
    |--------------------------------------------------------------------------
    | Register routes
    |--------------------------------------------------------------------------
     */

    /**
     * Register settings API routes.
     *
     * @since 1.0
     * @access public
     */
    public function register_routes() {
        // get list advanced gift cards, create single advanced gift card.
        \register_rest_route(
            $this->_constants->REST_API_NAMESPACE,
            '/' . $this->_base,
            array(
                array(
                    'methods'             => \WP_REST_Server::READABLE,
                    'permission_callback' => array( $this, 'get_admin_permissions_check' ),
                    'callback'            => array( $this, 'get_advanced_gift_cards' ),
                ),
                array(
                    'methods'             => \WP_REST_Server::CREATABLE,
                    'permission_callback' => array( $this, 'get_admin_permissions_check' ),
                    'callback'            => array( $this, 'create_advanced_gift_cards' ),
                ),
            )
        );

        \register_rest_route(
            $this->_constants->REST_API_NAMESPACE,
            '/' . $this->_base . '/sendemail',
            array(
                array(
                    'methods'             => \WP_REST_Server::CREATABLE,
                    'permission_callback' => array( $this, 'get_admin_permissions_check' ),
                    'callback'            => array( $this, 'send_advanced_gift_card_email' ),
                ),
            ),
        );

        // read, update, delete single advanced gift card.
        \register_rest_route(
            $this->_constants->REST_API_NAMESPACE,
            '/' . $this->_base . '/(?P<id>[\w]+)',
            array(
                array(
                    'methods'             => \WP_REST_Server::READABLE,
                    'permission_callback' => array( $this, 'get_admin_permissions_check' ),
                    'callback'            => array( $this, 'get_advanced_gift_card' ),
                ),
                array(
                    'methods'             => \WP_REST_Server::EDITABLE,
                    'permission_callback' => array( $this, 'get_admin_permissions_check' ),
                    'callback'            => array( $this, 'update_advanced_gift_card' ),
                ),
                array(
                    'methods'             => \WP_REST_Server::DELETABLE,
                    'permission_callback' => array( $this, 'get_admin_permissions_check' ),
                    'callback'            => array( $this, 'delete_advanced_gift_card' ),
                ),
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Permissions.
    |--------------------------------------------------------------------------
     */

    /**
     * Checks if a given request has manage woocommerce permission.
     *
     * @since 1.0
     * @access public
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
     */
    public function get_admin_permissions_check( $request ) {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return new \WP_Error(
                'rest_forbidden_context',
                __( 'Sorry, you are not allowed access to this endpoint.', 'advanced-gift-cards-for-woocommerce' ),
                array( 'status' => \rest_authorization_required_code() )
            );
        }

        return apply_filters( 'agcfw_get_store_credits_admin_permissions_check', true, $request );
    }

    /*
    |--------------------------------------------------------------------------
    | REST API callback methods.
    |--------------------------------------------------------------------------
     */

    /**
     * Get a list of advanced gift cards based on the provided query parameters.
     *
     * @since 1.0
     * @access public
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_advanced_gift_cards( $request ) {
        $params = $this->_sanitize_params( $request->get_params() );

        if ( ! isset( $params['date_format'] ) ) {
            $params['date_format'] = $this->_constants->DB_DATE_FORMAT;
        }

        $results = $this->_query_advanced_gift_cards( $params );

        if ( is_wp_error( $results ) ) {
            return $results;
        }

        $response = \rest_ensure_response( $results );
        $total    = $this->_query_advanced_gift_cards( $params, true );

        if ( is_wp_error( $total ) ) {
            return $total;
        }

        $response->header( 'X-TOTAL', $total );

        return apply_filters( 'agcfw_query_advanced_gift_cards', $response );
    }

    /**
     * Create a single advanced gift card.
     *
     * @since 1.0
     * @access public
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function create_advanced_gift_cards( $request ) {
        $params      = $this->_sanitize_params( $request->get_params() );
        $date_format = isset( $params['date_format'] ) ? $params['date_format'] : $this->_constants->DB_DATE_FORMAT;

        $advanced_gift_card = new Advanced_Gift_Card();

        foreach ( $params as $prop => $value ) {
            if ( in_array( $prop, array( 'date_created', 'date_expire' ), true ) ) {
                $advanced_gift_card->set_date_prop( $prop, $value, $date_format );
            } else {
                $advanced_gift_card->set_prop( $prop, $value );
            }
        }

        $check = $advanced_gift_card->save();

        if ( is_wp_error( $check ) ) {
            return $check;
        }

        if ( isset( $params['recipient'] ) && is_array( $params['recipient'] ) ) {
            $advanced_gift_card->save_email_recipient_data( $params['recipient'] );
        }

        return \rest_ensure_response(
            array(
				'message' => __( 'Successfully created advanced gift card.', 'advanced-gift-cards-for-woocommerce' ),
				'data'    => $advanced_gift_card->get_response_for_api( 'edit', $date_format ),
            )
        );
    }

    /**
     * Read a single advanced gift card.
     *
     * @since 1.0
     * @access public
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_advanced_gift_card( $request ) {
        $params             = $this->_sanitize_params( $request->get_params() );
        $advanced_gift_card = $this->_get_advanced_gift_card( $request['id'] );

        if ( is_wp_error( $advanced_gift_card ) ) {
            return $advanced_gift_card;
        }

        $date_format = isset( $params['date_format'] ) ? $params['date_format'] : $this->_constants->DISPLAY_DATE_FORMAT;
        $context     = isset( $params['context'] ) ? $params['context'] : 'view';

        return \rest_ensure_response( $advanced_gift_card->get_response_for_api( $context, $date_format ) );
    }

    /**
     * Update a single advanced gift card.
     *
     * @since 1.0
     * @access public
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function update_advanced_gift_card( $request ) {
        $params             = $this->_sanitize_params( $request->get_params() );
        $advanced_gift_card = $this->_get_advanced_gift_card( $request['id'] );

        if ( is_wp_error( $advanced_gift_card ) ) {
            return $advanced_gift_card;
        }

        $date_format = isset( $params['date_format'] ) ? $params['date_format'] : $this->_constants->DB_DATE_FORMAT;

        foreach ( $params as $prop => $value ) {
            if ( in_array( $prop, array( 'date_created', 'date_expire' ), true ) ) {
                $advanced_gift_card->set_date_prop( $prop, $value, $date_format );
            } else {
                $advanced_gift_card->set_prop( $prop, $value );
            }
        }

        $check = $advanced_gift_card->save();

        if ( is_wp_error( $check ) ) {
            return $check;
        }

        if ( isset( $params['recipient'] ) && is_array( $params['recipient'] ) ) {
            $advanced_gift_card->save_email_recipient_data( $params['recipient'] );
        }

        return \rest_ensure_response(
            array(
				'message' => __( 'Successfully updated advanced gift card.', 'advanced-gift-cards-for-woocommerce' ),
				'data'    => $advanced_gift_card->get_response_for_api( 'edit', $date_format ),
            )
        );
    }

    /**
     * Delete a single advanced gift card.
     *
     * @since 1.0
     * @access public
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function delete_advanced_gift_card( $request ) {
        $params             = $this->_sanitize_params( $request->get_params() );
        $advanced_gift_card = $this->_get_advanced_gift_card( $request['id'] );

        if ( is_wp_error( $advanced_gift_card ) ) {
            return $advanced_gift_card;
        }

        $date_format = isset( $params['date_format'] ) ? $params['date_format'] : $this->_constants->DB_DATE_FORMAT;
        $previous    = $advanced_gift_card->get_response_for_api( 'edit' );
        $check       = $advanced_gift_card->delete();

        if ( is_wp_error( $check ) ) {
            return $check;
        }

        return \rest_ensure_response(
            array(
				'message' => __( 'Successfully deleted advanced gift card.', 'advanced-gift-cards-for-woocommerce' ),
				'data'    => $previous,
            )
        );
    }

    /**
     * Delete a single advanced gift card.
     *
     * @since 1.3.4
     * @access public
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function send_advanced_gift_card_email( $request ) {

        $gift_card      = $this->_get_advanced_gift_card( $request->get_param( 'id' ) );
        $recipient_data = $gift_card->get_recipient_data();

        // Load WC mailer instance as for some reason the action scheduler doesn't load the mailer instance on cron.
        \WC()->mailer();

        if ( $gift_card->get_prop( 'order_item_id' ) ) {
            $order_item = $gift_card->get_order_item();
            $order      = $order_item instanceof \WC_Order_Item_Product ? $order_item->get_order() : null;

            if ( ! $order instanceof \WC_Order ) {
                return new \WP_Error(
                    'agc_send_email_error',
                    __( 'There was an error trying to send the gift card email. Please ensure that all details are valid.', 'advanced-gift-cards-for-woocommerce' ),
                    array( 'status' => 400 )
                );
            }

            // Run the action to trigger sending the email.
            do_action( 'agcfw_after_create_gift_card_for_order', $gift_card, $order_item, $order );

        } else {
            do_action( 'agcfw_after_create_manual_gift_card', $gift_card, $recipient_data );
        }

        $recipient_data = \AGCFW()->Helper_Functions->get_gift_card_recipient_data( $gift_card );

        return \rest_ensure_response(
            array(
                'message' => sprintf(
                    /* Translators: %s: Gift card email recipient */
                    __( 'Successfully sent the advanced gift card email to %s.', 'advanced-gift-cards-for-woocommerce' ),
                    $recipient_data['email'] ?? '',
                ),
                'data'    => $recipient_data,
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Queries
    |--------------------------------------------------------------------------
     */

    /**
     * Query advanced gift cards.
     *
     * @since 1.0
     * @access private
     *
     * @param array $params     Query parameters.
     * @param bool  $total_only Flag if to only return total count.
     * @return array List of advanced gift cards.
     */
    private function _query_advanced_gift_cards( $params, $total_only = false ) {
        global $wpdb;

        $params = wp_parse_args(
            $params,
            array(
				'page'       => 1,
				'per_page'   => 10,
				'sort_by'    => 'date_created',
				'sort_order' => 'desc',
				'user_id'    => 0,
				'search'     => '',
                'status'     => '',
            )
        );

        $select_query = $total_only ? 'COUNT(c.id)' : 'c.*';
        $user_query   = $params['user_id'] ? $wpdb->prepare( 'AND c.user_id = %d', $params['user_id'] ) : '';
        $search_query = '';
        $status_query = '';
        $joins        = '';

        if ( $params['search'] ) {
            $search       = '%' . esc_sql( $params['search'] ) . '%';
            $search_query = "AND (c.id LIKE '{$search}' OR c.code LIKE '{$search}' OR c.order_item_id LIKE '{$search}' OR c.user_id LIKE '{$search}' OR oi.order_item_name LIKE '{$search}') OR oim1.meta_value LIKE '{$search}' OR oim2.meta_value LIKE '{$search}'";
            $joins       .= "LEFT JOIN {$wpdb->prefix}woocommerce_order_items as oi ON (oi.order_item_id = c.order_item_id)
                LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as oim1 ON (oim1.order_item_id = oi.order_item_id AND oim1.meta_key = 'agcfw_recipient_name')
                LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as oim2 ON (oim2.order_item_id = oi.order_item_id AND oim2.meta_key = 'agcfw_recipient_email')
            ";
        }

        if ( $params['status'] ) {
            $status_query = $wpdb->prepare( 'AND c.status = %s', $params['status'] );
        }

        $query = "SELECT DISTINCT {$select_query} FROM {$wpdb->prefix}acfw_gift_cards AS c
            {$joins}
            WHERE 1 {$search_query} {$status_query}
            {$user_query}
        ";

        if ( $total_only ) {
            $results = $wpdb->get_var( $query ); // phpcs:ignore

            if ( \is_null( $results ) ) {
                return new \WP_Error(
                    'agcfw_query_advanced_gift_cards_fail',
                    __( 'There was an error fetching the total count of advanced gift cards.', 'advanced-gift-cards-for-woocommerce' ),
                    array(
                        'status' => 400,
                        'data'   => $params,
                    )
                );
            }

            return (int) $results;
        }

        $offset       = ( $params['page'] - 1 ) * $params['per_page'];
        $sort_columns = array(
            'date_created'  => 'c.date_created',
            'date_expire'   => 'c.date_expire',
            'id'            => 'c.id',
            'order_item_id' => 'c.order_item_id',
            'code'          => 'c.code',
            'status'        => 'c.status',
            'user_id'       => 'c.user_id',
        );

        // sort query.
        $sort_column = isset( $sort_columns[ $params['sort_by'] ] ) ? $sort_columns[ $params['sort_by'] ] : 'c.date_created';
        $sort_type   = 'asc' === $params['sort_order'] ? 'ASC' : 'DESC';
        $sort_query  = "ORDER BY {$sort_column} {$sort_type}";

        // limit query.
        $limit_query = 1 <= $params['page'] ? $wpdb->prepare( 'LIMIT %d OFFSET %d', $params['per_page'], $offset ) : '';

        // run the query.
        // phpcs:disable
        $results = $wpdb->get_results(
            "{$query}{$sort_query}
            {$limit_query}
            ",
            ARRAY_A
        );
        // phpcs:enable

        if ( \is_null( $results ) ) {
            return new \WP_Error(
                'agcfw_query_advanced_gift_cards_fail',
                __( 'There was an error fetching the advanced gift cards.', 'advanced-gift-cards-for-woocommerce' ),
                array(
                    'status' => 400,
                    'data'   => $params,
                )
            );
        }

        return array_map(
            function ( $r ) use ( $params ) {
                $advanced_gift_card = new Advanced_Gift_Card( $r );
                return $advanced_gift_card->get_response_for_api( 'view', $params['date_format'] );
            },
            $results
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Utility functions
    |--------------------------------------------------------------------------
     */

    /**
     * Get advanced gift card.
     *
     * @since 1.0
     * @access private
     *
     * @param int $id Entry ID.
     * @return Advanced_Gift_Card|WP_Error
     */
    private function _get_advanced_gift_card( $id ) {
        if ( empty( $id ) || is_null( $id ) ) {
            return new \WP_Error(
                'missing_params',
                __( 'Required parameters are missing', 'advanced-gift-cards-for-woocommerce' ),
                array(
					'status' => 400,
					'data'   => $id,
                )
            );
        }

        $advanced_gift_card = new Advanced_Gift_Card( absint( $id ) );

        if ( ! $advanced_gift_card->get_id() ) {
            return new \WP_Error(
                'missing_params',
                __( 'The provided ID is not a valid.', 'advanced-gift-cards-for-woocommerce' ),
                array(
					'status' => 400,
					'data'   => $id,
                )
            );
        }

        return $advanced_gift_card;
    }

    /**
     * Sanitize query parameters.
     *
     * @since 1.0
     * @access private
     *
     * @param array $params Query parameters.
     * @return array Sanitized parameters.
     */
    private function _sanitize_params( $params ) {
        if ( ! is_array( $params ) || empty( $params ) ) {
            return array();
        }

        $sanitized = array();
        foreach ( $params as $param => $value ) {
            switch ( $param ) {
                case 'id':
                case 'user_id':
                case 'order_item_id':
                    $sanitized[ $param ] = absint( $value );
                    break;

                case 'value':
                    $sanitized[ $param ] = \ACFWF()->Helper_Functions->sanitize_price( $value );
                    break;

                case 'recipient':
                    if ( is_array( $value ) && $value ) {
                        $sanitized[ $param ] = array(
                            'send_to'       => 'friend' === $value['send_to'] ? 'friend' : 'me',
                            'name'          => sanitize_text_field( $value['name'] ),
                            'email'         => sanitize_email( $value['email'] ),
                            'short_message' => sanitize_text_field( $value['short_message'] ),
                        );
                    }
                    break;

                default:
                    $sanitized[ $param ] = sanitize_text_field( $value );
            }
        }

        return $sanitized;
    }

    /*
    |--------------------------------------------------------------------------
    | Fulfill implemented interface contracts
    |--------------------------------------------------------------------------
     */

    /**
     * Execute Settings class.
     *
     * @since 1.0
     * @access public
     * @inherit AGCFW\Interfaces\Model_Interface
     */
    public function run() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }
}
