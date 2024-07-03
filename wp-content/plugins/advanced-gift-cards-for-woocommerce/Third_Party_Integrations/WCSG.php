<?php
namespace AGCFW\Third_Party_Integrations;

use AGCFW\Abstracts\Abstract_Main_Plugin_Class;
use AGCFW\Helpers\Helper_Functions;
use AGCFW\Helpers\Plugin_Constants;
use AGCFW\Interfaces\Model_Interface;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Model that houses the logic of the WCSG module.
 *
 * @since 1.3.3
 */
class WCSG implements Model_Interface {
    /*
    |--------------------------------------------------------------------------
    | Class Properties
    |--------------------------------------------------------------------------
     */

    /**
     * Property that holds the single main instance of WCSG.
     *
     * @since 1.0
     * @access private
     * @var WCSG
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
     * @return WCSG
     */
    public static function get_instance( Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants, Helper_Functions $helper_functions ) {
        if ( ! self::$_instance instanceof self ) {
            self::$_instance = new self( $main_plugin, $constants, $helper_functions );
        }

        return self::$_instance;
    }

    /**
     * Remove WCSG filter if the product item is a gift card
     * - This step is necessary because : woocommerce-subscriptions-gifting/includes/class-wcsg-product.php:38
     * - Using the same key as AGCFW `recipient_email`
     * - This will invoke `WCS_Gifting::validate_recipient_emails( wp_unslash( $_POST['recipient_email'] ) )`, which will then throw an error
     * - This is a temporary fix until the issue is resolved on their end.
     *
     * @since 1.3.3
     * @access public
     *
     * @param array $cart_item_data Cart item data.
     * @param int   $product_id     Product ID.
     * @return array Filtered cart item data.
     */
    public function fix_conflicting_add_to_cart_condition( $cart_item_data, $product_id ) {
        if ( isset( $cart_item_data['agcfw_data'] ) ) {
            remove_filter( 'woocommerce_add_cart_item_data', 'WCSG_Product::add_recipient_data', 1 );
        }

        return $cart_item_data;
    }

    /**
     * WooCommerce Subscriptions Gifting compatibility.
     *
     * @since 1.3.3
     * @access public
     * @inherit AGCFW\Interfaces\Model_Interface
     */
    public function run() {
        if ( ! $this->_helper_functions->is_plugin_active( 'woocommerce-subscriptions-gifting/woocommerce-subscriptions-gifting.php' ) ) {
            return;
        }

        add_filter( 'woocommerce_add_cart_item_data', array( $this, 'fix_conflicting_add_to_cart_condition' ), 0, 2 );
    }

}
