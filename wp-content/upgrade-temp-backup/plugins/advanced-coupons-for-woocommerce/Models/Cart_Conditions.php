<?php
namespace ACFWP\Models;

use ACFWP\Abstracts\Abstract_Main_Plugin_Class;
use ACFWP\Abstracts\Base_Model;
use ACFWP\Helpers\Helper_Functions;
use ACFWP\Helpers\Plugin_Constants;
use ACFWP\Interfaces\Initiable_Interface;
use ACFWP\Interfaces\Model_Interface;
use ACFWP\Models\Objects\Advanced_Coupon;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Model that houses the logic Cart Conditions premium module.
 * Public Model.
 *
 * @since 2.0
 */
class Cart_Conditions extends Base_Model implements Model_Interface, Initiable_Interface {
    /*
    |--------------------------------------------------------------------------
    | Class Properties
    |--------------------------------------------------------------------------
     */

    /**
     * Propert that houses all premium field options.
     *
     * @since 2.0
     * @access private
     * @var array
     */
    private $_premium_field_options;

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

        // Upsell : Premium Field Options.
        $this->_premium_field_options = array(
            'product-quantity'                          => __( 'Product Quantity In The Cart', 'advanced-coupons-for-woocommerce' ),
            'cart-weight'                               => __( 'Cart Weight', 'advanced-coupons-for-woocommerce' ),
            'custom-taxonomy'                           => __( 'Custom Taxonomy Exist In The Cart', 'advanced-coupons-for-woocommerce' ),
            'customer-registration-date'                => __( 'Within Hours After Customer Registered', 'advanced-coupons-for-woocommerce' ),
            'customer-last-ordered'                     => __( 'Within Hours After Customer Last Order', 'advanced-coupons-for-woocommerce' ),
            'custom-user-meta'                          => __( 'Custom User Meta', 'advanced-coupons-for-woocommerce' ),
            'custom-cart-item-meta'                     => __( 'Custom Cart Item Meta', 'advanced-coupons-for-woocommerce' ),
            'total-customer-spend'                      => __( 'Total Customer Spend', 'advanced-coupons-for-woocommerce' ),
            'product-stock-availability-exists-in-cart' => __( 'Product Stock Availability Exists In Cart', 'advanced-coupons-for-woocommerce' ),
            'has-ordered-before'                        => __( 'Has Ordered Before', 'advanced-coupons-for-woocommerce' ),
            'has-ordered-product-categories-before'     => __( 'Has Ordered Product Categories Before', 'advanced-coupons-for-woocommerce' ),
            'total-customer-spend-on-product-category'  => __( 'Total Customer Spend on a certain product category', 'advanced-coupons-for-woocommerce' ),
            'shipping-zone-region'                      => __( 'Shipping Zone And Region', 'advanced-coupons-for-woocommerce' ),
        );

