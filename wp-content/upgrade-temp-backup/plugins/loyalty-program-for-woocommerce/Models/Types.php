<?php
namespace LPFW\Models;

use LPFW\Abstracts\Abstract_Main_Plugin_Class;
use LPFW\Abstracts\Base_Model;
use LPFW\Helpers\Helper_Functions;
use LPFW\Helpers\Plugin_Constants;
use LPFW\Interfaces\Model_Interface;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Model that houses the logic of extending the coupon system of woocommerce.
 * It houses the logic of handling coupon url.
 * Public Model.
 *
 * @since 1.4
 */
class Types extends Base_Model implements Model_Interface {
    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
     */

    /**
     * Class constructor.
     *
     * @since 1.4
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
    | Earn source types
    |--------------------------------------------------------------------------
     */

    /**
     * Returns all point source types.
     * - ['info'] This will be used in WC_Email_Earned_Point_Notification to shows how customer can earn points.
     *
     * @since 1.4
     * @access public
     *
     * @param string $key Specific source type key.
     * @return array|bool List of source types data, specific source type data or false if specified source type doesn't exist.
     */
    public function get_point_earn_source_types( $key = '' ) {
        $point_settings = \LPFW()->API_Settings->get_point_amount_fields_and_options();
        $point_fields   = $point_settings['fields'];
        $sources        = array_merge(
            array(
                'buy_product'     => array(
                    'name'    => __( 'Purchasing products', 'loyalty-program-for-woocommerce' ),
                    'slug'    => 'buy_product',
                    'related' => array(
                        'object_type'         => 'order',
                        'admin_label'         => __( 'View Order', 'loyalty-program-for-woocommerce' ),
                        'label'               => __( 'View Order', 'loyalty-program-for-woocommerce' ),
                        'admin_link_callback' => 'get_edit_post_link',
                        'link_callback'       => array( \LPFW()->Helper_Functions, 'get_order_frontend_link' ),
                    ),
                    'info'    => sprintf(
                        /* Translators: %1$s Points to price ratio */
                        _n(
                            '%1$s Loyalty Point for every %2$s spent',
                            '%1$s Loyalty Points for every %2$s spent',
                            \LPFW()->Calculate->get_points_to_price_ratio(),
                            'loyalty-program-for-woocommerce'
                        ),
                        \LPFW()->Calculate->get_points_to_price_ratio(),
                        $this->_helper_functions->api_wc_price( 1 ),
                    ),
                ),
                'product_review'  => array(
                    'name'    => __( 'Leaving a product review', 'loyalty-program-for-woocommerce' ),
                    'slug'    => 'product_review',
                    'related' => array(
                        'object_type'         => 'comment',
                        'admin_label'         => __( 'View Review', 'loyalty-program-for-woocommerce' ),
                        'label'               => __( 'View Review', 'loyalty-program-for-woocommerce' ),
                        'admin_link_callback' => 'get_edit_comment_link',
                        'link_callback'       => 'get_comment_link',
                    ),
                    'info'    => sprintf(
						/* Translators: %1$s Points to earn */
                        _n(
                            '%1$s Loyalty Point',
                            '%1$s Loyalty Points',
                            $this->_helper_functions->get_option(
                                $point_fields['product_review']['id'],
                                $point_fields['product_review']['default'] ?? $point_fields['product_review']['min']
                            ),
                            'loyalty-program-for-woocommerce'
                        ),
                        $this->_helper_functions->get_option(
                            $point_fields['product_review']['id'],
                            $point_fields['product_review']['default'] ?? $point_fields['product_review']['min']
                        ),
                    ),
                ),
                'blog_comment'    => array(
                    'name'    => __( 'Leaving a blog comment', 'loyalty-program-for-woocommerce' ),
                    'slug'    => 'blog_comment',
                    'related' => array(
                        'object_type'         => 'comment',
                        'admin_label'         => __( 'View Comment', 'loyalty-program-for-woocommerce' ),
                        'label'               => __( 'View Comment', 'loyalty-program-for-woocommerce' ),
                        'admin_link_callback' => 'get_edit_comment_link',
                        'link_callback'       => 'get_comment_link',
                    ),
                    'info'    => sprintf(
						/* Translators: %1$s Points to earn */
                        _n(
                            '%1$s Loyalty Point',
                            '%1$s Loyalty Points',
                            $this->_helper_functions->get_option(
                                $point_fields['blog_comment']['id'],
                                $point_fields['blog_comment']['default'] ?? $point_fields['blog_comment']['min']
                            ),
                            'loyalty-program-for-woocommerce'
                        ),
                        $this->_helper_functions->get_option(
                            $point_fields['blog_comment']['id'],
                            $point_fields['blog_comment']['default'] ?? $point_fields['blog_comment']['min']
                        ),
                    ),
                ),
                'user_register'   => array(
                    'name'    => __( 'Registering as a user/customer', 'loyalty-program-for-woocommerce' ),
                    'slug'    => 'user_register',
                    'related' => array(
                        'object_type'         => 'user',
                        'admin_label'         => __( 'View User', 'loyalty-program-for-woocommerce' ),
                        'label'               => '—',
                        'admin_link_callback' => 'get_edit_user_link',
                    ),
                    'info'    => sprintf(
						/* Translators: %1$s Points to earn */
                        _n(
                            '%1$s Loyalty Point',
                            '%1$s Loyalty Points',
                            $this->_helper_functions->get_option(
                                $point_fields['user_register']['id'],
                                $point_fields['user_register']['default'] ?? $point_fields['user_register']['min']
                            ),
                            'loyalty-program-for-woocommerce'
                        ),
                        $this->_helper_functions->get_option(
                            $point_fields['user_register']['id'],
                            $point_fields['user_register']['default'] ?? $point_fields['user_register']['min']
                        ),
                    ),
                ),
                'first_order'     => array(
                    'name'    => __( 'After completing first order', 'loyalty-program-for-woocommerce' ),
                    'slug'    => 'first_order',
                    'related' => array(
                        'object_type'         => 'order',
                        'admin_label'         => __( 'View Order', 'loyalty-program-for-woocommerce' ),
                        'label'               => __( 'View Order', 'loyalty-program-for-woocommerce' ),
                        'admin_link_callback' => 'get_edit_post_link',
                        'link_callback'       => array( \LPFW()->Helper_Functions, 'get_order_frontend_link' ),
                    ),
                    'info'    => sprintf(
						/* Translators: %1$s Points to earn */
                        _n(
                            '%1$s Loyalty Point',
                            '%1$s Loyalty Points',
                            $this->_helper_functions->get_option(
                                $point_fields['first_order']['id'],
                                $point_fields['first_order']['default'] ?? $point_fields['first_order']['min']
                            ),
                            'loyalty-program-for-woocommerce'
                        ),
                        $this->_helper_functions->get_option(
                            $point_fields['first_order']['id'],
                            $point_fields['first_order']['default'] ?? $point_fields['first_order']['min']
                        ),
                    ),
                ),
                'high_spend'      => array(
                    'name'    => __( 'Spending over a certain amount', 'loyalty-program-for-woocommerce' ),
                    'slug'    => 'high_spend',
                    'related' => array(
                        'object_type'         => 'order',
                        'admin_label'         => __( 'View Order', 'loyalty-program-for-woocommerce' ),
                        'label'               => __( 'View Order', 'loyalty-program-for-woocommerce' ),
                        'admin_link_callback' => 'get_edit_post_link',
                        'link_callback'       => array( \LPFW()->Helper_Functions, 'get_order_frontend_link' ),
                    ),
                    'info'    => $this->_get_point_source_type_high_spend_info(
                        $this->_helper_functions->get_option(
                            $point_fields['high_spend']['id'],
                            $point_fields['high_spend']['default']
                        )
                    ),
                ),
                'within_period'   => array(
                    'name'    => __( 'Extra points during a period', 'loyalty-program-for-woocommerce' ),
                    'slug'    => 'within_period',
                    'related' => array(
                        'object_type'         => 'order',
                        'admin_label'         => __( 'View Order', 'loyalty-program-for-woocommerce' ),
                        'label'               => __( 'View Order', 'loyalty-program-for-woocommerce' ),
                        'admin_link_callback' => 'get_edit_post_link',
                        'link_callback'       => array( \LPFW()->Helper_Functions, 'get_order_frontend_link' ),
                    ),
                    'info'    => $this->_get_point_source_type_within_period_info(
                        $this->_helper_functions->get_option(
                            $point_fields['within_period']['id'],
                            $point_fields['within_period']['default']
                        )
                    ),
                ),
                'admin_increase'  => array(
                    'name'    => __( 'Admin Adjustment (increase)', 'loyalty-program-for-woocommerce' ),
                    'slug'    => 'admin_increase',
                    'related' => array(
                        'object_type'         => 'user',
                        /* Translators: %s: Admin user's name. */
                        'admin_label'         => __( 'Admin: %s', 'loyalty-program-for-woocommerce' ),
                        'label'               => '—',
                        'admin_link_callback' => 'get_edit_user_link',
                    ),
                ),
                'imported_points' => array(
                    'name'    => __( 'Imported points', 'loyalty-program-for-woocommerce' ),
                    'slug'    => 'imported_points',
                    'related' => array(
                        'object_type' => 'user_import',
                        'admin_label' => '-',
                        'label'       => '-',
                    ),
                ),
            ),
            apply_filters( 'lpfw_get_point_earn_source_types', array() )
        );

        if ( $key ) {
            return isset( $sources[ $key ] ) ? (object) $sources[ $key ] : false; // force convert multidimension array to object.
        }

        return $sources;
    }

