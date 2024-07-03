<?php
namespace AGCFW\Helpers;

use AGCFW\Abstracts\Abstract_Main_Plugin_Class;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Model that houses all the plugin constants.
 *
 * @since 1.0.0
 */
class Plugin_Constants {
    /*
    |--------------------------------------------------------------------------
    | Class Properties
    |--------------------------------------------------------------------------
     */

    /**
     * Single main instance of Plugin_Constants.
     *
     * @since  1.0.0
     * @access private
     * @var    Plugin_Constants
     */
    private static $_instance;

    /**
     * Class property that houses all the actual constants data.
     *
     * @since  1.0.0
     * @access private
     * @var    array
     */
    private $_data = array();

    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
     */

    /**
     * Class constructor.
     *
     * @since  1.0.0
     * @access public
     *
     * @param Abstract_Main_Plugin_Class $main_plugin Main plugin object.
     */
    public function __construct( Abstract_Main_Plugin_Class $main_plugin = null ) {
        $main_plugin_file_path = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'advanced-gift-cards-for-woocommerce' . DIRECTORY_SEPARATOR . 'advanced-gift-cards-for-woocommerce.php';
        $plugin_dir_path       = plugin_dir_path( $main_plugin_file_path ); // /home/user/var/www/wordpress/wp-content/plugins/advanced-gift-cards-for-woocommerce/.
        $plugin_dir_url        = plugin_dir_url( $main_plugin_file_path ); // http://example.com/wp-content/plugins/advanced-gift-cards-for-woocommerce/.
        $plugin_basename       = plugin_basename( $main_plugin_file_path ); // advanced-gift-cards-for-woocommerce/advanced-gift-cards-for-woocommerce.php.
        $plugin_dirname        = plugin_basename( dirname( $main_plugin_file_path ) ); // advanced-gift-cards-for-woocommerce.
        $slmw_url              = 'https://advancedcouponsplugin.com';

        $this->_data = array(

            // Configuration Constants.
            'TOKEN'                                  => 'agcfw',
            'INSTALLED_VERSION'                      => 'agcfw_installed_version',
            'VERSION'                                => '1.3.7',
            'TEXT_DOMAIN'                            => 'advanced-gift-cards-for-woocommerce',
            'THEME_TEMPLATE_PATH'                    => 'advanced-gift-cards-for-woocommerce',

            // SLMW URLs.
            'PLUGIN_SITE_URL'                        => $slmw_url,
            'LICENSE_ACTIVATION_URL'                 => $slmw_url . '/wp-admin/admin-ajax.php?action=slmw_activate_license',
            'UPDATE_DATA_URL'                        => $slmw_url . '/wp-admin/admin-ajax.php?action=slmw_get_update_data',
            'STATIC_PING_FILE'                       => $slmw_url . '/AGC.json',

            // SLMW Options.
            'OPTION_ACTIVATION_EMAIL'                => 'agcfw_slmw_activation_email',
            'OPTION_LICENSE_KEY'                     => 'agcfw_slmw_license_key',
            'OPTION_LICENSE_ACTIVATED'               => 'agcfw_license_activated',
            'OPTION_UPDATE_DATA'                     => 'agcfw_option_update_data',
            'OPTION_RETRIEVING_UPDATE_DATA'          => 'agcfw_option_retrieving_update_data',
            'SOFTWARE_KEY'                           => 'AGC',

            // Paths.
            'MAIN_PLUGIN_FILE_PATH'                  => $main_plugin_file_path,
            'PLUGIN_DIR_PATH'                        => $plugin_dir_path,
            'PLUGIN_DIR_URL'                         => $plugin_dir_url,
            'PLUGIN_BASENAME'                        => $plugin_basename,
            'PLUGIN_DIRNAME'                         => $plugin_dirname,
            'VIEWS_ROOT_PATH'                        => $plugin_dir_path . 'views/',
            'TEMPLATES_ROOT_PATH'                    => $plugin_dir_path . 'templates/',
            'LOGS_ROOT_PATH'                         => $plugin_dir_path . 'logs/',
            'JS_ROOT_PATH'                           => $plugin_dir_path . 'js/',
            'IMAGES_ROOT_PATH'                       => $plugin_dir_path . 'images/',
            'DIST_ROOT_PATH'                         => $plugin_dir_path . 'dist/',

            // Database.
            'DB_TABLES_CREATED'                      => 'agcfw_db_tables_created',
            'DB_TABLE_NAME'                          => 'acfw_gift_cards',
            'DB_TABLE_VERSION'                       => 'agcfw_db_table_version',
            'DB_VERSION'                             => '1.3.7',

            // URLs.
            'CSS_ROOT_URL'                           => $plugin_dir_url . 'css/',
            'IMAGES_ROOT_URL'                        => $plugin_dir_url . 'images/',
            'JS_ROOT_URL'                            => $plugin_dir_url . 'js/',
            'DIST_ROOT_URL'                          => $plugin_dir_url . 'dist/',

            // Options.
            'OPTION_WPB_ACTIVATION_CODE_TRIGGERED'   => 'option_agcfw_activation_code_triggered',
            'DESIGN_ATTACHMENTS'                     => 'agcfw_design_attachments',
            'DISPLAY_CHECKOUT_GIFT_CARD_REDEEM_FORM' => 'agcfw_display_checkout_gift_card_redeem_form',

            // Settings ( Help ).
            'OPTION_CLEAN_UP_PLUGIN_OPTIONS'         => 'agcfw_clean_up_plugin_options',

            // REST API.
            'REST_API_NAMESPACE'                     => 'coupons/v1',

            // Metas.
            'GIFT_CARD_VALUE'                        => '_agcfw_gift_card_value',
            'GIFT_CARD_IS_GIFTABLE'                  => '_agcfw_is_giftable',
            'GIFT_CARD_ALLOW_DELIVERY_DATE'          => '_agcfw_allow_delivery_date',
            'GIFT_CARD_EXPIRY'                       => '_agcfw_expiry',
            'GIFT_CARD_EXPIRY_CUSTOM'                => '_agcfw_expiry_custom',
            'GIFT_CARD_DESIGN'                       => '_agcfw_gift_card_design',
            'GIFT_CARD_CUSTOM_BG'                    => '_agcfw_gift_card_custom_bg',
            'GIFT_CARD_DATA'                         => '_agcfw_gift_card_data',
            'GIFT_CARD_SEND_TO_META'                 => 'agcfw_send_to',
            'GIFT_CARD_RECIPIENT_NAME_META'          => 'agcfw_recipient_name',
            'GIFT_CARD_RECIPIENT_EMAIL_META'         => 'agcfw_recipient_email',
            'GIFT_CARD_SHORT_MESSAGE_META'           => 'agcfw_short_message',
            'GIFT_CARD_ENTRY_ID_META'                => 'agcfw_gift_card_entry_id',
            'GIFT_CARD_DELIVERY_DATE_META'           => '_agcfw_gift_card_delivery_date',
            'GIFT_CARD_CUSTOMER_TIMEZONE_META'       => '_agcfw_customer_timezone',
            'EMAIL_ALREADY_SENT_META'                => '_agcfw_email_already_sent',

            // Store Credits.
            'STORE_CREDIT_APPLY_TYPE'                => 'acfw_store_credit_apply_type',
            'STORE_CREDITS_SESSION'                  => 'acfw_store_credits_discount',
            'STORE_CREDITS_COUPON_SESSION'           => 'acfw_store_credits_coupon_discount',

            // Action scheduler.
            'CREATE_GIFT_CARD_ACTION_SCHEDULE'       => 'agcfw_create_gift_card_action_schedule',

            // Notices.
            'SHOW_GETTING_STARTED_NOTICE'            => 'agcfw_show_getting_started_notice',

            // Others.
            'DISPLAY_DATE_FORMAT'                    => 'F j, Y g:i a',
            'DB_DATE_FORMAT'                         => 'Y-m-d H:i:s',
            'DELIVERY_DATE_FIELD_FORMAT'             => 'Y-m-d g:i a',

        );

        if ( $main_plugin ) {
            $main_plugin->add_to_public_helpers( $this );
        }
    }

