jQuery(document).ready(function ($) {
    'use strict';
    let isIE = /*@cc_on!@*/false || !!document.documentMode;
    if (!isIE) {
        popup_hide();
        delay_show_modal();
        wcb_init_button();
        $(document).on('keypress', function (e) {
            if ($('#vi-md_wcb').hasClass('wcb-md-show') && !$('#vi-md_wcb').hasClass('wcb-collapse-after-close') && e.keyCode === 13) {
                $('.wcb-button').trigger('click');
            }
        });
        $('.wcb-email').on('keypress', function (e) {
            $('#vi-md_wcb').find('.wcb-warning-message-wrap').css({'visibility': 'hidden', 'opacity': 0});
        });
        $('#vi-md_wcb').find('input').on('click', function () {
            $('#vi-md_wcb').find('.wcb-warning-message-wrap').css({'visibility': 'hidden', 'opacity': 0});
        });
        $('.wcb-gdpr-checkbox').on('click', function () {
            if ($(this).prop('checked')) {
                $(this).removeClass('wcb-warning-checkbox');
            } else {
                $(this).addClass('wcb-warning-checkbox');
            }
        });
        callCouponBoxAgain();
        $('.wcb-coupon-box-small-icon-close').on('click', function (e) {
            e.stopPropagation();
            if (wcb_params.wcb_popup_position === 'left') {
                $('.wcb-coupon-box-small-icon-wrap').addClass('wcb-coupon-box-small-icon-hide-left');
            } else {
                $('.wcb-coupon-box-small-icon-wrap').addClass('wcb-coupon-box-small-icon-hide-right');
            }
            let currentTime = parseInt(wcb_params.wcb_current_time),
                wcb_expire = parseInt(wcb_params.wcb_expire);
            setCookie('woo_coupon_box', 'closed:' + currentTime, wcb_expire);
        });
        let prevScrollpos = jQuery('html').prop('scrollTop') || jQuery('body').prop('scrollTop');
        window.onscroll = function (e) {
            let $container = $('#vi-md_wcb');
            // $(e.target).scrollTop()
            if (wcb_params.wcb_on_close === 'top') {
                if ($container.hasClass('wcb-collapse-after-close')) {
                    let currentScrollPos = jQuery('html').prop('scrollTop') || jQuery('body').prop('scrollTop');
                    if (prevScrollpos < currentScrollPos) {
                        $('.wcb-collapse-top').css({'top': 0})
                    } else {
                        $('.wcb-collapse-top').css({'top': '-' + $('.wcb-collapse-top').css('height')});
                        $container.find('.wcb-recaptcha-field').addClass('wcb-hidden');
                    }
                    prevScrollpos = currentScrollPos;
                }
            } else if (wcb_params.wcb_on_close === 'bottom') {

                if ($container.hasClass('wcb-collapse-after-close')) {
                    let currentScrollPos = jQuery('html').prop('scrollTop') || jQuery('body').prop('scrollTop');
                    if (prevScrollpos > currentScrollPos) {
                        $('.wcb-collapse-bottom').css({'bottom': 0});
                    } else {
                        $('.wcb-collapse-bottom').css({'bottom': '-' + $('.wcb-collapse-bottom').css('height')});
                        $container.find('.wcb-recaptcha-field').addClass('wcb-hidden');
                    }
                    prevScrollpos = currentScrollPos;
                }
            }
        }
    }
    $(document).on('click', '.wcb-coupon-treasure', function () {
        $(this).select();
        document.execCommand('copy');
        alert(wcb_params.i18n_copied_to_clipboard);
    });

    $(document).on('wcb_hide_popup', function () {
        if ('function' === typeof LeafScene || 'function' === typeof Snowflake) {
            let xr = $('.wcb-md-overlay').attr('class').split(' ');
            if (xr.length > 1) {
                $('.wcb-md-overlay').removeClass(xr[1]).html('').addClass(xr[1]);
            }
        }
    })
});

