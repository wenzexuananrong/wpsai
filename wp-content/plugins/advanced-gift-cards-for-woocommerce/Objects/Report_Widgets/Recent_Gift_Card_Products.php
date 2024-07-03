<?php

namespace AGCFW\Objects\Report_Widgets;

use AGCFW\Abstracts\Abstract_Gift_Cards_Report_Widget;
use ACFWF\Models\Objects\Date_Period_Range;
use AGCFW\Objects\Product;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Recenlty created gift card products report widget object.
 *
 * @since 1.3.4
 */
class Recent_Gift_Card_Products extends Abstract_Gift_Cards_Report_Widget {
    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Create a new Report Widget object.
     *
     * @since 1.3.4
     * @access public
     *
     * @param Date_Period_Range $report_period Date period range object.
     */
    public function __construct( $report_period ) {
        $this->key         = 'recent_gift_card_coupons';
        $this->widget_name = __( 'Recently Created Gift Card Products', 'advanced-gift-cards-for-woocommerce' );
        $this->type        = 'table';
        $this->title       = __( 'Recently Created Gift Card Products', 'advanced-gift-cards-for-woocommerce' );

        // build report data.
        parent::__construct( $report_period );
    }

    /*
    |--------------------------------------------------------------------------
    | Query methods
    |--------------------------------------------------------------------------
    */

    /**
     * Query report data freshly from the database.
     *
     * @since 1.3.4
     * @access private
     *
     * @return Product[] Array of gift card product objects.
     */
    private function _query_most_recent_gift_card_products() {
        // Get the most recent 5 gift card products.
        $products = wc_get_products(
            array(
                'status'  => 'publish',
                'type'    => 'advanced_gift_card',
                'limit'   => 5,
                'order'   => 'DESC',
                'orderby' => 'date',
            )
        );

        return $products;
    }

    /**
     * Query report data freshly from the database.
     *
     * @since 1.3.4
     * @access protected
     */
    protected function _query_report_data() {
        $products                  = $this->_query_most_recent_gift_card_products();
        $results                   = $this->_query_gift_cards_table_data();
        list($quantities, $values) = $this->_calculate_quantity_and_value_per_product( $results );

        // prepare data for response.
        $data = array();
        foreach ( $products as $product ) {
            $data[] = array(
                'id'       => $product->get_id(),
                'product'  => $product->get_name(),
                'quantity' => $quantities[ $product->get_id() ] ?? 0,
                'value'    => $values[ $product->get_id() ] ?? 0.0,
            );
        }

        $this->raw_data = $data;
    }
}
