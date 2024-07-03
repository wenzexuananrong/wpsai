<?php

namespace LPFW\Models\Emails;

use LPFW\Abstracts\Abstract_Main_Plugin_Class;
use LPFW\Abstracts\Email_Model;
use LPFW\Helpers\Helper_Functions;
use LPFW\Helpers\Plugin_Constants;
use LPFW\Interfaces\Model_Interface;
use LPFW\Models\REST_API\API_Email_Loyalty_Point_Reminder;
use LPFW\Objects\Customer;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class that houses the hooks of the loyalty point reminder email.
 *
 * @since 1.8.4
 */
class Email_Loyalty_Point_Reminder extends Email_Model implements Model_Interface {
    /**
     * Property that holds email id.
     *
     * @since 1.8.4
     * @access public
     * @var string
     */
    public static $id = 'lpfw_loyalty_point_reminder';

    /**
     * Property that holds email constants for storing in database.
     *
     * @since 1.8.4
     * @access public
     * @var array
     */
    public static $constants = array(
        'schedule'                => 'lpfw_loyalty_point_reminder_schedule',
        'schedule_waiting_period' => 'lpfw_loyalty_point_reminder_schedule_waiting_period',
    );

    /**
     * Property that holds email time schedule.
     *
     * @since 1.8.4
     * @access public
     * @var string
     */
    public static $time_schedule = '10:00:00';

    /**
     * Property that holds email arguments for preview and sending.
     *
     * @since 1.8.4
     * @access public
     * @var array $args
     */
    public $args = array();

    /**
     * Property that holds action schedule group configuration.
     *
     * @since 1.8.4
     * @access public
     * @var string $group
     */
    public $group = 'LPFW';

    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
     */

    /**
     * Class constructor.
     *
     * @since 1.8.4
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

    /**
     * Register woocommerce email instances.
     *
     * @since 1.8.4
     * @access public
     *
     * @param array $emails List of email objects.
     * @return array Filtered list of email objects.
     */
    public function register_woocommerce_email_classes( $emails ) {
        $emails[ $this::$id ] = new WC_Email_Loyalty_Point_Reminder( $this::$id );

        return $emails;
    }

    /**
     * Trigger email loyalty point reminder (Action Scheduler)
     *
     * @since 1.8.4
     * @access public
     *
     * @param array $user_id User ID.
     */
    public function trigger( $user_id ) {
        // Check if email is valid before sending it.
        $is_valid = $this->is_valid( (int) $user_id );
        if ( is_wp_error( $is_valid ) ) {
            return;
        }

        $customer = new \WC_Customer( $user_id );
        \WC()->mailer(); // Load WC mailer instance as for some reason the action scheduler doesn't load the mailer instance on cron.
        do_action( $this::$id . '_send', $customer );
    }

    /**
     * Get email preview content.
     *
     * @since 1.8.4
     * @access public
     */
    public function preview() {
        // Get arguments.
        $args = array(
            'email'   => '',
            'user_id' => 0,
            'name'    => '',
        );
        $args = wp_parse_args( $this->args, $args );

        // Get customers.
        $customer = isset( $args['user_id'] ) ? $args['user_id'] : $args['email']; // Get user_id or email.
        $customer = \ACFWF()->Helper_Functions->get_customer_object( $customer );

        // Display an error page when customer is not valid.
        if ( is_wp_error( $customer ) ) {
            wp_die( $customer ); // phpcs:ignore
        }

        // Override customer name if present in the arguments.
        if ( ! $args['user_id'] && $args['name'] ) {
            $customer->set_display_name( $args['name'] );
            $customer->apply_changes();
        }

        // Return email content.
        \WC()->mailer(); // This is required to load \WC_Email class.
        $email = new WC_Email_Loyalty_Point_Reminder( $this::$id );
        $email->set_customer( $customer );

        return $email->style_inline( $email->get_content_html() );
    }

    /**
     * Send the email hook, for filter purposes.
     *
     * @since 1.8.4
     * @access public
     *
     * @param \WC_Customer $customer Customer object.
     */
    public function send( $customer ) {
        // Hook: Before send email.
        do_action( $this::$id . '_before_send_email', $customer );

        // Get email instance.
        $email = \WC()->mailer()->emails[ $this::$id ];
        $email->setup_locale();
        $email->set_customer( $customer );
        $email->recipient = $customer->get_email();

        // Send email only if it is enabled and recipient is valid.
        if ( $email->is_enabled() && $email->get_recipient() ) {
            $subject = $email->get_subject() ? $email->get_subject() : $email->get_default_text( 'subject' );
            $subject = $email->format_string( $subject );
            $email->send(
                $email->get_recipient(),
                $subject,
                $email->get_content(),
                $email->get_headers(),
                $email->get_attachments()
            );
        }

        $email->restore_locale();

        // Hook: After send email.
        do_action( $this::$id . '_after_send_email', $customer );
    }

