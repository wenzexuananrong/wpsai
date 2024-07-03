<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
exit;}  ?>

<div class="acfw-daytime-schedules-section acfw-scheduler-section">
    <label class="acfw-section-toggle">
        <input type="checkbox" name="_acfw_enable_day_time_schedules" value="yes" <?php checked( $coupon->get_advanced_prop( 'enable_day_time_schedules' ), 'yes' ); ?> />
        <span><?php esc_html_e( 'Day/Time Schedules', 'advanced-coupons-for-woocommerce' ); ?></span>
    </label>
    <div class="options_group">
        <p class="form-field acfw_days_time_range_field">
            <label><?php esc_html_e( 'Days and time range', 'advanced-coupons-for-woocommerce' ); ?></label>
            <span class="days-time-fields">
                <?php foreach ( $day_time_fields as $key => $field_label ) : ?>
                <span class="days-time-field <?php echo esc_attr( $key ); ?>-time-field">
                    <label>
                        <input type="checkbox" name="acfw_day_time_schedules[<?php echo esc_attr( $key ); ?>][is_enabled]" value="yes" <?php checked( $schedules_data[ $key ]['is_enabled'], 'yes' ); ?> />
                        <span><?php echo esc_html( $field_label ); ?></span>
                    </label>
                    <span class="to-separator"><?php esc_html_e( 'from', 'advanced-coupons-for-woocommerce' ); ?></span>
                    <input class="start-time time-field" type="time" name="acfw_day_time_schedules[<?php echo esc_attr( $key ); ?>][start_time]" value="<?php echo esc_attr( $schedules_data[ $key ]['start_time'] ); ?>" pattern="[0-9]{2}:[0-9]{2}" placeholder="--:-- --">
                    <span class="to-separator"><?php esc_html_e( 'to', 'advanced-coupons-for-woocommerce' ); ?></span>
                    <input class="end-time time-field" type="time" name="acfw_day_time_schedules[<?php echo esc_attr( $key ); ?>][end_time]" value="<?php echo esc_attr( $schedules_data[ $key ]['end_time'] ); ?>" pattern="[0-9]{2}:[0-9]{2}" placeholder="--:-- --">
                </span>
                <?php endforeach; ?>
            </span>
            <?php echo wp_kses_post( wc_help_tip( __( 'Restrict the coupon to only be valid only on certain days and time of the week.', 'advanced-coupons-for-woocommerce' ) ) ); ?>
        </p>
        <p class="form-field acfw_invalid_days_time_error_message_field">
            <label><?php esc_html_e( 'Invalid days and time error message', 'advanced-coupons-for-woocommerce' ); ?></label>
            <?php echo wp_kses_post( wc_help_tip( __( 'Show a custom error message to customers that try to apply this coupon on days and/or times that are not valid.', 'advanced-coupons-for-woocommerce' ), true ) ); ?>
            <textarea class="short" name="_acfw_day_time_schedule_error_msg" placeholder="<?php echo esc_attr( $invalid_message_placeholder ); ?>" rows="2" cols="20"><?php echo wp_kses_post( $coupon->get_advanced_prop( 'day_time_schedule_error_msg' ) ); ?></textarea>
        </p>
    </div>
</div>
