System.register([],(function(e,i){"use strict";return{execute:function(){var e=document.createElement("style");e.textContent='#license-placeholder{max-width:860px;margin-top:20px}#license-placeholder *{font-family:"Lato","Lato",Helvetica,Arial,Sans-serif}#license-placeholder .action-button{display:inline-block;background:#ccc;padding:12px 30px;font-size:16px;border:0;font-weight:700;text-decoration:none;color:inherit}#license-placeholder .action-button:hover,#license-placeholder .action-button:focus{opacity:.8}#license-placeholder .no-hover{opacity:1!important}#license-placeholder .license-active{background-color:#c6cd2e;padding:12px 17px 12px 10px}#license-placeholder .license-active:before{margin-right:5px;font-size:1.1em}#license-placeholder .overview{margin-bottom:20px}#license-placeholder .overview h2{padding:0;margin:0;font-size:18px;line-height:40px}#license-placeholder .overview p{margin:0 0 15px}#license-placeholder .overview .feature-comparison{background:#035E6B;color:#fff}#license-placeholder .license-info{position:relative;border:1px solid #D5D5D5}#license-placeholder .license-info .overlay{display:none;position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,.6);align-items:center;justify-content:center;text-align:center}#license-placeholder .license-info .heading{padding:10px 10px 10px 30px;background:#f5f5f5;border-bottom:1px solid #D5D5D5;display:flex;align-items:center;justify-content:space-between}#license-placeholder .license-info .heading:after{content:"";display:table;clear:both}#license-placeholder .license-info .heading .left{width:calc(100% - 192px)}#license-placeholder .license-info .heading .upgrade-premium{background:#C6CD2E;color:#000}#license-placeholder .license-info .content{background:#ffffff;padding:35px 30px}#license-placeholder .license-info .content h2{margin:0 0 10px}#license-placeholder .license-info .content table{margin-top:25px;max-width:230px}#license-placeholder .license-info .content table *{padding:0;text-align:left}#license-placeholder .license-info .content table tr th,#license-placeholder .license-info .content table tr td{padding-right:10px}#license-placeholder .license-info .form{padding:20px 30px;border-top:1px solid #d5d5d5}#license-placeholder .license-info .form .flex{display:flex;align-items:end;justify-content:space-between}#license-placeholder .license-info .form .form-field.action{width:100%;max-width:198px}#license-placeholder .license-info .form .form-field{width:calc(50% - 99px)}#license-placeholder .license-info .form .form-field label{display:inline-block;font-weight:700;margin-bottom:5px}#license-placeholder .license-info .form .form-field input{padding:5px 10px;width:calc(100% - 15px);border:1px solid #D9D9D9;box-sizing:border-box;border-radius:2px;font-size:16px}#license-placeholder .license-info .form .form-field.action button{width:100%;margin-top:24px;background:#C6CD2E;cursor:pointer}#license-placeholder .license-info .form .form-field.action button.grayed{background:#ccc}#license-placeholder .license-info .form .help-row{padding-top:10px}#license-placeholder .license-info .form .help-row a{color:inherit}#license-placeholder .license-info .form .help-row a:hover{color:#00a0d2}@media (max-width: 680px){#license-placeholder .license-info .form{display:block}#license-placeholder .license-info .form .form-field{width:100%;margin-bottom:10px}}\n',document.head.appendChild(e),jQuery(document).ready((function(e){e("button[type='submit']").text(slmw_args.i18n_activate_license).val(slmw_args.i18n_activate_license).click((function(i){i.preventDefault();var n=e(this),l=n.closest(".license-info"),o=l.find(".active-indicator"),c=e.trim(e("#agcfw_slmw_activation_email").val()),a=e.trim(e("#agcfw_slmw_license_key").val());l.find(".overlay").css("display","flex"),n.val(slmw_args.i18n_activating_license).attr("disabled","disabled"),e.ajax({url:ajaxurl,type:"POST",data:{action:"agcfw_activate_license","activation-email":c,"license-key":a,"ajax-nonce":slmw_args.nonce_activate_license},dataType:"json"}).done((function(i){"success"===i.status?(e(".tap-activate-license-notice").length>0&&e(".tap-activate-license-notice").closest("div.error").remove(),o.addClass("license-active dashicons-before dashicons-yes-alt"),o.text(slmw_args.i18n_license_activated),vex.dialog.alert(i.success_msg),n.addClass("grayed")):(o.removeClass("license-active dashicons-before dashicons-yes-alt"),o.text(slmw_args.i18n_license_not_active),vex.dialog.alert(i.error_msg),n.removeClass("grayed"))})).fail((function(e){o.text(slmw_args.i18n_license_not_active),vex.dialog.alert(slmw_args.i18n_failed_to_activate_license),n.removeClass("grayed")})).always((function(){n.val(slmw_args.i18n_activate_license).removeAttr("disabled"),l.find(".overlay").css("display","none")}))}))}))}}}));