    /**
     * Ensure that only one instance of Plugin_Constants is loaded or can be loaded (Singleton Pattern).
     *
     * @since  1.0.0
     * @access public
     *
     * @param Abstract_Main_Plugin_Class $main_plugin Main plugin object.
     * @return Plugin_Constants
     */
    public static function get_instance( Abstract_Main_Plugin_Class $main_plugin = null ) {
        if ( ! self::$_instance instanceof self ) {
            self::$_instance = new self( $main_plugin );
        }

        return self::$_instance;
    }

    /**
     * Get constant property.
     * We use this magic method to automatically access data from the _data property so
     * we do not need to create individual methods to expose each of the constant properties.
     *
     * @since  1.0.0
     * @access public
     *
     * @throws \Exception Error message.
     * @param string $prop The name of the data property to access.
     * @return mixed Data property value.
     */
    public function __get( $prop ) {
        if ( array_key_exists( $prop, $this->_data ) ) {
            return $this->_data[ $prop ];
        } else {
            throw new \Exception( 'Trying to access unknown property' );
        }
    }

    /**
     * Get the gift card status options.
     *
     * @since 1.3.7
     * @access public
     *
     * @return array Gift card status options.
     */
    public function get_gift_card_status_options() {
        return array(
            'pending' => __( 'Pending', 'advanced-gift-cards-for-woocommerce' ),
            'used'    => __( 'Redeemed', 'advanced-gift-cards-for-woocommerce' ),
            'invalid' => __( 'Invalid', 'advanced-gift-cards-for-woocommerce' ),
            'expired' => __( 'Expired', 'advanced-gift-cards-for-woocommerce' ),
        );
    }

    /**
     * Get the gift card status label.
     *
     * @since 1.3.7
     * @access public
     *
     * @param string $status Gift card status.
     * @return string Gift card status label.
     */
    public function get_gift_card_status_label( $status ) {
        $options = $this->get_gift_card_status_options();
        return $options[ $status ] ?? '';
    }
}
