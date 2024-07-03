<?php
namespace AGCFW\Models;

use AGCFW\Abstracts\Abstract_Main_Plugin_Class;
use AGCFW\Abstracts\Base_Model;
use AGCFW\Helpers\Helper_Functions;
use AGCFW\Helpers\Plugin_Constants;
use AGCFW\Interfaces\Model_Interface;
use AGCFW\Objects\Blocks\Checkout_Integration;

// Exit if accessed directly.

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Model that houses the logic for WooCommerce cart block.
 *
 * @since 1.3.6
 */
class WC_Blocks extends Base_Model implements Model_Interface {
    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Class constructor.
     *
     * @since 1.3.6
     * @access public
     *
     * @param Abstract_Main_Plugin_Class $main_plugin      Main plugin object.
     * @param Plugin_Constants           $constants        Plugin constants object.
     * @param Helper_Functions           $helper_functions Helper functions object.
     */
    public function __construct( Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants, Helper_Functions $helper_functions ) {
        parent::__construct( $main_plugin, $constants, $helper_functions );
        $main_plugin->add_to_all_plugin_models( $this );
    }

    /**
     * Integration Interface
     * - This is used to enqueue css and js also for localizing data.
     * - To learn more please go to : https://github.com/woocommerce/woocommerce-blocks/blob/trunk/docs/third-party-developers/extensibility/checkout-block/integration-interface.md
     *
     * @since 1.3.6
     * @access public
     *
     * @param \Automattic\WooCommerce\Blocks\Integrations\IntegrationRegistry $integration_registry The integration registry.
     */
    public function checkout_block_integration( $integration_registry ) {
        $integration_registry->register(
            new Checkout_Integration(
                AGCFW(),
                $this->_constants,
                $this->_helper_functions
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Fulfill implemented interface contracts
    |--------------------------------------------------------------------------
    */

    /**
     * Register hooks.
     *
     * @since 1.3.6
     * @access public
     * @inherit AGCFW\Interfaces\Model_Interface
     */
    public function run() {
        add_action( 'woocommerce_blocks_checkout_block_registration', array( $this, 'checkout_block_integration' ), 10, 1 );
    }
}
