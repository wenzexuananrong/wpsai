System.register([],(function(e,t){"use strict";return{execute:function(){var e=document.createElement("style");e.textContent=".agc-tooltip-toggler{cursor:pointer;text-decoration:underline}.agc-tooltip-container{background:#fff;border:1px solid #eee;box-shadow:0 2px 10px rgba(0,0,0,.2)}.agc-tooltip-container .components-popover__content{width:100%;max-width:250px}.agc-tooltip-container .agc-tooltip-title{display:flex;align-items:center;justify-content:start;border-bottom:1px solid #eee;font-size:1em;font-weight:700;padding:.5em 1em}.agc-tooltip-container .agc-tooltip-title>*{margin-right:.5em}.agc-tooltip-container .agc-tooltip-content{font-size:.9em;padding:.5em 1em}\n",document.head.appendChild(e);const t="agc-wc-checkout-block",c=()=>{const{adminAjaxUrl:e,wc:{dummyUpdateCart:c}}=acfwfObj,{useState:o}=wp.element,{dispatch:a}=wp.data,{wcSettings:n}=wc,{redeem_form:r}=n.getSetting(`${t}_data`),[l,i]=o(""),[s,m]=o(!1);return{code:l,setCode:i,buttonDisabled:s,setButtonDisabled:m,redeem_form:r,dispatchRedeemGiftCard:t=>{m(!0),jQuery.post(e,{action:"agcfw_redeem_gift_card",gift_card_code:l,_wpnonce:r.nonce,is_cart_checkout_block:!0},(e=>{c(),i(""),a("core/notices").createNotice(e.status,e.message,{type:"snackbar",context:"wc/checkout"}),setTimeout((()=>{m(!1)}),200)}))}}};function o(e){const{code:t,setCode:o,buttonDisabled:a,redeem_form:n,dispatchRedeemGiftCard:r}=c(),{labels:l}=n;return React.createElement("div",{id:"agc-redeem-gift-card-form",className:"agc-redeem-gift-card-form acfw-checkout-form-button-field"},React.createElement("p",{className:"form-row form-row-first acfw-form-control-wrapper acfw-col-left-half"},React.createElement("label",{htmlFor:"gift_card_code"}),React.createElement("input",{type:"text",className:"gift_card_code input-text",value:t,placeholder:l.input_placeholder,onChange:e=>o(e.target.value)})),React.createElement("p",{className:"form-row form-row-last acfw-form-control-wrapper acfw-col-right-half"},React.createElement("label",{className:"agc-form-control-label acfw-form-control-label"}," "),React.createElement("button",{type:"button",className:"agc-form-control-button button alt",disabled:a||!t,onClick:r},l.button_text)))}const a=e=>{const{useState:t}=wp.element,{Popover:c}=wp.components,{iconSrc:o,toggleText:a,title:n,content:r}=e,[l,i]=t(!1);return React.createElement(React.Fragment,null,React.createElement("span",{className:"agc-tooltip-toggler",onClick:()=>i(!l)},a),l&&React.createElement(c,{className:"agc-tooltip-container",position:"top center",focusOnMount:!0,onFocusOutside:()=>i(!1)},React.createElement("h4",{className:"agc-tooltip-title"},React.createElement("img",{className:"agc-tooltip-icon",src:o,alt:"question"}),React.createElement("span",null,n)),React.createElement("div",{className:"agc-tooltip-content"},r)))};!function(){const{Accordion:e}=acfwfObj.components,{wcSettings:c}=wc,{ExperimentalOrderMeta:n}=wc.blocksCheckout,{registerPlugin:r}=wp.plugins,{caret_img_src:l,question_img_src:i,is_user_logged_in:s,display_gift_card_redeem_form:m,redeem_form:{labels:d}}=c.getSetting(`${t}_data`);"yes"===m&&r("agc-gift-card-discount-form",{render:()=>React.createElement(n,null,React.createElement("div",{className:"agc-components acfwf-components agc-checkout-ui-block"},React.createElement(e,{title:d.title,caret_img_src:l},s?React.createElement(React.Fragment,null,React.createElement("p",{className:"agc-redeem-description"},d.description," ",React.createElement(a,{iconSrc:i,toggleText:d.tooltip_link_text,title:d.tooltip_title,content:d.tooltip_content})),React.createElement(o,null)):React.createElement("p",{className:"agc-redeem-description",dangerouslySetInnerHTML:{__html:d.guest_content}})))),scope:"woocommerce-checkout"})}()}}}));
