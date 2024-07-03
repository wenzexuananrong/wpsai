jQuery(document).ready(function ($) {
    'use strict';
    let xr = $('.wcb-md-overlay').attr('class').split(' ');
    let leafContainer, leaves;
    if (xr.length > 1) {
        switch (xr[1]) {
            case 'wcb-falling-leaves':
                leafContainer = document.querySelector('.wcb-falling-leaves');
                leaves = new LeafScene(leafContainer);
                leaves.init();
                leaves.render();
                $('.wcb-leaf-scene div').attr('class', '').addClass('wcb-falling-leaves-leaves');
                break;
            case 'wcb-falling-leaves-1':
                leafContainer = document.querySelector('.wcb-falling-leaves');
                leaves = new LeafScene(leafContainer);
                leaves.init();
                leaves.render();
                $('.wcb-leaf-scene div').attr('class', '').addClass('wcb-falling-leaves-leaves-1');
                break;
            case 'wcb-falling-heart':
                leafContainer = document.querySelector('.wcb-falling-leaves');
                leaves = new LeafScene(leafContainer);
                leaves.init();
                leaves.render();
                $('.wcb-leaf-scene div').attr('class', '').addClass('wcb-falling-leaves-heart');
                break;
            case 'wcb-falling-snow':
                Snowflake.init($('.wcb-falling-snow')[0]);
                break;
        }
    }
});
(function ($) {
    'use strict';
    wp.customize.bind('preview-ready', function () {
        wp.customize.preview.bind('active', function () {
            wp.customize.preview.send('wcb_update_language', woocommerce_coupon_box_design_params.language);
        })
    });
    let language_control = woocommerce_coupon_box_design_params.language_control;
    wp.customize('woo_coupon_box_params[wcb_layout]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box').removeClass('wcb-md-show').removeClass('wcb-current-layout');
            $('.wcb-coupon-box-' + newval).addClass('wcb-md-show').addClass('wcb-current-layout');
        });
    });
    /*Button close*/
    wp.customize('woo_coupon_box_params[wcb_button_close]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-md-close').attr('class', 'wcb-md-close ' + newval);
            $('.woo_coupon_box_params-wcb_button_close label').removeClass('wcb-radio-icons-active');
            $('.woo_coupon_box_params-wcb_button_close .' + newval).parent().addClass('wcb-radio-icons-active');
        });
    });
    wp.customize('woo_coupon_box_params[wcb_button_close_color]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-md-close').css({'color': newval});
        });
    });
    wp.customize('woo_coupon_box_params[wcb_button_close_bg_color]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-md-close').css({'background-color': newval});
        });
    });
    wp.customize('woo_coupon_box_params[wcb_button_close_size]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-md-close').css({'font-size': newval + 'px'});
        });
    });
    wp.customize('woo_coupon_box_params[wcb_button_close_width]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-md-close').css({'width': newval + 'px', 'line-height': newval + 'px'});
        });
    });
    wp.customize('woo_coupon_box_params[wcb_button_close_border_radius]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-md-close').css({'border-radius': newval + 'px'});
        });
    });
    wp.customize('woo_coupon_box_params[wcb_button_close_position_x]', function (value) {
        value.bind(function (newval) {
            newval = -newval;
            $('.wcb-coupon-box .wcb-md-close').css({'right': newval + 'px'});
        });
    });
    wp.customize('woo_coupon_box_params[wcb_button_close_position_y]', function (value) {
        value.bind(function (newval) {
            newval = -newval;
            $('.wcb-coupon-box .wcb-md-close').css({'top': newval + 'px'});
        });
    });

    wp.customize('woo_coupon_box_params[wcb_view_mode]', function (value) {
        value.bind(function (newval) {
            if (newval === '1') {
                $('.wcb-view-before-subscribe').show();
                $('.wcb-view-after-subscribe').hide();
            } else {
                $('.wcb-view-before-subscribe').hide();
                $('.wcb-view-after-subscribe').show();
            }
        });
    });

    wp.customize('woo_coupon_box_params[wcb_title' + language_control + ']', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-modal-header.wcb-view-before-subscribe .wcb-coupon-box-title').html(newval);
        });
    });
    wp.customize('woo_coupon_box_params[wcb_title_after_subscribing' + language_control + ']', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-modal-header.wcb-view-after-subscribe .wcb-coupon-box-title').html(newval);
        });
    });

    wp.customize('woo_coupon_box_params[wcb_bg_header]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-modal-header').css('backgroundColor', newval);
        });
    });
    wp.customize('woo_coupon_box_params[wcb_header_bg_img]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-modal-header').css('background-image', 'url(' + newval + ')');
        });
    });

    wp.customize('woo_coupon_box_params[wcb_header_bg_img_repeat]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-modal-header').css('background-repeat', newval);
        });
    });

    wp.customize('woo_coupon_box_params[wcb_header_bg_img_size]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-modal-header').css('background-size', newval);
        });
    });

    wp.customize('woo_coupon_box_params[wcb_header_bg_img_position]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-modal-header').css('background-position', newval);
        });
    });

    wp.customize('woo_coupon_box_params[wcb_color_header]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-modal-header').css('color', newval);
        });
    });

    wp.customize('woo_coupon_box_params[wcb_title_size]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-modal-header').css({
                'font-size': newval + 'px',
                'line-height': newval + 'px'
            });
        });
    });
    wp.customize('woo_coupon_box_params[wcb_header_font]', function (value) {
        value.bind(function (newval) {
            let newData = JSON.parse(newval);
            let $font_f = newData.font;
            if ($font_f) {
                $font_f = $font_f.replace(/ /g, '+');
                let $src = '//fonts.googleapis.com/css?family=' + $font_f + ':300,400,700';
                $('<link rel="stylesheet" type="text/css" href="' + $src + '">').appendTo($('head'));
                $('#woocommerce-coupon-box-header-font').html('.wcb-coupon-box .wcb-modal-header{font-family:' + newData.font + ' !important;}');
            } else {
                $('#woocommerce-coupon-box-header-font').html('');
            }

        });
    });
    wp.customize('woo_coupon_box_params[wcb_body_font]', function (value) {
        value.bind(function (newval) {
            let newData = JSON.parse(newval);
            let $font_f = newData.font;
            if ($font_f) {
                $font_f = $font_f.replace(/ /g, '+');
                let $src = '//fonts.googleapis.com/css?family=' + $font_f + ':300,400,700';
                $('<link rel="stylesheet" type="text/css" href="' + $src + '">').appendTo($('head'));
                $('#woocommerce-coupon-box-body-font').html('.wcb-coupon-box .wcb-modal-body{font-family:' + newData.font + ' !important;}');
            } else {
                $('#woocommerce-coupon-box-body-font').html('');
            }
        });
    });

    wp.customize('woo_coupon_box_params[wcb_title_space]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-modal-header').css({
                'padding-top': newval + 'px',
                'padding-bottom': newval + 'px'
            });
        });
    });

    wp.customize('woo_coupon_box_params[wcb_message' + language_control + ']', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-coupon-message-before-subscribe').html(newval);
        });
    });

    wp.customize('woo_coupon_box_params[wcb_border_radius]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box-1 .wcb-md-content').css({'border-radius': newval + 'px'});
            $('.wcb-coupon-box-2 .wcb-content-wrap-child').css({'border-radius': newval + 'px'});
            $('.wcb-coupon-box-3 .wcb-md-content').css({'border-radius': newval + 'px'});
            $('.wcb-coupon-box-4 .wcb-md-content').css({'border-radius': newval + 'px'});
            $('.wcb-coupon-box-5 .wcb-content-wrap-child').css({'border-radius': newval + 'px'});
        });
    });


    wp.customize('woo_coupon_box_params[wcb_message_after_subscribe' + language_control + ']', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-coupon-message-after-subscribe').html(newval);
        });
    });
    wp.customize('woo_coupon_box_params[wcb_message_size]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-modal-body .wcb-coupon-message').css('font-size', newval + 'px');
        });
    });
    wp.customize('woo_coupon_box_params[wcb_color_message]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-modal-body .wcb-coupon-message').css('color', newval);
        });
    });
    wp.customize('woo_coupon_box_params[wcb_message_align]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-modal-body .wcb-coupon-message').css('text-align', newval);
        });
    });

    wp.customize('woo_coupon_box_params[wcb_body_bg]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-modal-body').css('backgroundColor', newval);
        });
    });

    wp.customize('woo_coupon_box_params[wcb_body_bg_img]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-modal-body').css('background-image', 'url(' + newval + ')');
        });
    });


    wp.customize('woo_coupon_box_params[wcb_body_bg_img_repeat]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-modal-body').css('background-repeat', newval);
        });
    });

    wp.customize('woo_coupon_box_params[wcb_body_bg_img_size]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-modal-body').css('background-size', newval);
        });
    });

    wp.customize('woo_coupon_box_params[wcb_body_bg_img_position]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-modal-body').css('background-position', newval);
        });
    });

    wp.customize('woo_coupon_box_params[wcb_body_text_color]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-modal-body').css('color', newval);
        });
    });

    wp.customize('woo_coupon_box_params[wcb_email_input_placeholder' + language_control + ']', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-newsletter .wcb-input-group input.wcb-email').attr('placeholder', newval + '(*)');
        });
    });

    wp.customize('woo_coupon_box_params[wcb_button_text' + language_control + ']', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-newsletter span.wcb-button').html(newval);
        });
    });

    wp.customize('woo_coupon_box_params[wcb_button_bg_color]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-newsletter span.wcb-button').css('backgroundColor', newval);
        });
    });
    wp.customize('woo_coupon_box_params[wcb_button_border_radius]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-newsletter span.wcb-button').css('border-radius', newval + 'px');
        });
    });
    wp.customize('woo_coupon_box_params[wcb_email_input_border_radius]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-newsletter input.wcb-email').css('border-radius', newval + 'px');
        });
    });
    // wp.customize('woo_coupon_box_params[wcb_email_input_color]', function (value) {
    //     value.bind(function (newval) {
    //         $('.wcb-coupon-box .wcb-newsletter input.wcb-email').css('color', newval);
    //     });
    // });
    // wp.customize('woo_coupon_box_params[wcb_email_input_bg_color]', function (value) {
    //     value.bind(function (newval) {
    //         $('.wcb-coupon-box .wcb-newsletter input.wcb-email').css('background', newval);
    //     });
    // });
    wp.customize('woo_coupon_box_params[wcb_email_input_color]', function (value) {
        value.bind(function (newval) {
            $('#woocommerce-coupon-box-email-input-color').html('.wcb-coupon-box .wcb-content-wrap .wcb-newsletter-form .wcb-input-group input,.wcb-coupon-box .wcb-content-wrap .wcb-newsletter-form .wcb-input-group ::placeholder{color:' + newval + ' !important;}');
        });
    });
    wp.customize('woo_coupon_box_params[wcb_email_input_bg_color]', function (value) {
        value.bind(function (newval) {
            $('#woocommerce-coupon-box-email-input-bg-color').html('.wcb-coupon-box .wcb-content-wrap .wcb-newsletter-form .wcb-input-group input{background:' + newval + ' !important;}');
        });
    });

    wp.customize('woo_coupon_box_params[wcb_email_button_space]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-newsletter input.wcb-email').css('margin-right', newval + 'px');
        });
    });

    wp.customize('woo_coupon_box_params[wcb_button_text_color]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-newsletter span.wcb-button').css('color', newval);
        });
    });

    wp.customize('woo_coupon_box_params[wcb_footer_text' + language_control + ']', function (value) {
        value.bind(function (newval) {
            $('.wcb-footer-text').html(newval);
        });
    });
    wp.customize('woo_coupon_box_params[wcb_footer_text_after_subscribe' + language_control + ']', function (value) {
        value.bind(function (newval) {
            $('.wcb-footer-text-after-subscribe').html(newval);
        });
    });
    wp.customize('woo_coupon_box_params[wcb_show_coupon]', function (value) {
        value.bind(function (newval) {
            if (newval) {
                $('.wcb-coupon-content').show();
            } else {
                $('.wcb-coupon-content').hide();
            }
        });
    });

    wp.customize('woo_coupon_box_params[wcb_register_account_checkbox]', function (value) {
        value.bind(function (newval) {
            if (newval) {
                $('.wcb-register-account-field').show();
            } else {
                $('.wcb-register-account-field').hide();
            }
        });
    });
    wp.customize('woo_coupon_box_params[wcb_register_account_checkbox_checked]', function (value) {
        value.bind(function (newval) {
            if (newval) {
                $('.wcb-register-account-checkbox').prop('checked', true);
            } else {
                $('.wcb-register-account-checkbox').prop('checked', false);
            }
        });
    });

    wp.customize('woo_coupon_box_params[wcb_register_account_message' + language_control + ']', function (value) {
        value.bind(function (newval) {
            $('.wcb-register-account-message').html(newval);
        });
    });

    wp.customize('woo_coupon_box_params[wcb_recaptcha_position]', function (value) {
        value.bind(function (newval) {
            if (newval === 'after') {
                $('.wcb-recaptcha-field.wcb-recaptcha-field-before').addClass('wcb-recaptcha-hidden');
                $('.wcb-recaptcha-field.wcb-recaptcha-field-after').removeClass('wcb-recaptcha-hidden');
            } else {

                $('.wcb-recaptcha-field.wcb-recaptcha-field-before').removeClass('wcb-recaptcha-hidden');
                $('.wcb-recaptcha-field.wcb-recaptcha-field-after').addClass('wcb-recaptcha-hidden');
            }
        });
    });

    wp.customize('woo_coupon_box_params[wcb_gdpr_checkbox]', function (value) {
        value.bind(function (newval) {
            if (newval) {
                $('.wcb-gdpr-field').show();
            } else {
                $('.wcb-gdpr-field').hide();
            }
        });
    });
    wp.customize('woo_coupon_box_params[wcb_gdpr_checkbox_checked]', function (value) {
        value.bind(function (newval) {
            if (newval) {
                $('.wcb-gdpr-checkbox').prop('checked', true);
            } else {
                $('.wcb-gdpr-checkbox').prop('checked', false);
            }
        });
    });

    wp.customize('woo_coupon_box_params[wcb_gdpr_message' + language_control + ']', function (value) {
        value.bind(function (newval) {
            $('.wcb-gdpr-message').html(newval);
        });
    });

    wp.customize('woo_coupon_box_params[alpha_color_overlay]', function (value) {
        value.bind(function (newval) {
            $('body .wcb-md-overlay').css('background', newval);
        });
    });


    wp.customize('woo_coupon_box_params[wcb_popup_type]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box').map(function () {
                let xr = $(this).attr('class').split(' ');
                xr[3] = newval;
                $(this).attr('class', xr.join(' '));
            });
            $('.wcb-current-layout').removeClass('wcb-md-show');
            setTimeout(function () {
                $('.wcb-current-layout').addClass('wcb-md-show');
            }, 1000);
        });
    });

    wp.customize('woo_coupon_box_params[wcb_color_follow_us]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-text-follow-us').css('color', newval);
        });
    });

    wp.customize('woo_coupon_box_params[wcb_follow_us' + language_control + ']', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-text-follow-us').html(newval);
        });
    });


    wp.customize('woo_coupon_box_params[wcb_countdown_number_color]', function (value) {
        value.bind(function (newval) {
            $('.counter-group .counter-block').css('color', newval);
        });
    });

    wp.customize('woo_coupon_box_params[wcb_countdown_text_color]', function (value) {
        value.bind(function (newval) {
            $('.counter-group .counter-block .counter-caption').css('color', newval);
        });
    });

    /*image column for layout 2,5*/
    wp.customize('woo_coupon_box_params[wcb_right_column_bg]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-md-content-right').css('backgroundColor', newval);
            $('.wcb-coupon-box .wcb-md-content-left').css('backgroundColor', newval);
        });
    });

    wp.customize('woo_coupon_box_params[wcb_right_column_bg_img]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-md-content-right').css('background-image', 'url(' + newval + ')');
            $('.wcb-coupon-box .wcb-md-content-left').css('background-image', 'url(' + newval + ')');
        });
    });


    wp.customize('woo_coupon_box_params[wcb_right_column_bg_img_repeat]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-md-content-right').css('background-repeat', newval);
            $('.wcb-coupon-box .wcb-md-content-left').css('background-repeat', newval);
        });
    });

    wp.customize('woo_coupon_box_params[wcb_right_column_bg_img_size]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-md-content-right').css('background-size', newval);
            $('.wcb-coupon-box .wcb-md-content-left').css('background-size', newval);
        });
    });

    wp.customize('woo_coupon_box_params[wcb_right_column_bg_img_position]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box .wcb-md-content-right').css('background-position', newval);
            $('.wcb-coupon-box .wcb-md-content-left').css('background-position', newval);
        });
    });

    /*Social*/
    wp.customize('woo_coupon_box_params[wcb_social_icons_size]', function (value) {
        value.bind(function (newval) {
            $('.wcb-social-icon').css({'font-size': newval + 'px', 'line-height': newval + 'px'});
        });
    });
    wp.customize('woo_coupon_box_params[wcb_social_icons_target]', function (value) {
        value.bind(function (newval) {
            $('.wcb-social-button').attr('target', newval);
        });
    });
    //facebook
    wp.customize('woo_coupon_box_params[wcb_social_icons_facebook_url]', function (value) {
        value.bind(function (newval) {
            $('.wcb-facebook-follow').attr('href', '//facebook.com/' + newval);
            if (newval) {
                $('.wcb-facebook-follow').show();
            } else {
                $('.wcb-facebook-follow').hide();
            }
        });
    });
    wp.customize('woo_coupon_box_params[wcb_social_icons_facebook_select]', function (value) {
        value.bind(function (newval) {
            $('.wcb-facebook-follow span').attr('class', 'wcb-social-icon ' + newval);
        });
    });
    wp.customize('woo_coupon_box_params[wcb_social_icons_facebook_color]', function (value) {
        value.bind(function (newval) {
            $('.wcb-facebook-follow span').css({'color': newval});
        });
    });

