<?php
namespace ACFWP\Models;

use ACFWP\Abstracts\Abstract_Main_Plugin_Class;
use ACFWP\Abstracts\Base_Model;
use ACFWP\Helpers\Helper_Functions;
use ACFWP\Helpers\Plugin_Constants;
use ACFWP\Interfaces\Initiable_Interface;
use ACFWP\Interfaces\Model_Interface;
use ACFWP\Models\Emails\Email_Store_Credit_Reminder;
use ACFWP\Models\REST_API\API_Email_Store_Credit_Reminder;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Model that houses the logic of extending the advanced coupons settings.
 *
 * @since 2.0
 */
class Module_Settings extends Base_Model implements Model_Interface, Initiable_Interface {
    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
     */

    /**
     * Class constructor.
     *
     * @since 2.0
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
    | Module settings
    |--------------------------------------------------------------------------
     */

    /**
     * Register premium settings sections.
     *
     * @since 2.0
     * @access public
     *
     * @param array $sections Settings sections.
     * @return array Filtered settings sections.
     */
    public function register_premium_settings_sections( $sections ) {
        $rearranage = array();
        foreach ( $sections as $key => $label ) {
            $rearranage[ $key ] = $label;

            // add after BOGO Deals settings tab.
            if ( 'acfw_setting_bogo_deals_section' === $key ) {
                if ( \ACFWF()->Helper_Functions->is_module( Plugin_Constants::SCHEDULER_MODULE ) ) {
                    $rearranage['acfw_setting_scheduler_section'] = __( 'Scheduler', 'advanced-coupons-for-woocommerce' );
                }
            }
        }

        return $rearranage;
    }

    /**
     * Register premium modules.
     *
     * @since 2.0
     * @access public
     *
     * @param array $modules Modules settings list.
     * @return array Filtered modules settings list.
     */
    public function register_premium_modules_settings( $modules ) {
        $modules[] = array(
            'title'   => __( 'Auto Apply', 'advanced-coupons-for-woocommerce' ),
            'type'    => 'checkbox',
            'desc'    => __( "Have your coupon automatically apply once it's able to be applied.", 'advanced-coupons-for-woocommerce' ),
            'id'      => Plugin_Constants::AUTO_APPLY_MODULE,
            'default' => 'yes',
        );

        $modules[] = array(
            'title'   => __( 'Advanced Usage Limits', 'advanced-coupons-for-woocommerce' ),
            'type'    => 'checkbox',
            'desc'    => __( 'Improves the usage limits feature of coupons, allowing you to set a time period to reset the usage counts.', 'advanced-coupons-for-woocommerce' ),
            'id'      => Plugin_Constants::USAGE_LIMITS_MODULE,
            'default' => 'yes',
        );

        $modules[] = array(
            'title'   => __( 'Shipping Overrides', 'advanced-coupons-for-woocommerce' ),
            'type'    => 'checkbox',
            'desc'    => __( 'Lets you provide coupons that can discount shipping prices for any shipping method.', 'advanced-coupons-for-woocommerce' ),
            'id'      => Plugin_Constants::SHIPPING_OVERRIDES_MODULE,
            'default' => 'yes',
        );

        $modules[] = array(
            'title'   => __( 'Add Products', 'advanced-coupons-for-woocommerce' ),
            'type'    => 'checkbox',
            'desc'    => __( 'On application of the coupon add certain products to the cart automatically after applying coupon.', 'advanced-coupons-for-woocommerce' ),
            'id'      => Plugin_Constants::ADD_PRODUCTS_MODULE,
            'default' => 'yes',
        );

        $modules[] = array(
            'title'   => __( 'One Click Apply', 'advanced-coupons-for-woocommerce' ),
            'type'    => 'checkbox',
            'desc'    => __( 'Lets you show a WooCommerce notice to a customer if the coupon is able to be applied with a button to apply it.', 'advanced-coupons-for-woocommerce' ),
            'id'      => Plugin_Constants::APPLY_NOTIFICATION_MODULE,
            'default' => 'yes',
        );

        $modules[] = array(
            'title'   => __( 'Payment Methods Restriction', 'advanced-coupons-for-woocommerce' ),
            'type'    => 'checkbox',
            'desc'    => __( 'Restrict coupons to be used by certain payment method gateways only.', 'advanced-coupons-for-woocommerce' ),
            'id'      => Plugin_Constants::PAYMENT_METHODS_RESTRICT,
            'default' => 'yes',
        );

        $modules[] = array(
            'title'   => __( 'Sort Coupons in Cart', 'advanced-coupons-for-woocommerce' ),
            'type'    => 'checkbox',
            'desc'    => __( 'Set priority for each coupon and automatically sort the applied coupons on cart/checkout. This will also sort coupons under auto apply and apply notifications.', 'advanced-coupons-for-woocommerce' ),
            'id'      => Plugin_Constants::SORT_COUPONS_MODULE,
            'default' => '',
        );

        $modules[] = array(
            'title'   => __( 'Virtual Coupons', 'advanced-coupons-for-woocommerce' ),
            'type'    => 'checkbox',
            'desc'    => __( "Bulk generate 100's or 1000's of unique alternative coupon codes for a coupon to use in welcome sequences, abandoned cart sequences, and other scenarios.", 'advanced-coupons-for-woocommerce' ),
            'id'      => Plugin_Constants::VIRTUAL_COUPONS_MODULE,
            'default' => 'yes',
        );

        $modules[] = array(
            'title'   => __( 'Cashback Coupon', 'advanced-coupons-for-woocommerce' ),
            'type'    => 'checkbox',
            'desc'    => __( 'A new coupon discount type that provides cashback to customers as store credits.', 'advanced-coupons-for-woocommerce' ),
            'id'      => Plugin_Constants::CASHBACK_COUPON_MODULE,
            'default' => 'yes',
        );

        return $modules;
    }

