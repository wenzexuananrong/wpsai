<?php

namespace LPFW\Models\Emails;

use LPFW\Abstracts\Abstract_Main_Plugin_Class;
use LPFW\Abstracts\Email_Model;
use LPFW\Helpers\Helper_Functions;
use LPFW\Helpers\Plugin_Constants;
use LPFW\Interfaces\Model_Interface;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class that houses the hooks of the earned points notification email.
 *
 * @since 1.8.4
 */
class Email_Earned_Points_Notification extends Email_Model implements Model_Interface {
    /**
     * Property that holds email id.
     *
     * @since 1.8.4
     * @access public
     * @var string
     */
    public static $id = 'lpfw_earned_points_notification';

    /**
     * Property that holds email constants for storing in database.
     *
     * @since 1.8.4
     * @access public
     * @var array
     */
    public static $constants = array(
        'schedule' => 'lpfw_earned_points_notification_schedule',
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
        $emails[ $this::$id ] = new WC_Email_Earned_Points_Notification( $this::$id );

        return $emails;
    }

    /**
     * Trigger email earned points notification (Action Scheduler)
     *
     * @since 1.8.4
     *
     * @access public
     *
     * @param int    $user_id                   User ID.
     * @param string $user_email                User email.
     * @param int    $user_earned_points        User earned points.
     * @param string $user_earned_points_action User earned points action.
     *
     * @return void
     */
    public function trigger( $user_id, $user_email = '', $user_earned_points = 0, $user_earned_points_action = '' ) {
        $customer      = new \WC_Customer( $user_id );
        $earned_points = array(
            'customer_earned_points'        => $user_earned_points,
            'customer_earned_points_action' => $user_earned_points_action,
        );
        \WC()->mailer(); // Load WC mailer instance as for some reason the action scheduler doesn't load the mailer instance on cron.
        do_action( $this::$id . '_send', $customer, $earned_points );
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

        // Set default earned points.
        $earned_points                                  = array();
        $balance                                        = \LPFW()->Calculate->get_user_points_balance_data( $customer->get_id() );
        $earned_points['customer_earned_points']        = $balance['points'] ?? 0;
        $earned_points['customer_earned_points_action'] = '{customer_earned_points_action}';

        // Get latest earn points entry and if available set it as earned points.
        $entry = \LPFW()->Entries->get_user_latest_entry( $customer->get_id(), array( 'entry_type' => 'earn' ) );
        if ( $entry ) {
            $earned_points['customer_earned_points']        = $entry['entry_amount'];
            $earned_points['customer_earned_points_action'] = $entry['entry_action'];
        }

        // Return email content.
        \WC()->mailer(); // This is required to load \WC_Email class.
        $email = new WC_Email_Earned_Points_Notification( $this::$id );
        $email->set_customer( $customer );
        $email->set_earned_points( $earned_points );

        return $email->style_inline( $email->get_content_html() );
    }

    /**
     * Send the email hook, for filter purposes.
     *
     * @since 1.8.4
     * @access public
     *
     * @param \WC_Customer $customer Customer object.
     * @param array        $earned_points Earned points.
     *
     * @return void
     */
    public function send( $customer, $earned_points = array() ) {
        // Do not send if $earned_points is empty.
        if ( empty( $earned_points ) ) {
            return;
        }

        // Hook: Before send email.
        do_action( $this::$id . '_before_send_email', $customer );

        // Get email instance.
        $email = \WC()->mailer()->emails[ $this::$id ];
        $email->setup_locale();
        $email->set_customer( $customer );
        $email->set_earned_points( $earned_points );
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
        $args = array_merge(
            array(
				'user_id'    => intval( $args['user_id'] ),
				'user_email' => $user->user_email ?? '',
            ),
            $this->args
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
     *
     * @return \WC_DateTime|null
     */
    public function reschedule( $args = array() ) {
        // Grab arguments.
        $args      = array_merge( $this->args, $args );
        $constants = $this::$constants;

        // Check if entry type is increase otherwise return.
        // There are only 2 entry type on LPFW which are earn and redeem.
        if ( ! isset( $args['type'] ) || 'earn' !== $args['type'] ) {
            return null;
        }

        // Return if customer id is not valid.
        if ( ! isset( $args['user_id'] ) || ! $args['user_id'] ) {
            return null;
        }

        // Reschedule only if email is enabled.
        $email = \WC()->mailer()->emails[ $this::$id ];
        if ( ! $email->is_enabled() ) {
            return null;
        }

        // Construct email data.
        $hook = $this::$id . '_trigger';
        $user = get_userdata( $args['user_id'] );
        $args = array(
            'user_id'                   => intval( $args['user_id'] ),
            'user_email'                => $user->user_email ?? '',
            'user_earned_points'        => intval( $args['points'] ),
            'user_earned_points_action' => $args['action'],
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
        $current_time = new \WC_DateTime( 'now', new \DateTimeZone( $this->_helper_functions->get_site_current_timezone() ) );
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
        add_action( $this::$id . '_trigger', array( $this, 'trigger' ), 10, 4 ); // Trigger email earned points notification (Action Scheduler).
        add_action( $this::$id . '_send', array( $this, 'send' ), 10, 2 ); // Send the email hook, for filter purposes.

        // Hook into loyalty point entry.
        add_filter( 'lpfw_point_entry_created', array( $this, 'reschedule' ), 10, 1 ); // Hook when loyalty point entry is created.
    }
}
