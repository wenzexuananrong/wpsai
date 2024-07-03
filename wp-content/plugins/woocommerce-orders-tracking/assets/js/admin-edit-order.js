jQuery(document).ready(function ($) {
    'use strict';
    /**
     * @var vi_wot_edit_order
     */
    let custom_carriers_list = JSON.parse(vi_wot_edit_order.custom_carriers_list),
        active_carriers = vi_wot_edit_order.active_carriers;
    let global_tracking_number = '';
    wotv_get_shipping_carriers_html();
    $('.woo-orders-tracking-edit-tracking-other-carrier-country').select2({
        placeholder: "Please fill in your shipping country name",
        theme: "wotv-select2-custom-country"
    });
    $(document).on('keydown', function (e) {
        if (!$('.woo-orders-tracking-edit-tracking-container').hasClass('woo-orders-tracking-hidden')) {
            if (!$('.woo-orders-tracking-edit-tracking-container').hasClass('woo-orders-tracking-edit-tracking-container-all')) {
                if (e.keyCode == 13) {
                    $('.woo-orders-tracking-edit-tracking-button-save').click();
                } else if (e.keyCode == 27) {
                    $('.woo-orders-tracking-edit-tracking-button-cancel').click();
                }
            }
        }
    });
    //Add new tracking for order item
    $(document).on('click', '.woo-orders-tracking-item-add-new-tracking', function (){
        let item_id = $(this).data('item_id');
        console.log(item_id);
        let last_track_detail = $(`.woo-orders-tracking-button-edit[data-item_id="${item_id}"]`).last().closest('.woo-orders-tracking-container'),
            last_track_column = $(`.woo-orders-tracking-tracking-number-container[data-item_id="${item_id}"]`).last(),
            quantity_index = $(`.woo-orders-tracking-button-edit[data-item_id="${item_id}"]`).length + 1;
        let current_track_column = last_track_column.clone();
        current_track_column.attr('class','woo-orders-tracking-tracking-number-container woo-orders-tracking-hidden');
        current_track_column.data('tracking_number','');
        current_track_column.data('carrier_slug','');
        current_track_column.data('paypal_status','0');
        current_track_column.data('digital_delivery','0');
        current_track_column.data('quantity_index',quantity_index);
        current_track_column.data('tracking_url','');
        current_track_column.data('carrier_name','');
        current_track_column.html('<span class="dashicons dashicons-edit woo-orders-tracking-tracking-service-action-button woo-orders-tracking-edit-order-tracking-item" title="Edit tracking"></span>')
        current_track_column.insertAfter(last_track_column);
        let current_track_detail = last_track_detail.clone();
        current_track_detail.find('.woo-orders-tracking-item-tracking-code-value').attr('title','Tracking carrier').html('<a href="" target="_blank"></a>');
        current_track_detail.find('.woo-orders-tracking-button-edit').data('tracking_number','');
        current_track_detail.find('.woo-orders-tracking-button-edit').data('tracking_url','');
        current_track_detail.find('.woo-orders-tracking-button-edit').data('carrier_name','');
        current_track_detail.find('.woo-orders-tracking-button-edit').data('carrier_id','');
        current_track_detail.find('.woo-orders-tracking-button-edit').data('digital_delivery','0');
        current_track_detail.find('.woo-orders-tracking-button-edit').attr('data-quantity_index',quantity_index);
        current_track_detail.find('.woo-orders-tracking-item-tracking-button-add-to-paypal-container').removeClass('woo-orders-tracking-paypal-active').addClass('woo-orders-tracking-paypal-inactive');
        current_track_detail.insertAfter(last_track_detail);
        current_track_detail.find('.woo-orders-tracking-button-edit').trigger('click');
    });
    /*Button add tracking number to paypal*/
    $(document).on('click', '.woo-orders-tracking-paypal-active', function () {
        let $button = $(this);
        let $paypal_image = $button.find('.woo-orders-tracking-item-tracking-button-add-to-paypal');

        $.ajax({
            url: vi_wot_edit_order.ajax_url,
            type: 'POST',
            dataType: 'JSON',
            data: {
                action: 'vi_woo_orders_tracking_add_tracking_to_paypal',
                item_id: $button.data('item_id'),
                order_id: $button.data('order_id'),
                action_nonce: $('#_vi_wot_item_nonce').val(),
            },
            beforeSend: function () {

                $button.removeClass('woo-orders-tracking-paypal-active').addClass('woo-orders-tracking-paypal-inactive');
                $paypal_image.attr('src', vi_wot_edit_order.loading_image);
            },
            success: function (response) {
                if (response.status === 'success') {
                    if (response.paypal_added_trackings) {
                        $('.woo-orders-tracking-item-tracking-paypal-added-tracking-numbers-values').val(response.paypal_added_trackings);
                    }
                    if (response.paypal_button_title) {
                        $paypal_image.attr('title', response.paypal_button_title);
                    }
                    villatheme_admin_show_message(response.message, response.status, response.message_content, false, 5000);
                } else {
                    $button.removeClass('woo-orders-tracking-paypal-inactive').addClass('woo-orders-tracking-paypal-active');
                    villatheme_admin_show_message(response.message, response.status, response.message_content);
                }
            },
            error: function (err) {
                villatheme_admin_show_message('Error', 'error', err.responseText.replace(/<\/?[^>]+(>|$)/g, ""));
                $button.removeClass('woo-orders-tracking-paypal-inactive').addClass('woo-orders-tracking-paypal-active');
            },
            complete: function () {
                $paypal_image.attr('src', vi_wot_edit_order.paypal_image);
            }
        });
    });
    $(document).on('click', '.woo-orders-tracking-button-edit', function () {
        if (vi_wot_edit_order.edit_single_tracking_old_ui) {
            $('.woo-orders-tracking-edit-tracking-container').removeClass('woo-orders-tracking-edit-tracking-container-all');
            $(this).addClass('woo-orders-tracking-button-editing');
            $('.woo-orders-tracking-edit-tracking-button-save').addClass('woo-orders-tracking-edit-tracking-save-only-one-item');
            vi_wotg_edit_tracking_show();
            let data = $(this).data(), tracking_number = data['tracking_number'];
            if (tracking_number === undefined) {
                tracking_number = data['tracking_code'];
            }
            $('#woo-orders-tracking-edit-tracking-number').val(tracking_number);
            global_tracking_number = tracking_number;
            if (data['tracking_url']) {
                $('.woo-orders-tracking-edit-tracking-carrier').val('shipping-carriers').trigger('change');
                if (data['carrier_id']) {
                    wotv_get_shipping_carriers_html(data['carrier_id']);
                    $('.woo-orders-tracking-edit-tracking-shipping-carrier').val(data['carrier_id']).trigger('change');
                } else {
                    wotv_get_shipping_carriers_html();
                    if (vi_wot_edit_order.shipping_carrier_default && data['tracking_url'].indexOf(tracking_number) !== -1) {
                        let pattern = vi_wot_edit_order.shipping_carrier_default_url_check,
                            pattern_url_check = data['tracking_url'].split(tracking_number, 1)[0];
                        pattern = pattern.split('{tracking_number}', 1)[0];
                        if (pattern === pattern_url_check) {
                            $('.woo-orders-tracking-edit-tracking-shipping-carrier').val(vi_wot_edit_order.shipping_carrier_default).trigger('change');
                        } else {
                            $('.woo-orders-tracking-edit-tracking-carrier').val('other').trigger('change');
                        }
                    } else {
                        $('.woo-orders-tracking-edit-tracking-carrier').val('other').trigger('change');
                        $('.woo-orders-tracking-edit-tracking-other-carrier-name').val(data['carrier_name']).trigger('change');
                    }
                }
                if ($('.woo-orders-tracking-edit-tracking-carrier').val() === 'other') {
                    $('#woo-orders-tracking-edit-tracking-other-carrier-url').val(data['tracking_url'].replace(tracking_number, '{tracking_number}'));
                }
            } else {
                if (tracking_number) {
                    $('.woo-orders-tracking-edit-tracking-carrier').val('shipping-carriers').trigger('change');
                    if (data['carrier_id']) {
                        wotv_get_shipping_carriers_html(data['carrier_id']);
                        $('.woo-orders-tracking-edit-tracking-shipping-carrier').val(data['carrier_id']).trigger('change');
                    } else {
                        wotv_get_shipping_carriers_html();
                    }
                } else {
                    wotv_get_shipping_carriers_html();
                    $('.woo-orders-tracking-edit-tracking-carrier').val('shipping-carriers').trigger('change');
                    if (vi_wot_edit_order.shipping_carrier_default) {
                        $('.woo-orders-tracking-edit-tracking-shipping-carrier').val(vi_wot_edit_order.shipping_carrier_default).trigger('change');
                    }
                }
            }
            $('.woo-orders-tracking-edit-tracking-save-only-one-item').attr({
                'data-order_id': data['order_id'],
                'data-item_id': data['item_id'],
                'data-item_name': data['item_name']
            });
        } else {
            let $button = $(this), $edit_tracking = $('.woo-orders-tracking-tracking-number-container');
            for (let i = 0; i < $edit_tracking.length; i++) {
                if ($edit_tracking.eq(i).data('item_id') == $button.data('item_id') && $edit_tracking.eq(i).data('quantity_index') === $button.data('quantity_index')) {
                    $edit_tracking.eq(i).find('.woo-orders-tracking-edit-order-tracking-item').trigger('click');
                    break;
                }
            }
        }
    });
    $('#woo-orders-tracking-edit-tracking-other-carrier-url').on('keyup', function () {
        let carrier_url = $(this).val();
        if (carrier_url.indexOf('{tracking_number}') === -1) {
            $(this).parent().find('.wotv-error-tracking-url').removeClass('woo-orders-tracking-hidden');
        } else {
            $(this).parent().find('.wotv-error-tracking-url').addClass('woo-orders-tracking-hidden');
        }
    });
    $(document).on('click', '.woo-orders-tracking-button-edit-all-tracking-number', function () {
        wotv_get_shipping_carriers_html();
        if ($('.woo-orders-tracking-button-edit').length === 1) {
            $('.woo-orders-tracking-button-edit').click();
        } else {
            $('.woo-orders-tracking-edit-tracking-button-save').addClass('woo-orders-tracking-edit-tracking-save-all-item');
            vi_wotg_edit_tracking_show();

            $('.woo-orders-tracking-edit-tracking-carrier').val('shipping-carriers').trigger('change');
            $('.woo-orders-tracking-edit-tracking-shipping-carrier').val(vi_wot_edit_order.shipping_carrier_default).trigger('change');
            let data = $(this).data();
            $('.woo-orders-tracking-edit-tracking-save-all-item').attr({'data-order_id': data['order_id']});
        }
    });

    $(document).on('click', '.woo-orders-tracking-overlay, .woo-orders-tracking-edit-tracking-close, .woo-orders-tracking-edit-tracking-button-cancel ', function () {
        vi_wotg_edit_tracking_hide();
    });
    $(document).on('change', '.woo-orders-tracking-edit-tracking-number', function () {
        global_tracking_number = $(this).val();
    });
    $(document).on('change', '.woo-orders-tracking-edit-tracking-shipping-carrier', function () {
        let $tracking_number = $('.woo-orders-tracking-edit-tracking-number');
        let selected_carrier = vi_wotg_get_custom_carrier_by_slug($(this).val());
        if (selected_carrier && selected_carrier.hasOwnProperty('digital_delivery') && selected_carrier.digital_delivery == 1) {
            $tracking_number.val('');
            $tracking_number.prop('disabled', true);
        } else {
            $tracking_number.val(global_tracking_number);
            $tracking_number.prop('disabled', false);
        }
    });
    $(document).on('change', '.woo-orders-tracking-edit-tracking-carrier', function () {
        let $tracking_number = $('.woo-orders-tracking-edit-tracking-number');
        switch ($(this).val()) {
            case 'other':
                $('.woo-orders-tracking-edit-tracking-content-body-row-shipping-carrier').addClass('woo-orders-tracking-hidden');
                $('.woo-orders-tracking-edit-tracking-content-body-row-service-carrier').addClass('woo-orders-tracking-hidden');
                $('.woo-orders-tracking-edit-tracking-content-body-row-other-carrier').removeClass('woo-orders-tracking-hidden');
                $tracking_number.val(global_tracking_number);
                $tracking_number.prop('disabled', false);
                break;
            case 'shipping-carriers':
                $('.woo-orders-tracking-edit-tracking-content-body-row-other-carrier').addClass('woo-orders-tracking-hidden');
                $('.woo-orders-tracking-edit-tracking-content-body-row-service-carrier').addClass('woo-orders-tracking-hidden');
                $('.woo-orders-tracking-edit-tracking-content-body-row-shipping-carrier').removeClass('woo-orders-tracking-hidden');
                let selected_carrier = vi_wotg_get_custom_carrier_by_slug($('.woo-orders-tracking-edit-tracking-shipping-carrier').val());
                if (selected_carrier && selected_carrier.hasOwnProperty('digital_delivery') && selected_carrier.digital_delivery == 1) {
                    $tracking_number.val('');
                    $tracking_number.prop('disabled', true);
                } else {
                    $tracking_number.val(global_tracking_number);
                    $tracking_number.prop('disabled', false);
                }
                break;
            default:
                $(this).val('other').trigger('change');
        }
    });

    $(document).on('click', '.woo-orders-tracking-edit-tracking-save-only-one-item', function () {
        let carrier_type = $('.woo-orders-tracking-edit-tracking-carrier').val(),
            editing = $('.woo-orders-tracking-button-editing'),
            tracking_number = $('#woo-orders-tracking-edit-tracking-number').val(),
            item_data = {
                'order_id': $(this).attr('data-order_id'),
                'item_id': $(this).attr('data-item_id'),
                'item_name': $(this).attr('data-item_name'),
            };
        let shipping_carrier_id = $('#woo-orders-tracking-edit-tracking-shipping-carrier').val();
        $('.woo-orders-tracking-edit-tracking-content-body-row-error').addClass('woo-orders-tracking-hidden');
        switch (carrier_type) {
            case 'other':
                let carrier_name = $('#woo-orders-tracking-edit-tracking-other-carrier-name').val(),
                    shipping_country = $('.woo-orders-tracking-edit-tracking-other-carrier-country').val(),
                    tracking_url = $('#woo-orders-tracking-edit-tracking-other-carrier-url').val();
                if (!tracking_number || !tracking_url || !carrier_name || !shipping_country) {
                    $('.woo-orders-tracking-edit-tracking-content-body-row-error').removeClass('woo-orders-tracking-hidden');
                    $('.woo-orders-tracking-edit-tracking-content-body-row-error p').html(vi_wot_edit_order.error_empty_field);
                    return false;
                }
                let data_new_carrier = {
                    action: 'wotv_save_track_info_item',
                    action_nonce: $('#_vi_wot_item_nonce').val(),
                    tracking_number: tracking_number,
                    change_order_status: $('#woo-orders-tracking-order_status').val(),
                    send_mail: $('#woo-orders-tracking-edit-tracking-send-email').prop('checked') ? 'yes' : 'no',
                    send_sms: $('#woo-orders-tracking-edit-tracking-send-sms').prop('checked') ? 'yes' : 'no',
                    add_to_paypal: $('#woo-orders-tracking-edit-tracking-add-to-paypal').prop('checked') ? 'yes' : 'no',
                    order_id: item_data['order_id'],
                    item_id: item_data['item_id'],
                    item_name: item_data['item_name'],
                    carrier_id: '',
                    carrier_name: carrier_name,
                    shipping_country: shipping_country,
                    tracking_url: tracking_url,
                    add_new_carrier: 1,
                };
                wotv_save_track_info_item(data_new_carrier, editing);
                break;
            case 'shipping-carriers':
                if (!shipping_carrier_id) {
                    $('.woo-orders-tracking-edit-tracking-content-body-row-error').removeClass('woo-orders-tracking-hidden');
                    $('.woo-orders-tracking-edit-tracking-content-body-row-error p').html(vi_wot_edit_order.error_empty_field);
                    return false;
                } else if (!tracking_number) {
                    let found_carrier = vi_wotg_get_custom_carrier_by_slug(shipping_carrier_id);
                    let digital_delivery = 0;
                    if (found_carrier && found_carrier.hasOwnProperty('digital_delivery')) {
                        digital_delivery = found_carrier.digital_delivery;
                    }
                    if (digital_delivery != 1) {
                        $('.woo-orders-tracking-edit-tracking-content-body-row-error').removeClass('woo-orders-tracking-hidden');
                        $('.woo-orders-tracking-edit-tracking-content-body-row-error p').html(vi_wot_edit_order.error_empty_field);
                        return false;
                    }
                }

                let shipping_data = {
                    action: 'wotv_save_track_info_item',
                    action_nonce: $('#_vi_wot_item_nonce').val(),
                    carrier_id: shipping_carrier_id,
                    carrier_name: $('#woo-orders-tracking-edit-tracking-shipping-carrier option[value="' + shipping_carrier_id + '"').text(),
                    tracking_number: tracking_number,
                    change_order_status: $('#woo-orders-tracking-order_status').val(),
                    send_mail: $('#woo-orders-tracking-edit-tracking-send-email').prop('checked') ? 'yes' : 'no',
                    send_sms: $('#woo-orders-tracking-edit-tracking-send-sms').prop('checked') ? 'yes' : 'no',
                    add_to_paypal: $('#woo-orders-tracking-edit-tracking-add-to-paypal').prop('checked') ? 'yes' : 'no',
                    order_id: item_data['order_id'],
                    item_id: item_data['item_id'],
                    item_name: item_data['item_name'],
                };
                wotv_save_track_info_item(shipping_data, editing);
                break;
        }

        $(this).removeAttr('data-order_id  data-item_id data-item_name');
    });

    function wotv_save_track_info_item(data, editing) {
        let $container = editing.closest('.woo-orders-tracking-container');
        data.quantity_index = editing.data('quantity_index');
        $.ajax({
            url: vi_wot_edit_order.ajax_url,
            type: 'POST',
            dataType: 'JSON',
            data: data,
            beforeSend: function () {
                $('.woo-orders-tracking-saving-overlay').removeClass('woo-orders-tracking-hidden');
            },
            success: function (response) {
                if (response.hasOwnProperty('change_order_status') && response.change_order_status) {
                    $('body').find('#order_status').val(response.change_order_status).trigger('change');
                }
                if (response.tracking_service_status === 'error') {
                    villatheme_admin_show_message(response.tracking_service, response.tracking_service_status, response.tracking_service_message);
                }
                if (response.status === 'error') {
                    villatheme_admin_show_message(response.message, response.status);
                } else {
                    /*Update data*/
                    villatheme_admin_show_message(response.message, response.status, '', false, 5000);
                    editing.data('tracking_number', response.tracking_number);
                    editing.data('tracking_url', response.carrier_url);
                    editing.data('carrier_id', response.carrier_id);
                    editing.data('carrier_name', response.carrier_name);
                    editing.data('digital_delivery', response.digital_delivery);
                    editing.closest('.woo-orders-tracking-container').find('.woo-orders-tracking-item-tracking-code-value').html('<a target="_blank" href="' + response.tracking_url_show + '">' + response.tracking_number + '</a>').attr('title', response.carrier_name);
                    let $shipping_carrier_select = $('#woo-orders-tracking-edit-tracking-shipping-carrier');
                    if (data.hasOwnProperty('add_new_carrier') && response.carrier_id) {
                        let option = {
                            id: response.carrier_id,
                            text: response.carrier_name
                        };

                        let newOption = new Option(option.text, option.id, false, false);
                        $shipping_carrier_select.append(newOption).trigger('change');
                    }

                    if (response.paypal_status === 'error') {
                        villatheme_admin_show_message('Can not add tracking to PayPal', 'error', response.paypal_message);
                    }
                    let $button_add_pay_pal_container = $container.find('.woo-orders-tracking-item-tracking-button-add-to-paypal-container');
                    if (response.paypal_button_class === 'inactive') {
                        $button_add_pay_pal_container.removeClass('woo-orders-tracking-paypal-active').addClass('woo-orders-tracking-paypal-inactive').attr('title', response.paypal_button_title);
                        $button_add_pay_pal_container.find('.woo-orders-tracking-item-tracking-button-add-to-paypal').attr('title', response.paypal_button_title);
                    } else if (response.paypal_button_class === 'active') {
                        $button_add_pay_pal_container.removeClass('woo-orders-tracking-paypal-inactive').addClass('woo-orders-tracking-paypal-active');
                        $button_add_pay_pal_container.find('.woo-orders-tracking-item-tracking-button-add-to-paypal').attr('title', response.paypal_button_title);
                    }
                    if (response.paypal_added_trackings) {
                        $('.woo-orders-tracking-item-tracking-paypal-added-tracking-numbers-values').val(response.paypal_added_trackings);
                    }
                    if (response.sms_status) {
                        if (response.sms_status === 'success') {
                            villatheme_admin_show_message(response.sms_message_title, response.sms_status, response.sms_message, false, 5000);
                        } else {
                            villatheme_admin_show_message(response.sms_message_title, response.sms_status, response.sms_message);
                        }
                    }
                    $(document.body).trigger('woo_orders_tracking_admin_edit_single_tracking_old_ui_success', response);
                }
            },
            error: function (err) {
                villatheme_admin_show_message('Error', 'error', err.responseText.replace(/<\/?[^>]+(>|$)/g, ""));
            },
            complete: function () {
                $('.woo-orders-tracking-saving-overlay').addClass('woo-orders-tracking-hidden');
                vi_wotg_edit_tracking_hide();
            }
        });
    }

    function vi_wotg_enable_scroll() {
        let scrollTop = parseInt($('html').css('top'));
        $('html').removeClass('vi_wotg-noscroll');
        $('html,body').scrollTop(-scrollTop);
    }

    function vi_wotg_disable_scroll() {
        if ($(document).height() > $(window).height()) {
            let scrollTop = ($('html').scrollTop()) ? $('html').scrollTop() : $('body').scrollTop(); // Works for Chrome, Firefox, IE...
            $('html').addClass('vi_wotg-noscroll').css('top', -scrollTop);
        }
    }

    function vi_wotg_edit_tracking_hide() {
        $('.woo-orders-tracking-edit-tracking-carrier').val('shipping-carriers').trigger('change');
        $('.woo-orders-tracking-button-edit').removeClass('woo-orders-tracking-button-editing');
        $('.woo-orders-tracking-edit-tracking-button-save').attr('class', 'button button-primary woo-orders-tracking-edit-tracking-button-save');
        $('.woo-orders-tracking-edit-tracking-container').addClass('woo-orders-tracking-hidden');
        vi_wotg_enable_scroll();
    }

    function vi_wotg_edit_tracking_show() {
        $('.woo-orders-tracking-edit-tracking-container').removeClass('woo-orders-tracking-hidden');

        vi_wotg_disable_scroll();
    }

    function vi_wotg_get_custom_carrier_by_slug(carrier_slug) {
        return wot_get_carrier_by_slug(carrier_slug, custom_carriers_list);
    }

    function wotv_get_shipping_carriers_html(carrier_slug = '') {
        let shipping_carriers_define_list,
            carriers,
            html = '',
            shipping_carrier_default = vi_wot_edit_order.shipping_carrier_default;
        shipping_carriers_define_list = JSON.parse(vi_wot_edit_order.shipping_carriers_define_list);
        carriers = shipping_carriers_define_list.concat(custom_carriers_list);
        let temp_active_carriers = active_carriers.concat();
        if (carrier_slug && temp_active_carriers.indexOf(carrier_slug) < 0) {
            temp_active_carriers.push(carrier_slug);
        }
        if (carriers.length > 0) {
            carriers = wot_sort_carriers(carriers);
            if (temp_active_carriers.length > 0) {
                for (let i = 0; i < carriers.length; i++) {
                    if (temp_active_carriers.indexOf(carriers[i].slug) > -1) {
                        html += '<option value="' + carriers[i].slug + '">' + carriers[i].name + '</option>';
                    }
                }
            } else {
                for (let i = 0; i < carriers.length; i++) {
                    html += '<option value="' + carriers[i].slug + '">' + carriers[i].name + '</option>';
                }
            }

            $('.woo-orders-tracking-edit-tracking-shipping-carrier').html(html).val(shipping_carrier_default).trigger('change').select2({
                placeholder: "Please fill in your shipping carrier name",
                theme: "wotv-select2-custom-carrier",
                dropdownParent: $('.woo-orders-tracking-edit-tracking-container')
            });
        }
    }

    $(document).on('click', '.woo-orders-tracking-edit-order-tracking-all', function () {
        $('.woo-orders-tracking-edit-order-tracking').trigger('click');
        $('.woo-orders-tracking-edit-tracking-container').addClass('woo-orders-tracking-edit-tracking-container-all');
    });
    $(document.body).on('woo_orders_tracking_admin_edit_trackings_success', function (e, response) {
        if (response.hasOwnProperty('order_status_changed') && response.order_status_changed) {
            $('body').find('#order_status').val(response.order_status_changed).trigger('change');
        }
        for (let i = 0; i < response.results.length; i++) {
            if (response.results[i].is_updated && response.results[i].item_id) {
                let tracking_data = response.results[i]['tracking_data'],
                    $item = $(`.woo-orders-tracking-button-edit[data-item_id="${response.results[i].item_id}"][data-quantity_index="${tracking_data.quantity_index}"]`)
                if ($item.length) {
                    let item_data = $item.data(),
                        $tracking_number = $item.closest('.woo-orders-tracking-container').find('.woo-orders-tracking-item-tracking-code-value');
                    for (let ele in item_data) {
                        if (item_data.hasOwnProperty(ele) && tracking_data.hasOwnProperty(ele)) {
                            $item.data(ele, tracking_data[ele]);
                        }
                    }
                    $tracking_number.attr('title', vi_wot_edit_order.i18n_tracking_number_field_title.replace('%s', tracking_data.carrier_name));
                    $tracking_number.find('>a').attr('href', tracking_data.tracking_url).html(tracking_data.tracking_number);
                }
            }
        }
    });
});