    /**
     * Register day time scheduler settings to the scheduler section.
     *
     * @since 3.5
     * @access public
     *
     * @param array $settings List of setting field elements.
     * @return array Filtered list of setting field elements.
     */
    public function register_day_time_scheduler_settings( $settings ) {
        $settings[] = array(
			'title'       => __( 'Invalid days and time error message (global)', 'advanced-coupons-for-woocommerce' ),
			'type'        => 'textarea',
			'desc'        => __( 'Optional. Show a custom error message to customers that try to apply this coupon on days and/or times that are not valid. Leave blank to use the default message.', 'advanced-coupons-for-woocommerce' ),
			'id'          => $this->_constants->DAYTIME_SCHEDULES_ERROR_MESSAGE,
			'css'         => 'width: 500px; display: block;',
			'placeholder' => __( 'The {coupon_code} coupon cannot be applied at this day or time.', 'advanced-coupons-for-woocommerce' ),
		);

        return $settings;
    }

    /**
     * Get premium settings fields.
     *
     * @since 2.0
     * @since 3.5 Function is disabled as there are no setting sections to be added anymore. Leaving it here for future use.
     * @access public
     *
     * @param array  $settings        Settings list.
     * @param string $current_section Current section name.
     * @return array Filtered settings list.
     */
    public function get_premium_settings_fields( $settings, $current_section ) {
        return $settings;
    }

    /**
     * Register Loyalty Programs settings page.
     *
     * @since 2.2
     * @access public
     *
     * @param string $toplevel_slug Top level menu slug.
     */
    public function register_loyalty_programs_submenu( $toplevel_slug ) {
        wc_deprecrated_function( 'Module_Settings::' . __FUNCTION__, '2.6.3' );
    }

    /**
     * Display loyalty programs settings page.
     *
     * @since 2.2
     * @access public
     */
    public function display_loyalty_programs_settings_page() {
        wc_deprecrated_function( 'Module_Settings::' . __FUNCTION__, '2.6.3' );
    }