    /*
    |--------------------------------------------------------------------------
    | Redeem data types
    |--------------------------------------------------------------------------
     */

    /**
     * Get redeem action types constants.
     *
     * @since 1.4
     * @since 1.8 Add store credits redeem action type.
     * @access public
     *
     * @param string $key Specific source type key.
     * @return array|bool List of redeem action types, specific redeem action type or false if specified redeem action type doesn't exist.
     */
    public function get_point_redeem_action_types( $key = '' ) {
        $actions = array(
            'coupon'         => array(
                'name'    => __( 'Redeem Coupon', 'loyalty-program-for-woocommerce' ),
                'slug'    => 'coupon',
                'related' => array(
                    'object_type'         => 'coupon',
                    'admin_label'         => __( 'View Coupon', 'loyalty-program-for-woocommerce' ),
                    /* Translators: %s: Coupon code. */
                    'label'               => __( 'Coupon: %s', 'loyalty-program-for-woocommerce' ),
                    'admin_link_callback' => 'get_edit_post_link',
                ),
            ),
            'expire'         => array(
                'name' => __( 'Points expired', 'loyalty-program-for-woocommerce' ),
                'slug' => 'expire',
            ),
            'admin_decrease' => array(
                'name'    => __( 'Admin Adjustment (decrease)', 'loyalty-program-for-woocommerce' ),
                'slug'    => 'admin_decrease',
                'related' => array(
                    'object_type'         => 'user',
                    /* Translators: %s: Admin user's name. */
                    'admin_label'         => __( 'Admin: %s', 'loyalty-program-for-woocommerce' ),
                    'label'               => '—',
                    'admin_link_callback' => 'get_edit_user_link',
                ),
            ),
            'revoke'         => array(
                'name'    => __( 'Points revoked', 'loyalty-program-for-woocommerce' ),
                'slug'    => 'revoke',
                'related' => array(
                    'object_type'         => 'order',
                    'admin_label'         => __( 'View Order', 'loyalty-program-for-woocommerce' ),
                    'label'               => __( 'View Order', 'loyalty-program-for-woocommerce' ),
                    'admin_link_callback' => 'get_edit_post_link',
                    'link_callback'       => array( \LPFW()->Helper_Functions, 'get_order_frontend_link' ),
                ),
            ),
            'store_credits'  => array(
                'name'    => __( 'Redeem Store Credits', 'loyalty-program-for-woocommerce' ),
                'slug'    => 'store_credits',
                'related' => array(
                    'object_type' => 'store_credit_entry',
                    'admin_label' => '—',
                    'label'       => '—',
                ),
            ),
        );

        if ( $key ) {
            return isset( $actions[ $key ] ) ? (object) $actions[ $key ] : false; // force convert multidimension array to object.
        }

        return $actions;
    }

