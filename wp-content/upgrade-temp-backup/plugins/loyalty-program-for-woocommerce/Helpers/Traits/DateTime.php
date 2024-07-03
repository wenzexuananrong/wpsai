<?php
namespace LPFW\Helpers\Traits;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Trait that houses all the helper functions specifically for DateTime.
 *
 * @since 1.8.4
 */
trait DateTime {
    /**
     * Returns the timezone string for a site, even if it's set to a UTC offset
     *
     * Adapted from http://www.php.net/manual/en/function.timezone-name-from-abbr.php#89155
     *
     * Reference:
     * http://www.skyverge.com/blog/down-the-rabbit-hole-wordpress-and-timezones/
     *
     * @since 1.0.0
     * @since 1.8.4 Moved from LPFW\Helpers\Helper_Functions class.
     * @access public
     *
     * @return string Valid PHP timezone string
     */
    public function get_site_current_timezone() {
        // if site timezone string exists, return it.
        $timezone = trim( get_option( 'timezone_string' ) );
        if ( $timezone ) {
            return $timezone;
        }

        // get UTC offset, if it isn't set then return UTC.
        $utc_offset = trim( get_option( 'gmt_offset', 0 ) );

        if ( filter_var( $utc_offset, FILTER_VALIDATE_INT ) === 0 || '' === $utc_offset || is_null( $utc_offset ) ) {
            return 'UTC';
        }

        return $this->convert_utc_offset_to_timezone( $utc_offset );
    }

    /**
     * Convert UTC offset to timezone.
     *
     * @since 1.2.0
     * @since 1.8.4 Moved from LPFW\Helpers\Helper_Functions class.
     * @access public
     *
     * @param float/int/string $utc_offset UTC offset.
     * @return string valid PHP timezone string
     */
    public function convert_utc_offset_to_timezone( $utc_offset ) {
        // adjust UTC offset from hours to seconds.
        $utc_offset *= 3600;

        // attempt to guess the timezone string from the UTC offset.
        $timezone = timezone_name_from_abbr( '', $utc_offset, 0 );
        if ( $timezone ) {
            return $timezone;
        }

        // last try, guess timezone string manually.
        $is_dst = gmdate( 'I' );

        foreach ( timezone_abbreviations_list() as $abbr ) {
            foreach ( $abbr as $city ) {
                if ( $city['dst'] === $is_dst && $city['offset'] === $utc_offset ) {
                    return $city['timezone_id'];
                }
            }
        }

        // fallback to UTC.
        return 'UTC';
    }

    /**
     * Get default datetime format for display.
     *
     * @since 1.5.1
     * @since 1.8.4 Moved from LPFW\Helpers\Helper_Functions class.
     * @access public
     *
     * @return string Datetime format.
     */
    public function get_default_datetime_format() {
        return sprintf( '%s %s', get_option( 'date_format', 'F j, Y' ), get_option( 'time_format', 'g:i a' ) );
    }

    /**
     * Convert datetime to site standard format.
     * 1. Datetime must use site timezone.
     * 2. Datetime must use site default datetime format.
     * 3. Datetime must be localize using i18n.
     *
     * @since 1.8.4
     *
     * @param string $datetime Datetime string.
     *
     * @return string
     */
    public function convert_datetime_to_site_standard_format( $datetime ) {
        $timezone = new \DateTimeZone( $this->get_site_current_timezone() );
        $standard = new \WC_DateTime( $datetime, $timezone ); // Convert to site timezone.
        return $standard->date_i18n( $this->get_default_datetime_format() ); // Convert to site default datetime format and localize.
    }
}
