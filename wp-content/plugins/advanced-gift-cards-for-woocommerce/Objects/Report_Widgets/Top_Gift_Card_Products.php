<?php

namespace AGCFW\Objects\Report_Widgets;

use AGCFW\Abstracts\Abstract_Gift_Cards_Report_Widget;
use ACFWF\Models\Objects\Date_Period_Range;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Top gift card products report widget object.
 *
 * @since 1.3.4
 */
class Top_Gift_Card_Products extends Abstract_Gift_Cards_Report_Widget {
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
        $this->key         = 'top_gift_card_coupons';
        $this->widget_name = __( 'Top Gift Card Products', 'advanced-gift-cards-for-woocommerce' );
        $this->type        = 'table';
        $this->title       = __( 'Top Gift Card Products', 'advanced-gift-cards-for-woocommerce' );

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
     * @access protected
     */
    protected function _query_report_data() {
        $results                   = $this->_query_gift_cards_table_data();
        list($quantities, $values) = $this->_calculate_quantity_and_value_per_product( $results );

        // sort usage count descendingly.
        arsort( $quantities, SORT_NUMERIC );

        $products = array();
        foreach ( $results as $row ) {
            $products[ $row['product_id'] ] = $row['product_name'];
        }

        // prepare data for response.
        $data = array();
        foreach ( $quantities as $product_id => $quantity ) {
            $data[] = array(
                'id'       => absint( $product_id ),
                'product'  => $products[ $product_id ],
                'quantity' => $quantity ?? 0,
                'value'    => $values[ $product_id ] ?? 0.0,
            );
        }

        $this->raw_data = $data;
    }
}