    /*
    |--------------------------------------------------------------------------
    | Utility Methods
    |--------------------------------------------------------------------------
     */

    /**
     * Get points data activity label.
     *
     * @since 1.2
     * @access public
     *
     * @param string|object $registry Activity key or data.
     * @param string        $type     Points type.
     * @return string Activity label.
     */
    public function get_activity_label( $registry, $type = 'earn' ) {
        // if registry provided is a string, then we try to get the registry.
        if ( ! is_object( $registry ) ) {
            $registries = array_merge( $this->get_point_earn_source_types(), $this->get_point_redeem_action_types() );
            $registry   = isset( $registries[ $registry ] ) ? $registries[ $registry ] : null;
        }

        // if registry is not valid return empty string.
        if ( ! $registry ) {
            return '';
        }

        $activity_label = $registry->name;

        if ( 'pending_earn' === $type ) {
            $activity_label .= ' ' . __( '(pending)', 'loyalty-program-for-woocommerce' );
        }

        return $activity_label;
    }

    /**
     * Register loyalty points store credit increase type.
     *
     * @since 1.8
     * @access public
     *
     * @param array $types Store credit increase source types.
     * @return array Filtered store credit increase source types.
     */
    public function register_loyalty_points_store_credit_increase_type( $types ) {

        $types['loyalty_points'] = array(
            'name'    => __( 'Loyalty points', 'loyalty-program-for-woocommerce' ),
            'slug'    => 'loyalty_points',
            'related' => array(
                'object_type' => 'loyalty_point_entry',
                'admin_label' => '—',
                'label'       => '—',
            ),
        );

        return $types;
    }

