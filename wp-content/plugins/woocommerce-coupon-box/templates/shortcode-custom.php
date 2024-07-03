<?php
if ( ! isset( $show_first_name ) || ! isset( $show_last_name ) || ! isset( $show_mobile )
     || ! isset( $show_birthday ) || ! isset( $show_gender ) || ! isset( $show_additional ) ) {
	return;
}
$wcb_input_fields = (int) $show_first_name + (int) $show_last_name + (int) $show_mobile + (int) $show_birthday + (int) $show_gender + (int) $show_additional;
$wcb_input_name_required     = $this->settings->get_params( 'wcb_input_name_required' );
$wcb_input_lname_required    = $this->settings->get_params( 'wcb_input_lname_required' );
$wcb_input_mobile_required   = $this->settings->get_params( 'wcb_input_mobile_required' );
$wcb_input_birthday_required = $this->settings->get_params( 'wcb_input_birthday_required' );
$wcb_input_gender_required   = $this->settings->get_params( 'wcb_input_gender_required' );
$wcb_input_additional_required   = $this->settings->get_params( 'wcb_input_additional_required' );
$wcb_input_additional_label   = $this->settings->get_params( 'wcb_input_additional_label' );
$wcb_input_additional_title  = $wcb_input_additional_required ? $wcb_input_additional_label . '(*)' : $wcb_input_additional_label;
?>
    <div class="wcb-custom-input-fields <?php echo esc_attr( $wcb_input_fields ); ?>">
		<?php
		if ( $show_first_name ) {
			?>
            <div class="wcb-input-field-item wcb-input-field-item-name">
                <input type="text" name="wcb_input_name" class="wcb-input-name<?php echo $wcb_input_name_required ? ' wcb-input-required' : '' ?>"
                       placeholder="<?php $wcb_input_name_required ? esc_html_e( 'Your first name(*)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your first name', 'woocommerce-coupon-box' ) ?>"
                       title="<?php $wcb_input_name_required ? esc_html_e( 'Your first name(*required)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your first name', 'woocommerce-coupon-box' ) ?>">
            </div>
			<?php
		}
		if ( $show_last_name ) {
			?>
            <div class="wcb-input-field-item wcb-input-field-item-lname">
                <input type="text" name="wcb_input_lname" class="wcb-input-lname<?php echo $wcb_input_lname_required ? ' wcb-input-required' : '' ?>"
                       placeholder="<?php $wcb_input_lname_required ? esc_html_e( 'Your last name(*)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your last name', 'woocommerce-coupon-box' ) ?>"
                       title="<?php $wcb_input_lname_required ? esc_html_e( 'Your last name(*required)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your last name', 'woocommerce-coupon-box' ) ?>">
            </div>
			<?php
		}
		if ( $show_mobile ) {
			?>
            <div class="wcb-input-field-item wcb-input-field-item-mobile">
                <input type="tel" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" name="wcb_input_mobile"
                       class="wcb-input-mobile<?php echo $wcb_input_mobile_required ? ' wcb-input-required' : '' ?>"
                       placeholder="<?php $wcb_input_mobile_required ? esc_html_e( 'Your mobile(*)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your mobile', 'woocommerce-coupon-box' ) ?>"
                       title="<?php $wcb_input_mobile_required ? esc_html_e( 'Your mobile(*required)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your mobile', 'woocommerce-coupon-box' ) ?>">
            </div>
			<?php
		}
		if ( $show_birthday ) {
			?>
            <div class="wcb-input-field-item wcb-input-field-item-birthday">
                <input type="date" name="wcb_input_birthday" class="wcb-input-birthday<?php echo $wcb_input_birthday_required ? ' wcb-input-required' : '' ?>"
                       placeholder="<?php $wcb_input_birthday_required ? esc_html_e( 'Your birthday(*)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your birthday', 'woocommerce-coupon-box' ) ?>"
                       title="<?php $wcb_input_birthday_required ? esc_html_e( 'Your birthday(*required)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your birthday', 'woocommerce-coupon-box' ) ?>">
            </div>
			<?php
		}
		if ( $show_gender ) {
			?>
            <div class="wcb-input-field-item wcb-input-field-item-gender">
                <select name="wcb_input_gender" class="wcb-input-gender<?php echo $wcb_input_gender_required ? ' wcb-input-required' : '' ?>"
                        title="<?php $wcb_input_gender_required ? esc_html_e( 'Your gender(*required)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your gender', 'woocommerce-coupon-box' ); ?>">
                    <option value=""><?php $wcb_input_gender_required ? esc_html_e( 'Your gender(*)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your gender', 'woocommerce-coupon-box' ) ?></option>
                    <option value="male"><?php esc_html_e( 'Male', 'woocommerce-coupon-box' ) ?></option>
                    <option value="female"><?php esc_html_e( 'Female', 'woocommerce-coupon-box' ) ?></option>
                    <option value="other"><?php esc_html_e( 'Other', 'woocommerce-coupon-box' ) ?></option>
                </select>
            </div>
			<?php
		}
		if ( $show_additional ) {
			?>
            <div class="wcb-input-field-item wcb-input-field-item-additional">
                <input type="text" name="wcb_input_additional" class="wcb-input-additional<?php echo $wcb_input_additional_required ? ' wcb-input-required' : '' ?>"
                       placeholder="<?php echo esc_html( $wcb_input_additional_title ) ?>"
                       title="<?php echo esc_html( $wcb_input_additional_label );
				       echo esc_html( $wcb_input_additional_required ? '(*)' : '' ) ?>">
            </div>
			<?php
		}
		?>
    </div>
<?php