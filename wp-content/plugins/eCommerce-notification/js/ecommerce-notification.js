{
    jQuery(document).ready(function () {
        'use strict';
        if (jQuery('#message-purchased').length > 0) {
            var notify = woo_notification;

            if (!_vi_ecommerce_notification_params.detect || _vi_ecommerce_notification_params.detect == 0) {
                notify.detect_address();
            }

            var el = document.getElementById('message-purchased');
            viSwipeDetect(el, function (swipedir) {
                if (swipedir !== 'none') {
                    if (parseInt(woo_notification.time_close) > 0) {
                        jQuery('#message-purchased').unbind();
                        woo_notification.setCookie('woo_notification_close', 1, 3600 * parseInt(woo_notification.time_close));
                    }
                    woo_notification.message_hide(false, swipedir);
                }
            });
        }
    });

    function vi_wn_b64DecodeUnicode(str) {
        var decodedStr = '';
        if (str) {
            try {
                decodedStr = decodeURIComponent(atob(str).split('').map(function (c) {
                    return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
                }).join(''));
            } catch (e) {
                return str;
            }
        }
        return decodedStr;
    }

    function viSwipeDetect(el, callback) {
        var touchsurface = el,
            swipedir,
            startX,
            startY,
            distX,
            distY,
            threshold = 150, //required min distance traveled to be considered swipe
            restraint = 100, // maximum distance allowed at the same time in perpendicular direction
            allowedTime = 300, // maximum time allowed to travel that distance
            elapsedTime,
            startTime,
            handleswipe = callback || function (swipedir) {
            };

        touchsurface.addEventListener(
            "touchstart",
            function (e) {
                var touchobj = e.changedTouches[0];
                swipedir = "none";
                startX = touchobj.pageX;
                startY = touchobj.pageY;
                startTime = new Date().getTime(); // record time when finger first makes contact with surface
            },
            {capture: true}
        );

        touchsurface.addEventListener(
            "touchmove",
            function (e) {
                e.preventDefault(); // prevent scrolling when inside DIV
            },
            false
        );

        touchsurface.addEventListener(
            "touchend",
            function (e) {
                var touchobj = e.changedTouches[0];
                distX = touchobj.pageX - startX; // get horizontal dist traveled by finger while in contact with surface
                distY = touchobj.pageY - startY; // get vertical dist traveled by finger while in contact with surface
                elapsedTime = new Date().getTime() - startTime; // get time elapsed
                if (elapsedTime <= allowedTime) {
                    // first condition for awipe met
                    if (Math.abs(distX) >= threshold && Math.abs(distY) <= restraint) {
                        // 2nd condition for horizontal swipe met
                        swipedir = distX < 0 ? "left" : "right"; // if dist traveled is negative, it indicates left swipe
                    } else if (
                        Math.abs(distY) >= threshold &&
                        Math.abs(distX) <= restraint
                    ) {
                        // 2nd condition for vertical swipe met
                        swipedir = distY < 0 ? "up" : "down"; // if dist traveled is negative, it indicates up swipe
                    }
                }
                handleswipe(swipedir);
            },
            false
        );
    }

    jQuery(window).on('load', function () {

        var notify = woo_notification;
        notify.id = jQuery('#message-purchased').data('product_id');
        notify.loop = _vi_ecommerce_notification_params.loop;
        notify.loop_session = _vi_ecommerce_notification_params.loop_session;
        notify.loop_session_duration = parseFloat(_vi_ecommerce_notification_params.loop_session_duration);
        notify.loop_session_total = parseInt(_vi_ecommerce_notification_params.loop_session_total);
        notify.init_delay = parseInt(_vi_ecommerce_notification_params.initial_delay);
        notify.total = parseInt(_vi_ecommerce_notification_params.notification_per_page);
        notify.display_time = parseInt(_vi_ecommerce_notification_params.display_time);
        notify.next_time = parseInt(_vi_ecommerce_notification_params.next_time);
        notify.ajax_url = _vi_ecommerce_notification_params.ajax_url;
        notify.products = _vi_ecommerce_notification_params.products;
        notify.messages = _vi_ecommerce_notification_params.messages;
        notify.image = _vi_ecommerce_notification_params.image;
        notify.redirect_target = _vi_ecommerce_notification_params.redirect_target;
        notify.time = _vi_ecommerce_notification_params.time;
        notify.display_effect = _vi_ecommerce_notification_params.display_effect;
        notify.hidden_effect = _vi_ecommerce_notification_params.hidden_effect;
        notify.messages = _vi_ecommerce_notification_params.messages;
        notify.names = _vi_ecommerce_notification_params.names;
        notify.detect = _vi_ecommerce_notification_params.detect;
        notify.billing = _vi_ecommerce_notification_params.billing;
        notify.in_the_same_cate = _vi_ecommerce_notification_params.in_the_same_cate;
        notify.message_custom = _vi_ecommerce_notification_params.message_custom;
        notify.message_number_min = _vi_ecommerce_notification_params.message_number_min;
        notify.message_number_max = _vi_ecommerce_notification_params.message_number_max;
        notify.time_close = _vi_ecommerce_notification_params.time_close;
        notify.show_close = _vi_ecommerce_notification_params.show_close;
        notify.change_virtual_time = _vi_ecommerce_notification_params.change_virtual_time;
        notify.start_virtual_time = _vi_ecommerce_notification_params.start_virtual_time;
        notify.end_virtual_time = _vi_ecommerce_notification_params.end_virtual_time;
        notify.change_message_number = _vi_ecommerce_notification_params.change_message_number;
        notify.current_hour = parseInt(_vi_ecommerce_notification_params.current_hour);
        if (!notify.detect || notify.detect == 0) {
            notify.cities = [notify.getCookie('wn_city')];
            notify.country = [notify.getCookie('wn_country')];
            var check_ip = notify.getCookie('wn_ip')
            if (check_ip && check_ip != 'undefined') {
                notify.init();
            }
        } else {
            notify.cities = _vi_ecommerce_notification_params.cities;
            notify.country = _vi_ecommerce_notification_params.country;
            notify.init();
        }
    });
    let woo_notification = {
        loop: 0,
        timeOutShow: 0,
        timeOutHide: 0,
        init_delay: 5,
        total: 30,
        display_time: 5,
        next_time: 60,
        count: 0,
        intel: 0,
        wn_popup: 0,
        id: 0,
        messages: '',
        products: '',
        ajax_url: '',
        display_effect: '',
        hidden_effect: '',
        time: '',
        names: '',
        cities: '',
        country: '',
        message_custom: '',
        message_number_min: '',
        message_number_max: '',
        detect: 0,
        show_close: 0,
        first_name_index: [],
        shortcodes: ['{first_name}', '{city}', '{state}', '{country}', '{product}', '{product_with_link}', '{time_ago}', '{custom}'],
        init: function () {
            if (this.loop == 1) {
                let now = Date.now();
                let displaying = this.getCookie('woo_notification_displaying');
                if (displaying) {
                    this.timeOutShow = setTimeout(function () {
                        woo_notification.init();
                    }, (displaying - now));
                    return false;
                }
            }

            if (this.ajax_url) {
                this.ajax_get_data();
            } else {
                setTimeout(function () {
                    woo_notification.get_product();
                }, this.init_delay * 1000);
            }
            jQuery('#message-purchased').on('mouseenter', function () {
                window.clearTimeout(woo_notification.wn_popup);
                window.clearTimeout(woo_notification.timeOutShow);
                window.clearTimeout(woo_notification.timeOutHide);
            }).on('mouseleave', function () {
                woo_notification.message_show(true)
            });
        },
        detect_address: function () {
            var ip_address = this.getCookie('wn_ip');
            if (!ip_address) {
                jQuery.getJSON('https://extreme-ip-lookup.com/json/', function (data) {
                    if (data.query) {
                        woo_notification.setCookie('wn_ip', data.query, 86400);
                    }
                    if (data.city) {
                        woo_notification.setCookie('wn_city', data.city, 86400);
                    }
                    if (data.country) {
                        woo_notification.setCookie('wn_country', data.country, 86400);
                    }
                });
            }
        },
        ajax_get_data: function () {
            if (this.ajax_url && !this.getCookie('woo_notification_close')) {
                var str_data;
                if (this.id) {
                    str_data = '&id=' + this.id;
                } else {
                    str_data = '';
                }
                jQuery.ajax({
                    type: 'POST',
                    data: 'action=woonotification_get_product' + str_data,
                    url: this.ajax_url,
                    success: function (data) {
                        var products = JSON.parse(data);
                        if (products && products != 'undefined' && products.length > 0) {
                            woo_notification.products = products;
                            // woo_notification.message_show();
                            setTimeout(function () {
                                woo_notification.get_product();
                            }, woo_notification.init_delay * 1000);
                            woo_notification.close_notify();
                        }
                    },
                    error: function (html) {
                    }
                })
            }
        },
        message_show: function (mouse_leave = false) {
            var message_id = jQuery('#message-purchased');
            if (!mouse_leave) {
                this.count++;
                this.audio();
            }
            if (this.loop == 1) {
                var now = Date.now();
                var displaying = this.getCookie('woo_notification_displaying');
                if (!mouse_leave) {
                    if (displaying) {
                        window.clearTimeout(this.timeOutShow);
                        this.timeOutShow = setTimeout(function () {
                            woo_notification.get_product();
                        }, (displaying - now));

                        return false;
                    }
                }
                window.clearTimeout(this.timeOutHide);
                this.timeOutHide = setTimeout(function () {
                    woo_notification.message_hide();
                }, (this.display_time * 1000));
                this.setCookieNew('woo_notification_displaying', (this.display_time + this.next_time));

                if (message_id.hasClass(this.hidden_effect)) {
                    message_id.removeClass(this.hidden_effect);
                }
                message_id.addClass(this.display_effect).css('display', 'flex');
            } else {
                this.wn_popup = setTimeout(function () {
                    woo_notification.message_hide();
                }, this.display_time * 1000);
                window.clearTimeout(this.intel);
                if (message_id.hasClass(this.hidden_effect)) {
                    message_id.removeClass(this.hidden_effect);
                }
                message_id.addClass(this.display_effect).css('display', 'flex');
            }
        },
        message_hide: function (close = false, swipe = '') {
            var message_id = jQuery('#message-purchased');
            if (message_id.hasClass(this.display_effect)) {
                message_id.removeClass(this.display_effect);
            }
            switch (swipe) {
                case 'left':
                    message_id.addClass('bounceOutLeft');
                    break;
                case 'right':
                    message_id.addClass('bounceOutRight');
                    break;
                case 'up':
                    message_id.addClass('bounceOutUp');
                    break;
                case 'down':
                    message_id.addClass('bounceOutDown');
                    break;
                default:
                    message_id.addClass(this.hidden_effect);
            }
            message_id.fadeOut(1000);
            if (close || this.getCookie('woo_notification_close')) {
                return false;
            }
            var count = this.count;
            if (this.loop == 1) {
                if (this.total > count) {
                    window.clearTimeout(this.wn_popup);
                    this.intel = setTimeout(function () {
                        woo_notification.get_product();
                    }, this.next_time * 1000);
                }

            } else {
                window.clearTimeout(this.wn_popup);
                window.clearTimeout(this.intel);
            }
        },
        get_product: function () {
            let products = this.products;
            let messages = this.messages;
            let data_first_name, data_state, data_country, data_city, time_str, index;
            if (products == 'undefined' || !products || !messages) {
                return false;
            }
            if (products.length > 0 && messages.length > 0) {
                /*Get message*/
                index = woo_notification.random(0, messages.length - 1);
                var string = messages[index];

                /*Get product*/
                index = woo_notification.random(0, products.length - 1);
                var product = products[index];

                /*Get name*/

                if (this.names && this.names != 'undefined') {
                    index = woo_notification.random(0, this.names.length - 1);
                    data_first_name = vi_wn_b64DecodeUnicode(this.names[index]);
                } else {
                    data_first_name = '';
                }
                if (this.cities && this.cities != 'undefined') {
                    if (Array.isArray(this.cities)) {
                        index = woo_notification.random(0, this.cities.length - 1);
                        data_city = vi_wn_b64DecodeUnicode(this.cities[index]);
                    } else {
                        data_city = this.cities;
                    }
                } else {
                    data_city = '';
                }

                data_state = '';
                data_country = this.country;

                time_str = this.get_time_string();
                let data_product = '<span class="wn-popup-product-title">' + product.title + '</span>';
                let data_product_link = '<a class="wn-popup-product-title-with-link"';
                data_product_link += ' href="' + product.url + '">' + product.title + '</a>';
                let data_time = '<small>' + _vi_ecommerce_notification_params.str_about + ' ' + time_str + ' ' + _vi_ecommerce_notification_params.str_ago + ' </small>';
                let data_custom = this.message_custom;
                let image_html = '';
                let replace_regex = '';
                if (product.thumb) {
                    jQuery('#message-purchased').addClass('wn-product-with-image').removeClass('wn-product-without-image');
                    image_html = ' <img class="wn-notification-image" src="' + product.thumb + '">';
                } else {
                    jQuery('#message-purchased').addClass('wn-product-without-image').removeClass('wn-product-with-image');
                }
                /*Replace custom message*/
                replace_regex = /{number}/g;

                data_custom = data_custom.replaceAll(replace_regex, this.get_data_custom_number(product.url));
                /*Replace message*/
                let replaceArray = this.shortcodes;
                let replaceArrayValue = [data_first_name, data_city, data_state, data_country, data_product, data_product_link, data_time, data_custom];
                let finalAns = string;
                for (let i = replaceArray.length - 1; i >= 0; i--) {
                    replace_regex = new RegExp(replaceArray[i], 'g');

                    finalAns = finalAns.replaceAll(replace_regex, replaceArrayValue[i]);
                }
                var close_html = '';
                // var html = image_html + '<p class="wn-notification-message-container">' + finalAns + '</p>' + close_html;
                var html = `<div class="wn-notification-wrapper">${image_html}<p class="wn-notification-message-container">${finalAns}</p></div>${close_html}`;
                jQuery('#message-purchased').html(html);
                if (parseInt(this.show_close) > 0) {
                    close_html = '<div id="notify-close"></div>'
                }
                jQuery('#message-purchased').html(html + close_html);
                woo_notification.message_show();
                woo_notification.close_notify();
            }
        },
        get_data_custom_number: function (product_url) {
            if (!this.change_message_number) {
                return this.random(this.message_number_min, this.message_number_max);
            }
            if (this.current_hour < 7) {
                return this.message_number_min;
            }
            let custom_numbers = woo_notification.getCookie('wn_data_custom_number'), number, number_min, number_max;
            custom_numbers = custom_numbers ? JSON.parse(custom_numbers) : {};
            if (custom_numbers[product_url]) {
                number_min = custom_numbers[product_url] - 3 > this.message_number_min ? custom_numbers[product_url] - 3 : this.message_number_min;
                number_max = custom_numbers[product_url] + 2 < this.message_number_max ? custom_numbers[product_url] + 2 : this.message_number_max;
            } else {
                number_min = this.message_number_min;
                number_max = this.message_number_max;
            }
            number = this.random(number_min, number_max);
            custom_numbers[product_url] = number;
            custom_numbers = JSON.stringify(custom_numbers);
            woo_notification.setCookie('wn_data_custom_number', custom_numbers, 120);
            return number;
        },
        get_time_cal: function () {
            if (this.change_virtual_time && this.start_virtual_time && this.end_virtual_time) {
                return this.random(this.start_virtual_time * 3600, this.end_virtual_time * 3600);
            } else {
                return this.random(0, this.time * 3600);
            }
        },
        get_time_string: function () {
            var time_cal = this.get_time_cal();
            // var time_cal = this.random(0, this.time * 3600);
            /*Check day*/
            var check_time = parseFloat(time_cal / 86400);
            if (check_time > 1) {
                check_time = parseInt(check_time);
                if (check_time == 1) {
                    return check_time + ' ' + _vi_ecommerce_notification_params.str_day
                } else {
                    return check_time + ' ' + _vi_ecommerce_notification_params.str_days
                }
            }
            check_time = parseFloat(time_cal / 3600);
            if (check_time > 1) {
                check_time = parseInt(check_time);
                if (check_time == 1) {
                    return check_time + ' ' + _vi_ecommerce_notification_params.str_hour
                } else {
                    return check_time + ' ' + _vi_ecommerce_notification_params.str_hours
                }
            }
            check_time = parseFloat(time_cal / 60);
            if (check_time > 1) {
                check_time = parseInt(check_time);
                if (check_time == 1) {
                    return check_time + ' ' + _vi_ecommerce_notification_params.str_min
                } else {
                    return check_time + ' ' + _vi_ecommerce_notification_params.str_mins
                }
            } else if (check_time < 10) {
                return _vi_ecommerce_notification_params.str_few_sec
            } else {
                check_time = parseInt(check_time);
                return check_time + ' ' + _vi_ecommerce_notification_params.str_secs
            }
        },
        close_notify: function () {
            jQuery('#notify-close').unbind().on('click', function () {
                woo_notification.message_hide();
            });
        },
        audio: function () {
            if (jQuery('#ecommerce-notification-audio').length > 0) {
                var audio = document.getElementById("ecommerce-notification-audio");
                var initSound = function () {
                    audio.play().then(function () {
                        setTimeout(function () {
                            audio.stop();
                        }, 0);
                    });
                    document.removeEventListener('touchstart', initSound, false);
                };
                document.addEventListener('touchstart', initSound, {capture: true});
                audio.play();
            }
        },
        random: function (min, max) {
            min = parseInt(min);
            max = parseInt(max);
            var rand_number = Math.random() * (max - min);
            return Math.round(rand_number) + min;
        },
        setCookie: function (cname, cvalue, expire) {
            var d = new Date();
            d.setTime(d.getTime() + (expire * 1000));
            var expires = "expires=" + d.toUTCString();
            document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
        },
        setCookieNew: function (cname, expire, cvalue = '', isDate = false) {
            var d = new Date();
            var d_timestamp = d.getTime() + (expire * 1000);
            if (isDate) {
                d_timestamp = expire;
            }
            d.setTime(d_timestamp);
            if (!cvalue) {
                cvalue = d_timestamp;
            }
            document.cookie = cname + "=" + cvalue + ";expires=" + d.toUTCString() + ";path=/";

        },

        getCookie: function (cname) {
            var name = cname + "=";
            var decodedCookie = decodeURIComponent(document.cookie);
            var ca = decodedCookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(name) == 0) {
                    return c.substring(name.length, c.length);
                }
            }
            return "";
        }
    };
}