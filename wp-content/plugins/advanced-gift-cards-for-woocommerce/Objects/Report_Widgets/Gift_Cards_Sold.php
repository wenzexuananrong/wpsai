<?php

namespace AGCFW\Objects\Report_Widgets;

use ACFWF\Abstracts\Abstract_Report_Widget;
use ACFWF\Models\Objects\Date_Period_Range;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Claimed gift cards report widget object.
 *
 * @since 1.1.1
 */
class Gift_Cards_Sold extends Abstract_Report_Widget {
    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Create a new Report Widget object.
     *
     * @since 1.1.1
     * @access public
     *
     * @param Date_Period_Range $report_period Date period range object.
     */
    public function __construct( $report_period ) {
        // build report data.
        parent::__construct( $report_period );

        $widget_name       = sprintf( '%s (%s)', __( 'Gift Cards Sold', 'advanced-gift-cards-for-woocommerce' ), get_woocommerce_currency_symbol() );
        $this->key         = 'gift_cards_sold';
        $this->widget_name = $widget_name;
        $this->type        = 'big_number';
        $this->description = $widget_name;
    }

    /*
    |--------------------------------------------------------------------------
    | Query methods
    |--------------------------------------------------------------------------
    */

    /**
     * Query report data.
     *
     * @since 1.1.1
     * @access protected
     */
    protected function _query_report_data() {
        $gift_card_stats = \AGCFW()->Calculate->calculate_gift_cards_period_statistics( $this->report_period );
        $this->raw_data  = $gift_card_stats['sold_in_period'];
    }

    /*
    |--------------------------------------------------------------------------
    | Conditional methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if the report widget data cache should be handled in this class.
     *
     * @since 1.1.1
     * @access public
     */
    public function is_cache() {
        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Utility methods
    |--------------------------------------------------------------------------
     */

    /**
     * NOTE: This method needs to be override on the child class.
     *
     * @since 1.1.1
     * @access public
     */
    protected function _format_report_data() {
        $this->title = $this->_format_price( $this->raw_data );
    }
}