function callCouponBoxAgain() {
    jQuery('.wcb-coupon-box-small-icon-wrap').on('click', function () {
        if (wcb_params.wcb_popup_type && !jQuery('#vi-md_wcb').hasClass(wcb_params.wcb_popup_type)) {
            jQuery('#vi-md_wcb').addClass(wcb_params.wcb_popup_type);
        }
        jQuery(document).on('keyup', closeOnEsc);
        jQuery('#vi-md_wcb').addClass('wcb-md-show');
        jQuery('html').addClass('wcb-html-scroll');
        if (wcb_params.wcb_popup_position === 'left') {
            jQuery(this).addClass('wcb-coupon-box-small-icon-hide-left');
        } else {
            jQuery(this).addClass('wcb-coupon-box-small-icon-hide-right');
        }
        jQuery(document).trigger('wcb_show_popup');
    });
    jQuery('.wcb-coupon-box-click-trigger').on('click', function () {
        if (wcb_params.wcb_popup_type && !jQuery('#vi-md_wcb').hasClass(wcb_params.wcb_popup_type)) {
            jQuery('#vi-md_wcb').addClass(wcb_params.wcb_popup_type);
        }
        jQuery(document).on('keyup', closeOnEsc);
        jQuery('#vi-md_wcb').addClass('wcb-md-show').removeClass('wcb-collapse-after-close wcb-collapse-top').attr('style', '');
        jQuery('html').addClass('wcb-html-scroll');
    })
}

