function f(){import.meta.url,import("_").catch(()=>1);async function*e(){}}jQuery(document).ready(function(e){var n=e("#agcfw-redeem-gift-card"),c={initPopover:function(){e(".agcfw-tooltip").each(function(){e(this).webuiPopover({title:e(this).data("title"),content:e(this).data("content"),width:250,closable:!0,animation:"pop",padding:!1,placement:"bottom-right"})})},initBlockMaxHeight:function(){var o=e("#agcfw-redeem-gift-card");if(o.hasClass("agcfw-toggle-redeem-form")){var t=o.find(".agcfw-inner-content").height()+2*parseInt(getComputedStyle(o.find(".agcfw-inner-content")[0]).fontSize);o.find(".agcfw-inner").css({maxHeight:t})}},submitRedeemForm:function(o){var t=e(this).closest(".agcfw-redeem-gift-card-form"),r=e(this).siblings("input.gift_card_code"),i=t.data("is_checkout");t.find("input,button").prop("disabled",!0),i&&c.blockCheckout(),e.post(woocommerce_params.ajax_url,{action:"agcfw_redeem_gift_card",gift_card_code:r.val(),_wpnonce:t.data("nonce")},function(a){i?(c.unblockCheckout(),e(document.body).trigger("update_checkout",{update_shipping_method:!1})):window.location.reload()},"json").always(function(){r.val(""),t.find("input,button").prop("disabled",!1)})},toggleFormButton:function(){var o=e(this),t=o.siblings(".button");t.prop("disabled",!o.val())},blockCheckout:function(){e(".woocommerce form.woocommerce-checkout").addClass("processing").block({message:null,overlayCSS:{background:"#fff",opacity:.6}})},unblockCheckout:function(){e(".woocommerce-error, .woocommerce-message").remove(),e(".woocommerce form.woocommerce-checkout").removeClass("processing").unblock()},toggleShowCheckoutRedeemForm:function(){n.toggleClass("show")},waitForVisibleElement:function(o){return new Promise(t=>{const r=setInterval(()=>{const i=document.querySelector(o);i&&i.offsetWidth>0&&i.offsetHeight>0&&(clearInterval(r),t(i))},100)})}};n.on("keyup",".agcfw-redeem-gift-card-form input.gift_card_code",c.toggleFormButton),n.on("click",".agcfw-redeem-gift-card-form button",c.submitRedeemForm),n.on("click","h3",c.toggleShowCheckoutRedeemForm),c.initPopover(),jQuery("#owp-checkout-timeline")?c.waitForVisibleElement("#agcfw-redeem-gift-card").then(function(){c.initBlockMaxHeight()}):c.initBlockMaxHeight()});export{f as __vite_legacy_guard};
