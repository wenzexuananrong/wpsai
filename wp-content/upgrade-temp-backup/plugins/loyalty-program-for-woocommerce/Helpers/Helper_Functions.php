<?php
namespace LPFW\Helpers;

use LPFW\Abstracts\Abstract_Main_Plugin_Class;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Model that houses all the helper functions of the plugin.
 *
 * 1.0.0
 */
class Helper_Functions {
    /*
    |--------------------------------------------------------------------------
    | Traits
    |--------------------------------------------------------------------------
     */
    use \LPFW\Helpers\Traits\Block;
    use \LPFW\Helpers\Traits\DateTime;
    use \LPFW\Helpers\Traits\Validation;

    /*
    |--------------------------------------------------------------------------
    | Class Properties
    |--------------------------------------------------------------------------
     */

    /**
     * Property that holds the single main instance of Helper_Functions.
     *
     * @since 1.0.0
     * @access private
     * @var Helper_Functions
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
     * @param Abstract_Main_Plugin_Class $main_plugin Main plugin object.
     * @param Plugin_Constants           $constants   Plugin constants object.
     */
    public function __construct( Abstract_Main_Plugin_Class $main_plugin = null, Plugin_Constants $constants ) {
        $this->_constants = $constants;

        if ( $main_plugin ) {
            $main_plugin->add_to_public_helpers( $this );
        }
    }

    /**
     * Ensure that only one instance of this class is loaded or can be loaded ( Singleton Pattern ).
     *
     * @since 1.0.0
     * @access public
     *
     * @param Abstract_Main_Plugin_Class $main_plugin Main plugin object.
     * @param Plugin_Constants           $constants   Plugin constants object.
     * @return Helper_Functions
     */
    public static function get_instance( Abstract_Main_Plugin_Class $main_plugin = null, Plugin_Constants $constants ) {
        if ( ! self::$_instance instanceof self ) {
            self::$_instance = new self( $main_plugin, $constants );
        }

        return self::$_instance;
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Functions
    |--------------------------------------------------------------------------
     */

    /**
     * Write data to plugin log file.
     *
     * @since 1.0.0
     * @access public
     *
     * @param mixed $log Data to log.
     */
    public function write_debug_log( $log ) {
        error_log( "\n[" . current_time( 'mysql' ) . "]\n" . $log . "\n--------------------------------------------------\n", 3, $this->_constants->LOGS_ROOT_PATH . 'debug.log' ); // phpcs:ignore
    }

    /**
     * Check if current user is authorized to manage the plugin on the backend.
     *
     * @since 1.0.0
     * @access public
     *
     * @param WP_User $user WP_User object.
     * @return boolean True if authorized, False otherwise.
     */
    public function current_user_authorized( $user = null ) {
        // Array of roles allowed to access/utilize the plugin.
        $admin_roles = apply_filters( 'ucfw_admin_roles', array( 'administrator' ) );

        if ( is_null( $user ) ) {
            $user = wp_get_current_user();
        }

        if ( $user->ID ) {
            return count( array_intersect( (array) $user->roles, $admin_roles ) ) ? true : false;
        } else {
            return false;
        }
    }

    /**
     * Get language for JS App
     *
     * @since 1.4
     * @access public
     *
     * @return string Language key.
     */
    public function get_app_language() {
        $site_lang = apply_filters( 'lpfw_app_language', get_option( 'WPLANG', 'en' ) );
        return 'en' === $site_lang ? 'en_US' : $site_lang;
    }

    /**
     * Get all user roles.
     *
     * @since 1.0.0
     * @access public
     *
     * @global WP_Roles $wp_roles Core class used to implement a user roles API.
     *
     * @return array Array of all site registered user roles. User role key as the key and value is user role text.
     */
    public function get_all_user_roles() {
        global $wp_roles;
        return $wp_roles->get_names();
    }

    /**
     * Check validity of a save post action.
     *
     * @since 1.0.0
     * @access private
     *
     * @param int    $post_id   Id of the coupon post.
     * @param string $post_type Post type to check.
     * @return bool True if valid save post action, False otherwise.
     */
    public function check_if_valid_save_post_action( $post_id, $post_type ) {
        if ( wp_is_post_autosave( $post_id )
            || wp_is_post_revision( $post_id )
            || ! current_user_can( 'edit_page', $post_id )
            || get_post_type() !== $post_type
            || empty( $_POST ) ) {  // phpcs:ignore
            return false;
        } else {
            return true;
        }
    }

    /**
     * Utility function that determines if a plugin is active or not.
     * Reference: https://developer.wordpress.org/reference/functions/is_plugin_active/
     *
     * @since 1.0.0
     * @access public
     *
     * @param string $plugin_basename Plugin base name. Ex. woocommerce/woocommerce.php.
     * @return boolean Returns true if active, false otherwise.
     */
    public function is_plugin_active( $plugin_basename ) {
        // Makes sure the plugin is defined before trying to use it.
        if ( ! function_exists( 'is_plugin_active' ) ) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        return is_plugin_active( $plugin_basename );
    }

    /**
     * Utility function that determines whether the plugin is active for the entire network.
     * Reference: https://developer.wordpress.org/reference/functions/is_plugin_active_for_network/
     *
     * @since 1.0.0
     * @access public
     *
     * @param string $plugin_basename Plugin base name. Ex. woocommerce/woocommerce.php.
     * @return boolean Returns true if active for the entire network, false otherwise.
     */
    public function is_plugin_active_for_network( $plugin_basename ) {
        // Makes sure the function is defined before trying to use it.
        if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
            require_once ABSPATH . '/wp-admin/includes/plugin.php';
        }

        return is_plugin_active_for_network( $plugin_basename );
    }