function popup_hide() {
    jQuery('.wcb-md-close').on('click', function () {
        jQuery('#vi-md_wcb').find('.wcb-warning-message').html('');
        jQuery('#vi-md_wcb').find('.wcb-warning-message-wrap').css({'visibility': 'hidden', 'opacity': 0});
        if (!jQuery('#vi-md_wcb').hasClass('wcb-subscribed')) {
            switch (wcb_params.wcb_on_close) {
                case 'top':
                    if (jQuery('#vi-md_wcb').hasClass('wcb-collapse-after-close')) {
                        /*Popup icons to call coupon box again*/
                        if (wcb_params.wcb_popup_position === 'left') {
                            jQuery('.wcb-coupon-box-small-icon-wrap').removeClass('wcb-coupon-box-small-icon-hide-left');
                        } else {
                            jQuery('.wcb-coupon-box-small-icon-wrap').removeClass('wcb-coupon-box-small-icon-hide-right');
                        }

                        jQuery('#vi-md_wcb').removeClass('wcb-md-show').removeClass('wcb-collapse-after-close').removeClass('wcb-collapse-top');
                        if (wcb_params.wcb_popup_type && !jQuery('#vi-md_wcb').hasClass(wcb_params.wcb_popup_type)) {
                            setTimeout(function () {
                                jQuery('#vi-md_wcb').addClass(wcb_params.wcb_popup_type);
                            }, 300)
                        }
                        let addedStyle = jQuery('#vi-md_wcb').attr('style') ? jQuery('#vi-md_wcb').attr('style') : '';
                        if (addedStyle.length) {
                            let arr = addedStyle.split(';');
                            for (let i = 0; i < arr.length; i++) {
                                if (arr[i].length) {
                                    let arr1 = arr[i].split(':');
                                    if (arr1[0] === 'top') {
                                        arr.splice(i, 1);
                                    }
                                }
                            }
                            addedStyle = arr.join(';');
                            jQuery('#vi-md_wcb').attr('style', addedStyle);
                        }
                    } else {
                        jQuery('#vi-md_wcb').addClass('wcb-collapse-after-close').addClass('wcb-collapse-top');
                        jQuery('#vi-md_wcb').find('.wcb-recaptcha-field').addClass('wcb-hidden');
                        jQuery('html').removeClass('wcb-html-scroll');

                        if (wcb_params.wcb_popup_type && jQuery('#vi-md_wcb').hasClass(wcb_params.wcb_popup_type)) {
                            jQuery('#vi-md_wcb').removeClass(wcb_params.wcb_popup_type);
                        }
                    }
                    break;
                case 'bottom':
                    if (jQuery('#vi-md_wcb').hasClass('wcb-collapse-after-close')) {
                        /*Popup icons to call coupon box again*/
                        if (wcb_params.wcb_popup_position === 'left') {
                            jQuery('.wcb-coupon-box-small-icon-wrap').removeClass('wcb-coupon-box-small-icon-hide-left');
                        } else {
                            jQuery('.wcb-coupon-box-small-icon-wrap').removeClass('wcb-coupon-box-small-icon-hide-right');
                        }

                        jQuery('#vi-md_wcb').removeClass('wcb-md-show').removeClass('wcb-collapse-after-close').removeClass('wcb-collapse-bottom');
                        if (wcb_params.wcb_popup_type && !jQuery('#vi-md_wcb').hasClass(wcb_params.wcb_popup_type)) {
                            setTimeout(function () {
                                jQuery('#vi-md_wcb').addClass(wcb_params.wcb_popup_type);
                            }, 300)
                        }
                        let addedStyle = jQuery('#vi-md_wcb').attr('style') ? jQuery('#vi-md_wcb').attr('style') : '';
                        if (addedStyle.length) {
                            let arr = addedStyle.split(';');
                            for (let i = 0; i < arr.length; i++) {
                                if (arr[i].length) {
                                    let arr1 = arr[i].split(':');
                                    if (arr1[0] === 'bottom') {
                                        arr.splice(i, 1);
                                    }
                                }
                            }
                            addedStyle = arr.join(';');
                            jQuery('#vi-md_wcb').attr('style', addedStyle);
                        }
                    } else {
                        jQuery('#vi-md_wcb').addClass('wcb-collapse-after-close').addClass('wcb-collapse-bottom');
                        jQuery('#vi-md_wcb').find('.wcb-recaptcha-field').addClass('wcb-hidden');
                        jQuery('html').removeClass('wcb-html-scroll');

                        if (wcb_params.wcb_popup_type && jQuery('#vi-md_wcb').hasClass(wcb_params.wcb_popup_type)) {
                            jQuery('#vi-md_wcb').removeClass(wcb_params.wcb_popup_type);
                        }
                    }
                    break;
                default:
                    /*Popup icons to call coupon box again*/
                    if (wcb_params.wcb_popup_position === 'left') {
                        jQuery('.wcb-coupon-box-small-icon-wrap').removeClass('wcb-coupon-box-small-icon-hide-left');
                    } else {
                        jQuery('.wcb-coupon-box-small-icon-wrap').removeClass('wcb-coupon-box-small-icon-hide-right');
                    }
                    jQuery('#vi-md_wcb').removeClass('wcb-md-show');
                    jQuery('html').removeClass('wcb-html-scroll');

            }
        } else {
            jQuery('#vi-md_wcb').removeClass('wcb-md-show');
            jQuery('html').removeClass('wcb-html-scroll');
        }

        jQuery(document).unbind('keyup', closeOnEsc);

        jQuery(document).trigger('wcb_hide_popup');
    });
    jQuery('.wcb-md-overlay').on('click', function () {
        jQuery('.wcb-md-close').trigger('click');
    });

}

function closeOnEsc(e) {
    if (jQuery('#vi-md_wcb').hasClass('wcb-md-show') && e.keyCode === 27) {
        jQuery('.wcb-md-close').trigger('click');
    }
}

function isValidEmailAddress(emailAddress) {
    let pattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/i;
    if ('' !== wcb_params.wcb_restrict_domain) {
        let restrict_domain_data = wcb_params.wcb_restrict_domain,
            restrict_domain = restrict_domain_data.split('|'),
            emailRestrict = false;
        jQuery.each(restrict_domain, function (r_key, r_val) {
            if ( emailAddress.includes(r_val) ) {
                emailRestrict = true;
                return false;
            }
        });
        if (emailRestrict) {
            return pattern.test(emailAddress);
        } else {
            return false;
        }
    }
    return pattern.test(emailAddress);
}

