document.addEventListener("DOMContentLoaded",(function(){if("undefined"==typeof checkoutList)return;const e=jQuery("form.checkout");let r=[];function o(){return r=jQuery("#"+PayoneerData.paymentFieldsContainerId),r.length>0}function n(){return"1"===PayoneerData.isPayForOrder}function t(){const e=jQuery("#"+PayoneerData.paymentFieldsContainerId+" .op-payment-widget-container");return!!e.length&&0===jQuery(".GLOBAL_ERROR",e).length}function a(){return"payment_method_payoneer-checkout"===jQuery('.woocommerce-checkout input[name="payment_method"]:checked').attr("id")}function i(){a()?(jQuery("#place_order").prop("disabled",!0).hide(),jQuery("#payoneer_place_order").prop("disabled",!1).show()):(jQuery("#payoneer_place_order").prop("disabled",!0).hide(),jQuery("#place_order").prop("disabled",!1).show())}const c=function(e,r=300){let o;return(...n)=>{clearTimeout(o),o=setTimeout((()=>{e.apply(this,n)}),r)}}((function(){const o=jQuery("#"+PayoneerData.listUrlContainerId).val(),t="0"===PayoneerData.isPayForOrder?"form.checkout":"#order_review",a={listUrl:o,fullPageLoading:!1,payButton:PayoneerData.payButtonId,widgetCssUrl:PayoneerData.widgetCssUrl,cssUrl:PayoneerData.cssUrl,onBeforeCharge:async()=>{const r=new Promise((r=>{let o=jQuery("input[name="+PayoneerData.hostedModeOverrideFlag+"]");var n,a;o.prop("disabled",!0),PayoneerData.isPayForOrder?(n=()=>r(!0),a=()=>r(!1),jQuery.ajax({type:"POST",url:wc_checkout_params.ajax_url,xhrFields:{withCredentials:!0},dataType:"json",data:{action:"payoneer_order_pay",fields:jQuery("#order_review").serialize(),params:new URL(document.location).searchParams.toString()},success:function(e){n()},error:function(e,r,o){a(),window.location.reload()}})):(function(r,o,n){let t=!1;e.one("checkout_place_order_success",(function(e,o){if(!t)return t=!0,r(!0),!1})),jQuery(document.body).one("checkout_error",(function(e,o){t||(t=!0,n&&n(),r(!1))})),window.setTimeout((function(){t||(t=!0,n&&n(),r(!1))}),2e4)}(r,0,(function(){o.prop("disabled",!1)})),jQuery(t).submit())}));return await r},onBeforeServerError:async e=>{const r=new Promise((r=>{if(n()){const e=new URL(document.location);e.searchParams.set(PayoneerData.payOrderErrorFlag,!0),window.location.href=e.toString(),r(!1)}jQuery("input[name="+PayoneerData.onErrorRefreshFragmentFlag+"]").prop("value",!0),console.log("onBeforeServerError",e),jQuery("#"+PayoneerData.paymentFieldsContainerId).empty(),jQuery(document.body).trigger("update_checkout"),r(!1)}));return await r}};o&&""!==o&&(destroyWidget(),console.log("Initializing Payoneer payment widget",a),r.empty().checkoutList(a))}));jQuery(document.body).on("payment_method_selected",(function(){a()&&o()&&!t()&&c(),i()})),jQuery(document.body).on("updated_checkout",(function(){o()&&!t()&&a()&&c(),i()})),jQuery("#payoneer_place_order").on("click",(function(e){e.preventDefault()})),n()&&a()&&o()&&!t()&&c(),jQuery("#payoneer_place_order").on("click",(function(e){e.preventDefault()})),i()}),!1);
//# sourceMappingURL=payoneer-checkout.js.map