        $main_plugin->add_to_all_plugin_models( $this );
        $main_plugin->add_to_public_models( $this );
    }

    /*
    |--------------------------------------------------------------------------
    | Implementation
    |--------------------------------------------------------------------------
     */

    /**
     * Get condition field value.
     *
     * @since 2.0
     * @access public
     *
     * @param mixed  $value           Condition field value.
     * @param array  $condition_field Condition field.
     * @param string $field_method    Condition field method name.
     * @return mixed Filtered condition field value.
     */
    public function get_condition_field_value( $value, $condition_field, $field_method ) {
        // don't proceed if method doesn't exist or when not running this on normal cart/checkout environment.
        if ( ! method_exists( $this, $field_method ) || ! \WC()->cart ) {
            return $value;
        }

        return $this->$field_method( $condition_field['data'] );
    }

    /**
     * Cart condition premium field options.
     *
     * @since 2.0
     * @access public
     *
     * @param array $options Field options list.
     * @return array Filtered field options list.
     */
    public function cart_condition_premium_field_options( $options ) {
        return array_merge( $options, $this->_premium_field_options );
    }

    /**
     * Sanitized cart condition field data.
     *
     * @since 2.0
     * @access public
     *
     * @param mixed  $data            Condition field data.
     * @param array  $condition_field Condition field.
     * @param string $type           Condition field type.
     * @return mixed Filtered condition field data.
     */
    public function sanitize_cart_condition_field( $data, $condition_field, $type ) {

        switch ( $type ) {

            case 'product-quantity':
                $data = array();
                if ( isset( $condition_field['data'] ) && is_array( $condition_field['data'] ) ) {
                    foreach ( $condition_field['data'] as $product ) {
                        $data[] = array(
                            'product_id'      => intval( $product['product_id'] ),
                            'condition'       => \ACFWF()->Helper_Functions->sanitize_condition_select_value( $product['condition'], '=' ),
                            'quantity'        => intval( $product['quantity'] ),
                            'product_label'   => sanitize_text_field( $product['product_label'] ),
                            'condition_label' => sanitize_text_field( $product['condition_label'] ),
                        );
                    }
                }
                break;

            case 'cart-weight':
                $data = array(
                    'condition' => isset( $condition_field['data']['condition'] ) ? \ACFWF()->Helper_Functions->sanitize_condition_select_value( $condition_field['data']['condition'], '=' ) : '=',
                    'value'     => isset( $condition_field['data']['value'] ) ? wc_format_decimal( $condition_field['data']['value'] ) : '',
                );
                break;

            case 'custom-taxonomy':
                $data = array(
                    'condition'    => isset( $condition_field['data']['condition'] ) ? sanitize_text_field( $condition_field['data']['condition'] ) : '',
                    'value'        => isset( $condition_field['data']['value'] ) ? array_map( 'absint', $condition_field['data']['value'] ) : array(),
                    'qtyCondition' => isset( $condition_field['data']['qtyCondition'] ) ? sanitize_text_field( $condition_field['data']['qtyCondition'] ) : '',
                    'quantity'     => isset( $condition_field['data']['quantity'] ) ? intval( $condition_field['data']['quantity'] ) : 0,
                );
                break;

            case 'product-stock-availability-exists-in-cart':
                $data = array(
                    'products' => array(),
                );
                if ( isset( $condition_field['data']['products'] ) && is_array( $condition_field['data']['products'] ) ) {
                    foreach ( $condition_field['data']['products'] as $product ) {
                        $product['product']['id']      = intval( $product['product']['id'] );
                        $product['product']['label']   = sanitize_text_field( $product['product']['label'] );
                        $product['condition']['id']    = sanitize_text_field( $product['condition']['id'] );
                        $product['condition']['label'] = sanitize_text_field( $product['condition']['label'] );
                        $data['products'][]            = $product;
                    }
                }
                break;

            case 'has-ordered-before':
                $data = array(
                    'condition' => sanitize_text_field( $condition_field['data']['condition'] ),
                    'value'     => sanitize_text_field( $condition_field['data']['value'] ),
                    'products'  => array(),
                );
                if ( isset( $condition_field['data']['products'] ) && is_array( $condition_field['data']['products'] ) ) {
                    foreach ( $condition_field['data']['products'] as $product ) {
                        $data['products'][] = array(
                            'product_id'      => intval( $product['product_id'] ),
                            'condition'       => \ACFWF()->Helper_Functions->sanitize_condition_select_value( $product['condition'], '=' ),
                            'quantity'        => intval( $product['quantity'] ),
                            'product_label'   => sanitize_text_field( $product['product_label'] ),
                            'condition_label' => sanitize_text_field( $product['condition_label'] ),
                        );
                    }
                }
                break;

            case 'has-ordered-product-categories-before':
                $data = array(
                    'categories' => array(),
                    'condition'  => sanitize_text_field( $condition_field['data']['condition'] ),
                    'quantity'   => isset( $condition_field['data']['quantity'] ) ? intval( $condition_field['data']['quantity'] ) : 0,
                    'type'       => array(
                        'condition' => sanitize_text_field( $condition_field['data']['type']['condition'] ),
                        'label'     => sanitize_text_field( $condition_field['data']['type']['label'] ),
                        'value'     => sanitize_text_field( $condition_field['data']['type']['value'] ),
                    ),
                );

                if (
                    isset( $condition_field['data']['categories'] ) &&
                    is_array( $condition_field['data']['categories'] )
                ) {
                    foreach ( $condition_field['data']['categories'] as $category ) {
                        $data['categories'][] = intval( $category );
                    }
                }
                break;

            case 'total-customer-spend-on-product-category':
                $data = array(
                    'categories' => array(),
                    'condition'  => sanitize_text_field( $condition_field['data']['condition'] ),
                    'spend'      => isset( $condition_field['data']['spend'] ) ? wc_format_decimal( $condition_field['data']['spend'] ) : 0,
                    'type'       => array(
                        'condition' => sanitize_text_field( $condition_field['data']['type']['condition'] ),
                        'label'     => sanitize_text_field( $condition_field['data']['type']['label'] ),
                        'value'     => sanitize_text_field( $condition_field['data']['type']['value'] ),
                    ),
                );

                if (
                    isset( $condition_field['data']['categories'] ) &&
                    is_array( $condition_field['data']['categories'] )
                ) {
                    foreach ( $condition_field['data']['categories'] as $category ) {
                        $data['categories'][] = intval( $category );
                    }
                }
                break;

            case 'customer-registration-date':
                $data = $condition_field['data'] ? intval( $condition_field['data'] ) : 1;
                break;

            case 'customer-last-ordered':
                $data = intval( $condition_field['data'] );
                break;

            case 'custom-user-meta':
            case 'custom-cart-item-meta':
                $type  = isset( $condition_field['data']['type'] ) ? sanitize_text_field( $condition_field['data']['type'] ) : '';
                $value = isset( $condition_field['data']['value'] ) ? $condition_field['data']['value'] : '';
                $data  = array(
                    'condition' => isset( $condition_field['data']['condition'] ) ? \ACFWF()->Helper_Functions->sanitize_condition_select_value( $condition_field['data']['condition'], '=' ) : '=',
                    'key'       => isset( $condition_field['data']['key'] ) ? sanitize_text_field( $condition_field['data']['key'] ) : '',
                    'value'     => $this->_sanitize_custom_meta_value( $value, $type ),
                    'type'      => $type,
                );
                break;

            case 'shipping-zone-region':
                $data = array(
                    'condition' => isset( $condition_field['data']['condition'] ) ? intval( $condition_field['data']['condition'] ) : '',
                    'value'     => isset( $condition_field['data']['value'] ) ? array_map( 'sanitize_text_field', $condition_field['data']['value'] ) : '',
                );
                break;

            case 'total-customer-spend':
                $data = array(
                    'condition' => isset( $condition_field['data']['condition'] ) ? \ACFWF()->Helper_Functions->sanitize_condition_select_value( $condition_field['data']['condition'], '=' ) : '=',
                    'value'     => isset( $condition_field['data']['value'] ) ? (float) wc_format_decimal( $condition_field['data']['value'] ) : '',
                    'offset'    => isset( $condition_field['data']['offset'] ) ? intval( $condition_field['data']['offset'] ) : '',
                );
                break;

            case 'number-of-orders':
                $data = array(
                    'condition' => isset( $condition_field['data']['condition'] ) ? \ACFWF()->Helper_Functions->sanitize_condition_select_value( $condition_field['data']['condition'], '=' ) : '=',
                    'value'     => isset( $condition_field['data']['value'] ) ? intval( $condition_field['data']['value'] ) : '',
                    'offset'    => isset( $condition_field['data']['offset'] ) ? intval( $condition_field['data']['offset'] ) : '',
                );
        }

        return $data;
    }

    /**
     * Sanitize custom meta value for saving.
     *
     * @since 2.4.1
     * @access private
     *
     * @param mixed  $value Meta value.
     * @param string $type  Meta type.
     * @return mixed Sanitized meta value.
     */
    private function _sanitize_custom_meta_value( $value, $type ) {
        switch ( $type ) {
            case 'number':
                return intval( $value );
            case 'price':
                return wc_format_decimal( $value );
        }

        return sanitize_text_field( $value );
    }

    /**
     * Format cart conditions data for editing context.
     *
     * @since 2.4.1
     * @access public
     *
     * @param array           $field  Field data.
     * @param Advanced_Coupon $coupon Advanced coupon object (ACFWF).
     * @return array Formatted field data.
     */
    public function format_cart_conditions_for_edit( $field, $coupon ) {
        switch ( $field['type'] ) {

            case 'total-customer-spend':
                $field['data']['value'] = wc_format_localized_price( $field['data']['value'] );
                break;

            case 'total-customer-spend-on-product-category':
                $field['data']['type']['value'] = wc_format_localized_price( $field['data']['type']['value'] );
                break;

            case 'custom-user-meta':
            case 'custom-cart-item-meta':
                if ( 'price' === $field['data']['type'] ) {
                    $field['data']['value'] = wc_format_localized_price( $field['data']['value'] );
                }

                break;
        }

        return $field;
    }

    /**
     * Cart condition panel data attributes.
     *
     * @since 2.0
     * @access public
     *
     * @param array $data_atts Panel data attributes.
     * @return array Filtered panel data attributes.
     */
    public function cart_conditions_panel_data_atts( $data_atts ) {
        $data_atts['custom_tax_options'] = $this->get_custom_taxonomy_options();
        $data_atts['shipping_regions']   = $this->get_all_shipping_region_options();

        return $data_atts;
    }

    /**
     * Premium condition fields localized data.
     *
     * @since 2.0
     * @access public
     *
     * @param array $cart_condition_data Localized data.
     * @return array Filtered localized data.
     */
    public function condition_fields_localized_data( $cart_condition_data ) {
        $tax_options = get_taxonomies( array( 'object_type' => array( 'product' ) ), 'objects' );
        unset( $tax_options['product_cat'] );

        if ( isset( $tax_options['product_type'] ) ) {
            $tax_options['product_type']->label = __( 'Product Type', 'advanced-coupons-for-woocommerce' );
        }

        $premium_fields = array(
            'product_quantity'                          => array(
                'group'         => 'products',
                'key'           => 'product-quantity',
                'title'         => __( 'Product Quantities Exists In Cart', 'advanced-coupons-for-woocommerce' ),
                'product_col'   => __( 'Product', 'advanced-coupons-for-woocommerce' ),
                'condition_col' => __( 'Condition', 'advanced-coupons-for-woocommerce' ),
                'quantity_col'  => __( 'Quantity', 'advanced-coupons-for-woocommerce' ),
            ),
            'cart_weight'                               => array(
                'group' => 'cart-items',
                'key'   => 'cart-weight',
                'title' => __( 'Cart Weight', 'advanced-coupons-for-woocommerce' ),
                'desc'  => __( 'Total weight of cart items', 'advanced-coupons-for-woocommerce' ),
                'field' => sprintf(
                    /* translators: %s: Weight unit */
                    __( 'Cart Weight (%s)', 'advanced-coupons-for-woocommerce' ),
                    $this->_helper_functions->get_weight_unit_label( get_option( 'woocommerce_weight_unit', 'kg' ) )
                ),
            ),
            'customer_registration_date'                => array(
                'group' => 'customers',
                'key'   => 'customer-registration-date',
                'title' => __( 'Within Hours After Customer Registered', 'advanced-coupons-for-woocommerce' ),
                'desc'  => __( 'Allow usage of this coupon within hours after customer was registered', 'advanced-coupons-for-woocommerce' ),
                'hours' => __( 'Hours', 'advanced-coupons-for-woocommerce' ),
            ),
            'customer_last_ordered'                     => array(
                'group' => 'customers',
                'key'   => 'customer-last-ordered',
                'title' => __( 'Within Hours After Customer Last Order', 'advanced-coupons-for-woocommerce' ),
                'desc'  => __( 'Allow usage of this coupon within hours after customer has ordered', 'advanced-coupons-for-woocommerce' ),
                'hours' => __( 'Hours', 'advanced-coupons-for-woocommerce' ),
            ),
            'total_customer_spend'                      => array(
                'group'       => 'customers',
                'key'         => 'total-customer-spend',
                'title'       => __( 'Total Customer Spend', 'advanced-coupons-for-woocommerce' ),
                'desc'        => __( 'Total amount customer spent for the last x number of days. Setting offset to 0 will get overall customer total spend.', 'advanced-coupons-for-woocommerce' ),
                'total_spend' => __( 'Total Spend', 'advanced-coupons-for-woocommerce' ),
                'days_offset' => __( 'Days offset', 'advanced-coupons-for-woocommerce' ),
            ),
            'product_stock_availability_exists_in_cart' => array(
                'group'    => 'products',
                'key'      => 'product-stock-availability-exists-in-cart',
                'title'    => __( 'Product Stock Availability Exists In Cart', 'advanced-coupons-for-woocommerce' ),
                'products' => array(
                    'exists'  => __( 'Product has already been added to the table', 'advanced-coupons-for-woocommerce' ),
                    'options' => array(
                        'conditions' => array(
                            'instock'     => __( 'In Stock', 'advanced-coupons-for-woocommerce' ),
                            'outofstock'  => __( 'Out of Stock', 'advanced-coupons-for-woocommerce' ),
                            'onbackorder' => __( 'On Backorder', 'advanced-coupons-for-woocommerce' ),
                        ),
                    ),
                    'label'   => array(
                        'columns' => array(
                            'product'   => __( 'Product', 'advanced-coupons-for-woocommerce' ),
                            'condition' => __( 'Condition', 'advanced-coupons-for-woocommerce' ),
                            'quantity'  => __( 'Quantity', 'advanced-coupons-for-woocommerce' ),
                        ),
                    ),
                ),
            ),

            'has_ordered_before'                        => array(
                'group'            => 'products',
                'key'              => 'has-ordered-before',
                'title'            => __( 'Customer Has Ordered Products Before', 'advanced-coupons-for-woocommerce' ),
                'type'             => __( 'Type', 'advanced-coupons-for-woocommerce' ),
                'within_a_period'  => __( 'Within a period', 'advanced-coupons-for-woocommerce' ),
                'number_of_orders' => __( 'Number of orders', 'advanced-coupons-for-woocommerce' ),
                'num_prev_days'    => __( 'No. of previous days', 'advanced-coupons-for-woocommerce' ),
            ),
            'shipping_zone_region'                      => array(
                'group'               => 'customers',
                'key'                 => 'shipping-zone-region',
                'title'               => __( 'Shipping Zone And Region', 'advanced-coupons-for-woocommerce' ),
                'zone_label'          => __( 'Shipping Zone', 'advanced-coupons-for-woocommerce' ),
                'zone_placeholder'    => __( 'Select shipping zone', 'advanced-coupons-for-woocommerce' ),
                'regions_label'       => __( 'Zone Region(s)', 'advanced-coupons-for-woocommerce' ),
                'regions_placeholder' => __( 'Select zone region(s)', 'advanced-coupons-for-woocommerce' ),
                'zone_options'        => $this->get_shipping_zones_options(),
            ),
            'custom_taxonomy'                           => array(
                'group'           => 'product-categories',
                'key'             => 'custom-taxonomy',
                'title'           => __( 'Custom Taxonomy Exists In Cart', 'advanced-coupons-for-woocommerce' ),
                'select_taxonomy' => __( 'Select taxonomy', 'advanced-coupons-for-woocommerce' ),
                'product_type'    => __( 'Product Type', 'advanced-coupons-for-woocommerce' ),
                'select_terms'    => __( 'Select terms', 'advanced-coupons-for-woocommerce' ),
                'tax_options'     => array_values( $tax_options ),
            ),
            'has_ordered_product_categories_before'     => array(
                'group'            => 'product-categories',
                'key'              => 'has-ordered-product-categories-before',
                'title'            => __( 'Customer Has Ordered Product Categories Before', 'advanced-coupons-for-woocommerce' ),
                'type'             => __( 'Type', 'advanced-coupons-for-woocommerce' ),
                'within_a_period'  => __( 'Within a period', 'advanced-coupons-for-woocommerce' ),
                'number_of_orders' => __( 'Number of orders', 'advanced-coupons-for-woocommerce' ),
                'num_prev_days'    => __( 'No. of previous days', 'advanced-coupons-for-woocommerce' ),
                'categories'       => array(
                    'options' => \ACFWF()->Cart_Conditions->get_product_category_options(),
                    'exists'  => __( 'Product Category has already been added to the table', 'advanced-coupons-for-woocommerce' ),
                    'label'   => array(
                        'buttons' => array(
                            'add' => __( 'Add Category', 'advanced-coupons-for-woocommerce' ),
                        ),
                        'columns' => array(
                            'category'  => __( 'Category', 'advanced-coupons-for-woocommerce' ),
                            'condition' => __( 'Condition', 'advanced-coupons-for-woocommerce' ),
                            'quantity'  => __( 'Quantity', 'advanced-coupons-for-woocommerce' ),
                        ),
                    ),
                ),
            ),
            'total_customer_spend_on_product_category'  => array(
                'group'            => 'product-categories',
                'key'              => 'total-customer-spend-on-product-category',
                'title'            => __( 'Total Customer Spend on a certain product category', 'advanced-coupons-for-woocommerce' ),
                'type'             => __( 'Type', 'advanced-coupons-for-woocommerce' ),
                'within_a_period'  => __( 'Within a period', 'advanced-coupons-for-woocommerce' ),
                'number_of_orders' => __( 'Number of orders', 'advanced-coupons-for-woocommerce' ),
                'num_prev_days'    => __( 'No. of previous days', 'advanced-coupons-for-woocommerce' ),
                'categories'       => array(
                    'options' => \ACFWF()->Cart_Conditions->get_product_category_options(),
                    'exists'  => __( 'Product Category has already been added to the table', 'advanced-coupons-for-woocommerce' ),
                    'label'   => array(
                        'buttons' => array(
                            'add' => __( 'Add Category', 'advanced-coupons-for-woocommerce' ),
                        ),
                        'columns' => array(
                            'category'  => __( 'Category', 'advanced-coupons-for-woocommerce' ),
                            'condition' => __( 'Condition', 'advanced-coupons-for-woocommerce' ),
                            'spend'     => __( 'Spend', 'advanced-coupons-for-woocommerce' ),
                        ),
                    ),
                ),
            ),
            'custom_user_meta'                          => array(
                'group'        => 'advanced',
                'key'          => 'custom-user-meta',
                'title'        => __( 'Custom User Meta', 'advanced-coupons-for-woocommerce' ),
                'meta_key'     => __( 'Meta Key', 'advanced-coupons-for-woocommerce' ),
                'meta_value'   => __( 'Meta Value', 'advanced-coupons-for-woocommerce' ),
                'value_type'   => __( 'Value type', 'advanced-coupons-for-woocommerce' ),
                'type_options' => array(
                    'string' => __( 'Text', 'advanced-coupons-for-woocommerce' ),
                    'number' => __( 'Number', 'advanced-coupons-for-woocommerce' ),
                    'price'  => __( 'Price', 'advanced-coupons-for-woocommerce' ),
                ),
            ),
            'custom_cart_item_meta'                     => array(
                'group'        => 'advanced',
                'key'          => 'custom-cart-item-meta',
                'title'        => __( 'Custom Cart Item Meta', 'advanced-coupons-for-woocommerce' ),
                'meta_key'     => __( 'Meta Key', 'advanced-coupons-for-woocommerce' ),
                'meta_value'   => __( 'Meta Value', 'advanced-coupons-for-woocommerce' ),
                'value_type'   => __( 'Value type', 'advanced-coupons-for-woocommerce' ),
                'type_options' => array(
                    'string' => __( 'Text', 'advanced-coupons-for-woocommerce' ),
                    'number' => __( 'Number', 'advanced-coupons-for-woocommerce' ),
                    'price'  => __( 'Price', 'advanced-coupons-for-woocommerce' ),
                ),
            ),
            'number_of_orders'                          => array(
                'group'           => 'customers',
                'key'             => 'number-of-orders',
                'title'           => __( 'Number of Customer Orders', 'advanced-coupons-for-woocommerce' ),
                'desc'            => __( "Currently logged in customer's total number of orders", 'advanced-coupons-for-woocommerce' ),
                'count_label'     => __( 'Count', 'advanced-coupons-for-woocommerce' ),
                'prev_days_label' => __( 'No. of previous days', 'advanced-coupons-for-woocommerce' ),
            ),
        );

        return array_merge( $cart_condition_data, $premium_fields );
    }

    /**
     * Add premium condition field options to localized data.
     *
     * @since 2.0
     * @access public
     *
     * @param array $options Condition field options.
     * @return array Filtered condition field options.
     */
    public function condition_field_options_localized_data( $options ) {
        return array_merge( $options, array_keys( $this->_premium_field_options ) );
    }

    /*
    |--------------------------------------------------------------------------
    | Condition field methods
    |--------------------------------------------------------------------------
     */

    /**
     * Get product quantity condition field value.
     *
     * @since 2.0
     * @access private
     *
     * @param array $product_conditions List of product condition data.
     * @param array $obj_products       List of products in cart/order.
     * @return bool Condition field value.
     */
    private function _get_product_quantity_condition_field_value( $product_conditions, $obj_products = array() ) {
        $field_condition = true;
        $product_ids     = array_column( $product_conditions, 'product_id' );
        $quantities      = array_column( $product_conditions, 'quantity', 'product_id' );
        $conditions      = array_column( $product_conditions, 'condition', 'product_id' );
        $loop_quantities = array();

        if ( ! is_array( $obj_products ) || empty( $obj_products ) ) {
            $obj_products = \WC()->cart->get_cart_contents();
        }

        // get quantities of each product in the cart that is present in the condition.
        foreach ( $obj_products as $cart_id => $cart_item ) {

            $id  = isset( $cart_item['variation_id'] ) && $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'];
            $id  = apply_filters( 'acfw_filter_cart_item_product_id', $id );
            $key = array_search( $id, $product_ids, true );

            if ( false === $key || isset( $cart_item['acfw_add_product'] ) || isset( $cart_item['acfw_bogo_deals'] ) ) {
                continue;
            }

            if ( isset( $loop_quantities[ $id ] ) ) {
                $loop_quantities[ $id ] += $cart_item['quantity'];
            } else {
                $loop_quantities[ $id ] = $cart_item['quantity'];
            }
        }

        // make sure all products in the condition is included in the $loop_quantities array. If not then we add it and set quantity to 0.
        foreach ( $quantities as $pid => $quantity ) {

            if ( isset( $loop_quantities[ $pid ] ) ) {
                continue;
            }

            $loop_quantities[ $pid ] = 0;
        }

        if ( empty( $loop_quantities ) ) {
            return false;
        }

        foreach ( $loop_quantities as $prod_id => $quantity ) {

            if ( ! isset( $quantities[ $prod_id ] ) || ! isset( $conditions[ $prod_id ] ) ) {
                continue;
            }

            $current_condition = \ACFWF()->Helper_Functions->compare_condition_values( $quantity, $quantities[ $prod_id ], $conditions[ $prod_id ] );
            $field_condition   = \ACFWF()->Helper_Functions->compare_condition_values( $field_condition, $current_condition, 'and' );
        }

        return $field_condition;
    }

    /**
     * Get cart weight condition field value.
     *
     * @since 3.5.6
     * @access private
     *
     * @param array $condition_data Condition data.
     * @return bool Condition field value.
     */
    private function _get_cart_weight_condition_field_value( $condition_data ) {
        $weight = array_reduce(
            \WC()->cart->get_cart_contents(),
            function ( $carry, $item ) {
                if ( ! isset( $item['data'] ) || ! \ACFWF()->Cart_Conditions->is_cart_item_valid( $item ) ) {
                    return $carry;
                }
                $weight = (float) $item['data']->get_weight() ?? 0;
                return $carry + ( wc_add_number_precision( $weight ) * $item['quantity'] );
            },
            0
        );

        return \ACFWF()->Helper_Functions->compare_condition_values(
            $weight,
            wc_add_number_precision( $condition_data['value'] ),
            $condition_data['condition']
        );
    }

    /**
     * Get custom taxonomy condition field value.
     *
     * @since 1.14
     * @access private
     *
     * @param array $data List of product condition data.
     * @return bool Condition field value.
     */
    private function _get_custom_taxonomy_condition_field_value( $data ) {
        $product_ids = array();
        $cart_terms  = array();

        $taxonomy        = $data['condition'];
        $condition_terms = apply_filters( 'acfw_filter_product_tax_terms', $data['value'] );

        // get all children terms from all condition taxonimy terms.
        $children_terms = array_reduce(
            $condition_terms,
            function ( $c, $cat ) use ( $taxonomy ) {

            $term_children = get_term_children( $cat, $taxonomy );

            if ( is_wp_error( $term_children ) || empty( $term_children ) ) {
                return $c;
            } else {
                return array_merge( $c, $term_children );
            }
            },
            array()
        );

        // merge children terns to main condition terms array.
        if ( ! empty( $children_cats ) ) {
            $condition_terms = array_merge( $condition_terms, $children_terms );
        }

        $quantity_cond  = isset( $data['qtyCondition'] ) ? $data['qtyCondition'] : '>';
        $quantity_value = isset( $data['quantity'] ) ? (int) $data['quantity'] : 0;
        $cart_quantity  = 0;

        foreach ( \WC()->cart->get_cart_contents() as $cart_id => $cart_item ) {

            if ( ! \ACFWF()->Cart_Conditions->is_cart_item_valid( $cart_item ) ) {
                continue;
            }

            $product_terms = array();

            if ( is_a( $cart_item['data'], 'WC_Product_Variation' ) ) {

                $parent_prod   = wc_get_product( $cart_item['data']->get_parent_id() );
                $variable_atts = $parent_prod->get_variation_attributes();

                // if taxonomy is part of variation attributes, then we only get the term used for the variation.
                if ( in_array( $taxonomy, array_keys( $variable_atts ), true ) ) {

                    $variation_atts = $cart_item['data']->get_variation_attributes();
                    $term_slug      = isset( $variation_atts[ 'attribute_' . $taxonomy ] ) ? $variation_atts[ 'attribute_' . $taxonomy ] : '';
                    $term           = get_term_by( 'slug', $term_slug, $taxonomy );

                    if ( is_object( $term ) ) {
                        $product_terms[] = $term->term_id;
                    }
                } else {
                    $product_terms = $this->_get_terms_for_products( $parent_prod->get_id(), $taxonomy );
                }
            } else {
                $product_terms = $this->_get_terms_for_products( $cart_item['data']->get_id(), $taxonomy );
            }

            $intersect = array_intersect( $product_terms, $condition_terms );

            if ( ! empty( $intersect ) ) {
                $cart_quantity += (int) $cart_item['quantity'];
            }
        }

        return \ACFWF()->Helper_Functions->compare_condition_values( $cart_quantity, $quantity_value, $quantity_cond );
    }

    /**
     * Get Product Stock Availability Exists In Cart condition field value.
     *
     * @since 3.5.4
     * @access private
     *
     * @param array $condition_data Condition data.
     * @param array $obj_products   List of products in cart/order.
     * @return bool Condition field value.
     */
    private function _get_product_stock_availability_exists_in_cart_condition_field_value( $condition_data, $obj_products = array() ) {
        $field_condition = true;
        $products        = array_map(
            function( $item ) {
                return array(
                    'product_id'      => $item['product']['id'],
                    'product_label'   => $item['product']['label'],
                    'condition_id'    => $item['condition']['id'],
                    'condition_label' => $item['condition']['label'],
                );
            },
            $condition_data['products']
        );
        $product_ids     = array_column( $products, 'product_id' );
        $conditions      = array_column( $products, 'condition_id', 'product_id' );

        // loop through all products coupon condition.
        foreach ( $product_ids as $product_id ) {
            $product = wc_get_product( $product_id );
            $id      = $product->get_id();

            // Condition that makes coupon invalid.
            if (
                ! in_array( $conditions[ $id ], array( 'instock', 'outofstock', 'onbackorder' ), true ) || // if condition is not instock, outofstock, onbackorder.
                ( $product->get_stock_status() !== $conditions[ $id ] ) // if product stock status is not equal to condition.
            ) {
                $field_condition = false;
                break;
            }
        }

        return $field_condition;
    }

    /**
     * Get has ordered before condition field value.
     *
     * @since 2.0
     * @access private
     *
     * @param array $condition_data Condition data.
     * @return bool Condition field value.
     */
    private function _get_has_ordered_before_condition_field_value( $condition_data ) {
        $current_user    = wp_get_current_user();
        $field_condition = true;

        // skip if user is not logged-in.
        if ( ! $current_user->ID ) {
            return false;
        }

        // The following are the extracted variables: $condition, $value, $products.
        extract( $condition_data ); // phpcs:ignore

        $product_ids = array_column( $products, 'product_id' );
        $quantities  = array_column( $products, 'quantity', 'product_id' );
        $conditions  = array_column( $products, 'condition', 'product_id' );
        $condition   = $condition ?? 'number-of-orders';

        if ( 'within-a-period' === $condition ) {
            $order_ids = $this->_get_orders_within_a_period( $current_user->ID, wc_get_is_paid_statuses(), $value );
        } elseif ( 'number-of-orders' === $condition ) {
            $order_ids = $this->_get_user_previous_orders( $current_user->ID, wc_get_is_paid_statuses(), $value );
        }

        // skip if there are no orders.
        if ( ! isset( $order_ids ) || empty( $order_ids ) ) {
            return false;
        }

        // get summarized totals of product quantities from the detected orders.
        $loop_quantities = $this->_count_product_quantities_of_orders( $order_ids );

        // make sure all products in the condition is included in the $loop_quantities array. If not then we add it and set quantity to 0.
        foreach ( $quantities as $pid => $quantity ) {

            if ( isset( $loop_quantities[ $pid ] ) ) {
                continue;
            }

            $loop_quantities[ $pid ] = 0;
        }

        if ( empty( $loop_quantities ) ) {
            return false;
        }

        foreach ( $loop_quantities as $prod_id => $quantity ) {

            if ( ! isset( $quantities[ $prod_id ] ) || ! isset( $conditions[ $prod_id ] ) ) {
                continue;
            }

            $current_condition = \ACFWF()->Helper_Functions->compare_condition_values( $quantity, $quantities[ $prod_id ], $conditions[ $prod_id ] );
            $field_condition   = \ACFWF()->Helper_Functions->compare_condition_values( $field_condition, $current_condition, 'and' );
        }

        return $field_condition;
    }

    /**
     * Get has ordered product categories before condition field value.
     *
     * @since 3.5.4
     * @access private
     *
     * @param array $condition_data Condition data.
     * @return bool Condition field value.
     */
    private function _get_has_ordered_product_categories_before_condition_field_value( $condition_data ) {
        $current_user    = wp_get_current_user();
        $field_condition = true;

        // skip if user is not logged-in.
        if ( ! $current_user->ID ) {
            return false;
        }

        // The following are the extracted variables: $categories, $condition, $quantity, $type.
        extract( $condition_data ); // phpcs:ignore

        if ( 'within-a-period' === $type['condition'] ) {
            $order_ids = $this->_get_orders_within_a_period( $current_user->ID, wc_get_is_paid_statuses(), $type['value'] );
        } elseif ( 'number-of-orders' === $type['condition'] ) {
            $order_ids = $this->_get_user_previous_orders( $current_user->ID, wc_get_is_paid_statuses(), $type['value'] );
        }

        // skip if there are no orders.
        if ( ! isset( $order_ids ) || empty( $order_ids ) ) {
            return false;
        }

        // get summarized totals of product quantities from the detected orders.
        $loop_quantities = $this->_count_product_categories_quantities_of_orders( $order_ids );

        // make sure all categories in the condition is included in the $loop_quantities array. If not then we add it and set quantity to 0.
        foreach ( $categories as $cid ) {

            if ( isset( $loop_quantities[ $cid ] ) ) {
                continue;
            }

            $loop_quantities[ $cid ] = 0;
        }

        if ( empty( $loop_quantities ) ) {
            return false;
        }

        foreach ( $loop_quantities as $cid => $cq ) {

            if ( ! in_array( $cid, $categories, true ) ) {
                continue;
            }

            $current_condition = \ACFWF()->Helper_Functions->compare_condition_values( $cq, $quantity, $condition );
            $field_condition   = \ACFWF()->Helper_Functions->compare_condition_values( $field_condition, $current_condition, 'and' );

        }

        return $field_condition;
    }

    /**
     * Get total customer spend on product category field value.
     *
     * @since 3.5.6
     * @access private
     *
     * @param array $condition_data Condition data.
     * @return bool Condition field value.
     */
    private function _get_total_customer_spend_on_product_category_condition_field_value( $condition_data ) {
        $current_user = wp_get_current_user();

        // skip if user is not logged-in.
        if ( ! $current_user->ID ) {
            return false;
        }

        // Extract variables from an array: $categories, $condition, $spend, $type.
        $categories = $condition_data['categories'] ?? array();
        $condition  = $condition_data['condition'] ?? '';
        $spend      = $condition_data['spend'] ?? 0;
        $type       = $condition_data['type'] ?? array();

        if ( 'within-a-period' === $type['condition'] ) {
            $order_ids = $this->_get_orders_within_a_period( $current_user->ID, wc_get_is_paid_statuses(), $type['value'] );
        } elseif ( 'number-of-orders' === $type['condition'] ) {
            $order_ids = $this->_get_user_previous_orders( $current_user->ID, wc_get_is_paid_statuses(), $type['value'] );
        }

        // skip if there are no orders.
        if ( ! isset( $order_ids ) || empty( $order_ids ) ) {
            return false;
        }

        // get summarized totals of product spend from the detected orders.
        $total_spend = $this->_calculate_product_categories_spends_of_orders( $order_ids, $categories );

        return \ACFWF()->Helper_Functions->compare_condition_values(
            $total_spend,
            wc_add_number_precision( $spend ),
            $condition
        );
    }

    /**
     * Get customer registration date condition field value.
     *
     * @since 2.0
     * @access private
     *
     * @param array $data Condition data.
     * @return bool Condition field value.
     */
    private function _get_customer_registration_date_condition_field_value( $data ) {
        if ( ! is_user_logged_in() ) {
            return false;
        }

        $timezone        = new \DateTimeZone( 'UTC' );
        $user_registered = date_create( wp_get_current_user()->user_registered, $timezone );
        $date_now        = date_create( 'now', $timezone );
        $interval        = 1 === $data ? ' hour' : ' hours';
        $date_compare    = clone $user_registered;

        $date_compare->add( date_interval_create_from_date_string( $data . $interval ) );

        return $date_now >= $user_registered && $date_now <= $date_compare;
    }

    /**
     * Get customer last ordered condition field value.
     *
     * @since 2.0
     * @access private
     *
     * @param array $data Condition data.
     * @return bool Condition field value.
     */
    private function _get_customer_last_ordered_condition_field_value( $data ) {
        if ( ! is_user_logged_in() ) {
            return false;
        }

        $timezone   = new \DateTimeZone( 'UTC' );
        $customer   = new \WC_Customer( get_current_user_id() );
        $last_order = $customer->get_last_order();
        $order_date = is_a( $last_order, 'WC_Order' ) && $last_order->get_status() === 'completed' ? $last_order->get_date_completed() : null;
        $date_now   = date_create( 'now', $timezone );
        $interval   = 1 === $data ? ' hour' : ' hours';

        if ( ! $order_date || ! is_a( $order_date, 'DateTime' ) ) {
            return false;
        }

        if ( $order_date && 0 === $data ) {
            return true;
        }

        $date_compare = clone $order_date;
        $date_compare->add( date_interval_create_from_date_string( $data . $interval ) );

        return $date_now >= $order_date && $date_now <= $date_compare;
    }

    /**
     * Get shipping zone region condition field value.
     *
     * @since 2.0
     * @access private
     *
     * @param array $data Condition data.
     * @return bool Condition field value.
     */
    private function _get_shipping_zone_region_condition_field_value( $data ) {
        $c_zone_id     = $data['condition'];
        $c_regions     = $data['value'];
        $country       = \WC()->cart->get_customer()->get_shipping_country();
        $state         = \WC()->cart->get_customer()->get_shipping_state();
        $user_zones    = array(
            'continent:' . \WC()->countries->get_continent_code_for_country( $country ),
            'country:' . $country,
        );
        $shipping_zone = wc_get_shipping_zone(
            array(
				'destination' => array(
					'country'   => $country,
					'state'     => $state,
					'postcode'  => \WC()->cart->get_customer()->get_shipping_postcode(),
					'city'      => \WC()->cart->get_customer()->get_shipping_city(),
					'address'   => \WC()->cart->get_customer()->get_shipping_address(),
					'address_1' => \WC()->cart->get_customer()->get_shipping_address(), // Provide both address and address_1 for backwards compatibility.
					'address_2' => \WC()->cart->get_customer()->get_shipping_address_2(),
				),
            )
        );

        if ( isset( $state ) ) {
            $user_zones[] = 'state:' . $country . ':' . $state;
        }

        return ( $shipping_zone->get_id() === $c_zone_id && ! empty( array_intersect( $c_regions, $user_zones ) ) );
    }

    /**
     * Get custom user meta condition field value.
     *
     * @since 2.0
     *
     * @param array $data Condition data.
     * @return bool Condition field value.
     */
    private function _get_custom_user_meta_condition_field_value( $data ) {
        if ( ! is_user_logged_in() ) {
            return false;
        }

        $user_id  = wp_get_current_user()->ID;
        $meta_val = get_user_meta( $user_id, $data['key'], true );
        $value    = $data['value'];

        if ( 'number' === $data['type'] ) {
            $meta_val = intval( $meta_val );
        } elseif ( 'price' === $data['type'] ) {
            $meta_val = floatval( $meta_val );
            $value    = \ACFWF()->Helper_Functions->sanitize_price( $value );
        }

        return \ACFWF()->Helper_Functions->compare_condition_values( $meta_val, $value, $data['condition'] );
    }

    /**
     * Get custom cart item meta condition field value.
     *
     * @since 2.0
     * @since 3.2.1 Make it possible to check values in a multi dimensional array.
     * @access private
     *
     * @param array $data Condition data.
     * @return bool Condition field value.
     */
    private function _get_custom_cart_item_meta_condition_field_value( $data ) {
        foreach ( \WC()->cart->get_cart_contents() as $cart_item ) {

            $value    = $data['value'];
            $meta_val = $this->_deep_find_cart_item_meta_value_by_key( $cart_item, $data['key'] );

            /**
             * Add support for EXISTS and DOESN'T EXIST condition types.
             * If value is null, then it doesn't exist, otherwise it exists.
             *
             * @since 3.2.1
             */
            if ( in_array( $data['condition'], array( 'exists', 'notexist' ), true ) ) {
                return 'exists' === $data['condition'] ? ! is_null( $meta_val ) : is_null( $meta_val );
            }

            // format value for number and price types.
            if ( 'number' === $data['type'] ) {
                $meta_val = intval( $meta_val );
            } elseif ( 'price' === $data['type'] ) {
                $meta_val = floatval( $meta_val );
                $value    = \ACFWF()->Helper_Functions->sanitize_price( $value );
            }

            $check = \ACFWF()->Helper_Functions->compare_condition_values( $meta_val, $value, $data['condition'] );

            if ( $check ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get total customer spend condition field value.
     *
     * @since 1.13
     * @access private
     *
     * @param array $condition_data Condition data.
     * @return bool Condition field value.
     */
    private function _get_total_customer_spend_condition_field_value( $condition_data ) {
        $current_user = wp_get_current_user();
        if ( ! is_object( $current_user ) || ! $current_user->ID ) {
            return false;
        }

        // The following are the extracted variables: $condition, $offset and $value.
        extract( $condition_data ); // phpcs:ignore

        // Set Variables.
        $statuses = array_map(
            function( $status ) {
                return 'wc-' . $status;
            },
            wc_get_is_paid_statuses()
        );
        $offset   = intval( $offset );

        // Set Parameters.
        $args = array(
            'status'      => $statuses,
            'customer_id' => $current_user->ID,
        );

        // Add date query if offset is set.
        if ( $offset > 0 ) {
            $args['date_query'] = array(
                array(
                    'after'     => $this->_get_days_offset_utc_date( $offset ),
                    'inclusive' => true,
                ),
            );
        }

        // Get Orders.
        $orders  = wc_get_orders( $args );
        $results = array();
        foreach ( $orders as $order ) {
            $tmp           = array();
            $tmp['ID']     = $order->get_id();
            $tmp['amount'] = $order->get_total();
            $results[]     = (object) $tmp;
        }

        $total_spend = array_reduce(
            $results,
            function ( $c, $r ) {
            return $c + apply_filters( 'acfw_filter_order_amount', (float) $r->amount, $r->ID );
            },
            0
        );

        $total_spend = round( $total_spend, get_option( 'woocommerce_price_num_decimals', 2 ) );

        return \ACFWF()->Helper_Functions->compare_condition_values( (float) $total_spend, (float) $value, $condition );
    }

    /**
     * Get number of orders condition field value.
     *
     * @since 3.2
     * @access private
     *
     * @param array $condition_data Condition data.
     * @return bool Condition field value.
     */
    private function _get_number_of_orders_condition_field_value( $condition_data ) {
        $current_user = wp_get_current_user();
        if ( ! is_object( $current_user ) || ! $current_user->ID ) {
            return false;
        }

        // The following are the extracted variables: outputs $condition, $offset and $value.
        extract( $condition_data ); // phpcs:ignore

        // Set Variables.
        $statuses = array_map(
            function( $status ) {
                return 'wc-' . $status;
            },
            apply_filters( 'acfwp_number_of_orders_cart_condition_statuses', wc_get_is_paid_statuses() )
        );
        $offset   = intval( $offset );

        // Set Parameters.
        $args = array(
            'status'      => $statuses,
            'customer_id' => $current_user->ID,
        );

        // Add date query if offset is set.
        if ( $offset > 0 ) {
            $args['date_query'] = array(
                array(
                    'after'     => $this->_get_days_offset_utc_date( $offset ),
                    'inclusive' => true,
                ),
            );
        }

        // Get Orders.
        $orders      = wc_get_orders( $args );
        $order_count = count( $orders );

        return \ACFWF()->Helper_Functions->compare_condition_values( (int) $order_count, (int) $value, $condition );
    }

    /*
    |--------------------------------------------------------------------------
    | Utility Functions
    |--------------------------------------------------------------------------
     */

    /**
     * Get custom taxonomy options.
     *
     * @since 2.0
     * @access public
     *
     * @return array Custom taxonomy options.
     */
    public function get_custom_taxonomy_options() {
        global $wpdb;

        $taxonomies   = get_taxonomies( array( 'object_type' => array( 'product' ) ), 'objects' );
        $tax_options  = array();
        $taxonomy_str = implode( "','", array_keys( $taxonomies ) );

        // Disabled PHPCS here as there are no user defined data in this query.
        // phpcs:disable
        $tax_terms    = $wpdb->get_results(
            "SELECT t.term_id,t.name,t.slug,tx.taxonomy FROM {$wpdb->terms} AS t
            INNER JOIN {$wpdb->term_taxonomy} AS tx ON ( tx.term_id = t.term_id )
            WHERE tx.taxonomy IN ('{$taxonomy_str}')
        ",
            ARRAY_A
        );
        // phpcs:enable

        foreach ( $taxonomies as $taxonomy ) {

            if ( 'product_cat' === $taxonomy->name ) {
                continue;
            }

            $terms = array_filter(
                $tax_terms,
                function ( $t ) use ( $taxonomy ) {

                // We don't include grouped and external product types as products under these can't be added to the cart.
                if ( 'product_type' === $t['taxonomy'] && in_array( $t['slug'], array( 'grouped', 'external' ), true ) ) {
                    return false;
                }

                return $t['taxonomy'] === $taxonomy->name;
                }
            );

            $tax_options[] = array(
                'slug'  => $taxonomy->name,
                'terms' => array_values( apply_filters( 'acfwp_cart_condition_tax_term_option', $terms, $taxonomy->name ) ),
            );
        }

        return $tax_options;
    }

    /**
     * Get days offset timestamp value.
     *
     * @since 1.13
     * @access private
     *
     * @param int $offset Days offset.
     * @return string UTC date (mysql format).
     */
    private function _get_days_offset_utc_date( $offset ) {
        $utc      = new \DateTimeZone( 'UTC' );
        $timezone = new \DateTimeZone( $this->_helper_functions->get_site_current_timezone() );
        $interval = new \DateInterval( sprintf( 'P%sD', $offset ) );
        $dateobj  = new \DateTime( 'now', $timezone );

        $dateobj->setTime( 0, 0, 0 );
        $dateobj->sub( $interval );

        $dateobj->setTimezone( $utc );

        return $dateobj->format( 'Y-m-d H:i:s' );
    }

    /**
     * Get orders within a period.
     *
     * @since 2.0
     * @access private
     *
     * @param int    $user_id  Customer ID.
     * @param array  $statuses Order status to query.
     * @param int    $period   Number of days before current time.
     * @param string $sort     Order results sorting.
     * @return array Order ids within a period.
     */
    private function _get_orders_within_a_period( $user_id, $statuses = array( 'wc-completed' ), $period = 3, $sort = 'DESC' ) {
        // Dates.
        $date_time   = new \DateTimeZone( $this->_helper_functions->get_site_current_timezone() );
        $latest_date = new \WC_DateTime( 'now', $date_time );
        $old_date    = clone $latest_date;
        $old_date->modify( '-' . $period . ' day' );

        // Arguments.
        $args = array(
            'customer_id' => $user_id,
            'limit'       => -1,
            'status'      => $statuses,
            'orderby'     => 'date',
            'order'       => $sort,
        );

        // Get orders within a period.
        $orders  = wc_get_orders( $args );
        $results = array();
        foreach ( $orders as $order ) {
            // If period is 0, then get all orders.
            if ( 0 === absint( $period ) ) {
                $results[] = $order->get_id();
                continue;
            }
            // If order status is completed and completed date is within the period.
            if ( $order->get_status() === 'completed' ) {
                $completed_date = $order->get_date_completed();
                if ( $completed_date && $completed_date >= $old_date && $completed_date <= $latest_date ) {
                    $results[] = $order->get_id();
                    continue;
                }
            }
            // If order status is processing and paid date is within the period.
            if ( $order->get_status() === 'processing' ) {
                $paid_date = $order->get_date_paid();
                if ( $paid_date && $paid_date >= $old_date && $paid_date <= $latest_date ) {
                    $results[] = $order->get_id();
                    continue;
                }
            }
            // If order status is processing or completed and order date is within the period.
            if ( in_array( $order->get_status(), array( 'processing', 'completed' ), true ) ) {
                $date = $order->get_date_created();
                if ( $date && $date >= $old_date && $date <= $latest_date ) {
                    $results[] = $order->get_id();
                    continue;
                }
            }
        }

        return $results;
    }

    /**
     * Get user previous orders.
     *
     * @since 2.0
     * @access private
     *
     * @param int    $user_id  Customer ID.
     * @param array  $statuses Order status to query.
     * @param int    $limit    Number of previous orders to fetch.
     * @param string $sort     Order results sorting.
     * @return array Customer previous order ids.
     */
    private function _get_user_previous_orders( $user_id, $statuses = array( 'wc-completed' ), $limit = null, $sort = 'DESC' ) {
        // Arguments.
        $args = array(
            'customer_id' => $user_id,
            'limit'       => ( $limit && is_numeric( $limit ) ) ? $limit : -1,
            'status'      => $statuses,
            'return'      => 'ids',
            'orderby'     => 'date',
            'order'       => $sort,
        );

        // Get user previous orders.
        return wc_get_orders( $args );
    }

    /**
     * Count quantities of products in listed orders.
     *
     * @since 2.0
     * @access private
     *
     * @global wpdb $wpdb Object that contains a set of functions used to interact with a database.
     *
     * @param array $order_ids List of order IDs.
     * @return array List of product quantities.
     */
    private function _count_product_quantities_of_orders( $order_ids ) {
        global $wpdb;

        $results = array();
        if ( ! is_array( $order_ids ) || empty( $order_ids ) ) {
            return $results;
        }

        $ids_string = implode( ',', $order_ids );

        $query = "SELECT item.order_item_id , item.order_id , product.meta_value AS 'product_id' , variation.meta_value AS 'variation_id' , quantity.meta_value AS 'quantity' 
                  FROM {$wpdb->prefix}woocommerce_order_items AS item
                  INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS product ON ( item.order_item_id = product.order_item_id AND product.meta_key = '_product_id' )
                  INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS variation ON ( item.order_item_id = variation.order_item_id AND variation.meta_key = '_variation_id' )
                  INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS quantity ON ( item.order_item_id = quantity.order_item_id AND quantity.meta_key = '_qty' )
                  WHERE item.order_id IN ( {$ids_string} )
                  GROUP BY item.order_item_id";

        $raw_data = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore

        if ( ! is_array( $raw_data ) || empty( $raw_data ) ) {
            return $results;
        }

        foreach ( $raw_data as $data ) {
            $product_id   = absint( $data['product_id'] );
            $variation_id = absint( $data['variation_id'] );
            $key_id       = $variation_id > 0 ? $variation_id : $product_id;

            if ( isset( $results[ $key_id ] ) ) {
                $results[ $key_id ] += intval( $data['quantity'] );
            } else {
                $results[ $key_id ] = intval( $data['quantity'] );
            }
        }

        return $results;
    }

    /**
     * Calculate product categories spends of orders.
     *
     * @since 3.5.6
     * @access private
     *
     * @param array $order_ids List of order IDs.
     * @param array $categories List of product categories to check.
     *
     * @return float Total amount of product categories spends.
     */
    private function _calculate_product_categories_spends_of_orders( $order_ids, $categories ) {
        $total = 0.0;

        // Validate order ids.
        if ( ! $order_ids || ! is_array( $order_ids ) || empty( $order_ids ) ) {
            return $total;
        }

        // Loop through order ids.
        foreach ( $order_ids as $order_id ) {
            // Grab Order.
            $order = wc_get_order( $order_id );
            if ( ! $order ) {
                continue;
            }

            // Loop through items in order.
            foreach ( $order->get_items() as $item ) {
                $product_cat = wp_get_post_terms( $item->get_product_id(), 'product_cat', array( 'fields' => 'ids' ) );
                if ( ! empty( array_intersect( $product_cat, $categories ) ) ) {
                    $total += wc_add_number_precision( $item->get_total() );
                }
            }
        }

        return $total;
    }

    /**
     * Count quantities of product categories in listed orders.
     *
     * @since 3.5.4
     * @access private
     *
     * @global wpdb $wpdb Object that contains a set of functions used to interact with a database.
     *
     * @param array $order_ids List of order IDs.
     * @return array List of product quantities.
     */
    private function _count_product_categories_quantities_of_orders( $order_ids ) {
        global $wpdb;

        $results = array();
        if ( ! is_array( $order_ids ) || empty( $order_ids ) ) {
            return $results;
        }

        $ids_string = implode( ',', $order_ids );

        $query = "SELECT 
                    item.order_item_id ,
                    item.order_id ,
                    product.meta_value AS 'product_id' ,
                    variation.meta_value AS 'variation_id' ,
                    quantity.meta_value AS 'quantity', 
                    term_relationships.term_taxonomy_id AS category_id
                  FROM {$wpdb->prefix}woocommerce_order_items AS item
                  INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS product ON ( item.order_item_id = product.order_item_id AND product.meta_key = '_product_id' )
                  INNER JOIN {$wpdb->prefix}term_relationships AS term_relationships ON product.meta_value = term_relationships.object_id
                  INNER JOIN {$wpdb->prefix}term_taxonomy AS term_taxonomy ON term_relationships.term_taxonomy_id = term_taxonomy.term_taxonomy_id
                  INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS variation ON ( item.order_item_id = variation.order_item_id AND variation.meta_key = '_variation_id' )
                  INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS quantity ON ( item.order_item_id = quantity.order_item_id AND quantity.meta_key = '_qty' )
                  WHERE item.order_id IN ( {$ids_string} ) AND term_taxonomy.taxonomy = 'product_cat'
                  GROUP BY item.order_item_id";

        $raw_data = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore

        if ( ! is_array( $raw_data ) || empty( $raw_data ) ) {
            return $results;
        }

        foreach ( $raw_data as $data ) {

            $category_id = absint( $data['category_id'] );

            if ( isset( $results[ $category_id ] ) ) {
                $results[ $category_id ] += intval( $data['quantity'] );
            } else {
                $results[ $category_id ] = intval( $data['quantity'] );
            }
        }

        return $results;
    }

    /**
     * Get terms for list of products IDs.
     *
     * @since 1.14
     * @access private
     *
     * @param array|int $product_ids List of product ids or a single product ID.
     * @param string    $taxonomy   Taxonomy slug.
     * @return array List of term ids.
     */
    private function _get_terms_for_products( $product_ids, $taxonomy ) {
        global $wpdb;

        $product_ids = ! is_array( $product_ids ) ? array( $product_ids ) : $product_ids;

        if ( empty( $product_ids ) ) {
            return array();
        }

        $in_product_ids = implode( ',', array_map( 'absint', $product_ids ) );
        $taxonomy       = esc_sql( $taxonomy );

        // Disabled PHPCS here as there are no user defined data in this query.
        // phpcs:disable
        $term_ids = $wpdb->get_col(
            "SELECT tx.term_id FROM {$wpdb->term_relationships} AS r
            INNER JOIN {$wpdb->term_taxonomy} AS tx ON (tx.term_taxonomy_id = r.term_taxonomy_id)
            WHERE r.object_id IN ({$in_product_ids})
                AND tx.taxonomy = '{$taxonomy}'
        "
        );
        // phpcs:enable

        return array_map( 'absint', $term_ids );
    }

    /**
     * Get all shipping region options.
     *
     * @since 1.14
     * @access public
     *
     * @return array List of shipping region options.
     */
    public function get_all_shipping_region_options() {
        $wc_countries      = new \WC_Countries();
        $continents        = $wc_countries->get_shipping_continents();
        $allowed_countries = $wc_countries->get_allowed_countries();
        $regions           = array();

        foreach ( $continents as $continent_code => $continent ) {

            $regions[ 'continent:' . $continent_code ] = sprintf(
                /* Translators: %s: Continent name. */
                __( '%s (continent)', 'advanced-coupons-for-woocommerce' ),
                $continent['name']
            );

            $countries = array_intersect( array_keys( $allowed_countries ), $continent['countries'] );
            foreach ( $countries as $country_code ) {

                $regions[ 'country:' . $country_code ] = $allowed_countries[ $country_code ];

                $states = $wc_countries->get_states( $country_code );

                if ( ! is_array( $states ) || empty( $states ) ) {
                    continue;
                }

                foreach ( $states as $state_code => $state_name ) {
                    $regions[ 'state:' . $country_code . ':' . $state_code ] = sprintf( '%s, %s', $state_name, $allowed_countries[ $country_code ] );
                }
}
        }

        return $regions;
    }

    /**
     * Get shipping zones and its regions as options
     *
     * @since 1.14
     * @access public
     *
     * @return array List of regions as options.
     */
    public function get_shipping_zones_options() {
        $zones   = $this->_helper_functions->get_shipping_zones();
        $options = array();

        foreach ( $zones as $zone ) {
            $options[ $zone['id'] ] = array(
                'name'    => $zone['zone_name'],
                'regions' => $this->_get_shipping_zone_region_options( $zone['zone_locations'] ),
            );
        }

        return $options;
    }

    /**
     * Get shipping zone region options.
     *
     * @since 1.14
     * @access private
     *
     * @param array $zone_locations List of zone locations.
     * @return array List of zone regions as options.
     */
    private function _get_shipping_zone_region_options( $zone_locations ) {
        $regions = array();

        if ( is_array( $zone_locations ) && ! empty( $zone_locations ) ) {

            foreach ( $zone_locations as $location ) {
                if ( 'postcode' === $location->type ) {
                    continue;
                }

                $regions[] = $location->type . ':' . $location->code;
            }
        }

        return $regions;
    }

    /**
     * Deep find value of a specific meta that exists in a cart item data based on it's key.
     *
     * @since 3.2.1
     * @access private
     *
     * @param array  $cart_item Cart item data.
     * @param string $meta_key  Meta key path (example: meta_key|sub_meta1|sub_meta2).
     * @return mixed Cart item meta value.
     */
    private function _deep_find_cart_item_meta_value_by_key( $cart_item, $meta_key ) {
        $key_path = explode( '|', $meta_key );
        $meta_val = null;

        foreach ( $key_path as $i => $key ) {

            // handles the first iteration differently.
            if ( 0 === $i ) {

                if ( isset( $cart_item[ $key ] ) ) {
                    // get the WC_Product object data array values if `data` is the first key to be checked.
                    $meta_val = 'data' === $key ? $cart_item['data']->get_data() : $cart_item[ $key ];
                } else {
                    // break the loop when the meta doesn't exist.
                    $meta_val = null;
                    break;
                }

                continue;
            }

            /**
             * Handles the second to last iteration.
             * continues to loop until the last item is reached, the value previously returned is an array,
             * and the key exists in the previously returned array.
             */
            if ( is_array( $meta_val ) && isset( $meta_val[ $key ] ) ) {
                $meta_val = $meta_val[ $key ];
                continue;
            }

            $meta_val = null;
            break;
        }

        return $meta_val;
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
    public function initialize() {
        if ( ! \ACFWF()->Helper_Functions->is_module( Plugin_Constants::CART_CONDITIONS_MODULE ) ) {
            return;
        }
    }

    /**
     * Execute Cart_Conditions class.
     *
     * @since 2.0
     * @access public
     * @inherit ACFWP\Interfaces\Model_Interface
     */
    public function run() {
        if ( ! \ACFWF()->Helper_Functions->is_module( Plugin_Constants::CART_CONDITIONS_MODULE ) ) {
            return;
        }

        add_filter( 'acfw_get_cart_condition_field_value', array( $this, 'get_condition_field_value' ), 10, 3 );
        add_filter( 'acfw_cart_condition_field_options', array( $this, 'cart_condition_premium_field_options' ) );
        add_filter( 'acfw_sanitize_cart_condition_field', array( $this, 'sanitize_cart_condition_field' ), 10, 3 );
        add_filter( 'acfw_format_edit_cart_condition_field', array( $this, 'format_cart_conditions_for_edit' ), 10, 2 );
        add_filter( 'acfw_cart_conditions_panel_data_atts', array( $this, 'cart_conditions_panel_data_atts' ) );
        add_filter( 'acfw_condition_fields_localized_data', array( $this, 'condition_fields_localized_data' ) );
        add_filter( 'acfw_condition_field_options_localized_data', array( $this, 'condition_field_options_localized_data' ) );
    }
}
