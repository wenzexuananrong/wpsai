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
class Gift_Cards_Claimed_Count extends Abstract_Report_Widget {
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

        $this->key         = 'gift_cards_claimed_count';
        $this->widget_name = __( 'Gift Cards Claimed', 'advanced-gift-cards-for-woocommerce' );
        $this->type        = 'big_number';
        $this->description = __( 'Gift Cards Claimed', 'advanced-gift-cards-for-woocommerce' );
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
        $gift_card_stats = \AGCFW()->Calculate->calculate_gift_cards_period_statistics( $this->report_period );
        $this->raw_data  = $gift_card_stats['claimed_in_period_count'];
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
        $this->title = (int) $this->raw_data;
    }
}