    /**
     * Check if REST API request is valid.
     *
     * @deprecated 1.8.4
     *
     * @since 1.0
     * @access public
     *
     * @param \WP_REST_Request $request Full details about the request.
     * @return bool|WP_Error True if the request has read access for the item, WP_Error object otherwise.
     */
    public function check_if_valid_api_request( \WP_REST_Request $request ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
        wc_deprecated_function( __METHOD__, '1.8.4' );
        return true;
    }

    /**
     * This function is an alias for WP get_option(), but will return the default value if option value is empty or invalid.
     *
     * @since 1.0
     * @access public
     *
     * @param string $option_name   Name of the option of value to fetch.
     * @param mixed  $default_value Defaut option value.
     * @return mixed Option value.
     */
    public function get_option( $option_name, $default_value = '' ) {
        $option_type  = $default_value ? gettype( $default_value ) : null;
        $option_value = get_option( $option_name, $default_value );

        if ( ! $option_value ) {
            return $default_value;
        }

        if ( $option_type ) {

            switch ( $option_type ) {
                case 'integer':
                    $option_value = (int) $option_value;
                    break;

                case 'float':
                    $option_value = (float) $option_value;
                    break;

                case 'array':
                    $option_value = is_array( $option_value ) ? $option_value : array();
                    break;
            }
        }

        return $option_value;
    }

    /**
     * Get points name.
     *
     * @since 1.3
     * @access public
     *
     * @return string Points name.
     */
    public function get_points_name() {
        return $this->get_option( $this->_constants->POINTS_NAME, __( 'Points', 'loyalty-program-for-woocommerce' ) );
    }

    /**
     * Generate random string.
     *
     * @since 1.0
     * @access public
     *
     * @param int $length String length.
     * @return string Random string.
     */
    public function random_str( $length ) {
        $x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr( str_shuffle( str_repeat( $x, ceil( $length / strlen( $x ) ) ) ), 1, $length );
    }

    /**
     * Sanitize price string as float.
     *
     * @since 1.0
     * @access public
     *
     * @param string $price Price string.
     * @return float Sanitized price.
     */
    public function sanitize_price( $price ) {
        $thousand_sep = get_option( 'woocommerce_price_thousand_sep' );
        $decimal_sep  = get_option( 'woocommerce_price_decimal_sep' );

        if ( $thousand_sep ) {
            $price = str_replace( $thousand_sep, '', $price );
        }

        if ( $decimal_sep ) {
            $price = str_replace( $decimal_sep, '.', $price );
        }

        $price = str_replace( get_woocommerce_currency_symbol(), '', $price );

        return (float) $price;
    }