//twitter
    wp.customize('woo_coupon_box_params[wcb_social_icons_twitter_url]', function (value) {
        value.bind(function (newval) {
            $('.wcb-twitter-follow').attr('href', '//twitter.com/' + newval);
            if (newval) {
                $('.wcb-twitter-follow').show();
            } else {
                $('.wcb-twitter-follow').hide();
            }
        });
    });
    wp.customize('woo_coupon_box_params[wcb_social_icons_twitter_select]', function (value) {
        value.bind(function (newval) {
            $('.wcb-twitter-follow span').attr('class', 'wcb-social-icon ' + newval);
        });
    });
    wp.customize('woo_coupon_box_params[wcb_social_icons_twitter_color]', function (value) {
        value.bind(function (newval) {
            $('.wcb-twitter-follow span').css({'color': newval});
        });
    });
//pinterest
    wp.customize('woo_coupon_box_params[wcb_social_icons_pinterest_url]', function (value) {
        value.bind(function (newval) {
            $('.wcb-pinterest-follow').attr('href', '//pinterest.com/' + newval);
            if (newval) {
                $('.wcb-pinterest-follow').show();
            } else {
                $('.wcb-pinterest-follow').hide();
            }
        });
    });
    wp.customize('woo_coupon_box_params[wcb_social_icons_pinterest_select]', function (value) {
        value.bind(function (newval) {
            $('.wcb-pinterest-follow span').attr('class', 'wcb-social-icon ' + newval);
        });
    });
    wp.customize('woo_coupon_box_params[wcb_social_icons_pinterest_color]', function (value) {
        value.bind(function (newval) {
            $('.wcb-pinterest-follow span').css({'color': newval});
        });
    });
