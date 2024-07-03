<?php
namespace LPFW\Objects\Blocks;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;
use LPFW\Abstracts\Base_Model;
use LPFW\Objects\Vite_App;

/**
 * Class for integrating with WooCommerce Blocks
 *
 * @since 1.8.5
 */
class Checkout_Integration extends Base_Model implements IntegrationInterface {
    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
    */

    /**
     * The name of the integration.
     * This is used internally to identify the integration and should be unique.
     *
     * @since 1.8.5
     * @access public
     *
     * @return string
     */
    public function get_name() {
        return 'lpfw-wc-checkout-block';
    }

    /**
     * When called invokes any initialization/setup for the integration.
     *
     * @since 1.8.5
     * @access public
     */
    public function initialize() {
        $this->register_scripts();
    }

    /**
     * Register Scripts and Styles.
     * - This is where you will register custom scripts for your block
     *
     * @since 1.8.5
     * @access private
     */
    private function register_scripts() {
        $vite_app = new Vite_App(
            'lpfw-wc-checkout-block-integration', // Don't forget to register this handle in the get_script_handles() or get_editor_script_handles() method.
            'packages/lpfw-checkout-block/index.tsx',
            array()
        );
        $vite_app->register();
    }

    /**
     * Returns an array of script handles to enqueue in the frontend context.
     *
     * @since 1.8.5
     * @access public
     *
     * @return string[]
     */
    public function get_script_handles() {
        return array(
            'lpfw-wc-checkout-block-integration',
        );
    }

    /**
     * Returns an array of script handles to enqueue in the editor context.
     *
     * @since 1.8.5
     * @access public
     *
     * @return string[]
     */
    public function get_editor_script_handles() {
        return array();
    }

    /**
     * An array of key, value pairs of data made available to the block on the client side.
     * - To access this data in the block see code sample here : https://github.com/agungsundoro/woocommerce-blocks-test/blob/0a520112707e7e09af67e3fbbbb8876a846e6c56/packages/acfw-wc-blocks/cart/data.tsx#L19-L26
     *
     * @since 1.8.5
     * @access public
     *
     * @return array
     */
    public function get_script_data() {
        // Return data.
        return array(
            'loyalty_points' => array(
                'button_text'  => __( 'Apply', 'loyalty-program-for-woocommerce' ),
                'redeem_nonce' => wp_create_nonce( 'lpfw_redeem_points_for_user' ),
            ),
            'caret_img_src'  => \ACFWF()->Plugin_Constants->IMAGES_ROOT_URL . 'caret.svg',
        );
    }
}
