(function($) {
	woodmartThemeModule.curr_product_cat = function() {
		var curr_product_cat=woodmartThemeModule.getParameterByName('product_cat');
		$('.children_cat_panel ul li').each(function(){
			 	var product_cat=$(this).data('product_cat');
				if ( typeof product_cat !=='undefined' && (product_cat == curr_product_cat)){
						$(this).addClass('active');
				}
				$(this).attr('data-href',$(this).children('a').attr('href'));
				$(this).children('a').attr('href','javascript:void(0);');
		});
		$('body').on('click','.children_cat_panel ul li',function () {
			console.log($(this).children('a').length);
			if($(this).children('a').length>0){
				if($(this).hasClass('active')){
					window.history.go(-1);
				}else{
					window.location.href=$(this).data('href');
				}
			}
		})
	};
	woodmartThemeModule.getParameterByName = function(name) {
		var url = window.location.href;
		name = name.replace(/[\[\]]/g, '\\$&');
		var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)');
		var results = regex.exec(url);
		if (!results) return null;
		if (!results[2]) return '';
		return decodeURIComponent(results[2].replace(/\+/g, ' '));
	}

	$(document).ready(function() {
		woodmartThemeModule.$document.on('wdShopPageInit', function() {
			woodmartThemeModule.curr_product_cat();
		});
		woodmartThemeModule.curr_product_cat();
	});
})(jQuery);