    /**
     * Get price with WWP/P support.
     *
     * @since 1.0
     * @since 1.6   Add include tax flag parameter. Add implementation for "Always use regular price" setting.
     * @since 1.6.3 When WWP is active, use normal product price when a product has no wholesale price available.
     * @access private
     *
     * @param \WC_Product $product     Product object.
     * @param bool        $include_tax Check if tax value should be included in price.
     * @return float Product price.
     */
    public function get_price( $product, $include_tax = false ) {
        $price = -1;

        // get wholesale price.
        if ( class_exists( 'WWP_Wholesale_Prices' ) && method_exists( 'WWP_Wholesale_Prices', 'get_product_wholesale_price_on_shop_v3' ) ) {

            $wwp_roles_obj = \WWP_Wholesale_Roles::getInstance();
            $wholesa_roles = $wwp_roles_obj->getUserWholesaleRole();

            if ( ! empty( $wholesa_roles ) ) {

                $wholesale_prices = \WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v3( $product->get_id(), $wholesa_roles );
                $price            = isset( $wholesale_prices['wholesale_price_raw'] ) && ! empty( $wholesale_prices['wholesale_price_raw'] ) ? (float) $wholesale_prices['wholesale_price_raw'] : $price;
            }
        }

        // if there's no wholesale price detected, then we get the normal price.
        if ( 0 > $price ) {
            $price = 'yes' === get_option( $this->_constants->ALWAYS_USE_REGULAR_PRICE ) ? $product->get_regular_price() : $product->get_price();
        }

        if ( $include_tax ) {
            return wc_get_price_including_tax(
                $product,
                array(
                    'qty'   => 1,
                    'price' => $price,
                )
            );
        } else {
            return wc_get_price_excluding_tax(
                $product,
                array(
                    'qty'   => 1,
                    'price' => $price,
                )
            );
        }
    }

    /**
     * Check if an ACFW module is active.
     *
     * @since 1.0
     * @access public
     *
     * @param string $module ACFW module slug.
     * @return bool True if enabled, false otherwise.
     */
    public function is_acfw_module( $module ) {
        if ( ! $this->is_plugin_active( $this->_constants->ACFWF_PLUGIN ) ) {
            return false;
        }

        return \ACFWF()->Helper_Functions->is_module( $module );
    }

    /**
     * Alternative of the wc_price function for API display.
     *
     * @since 1.0
     * @access public
     *
     * @param float $price Price in float.
     * @return string Sanitized price.
     */
    public function api_wc_price( $price ) {
        return html_entity_decode( wc_clean( wc_price( $price ) ) );
    }

    /**
     * Get customer display name.
     *
     * @since 1.0
     * @access public
     *
     * @param int|WC_Customer $cid Customer ID.
     * @return string Customer name.
     */
    public function get_customer_name( $cid ) {
        $customer = $cid instanceof \WC_Customer ? $cid : new \WC_Customer( $cid );

        // return empty string when customer is not valid.
        if ( ! $customer instanceof \WC_Customer ) {
            return '';
        }

        $customer_name = sprintf( '%s %s', $customer->get_first_name(), $customer->get_last_name() );

        // set customer name to email if user has no set first and last name.
        if ( ! trim( $customer_name ) ) {
            $customer_name = $this->get_customer_email( $customer );
        }

        return $customer_name;
    }

    /**
     * Get customer display email.
     *
     * @since 1.0
     * @access public
     *
     * @param int|WC_Customer $cid Customer ID.
     * @return string Customer email.
     */
    public function get_customer_email( $cid ) {
        $customer = $cid instanceof \WC_Customer ? $cid : new \WC_Customer( $cid );

        // return empty string when customer is not valid.
        if ( ! $customer instanceof \WC_Customer ) {
            return '';
        }

        return $customer->get_billing_email() ? $customer->get_billing_email() : $customer->get_email();
    }

    /**
     * Get all enabled points calc options.
     *
     * @since 1.1
     * @access public
     *
     * @return array String array of all enabled points calc options.
     */
    public function get_enabled_points_calc_options() {
        $raw = get_option(
            $this->_constants->POINTS_CALCULATION_OPTIONS,
            array(
                'discounts' => 'yes',
                'tax'       => 'yes',
            )
        );

        return is_array( $raw ) ? array_keys(
            array_filter(
                $raw,
                function ( $o ) {
                    return 'yes' === $o;
                }
            )
        ) : array();
    }