    /**
     * Check if email is scheduled.
     *
     * @since 1.8.4
     * @access public
     *
     * @return \WC_DateTime|null
     */
    public function is_scheduled() {
        // Grab arguments.
        $args = $this->args;

        // Return if customer id is not valid.
        if ( ! isset( $args['user_id'] ) || ! $args['user_id'] ) {
            return null;
        }

        // Construct email data.
        $user = get_userdata( $args['user_id'] );
        $args = array(
            'user_id'    => intval( $args['user_id'] ),
            'user_email' => $user->user_email ?? '',
        );

        // Check if schedule exists.
        return \WC()->queue()->get_next( $this::$id . '_trigger', $args, $this->group );
    }

    /**
     * Reschedule WooCommerce Action Schedule for this Email.
     * - This is useful to avoid spamming customer.
     *
     * - This also used to hook into loyalty point entry,
     * so everytime customer get a new loyalty point,
     * the email will be added or rescheduled if exists.
     *
     * @since 1.8.4
     * @access public
     *
     * @param array $args List of arguments.
     * - This is necessary because this parameter is used to hook into loyalty point entry.
     */
    public function reschedule( $args = array() ) {
        // Grab arguments.
        $args      = array_merge( $this->args, $args );
        $constants = $this::$constants;

        // Return if customer id is not valid.
        if ( ! isset( $args['user_id'] ) || ! $args['user_id'] ) {
            return;
        }

        // Reschedule only if email is enabled.
        $email = \WC()->mailer()->emails[ $this::$id ];
        if ( ! $email->is_enabled() ) {
            return;
        }

        // Construct email data.
        $hook = $this::$id . '_trigger';
        $user = get_userdata( $args['user_id'] );
        $args = array(
            'user_id'    => intval( $args['user_id'] ),
            'user_email' => $user->user_email ?? '',
        );

        // Dequeue if schedule exists, this is useful to avoid spamming customer.
        $this->args   = $args;
        $is_scheduled = $this->is_scheduled();
        if ( $is_scheduled ) {
            \WC()->queue()->cancel_all( $hook, $args, $this->group );
        }

        // Get time to schedule.
        $timeschedule                   = get_option( $constants['schedule'], $this::$time_schedule );
        $timeschedule                   = explode( ':', $timeschedule );
        $timeschedule                   = array_map( 'intval', $timeschedule );
        $timeschedule                   = array_pad( $timeschedule, 3, 0 );
        list( $hour, $minute, $second ) = $timeschedule;

        // Register new action schedule.
        $waiting_preiod = get_option( $constants['schedule_waiting_period'], 30 ); // Get waiting period option, the default option is 30 days.
        $waiting_preiod = apply_filters( $constants['schedule_waiting_period'], $waiting_preiod ); // Allow to filter waiting period.
        $current_time   = new \WC_DateTime( 'now', new \DateTimeZone( $this->_helper_functions->get_site_current_timezone() ) );
        $current_time->modify( '+' . $waiting_preiod . ' days' ); // Add waiting period to the current time.
        $current_time->setTime( $hour, $minute, $second ); // Set the time to 00:00:00.
        $current_time->setTimezone( new \DateTimeZone( 'UTC' ) ); // Set the timezone to UTC.
        \WC()->queue()->schedule_single( $current_time->getTimestamp(), $hook, $args, $this->group );
    }

    /**
     * Get Preview URL.
     *
     * @since 1.8.4
     * @access public
     *
     * @param int $user_id User ID.
     */
    public function get_preview_url( $user_id ) {
        // Prepare email preview URL parameters.
        $parameters             = array();
        $parameters['action']   = $this::$id . '_preview_email';
        $parameters['args']     = array(
            'user_id' => $user_id,
        );
        $parameters['_wpnonce'] = wp_create_nonce( $this::$id . '_page_preview' );

        // Preview Email URL.
        return add_query_arg( $parameters, admin_url( 'admin-ajax.php' ) );
    }

