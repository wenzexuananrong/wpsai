function f(){import.meta.url,import("_").catch(()=>1);async function*n(){}}function o(){jQuery(".lpfw-drm-notice").on("click",".notice-dismiss",s),jQuery(document).on("lpfw:drm:wpadmin",i),jQuery(document).on("lpfw:drm:adminapp:license",l),jQuery(document).on("lpfw:drm:adminapp",i)}function s(n){n.preventDefault();const e=jQuery(this).closest(".lpfw-drm-notice");e.fadeOut("fast"),jQuery.post(ajaxurl,{action:"lpfw_dismiss_license_notice",nonce:lpfwLicense.dismiss_notice_nonce,notice:e.data("id")})}function i(){jQuery(".notice.lpfw-drm-notice").css({display:"block"}),jQuery(".lpfw-drm-notice .activate-license-form").length&&jQuery(".lpfw-drm-notice .activate-license-form").addClass("lpfw-license-form")}function l(){jQuery(".notice.lpfw-drm-notice").css({display:"none"}),jQuery(".lpfw-drm-notice .activate-license-form").length&&jQuery(".lpfw-drm-notice .activate-license-form").removeClass("lpfw-license-form")}function r(){jQuery("body").on("click","#lpfw-license-interstitial .refresh-license-status",a),jQuery(document).on("lpfw:drm:wpadmin",c),jQuery(document).on("lpfw:drm:adminapp:notloyalty",c),jQuery(document).on("lpfw:drm:adminapp:loyalty",u)}function a(n){n.preventDefault();const t=jQuery(this);t.hasClass("is-loading")||(t.addClass("is-loading"),jQuery.post(ajaxurl,{action:"lpfw_refresh_license_status",nonce:lpfwLicense.refresh_license_nonce},e=>{if(e!=null&&e.license_status&&e.license_status==="active"){window.location.reload();return}alert(e.message),t.removeClass("is-loading")}))}function u(){jQuery("#lpfw-license-interstitial").css({display:"flex"})}function c(){jQuery("#lpfw-license-interstitial").css({display:"none"})}jQuery(document).on("ready",function(){o(),r(),d()});function d(){if(!window.hasOwnProperty("acfwpElements")||!acfwpElements.appStore){jQuery(document).trigger("lpfw:drm:wpadmin");return}const{store:n}=acfwpElements.appStore;n.subscribe(()=>{const t=n.getState().page;t.includes("license")?jQuery(document).trigger("lpfw:drm:adminapp:license"):jQuery(document).trigger("lpfw:drm:adminapp"),t.includes("loyalty")?jQuery(document).trigger("lpfw:drm:adminapp:loyalty"):jQuery(document).trigger("lpfw:drm:adminapp:notloyalty")})}export{f as __vite_legacy_guard};