//instagram
    wp.customize('woo_coupon_box_params[wcb_social_icons_instagram_url]', function (value) {
        value.bind(function (newval) {
            $('.wcb-instagram-follow').attr('href', '//instagram.com/' + newval);
            if (newval) {
                $('.wcb-instagram-follow').show();
            } else {
                $('.wcb-instagram-follow').hide();
            }
        });
    });
    wp.customize('woo_coupon_box_params[wcb_social_icons_instagram_select]', function (value) {
        value.bind(function (newval) {
            $('.wcb-instagram-follow span').attr('class', 'wcb-social-icon ' + newval);
        });
    });
    wp.customize('woo_coupon_box_params[wcb_social_icons_instagram_color]', function (value) {
        value.bind(function (newval) {
            $('.wcb-instagram-follow span').css({'color': newval});
        });
    });
//dribbble
    wp.customize('woo_coupon_box_params[wcb_social_icons_dribbble_url]', function (value) {
        value.bind(function (newval) {
            $('.wcb-dribbble-follow').attr('href', '//dribbble.com/' + newval);
            if (newval) {
                $('.wcb-dribbble-follow').show();
            } else {
                $('.wcb-dribbble-follow').hide();
            }
        });
    });
    wp.customize('woo_coupon_box_params[wcb_social_icons_dribbble_select]', function (value) {
        value.bind(function (newval) {
            $('.wcb-dribbble-follow span').attr('class', 'wcb-social-icon ' + newval);
        });
    });
    wp.customize('woo_coupon_box_params[wcb_social_icons_dribbble_color]', function (value) {
        value.bind(function (newval) {
            $('.wcb-dribbble-follow span').css({'color': newval});
        });
    });
//tumblr
    wp.customize('woo_coupon_box_params[wcb_social_icons_tumblr_url]', function (value) {
        value.bind(function (newval) {
            $('.wcb-tumblr-follow').attr('href', '//tumblr.com/' + newval);
            if (newval) {
                $('.wcb-tumblr-follow').show();
            } else {
                $('.wcb-tumblr-follow').hide();
            }
        });
    });
    wp.customize('woo_coupon_box_params[wcb_social_icons_tumblr_select]', function (value) {
        value.bind(function (newval) {
            $('.wcb-tumblr-follow span').attr('class', 'wcb-social-icon ' + newval);
        });
    });
    wp.customize('woo_coupon_box_params[wcb_social_icons_tumblr_color]', function (value) {
        value.bind(function (newval) {
            $('.wcb-tumblr-follow span').css({'color': newval});
        });
    });
//google
    wp.customize('woo_coupon_box_params[wcb_social_icons_google_url]', function (value) {
        value.bind(function (newval) {
            $('.wcb-google-follow').attr('href', '//plus.google.com/' + newval);
            if (newval) {
                $('.wcb-google-follow').show();
            } else {
                $('.wcb-google-follow').hide();
            }
        });
    });
    wp.customize('woo_coupon_box_params[wcb_social_icons_google_select]', function (value) {
        value.bind(function (newval) {
            $('.wcb-google-follow span').attr('class', 'wcb-social-icon ' + newval);
        });
    });
    wp.customize('woo_coupon_box_params[wcb_social_icons_google_color]', function (value) {
        value.bind(function (newval) {
            $('.wcb-google-follow span').css({'color': newval});
        });
    });

    //vkontakte
    wp.customize('woo_coupon_box_params[wcb_social_icons_vkontakte_url]', function (value) {
        value.bind(function (newval) {
            $('.wcb-vkontakte-follow').attr('href', '//vk.com/' + newval);
            if (newval) {
                $('.wcb-vkontakte-follow').show();
            } else {
                $('.wcb-vkontakte-follow').hide();
            }
        });
    });
    wp.customize('woo_coupon_box_params[wcb_social_icons_vkontakte_select]', function (value) {
        value.bind(function (newval) {
            $('.wcb-vkontakte-follow span').attr('class', 'wcb-social-icon ' + newval);
        });
    });
    wp.customize('woo_coupon_box_params[wcb_social_icons_vkontakte_color]', function (value) {
        value.bind(function (newval) {
            $('.wcb-vkontakte-follow span').css({'color': newval});
        });
    });
