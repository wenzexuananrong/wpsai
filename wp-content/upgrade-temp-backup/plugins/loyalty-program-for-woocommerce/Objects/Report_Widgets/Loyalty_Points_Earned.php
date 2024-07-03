<?php

namespace LPFW\Objects\Report_Widgets;

use ACFWF\Abstracts\Abstract_Report_Widget;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Model that houses the data model of a loyalty points earned report widget object.
 *
 * @since 1.5.3
 */
class Loyalty_Points_Earned extends Abstract_Report_Widget {
    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Create a new Report Widget object.
     *
     * @since 1.5.3
     * @access public
     *
     * @param ACFWF\Models\Objects\Date_Period_Range $report_period Date period range object.
     */
    public function __construct( $report_period ) {
        // build report data.
        parent::__construct( $report_period );

        $this->key         = 'loyalty_points_earned';
        $this->widget_name = __( 'Loyalty Points Earned', 'loyalty-program-for-woocommerce' );
        $this->type        = 'big_number';
        $this->description = __( 'Loyalty Points Earned', 'loyalty-program-for-woocommerce' );
        $this->page_link   = 'acfw-loyalty-program';
    }

    /*
    |--------------------------------------------------------------------------
    | Query methods
    |--------------------------------------------------------------------------
    */

    /**
     * Query report data.
     *
     * @since 1.5.3
     * @access protected
     */
    protected function _query_report_data() {
        global $wpdb;

        $loyalty_stats = \LPFW()->Calculate->calculate_loyalty_points_period_statistics( $this->report_period );

        $this->raw_data = $loyalty_stats['earned_in_period'];
    }

    /*
    |--------------------------------------------------------------------------
    | Conditional methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if the report widget data cache should be handled in this class.
     *
     * @since 1.5.3
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
     * @since 1.5.3
     * @access public
     */
    protected function _format_report_data() {
        $this->title = \ACFWF()->Helper_Functions->format_integer_for_display( $this->raw_data );
    }
}
