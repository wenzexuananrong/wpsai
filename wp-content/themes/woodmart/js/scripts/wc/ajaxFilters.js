/* global woodmart_settings */
(function($) {
	woodmartThemeModule.ajaxFilters = function() {
		if (!woodmartThemeModule.$body.hasClass('woodmart-ajax-shop-on') || typeof ($.fn.pjax) === 'undefined' || woodmartThemeModule.$body.hasClass('single-product') || woodmartThemeModule.$body.hasClass('elementor-editor-active') || $('.products[data-source="main_loop"]').length === 0) {
			return;
		}

		var that         = this,
		    filtersState = false;

		woodmartThemeModule.$body.on('click', '.post-type-archive-product .products-footer .woocommerce-pagination a', function() {
			scrollToTop(true);
		});

		woodmartThemeModule.$document.pjax(woodmart_settings.ajax_links, '.main-page-wrapper', {
			timeout       : woodmart_settings.pjax_timeout,
			scrollTo      : false,
			renderCallback: function(context, html, afterRender) {
				woodmartThemeModule.removeDuplicatedStylesFromHTML(html, function(html) {
					context.html(html);
					afterRender();
					woodmartThemeModule.$document.trigger('wdShopPageInit');
					woodmartThemeModule.$document.trigger('wood-images-loaded');
				});
			}
		});

		if (woodmart_settings.price_filter_action === 'click') {
			woodmartThemeModule.$document.on('click', '.widget_price_filter form .button', function() {
				var form = $('.widget_price_filter form');
				$.pjax({
					container: '.main-page-wrapper',
					timeout  : woodmart_settings.pjax_timeout,
					url      : form.attr('action'),
					data     : form.serialize(),
					scrollTo : false,
					renderCallback: function(context, html, afterRender) {
						woodmartThemeModule.removeDuplicatedStylesFromHTML(html, function(html) {
							context.html(html);
							afterRender();
							woodmartThemeModule.$document.trigger('wdShopPageInit');
							woodmartThemeModule.$document.trigger('wood-images-loaded');
						});
					}
				});

				return false;
			});
		} else if (woodmart_settings.price_filter_action === 'submit') {
			woodmartThemeModule.$document.on('submit', '.widget_price_filter form', function(event) {
				$.pjax.submit(event, '.main-page-wrapper');
			});
		}

		woodmartThemeModule.$document.on('pjax:error', function(xhr, textStatus, error) {
			console.log('pjax error ' + error);
		});

		woodmartThemeModule.$document.on('pjax:start', function() {
			$('html, body').animate({scrollTop:0}, 400);

			scrollToTop(true);
			var $siteContent = $('.site-content');

			$siteContent.removeClass('ajax-loaded');
			$siteContent.addClass('ajax-loading');

			woodmartThemeModule.$document.trigger('wdPjaxStart');
			woodmartThemeModule.$window.trigger('scroll.loaderVerticalPosition');
		});

		woodmartThemeModule.$document.on('pjax:complete', function() {
			woodmartThemeModule.$window.off('scroll.loaderVerticalPosition');

			woodmartThemeModule.$document.trigger('wood-images-loaded');

			$('.wd-scroll-content').on('scroll', function() {
				woodmartThemeModule.$document.trigger('wood-images-loaded');
			});

			if (typeof woodmart_wpml_js_data !== 'undefined' && woodmart_wpml_js_data.languages) {
				$.each(woodmart_wpml_js_data.languages, function(index, language) {
					$('.wpml-ls-item-' + language.code + ' .wpml-ls-link').attr('href', language.url);
				});
			}
		});

		woodmartThemeModule.$document.on('pjax:beforeReplace', function() {
			if ($('.filters-area').hasClass('filters-opened') && woodmart_settings.shop_filters_close === 'yes') {
				filtersState = true;
				woodmartThemeModule.$body.addClass('body-filters-opened');
			}
		});

		woodmartThemeModule.$document.on('wdShopPageInit', function() {
			var $siteContent = $('.site-content');

			if (filtersState) {
				$('.filters-area').css('display', 'block');
				woodmartThemeModule.openFilters(200);
				filtersState = false;
			}

			$siteContent.removeClass('ajax-loading');
			$siteContent.addClass('ajax-loaded');
		});

		var scrollToTop = function(type) {
			if (woodmart_settings.ajax_scroll === 'no' && type === false) {
				return false;
			}

			var $scrollTo = $(woodmart_settings.ajax_scroll_class),
			    scrollTo  = $scrollTo.offset().top - woodmart_settings.ajax_scroll_offset;

			$('html, body').stop().animate({
				scrollTop: scrollTo
			}, 400);
		};
	};

	$(document).ready(function() {
		woodmartThemeModule.ajaxFilters();
	});

	window.addEventListener('popstate', function() {
		woodmartThemeModule.ajaxFilters();
	});
})(jQuery);
