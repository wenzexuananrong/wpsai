function l(){import.meta.url,import("_").catch(()=>1);async function*n(){}}function a(){jQuery(".agc-drm-notice").on("click",".notice-dismiss",s),jQuery(document).on("agc:drm:wpadmin",i),jQuery(document).on("agc:drm:adminapp:license",r),jQuery(document).on("agc:drm:adminapp",i)}function s(n){n.preventDefault();const e=jQuery(this).closest(".agc-drm-notice");e.fadeOut("fast"),jQuery.post(ajaxurl,{action:"agcfw_dismiss_license_notice",nonce:agcLicense.dismiss_notice_nonce,notice:e.data("id")})}function i(){jQuery(".notice.agc-drm-notice").css({display:"block"}),jQuery(".agc-drm-notice .activate-license-form").length&&jQuery(".agc-drm-notice .activate-license-form").addClass("agc-license-form")}function r(){jQuery(".notice.agc-drm-notice").css({display:"none"}),jQuery(".agc-drm-notice .activate-license-form").length&&jQuery(".agc-drm-notice .activate-license-form").removeClass("agc-license-form")}function o(){jQuery("body").on("click","#agc-license-interstitial .refresh-license-status",d),jQuery(document).on("agc:drm:wpadmin",c),jQuery(document).on("agc:drm:adminapp:notgiftcards",c),jQuery(document).on("agc:drm:adminapp:giftcards",u)}function d(n){n.preventDefault();const t=jQuery(this);t.hasClass("is-loading")||(t.addClass("is-loading"),jQuery.post(ajaxurl,{action:"lpfw_refresh_license_status",nonce:agcLicense.refresh_license_nonce},e=>{if(e!=null&&e.license_status&&e.license_status==="active"){window.location.reload();return}alert(e.message),t.removeClass("is-loading")}))}function u(){jQuery("#agc-license-interstitial").css({display:"flex"})}function c(){jQuery("#agc-license-interstitial").css({display:"none"})}jQuery(document).on("ready",function(){a(),o(),m()});function m(){if(!window.hasOwnProperty("acfwpElements")||!acfwpElements.appStore){jQuery(document).trigger("agc:drm:wpadmin");return}const{store:n}=acfwpElements.appStore;n.subscribe(()=>{const t=n.getState().page;t.includes("license")?jQuery(document).trigger("agc:drm:adminapp:license"):jQuery(document).trigger("agc:drm:adminapp"),t.includes("gift-cards")?jQuery(document).trigger("agc:drm:adminapp:giftcards"):jQuery(document).trigger("agc:drm:adminapp:notgiftcards")})}export{l as __vite_legacy_guard};
