(function($) {
    woodmartThemeModule.product_delivery_time = function() {
        $('.FilterSelect').val($('.popup_delivery_time').data('country-code'));
        $(document).on('change', '.FilterSelect', function() {
            var country_code = $(this).val();
            woodmartThemeModule.get_delivery_time(country_code);
        });
    };
    woodmartThemeModule.get_delivery_time = function(country_code) {
        var language=$('.popup_delivery_time').data('lang');
        $.ajax({
            url     : woodmart_settings.ajaxurl,
            data    : {
                action    : 'woodmart_get_delivery_time',
                language: language,
                country_code:country_code

            },
            method  : 'POST',
            success : function(response) {
                if (response && response.status==200) {
                    $('.popup_delivery_time').attr('data-country-code',response.data.country_code);
                    $('.country_name').html(response.data.country);
                    $('.delivery_time').html(response.data.date_min+'-'+response.data.date_max);
                    $('.delivery_time_costs').html(response.data.costs);
                    $('.s_e_delivery_time').html(response.data.start_delivery_time+' - '+response.data.end_delivery_time);
                }
            },
            error   : function() {
                console.log('ajax error');
            },
            complete: function() {}
        });
    }
    $(document).ready(function() {
        woodmartThemeModule.product_delivery_time();
    });
})(jQuery);