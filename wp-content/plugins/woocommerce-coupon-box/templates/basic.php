<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$settings = new VI_WOOCOMMERCE_COUPON_BOX_DATA();

$wcb_view_mode                   = $settings->get_params( 'wcb_view_mode' );
$wcb_show_coupon                 = $settings->get_params( 'wcb_show_coupon' );
$wcb_popup_type                  = $settings->get_params( 'wcb_popup_type' );
$wbs_title                       = $settings->get_params( 'wcb_title' );
$wcb_message                     = $settings->get_params( 'wcb_message' );
$wcb_follow_us                   = $settings->get_params( 'wcb_follow_us' );
$wcb_follow_us_after_subscribe   = $settings->get_params( 'wcb_follow_us_after_subscribe' );
$wcb_footer_text                 = $settings->get_params( 'wcb_footer_text' );
$wcb_footer_text_after_subscribe = $settings->get_params( 'wcb_footer_text_after_subscribe' );

$wcb_gdpr_checkbox         = $settings->get_params( 'wcb_gdpr_checkbox' );
$wcb_gdpr_checkbox_checked = $settings->get_params( 'wcb_gdpr_checkbox_checked' );
$wcb_gdpr_message          = $settings->get_params( 'wcb_gdpr_message' );

$wcb_register_account                  = $settings->get_params( 'wcb_register_account' );
$wcb_register_account_checkbox         = $settings->get_params( 'wcb_register_account_checkbox' );
$wcb_register_account_checkbox_checked = $settings->get_params( 'wcb_register_account_checkbox_checked' );
$wcb_register_account_message          = $settings->get_params( 'wcb_register_account_message' );

$wcb_button_close = $settings->get_params( 'wcb_button_close' );
$wcb_layout       = $settings->get_params( 'wcb_layout' );
$wcb_effect       = $settings->get_params( 'wcb_effect' );

$wcb_input_name              = $settings->get_params( 'wcb_input_name' );
$wcb_input_name_required     = $settings->get_params( 'wcb_input_name_required' );
$wcb_input_lname             = $settings->get_params( 'wcb_input_lname' );
$wcb_input_lname_required    = $settings->get_params( 'wcb_input_lname_required' );
$wcb_input_mobile            = $settings->get_params( 'wcb_input_mobile' );
$wcb_input_mobile_required   = $settings->get_params( 'wcb_input_mobile_required' );
$wcb_input_birthday          = $settings->get_params( 'wcb_input_birthday' );
$wcb_input_birthday_required = $settings->get_params( 'wcb_input_birthday_required' );
$wcb_input_gender            = $settings->get_params( 'wcb_input_gender' );
$wcb_input_gender_required   = $settings->get_params( 'wcb_input_gender_required' );
$wcb_input_additional            = $settings->get_params( 'wcb_input_additional' );
$wcb_input_additional_required   = $settings->get_params( 'wcb_input_additional_required' );
$wcb_input_additional_type   = $settings->get_params( 'wcb_input_additional_type' );
$wcb_input_additional_label   = $settings->get_params( 'wcb_input_additional_label' );
$wcb_input_additional_title  = $wcb_input_additional_required ? $wcb_input_additional_label . '(*)' : $wcb_input_additional_label;

$wcb_recaptcha              = $settings->get_params( 'wcb_recaptcha' );
$wcb_recaptcha_position     = $settings->get_params( 'wcb_recaptcha_position' );
$wcb_no_thank_button_enable = $settings->get_params( 'wcb_no_thank_button_enable' );
$wcb_no_thank_button_title  = $settings->get_params( 'wcb_no_thank_button_title' );

$wcb_input_fields = (int) $wcb_input_name + (int) $wcb_input_lname + (int) $wcb_input_mobile + (int) $wcb_input_birthday + (int) $wcb_input_gender + (int) $wcb_input_additional;

