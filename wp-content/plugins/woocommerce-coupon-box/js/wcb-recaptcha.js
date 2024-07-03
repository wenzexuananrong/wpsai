jQuery(document).ready(function ($) {
    'use strict';
    let container = jQuery('#vi-md_wcb');
    container.on('renderReCaptcha', function () {
        if (wcb_recaptcha_params.wcb_recaptcha == 1) {
            if (wcb_recaptcha_params.wcb_recaptcha_version == 2) {
                wcb_reCaptchaV2Onload();
            } else {
                wcb_reCaptchaV3Onload();
                container.find('.wcb-recaptcha-field').hide();
            }
        }
    });

    window.addEventListener('load', function () {
        if (wcb_recaptcha_params.wcb_recaptcha == 1) {
            if (wcb_recaptcha_params.wcb_recaptcha_version == 2) {
                wcb_reCaptchaV2Onload();
            } else {
                wcb_reCaptchaV3Onload();
                container.find('.wcb-recaptcha-field').hide();
            }
        }
    });
});

function wcb_validateRecaptcha(response) {
    if (response) {
        jQuery('.wcb-recaptcha-field .wcb-g-validate-response').val(response);
    }
}

function wcb_expireRecaptcha() {
    jQuery('.wcb-recaptcha-field .wcb-g-validate-response').val(null);
    if (wcb_recaptcha_params.wcb_layout == 3) {
        let old_width = jQuery('.wcb-coupon-box-3 .wcb-recaptcha > div').width();
        let parent_width = jQuery('.wcb-coupon-box-3 .wcb-recaptcha').width();
        jQuery('.wcb-coupon-box-3 .wcb-recaptcha > div').css({transform: 'scale(' + parent_width / old_width + ',1)'});
    }
}

function wcb_reCaptchaV3Onload() {
    if (typeof grecaptcha !== 'undefined') {
        grecaptcha.ready(function () {
            grecaptcha.execute(wcb_recaptcha_params.wcb_recaptcha_site_key, {action: 'homepage'}).then(function (token) {
                wcb_validateRecaptcha(token);
            })
        });
    }
}

function wcb_reCaptchaV2Onload() {
    if (jQuery('.wcb-recaptcha').length == 0 || jQuery.find('.wcb-recaptcha iframe').length) {
        return true;
    }
    for (let i = 0; i < jQuery('.wcb-recaptcha').length; i++) {
        grecaptcha.render(jQuery('.wcb-recaptcha')[i], {

            'sitekey': wcb_recaptcha_params.wcb_recaptcha_site_key,

            'callback': wcb_validateRecaptcha,

            'expired-callback': wcb_expireRecaptcha,

            'theme': wcb_recaptcha_params.wcb_recaptcha_secret_theme,

            'isolated': false
        });

    }
    if (wcb_recaptcha_params.wcb_layout == 3) {
        let old_width = jQuery('.wcb-coupon-box-3 .wcb-recaptcha > div').width();
        let parent_width = jQuery('.wcb-coupon-box-3 .wcb-recaptcha-field > div').width();
        jQuery('.wcb-coupon-box-3 .wcb-recaptcha > div').css({transform: 'scale(' + parent_width / old_width + ',1)'});
    }
}