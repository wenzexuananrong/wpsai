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
class Total_Claimed_Gift_Cards_Count extends Abstract_Report_Widget {
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

        $this->key         = 'total_claimed_gift_cards_count';
        $this->widget_name = __( 'Total Claimed Gift Cards', 'advanced-gift-cards-for-woocommerce' );
        $this->type        = 'big_number';
        $this->description = __( 'Total Claimed Gift Cards', 'advanced-gift-cards-for-woocommerce' );
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
        $this->raw_data  = (int) $gift_card_stats['total_claimed_count'];
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
        $this->title = $this->raw_data;
    }
}