    /**
     * Add premium options.
     *
     * @since 3.5.5
     * @access public
     *
     * @param array $options Premium Options.
     */
    public function admin_setting_premium( $options ) {
        // Store Credit Reminder - Subtitle.
        $options[] = array(
            'title' => __( 'Reminder Emails', 'advanced-coupons-for-woocommerce' ),
            'type'  => 'subtitle',
            'desc'  => __( 'Send a reminder emails to your customers to get them to come back and use their store credit. Emails will be sent automatically when customers have been inactive for the given period and contain their current store credit balance along with suggested product from your store.', 'advanced-coupons-for-woocommerce' ),
        );
        // Store Credit Reminder - Waiting period for inactive user.
        $options[] = array(
            'id'       => Email_Store_Credit_Reminder::$id . '_schedule_waiting_period',
            'title'    => __( 'How long after being inactive should a reminder be sent?', 'advanced-coupons-for-woocommerce' ),
            'default'  => 30,
            'type'     => 'number',
            'desc_tip' => __( "A customer is said to be inactive if they haven't placed any orders after the number of days set in this setting. Once inactive, the reminder email will trigger and resend after each period if they are still found to be inactive.", 'advanced-coupons-for-woocommerce' ),
            'suffix'   => 'days',
        );
        // Store Credit Reminder - Time Schedule.
        $options[] = array(
            'id'       => Email_Store_Credit_Reminder::$id . '_schedule',
            'title'    => __( 'What time of day should reminders be sent?', 'advanced-coupons-for-woocommerce' ),
            'type'     => 'timepicker',
            'format'   => 'h:mm a',
            'default'  => gmdate( 'h:i a', strtotime( Email_Store_Credit_Reminder::$time_schedule ) ),
            'desc_tip' => __( 'This is the time of day that emails are scheduled to send based on the timezone of your store (" + timezone setting text + "). By default, emails will be set to send at 10am.', 'advanced-coupons-for-woocommerce' ),
        );
        // Store Credit Reminder - Promotion.
        $desc      = ACFWP()->Email_Store_Credit_Reminder->get_preview_url_button( get_current_user_id() );
        $desc     .= ACFWP()->Email_Store_Credit_Reminder->get_woocommerce_email_setting_url_button();
        $options[] = array(
            'id'       => Email_Store_Credit_Reminder::$id . '_promotion',
            'title'    => __( 'Suggested product/categories to include in reminder emails', 'advanced-coupons-for-woocommerce' ),
            'type'     => 'select',
            'default'  => 'none',
            'desc_tip' => __( 'The product/categories selected will be shown to customers at the bottom of the email.', 'advanced-coupons-for-woocommerce' ),
            'desc'     => $desc,
            'options'  => array(
                'none'       => __( 'None', 'advanced-coupons-for-woocommerce' ),
                'random'     => __( 'Random', 'advanced-coupons-for-woocommerce' ),
                'popular'    => __( 'Popular', 'advanced-coupons-for-woocommerce' ),
                'highrating' => __( 'High Rating', 'advanced-coupons-for-woocommerce' ),
                $this->_get_admin_option_store_credits_promotion_product_categories(),
            ),
        );

        return $options;
    }

    /**
     * Get Admin Option for Store Credits Promotion (Product Categories).
     * - This function is design to return & reformat array for select option.
     *
     * @since 3.5.5
     * @access private
     */
    private function _get_admin_option_store_credits_promotion_product_categories() {
        $product_categories = $this->_helper_functions->get_product_categories();
        $options            = array();
        foreach ( $product_categories as $value => $label ) {
            $options[] = array(
                'label' => $label,
                'value' => sprintf( 'cat-%s', $value ),
            );
        }

        return array(
            'label'   => __( 'Product Categories', 'advanced-coupons-for-woocommerce' ),
            'options' => $options,
        );
    }

    /**
     * Add premium localized data.
     *
     * @since 3.5.5
     * @access public
     *
     * @param array $data Premium Localized Data.
     */
    public function admin_setting_premium_localized( $data ) {
        $settings = array();

        // Store Credit Reminder Settings.
        $id                                = Email_Store_Credit_Reminder::$id;
        $rest_url                          = $this->_constants->REST_API_NAMESPACE . '/' . API_Email_Store_Credit_Reminder::$base . '/send';
        $rest_url                          = get_rest_url( null, $rest_url );
        $settings['store_credit_reminder'] = array(
            'rest_url' => $rest_url,
            'labels'   => array(
                'remind'  => __( 'Remind', 'advanced-coupons-for-woocommerce' ),
                'loading' => __( 'Loading', 'advanced-coupons-for-woocommerce' ),
                'modal'   => array(
                    'heading' => __( 'Send a Reminder', 'advanced-coupons-for-woocommerce' ),
                    'text'    => __( 'Manually send an email reminder to this customer to remind them about using their store credit :', 'advanced-coupons-for-woocommerce' ),
                    'button'  => array(
                        'preview' => __( 'Preview', 'advanced-coupons-for-woocommerce' ),
                        'send'    => __( 'Send', 'advanced-coupons-for-woocommerce' ),
                    ),
                    'error'   => array(
                        'heading' => __( 'Failed to send email', 'advanced-coupons-for-woocommerce' ),
                    ),
                    'success' => array(
                        'text' => __( 'The reminder has been successfully emailed to customer_account', 'advanced-coupons-for-woocommerce' ),
                    ),
                ),
            ),
            'nonce'    => array(
                'preview' => wp_create_nonce( sprintf( '%s_page_preview', $id ) ),
                'send'    => wp_create_nonce( sprintf( '%s_page_send', $id ) ),
            ),
        );

        // Merge data.
        foreach ( $settings as $key => $setting ) {
            $data['store_credits_page'] = array_merge_recursive( $data['store_credits_page'], $settings[ $key ] );
        }

        return $data;
    }

    /*
    |--------------------------------------------------------------------------
    | Fulfill implemented interface contracts
    |--------------------------------------------------------------------------
     */

