'use strict';
jQuery(document).ready(function () {
    shortcodeInitButton();
});

function isValidEmailAddress(emailAddress) {
    var pattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/i;
    return pattern.test(emailAddress);
}

function shortcodeInitButton() {
    jQuery('.wcbwidget-button').unbind('click').on('click', function () {
        let $buttonSC = jQuery(this),
            shortcodeContainer = $buttonSC.closest('.woocommerce-coupon-box-widget'),
            email = shortcodeContainer.find('.wcbwidget-email').val(),
            con_first_name = shortcodeContainer.find('.wcb-input-name'),
            con_last_name = shortcodeContainer.find('.wcb-input-lname'),
            con_mobile = shortcodeContainer.find('.wcb-input-mobile'),
            con_birthday = shortcodeContainer.find('.wcb-input-birthday'),
            con_gender = shortcodeContainer.find('.wcb-input-gender'),
            con_additional = shortcodeContainer.find('.wcb-input-additional'),
            con_custom_field = shortcodeContainer.find('.wcb-custom-input-fields input, .wcb-custom-input-fields select'),
            require_fields = true;

        let showCouponcode = $buttonSC.data('show_coupon'),
            g_validate_response = shortcodeContainer.find('.wcb-recaptcha-field .wcb-g-validate-response').val();
        if (wcb_widget_params.enable_recaptcha && typeof grecaptcha !== 'undefined' && wcb_recaptcha_params.wcb_recaptcha_site_key && !g_validate_response) {
            shortcodeContainer.find('.wcb-recaptcha-field .wcb-recaptcha').addClass('wcb-warning-checkbox').focus();
            return false;
        } else {
            shortcodeContainer.find('.wcb-recaptcha-field .wcb-recaptcha').removeClass('wcb-warning-checkbox');
        }
        shortcodeContainer.find('.wcbwidget-email').removeClass('wcbwidget-invalid-email');
        shortcodeContainer.find('.wcbwidget-warning-message').html('');
        if (isValidEmailAddress(email)) {
            if (wcb_widget_params.wcb_gdpr_checkbox && shortcodeContainer.find('.wcbwidget-gdpr-checkbox').prop('checked') !== true) {
                shortcodeContainer.find('.wcbwidget-warning-message').html(wcb_widget_params.wcb_gdpr_warning);
                shortcodeContainer.find('.wcbwidget-gdpr-checkbox').addClass('wcb-warning-checkbox').focus();
                return false;
            } else {
                shortcodeContainer.find('.wcbwidget-gdpr-checkbox').removeClass('wcb-warning-checkbox');
            }
            jQuery.each(con_custom_field, function (f_key, f_value) {
                if (jQuery(f_value).hasClass('wcb-input-required') && '' === jQuery(f_value).val()) {
                    shortcodeContainer.find('.wcbwidget-warning-message').html(wcb_widget_params.wcb_empty_field_warning);
                    jQuery(f_value).addClass('wcbwidget-invalid-field');
                    $buttonSC.removeClass('wcbwidget-adding');
                    require_fields = false
                } else {

                    jQuery(f_value).removeClass('wcbwidget-invalid-field');
                }
            });
            if ( ! require_fields ) {
                shortcodeContainer.find('.wcb-custom-input-fields .wcbwidget-invalid-field').eq(0).focus();
                return;
            }
            shortcodeContainer.find('.wcbwidget-email').removeClass('wcbwidget-invalid-email');
            $buttonSC.addClass('wcbwidget-adding');
            $buttonSC.unbind();

            jQuery.ajax({
                type: 'POST',
                dataType: 'json',
                url: wcb_widget_params.ajaxurl,
                data: {
                    email: email,
                    wcb_nonce: 1,
                    language_ajax: wcb_widget_params.language_ajax || '',
                    show_coupon: showCouponcode,
                    first_name: con_first_name.val(),
                    last_name: con_last_name.val(),
                    mobile: con_mobile.val(),
                    birthday: con_birthday.val(),
                    gender: con_gender.val(),
                    additional: con_additional.val(),
                    g_validate_response: g_validate_response,
                },
                success: function (response) {
                    $buttonSC.removeClass('wcbwidget-adding');
                    if (response.status === 'subscribed') {
                        shortcodeContainer.find('.wcbwidget-newsletter').html(response.message);
                        if (showCouponcode) {
                            shortcodeContainer.find('.wcbwidget-newsletter').append(response.code);
                        }
                        var currentTime = parseInt(wcb_widget_params.wcb_current_time),
                            wcb_expire_subscribed = currentTime + parseInt(wcb_widget_params.wcb_expire_subscribed);
                        setCookie('woo_coupon_box', currentTime, wcb_expire_subscribed);
                    } else {
                        shortcodeContainer.find('.wcbwidget-warning-message').html(response.warning);
                        shortcodeContainer.find('.wcbwidget-email').addClass('wcbwidget-invalid-email').focus();
                        shortcodeInitButton();
                    }
                },
                error: function (data) {
                    $buttonSC.removeClass('wcbwidget-adding');
                    shortcodeInitButton();
                    setCookie('woo_coupon_box', '', -1);
                }
            });
        } else {
            if (!email) {
                shortcodeContainer.find('.wcbwidget-warning-message').html(wcb_widget_params.wcb_empty_email_warning);

            } else {
                shortcodeContainer.find('.wcbwidget-warning-message').html(wcb_widget_params.wcb_invalid_email_warning);

            }
            shortcodeContainer.find('.wcbwidget-email').addClass('wcbwidget-invalid-email').focus();
            $buttonSC.removeClass('wcbwidget-adding');
        }
    })
}

function setCookie(cname, cvalue, expire) {
    let d = new Date();
    d.setTime(d.getTime() + (expire * 1000));
    let expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

/**
 * Get Cookie
 * @param cname
 * @returns {*}
 */
function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
    }
    return "";
}
