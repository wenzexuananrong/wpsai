System.register([],(function(e,a){"use strict";return{execute:function(){var e,a=document.createElement("style");a.textContent=".woocommerce_options_panel .advanced-gift-card-fields>h3{margin:10px 10px 5px}.woocommerce_options_panel .advanced-gift-card-fields>p.gift-card-description{margin:0;padding:5px 10px}.woocommerce_options_panel .advanced-gift-card-fields .agcfw-built-in-design-options{margin-bottom:2em}.woocommerce_options_panel .advanced-gift-card-fields .agcfw-built-in-design-options label{float:none;display:inline-block;width:32%;margin:0 1% 0 0}.woocommerce_options_panel .advanced-gift-card-fields .agcfw-built-in-design-options label.selected img{border:2px solid #2460BA;box-shadow:0 0 4px 4px rgba(36,96,186,.25);border-radius:3px}.woocommerce_options_panel .advanced-gift-card-fields .agcfw-built-in-design-options input{display:none}.woocommerce_options_panel .advanced-gift-card-fields .agcfw-built-in-design-options img{width:100%;height:auto}.woocommerce_options_panel .advanced-gift-card-fields .agcfw-custom-bg-option .empty-placeholder,.woocommerce_options_panel .advanced-gift-card-fields .agcfw-custom-bg-option .image-placeholder{display:none}.woocommerce_options_panel .advanced-gift-card-fields .agcfw-custom-bg-option>p{padding:0;margin:0 0 5px}.woocommerce_options_panel .advanced-gift-card-fields .agcfw-custom-bg-option .empty-placeholder p{padding:0;font-style:italic;color:#999}.woocommerce_options_panel .advanced-gift-card-fields .agcfw-custom-bg-option .active{display:block}.woocommerce_options_panel .advanced-gift-card-fields .agcfw-custom-bg-option .image-placeholder{max-width:300px}.woocommerce_options_panel .advanced-gift-card-fields .agcfw-custom-bg-option .image-placeholder img{width:100%;height:auto;cursor:pointer}.woocommerce_options_panel .advanced-gift-card-fields .agcfw-custom-bg-option a.remove-custom-bg{color:#b32d2e}.woocommerce_options_panel .advanced-gift-card-fields span.agcfw-custom-expiry-wrapper{display:none;margin-left:5px;width:110px;height:28px;border:1px solid #8c8f94;border-radius:3px;background:#fafafa}.woocommerce_options_panel .advanced-gift-card-fields span.agcfw-custom-expiry-wrapper.show{display:inline-block}.woocommerce_options_panel .advanced-gift-card-fields span.agcfw-custom-expiry-wrapper input[type=number].short{margin:0;width:calc(100% - 35px);min-height:28px!important;border:0;outline:0!important;box-shadow:none!important}.woocommerce_options_panel .advanced-gift-card-fields span.agcfw-custom-expiry-wrapper .field-suffix{display:inline-block;padding-left:5px;width:25px;border-left:1px solid #e1e1e1;height:28px}.vex.agcfw-email-preview-vex{padding-top:10vh;z-index:11111}.vex.agcfw-email-preview-vex .vex-content{width:70vw;min-width:700px;height:80vh;padding:0}.vex.agcfw-email-preview-vex .vex-content iframe{width:100%;height:100%}\n",document.head.appendChild(a),vex.defaultOptions.className="vex-theme-plain",(e=jQuery)(".options_group.pricing").addClass("show_if_advanced_gift_card"),e("._tax_status_field").closest(".options_group").addClass("show_if_advanced_gift_card"),e("label[for='_virtual']").addClass("show_if_advanced_gift_card"),jQuery(document).ready((function(e){var a={customBackgroundFrame:null,toggleVirtualDisabled:function(){const a=e(this),o=e("input#_virtual");"advanced_gift_card"===a.val()?o.prop("checked",!0).prop("disabled",!0):o.prop("disabled",!1)},selectGiftCardDesign:function(){var a=e(this),o=a.closest(".agcfw-built-in-design-options"),i=a.closest("label");o.find("label").removeClass("selected"),i.addClass("selected")},initcustomBackgroundFrame:function(){e(this),a.customBackgroundFrame||(a.customBackgroundFrame=wp.media.frames.agcfw_custom_bg_frame=wp.media({title:"Choose Image",button:{text:"Select Image"},states:[new wp.media.controller.Library({title:"Choose Image",filterable:"all",multiple:!1})]}),a.customBackgroundFrame.on("select",(function(){var o=e(".agcfw-custom-bg-option input"),i=e(".agcfw-custom-bg-option .image-wrapper"),t=a.customBackgroundFrame.state().get("selection").single().toJSON(),c=t.sizes&&t.sizes.medium?t.sizes.medium.url:t.url;i.html("<img src='"+c+"' />"),o.val(t.id),e(".agcfw-custom-bg-option .empty-placeholder").hide(),e(".agcfw-custom-bg-option .image-placeholder").show(),e(".agcfw-built-in-design-options").hide(),e(".agcfw-custom-bg-option > p").hide()}))),a.customBackgroundFrame.open()},removeCustomBackgroundImage:function(){var a=e(this).closest(".agcfw-custom-bg-option");a.find(".image-placeholder").hide(),a.find(".empty-placeholder").show(),a.find(".image-placehoder .image-wrapper").html(""),a.find("input").val(""),e(".agcfw-built-in-design-options").show(),e(".agcfw-custom-bg-option > p").show()},displayEmailPreview:function(){vex.dialog.open({content:" ",className:"vex-theme-plain agcfw-email-preview-vex",afterOpen:function(){var a=new URLSearchParams;a.append("action","agcfw_gift_card_preview_email"),a.append("value",e("input[name='agcfw[value]']").val()),a.append("design",e("input[name='agcfw[design]']:checked").val()),a.append("custom_bg",e("input[name='agcfw[custom_bg]']").val()),a.append("_wpnonce",e(".agcfw_preview_email_field").data("nonce"));var o=ajaxurl+"?"+a.toString();e(".agcfw-email-preview-vex .vex-content").html("<iframe src='"+o+"' width='100%' height='100%'></iframe>")}})},toggleCustomExpiryField:function(){var a=e(this),o=a.siblings(".agcfw-custom-expiry-wrapper");"custom"===a.val()?(o.addClass("show"),o.find("input").prop("readonly",!1)):(o.removeClass("show"),o.find("input").prop("readonly",!0))},toggleAllowDeliveryDateField:function(){var a=e(this),o=e(this).parent(".form-field").siblings(".agcfw_allow_delivery_date_field");a.is(":checked")?(o.removeClass("disabled"),o.find("input").prop("disabled",!1)):(o.addClass("disabled"),o.find("input").prop("disabled",!0))}};e("#woocommerce-product-data").on("change agcfw_toggle_virtual","select#product-type",a.toggleVirtualDisabled),e("select#product-type").trigger("agcfw_toggle_virtual"),e("#woocommerce-product-data").on("change",".agcfw-built-in-design-options input",a.selectGiftCardDesign),e(".agcfw-built-in-design-options input:checked").trigger("change"),e(".agcfw-custom-bg-option").on("click","button,.image-wrapper img",a.initcustomBackgroundFrame),e(".agcfw-custom-bg-option").on("click","a.remove-custom-bg",a.removeCustomBackgroundImage),e("#woocommerce-product-data").on("click",".agcfw_preview_email_field button",a.displayEmailPreview),e("#woocommerce-product-data").on("change agc_load",".agcfw_gift_card_expiry select",a.toggleCustomExpiryField),e("#woocommerce-product-data").on("change agc_load",".agcfw_is_giftable_field input",a.toggleAllowDeliveryDateField),e(".agcfw_gift_card_expiry select").trigger("agc_load"),e(".agcfw_is_giftable_field input").trigger("agc_load")}))}}}));