    /**
     * Filter help section options.
     *
     * @since 2.0
     * @access public
     *
     * @param array $settings Settings data.
     */
    public function filter_help_section_options( $settings ) {
        // get the last key of the array.
        $end_key     = key( array_slice( $settings, -1, 1, true ) );
        $section_end = array( $settings[ $end_key ] );

        unset( $settings[ $end_key ] );

        $fields = array(

            array(
                'title' => __( 'Utilities', 'advanced-coupons-for-woocommerce' ),
                'type'  => 'acfw_divider_row',
                'id'    => 'acfw_utilities_divider_row',
            ),

            array(
                'title' => __( 'Rebuild/Clear Auto Apply Coupons Cache', 'advanced-coupons-for-woocommerce' ),
                'type'  => 'acfw_rebuild_auto_apply_cache',
                'desc'  => __( 'Manually rebuild and validate all auto apply coupons within the cache or clear the cache entirely.', 'advanced-coupons-for-woocommerce' ),
                'id'    => 'acfw_rebuild_auto_apply_cache',
            ),

            array(
                'title' => __( 'Rebuild/Clear Apply Notification Coupons Cache', 'advanced-coupons-for-woocommerce' ),
                'type'  => 'acfw_rebuild_apply_notification_cache',
                'desc'  => __( 'Manually rebuild and validate all apply notification coupons within the cache or clear the cache entirely.', 'advanced-coupons-for-woocommerce' ),
                'id'    => 'acfw_rebuild_apply_notifications_cache',
            ),

            array(
                'title' => __( 'Reset coupons usage limit', 'advanced-coupons-for-woocommerce' ),
                'type'  => 'acfw_reset_coupon_usage_limit',
                'desc'  => __( 'Manually run cron for resetting usage limit for all applicable coupons.', 'advanced-coupons-for-woocommerce' ),
            ),

        );

        return array_merge( $settings, $fields, $section_end );
    }

    /**
     * Extend general settings option.
     *
     * @since 3.5.5
     * @access public
     *
     * @param array $option General settings option.
     */
    public function setting_general_option( $option ) {
        $option[] = array(
            'title'   => __( 'Hide the applied coupon on the cart and checkout if it is equal to $0', 'advanced-coupons-for-woocommerce' ),
            'type'    => 'checkbox',
            'desc'    => __( 'If checked, this feature will hide coupons that have a value of $0 from view on the cart and checkout pages.', 'advanced-coupons-for-woocommerce' ),
            'id'      => $this->_constants->OPTION_HIDE_ZERO_DOLLAR_COUPON,
            'default' => 'no',
        );

        return $option;
    }

    /*
    |--------------------------------------------------------------------------
    | REST API
    |--------------------------------------------------------------------------
     */

    /**
     * Register ACFWP API settings sections.
     *
     * @since 2.2
     * @since 3.5 Function is disabled as there are no setting sections to be added anymore. Leaving it here for future use.
     * @access public
     *
     * @param array  $sections        Settings sections.
     * @param string $current_section Current section.
     * @return array Filtered settings section.
     */
    public function register_acfwp_api_settings_sections( $sections, $current_section ) {
        return $sections;
    }

    /*
    |--------------------------------------------------------------------------
    | Fulfill implemented interface contracts
    |--------------------------------------------------------------------------
     */

    /**
     * Execute codes that needs to run plugin activation.
     *
     * @since 2.0
     * @access public
     * @implements ACFWP\Interfaces\Initializable_Interface
     */
    public function initialize() {}

    /**
     * Execute Module_Settings class.
     *
     * @since 2.0
     * @access public
     * @inherit ACFWP\Interfaces\Model_Interface
     */
    public function run() {
        add_filter( 'woocommerce_get_sections_acfw_settings', array( $this, 'register_premium_settings_sections' ), 10, 1 );
        add_filter( 'acfw_modules_settings', array( $this, 'register_premium_modules_settings' ), 10, 1 );
        add_filter( 'acfw_settings_help_section_options', array( $this, 'filter_help_section_options' ), 10, 1 );
        add_filter( 'acfw_setting_scheduler_options', array( $this, 'register_day_time_scheduler_settings' ), 10, 1 );
        add_filter( 'acfw_setting_general_options', array( $this, 'setting_general_option' ), 10, 1 );

        // Store Credits.
        if ( \ACFWF()->Helper_Functions->is_module( Plugin_Constants::STORE_CREDITS_MODULE ) ) {
            add_filter( 'acfw_setting_store_credits_options', array( $this, 'admin_setting_premium' ) );
            add_filter( 'acfwf_admin_app_localized', array( $this, 'admin_setting_premium_localized' ), 10, 1 );
        }
    }

}
