function m(){import.meta.url,import("_").catch(()=>1);async function*n(){}}const e=jQuery;jQuery(document).on("ready",function(){e('.acfwp-license-settings-block button[type="submit"]').on("click",d)});function d(n){var c,l,o,r;n.preventDefault();const i=e(this),a=i.closest(".license-info"),t=a.find(".active-indicator"),_=(l=(c=e("#acfw_slmw_activation_email").val())==null?void 0:c.toString().trim())!=null?l:"",v=(r=(o=e("#acfw_slmw_license_key").val())==null?void 0:o.toString().trim())!=null?r:"";a.find(".overlay").css("display","flex"),i.val(slmw_args.i18n_activating_license).attr("disabled","disabled"),e.post(ajaxurl,{action:"acfw_activate_license","activation-email":_,"license-key":v,"ajax-nonce":slmw_args.nonce_activate_license},"json").done(function(s){s.status==="success"?(e(".tap-activate-license-notice").length>0&&e(".tap-activate-license-notice").closest("div.error").remove(),t.addClass("license-active dashicons-before dashicons-yes-alt"),t.text(slmw_args.i18n_license_activated),vex.dialog.alert(s.message)):(t.removeClass("license-active dashicons-before dashicons-yes-alt"),t.text(slmw_args.i18n_license_not_active),vex.dialog.alert(s.message),i.removeClass("grayed"))}).fail(function(){t.text(slmw_args.i18n_license_not_active),vex.dialog.alert(slmw_args.i18n_failed_to_activate_license),i.removeClass("grayed")}).always(function(){i.val(slmw_args.i18n_activate_license).removeAttr("disabled"),a.find(".overlay").css("display","none")})}export{m as __vite_legacy_guard};
