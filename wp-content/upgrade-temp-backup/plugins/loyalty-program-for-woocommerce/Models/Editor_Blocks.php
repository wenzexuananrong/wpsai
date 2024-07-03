<?php
namespace LPFW\Models;

use LPFW\Abstracts\Abstract_Main_Plugin_Class;
use LPFW\Helpers\Helper_Functions;
use LPFW\Helpers\Plugin_Constants;
use LPFW\Interfaces\Initiable_Interface;
use LPFW\Interfaces\Model_Interface;
use LPFW\Objects\Vite_App;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Model that houses the logic of extending the coupon system of woocommerce.
 * It houses the logic of handling coupon url.
 * Public Model.
 *
 * @since 1.8.1
 */
class Editor_Blocks implements Model_Interface, Initiable_Interface {
    /*
    |--------------------------------------------------------------------------
    | Class Properties
    |--------------------------------------------------------------------------
     */

    /**
     * Property that holds the single main instance of URL_Coupon.
     *
     * @since 1.8.1
     * @access private
     * @var Editor_Blocks
     */
    private static $_instance;

    /**
     * Model that houses all the plugin constants.
     *
     * @since 1.8.1
     * @access private
     * @var Plugin_Constants
     */
    private $_constants;

    /**
     * Property that houses all the helper functions of the plugin.
     *
     * @since 1.8.1
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
     * @since 1.8.1
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
     * @since 1.8.1
     * @access public
     *
     * @param Abstract_Main_Plugin_Class $main_plugin      Main plugin object.
     * @param Plugin_Constants           $constants        Plugin constants object.
     * @param Helper_Functions           $helper_functions Helper functions object.
     * @return Editor_Blocks
     */
    public static function get_instance( Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants, Helper_Functions $helper_functions ) {
        if ( ! self::$_instance instanceof self ) {
            self::$_instance = new self( $main_plugin, $constants, $helper_functions );
        }

        return self::$_instance;
    }

    /**
     * Register custom gutenberg blocks.
     *
     * @since 1.8.1
     * @access private
     */
    private function _register_blocks() {
        \register_block_type(
            'acfw/loyalty-points-redeem-form',
            array(
                'title'           => __( 'Loyalty Points Redeem Form', 'loyalty-program-for-woocommerce' ),
                'description'     => __( 'Display the redeem form for loyalty points.', 'loyalty-program-for-woocommerce' ),
                'render_callback' => array( $this, 'render_redeem_points_block' ),
                'attributes'      => $this->get_redeem_points_default_atts(),
            )
        );
    }

    /**
     * Render the redeem points block.
     *
     * @since 1.8.1
     * @access public
     *
     * @param array $attributes Block attributes.
     * @return string HTML markup of block.
     */
    public function render_redeem_points_block( $attributes ) {

        $redeem_points_vite = new Vite_App(
            'lpfw-redeem-points-block',
            'packages/lpfw-redeem-points-block/index.ts',
            array( 'jquery', 'wc-add-to-cart' )
        );
        $redeem_points_vite->enqueue();

        $attributes   = is_array( $attributes ) ? $attributes : array();
        $defaults     = $this->get_redeem_points_default_atts( true );
        $attributes   = wp_parse_args( $attributes, $defaults );
        $user_points  = \LPFW()->Calculate->get_user_total_points( get_current_user_id() );
        $min_points   = (int) $this->_helper_functions->get_option( $this->_constants->MINIMUM_POINTS_REDEEM, '0' );
        $points_name  = $this->_helper_functions->get_points_name();
        $points_worth = $this->_helper_functions->api_wc_price( \LPFW()->Calculate->calculate_redeem_points_worth( $user_points ) );
        $max_points   = LPFW()->Calculate->calculate_allowed_max_points( $user_points );
        $classnames   = array( 'lpfw-redeem-points-block' );

        // replace placeholder tags with actual calculated values.
        $attributes = array_map(
            function ( $value ) use ( $user_points, $points_worth, $max_points, $min_points ) {
                if ( 'string' !== gettype( $value ) ) {
                    return $value;
                }

                $test = str_replace(
                    array( '{points}', '{points_worth}', '{max_points}', '{min_points}' ),
                    array( $user_points, $points_worth, $max_points, $min_points ),
                    $value
                );

                return $test;
            },
            $attributes
        );

        extract( $attributes ); //  phpcs:ignore

        if ( isset( $className ) ) {
            $classnames[] = $className;
        }

        ob_start();

        $this->_helper_functions->load_template(
            'lpfw-blocks/redeem-points-block.php',
            array(
                'classnames'         => $classnames,
                'points_summary'     => $points_summary,
                'points_description' => $points_description,
                'input_placeholder'  => $input_placeholder,
                'button_text'        => $button_text,
                'min_points'         => $min_points,
                'max_points'         => $max_points,
            )
        );

        return ob_get_clean();
    }

