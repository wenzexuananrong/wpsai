'use strict';
jQuery(document).ready(function ($) {
    jQuery('.vi-ui.tabular.menu .item').tab({history: true, historyType: 'hash'});

    let nameModal = $('.wn-modal-generate-name-by-country');
    let namesTable = $('.wn-name-by-country-list tbody');
    let countriesDropdown = nameModal.find('.wn-generate-name-countries').clone().removeClass();
    let fakerLocales = faker.locales,
        languageDropdown = $('.wn-generate-name-language');

    for (let langCode in fakerLocales) {
        let data = fakerLocales[langCode];
        if (typeof data.name !== 'undefined') {
            languageDropdown.append(`<option value="${langCode}">${data.title}</option>`);
        }
    }

    jQuery('.vi-ui.checkbox').checkbox();
    jQuery('select.vi-select-post_type').dropdown({
        onChange: function (value, text) {
            jQuery('.vi-select-items').html('');
        }
    });
    jQuery('select.vi-ui.dropdown').dropdown();
    /*Search*/

    jQuery(".product-search").select2({
        placeholder: "Please fill in your  product title",
        ajax: {
            url: "admin-ajax.php?action=wcn_search_product",
            dataType: 'json',
            type: "GET",
            quietMillis: 50,
            delay: 250,
            data: function (params) {
                var post_type = jQuery('select[name="ecommerce_notification_params[post_type]"]').val();

                return {
                    keyword: params.term,
                    post_type: post_type
                };
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) {
            return markup;
        }, // let our custom formatter work
        minimumInputLength: 1,
        closeOnSelect: false
    });

    /*Add new message*/
    jQuery('.add-message').on('click', function () {
        var tr = jQuery('.message-purchased').find('tr').last().clone();
        jQuery(tr).appendTo('.message-purchased');
        remove_message()
    });
    remove_message();

    function remove_message() {
        jQuery('.remove-message').unbind();
        jQuery('.remove-message').on('click', function () {
            if (confirm("Would you want to remove this message?")) {
                if (jQuery('.message-purchased tr').length > 1) {
                    var tr = jQuery(this).closest('tr').remove();
                }
            } else {

            }
        });
    }

    /*Add field depend*/
    /*Products*/

    jQuery('.virtual_address').dependsOn({
        'select[name="ecommerce_notification_params[country]"]': {
            values: ['1']
        }
    });
    jQuery('.detect_address').dependsOn({
        'select[name="ecommerce_notification_params[country]"]': {
            values: ['0']
        }
    });

    /*Time*/
    jQuery('.time_loop').dependsOn({
        'input[name="ecommerce_notification_params[loop]"]': {
            checked: true
        }
    });
    /*Initial time random*/
    jQuery('.initial_delay_random').dependsOn({
        'input[name="ecommerce_notification_params[initial_delay_random]"]': {
            checked: true
        }
    });
    /*Logs*/
    jQuery('.save_logs').dependsOn({
        'input[name="ecommerce_notification_params[save_logs]"]': {
            checked: true
        }
    });
// Color picker
    jQuery('.color-picker').iris({
        change: function (event, ui) {
            jQuery(this).parent().find('.color-picker').css({backgroundColor: ui.color.toString()});
            var ele = jQuery(this).data('ele');
            if (ele == 'highlight') {
                jQuery('#message-purchased').find('a').css({'color': ui.color.toString()});
            } else if (ele == 'textcolor') {
                jQuery('#message-purchased').css({'color': ui.color.toString()});
            } else {
                jQuery('#message-purchased').css({backgroundColor: ui.color.toString()});
            }
        },
        hide: true,
        border: true
    }).on('click', function () {
        jQuery('.iris-picker').hide();
        jQuery(this).closest('td').find('.iris-picker').show();
    });

    jQuery('body').on('click', function () {
        jQuery('.iris-picker').hide();
    });
    jQuery('.color-picker').on('click', function (event) {
        event.stopPropagation();
    });
    jQuery('input[name="ecommerce_notification_params[position]"]').on('change', function () {
        var data = jQuery(this).val();
        if (data == 1) {
            jQuery('#message-purchased').removeClass('top_left top_right').addClass('bottom_right');
        } else if (data == 2) {
            jQuery('#message-purchased').removeClass('bottom_right top_right').addClass('top_left');
        } else if (data == 3) {
            jQuery('#message-purchased').removeClass('bottom_right top_left').addClass('top_right');
        } else {
            jQuery('#message-purchased').removeClass('bottom_right top_left top_right');
        }
    });
    jQuery('select[name="ecommerce_notification_params[image_position]"]').on('change', function () {
        var data = jQuery(this).val();
        if (data == 1) {
            jQuery('#message-purchased').addClass('img-right');
        } else {
            jQuery('#message-purchased').removeClass('img-right');
        }
    });

    /*add optgroup to select box semantic*/
    jQuery('.vi-ui.dropdown.selection').has('optgroup').each(function () {
        var $menu = jQuery('<div/>').addClass('menu');
        jQuery(this).find('optgroup').each(function () {
            $menu.append("<div class=\"header\">" + this.label + "</div><div class=\"divider\"></div>");
            return jQuery(this).children().each(function () {
                return $menu.append("<div class=\"item\" data-value=\"" + this.value + "\">" + this.innerHTML + "</div>");
            });
        });
        return jQuery(this).find('.menu').html($menu.html());
    });

    let close_icon = jQuery('#notify-close');
    if (jQuery('input[name="ecommerce_notification_params[show_close_icon]"]').prop('checked')) {
        close_icon.show()
    } else {
        close_icon.hide()
    }

    jQuery('input[name="ecommerce_notification_params[show_close_icon]"]').on('change', function () {
        if (jQuery(this).prop('checked')) {
            close_icon.show()
        } else {
            close_icon.hide()
        }
    });
    jQuery('input[name="ecommerce_notification_params[background_image]"]').on('change', function () {
        var data = jQuery(this).val();
        var init_data = {
            'black': {
                'hightlight': '#ffffff',
                'text': '#ffffff',

            },
            'red': {
                'hightlight': '#ffffff',
                'text': '#ffffff',

            },
            'pink': {
                'hightlight': '#ffffff',
                'text': '#ffffff',

            },
            'yellow': {
                'hightlight': '#000000',
                'text': '#000000',

            },
            'violet': {
                'hightlight': '#ffffff',
                'text': '#ffffff',

            },
            'blue': {
                'hightlight': '#ffffff',
                'text': '#ffffff',

            },
            'spring': {
                'hightlight': '#ffffff',
                'text': '#ffffff',

            },
            'grey': {
                'hightlight': '#000000',
                'text': '#000000',

            },
            'autumn': {
                'hightlight': '#ffffff',
                'text': '#ffffff',

            },
            'orange': {
                'hightlight': '#ffffff',
                'text': '#ffffff',

            },
            'summer': {
                'hightlight': '#ffffff',
                'text': '#ffffff',

            },
            'winter': {
                'hightlight': '#3c8b90',
                'text': '#3c8b90',

            },
            'black_friday': {
                'hightlight': '#ffffff',
                'text': '#ffffff',

            },
            'new_year': {
                'hightlight': '#ffffff',
                'text': '#ffffff',

            },
            'valentine': {
                'hightlight': '#ffffff',
                'text': '#ffffff',

            },
            'halloween': {
                'hightlight': '#ffffff',
                'text': '#ffffff',

            },
            'kids': {
                'hightlight': '#ffffff',
                'text': '#ffffff',

            },
            'father_day': {
                'hightlight': '#ffffff',
                'text': '#ffffff',

            },
            'mother_day': {
                'hightlight': '#ffffff',
                'text': '#ffffff',

            },
            'shoes': {
                'hightlight': '#ffffff',
                'text': '#ffffff',

            },
            't_shirt': {
                'hightlight': '#ffffff',
                'text': '#ffffff',

            },
            'christmas': {
                'hightlight': '#6bbeaa',
                'text': '#6bbeaa',

            },
        };
        if (parseInt(data) == 0) {
            jQuery('#message-purchased').removeClass('wn-extended');
            jQuery('.message-purchase-main').css({'color': '#212121', 'background-color': '#ffffff'});
            jQuery('input[name="ecommerce_notification_params[highlight_color]"]').val('#212121').trigger('change');
            jQuery('input[name="ecommerce_notification_params[text_color]"]').val('#212121').trigger('change');
            jQuery('input[name="ecommerce_notification_params[close_icon_color]"]').val('#212121').trigger('change');
            jQuery('input[name="ecommerce_notification_params[backgroundcolor]"]').val('#ffffff').trigger('change');
        } else {
            jQuery('#message-purchased').addClass('wn-extended');
            jQuery('#vi-ecommerce-notification-background-image').html('#message-purchased.wn-extended::before {background-image: url(../wp-content/plugins/ecommerce-notification/images/background/bg_' + data + '.png);');
            jQuery('input[name="ecommerce_notification_params[highlight_color]"]').val(init_data[data]['hightlight']).trigger('change');
            jQuery('input[name="ecommerce_notification_params[text_color]"]').val(init_data[data]['text']).trigger('change');
            jQuery('input[name="ecommerce_notification_params[close_icon_color]"]').val(init_data[data]['text']).trigger('change');
        }

    });

    jQuery('#message-purchased').attr('data-effect_display', '');
    jQuery('#message-purchased').attr('data-effect_hidden', '');
    jQuery('select[name="ecommerce_notification_params[message_display_effect]').on('change', function () {
        jQuery('#message-purchased').attr('data-effect_hidden', '');
        var data = jQuery(this).val(),
            message_purchased = jQuery('#message-purchased');

        switch (data) {
            case 'bounceIn':
                message_purchased.attr('data-effect_display', 'bounceIn');
                break;
            case 'bounceInDown':
                message_purchased.attr('data-effect_display', 'bounceInDown');
                break;
            case 'bounceInLeft':
                message_purchased.attr('data-effect_display', 'bounceInLeft');
                break;
            case 'bounceInRight':
                message_purchased.attr('data-effect_display', 'bounceInRight');
                break;
            case 'bounceInUp':
                message_purchased.attr('data-effect_display', 'bounceInUp');
                break;
            case 'fade-in':
                message_purchased.attr('data-effect_display', 'fade-in');
                break;
            case 'fadeInDown':
                message_purchased.attr('data-effect_display', 'fadeInDown');
                break;
            case 'fadeInDownBig':
                message_purchased.attr('data-effect_display', 'fadeInDownBig');
                break;
            case 'fadeInLeft':
                message_purchased.attr('data-effect_display', 'fadeInLeft');
                break;
            case 'fadeInLeftBig':
                message_purchased.attr('data-effect_display', 'fadeInLeftBig');
                break;
            case 'fadeInRight':
                message_purchased.attr('data-effect_display', 'fadeInRight');
                break;
            case 'fadeInRightBig':
                message_purchased.attr('data-effect_display', 'fadeInRightBig');
                break;
            case 'fadeInUp':
                message_purchased.attr('data-effect_display', 'fadeInUp');
                break;
            case 'fadeInUpBig':
                message_purchased.attr('data-effect_display', 'fadeInUpBig');
                break;
            case 'flipInX':
                message_purchased.attr('data-effect_display', 'flipInX');
                break;
            case 'flipInY':
                message_purchased.attr('data-effect_display', 'flipInY');
                break;
            case 'lightSpeedIn':
                message_purchased.attr('data-effect_display', 'lightSpeedIn');
                break;
            case 'rotateIn':
                message_purchased.attr('data-effect_display', 'rotateIn');
                break;
            case 'rotateInDownLeft':
                message_purchased.attr('data-effect_display', 'rotateInDownLeft');
                break;
            case 'rotateInDownRight':
                message_purchased.attr('data-effect_display', 'rotateInDownRight');
                break;
            case 'rotateInUpLeft':
                message_purchased.attr('data-effect_display', 'rotateInUpLeft');
                break;
            case 'rotateInUpRight':
                message_purchased.attr('data-effect_display', 'rotateInUpRight');
                break;
            case 'slideInUp':
                message_purchased.attr('data-effect_display', 'slideInUp');
                break;
            case 'slideInDown':
                message_purchased.attr('data-effect_display', 'slideInDown');
                break;
            case 'slideInLeft':
                message_purchased.attr('data-effect_display', 'slideInLeft');
                break;
            case 'slideInRight':
                message_purchased.attr('data-effect_display', 'slideInRight');
                break;
            case 'zoomIn':
                message_purchased.attr('data-effect_display', 'zoomIn');
                break;
            case 'zoomInDown':
                message_purchased.attr('data-effect_display', 'zoomInDown');
                break;
            case 'zoomInLeft':
                message_purchased.attr('data-effect_display', 'zoomInLeft');
                break;
            case 'zoomInRight':
                message_purchased.attr('data-effect_display', 'zoomInRight');
                break;
            case 'zoomInUp':
                message_purchased.attr('data-effect_display', 'zoomInUp');
                break;
            case 'rollIn':
                message_purchased.attr('data-effect_display', 'rollIn');
                break;
        }

    });

    jQuery('select[name="ecommerce_notification_params[message_hidden_effect]').on('change', function () {
        var data = jQuery(this).val(),
            message_purchased = jQuery('#message-purchased');

        switch (data) {
            case 'bounceOut':
                message_purchased.attr('data-effect_hidden', 'bounceOut');
                break;
            case 'bounceOutDown':
                message_purchased.attr('data-effect_hidden', 'bounceOutDown');
                break;
            case 'bounceOutLeft':
                message_purchased.attr('data-effect_hidden', 'bounceOutLeft');
                break;
            case 'bounceOutRight':
                message_purchased.attr('data-effect_hidden', 'bounceOutRight');
                break;
            case 'bounceOutUp':
                message_purchased.attr('data-effect_hidden', 'bounceOutUp');
                break;
            case 'fade-out':
                message_purchased.attr('data-effect_hidden', 'fade-out');
                break;
            case 'fadeOutDown':
                message_purchased.attr('data-effect_hidden', 'fadeOutDown');
                break;
            case 'fadeOutDownBig':
                message_purchased.attr('data-effect_hidden', 'fadeOutDownBig');
                break;
            case 'fadeOutLeft':
                message_purchased.attr('data-effect_hidden', 'fadeOutLeft');
                break;
            case 'fadeOutLeftBig':
                message_purchased.attr('data-effect_hidden', 'fadeOutLeftBig');
                break;
            case 'fadeOutRight':
                message_purchased.attr('data-effect_hidden', 'fadeOutRight');
                break;
            case 'fadeOutRightBig':
                message_purchased.attr('data-effect_hidden', 'fadeOutRightBig');
                break;
            case 'fadeOutUp':
                message_purchased.attr('data-effect_hidden', 'fadeOutUp');
                break;
            case 'fadeOutUpBig':
                message_purchased.attr('data-effect_hidden', 'fadeOutUpBig');
                break;
            case 'flipOutX':
                message_purchased.attr('data-effect_hidden', 'flipOutX');
                break;
            case 'flipOutY':
                message_purchased.attr('data-effect_hidden', 'flipOutY');
                break;
            case 'lightSpeedOut':
                message_purchased.attr('data-effect_hidden', 'lightSpeedOut');
                break;
            case 'rotateOut':
                message_purchased.attr('data-effect_hidden', 'rotateOut');
                break;
            case 'rotateOutDownLeft':
                message_purchased.attr('data-effect_hidden', 'rotateOutDownLeft');
                break;
            case 'rotateOutDownRight':
                message_purchased.attr('data-effect_hidden', 'rotateOutDownRight');
                break;
            case 'rotateOutUpLeft':
                message_purchased.attr('data-effect_hidden', 'rotateOutUpLeft');
                break;
            case 'rotateOutUpRight':
                message_purchased.attr('data-effect_hidden', 'rotateOutUpRight');
                break;
            case 'slideOutUp':
                message_purchased.attr('data-effect_hidden', 'slideOutUp');
                break;
            case 'slideOutDown':
                message_purchased.attr('data-effect_hidden', 'slideOutDown');
                break;
            case 'slideOutLeft':
                message_purchased.attr('data-effect_hidden', 'slideOutLeft');
                break;
            case 'slideOutRight':
                message_purchased.attr('data-effect_hidden', 'slideOutRight');
                break;
            case 'zoomOut':
                message_purchased.attr('data-effect_hidden', 'zoomOut');
                break;
            case 'zoomOutDown':
                message_purchased.attr('data-effect_hidden', 'zoomOutDown');
                break;
            case 'zoomOutLeft':
                message_purchased.attr('data-effect_hidden', 'zoomOutLeft');
                break;
            case 'zoomOutRight':
                message_purchased.attr('data-effect_hidden', 'zoomOutRight');
                break;
            case 'zoomOutUp':
                message_purchased.attr('data-effect_hidden', 'zoomOutUp');
                break;
            case 'rollOut':
                message_purchased.attr('data-effect_hidden', 'rollOut');
                break;
        }

    });
    /**
     * Start Get download key
     */
    jQuery('.villatheme-get-key-button').one('click', function (e) {
        let v_button = jQuery(this);
        v_button.addClass('loading');
        let data = v_button.data();
        let item_id = data.id;
        let app_url = data.href;
        let main_domain = window.location.hostname;
        main_domain = main_domain.toLowerCase();
        let popup_frame;
        e.preventDefault();
        let download_url = v_button.attr('data-download');
        popup_frame = window.open(app_url, "myWindow", "width=380,height=600");
        window.addEventListener('message', function (event) {
            /*Callback when data send from child popup*/
            let obj = jQuery.parseJSON(event.data);
            let update_key = '';
            let message = obj.message;
            let support_until = '';
            let check_key = '';
            if (obj['data'].length > 0) {
                for (let i = 0; i < obj['data'].length; i++) {
                    if (obj['data'][i].id == item_id && (obj['data'][i].domain == main_domain || obj['data'][i].domain == '' || obj['data'][i].domain == null)) {
                        if (update_key == '') {
                            update_key = obj['data'][i].download_key;
                            support_until = obj['data'][i].support_until;
                        } else if (support_until < obj['data'][i].support_until) {
                            update_key = obj['data'][i].download_key;
                            support_until = obj['data'][i].support_until;
                        }
                        if (obj['data'][i].domain == main_domain) {
                            update_key = obj['data'][i].download_key;
                            break;
                        }
                    }
                }
                if (update_key) {
                    check_key = 1;
                    jQuery('.villatheme-autoupdate-key-field').val(update_key);
                }
            }
            v_button.removeClass('loading');
            if (check_key) {
                jQuery('<p><strong>' + message + '</strong></p>').insertAfter(".villatheme-autoupdate-key-field");
                jQuery(v_button).closest('form').submit();
            } else {
                jQuery('<p><strong> Your key is not found. Please contact support@villatheme.com </strong></p>').insertAfter(".villatheme-autoupdate-key-field");
            }
        });
    });
    /**
     * End get download key
     */


    nameModal.modal();
    $('body').on('click', '.wn-name-by-country-remove', function () {
        $(this).closest('.wn-name-by-country-row').remove();
    }).on('click', '.wn-add-name-by-country', function () {
        nameModal.modal('show');
    });

    $('.wn-do-generate').on('click', function () {
        let qty = nameModal.find('.wn-generate-name-quantity').val();
        let gender = nameModal.find('.wn-generate-name-gender').dropdown('get value');
        let countries = nameModal.find('.wn-generate-name-countries').dropdown('get value');
        let lang = nameModal.find('.wn-generate-name-language').dropdown('get value');

        if (!qty) return;

        let names = [];

        faker.locale = lang;

        for (let i = 0; i < qty; i++) {
            names.push(faker.name.firstName(gender));
        }

        names = names.join("\n");

        let i = Date.now();
        let row = $(`<tr class="wn-name-by-country-row">
                        <td><textarea rows="3" name="ecommerce_notification_params[name_by_country][${i}][names]">${names}</textarea></td>
                        <td>
                            <select class="wn-name-by-country-row-countries vi-ui dropdown fluid" name="ecommerce_notification_params[name_by_country][${i}][countries][]" multiple>
                                ${countriesDropdown.html()}
                            </select>
                        </td>
                        <td><span class="vi-ui icon button mini red wn-name-by-country-remove"><i class="icon x"> </i></span></td>
                    </tr>`);

        row.find('.wn-name-by-country-row-countries').dropdown('set selected', countries);

        namesTable.append(row);
    });

});