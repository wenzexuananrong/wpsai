System.register([],(function(e,t){"use strict";return{execute:function(){var e=document.createElement("style");e.textContent='.loyalty-program-product-fields h3{margin:10px 0 0 10px;font-size:1.2em}p.form-field.lpfw-toggled-field{position:relative}p.form-field.lpfw-toggled-field:after{content:"";position:absolute;top:0;left:0;z-index:10;display:none;width:100%;height:110%;background:#fff;opacity:.7;visibility:visible}p.form-field.lpfw-toggled-field.block:after{display:block}\n',document.head.appendChild(e),jQuery(document).ready((function(e){var t={toggleProductFields:function(){var t=e(this),o=t.closest(".form-field").siblings(".lpfw-toggled-field");t.prop("checked")?(o.removeClass("block"),o.find("input").prop("disabled",!1)):(o.addClass("block"),o.find("input").prop("disabled",!0))},init:function(){e("#woocommerce-product-data").on("change lpfw_load","#lpfw_allow_earn_points",t.toggleProductFields),e("#lpfw_allow_earn_points").trigger("lpfw_load")}};t.init()}))}}}));