    /**
     * Get point source type high spend info.
     *
     * @since 1.8.4
     *
     * @param array $actions High spend actions option.
     *
     * @return array High spend info.
     */
    private function _get_point_source_type_high_spend_info( $actions ) {
        $info = array();
        foreach ( $actions as $action ) {
            $info[] = sprintf(
                /* Translators: %1$s Points to earn */
                _n(
                    '%1$s Loyalty Point for every $%2$s spent',
                    '%1$s Loyalty Points for every $%2$s spent',
                    absint( $action['points'] ),
                    'loyalty-program-for-woocommerce'
                ),
                absint( $action['points'] ),
                absint( $action['sanitized'] ),
            );
        }
        return $info;
    }

    /**
     * Get point source type high spend info.
     *
     * @since 1.8.4
     *
     * @param array $actions Within period actions option.
     *
     * @return array Within period info.
     */
    private function _get_point_source_type_within_period_info( $actions ) {
        $info = array();
        foreach ( $actions as $action ) {
            $info[] = sprintf(
                /* Translators: %1$s Points to earn */
                _n(
                    '%1$s Loyalty Point during %2$s - %3$s',
                    '%1$s Loyalty Points during %2$s - %3$s',
                    absint( $action['points'] ),
                    'loyalty-program-for-woocommerce'
                ),
                absint( $action['points'] ),
                $this->_helper_functions->convert_datetime_to_site_standard_format( $action['sdate'] . ' ' . $action['stime'] ),
                $this->_helper_functions->convert_datetime_to_site_standard_format( $action['edate'] . ' ' . $action['etime'] ),
            );
        }
        return $info;
    }

    /*
    |--------------------------------------------------------------------------
    | Fulfill implemented interface contracts
    |--------------------------------------------------------------------------
     */

    /**
     * Execute Types class.
     *
     * @since 1.4
     * @access public
     * @inherit LPFW\Interfaces\Model_Interface
     */
    public function run() {
        add_filter( 'acfw_get_store_credits_increase_source_types', array( $this, 'register_loyalty_points_store_credit_increase_type' ) );
    }
}