    /**
     * Get Preview URL Button.
     *
     * @since 1.8.4
     * @access public
     *
     * @param int $user_id User ID.
     */
    public function get_preview_url_button( $user_id ) {
        return sprintf(
            '<a href="%s" target="_blank" class="btn-preview-email">%s</a>',
            $this->get_preview_url( $user_id ),
            __( 'Click to preview email', 'loyalty-program-for-woocommerce' )
        );
    }

    /**
     * Get WooCommerce Email Setting URL Button.
     *
     * @since 1.8.4
     * @access public
     */
    public function get_woocommerce_email_setting_url_button() {
        return sprintf(
            '<a href="%s" class="btn-wc-email-setting">%s</a>',
            admin_url( 'admin.php?page=wc-settings&tab=email&section=' . $this::$id ),
            __( 'Edit email content', 'loyalty-program-for-woocommerce' )
        );
    }

    /**
     * Add localized data.
     * - Loyalty Program Reminder.
     *
     * @since 3.5.5
     * @access public
     *
     * @param array $data Localized Data.
     */
    public function admin_localized( $data ) {
        $id                         = $this::$id;
        $rest_url                   = $this->_constants->REST_API_NAMESPACE . '/' . API_Email_Loyalty_Point_Reminder::$base . '/send';
        $rest_url                   = get_rest_url( null, $rest_url );
        $data['admin_url']          = admin_url();
        $data['customer_list_page'] = array(
            'rest_url' => $rest_url,
            'labels'   => array(
                'view_stats' => __( 'View Stats', 'loyalty-program-for-woocommerce' ),
                'remind'     => __( 'Remind', 'loyalty-program-for-woocommerce' ),
                'loading'    => __( 'Loading', 'loyalty-program-for-woocommerce' ),
                'modal'      => array(
                    'heading' => __( 'Send a Reminder', 'loyalty-program-for-woocommerce' ),
                    'text'    => __( 'Manually send an email reminder to this customer to remind them about using their loyalty point :', 'loyalty-program-for-woocommerce' ),
                    'button'  => array(
                        'preview' => __( 'Preview', 'loyalty-program-for-woocommerce' ),
                        'send'    => __( 'Send', 'loyalty-program-for-woocommerce' ),
                    ),
                    'error'   => array(
                        'heading' => __( 'Failed to send email', 'loyalty-program-for-woocommerce' ),
                    ),
                    'success' => array(
                        'text' => __( 'The reminder has been successfully emailed to customer_account', 'loyalty-program-for-woocommerce' ),
                    ),
                ),
            ),
            'nonce'    => array(
                'preview' => wp_create_nonce( sprintf( '%s_page_preview', $id ) ),
                'send'    => wp_create_nonce( sprintf( '%s_page_send', $id ) ),
            ),
        );

        return $data;
    }

    /**
     * Check if scheduled email is valid to be sent to a customer.
     *
     * For this particular email, this is where we check if :
     * - Customer has balance.
     *
     * @since 1.8.4
     * @access public
     *
     * @param int $user_id User ID.
     *
     * @return \WP_Error|true — Return true if email is valid, otherwise return WP_Error object.
     */
    public function is_valid( $user_id ) {
        // Check if customer has a balance.
        $customer = new Customer( $user_id );
        $balance  = $customer->get_points(); // Get balance.
        if ( 0 >= $balance ) {
            return new \WP_Error(
                $this::$id . '_balance_invalid',
                __( 'Email reminder can not be sent to customers with insufficient balance.', 'loyalty-program-for-woocommerce' ),
                array(
                    'status' => 400,
                    'data'   => compact( 'user_id' ),
                )
            );
        }

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Hooks
    |--------------------------------------------------------------------------
    */

    /**
     * Execute Emails class.
     *
     * @since 1.8.4
     * @access public
     * @inherit ACFWF\Interfaces\Model_Interface
     */
    public function run() {
        add_filter( 'woocommerce_email_classes', array( $this, 'register_woocommerce_email_classes' ) ); // Register woocommerce email instances.
        add_action( $this::$id . '_trigger', array( $this, 'trigger' ), 10, 1 ); // Trigger email loyalty point reminder (Action Scheduler).
        add_action( $this::$id . '_send', array( $this, 'send' ), 10, 1 ); // Send the email hook, for filter purposes.
        add_filter( 'lpfw_admin_app_localized', array( $this, 'admin_localized' ) );

        // Hook into loyalty point entry.
        add_filter( 'lpfw_point_entry_created', array( $this, 'reschedule' ), 10, 1 ); // Hook when loyalty point entry is created.
        add_filter( 'lpfw_point_entry_updated', array( $this, 'reschedule' ), 10, 1 ); // Hook when loyalty point entry is updated.
    }
}