    /*
    |--------------------------------------------------------------------------
    | Utilities
    |--------------------------------------------------------------------------
     */

    /**
     * Get default attributes for the redeem points block.
     *
     * @since 1.8.1
     * @access private
     *
     * @param bool $defaults_only Set to true to only return default values.
     * @return array List of attributes or default values.
     */
    public function get_redeem_points_default_atts( $defaults_only = false ) {
        $points_name = $this->_helper_functions->get_points_name();
        $attributes  = array(
            'points_summary'     => array(
                'type'    => 'string',
                'default' => sprintf(
                    /* Translators: %1$s: user points balance, %2$s: points name, %3$s: Point's worth in currency */
                    __( 'You have %1$s %2$s (worth %3$s)', 'loyalty-program-for-woocommerce' ),
                    '<strong>{points}</strong>',
                    strtolower( $points_name ),
                    '<strong>{points_worth}</strong>'
                ),
            ),
            'points_description' => array(
                'type'    => 'string',
                'default' => sprintf(
                    /* Translators: %1$s: Maximum allowed points value to redeem, %2$s: points name */
                    __( 'You may redeem up to %1$s %2$s to get a discount for your order.', 'loyalty-program-for-woocommerce' ),
                    '<strong>{max_points}</strong>',
                    strtolower( $points_name )
                ),
            ),
            'input_placeholder'  => array(
                'type'    => 'string',
                'default' => sprintf(
                    /* Translators: %s: points name */
                    __( 'Enter %s', 'loyalty-program-for-woocommerce' ),
                    strtolower( $points_name )
                ),
            ),
            'button_text'        => array(
                'type'    => 'string',
                'default' => __( 'Redeem', 'loyalty-program-for-woocommerce' ),
            ),
        );

        return $defaults_only ? $this->_get_attributes_default_values( $attributes ) : $attributes;
    }

    /**
     * Get the default values only from the list of attributes.
     *
     * @since 1.8.1
     * @access private
     *
     * @param array $attributes Gutenberg block attributes.
     * @return array List of default values for each attribute.
     */
    private function _get_attributes_default_values( $attributes ) {
        // add default classname attribute from "Advanced" panel.
        $attributes['className'] = array( 'default' => '' );

        return array_map(
            function ( $a ) {
                return $a['default'];
            },
            $attributes
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Fulfill implemented interface contracts
    |--------------------------------------------------------------------------
     */

    /**
     * Execute codes that needs to run plugin activation.
     *
     * @since 1.8.1
     * @access public
     * @implements LPFW\Interfaces\Initializable_Interface
     */
    public function initialize() {
        $this->_register_blocks();
    }

    /**
     * Execute Editor_Blocks class.
     *
     * @since 1.8.1
     * @access public
     * @inherit LPFW\Interfaces\Model_Interface
     */
    public function run() {

        add_shortcode( 'lpfw_redeem_points', array( $this, 'render_redeem_points_block' ) );
    }
}
