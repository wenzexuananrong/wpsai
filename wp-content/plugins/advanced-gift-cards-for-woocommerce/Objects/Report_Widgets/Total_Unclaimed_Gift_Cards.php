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
 * @since 1.3.4
 */
class Total_Unclaimed_Gift_Cards extends Abstract_Report_Widget {
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
        // build report data.
        parent::__construct( $report_period );

        $widget_name       = sprintf( '%s (%s)', __( 'Total Unclaimed Gift Cards', 'advanced-gift-cards-for-woocommerce' ), get_woocommerce_currency_symbol() );
        $this->key         = 'total_unclaimed_gift_cards';
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
     * @since 1.3.4
     * @access protected
     */
    protected function _query_report_data() {
        $gift_card_stats = \AGCFW()->Calculate->calculate_gift_cards_total_statistics();
        $this->raw_data  = $gift_card_stats['total_unclaimed'];
    }

    /*
    |--------------------------------------------------------------------------
    | Conditional methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if the report widget data cache should be handled in this class.
     *
     * @since 1.3.4
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
     * @since 1.3.4
     * @access public
     */
    protected function _format_report_data() {
        $this->title = $this->_format_price( $this->raw_data );
    }
}
