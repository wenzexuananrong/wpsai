import{s as w,L as g}from"../../common/loyalty-point-notice.f2314cf9.js";function N(){import.meta.url,import("_").catch(()=>1);async function*t(){}}const b="lpfw-wc-checkout-block",h={integration:b},{wcSettings:E}=wc,R=t=>E.getSetting("".concat(t,"_data")),k=()=>{var c;const{useState:t}=wp.element,{lpfw_block:e}=w.getCartData().extensions,s=(c=e==null?void 0:e.loyalty_points)!=null&&c.labels?e.loyalty_points.labels:{balance_text:"",instructions:"",placeholder:"",toggle_text:""},{caret_img_src:i,loyalty_points:m}=R(h.integration),{button_text:u,redeem_nonce:p}=m,[a,n]=t(""),[f,o]=t(!1);return{caret_img_src:i,button_text:u,...s,dispatchLolyatyPoint:()=>{const{adminAjaxUrl:r}=acfwfObj,{dummyUpdateCart:_}=acfwfObj.wc,{dispatch:d}=wp.data;o(!0),jQuery.post(r,{action:"lpfw_redeem_points_for_user",redeem_points:a,wpnonce:p,is_checkout:1,is_cart_checkout_block:!0},function(y){_(),n(""),d("core/notices").createNotice(y.status,y.message,{type:"snackbar",context:"wc/checkout"}),setTimeout(()=>{o(!1)},200)})},amount:a,setAmount:n,ButtonDisabled:f,setButtonDisabled:o,lpfw_block:e}};function x(){var c,r;const{Accordion:t}=acfwfObj.components,{caret_img_src:e,toggle_text:s,balance_text:i,button_text:m,instructions:u,placeholder:p,dispatchLolyatyPoint:a,amount:n,setAmount:f,ButtonDisabled:o,lpfw_block:l}=k();return(r=(c=l==null?void 0:l.loyalty_points)==null?void 0:c.points)!=null&&r.user_points?React.createElement("div",{className:"acfwf-components lpfw-checkout-ui-block"},React.createElement(t,{title:s,caret_img_src:e},React.createElement("div",null,React.createElement("p",{className:"acfw-store-credit-user-balance"},React.createElement("div",{dangerouslySetInnerHTML:{__html:i}})),React.createElement("p",{className:"lpfw-loyalty-point-instructions"},React.createElement("div",{dangerouslySetInnerHTML:{__html:u}})),React.createElement("div",{id:"lpfw_redeem_loyalty_point",className:"lpfw-redeem-loyalty-point-form-field acfw-checkout-form-button-field "},React.createElement("p",{className:"form-row form-row-first acfw-form-control-wrapper acfw-col-left-half wfacp-input-form"},React.createElement("label",{htmlFor:"coupon_code"}),React.createElement("input",{type:"text",className:"input-text wc_input_price ",value:n,placeholder:p,onChange:_=>{f(_.target.value)}})),React.createElement("p",{className:"form-row form-row-last acfw-col-left-half acfw_coupon_btn_wrap"},React.createElement("label",{className:"acfw-form-control-label"}," "),React.createElement("button",{type:"button",className:"button alt",onClick:a,disabled:o},m)))))):null}function L(){const{registerPlugin:t}=wp.plugins,{ExperimentalOrderMeta:e}=wc.blocksCheckout;t("lpfw-loyalty-point",{render:()=>React.createElement(e,null,React.createElement(g,null),React.createElement(x,null)),scope:"woocommerce-checkout"})}L();export{N as __vite_legacy_guard};
