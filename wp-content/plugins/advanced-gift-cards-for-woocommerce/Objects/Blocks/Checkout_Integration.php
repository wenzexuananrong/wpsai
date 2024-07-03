<?php
namespace AGCFW\Objects\Blocks;

use AGCFW\Abstracts\Base_Model;
use AGCFW\Helpers\Plugin_Constants;
use AGCFW\Objects\Vite_App;
use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

/**
 * Class for integrating with WooCommerce Blocks
 *
 * @since 1.3.6
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
     * @since 1.3.6
     * @access public
     *
     * @return string
     */
    public function get_name() {
        return 'agc-wc-checkout-block';
    }

    /**
     * When called invokes any initialization/setup for the integration.
     *
     * @since 1.3.6
     * @access public
     */
    public function initialize() {
        $this->register_scripts();
    }

    /**
     * Register Scripts and Styles.
     * - This is where you will register custom scripts for your block
     *
     * @since 1.3.6
     * @access private
     */
    private function register_scripts() {
        $vite_app = new Vite_App(
            'agc-wc-checkout-block-integration', // Don't forget to register this handle in the get_script_handles() or get_editor_script_handles() method.
            'packages/agc-checkout-block/index.tsx',
            array( 'wp-components' ),
            array( 'wp-components' )
        );
        $vite_app->register();
    }

    /**
     * Returns an array of script handles registered by the integration.
     *
     * @since 1.3.6
     * @access public
     *
     * @return array
     */
    public function get_script_handles() {
        return array(
            'agc-wc-checkout-block-integration',
        );
    }

    /**
     * Returns an array of script handles registered by the integration for the editor.
     *
     * @since 1.3.6
     * @access public
     *
     * @return array
     */
    public function get_editor_script_handles() {
        return array();
    }

    /**
     * An array of key, value pairs of data made available to the block on the client side.
     * - To access this data in the block see code sample here : https://github.com/agungsundoro/woocommerce-blocks-test/blob/0a520112707e7e09af67e3fbbbb8876a846e6c56/packages/acfw-wc-blocks/cart/data.tsx#L19-L26
     *
     * @since 1.3.6
     * @access public
     *
     * @return array
     */
    public function get_script_data() {

        return array(
            'redeem_form'                   => array(
                'labels' => \AGCFW()->Redeem->get_default_redeem_form_template_args(),
                'nonce'  => wp_create_nonce( 'agcfw_redeem_gift_card' ),
            ),
            'is_user_logged_in'             => is_user_logged_in(),
            'caret_img_src'                 => $this->_constants->IMAGES_ROOT_URL . 'caret.svg',
            'question_img_src'              => $this->_constants->IMAGES_ROOT_URL . 'question.svg',
            'display_gift_card_redeem_form' => get_option( $this->_constants->DISPLAY_CHECKOUT_GIFT_CARD_REDEEM_FORM, 'yes' ),
        );
    }
}