//linkedin
    wp.customize('woo_coupon_box_params[wcb_social_icons_linkedin_url]', function (value) {
        value.bind(function (newval) {
            $('.wcb-linkedin-follow').attr('href', '//linkedin.com/in/' + newval);
            if (newval) {
                $('.wcb-linkedin-follow').show();
            } else {
                $('.wcb-linkedin-follow').hide();
            }
        });
    });
    wp.customize('woo_coupon_box_params[wcb_social_icons_linkedin_select]', function (value) {
        value.bind(function (newval) {
            $('.wcb-linkedin-follow span').attr('class', 'wcb-social-icon ' + newval);
        });
    });
    wp.customize('woo_coupon_box_params[wcb_social_icons_linkedin_color]', function (value) {
        value.bind(function (newval) {
            $('.wcb-linkedin-follow span').css({'color': newval});
        });
    });
//youtube
    wp.customize('woo_coupon_box_params[wcb_social_icons_youtube_url]', function (value) {
        value.bind(function (newval) {
            $('.wcb-youtube-follow').attr('href', newval);
            if (newval) {
                $('.wcb-youtube-follow').show();
            } else {
                $('.wcb-youtube-follow').hide();
            }
        });
    });
    wp.customize('woo_coupon_box_params[wcb_social_icons_youtube_select]', function (value) {
        value.bind(function (newval) {
            $('.wcb-youtube-follow span').attr('class', 'wcb-social-icon ' + newval);
        });
    });
    wp.customize('woo_coupon_box_params[wcb_social_icons_youtube_color]', function (value) {
        value.bind(function (newval) {
            $('.wcb-youtube-follow span').css({'color': newval});
        });
    });

    wp.customize('woo_coupon_box_params[wcb_custom_css]', function (value) {
        value.bind(function (newval) {
            $('#woocommerce-coupon-box-custom-css').html(newval);
        });
    });
    /*popup icon*/
    wp.customize('woo_coupon_box_params[wcb_popup_icon]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box-small-icon').attr('class', 'wcb-coupon-box-small-icon ' + newval);
        });
    });
    wp.customize('woo_coupon_box_params[wcb_popup_icon_enable]', function (value) {
        value.bind(function (newval) {
            if (newval) {
                if ($('.wcb-coupon-box-small-icon-wrap').hasClass('wcb-coupon-box-small-icon-position-bottom-left') || $('.wcb-coupon-box-small-icon-wrap').hasClass('wcb-coupon-box-small-icon-position-top-left')) {
                    $('.wcb-coupon-box-small-icon-wrap').removeClass('wcb-coupon-box-small-icon-hide-left');
                } else {
                    $('.wcb-coupon-box-small-icon-wrap').removeClass('wcb-coupon-box-small-icon-hide-right');
                }
            } else {
                if ($('.wcb-coupon-box-small-icon-wrap').hasClass('wcb-coupon-box-small-icon-position-bottom-left') || $('.wcb-coupon-box-small-icon-wrap').hasClass('wcb-coupon-box-small-icon-position-top-left')) {
                    $('.wcb-coupon-box-small-icon-wrap').addClass('wcb-coupon-box-small-icon-hide-left');
                } else {
                    $('.wcb-coupon-box-small-icon-wrap').addClass('wcb-coupon-box-small-icon-hide-right');
                }
            }
        });
    });
    wp.customize('woo_coupon_box_params[wcb_popup_icon_position]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box-small-icon-wrap').removeClass('wcb-coupon-box-small-icon-position-top-right').removeClass('wcb-coupon-box-small-icon-position-bottom-right').removeClass('wcb-coupon-box-small-icon-position-top-left').removeClass('wcb-coupon-box-small-icon-position-bottom-left').addClass('wcb-coupon-box-small-icon-position-' + newval);
        });
    });
    wp.customize('woo_coupon_box_params[wcb_popup_icon_mobile]', function (value) {
        value.bind(function (newval) {
            if (newval) {
                $('.wcb-coupon-box-small-icon-wrap').removeClass('wcb-coupon-box-small-icon-hidden-mobile');
            } else {
                $('.wcb-coupon-box-small-icon-wrap').addClass('wcb-coupon-box-small-icon-hidden-mobile');
            }
        });
    });
    wp.customize('woo_coupon_box_params[wcb_popup_icon_size]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box-small-icon').css({'font-size': newval + 'px', 'line-height': newval + 'px'});
        });
    });
    wp.customize('woo_coupon_box_params[wcb_popup_icon_border_radius]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box-small-icon-wrap').css({'border-radius': newval + 'px'});
        });
    });
    wp.customize('woo_coupon_box_params[wcb_popup_icon_color]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box-small-icon').css('color', newval);
        });
    });
    wp.customize('woo_coupon_box_params[wcb_popup_icon_bg_color]', function (value) {
        value.bind(function (newval) {
            $('.wcb-coupon-box-small-icon-wrap').css('background-color', newval);
        });
    });
    /*custom input fields*/

    wp.customize('woo_coupon_box_params[wcb_custom_input_border_radius]', function (value) {
        value.bind(function (newval) {
            $('#woocommerce-coupon-box-custom-input-border-radius').html('.wcb-coupon-box .wcb-content-wrap .wcb-custom-input-fields div.wcb-input-field-item{border-radius:' + newval + 'px;}');
        });
    });
    wp.customize('woo_coupon_box_params[wcb_custom_input_color]', function (value) {
        value.bind(function (newval) {
            $('#woocommerce-coupon-box-custom-input-color').html('.wcb-coupon-box .wcb-content-wrap .wcb-custom-input-fields div.wcb-input-field-item input,.wcb-coupon-box .wcb-content-wrap .wcb-custom-input-fields div.wcb-input-field-item select,.wcb-coupon-box .wcb-content-wrap .wcb-custom-input-fields .wcb-input-field-item ::placeholder{color:' + newval + ' !important;}');
        });
    });
    wp.customize('woo_coupon_box_params[wcb_custom_input_bg_color]', function (value) {
        value.bind(function (newval) {
            $('#woocommerce-coupon-box-custom-input-bg-color').html('.wcb-coupon-box .wcb-content-wrap .wcb-custom-input-fields div.wcb-input-field-item input,.wcb-coupon-box .wcb-content-wrap .wcb-custom-input-fields div.wcb-input-field-item select{background:' + newval + ' !important;}');
        });
    });
    wp.customize('woo_coupon_box_params[wcb_input_name_required]', function (value) {
        value.bind(function (newval) {
            if (newval) {
                $('.wcb-input-name').attr('placeholder', 'Your first name(*)').attr('title', 'Your first name(*required)');
            } else {
                $('.wcb-input-name').attr('placeholder', 'Your first name').attr('title', 'Your first name');
            }
        });
    });
    wp.customize('woo_coupon_box_params[wcb_input_lname_required]', function (value) {
        value.bind(function (newval) {
            if (newval) {
                $('.wcb-input-lname').attr('placeholder', 'Your last name(*)').attr('title', 'Your last name(*required)');
            } else {
                $('.wcb-input-lname').attr('placeholder', 'Your last name').attr('title', 'Your last name');
            }
        });
    });
    wp.customize('woo_coupon_box_params[wcb_input_mobile_required]', function (value) {
        value.bind(function (newval) {
            if (newval) {
                $('.wcb-input-mobile').attr('placeholder', 'Your mobile(*)').attr('title', 'Your mobile(*required)');
            } else {
                $('.wcb-input-mobile').attr('placeholder', 'Your mobile').attr('title', 'Your mobile');
            }
        });
    });
    wp.customize('woo_coupon_box_params[wcb_input_birthday_required]', function (value) {
        value.bind(function (newval) {
            if (newval) {
                $('.wcb-input-birthday').attr('placeholder', 'Your birthday(*)').attr('title', 'Your birthday(*required)');
            } else {
                $('.wcb-input-birthday').attr('placeholder', 'Your birthday').attr('title', 'Your birthday');
            }
        });
    });
    wp.customize('woo_coupon_box_params[wcb_input_gender_required]', function (value) {
        value.bind(function (newval) {
            if (newval) {
                $('.wcb-input-gender').attr('title', 'Your gender(*required)').find('option').eq(0).html('Your gender(*)');
            } else {
                $('.wcb-input-gender').attr('title', 'Your gender').find('option').eq(0).html('Your gender');
            }
        });
    });
    wp.customize('woo_coupon_box_params[wcb_input_additional_required]', function (value) {
        value.bind(function (newval) {
            let custom_input_additional_label = wp.customize('woo_coupon_box_params[wcb_input_additional_label]').get();
            if (newval) {
                $('.wcb-input-additional').attr('title', custom_input_additional_label + '(*)').attr('placeholder', custom_input_additional_label + '(*)');
            } else {
                $('.wcb-input-additional').attr('title', custom_input_additional_label).attr('placeholder', custom_input_additional_label);
            }
        });
    });
    wp.customize('woo_coupon_box_params[wcb_input_additional_label]', function (value) {
        value.bind(function (newval) {
            let wcb_input_additional_required = wp.customize('woo_coupon_box_params[wcb_input_additional_required]').get();
            if (wcb_input_additional_required) {
                $('.wcb-input-additional').attr('title', newval + '(*)').attr('placeholder', newval + '(*)');
            } else {
                $('.wcb-input-additional').attr('title', newval).attr('placeholder', newval);
            }
        });
    });
    wp.customize('woo_coupon_box_params[wcb_input_name]', function (value) {
        value.bind(function (newval) {
            let inputCount, returnHTML = '';
            if (newval) {
                inputCount = 1;
                returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-name">\n' +
                    '<input type="text" name="wcb_input_name" class="wcb-input-name" placeholder="Your first name" title="Your first name">\n' +
                    '</div>';
                if (wp.customize('woo_coupon_box_params[wcb_input_lname]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-lname">\n' +
                        '<input type="tel" name="wcb_input_lname" class="wcb-input-lname" placeholder="Your last name" title="Your last name">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_mobile]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-mobile">\n' +
                        '<input type="tel" name="wcb_input_mobile" class="wcb-input-mobile" placeholder="Your mobile" title="Your mobile">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_birthday]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-birthday">\n' +
                        '<input type="date" name="wcb_input_birthday" class="wcb-input-birthday" title="Your birthday">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_gender]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-gender">\n' +
                        '<select name="wcb_input_gender" class="wcb-input-gender" title="Your gender">\n' +
                        '<option>Your gender</option>\n' +
                        '<option value="male">Male</option>\n' +
                        '<option value="female">Female</option>\n' +
                        '<option value="other">Other</option>\n' +
                        '</select>\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_additional]').get()) {
                    inputCount++;
                    let custom_input_additional_require = wp.customize('woo_coupon_box_params[wcb_input_additional_required]').get(),
                        custom_input_additional_label = wp.customize('woo_coupon_box_params[wcb_input_additional_label]').get(),
                        custom_input_additional_title = custom_input_additional_require ? custom_input_additional_label + '(*)' : custom_input_additional_label;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-additional">\n' +
                        '<input type="text" name="wcb_input_additional" class="wcb-input-additional" ' +
                        'placeholder="' + custom_input_additional_title + '" ' +
                        'title="' + custom_input_additional_title + '">\n' +
                        '</div>';
                }
            } else {
                inputCount = 0;
                if (wp.customize('woo_coupon_box_params[wcb_input_lname]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-lname">\n' +
                        '<input type="tel" name="wcb_input_lname" class="wcb-input-lname" placeholder="Your last name" title="Your last name">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_mobile]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-mobile">\n' +
                        '<input type="tel" name="wcb_input_mobile" class="wcb-input-mobile" placeholder="Your mobile" title="Your mobile">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_birthday]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-birthday">\n' +
                        '<input type="date" name="wcb_input_birthday" class="wcb-input-birthday" title="Your birthday">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_gender]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-gender">\n' +
                        '<select name="wcb_input_gender" class="wcb-input-gender" title="Your gender">\n' +
                        '<option>Your gender</option>\n' +
                        '<option value="male">Male</option>\n' +
                        '<option value="female">Female</option>\n' +
                        '<option value="other">Other</option>\n' +
                        '</select>\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_additional]').get()) {
                    inputCount++;
                    let custom_input_additional_require = wp.customize('woo_coupon_box_params[wcb_input_additional_required]').get(),
                        custom_input_additional_label = wp.customize('woo_coupon_box_params[wcb_input_additional_label]').get(),
                        custom_input_additional_title = custom_input_additional_require ? custom_input_additional_label + '(*)' : custom_input_additional_label;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-additional">\n' +
                        '<input type="text" name="wcb_input_additional" class="wcb-input-additional" ' +
                        'placeholder="' + custom_input_additional_title + '" ' +
                        'title="' + custom_input_additional_title + '">\n' +
                        '</div>';
                }
            }
            $('.wcb-custom-input-fields').attr('class', 'wcb-custom-input-fields wcb-view-before-subscribe ' + 'wcb-input-fields-count-' + inputCount).html(returnHTML);
        });
    });
    wp.customize('woo_coupon_box_params[wcb_input_lname]', function (value) {
        value.bind(function (newval) {
            let inputCount, returnHTML = '';
            if (newval) {
                inputCount = 1;
                if (wp.customize('woo_coupon_box_params[wcb_input_name]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-name">\n' +
                        '<input type="tel" name="wcb_input_name" class="wcb-input-name" placeholder="Your first name" title="Your first name">\n' +
                        '</div>';
                }
                returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-lname">\n' +
                    '<input type="text" name="wcb_input_lname" class="wcb-input-lname" placeholder="Your last name" title="Your last name">\n' +
                    '</div>';

                if (wp.customize('woo_coupon_box_params[wcb_input_mobile]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-mobile">\n' +
                        '<input type="tel" name="wcb_input_mobile" class="wcb-input-mobile" placeholder="Your mobile" title="Your mobile">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_birthday]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-birthday">\n' +
                        '<input type="date" name="wcb_input_birthday" class="wcb-input-birthday" title="Your birthday">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_gender]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-gender">\n' +
                        '<select name="wcb_input_gender" class="wcb-input-gender" title="Your gender">\n' +
                        '<option>Your gender</option>\n' +
                        '<option value="male">Male</option>\n' +
                        '<option value="female">Female</option>\n' +
                        '<option value="other">Other</option>\n' +
                        '</select>\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_additional]').get()) {
                    inputCount++;
                    let custom_input_additional_require = wp.customize('woo_coupon_box_params[wcb_input_additional_required]').get(),
                        custom_input_additional_label = wp.customize('woo_coupon_box_params[wcb_input_additional_label]').get(),
                        custom_input_additional_title = custom_input_additional_require ? custom_input_additional_label + '(*)' : custom_input_additional_label;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-additional">\n' +
                        '<input type="text" name="wcb_input_additional" class="wcb-input-additional" ' +
                        'placeholder="' + custom_input_additional_title + '" ' +
                        'title="' + custom_input_additional_title + '">\n' +
                        '</div>';
                }
            } else {
                inputCount = 0;
                if (wp.customize('woo_coupon_box_params[wcb_input_name]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-name">\n' +
                        '<input type="tel" name="wcb_input_name" class="wcb-input-name" placeholder="Your first name" title="Your first name">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_mobile]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-mobile">\n' +
                        '<input type="tel" name="wcb_input_mobile" class="wcb-input-mobile" placeholder="Your mobile" title="Your mobile">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_birthday]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-birthday">\n' +
                        '<input type="date" name="wcb_input_birthday" class="wcb-input-birthday" title="Your birthday">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_gender]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-gender">\n' +
                        '<select name="wcb_input_gender" class="wcb-input-gender" title="Your gender">\n' +
                        '<option>Your gender</option>\n' +
                        '<option value="male">Male</option>\n' +
                        '<option value="female">Female</option>\n' +
                        '<option value="other">Other</option>\n' +
                        '</select>\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_additional]').get()) {
                    inputCount++;
                    let custom_input_additional_require = wp.customize('woo_coupon_box_params[wcb_input_additional_required]').get(),
                        custom_input_additional_label = wp.customize('woo_coupon_box_params[wcb_input_additional_label]').get(),
                        custom_input_additional_title = custom_input_additional_require ? custom_input_additional_label + '(*)' : custom_input_additional_label;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-additional">\n' +
                        '<input type="text" name="wcb_input_additional" class="wcb-input-additional" ' +
                        'placeholder="' + custom_input_additional_title + '" ' +
                        'title="' + custom_input_additional_title + '">\n' +
                        '</div>';
                }
            }
            $('.wcb-custom-input-fields').attr('class', 'wcb-custom-input-fields wcb-view-before-subscribe ' + 'wcb-input-fields-count-' + inputCount).html(returnHTML);
        });
    });
    wp.customize('woo_coupon_box_params[wcb_input_mobile]', function (value) {
        value.bind(function (newval) {
            let inputCount, returnHTML = '';
            if (newval) {
                inputCount = 1;

                if (wp.customize('woo_coupon_box_params[wcb_input_name]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-name">\n' +
                        '<input type="text" name="wcb_input_name" class="wcb-input-name" placeholder="Your first name" title="Your first name">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_lname]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-lname">\n' +
                        '<input type="tel" name="wcb_input_lname" class="wcb-input-lname" placeholder="Your last name" title="Your last name">\n' +
                        '</div>';
                }
                returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-mobile">\n' +
                    '<input type="tel" name="wcb_input_mobile" class="wcb-input-mobile" placeholder="Your mobile" title="Your mobile">\n' +
                    '</div>';
                if (wp.customize('woo_coupon_box_params[wcb_input_birthday]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-birthday">\n' +
                        '<input type="date" name="wcb_input_birthday" class="wcb-input-birthday" title="Your birthday">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_gender]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-gender">\n' +
                        '<select name="wcb_input_gender" class="wcb-input-gender" title="Your gender">\n' +
                        '<option>Your gender</option>\n' +
                        '<option value="male">Male</option>\n' +
                        '<option value="female">Female</option>\n' +
                        '<option value="other">Other</option>\n' +
                        '</select>\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_additional]').get()) {
                    inputCount++;
                    let custom_input_additional_require = wp.customize('woo_coupon_box_params[wcb_input_additional_required]').get(),
                        custom_input_additional_label = wp.customize('woo_coupon_box_params[wcb_input_additional_label]').get(),
                        custom_input_additional_title = custom_input_additional_require ? custom_input_additional_label + '(*)' : custom_input_additional_label;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-additional">\n' +
                        '<input type="text" name="wcb_input_additional" class="wcb-input-additional" ' +
                        'placeholder="' + custom_input_additional_title + '" ' +
                        'title="' + custom_input_additional_title + '">\n' +
                        '</div>';
                }
            } else {
                inputCount = 0;
                if (wp.customize('woo_coupon_box_params[wcb_input_name]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-name">\n' +
                        '<input type="text" name="wcb_input_name" class="wcb-input-name" placeholder="Your first name" title="Your first name">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_lname]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-lname">\n' +
                        '<input type="tel" name="wcb_input_lname" class="wcb-input-lname" placeholder="Your last name" title="Your last name">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_birthday]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-birthday">\n' +
                        '<input type="date" name="wcb_input_birthday" class="wcb-input-birthday" title="Your birthday">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_gender]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-gender">\n' +
                        '<select name="wcb_input_gender" class="wcb-input-gender" title="Your gender">\n' +
                        '<option>Your gender</option>\n' +
                        '<option value="male">Male</option>\n' +
                        '<option value="female">Female</option>\n' +
                        '<option value="other">Other</option>\n' +
                        '</select>\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_additional]').get()) {
                    inputCount++;
                    let custom_input_additional_require = wp.customize('woo_coupon_box_params[wcb_input_additional_required]').get(),
                        custom_input_additional_label = wp.customize('woo_coupon_box_params[wcb_input_additional_label]').get(),
                        custom_input_additional_title = custom_input_additional_require ? custom_input_additional_label + '(*)' : custom_input_additional_label;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-additional">\n' +
                        '<input type="text" name="wcb_input_additional" class="wcb-input-additional" ' +
                        'placeholder="' + custom_input_additional_title + '" ' +
                        'title="' + custom_input_additional_title + '">\n' +
                        '</div>';
                }
            }
            $('.wcb-custom-input-fields').attr('class', 'wcb-custom-input-fields wcb-view-before-subscribe ' + 'wcb-input-fields-count-' + inputCount).html(returnHTML);
        });
    });
    wp.customize('woo_coupon_box_params[wcb_input_birthday]', function (value) {
        value.bind(function (newval) {
            let inputCount, returnHTML = '';
            if (newval) {
                inputCount = 1;
                if (wp.customize('woo_coupon_box_params[wcb_input_name]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-name">\n' +
                        '<input type="text" name="wcb_input_name" class="wcb-input-name" placeholder="Your first name" title="Your first name">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_lname]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-lname">\n' +
                        '<input type="tel" name="wcb_input_lname" class="wcb-input-lname" placeholder="Your last name" title="Your last name">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_mobile]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-mobile">\n' +
                        '<input type="tel" name="wcb_input_mobile" class="wcb-input-mobile" placeholder="Your mobile" title="Your mobile">\n' +
                        '</div>';
                }

                returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-birthday">\n' +
                    '<input type="date" name="wcb_input_birthday" class="wcb-input-birthday" title="Your birthday">\n' +
                    '</div>';
                if (wp.customize('woo_coupon_box_params[wcb_input_gender]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-gender">\n' +
                        '<select name="wcb_input_gender" class="wcb-input-gender" title="Your gender">\n' +
                        '<option>Your gender</option>\n' +
                        '<option value="male">Male</option>\n' +
                        '<option value="female">Female</option>\n' +
                        '<option value="other">Other</option>\n' +
                        '</select>\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_additional]').get()) {
                    inputCount++;
                    let custom_input_additional_require = wp.customize('woo_coupon_box_params[wcb_input_additional_required]').get(),
                        custom_input_additional_label = wp.customize('woo_coupon_box_params[wcb_input_additional_label]').get(),
                        custom_input_additional_title = custom_input_additional_require ? custom_input_additional_label + '(*)' : custom_input_additional_label;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-additional">\n' +
                        '<input type="text" name="wcb_input_additional" class="wcb-input-additional" ' +
                        'placeholder="' + custom_input_additional_title + '" ' +
                        'title="' + custom_input_additional_title + '">\n' +
                        '</div>';
                }
            } else {
                inputCount = 0;
                if (wp.customize('woo_coupon_box_params[wcb_input_name]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-name">\n' +
                        '<input type="text" name="wcb_input_name" class="wcb-input-name" placeholder="Your first name" title="Your first name">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_lname]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-lname">\n' +
                        '<input type="tel" name="wcb_input_lname" class="wcb-input-lname" placeholder="Your last name" title="Your last name">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_mobile]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-mobile">\n' +
                        '<input type="tel" name="wcb_input_mobile" class="wcb-input-mobile" placeholder="Your mobile" title="Your mobile">\n' +
                        '</div>';
                }

                if (wp.customize('woo_coupon_box_params[wcb_input_gender]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-gender">\n' +
                        '<select name="wcb_input_gender" class="wcb-input-gender" title="Your gender">\n' +
                        '<option>Your gender</option>\n' +
                        '<option value="male">Male</option>\n' +
                        '<option value="female">Female</option>\n' +
                        '<option value="other">Other</option>\n' +
                        '</select>\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_additional]').get()) {
                    inputCount++;
                    let custom_input_additional_require = wp.customize('woo_coupon_box_params[wcb_input_additional_required]').get(),
                        custom_input_additional_label = wp.customize('woo_coupon_box_params[wcb_input_additional_label]').get(),
                        custom_input_additional_title = custom_input_additional_require ? custom_input_additional_label + '(*)' : custom_input_additional_label;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-additional">\n' +
                        '<input type="text" name="wcb_input_additional" class="wcb-input-additional" ' +
                        'placeholder="' + custom_input_additional_title + '" ' +
                        'title="' + custom_input_additional_title + '">\n' +
                        '</div>';
                }
            }
            $('.wcb-custom-input-fields').attr('class', 'wcb-custom-input-fields wcb-view-before-subscribe ' + 'wcb-input-fields-count-' + inputCount).html(returnHTML);
        });
    });
    wp.customize('woo_coupon_box_params[wcb_input_gender]', function (value) {
        value.bind(function (newval) {
            let inputCount, returnHTML = '';
            if (newval) {
                inputCount = 1;
                if (wp.customize('woo_coupon_box_params[wcb_input_name]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-name">\n' +
                        '<input type="text" name="wcb_input_name" class="wcb-input-name" placeholder="Your first name" title="Your first name">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_lname]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-lname">\n' +
                        '<input type="tel" name="wcb_input_lname" class="wcb-input-lname" placeholder="Your last name" title="Your last name">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_mobile]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-mobile">\n' +
                        '<input type="tel" name="wcb_input_mobile" class="wcb-input-mobile" placeholder="Your mobile" title="Your mobile">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_birthday]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-birthday">\n' +
                        '<input type="date" name="wcb_input_birthday" class="wcb-input-birthday" title="Your birthday">\n' +
                        '</div>';
                }
                returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-gender">\n' +
                    '<select name="wcb_input_gender" class="wcb-input-gender" title="Your gender">\n' +
                    '<option>Your gender</option>\n' +
                    '<option value="male">Male</option>\n' +
                    '<option value="female">Female</option>\n' +
                    '<option value="other">Other</option>\n' +
                    '</select>\n' +
                    '</div>';
                if (wp.customize('woo_coupon_box_params[wcb_input_additional]').get()) {
                    inputCount++;
                    let custom_input_additional_require = wp.customize('woo_coupon_box_params[wcb_input_additional_required]').get(),
                        custom_input_additional_label = wp.customize('woo_coupon_box_params[wcb_input_additional_label]').get(),
                        custom_input_additional_title = custom_input_additional_require ? custom_input_additional_label + '(*)' : custom_input_additional_label;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-additional">\n' +
                        '<input type="text" name="wcb_input_additional" class="wcb-input-additional" ' +
                        'placeholder="' + custom_input_additional_title + '" ' +
                        'title="' + custom_input_additional_title + '">\n' +
                        '</div>';
                }
            } else {
                inputCount = 0;
                if (wp.customize('woo_coupon_box_params[wcb_input_name]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-name">\n' +
                        '<input type="text" name="wcb_input_name" class="wcb-input-name" placeholder="Your first name" title="Your first name">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_lname]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-lname">\n' +
                        '<input type="tel" name="wcb_input_lname" class="wcb-input-lname" placeholder="Your last name" title="Your last name">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_mobile]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-mobile">\n' +
                        '<input type="tel" name="wcb_input_mobile" class="wcb-input-mobile" placeholder="Your mobile" title="Your mobile">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_birthday]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-birthday">\n' +
                        '<input type="date" name="wcb_input_birthday" class="wcb-input-birthday" title="Your birthday">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_additional]').get()) {
                    inputCount++;
                    let custom_input_additional_require = wp.customize('woo_coupon_box_params[wcb_input_additional_required]').get(),
                        custom_input_additional_label = wp.customize('woo_coupon_box_params[wcb_input_additional_label]').get(),
                        custom_input_additional_title = custom_input_additional_require ? custom_input_additional_label + '(*)' : custom_input_additional_label;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-additional">\n' +
                        '<input type="text" name="wcb_input_additional" class="wcb-input-additional" ' +
                        'placeholder="' + custom_input_additional_title + '" ' +
                        'title="' + custom_input_additional_title + '">\n' +
                        '</div>';
                }
            }
            $('.wcb-custom-input-fields').attr('class', 'wcb-custom-input-fields wcb-view-before-subscribe ' + 'wcb-input-fields-count-' + inputCount).html(returnHTML);
        });
    });
    wp.customize('woo_coupon_box_params[wcb_input_additional]', function (value) {
        value.bind(function (newval) {
            let inputCount, returnHTML = '';
            if (newval) {
                inputCount = 1;
                if (wp.customize('woo_coupon_box_params[wcb_input_name]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-name">\n' +
                        '<input type="text" name="wcb_input_name" class="wcb-input-name" placeholder="Your first name" title="Your first name">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_lname]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-lname">\n' +
                        '<input type="tel" name="wcb_input_lname" class="wcb-input-lname" placeholder="Your last name" title="Your last name">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_mobile]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-mobile">\n' +
                        '<input type="tel" name="wcb_input_mobile" class="wcb-input-mobile" placeholder="Your mobile" title="Your mobile">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_birthday]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-birthday">\n' +
                        '<input type="date" name="wcb_input_birthday" class="wcb-input-birthday" title="Your birthday">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_birthday]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-gender">\n' +
                        '<select name="wcb_input_gender" class="wcb-input-gender" title="Your gender">\n' +
                        '<option>Your gender</option>\n' +
                        '<option value="male">Male</option>\n' +
                        '<option value="female">Female</option>\n' +
                        '<option value="other">Other</option>\n' +
                        '</select>\n' +
                        '</div>';
                }
                let custom_input_additional_require = wp.customize('woo_coupon_box_params[wcb_input_additional_required]').get(),
                    custom_input_additional_label = wp.customize('woo_coupon_box_params[wcb_input_additional_label]').get(),
                    custom_input_additional_title = custom_input_additional_require ? custom_input_additional_label + '(*)' : custom_input_additional_label;
                returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-additional">\n' +
                    '<input type="text" name="wcb_input_additional" class="wcb-input-additional" ' +
                    'placeholder="' + custom_input_additional_title + '" ' +
                    'title="' + custom_input_additional_title + '">\n' +
                    '</div>';
            } else {
                inputCount = 0;
                if (wp.customize('woo_coupon_box_params[wcb_input_name]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-name">\n' +
                        '<input type="text" name="wcb_input_name" class="wcb-input-name" placeholder="Your first name" title="Your first name">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_lname]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-lname">\n' +
                        '<input type="tel" name="wcb_input_lname" class="wcb-input-lname" placeholder="Your last name" title="Your last name">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_mobile]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-mobile">\n' +
                        '<input type="tel" name="wcb_input_mobile" class="wcb-input-mobile" placeholder="Your mobile" title="Your mobile">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_birthday]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-birthday">\n' +
                        '<input type="date" name="wcb_input_birthday" class="wcb-input-birthday" title="Your birthday">\n' +
                        '</div>';
                }
                if (wp.customize('woo_coupon_box_params[wcb_input_birthday]').get()) {
                    inputCount++;
                    returnHTML += '<div class="wcb-input-field-item wcb-input-field-item-gender">\n' +
                        '<select name="wcb_input_gender" class="wcb-input-gender" title="Your gender">\n' +
                        '<option>Your gender</option>\n' +
                        '<option value="male">Male</option>\n' +
                        '<option value="female">Female</option>\n' +
                        '<option value="other">Other</option>\n' +
                        '</select>\n' +
                        '</div>';
                }

            }
            $('.wcb-custom-input-fields').attr('class', 'wcb-custom-input-fields wcb-view-before-subscribe ' + 'wcb-input-fields-count-' + inputCount).html(returnHTML);
        });
    });
    wp.customize('woo_coupon_box_params[wcb_effect]', function (value) {
        value.bind(function (newval) {
            let $overlay=$('.wcb-md-overlay'),xr = $overlay.attr('class').split(' '),
                leafContainer,
                leaves;
            if (!newval) {
                $overlay.removeClass(xr[1]).html('');
            } else {
                switch (newval) {
                    case 'wcb-falling-leaves':
                        $overlay.removeClass(xr[1]).html('').addClass('wcb-falling-leaves');
                        leafContainer = document.querySelector('.wcb-falling-leaves');
                        leaves = new LeafScene(leafContainer);
                        leaves.init();
                        leaves.render();
                        $('.wcb-leaf-scene div').attr('class', '').addClass('wcb-falling-leaves-leaves');
                        break;
                    case 'wcb-falling-leaves-1':
                        $overlay.removeClass(xr[1]).html('').addClass('wcb-falling-leaves');
                        leafContainer = document.querySelector('.wcb-falling-leaves');
                        leaves = new LeafScene(leafContainer);
                        leaves.init();
                        leaves.render();
                        $('.wcb-leaf-scene div').attr('class', '').addClass('wcb-falling-leaves-leaves-1');
                        break;
                    case 'wcb-falling-heart':
                        $overlay.removeClass(xr[1]).html('').addClass('wcb-falling-leaves');
                        leafContainer = document.querySelector('.wcb-falling-leaves');
                        leaves = new LeafScene(leafContainer);
                        leaves.init();
                        leaves.render();
                        $('.wcb-leaf-scene div').attr('class', '').addClass('wcb-falling-leaves-heart');
                        break;
                    case 'wcb-falling-snow':
                        $overlay.removeClass(xr[1]).html('').addClass(newval);
                        Snowflake.init($('.wcb-falling-snow')[0]);
                        break;
                    case 'wcb-falling-snow-1':
                        $overlay.removeClass(xr[1]).html('<div class="wcb-weather wcb-weather-snow"></div>');
                        break;
                    case 'wcb-falling-rain':
                        $overlay.removeClass(xr[1]).html('<div class="wcb-weather wcb-weather-rain"></div>');
                        break;
                    case 'snowflakes':
                        $overlay.removeClass(xr[1]).html('<div class="wcb-background-effect-snowflakes" aria-hidden="true">\n' +
                            '                <div class="wcb-background-effect-snowflake">\n' +
                            '                    ❅\n' +
                            '                </div>\n' +
                            '                <div class="wcb-background-effect-snowflake">\n' +
                            '                    ❅\n' +
                            '                </div>\n' +
                            '                <div class="wcb-background-effect-snowflake">\n' +
                            '                    ❆\n' +
                            '                </div>\n' +
                            '                <div class="wcb-background-effect-snowflake">\n' +
                            '                    ❄\n' +
                            '                </div>\n' +
                            '                <div class="wcb-background-effect-snowflake">\n' +
                            '                    ❅\n' +
                            '                </div>\n' +
                            '                <div class="wcb-background-effect-snowflake">\n' +
                            '                    ❆\n' +
                            '                </div>\n' +
                            '                <div class="wcb-background-effect-snowflake">\n' +
                            '                    ❄\n' +
                            '                </div>\n' +
                            '                <div class="wcb-background-effect-snowflake">\n' +
                            '                    ❅\n' +
                            '                </div>\n' +
                            '                <div class="wcb-background-effect-snowflake">\n' +
                            '                    ❆\n' +
                            '                </div>\n' +
                            '                <div class="wcb-background-effect-snowflake">\n' +
                            '                    ❄\n' +
                            '                </div>\n' +
                            '                <div class="wcb-background-effect-snowflake">\n' +
                            '                    ❅\n' +
                            '                </div>\n' +
                            '                <div class="wcb-background-effect-snowflake">\n' +
                            '                    ❅\n' +
                            '                </div>\n' +
                            '                <div class="wcb-background-effect-snowflake">\n' +
                            '                    ❆\n' +
                            '                </div>\n' +
                            '                <div class="wcb-background-effect-snowflake">\n' +
                            '                    ❄\n' +
                            '                </div>\n' +
                            '                <div class="wcb-background-effect-snowflake">\n' +
                            '                    ❅\n' +
                            '                </div>\n' +
                            '                <div class="wcb-background-effect-snowflake">\n' +
                            '                    ❆\n' +
                            '                </div>\n' +
                            '                <div class="wcb-background-effect-snowflake">\n' +
                            '                    ❄\n' +
                            '                </div>\n' +
                            '                <div class="wcb-background-effect-snowflake">\n' +
                            '                    ❅\n' +
                            '                </div>\n' +
                            '                <div class="wcb-background-effect-snowflake">\n' +
                            '                    ❆\n' +
                            '                </div>\n' +
                            '                <div class="wcb-background-effect-snowflake">\n' +
                            '                    ❄\n' +
                            '                </div>\n' +
                            '            </div>');
                        break;

                    case 'snowflakes-1':
                        $overlay.removeClass(xr[1]).html('<div class="wcb-background-effect-snowflakes-1" aria-hidden="true">\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '                <span></span>\n' +
                            '            </div>');
                        break;
                    case 'snowflakes-2-1':
                    case 'snowflakes-2-2':
                    case 'snowflakes-2-3':
                        $overlay.attr('class', 'wcb-md-overlay wcb-background-effect-snowflakes-2 wcb-background-effect-' + newval).html('<i></i>');
                        break;
                }
            }
        });
    });

    /* Google recaptcha */
    window.addEventListener('load', function () {
        if (woocommerce_coupon_box_design_params.wcb_recaptcha == 1) {
            if (woocommerce_coupon_box_design_params.wcb_recaptcha_version == 2) {
                wcb_reCaptchaV2Onload();
            } else {
                wcb_reCaptchaV3Onload();
                $('.wcb-recaptcha-field').hide();
            }
        } else {
            $('.wcb-recaptcha-field').hide();
        }
    });

    function wcb_reCaptchaV3Onload() {
        grecaptcha.ready(function () {
            grecaptcha.execute(woocommerce_coupon_box_design_params.wcb_recaptcha_site_key, {action: 'homepage'}).then(function (token) {
            });
        });
    }

    function wcb_reCaptchaV2Onload() {
        if ($('.wcb-recaptcha-field').length == 0) {
            return true;
        }
        for (let j = 0; j < $('.wcb-recaptcha-field').length; j++) {
            let container = $('.wcb-recaptcha-field').eq(j);
            if (container.find('.wcb-recaptcha').length == 0 || container.find('.wcb-recaptcha iframe').length) {
                return true;
            }
            grecaptcha.render('wcb-recaptcha-' + (j + 1), {

                'sitekey': woocommerce_coupon_box_design_params.wcb_recaptcha_site_key,

                // 'callback' : wcb_validateRecaptcha,
                //
                // 'expired-callback' : wcb_expireRecaptcha,

                'theme': woocommerce_coupon_box_design_params.wcb_recaptcha_secret_theme,

                'isolated': false
            });

        }


        let old_width = $('.wcb-coupon-box-3 .wcb-recaptcha > div').width();
        let parent_width = $('.wcb-coupon-box-3 .wcb-recaptcha').width();
        $('.wcb-coupon-box-3 .wcb-recaptcha > div').css({transform: 'scale(' + parent_width / old_width + ',1)'});
    }

    /* button 'no, thanks' */
    wp.customize('woo_coupon_box_params[wcb_no_thank_button_enable]', function (value) {
        value.bind(function (newval) {
            if (newval) {
                $('.wcb-md-close-never-reminder-field').show();
            } else {
                $('.wcb-md-close-never-reminder-field').hide();
            }
        });
    });
    wp.customize('woo_coupon_box_params[wcb_no_thank_button_title' + language_control + ']', function (value) {
        value.bind(function (newval) {
            $('.wcb-md-close-never-reminder').html(newval);
        });
    });
    wp.customize('woo_coupon_box_params[wcb_no_thank_button_border_radius]', function (value) {
        value.bind(function (newval) {
            $('.wcb-md-close-never-reminder').css({'border-radius': newval + 'px'});
        });
    });
    wp.customize('woo_coupon_box_params[wcb_no_thank_button_color]', function (value) {
        value.bind(function (newval) {
            $('.wcb-md-close-never-reminder').css({'color': newval});
        });
    });
    wp.customize('woo_coupon_box_params[wcb_no_thank_button_bg_color]', function (value) {
        value.bind(function (newval) {
            $('.wcb-md-close-never-reminder').css({'background': newval});
        });
    });
})(jQuery);