    /**
     * Get order frontend link.
     *
     * @since 1.4
     * @access public
     *
     * @param WC_Order $order Order object.
     * @return string Order view frontend URL
     */
    public function get_order_frontend_link( $order ) {
        $order = $order instanceof \WC_Order ? $order : \wc_get_order( $order );
        return $order instanceof \WC_Order ? $order->get_view_order_url() : '';
    }

    /**
     * Load templates in an overridable manner.
     *
     * @since 1.3
     * @access public
     *
     * @param string $template Template path.
     * @param array  $args     Options to pass to the template.
     * @param string $path     Default template path.
     */
    public function load_template( $template, $args = array(), $path = '' ) {
        $path = $path ? $path : $this->_constants->TEMPLATES_ROOT_PATH;
        wc_get_template( $template, $args, '', $path );
    }

    /**
     * Check if product is allowed to earn points based on it's meta.
     *
     * @since 1.6
     * @access public
     *
     * @param WC_Product $product Product object.
     * @return string 'yes' or 'no'.
     */
    public function is_product_allowed_to_earn_points( $product ) {
        $is_allowed = $product->get_meta( $this->_constants->PRODUCT_ALLOW_EARN_POINTS, true );
        return empty( $is_allowed ) ? 'yes' : $is_allowed;
    }

    /**
     * Check if the product categories that the provided product is under allows the product to earn points or not.
     * If at least one of the categories disallows it, then the product will not earn points.
     *
     * @since 1.6
     * @access public
     *
     * @param int $product_id Product ID.
     * @return string 'yes' or 'no'.
     */
    public function is_product_categories_allowed_to_earn_points( $product_id ) {
        $categories = get_the_terms( $product_id, 'product_cat' );

        if ( is_array( $categories ) && ! is_wp_error( $categories ) ) {
            foreach ( $categories as $category ) {
                $is_allowed = get_term_meta( $category->term_id, $this->_constants->PRODUCT_CAT_ALLOW_EARN_POINTS, true );
                $is_allowed = empty( $is_allowed ) ? 'yes' : $is_allowed;

                // return explicit false if one of the categories disallows earning points for product.
                if ( 'no' === $is_allowed ) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get custom ruleset for SVG html tag for wp_kses.
     *
     * @since 1.7
     * @access public
     *
     * @return array SVG Kses custom rules.
     */
    public function get_svg_kses_ruleset() {
        $kses_defaults = wp_kses_allowed_html( 'post' );
        $svg_args      = array(
            'svg'   => array(
                'class'           => true,
                'aria-hidden'     => true,
                'aria-labelledby' => true,
                'role'            => true,
                'xmlns'           => true,
                'width'           => true,
                'height'          => true,
                'viewbox'         => true, // <= Must be lower case!
            ),
            'g'     => array( 'fill' => true ),
            'title' => array( 'title' => true ),
            'path'  => array(
                'd'    => true,
                'fill' => true,
            ),
        );
        return array_merge( $kses_defaults, $svg_args );
    }

    /**
     * Get the activation URL for a plugin dependency.
     *
     * @since 1.8.2
     * @access public
     *
     * @param string $plugin_basename Plugin basename.
     * @return string Nonced plugin activation URL.
     */
    public function get_plugin_dependency_activation_url( $plugin_basename ) {
        $url_prefix = is_multisite() && $this->is_plugin_active_for_network( $this->_constants->PLUGIN_BASENAME ) ? network_admin_url() : admin_url();

        return wp_nonce_url(
            $url_prefix . 'plugins.php?action=activate&amp;plugin=' . $plugin_basename . '&amp;plugin_status=all&amp;s',
            'activate-plugin_' . $plugin_basename
        );
    }

    /**
     * Get the install or update URL for a given plugin dependency.
     *
     * @since 1.8.2
     * @access public
     *
     * @param string $plugin_key Plugin key for install or basename for update.
     * @param bool   $is_upgrade Flag if URL is for upgrade or not.
     * @return string Nonced plugin install URL.
     */
    public function get_plugin_dependency_install_url( $plugin_key, $is_upgrade = false ) {
        $url_prefix = is_multisite() ? network_admin_url() : admin_url();
        $action     = $is_upgrade ? 'upgrade-plugin' : 'install-plugin';

        return wp_nonce_url(
            $url_prefix . 'update.php?action=' . $action . '&amp;plugin=' . $plugin_key,
            $action . '_' . $plugin_key
        );
    }
}