if ( $wcb_layout == 1 ) {
	?>
    <div class="wcb-md-modal wcb-coupon-box wcb-coupon-box-<?php echo $wcb_layout; ?> <?php echo esc_attr( $wcb_popup_type ); ?>"
         id="vi-md_wcb">
        <div class="wcb-content-wrap">
            <span class="wcb-md-close <?php echo esc_attr( $wcb_button_close ) ?>"></span>
            <div class="wcb-md-content">
                <div class="wcb-modal-header">
                    <span class="wcb-coupon-box-title"><?php echo $wbs_title; ?></span>
                </div>
                <div class="wcb-modal-body">
                    <div class="wcb-coupon-message">
						<?php echo do_shortcode( $wcb_message ); ?>
                    </div>
                    <div class="wcb-text-title wcb-text-follow-us"><?php echo $wcb_follow_us; ?></div>
                    <div class="wcb-sharing-container">
                        {socials}
                    </div>
                    <div class="wcb-coupon-content" style="<?php if ( ! $wcb_show_coupon ) {
						echo esc_attr( 'display:none;' );
					} ?>">
                    </div>

                    <div class="wcb-coupon-box-newsletter">
						<?php
						if ( $wcb_input_fields ) {
							$wcb_input_fields = 'wcb-input-fields-count-' . $wcb_input_fields;

							?>
                            <div class="wcb-custom-input-fields <?php echo esc_attr( $wcb_input_fields ); ?>">
								<?php
								if ( $wcb_input_name ) {
									?>
                                    <div class="wcb-input-field-item wcb-input-field-item-name">
                                        <input type="text" name="wcb_input_name" class="wcb-input-name"
                                               placeholder="<?php $wcb_input_name_required ? esc_html_e( 'Your first name(*)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your first name', 'woocommerce-coupon-box' ) ?>"
                                               title="<?php $wcb_input_name_required ? esc_html_e( 'Your first name(*required)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your first name', 'woocommerce-coupon-box' ) ?>">
                                    </div>
									<?php
								}
								if ( $wcb_input_lname ) {
									?>
                                    <div class="wcb-input-field-item wcb-input-field-item-lname">
                                        <input type="text" name="wcb_input_lname" class="wcb-input-lname"
                                               placeholder="<?php $wcb_input_lname_required ? esc_html_e( 'Your last name(*)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your last name', 'woocommerce-coupon-box' ) ?>"
                                               title="<?php $wcb_input_lname_required ? esc_html_e( 'Your last name(*required)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your last name', 'woocommerce-coupon-box' ) ?>">
                                    </div>
									<?php
								}
								if ( $wcb_input_mobile ) {
									?>
                                    <div class="wcb-input-field-item wcb-input-field-item-mobile">
                                        <input type="tel" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" name="wcb_input_mobile"
                                               class="wcb-input-mobile"
                                               placeholder="<?php $wcb_input_mobile_required ? esc_html_e( 'Your mobile(*)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your mobile', 'woocommerce-coupon-box' ) ?>"
                                               title="<?php $wcb_input_mobile_required ? esc_html_e( 'Your mobile(*required)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your mobile', 'woocommerce-coupon-box' ) ?>">
                                    </div>
									<?php
								}
								if ( $wcb_input_birthday ) {
									?>
                                    <div class="wcb-input-field-item wcb-input-field-item-birthday">
                                        <input type="date" name="wcb_input_birthday" class="wcb-input-birthday"
                                               placeholder="<?php $wcb_input_birthday_required ? esc_html_e( 'Your birthday(*)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your birthday', 'woocommerce-coupon-box' ) ?>"
                                               title="<?php $wcb_input_birthday_required ? esc_html_e( 'Your birthday(*required)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your birthday', 'woocommerce-coupon-box' ) ?>">
                                    </div>
									<?php
								}
								if ( $wcb_input_gender ) {
									?>
                                    <div class="wcb-input-field-item wcb-input-field-item-gender">
                                        <select name="wcb_input_gender" class="wcb-input-gender"
                                                title="<?php $wcb_input_gender_required ? esc_html_e( 'Your gender(*required)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your gender', 'woocommerce-coupon-box' ); ?>">
                                            <option value=""><?php $wcb_input_gender_required ? esc_html_e( 'Your gender(*)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your gender', 'woocommerce-coupon-box' ) ?></option>
                                            <option value="male"><?php esc_html_e( 'Male', 'woocommerce-coupon-box' ) ?></option>
                                            <option value="female"><?php esc_html_e( 'Female', 'woocommerce-coupon-box' ) ?></option>
                                            <option value="other"><?php esc_html_e( 'Other', 'woocommerce-coupon-box' ) ?></option>
                                        </select>
                                    </div>
									<?php
								}
								if ( $wcb_input_additional ) {
								    ?>
                                    <div class="wcb-input-field-item wcb-input-field-item-additional">
                                        <input type="text" name="wcb_input_additional" class="wcb-input-additional"
                                               placeholder="<?php echo esc_html( $wcb_input_additional_title ) ?>"
                                               title="<?php echo esc_html( $wcb_input_additional_label ) ;
                                               echo esc_html( $wcb_input_additional_required ? '(*)' : '' ) ?>">
                                    </div>
                                    <?php
								}
								?>
                            </div>
							<?php
						}
						?>
                        <div class="wcb-newsletter">
                            <div class="wcb-warning-message-wrap">
                                <span class="wcb-warning-message"></span>
                            </div>
                            <div class="wcb-newsletter-form">
                                <div class="wcb-input-group">
                                    <label for="wcb_email" class="wcb-coupon-box-hidden"></label>
                                    <input type="email"
                                           id="wcb_email"
                                           placeholder="<?php echo esc_attr( $settings->get_params( 'wcb_email_input_placeholder' ) . '(*)' ) ?>"
                                           class="wcb-form-control wcb-email"
                                           name="wcb_email">

                                    <div class="wcb-input-group-btn">
                                        <span class="wcb-btn wcb-btn-primary wcb-button"><?php echo $settings->get_params( 'wcb_button_text' ) ?></span>
                                    </div>
                                </div>
                            </div>

							<?php
							if ( $wcb_register_account && $wcb_register_account_checkbox ) {
								?>
                                <div class="wcb-register-account-field">
                                    <label for="wcb_register_account_checkbox" class="wcb-coupon-box-hidden"></label>
                                    <input type="checkbox" name="wcb_register_account_checkbox" id="wcb_register_account_checkbox"
                                           class="wcb-register-account-checkbox" <?php esc_attr_e( $wcb_register_account_checkbox_checked ? 'checked' : '' ); ?>>
                                    <span class="wcb-register-account-message"><?php echo $wcb_register_account_message; ?></span>
                                </div>
								<?php
							}

							if ( $wcb_gdpr_checkbox ) {
								?>
                                <div class="wcb-gdpr-field">
                                    <label for="wcb_gdpr_checkbox" class="wcb-coupon-box-hidden"></label>
                                    <input type="checkbox" name="wcb_gdpr_checkbox"
                                           id="wcb_gdpr_checkbox"
                                           class="wcb-gdpr-checkbox" <?php if ( $wcb_gdpr_checkbox_checked ) {
										echo esc_attr( 'checked' );
									} ?>>
                                    <span class="wcb-gdpr-message"><?php echo $wcb_gdpr_message; ?></span>
                                </div>
								<?php
							}
							if ( $wcb_recaptcha ) {
								?>
                                <div class="wcb-recaptcha-field">
                                    <div  class="wcb-recaptcha"></div>
                                    <input type="hidden" value="" class="wcb-g-validate-response">
                                </div>
								<?php
							}
							if ( $wcb_no_thank_button_enable ) {
								?>
                                <div class="wcb-md-close-never-reminder-field">
                                    <div class="wcb-md-close-never-reminder">
										<?php echo $wcb_no_thank_button_title ?>
                                    </div>
                                </div>
								<?php
							}
							?>
                            <div class="wcb-footer-text"><?php echo do_shortcode( $wcb_footer_text ); ?></div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
	<?php
} elseif ( $wcb_layout == 2 ) {
	?>
    <div class="wcb-md-modal wcb-coupon-box wcb-coupon-box-<?php echo $wcb_layout; ?> <?php echo esc_attr( $wcb_popup_type ); ?>"
         id="vi-md_wcb">
        <div class="wcb-content-wrap">
            <span class="wcb-md-close <?php echo esc_attr( $wcb_button_close ) ?>"></span>

            <div class="wcb-content-wrap-child">
                <div class="wcb-md-content">
                    <div class="wcb-modal-body">
                        <div class="wcb-modal-header">
                            <span class="wcb-coupon-box-title"><?php echo $wbs_title; ?></span>
                        </div>
                        <div class="wcb-coupon-message">
							<?php echo do_shortcode( $wcb_message ); ?>
                        </div>
                        <div class="wcb-text-title wcb-text-follow-us"><?php echo $wcb_follow_us; ?></div>
                        <div class="wcb-sharing-container">
                            {socials}
                        </div>
                        <div class="wcb-coupon-content" style="<?php if ( ! $wcb_show_coupon ) {
							echo esc_attr( 'display:none;' );
						} ?>">
                        </div>

                        <div class="wcb-coupon-box-newsletter">
							<?php
							if ( $wcb_input_fields ) {
								$wcb_input_fields = 'wcb-input-fields-count-' . $wcb_input_fields;

								?>
                                <div class="wcb-custom-input-fields <?php echo esc_attr( $wcb_input_fields ); ?>">
									<?php
									if ( $wcb_input_name ) {
										?>
                                        <div class="wcb-input-field-item wcb-input-field-item-name">
                                            <input type="text" name="wcb_input_name" class="wcb-input-name"
                                                   placeholder="<?php $wcb_input_name_required ? esc_html_e( 'Your first name(*)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your first name', 'woocommerce-coupon-box' ) ?>"
                                                   title="<?php $wcb_input_name_required ? esc_html_e( 'Your first name(*required)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your first name', 'woocommerce-coupon-box' ) ?>">
                                        </div>
										<?php
									}

									if ( $wcb_input_lname ) {
										?>
                                        <div class="wcb-input-field-item wcb-input-field-item-lname">
                                            <input type="text" name="wcb_input_lname" class="wcb-input-lname"
                                                   placeholder="<?php $wcb_input_lname_required ? esc_html_e( 'Your last name(*)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your last name', 'woocommerce-coupon-box' ) ?>"
                                                   title="<?php $wcb_input_lname_required ? esc_html_e( 'Your last name(*required)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your last name', 'woocommerce-coupon-box' ) ?>">
                                        </div>
										<?php
									}
									if ( $wcb_input_mobile ) {
										?>
                                        <div class="wcb-input-field-item wcb-input-field-item-mobile">
                                            <input type="tel" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}"
                                                   name="wcb_input_mobile"
                                                   class="wcb-input-mobile"
                                                   placeholder="<?php $wcb_input_mobile_required ? esc_html_e( 'Your mobile(*)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your mobile', 'woocommerce-coupon-box' ) ?>"
                                                   title="<?php $wcb_input_mobile_required ? esc_html_e( 'Your mobile(*required)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your mobile', 'woocommerce-coupon-box' ) ?>">
                                        </div>
										<?php
									}
									if ( $wcb_input_birthday ) {
										?>
                                        <div class="wcb-input-field-item wcb-input-field-item-birthday">
                                            <input type="date" name="wcb_input_birthday" class="wcb-input-birthday"
                                                   placeholder="<?php $wcb_input_birthday_required ? esc_html_e( 'Your birthday(*)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your birthday', 'woocommerce-coupon-box' ) ?>"
                                                   title="<?php $wcb_input_birthday_required ? esc_html_e( 'Your birthday(*required)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your birthday', 'woocommerce-coupon-box' ) ?>">
                                        </div>
										<?php
									}
									if ( $wcb_input_gender ) {
										?>
                                        <div class="wcb-input-field-item wcb-input-field-item-gender">
                                            <select name="wcb_input_gender" class="wcb-input-gender"
                                                    title="<?php $wcb_input_gender_required ? esc_html_e( 'Your gender(*required)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your gender', 'woocommerce-coupon-box' ); ?>">
                                                <option value=""><?php $wcb_input_gender_required ? esc_html_e( 'Your gender(*)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your gender', 'woocommerce-coupon-box' ) ?></option>
                                                <option value="male"><?php esc_html_e( 'Male', 'woocommerce-coupon-box' ) ?></option>
                                                <option value="female"><?php esc_html_e( 'Female', 'woocommerce-coupon-box' ) ?></option>
                                                <option value="other"><?php esc_html_e( 'Other', 'woocommerce-coupon-box' ) ?></option>
                                            </select>
                                        </div>
										<?php
									}
									if ( $wcb_input_additional ) {
										?>
                                        <div class="wcb-input-field-item wcb-input-field-item-additional">
                                            <input type="text" name="wcb_input_additional" class="wcb-input-additional"
                                                   placeholder="<?php echo esc_html( $wcb_input_additional_label ) ?>"
                                                   title="<?php echo esc_html( $wcb_input_additional_label ) ;
											       echo esc_html( $wcb_input_additional_required ? '(*)' : '' ) ?>">
                                        </div>
										<?php
									}
									?>
                                </div>
								<?php
							}
							?>
                            <div class="wcb-newsletter">
                                <div class="wcb-warning-message-wrap">
                                    <span class="wcb-warning-message"></span>
                                </div>
                                <div class="wcb-newsletter-form">
                                    <div class="wcb-input-group">
                                        <label for="wcb_email" class="wcb-coupon-box-hidden"></label>
                                        <input type="email"
                                               placeholder="<?php echo esc_attr( $settings->get_params( 'wcb_email_input_placeholder' ) . '(*)' ) ?>"
                                               class="wcb-form-control wcb-email"
                                               id="wcb_email"
                                               name="wcb_email">
                                        <?php
                                        if ( $wcb_recaptcha && 'before' == $wcb_recaptcha_position ) {
	                                        ?>
                                            <div class="wcb-recaptcha-field wcb-recaptcha-field-before">
                                                <div  class="wcb-recaptcha"></div>
                                                <input type="hidden" value="" class="wcb-g-validate-response">
                                            </div>
	                                        <?php
                                        }
                                        ?>

                                        <div class="wcb-input-group-btn">
                                            <span class="wcb-btn wcb-btn-primary wcb-button"><?php echo $settings->get_params( 'wcb_button_text' ) ?></span>
                                        </div>
                                    </div>
                                </div>

								<?php
								if ( $wcb_register_account && $wcb_register_account_checkbox ) {
									?>
                                    <div class="wcb-register-account-field">
                                        <label for="wcb_register_account_checkbox" class="wcb-coupon-box-hidden"></label>
                                        <input type="checkbox" name="wcb_register_account_checkbox" id="wcb_register_account_checkbox"
                                               class="wcb-register-account-checkbox" <?php esc_attr_e( $wcb_register_account_checkbox_checked ? 'checked' : '' ); ?>>
                                        <span class="wcb-register-account-message"><?php echo $wcb_register_account_message; ?></span>
                                    </div>
									<?php
								}

								if ( $wcb_gdpr_checkbox ) {
									?>
                                    <div class="wcb-gdpr-field">
                                        <label for="wcb_gdpr_checkbox" class="wcb-coupon-box-hidden"></label>
                                        <input type="checkbox" name="wcb_gdpr_checkbox"
                                               id="wcb_gdpr_checkbox"
                                               class="wcb-gdpr-checkbox" <?php if ( $wcb_gdpr_checkbox_checked ) {
											echo esc_attr( 'checked' );
										} ?>>
                                        <span class="wcb-gdpr-message"><?php echo $wcb_gdpr_message; ?></span>
                                    </div>
									<?php
								}
								if ( $wcb_recaptcha && 'after' == $wcb_recaptcha_position ) {
									?>
                                    <div class="wcb-recaptcha-field">
                                        <div  class="wcb-recaptcha"></div>
                                        <input type="hidden" value="" class="wcb-g-validate-response">
                                    </div>
									<?php
								}
								if ( $wcb_no_thank_button_enable ) {
									?>
                                    <div class="wcb-md-close-never-reminder-field">
                                        <div class="wcb-md-close-never-reminder">
											<?php echo $wcb_no_thank_button_title ?>
                                        </div>
                                    </div>
									<?php
								}
								?>
                                <div class="wcb-footer-text"><?php echo do_shortcode( $wcb_footer_text ); ?></div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="wcb-md-content-right"></div>
            </div>
        </div>
    </div>
	<?php
} elseif ( $wcb_layout == 3 ) {
	?>
    <div class="wcb-md-modal wcb-coupon-box wcb-coupon-box-<?php echo $wcb_layout; ?> <?php echo esc_attr( $wcb_popup_type ); ?>"
         id="vi-md_wcb">
        <div class="wcb-content-wrap">
            <span class="wcb-md-close <?php echo esc_attr( $wcb_button_close ) ?>"></span>
            <div class="wcb-md-content">

                <div class="wcb-modal-body">
                    <div class="wcb-modal-header">
                        <span class="wcb-coupon-box-title"><?php echo $wbs_title; ?></span>
                    </div>
                    <div class="wcb-coupon-message">
						<?php echo do_shortcode( $wcb_message ); ?>
                    </div>
                    <div class="wcb-text-title wcb-text-follow-us"><?php echo $wcb_follow_us; ?></div>
                    <div class="wcb-sharing-container">
                        {socials}
                    </div>
                    <div class="wcb-coupon-content" style="<?php if ( ! $wcb_show_coupon ) {
						echo esc_attr( 'display:none;' );
					} ?>">
                    </div>

                    <div class="wcb-coupon-box-newsletter">
						<?php
						if ( $wcb_input_fields ) {
							$wcb_input_fields = 'wcb-input-fields-count-' . $wcb_input_fields;

							?>
                            <div class="wcb-custom-input-fields <?php echo esc_attr( $wcb_input_fields ); ?>">
								<?php
								if ( $wcb_input_name ) {
									?>
                                    <div class="wcb-input-field-item wcb-input-field-item-name">
                                        <input type="text" name="wcb_input_name" class="wcb-input-name"
                                               placeholder="<?php $wcb_input_name_required ? esc_html_e( 'Your first name(*)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your first name', 'woocommerce-coupon-box' ) ?>"
                                               title="<?php $wcb_input_name_required ? esc_html_e( 'Your first name(*required)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your first name', 'woocommerce-coupon-box' ) ?>">
                                    </div>
									<?php
								}

								if ( $wcb_input_lname ) {
									?>
                                    <div class="wcb-input-field-item wcb-input-field-item-lname">
                                        <input type="text" name="wcb_input_lname" class="wcb-input-lname"
                                               placeholder="<?php $wcb_input_lname_required ? esc_html_e( 'Your last name(*)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your last name', 'woocommerce-coupon-box' ) ?>"
                                               title="<?php $wcb_input_lname_required ? esc_html_e( 'Your last name(*required)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your last name', 'woocommerce-coupon-box' ) ?>">
                                    </div>
									<?php
								}
								if ( $wcb_input_mobile ) {
									?>
                                    <div class="wcb-input-field-item wcb-input-field-item-mobile">
                                        <input type="tel" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" name="wcb_input_mobile"
                                               class="wcb-input-mobile"
                                               placeholder="<?php $wcb_input_mobile_required ? esc_html_e( 'Your mobile(*)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your mobile', 'woocommerce-coupon-box' ) ?>"
                                               title="<?php $wcb_input_mobile_required ? esc_html_e( 'Your mobile(*required)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your mobile', 'woocommerce-coupon-box' ) ?>">
                                    </div>
									<?php
								}
								if ( $wcb_input_birthday ) {
									?>
                                    <div class="wcb-input-field-item wcb-input-field-item-birthday">
                                        <input type="date" name="wcb_input_birthday" class="wcb-input-birthday"
                                               placeholder="<?php $wcb_input_birthday_required ? esc_html_e( 'Your birthday(*)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your birthday', 'woocommerce-coupon-box' ) ?>"
                                               title="<?php $wcb_input_birthday_required ? esc_html_e( 'Your birthday(*required)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your birthday', 'woocommerce-coupon-box' ) ?>">
                                    </div>
									<?php
								}
								if ( $wcb_input_gender ) {
									?>
                                    <div class="wcb-input-field-item wcb-input-field-item-gender">
                                        <select name="wcb_input_gender" class="wcb-input-gender"
                                                title="<?php $wcb_input_gender_required ? esc_html_e( 'Your gender(*required)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your gender', 'woocommerce-coupon-box' ); ?>">
                                            <option value=""><?php $wcb_input_gender_required ? esc_html_e( 'Your gender(*)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your gender', 'woocommerce-coupon-box' ) ?></option>
                                            <option value="male"><?php esc_html_e( 'Male', 'woocommerce-coupon-box' ) ?></option>
                                            <option value="female"><?php esc_html_e( 'Female', 'woocommerce-coupon-box' ) ?></option>
                                            <option value="other"><?php esc_html_e( 'Other', 'woocommerce-coupon-box' ) ?></option>
                                        </select>
                                    </div>
									<?php
								}
								if ( $wcb_input_additional ) {
									?>
                                    <div class="wcb-input-field-item wcb-input-field-item-additional">
                                        <input type="text" name="wcb_input_additional" class="wcb-input-additional"
                                               placeholder="<?php echo esc_html( $wcb_input_additional_label ) ?>"
                                               title="<?php echo esc_html( $wcb_input_additional_label ) ;
										       echo esc_html( $wcb_input_additional_required ? '(*)' : '' ) ?>">
                                    </div>
									<?php
								}
								?>
                            </div>
							<?php
						}
						?>
                        <div class="wcb-newsletter">
                            <div class="wcb-warning-message-wrap">
                                <span class="wcb-warning-message"></span>
                            </div>
                            <div class="wcb-newsletter-form">
                                <div class="wcb-input-group">
                                    <label for="wcb_email" class="wcb-coupon-box-hidden"></label>
                                    <input type="email"
                                           placeholder="<?php echo esc_attr( $settings->get_params( 'wcb_email_input_placeholder' ) . '(*)' ) ?>"
                                           class="wcb-form-control wcb-email"
                                           id="wcb_email"
                                           name="wcb_email">
                                    <?php
                                    if ( $wcb_recaptcha && 'before' == $wcb_recaptcha_position ) {
	                                    ?>
                                        <div class="wcb-recaptcha-field wcb-recaptcha-field-before">
                                            <div  class="wcb-recaptcha"></div>
                                            <input type="hidden" value="" class="wcb-g-validate-response">
                                        </div>
	                                    <?php
                                    }
                                    ?>

                                    <div class="wcb-input-group-btn">
                                        <span class="wcb-btn wcb-btn-primary wcb-button"><?php echo $settings->get_params( 'wcb_button_text' ) ?></span>
                                    </div>
                                </div>
                            </div>
							<?php

							if ( $wcb_register_account && $wcb_register_account_checkbox ) {
								?>
                                <div class="wcb-register-account-field">
                                    <label for="wcb_register_account_checkbox" class="wcb-coupon-box-hidden"></label>
                                    <input type="checkbox" name="wcb_register_account_checkbox" id="wcb_register_account_checkbox"
                                           class="wcb-register-account-checkbox" <?php esc_attr_e( $wcb_register_account_checkbox_checked ? 'checked' : '' ); ?>>
                                    <span class="wcb-register-account-message"><?php echo $wcb_register_account_message; ?></span>
                                </div>
								<?php
							}

							if ( $wcb_gdpr_checkbox ) {
								?>
                                <div class="wcb-gdpr-field">
                                    <label for="wcb_gdpr_checkbox" class="wcb-coupon-box-hidden"></label>
                                    <input type="checkbox" name="wcb_gdpr_checkbox"
                                           id="wcb_gdpr_checkbox"
                                           class="wcb-gdpr-checkbox" <?php if ( $wcb_gdpr_checkbox_checked ) {
										echo esc_attr( 'checked' );
									} ?>>
                                    <span class="wcb-gdpr-message"><?php echo $wcb_gdpr_message; ?></span>
                                </div>
								<?php
							}
							if ( $wcb_recaptcha && 'after' == $wcb_recaptcha_position ) {
								?>
                                <div class="wcb-recaptcha-field">
                                    <div  class="wcb-recaptcha"></div>
                                    <input type="hidden" value="" class="wcb-g-validate-response">
                                </div>
								<?php
							}
							if ( $wcb_no_thank_button_enable ) {
								?>
                                <div class="wcb-md-close-never-reminder-field">
                                    <div class="wcb-md-close-never-reminder">
										<?php echo $wcb_no_thank_button_title ?>
                                    </div>
                                </div>
								<?php
							}
							?>
                            <div class="wcb-footer-text"><?php echo do_shortcode( $wcb_footer_text ); ?></div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
	<?php
} elseif ( $wcb_layout == 4 ) {
	?>
    <div class="wcb-md-modal wcb-coupon-box wcb-coupon-box-<?php echo $wcb_layout; ?> <?php echo esc_attr( $wcb_popup_type ); ?>"
         id="vi-md_wcb">
        <div class="wcb-content-wrap">
            <span class="wcb-md-close <?php echo esc_attr( $wcb_button_close ) ?>"></span>
            <div class="wcb-md-content">

                <div class="wcb-modal-body">
                    <div class="wcb-modal-header">
                        <span class="wcb-coupon-box-title"><?php echo $wbs_title; ?></span>
                    </div>
                    <div class="wcb-modal-body-left">
                        <div class="wcb-coupon-message">
							<?php echo do_shortcode( $wcb_message ); ?>
                        </div>
                        <div class="wcb-text-title wcb-text-follow-us"><?php echo $wcb_follow_us; ?></div>
                        <div class="wcb-sharing-container">
                            {socials}
                        </div>
                        <div class="wcb-coupon-content" style="<?php if ( ! $wcb_show_coupon ) {
							echo esc_attr( 'display:none;' );
						} ?>">
                        </div>

                        <div class="wcb-coupon-box-newsletter">
							<?php
							if ( $wcb_input_fields ) {
								$wcb_input_fields = 'wcb-input-fields-count-' . $wcb_input_fields;

								?>
                                <div class="wcb-custom-input-fields <?php echo esc_attr( $wcb_input_fields ); ?>">
									<?php
									if ( $wcb_input_name ) {
										?>
                                        <div class="wcb-input-field-item wcb-input-field-item-name">
                                            <input type="text" name="wcb_input_name" class="wcb-input-name"
                                                   placeholder="<?php $wcb_input_name_required ? esc_html_e( 'Your first name(*)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your first name', 'woocommerce-coupon-box' ) ?>"
                                                   title="<?php $wcb_input_name_required ? esc_html_e( 'Your first name(*required)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your first name', 'woocommerce-coupon-box' ) ?>">
                                        </div>
										<?php
									}

									if ( $wcb_input_lname ) {
										?>
                                        <div class="wcb-input-field-item wcb-input-field-item-lname">
                                            <input type="text" name="wcb_input_lname" class="wcb-input-lname"
                                                   placeholder="<?php $wcb_input_lname_required ? esc_html_e( 'Your last name(*)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your last name', 'woocommerce-coupon-box' ) ?>"
                                                   title="<?php $wcb_input_lname_required ? esc_html_e( 'Your last name(*required)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your last name', 'woocommerce-coupon-box' ) ?>">
                                        </div>
										<?php
									}
									if ( $wcb_input_mobile ) {
										?>
                                        <div class="wcb-input-field-item wcb-input-field-item-mobile">
                                            <input type="tel" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}"
                                                   name="wcb_input_mobile"
                                                   class="wcb-input-mobile"
                                                   placeholder="<?php $wcb_input_mobile_required ? esc_html_e( 'Your mobile(*)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your mobile', 'woocommerce-coupon-box' ) ?>"
                                                   title="<?php $wcb_input_mobile_required ? esc_html_e( 'Your mobile(*required)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your mobile', 'woocommerce-coupon-box' ) ?>">
                                        </div>
										<?php
									}
									if ( $wcb_input_birthday ) {
										?>
                                        <div class="wcb-input-field-item wcb-input-field-item-birthday">
                                            <input type="date" name="wcb_input_birthday" class="wcb-input-birthday"
                                                   placeholder="<?php $wcb_input_birthday_required ? esc_html_e( 'Your birthday(*)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your birthday', 'woocommerce-coupon-box' ) ?>"
                                                   title="<?php $wcb_input_birthday_required ? esc_html_e( 'Your birthday(*required)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your birthday', 'woocommerce-coupon-box' ) ?>">
                                        </div>
										<?php
									}
									if ( $wcb_input_gender ) {
										?>
                                        <div class="wcb-input-field-item wcb-input-field-item-gender">
                                            <select name="wcb_input_gender" class="wcb-input-gender"
                                                    title="<?php $wcb_input_gender_required ? esc_html_e( 'Your gender(*required)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your gender', 'woocommerce-coupon-box' ); ?>">
                                                <option value=""><?php $wcb_input_gender_required ? esc_html_e( 'Your gender(*)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your gender', 'woocommerce-coupon-box' ) ?></option>
                                                <option value="male"><?php esc_html_e( 'Male', 'woocommerce-coupon-box' ) ?></option>
                                                <option value="female"><?php esc_html_e( 'Female', 'woocommerce-coupon-box' ) ?></option>
                                                <option value="other"><?php esc_html_e( 'Other', 'woocommerce-coupon-box' ) ?></option>
                                            </select>
                                        </div>
										<?php
									}
									if ( $wcb_input_additional ) {
										?>
                                        <div class="wcb-input-field-item wcb-input-field-item-additional">
                                            <input type="text" name="wcb_input_additional" class="wcb-input-additional"
                                                   placeholder="<?php echo esc_html( $wcb_input_additional_label ) ?>"
                                                   title="<?php echo esc_html( $wcb_input_additional_label ) ;
											       echo esc_html( $wcb_input_additional_required ? '(*)' : '' ) ?>">
                                        </div>
										<?php
									}
									?>
                                </div>
								<?php
							}
							?>
                            <div class="wcb-newsletter">
                                <div class="wcb-warning-message-wrap">
                                    <span class="wcb-warning-message"></span>
                                </div>
                                <div class="wcb-newsletter-form">
                                    <div class="wcb-input-group">
                                        <label for="wcb_email" class="wcb-coupon-box-hidden"></label>
                                        <input type="email"
                                               placeholder="<?php echo esc_attr( $settings->get_params( 'wcb_email_input_placeholder' ) . '(*)' ) ?>"
                                               class="wcb-form-control wcb-email"
                                               id="wcb_email"
                                               name="wcb_email">
                                        <?php
                                        if ( $wcb_recaptcha && 'before' == $wcb_recaptcha_position ) {
	                                        ?>
                                            <div class="wcb-recaptcha-field wcb-recaptcha-field-before">
                                                <div  class="wcb-recaptcha"></div>
                                                <input type="hidden" value="" class="wcb-g-validate-response">
                                            </div>
	                                        <?php
                                        }
                                        ?>

                                        <div class="wcb-input-group-btn">
                                            <span class="wcb-btn wcb-btn-primary wcb-button"><?php echo $settings->get_params( 'wcb_button_text' ) ?></span>
                                        </div>
                                    </div>
                                </div>

								<?php
								if ( $wcb_register_account && $wcb_register_account_checkbox ) {
									?>
                                    <div class="wcb-register-account-field">
                                        <label for="wcb_register_account_checkbox" class="wcb-coupon-box-hidden"></label>
                                        <input type="checkbox" name="wcb_register_account_checkbox" id="wcb_register_account_checkbox"
                                               class="wcb-register-account-checkbox" <?php esc_attr_e( $wcb_register_account_checkbox_checked ? 'checked' : '' ); ?>>
                                        <span class="wcb-register-account-message"><?php echo $wcb_register_account_message; ?></span>
                                    </div>
									<?php
								}

								if ( $wcb_gdpr_checkbox ) {
									?>
                                    <div class="wcb-gdpr-field">
                                        <label for="wcb_gdpr_checkbox" class="wcb-coupon-box-hidden"></label>
                                        <input type="checkbox" name="wcb_gdpr_checkbox"
                                               id="wcb_gdpr_checkbox"
                                               class="wcb-gdpr-checkbox" <?php if ( $wcb_gdpr_checkbox_checked ) {
											echo esc_attr( 'checked' );
										} ?>>
                                        <span class="wcb-gdpr-message"><?php echo $wcb_gdpr_message; ?></span>
                                    </div>
									<?php
								}
								if ( $wcb_recaptcha && 'after' == $wcb_recaptcha_position ) {
									?>
                                    <div class="wcb-recaptcha-field">
                                        <div  class="wcb-recaptcha"></div>
                                        <input type="hidden" value="" class="wcb-g-validate-response">
                                    </div>
									<?php
								}
								if ( $wcb_no_thank_button_enable ) {
									?>
                                    <div class="wcb-md-close-never-reminder-field">
                                        <div class="wcb-md-close-never-reminder">
											<?php echo $wcb_no_thank_button_title ?>
                                        </div>
                                    </div>
									<?php
								}
								?>
                                <div class="wcb-footer-text"><?php echo do_shortcode( $wcb_footer_text ); ?></div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
	<?php
} elseif ( $wcb_layout == 5 ) {
	?>
    <!--template 5-->
    <div class="wcb-md-modal wcb-coupon-box wcb-coupon-box-<?php echo $wcb_layout; ?> <?php echo esc_attr( $wcb_popup_type ); ?>"
         id="vi-md_wcb">
        <div class="wcb-content-wrap">
            <span class="wcb-md-close <?php echo esc_attr( $wcb_button_close ) ?>"></span>

            <div class="wcb-content-wrap-child">
                <div class="wcb-md-content-left"></div>
                <div class="wcb-md-content">
                    <div class="wcb-modal-body">
                        <div class="wcb-sharing-container">
                            {socials}
                        </div>
                        <div class="wcb-modal-header">
                            <span class="wcb-coupon-box-title"><?php echo $wbs_title; ?></span>
                        </div>
                        <div class="wcb-coupon-message">
							<?php echo do_shortcode( $wcb_message ); ?>
                        </div>

                        <div class="wcb-coupon-content" style="<?php if ( ! $wcb_show_coupon ) {
							echo esc_attr( 'display:none;' );
						} ?>">
                        </div>

                        <div class="wcb-coupon-box-newsletter">
							<?php
							if ( $wcb_input_fields ) {
								$wcb_input_fields = 'wcb-input-fields-count-' . $wcb_input_fields;

								?>
                                <div class="wcb-custom-input-fields <?php echo esc_attr( $wcb_input_fields ); ?>">
									<?php
									if ( $wcb_input_name ) {
										?>
                                        <div class="wcb-input-field-item wcb-input-field-item-name">
                                            <input type="text" name="wcb_input_name" class="wcb-input-name"
                                                   placeholder="<?php $wcb_input_name_required ? esc_html_e( 'Your first name(*)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your first name', 'woocommerce-coupon-box' ) ?>"
                                                   title="<?php $wcb_input_name_required ? esc_html_e( 'Your first name(*required)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your first name', 'woocommerce-coupon-box' ) ?>">
                                        </div>
										<?php
									}

									if ( $wcb_input_lname ) {
										?>
                                        <div class="wcb-input-field-item wcb-input-field-item-lname">
                                            <input type="text" name="wcb_input_lname" class="wcb-input-lname"
                                                   placeholder="<?php $wcb_input_lname_required ? esc_html_e( 'Your last name(*)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your last name', 'woocommerce-coupon-box' ) ?>"
                                                   title="<?php $wcb_input_lname_required ? esc_html_e( 'Your last name(*required)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your last name', 'woocommerce-coupon-box' ) ?>">
                                        </div>
										<?php
									}
									if ( $wcb_input_mobile ) {
										?>
                                        <div class="wcb-input-field-item wcb-input-field-item-mobile">
                                            <input type="tel" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}"
                                                   name="wcb_input_mobile"
                                                   class="wcb-input-mobile"
                                                   placeholder="<?php $wcb_input_mobile_required ? esc_html_e( 'Your mobile(*)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your mobile', 'woocommerce-coupon-box' ) ?>"
                                                   title="<?php $wcb_input_mobile_required ? esc_html_e( 'Your mobile(*required)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your mobile', 'woocommerce-coupon-box' ) ?>">
                                        </div>
										<?php
									}
									if ( $wcb_input_birthday ) {
										?>
                                        <div class="wcb-input-field-item wcb-input-field-item-birthday">
                                            <input type="date" name="wcb_input_birthday" class="wcb-input-birthday"
                                                   placeholder="<?php $wcb_input_birthday_required ? esc_html_e( 'Your birthday(*)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your birthday', 'woocommerce-coupon-box' ) ?>"
                                                   title="<?php $wcb_input_birthday_required ? esc_html_e( 'Your birthday(*required)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your birthday', 'woocommerce-coupon-box' ) ?>">
                                        </div>
										<?php
									}
									if ( $wcb_input_gender ) {
										?>
                                        <div class="wcb-input-field-item wcb-input-field-item-gender">
                                            <select name="wcb_input_gender" class="wcb-input-gender"
                                                    title="<?php $wcb_input_gender_required ? esc_html_e( 'Your gender(*required)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your gender', 'woocommerce-coupon-box' ); ?>">
                                                <option value=""><?php $wcb_input_gender_required ? esc_html_e( 'Your gender(*)', 'woocommerce-coupon-box' ) : esc_html_e( 'Your gender', 'woocommerce-coupon-box' ) ?></option>
                                                <option value="male"><?php esc_html_e( 'Male', 'woocommerce-coupon-box' ) ?></option>
                                                <option value="female"><?php esc_html_e( 'Female', 'woocommerce-coupon-box' ) ?></option>
                                                <option value="other"><?php esc_html_e( 'Other', 'woocommerce-coupon-box' ) ?></option>
                                            </select>
                                        </div>
										<?php
									}
									if ( $wcb_input_additional ) {
										?>
                                        <div class="wcb-input-field-item wcb-input-field-item-additional">
                                            <input type="text" name="wcb_input_additional" class="wcb-input-additional"
                                                   placeholder="<?php echo esc_html( $wcb_input_additional_label ) ?>"
                                                   title="<?php echo esc_html( $wcb_input_additional_label ) ;
											       echo esc_html( $wcb_input_additional_required ? '(*)' : '' ) ?>">
                                        </div>
										<?php
									}
									?>
                                </div>
								<?php
							}
							?>
                            <div class="wcb-newsletter">
                                <div class="wcb-warning-message-wrap">
                                    <span class="wcb-warning-message"></span>
                                </div>
                                <div class="wcb-newsletter-form">
                                    <div class="wcb-input-group">
                                        <label for="wcb_email" class="wcb-coupon-box-hidden"></label>
                                        <input type="email"
                                               placeholder="<?php echo esc_attr( $settings->get_params( 'wcb_email_input_placeholder' ) . '(*)' ) ?>"
                                               class="wcb-form-control wcb-email"
                                               id="wcb_email"
                                               name="wcb_email">

                                        <div class="wcb-input-group-btn">
                                            <span class="wcb-btn wcb-btn-primary wcb-button"><?php echo $settings->get_params( 'wcb_button_text' ) ?></span>
                                        </div>
                                    </div>
                                </div>

								<?php
								if ( $wcb_register_account && $wcb_register_account_checkbox ) {
									?>
                                    <div class="wcb-register-account-field">
                                        <label for="wcb_register_account_checkbox" class="wcb-coupon-box-hidden"></label>
                                        <input type="checkbox" name="wcb_register_account_checkbox" id="wcb_register_account_checkbox"
                                               class="wcb-register-account-checkbox" <?php esc_attr_e( $wcb_register_account_checkbox_checked ? 'checked' : '' ); ?>>
                                        <span class="wcb-register-account-message"><?php echo $wcb_register_account_message; ?></span>
                                    </div>
									<?php
								}

								if ( $wcb_gdpr_checkbox ) {
									?>
                                    <div class="wcb-gdpr-field">
                                        <label for="wcb_gdpr_checkbox" class="wcb-coupon-box-hidden"></label>
                                        <input type="checkbox" name="wcb_gdpr_checkbox"
                                               id="wcb_gdpr_checkbox"
                                               class="wcb-gdpr-checkbox" <?php if ( $wcb_gdpr_checkbox_checked ) {
											echo esc_attr( 'checked' );
										} ?>>
                                        <span class="wcb-gdpr-message"><?php echo $wcb_gdpr_message; ?></span>
                                    </div>
									<?php
								}
								if ( $wcb_recaptcha ) {
									?>
                                    <div class="wcb-recaptcha-field">
                                        <div  class="wcb-recaptcha"></div>
                                        <input type="hidden" value="" class="wcb-g-validate-response">
                                    </div>
									<?php
								}
								if ( $wcb_no_thank_button_enable ) {
									?>
                                    <div class="wcb-md-close-never-reminder-field">
                                        <div class="wcb-md-close-never-reminder">
											<?php echo $wcb_no_thank_button_title ?>
                                        </div>
                                    </div>
									<?php
								}
								?>
                                <div class="wcb-footer-text"><?php echo do_shortcode( $wcb_footer_text ); ?></div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
	<?php
}
switch ( $wcb_effect ) {
	case 'wcb-falling-snow-1':
		?>
        <div class="wcb-md-overlay">
            <div class="wcb-weather wcb-weather-snow"></div>
        </div>
		<?php
		break;
	case 'wcb-falling-rain':
		?>
        <div class="wcb-md-overlay">
            <div class="wcb-weather wcb-weather-rain"></div>
        </div>
		<?php
		break;
	case 'snowflakes':
		?>
        <div class="wcb-md-overlay">
            <div class="wcb-background-effect-snowflakes" aria-hidden="true">
                <div class="wcb-background-effect-snowflake">
                    ❅
                </div>
                <div class="wcb-background-effect-snowflake">
                    ❅
                </div>
                <div class="wcb-background-effect-snowflake">
                    ❆
                </div>
                <div class="wcb-background-effect-snowflake">
                    ❄
                </div>
                <div class="wcb-background-effect-snowflake">
                    ❅
                </div>
                <div class="wcb-background-effect-snowflake">
                    ❆
                </div>
                <div class="wcb-background-effect-snowflake">
                    ❄
                </div>
                <div class="wcb-background-effect-snowflake">
                    ❅
                </div>
                <div class="wcb-background-effect-snowflake">
                    ❆
                </div>
                <div class="wcb-background-effect-snowflake">
                    ❄
                </div>
                <div class="wcb-background-effect-snowflake">
                    ❅
                </div>
                <div class="wcb-background-effect-snowflake">
                    ❅
                </div>
                <div class="wcb-background-effect-snowflake">
                    ❆
                </div>
                <div class="wcb-background-effect-snowflake">
                    ❄
                </div>
                <div class="wcb-background-effect-snowflake">
                    ❅
                </div>
                <div class="wcb-background-effect-snowflake">
                    ❆
                </div>
                <div class="wcb-background-effect-snowflake">
                    ❄
                </div>
                <div class="wcb-background-effect-snowflake">
                    ❅
                </div>
                <div class="wcb-background-effect-snowflake">
                    ❆
                </div>
                <div class="wcb-background-effect-snowflake">
                    ❄
                </div>
            </div>
        </div>
		<?php
		break;
	case 'snowflakes-1':
		?>
        <div class="wcb-md-overlay">
            <div class="wcb-background-effect-snowflakes-1" aria-hidden="true">
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>

		<?php
		break;
	case 'snowflakes-2-1':
	case 'snowflakes-2-2':
	case 'snowflakes-2-3':
		?>
        <div class="wcb-md-overlay <?php echo 'wcb-background-effect-snowflakes-2 wcb-background-effect-' . $wcb_effect ?>">
            <i></i>
        </div>
		<?php
		break;
	default:
		?>
        <div class="wcb-md-overlay <?php echo $wcb_effect ?>"></div>
	<?php
}
