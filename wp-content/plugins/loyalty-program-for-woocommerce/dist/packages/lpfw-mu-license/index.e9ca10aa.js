function m(){import.meta.url,import("_").catch(()=>1);async function*s(){}}const a=jQuery;jQuery(document).ready(function(s){s("button[type='submit']").text(slmw_args.i18n_activate_license).val(slmw_args.i18n_activate_license).click(d)});function d(s){var c,l,o,r;s.preventDefault();const e=a(this),n=e.closest(".license-info"),i=n.find(".active-indicator"),_=(l=(c=a("#lpfw_slmw_activation_email").val())==null?void 0:c.toString().trim())!=null?l:"",v=(r=(o=a("#lpfw_slmw_license_key").val())==null?void 0:o.toString().trim())!=null?r:"";n.find(".overlay").css("display","flex"),e.val(slmw_args.i18n_activating_license).attr("disabled","disabled"),a.ajax({url:ajaxurl,type:"POST",data:{action:"lpfw_activate_license","activation-email":_,"license-key":v,"ajax-nonce":slmw_args.nonce_activate_license},dataType:"json"}).done(function(t){t.status==="success"?(a(".tap-activate-license-notice").length>0&&a(".tap-activate-license-notice").closest("div.error").remove(),i.addClass("license-active dashicons-before dashicons-yes-alt"),i.text(slmw_args.i18n_license_activated),vex.dialog.alert(t.message),e.addClass("grayed")):(i.removeClass("license-active dashicons-before dashicons-yes-alt"),i.text(slmw_args.i18n_license_not_active),vex.dialog.alert(t.message),e.removeClass("grayed"))}).fail(function(t){i.text(slmw_args.i18n_license_not_active),vex.dialog.alert(slmw_args.i18n_failed_to_activate_license),e.removeClass("grayed"),console.log(t)}).always(function(){e.val(slmw_args.i18n_activate_license).removeAttr("disabled"),n.find(".overlay").css("display","none")})}export{m as __vite_legacy_guard};