function wcb_init_button() {
    let container = jQuery('#vi-md_wcb');
    container.find('.wcb-md-close-never-reminder').unbind('click').on('click', function () {
        if (wcb_params.wcb_never_reminder_enable == 1) {
            let currentTime = parseInt(wcb_params.wcb_current_time);
            setCookie('woo_coupon_box', 'closed:' + currentTime, 10 * 365 * 24 * 60 * 60);
            jQuery('.wcb-md-close').trigger('click');
            if (wcb_params.wcb_popup_position === 'left') {
                jQuery('.wcb-coupon-box-small-icon-wrap').addClass('wcb-coupon-box-small-icon-hide-left');
            } else {
                jQuery('.wcb-coupon-box-small-icon-wrap').addClass('wcb-coupon-box-small-icon-hide-right');
            }
        } else {
            jQuery('.wcb-md-close').trigger('click');
        }
    });
    container.find('.wcb-button').unbind('click').on('click', function () {
        let button = jQuery(this),
            email = container.find('.wcb-email').val(),
            wcb_input_name = container.find('.wcb-input-name').val(),
            wcb_input_lname = container.find('.wcb-input-lname').val(),
            wcb_input_mobile = container.find('.wcb-input-mobile').val(),
            wcb_input_birthday = container.find('.wcb-input-birthday').val(),
            wcb_input_gender = container.find('.wcb-input-gender').val(),
            wcb_input_additional = container.find('.wcb-input-additional').val(),
            g_validate_response = container.find('.wcb-recaptcha-field .wcb-g-validate-response').val(),
            accept_register_account = container.find('.wcb-register-account-checkbox:checked').length;

        container.find('.wcb-email').removeClass('wcb-invalid-email');
        container.find('.wcb-warning-message-wrap').css({'visibility': 'hidden', 'opacity': 0});
        container.find('.wcb-gdpr-checkbox').removeClass('wcb-warning-checkbox');

        if (!container.hasClass('wcb-collapse-after-close')) {
            if (wcb_params.wcb_input_name == 1 && wcb_params.wcb_input_name_required == 1 && !wcb_input_name) {
                container.find('.wcb-input-name').addClass('wcb-invalid-email').focus();
                return false;
            } else {
                container.find('.wcb-input-name').removeClass('wcb-invalid-email');
            }
            if (wcb_params.wcb_input_lname == 1 && wcb_params.wcb_input_lname_required == 1 && !wcb_input_lname) {
                container.find('.wcb-input-lname').addClass('wcb-invalid-email').focus();
                return false;
            } else {
                container.find('.wcb-input-lname').removeClass('wcb-invalid-email');
            }
            if (wcb_params.wcb_input_mobile == 1 && wcb_params.wcb_input_mobile_required == 1 && !wcb_input_mobile) {
                container.find('.wcb-input-mobile').addClass('wcb-invalid-email').focus();
                return false;
            } else {
                container.find('.wcb-input-mobile').removeClass('wcb-invalid-email');
            }
            if (wcb_params.wcb_input_birthday == 1 && wcb_params.wcb_input_birthday_required == 1 && !wcb_input_birthday) {
                container.find('.wcb-input-birthday').addClass('wcb-invalid-email').focus();
                return false;
            } else {
                container.find('.wcb-input-birthday').removeClass('wcb-invalid-email');
            }
            if (wcb_params.wcb_input_gender == 1 && wcb_params.wcb_input_gender_required == 1 && !wcb_input_gender) {
                container.find('.wcb-input-gender').addClass('wcb-invalid-email').focus();
                return false;
            } else {
                container.find('.wcb-input-gender').removeClass('wcb-invalid-email');
            }
            // if (wcb_params.wcb_input_gender == 1 && wcb_params.wcb_input_gender_required == 1 && !wcb_input_gender) {
            //     container.find('.wcb-input-gender').addClass('wcb-invalid-email').focus();
            //     return false;
            // } else {
            //     container.find('.wcb-input-gender').removeClass('wcb-invalid-email');
            // }
            if (wcb_params.wcb_input_additional == 1 && wcb_params.wcb_input_additional_required == 1 && !wcb_input_additional) {
                container.find('.wcb-input-additional').addClass('wcb-invalid-email').focus();
                return false;
            } else {
                container.find('.wcb-input-additional').removeClass('wcb-invalid-email');
            }
        }
        if (wcb_params.enable_recaptcha && typeof grecaptcha !== 'undefined' && wcb_recaptcha_params.wcb_recaptcha_site_key && !g_validate_response) {
            container.find('.wcb-recaptcha-field .wcb-recaptcha').addClass('wcb-warning-checkbox').focus();
            if (container.hasClass('wcb-collapse-after-close')) {
                container.find('.wcb-recaptcha-field').removeClass('wcb-hidden');
            }
            return false;
        } else {
            container.find('.wcb-recaptcha-field .wcb-recaptcha').removeClass('wcb-warning-checkbox');
        }
        if (isValidEmailAddress(email)) {
            if (wcb_params.wcb_gdpr_checkbox && container.find('.wcb-gdpr-checkbox').prop('checked') !== true) {
                container.find('.wcb-gdpr-checkbox').addClass('wcb-warning-checkbox').focus();
                return false;
            }
            container.find('.wcb-email').removeClass('wcb-invalid-email');
            button.addClass('wcb-adding');
            button.unbind();

            jQuery.ajax({
                type: 'POST',
                dataType: 'json',
                url: wcb_params.ajaxurl,
                data: {
                    wcb_input_name: wcb_input_name,
                    wcb_input_lname: wcb_input_lname,
                    wcb_input_mobile: wcb_input_mobile,
                    wcb_input_birthday: wcb_input_birthday,
                    wcb_input_gender: wcb_input_gender,
                    wcb_input_additional: wcb_input_additional,
                    g_validate_response: g_validate_response,
                    email: email,
                    language_ajax: wcb_params.language_ajax,
                    wcb_nonce: 1,
                    accept_register_account: accept_register_account
                },
                success: function (response) {
                    button.removeClass('wcb-adding');
                    if (response.status === 'subscribed') {
                        if (wcb_params.wcb_title_after_subscribing) {
                            container.find('.wcb-coupon-box-title').html(wcb_params.wcb_title_after_subscribing);
                        } else {
                            container.find('.wcb-coupon-box-title').html('').hide();
                        }
                        if (container.hasClass('wcb-collapse-after-close')) {
                            if (wcb_params.wcb_show_coupon && response.code) {
                                container.find('.wcb-coupon-content').html(response.code).css({'width': '40%'});
                                container.find('.wcb-coupon-treasure').focus(function () {
                                    jQuery(this).select();
                                });
                            } else {
                                container.find('.wcb-coupon-message').css({'width': '90%'});
                            }
                            container.find('.wcb-coupon-box-newsletter').hide();
                            container.find('.wcb-coupon-message').html(response.message);
                        } else {
                            if (wcb_params.wcb_show_coupon && response.code) {
                                container.find('.wcb-coupon-content').html(response.code);
                                container.find('.wcb-coupon-treasure').focus(function () {
                                    jQuery(this).select();
                                });
                            }
                            container.find('.wcb-coupon-box-newsletter').html(response.thankyou);
                            container.find('.wcb-coupon-message').html(response.message);
                        }
                        container.addClass('wcb-subscribed');
                        let currentTime = parseInt(response.wcb_current_time),
                            wcb_expire_subscribed = parseInt(wcb_params.wcb_expire_subscribed);
                        setCookie('woo_coupon_box', 'subscribed:' + currentTime, wcb_expire_subscribed);
                    } else {
                        container.find('.wcb-warning-message').html(response.warning);
                        container.find('.wcb-warning-message-wrap').css({'visibility': 'visible', 'opacity': 1});
                        if (response.g_validate_response) {
                            container.find('.wcb-recaptcha-field .wcb-recaptcha').addClass('wcb-warning-checkbox').focus();
                        } else {
                            container.find('.wcb-email').addClass('wcb-invalid-email').focus();
                        }
                        wcb_init_button();
                    }
                },
                error: function (data) {
                    button.removeClass('wcb-adding');
                    wcb_init_button();
                    setCookie('woo_coupon_box', '', -1);
                }
            });
        } else {
            if (!email) {
                container.find('.wcb-warning-message').html(wcb_params.wcb_empty_email_warning);
                container.find('.wcb-warning-message-wrap').css({'visibility': 'visible', 'opacity': 1});

            } else {
                container.find('.wcb-warning-message').html(wcb_params.wcb_invalid_email_warning);
                container.find('.wcb-warning-message-wrap').css({'visibility': 'visible', 'opacity': 1});

            }
            container.find('.wcb-email').addClass('wcb-invalid-email').focus();
            button.removeClass('wcb-adding');
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
    let name = cname + "=";
    let ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
    }
    return "";
}

function delay_show_modal() {
    if (wcb_params.wcb_select_popup === 'button') {
        jQuery('.wcb-open-popup').on('click', function () {
            couponBoxShow();
        });
    } else if (!getCookie('woo_coupon_box') && !jQuery('#vi-md_wcb').hasClass('wcb-md-show')) {
        switch (wcb_params.wcb_select_popup) {
            case 'time':
                setTimeout(function () {
                    couponBoxShow();
                }, wcb_params.wcb_popup_time * 1000);
                break;
            case 'scroll':
                let htmlHeight, scrollHeight, scrollTop, scrollRate;
                jQuery(document).on('scroll', function () {
                    if (!getCookie('woo_coupon_box')) {
                        htmlHeight = jQuery('html').prop('scrollHeight') || jQuery('body').prop('scrollHeight');
                        scrollHeight = window.innerHeight;
                        scrollTop = jQuery('html').prop('scrollTop') || jQuery('body').prop('scrollTop');
                        if (htmlHeight > 0) {
                            scrollRate = (scrollTop + scrollHeight) * 100 / (htmlHeight);
                            if (scrollRate > wcb_params.wcb_popup_scroll) {
                                couponBoxShow();
                            }
                        }
                    }
                });
                break;
            case 'exit':
                jQuery(window).on('mousemove', function (event) {
                    let scrollTop = jQuery('html').prop('scrollTop') || jQuery('body').prop('scrollTop');
                    let pageY = event.pageY;
                    if (!getCookie('woo_coupon_box')) {
                        if (pageY - scrollTop < 10) {
                            couponBoxShow();
                        }
                    }
                });
                break;
        }
    }

}

function couponBoxShow() {
    if (!jQuery('#vi-md_wcb').hasClass('wcb-md-show')) {
        if (wcb_params.wcb_popup_type && !jQuery('#vi-md_wcb').hasClass(wcb_params.wcb_popup_type)) {
            jQuery('#vi-md_wcb').addClass(wcb_params.wcb_popup_type);
        }
        jQuery(document).on('keyup', closeOnEsc);
        let currentTime = parseInt(wcb_params.wcb_current_time),
            wcb_expire = parseInt(wcb_params.wcb_expire);
        jQuery('#vi-md_wcb').addClass('wcb-md-show').trigger('renderReCaptcha');
        jQuery('html').addClass('wcb-html-scroll');
        setCookie('woo_coupon_box', 'shown:' + currentTime, wcb_expire);
        if (wcb_params.wcb_popup_position === 'left') {
            jQuery('.wcb-coupon-box-small-icon-wrap').addClass('wcb-coupon-box-small-icon-hide-left');
        } else {
            jQuery('.wcb-coupon-box-small-icon-wrap').addClass('wcb-coupon-box-small-icon-hide-right');
        }
        jQuery(document).trigger('wcb_show_popup');
    }
}