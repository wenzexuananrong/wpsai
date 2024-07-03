System.register([],(function(e,t){"use strict";return{execute:function(){var e=document.createElement("style");e.textContent=".agcfw-block-redeem-form{font-size:1em}\n",document.head.appendChild(e);const{redeemFormBlockTexts:t}=agcfwBlocksi18n,n=wp.components.withSpokenMessages((e=>{const{name:n,attributes:l,setAttributes:o}=e,{labels:a}=t;return React.createElement(React.Fragment,null,React.createElement(wp.blockEditor.InspectorControls,{key:"inspector"},React.createElement(wp.components.PanelBody,{title:a.main,initialOpen:!0},React.createElement(wp.components.TextControl,{label:a.title,value:l.title,onChange:e=>o({title:e})}),React.createElement(wp.components.TextControl,{label:a.description,value:l.description,onChange:e=>o({description:e})})),React.createElement(wp.components.PanelBody,{title:a.tooltip_content},React.createElement(wp.components.TextControl,{label:a.link_text,value:l.tooltip_link_text,onChange:e=>o({tooltip_link_text:e})}),React.createElement(wp.components.TextControl,{label:a.title,value:l.tooltip_title,onChange:e=>o({tooltip_title:e})}),React.createElement(wp.components.TextControl,{label:a.content,value:l.tooltip_content,onChange:e=>o({tooltip_content:e})})),React.createElement(wp.components.PanelBody,{title:a.form_fields},React.createElement(wp.components.TextControl,{label:a.input_placeholder,value:l.input_placeholder,onChange:e=>o({input_placeholder:e})}),React.createElement(wp.components.TextControl,{label:a.button_text,value:l.button_text,onChange:e=>o({button_text:e})})),React.createElement(wp.components.PanelBody,{title:a.guest_panel},React.createElement(wp.components.TextareaControl,{label:a.guest_content,value:l.guest_content,onChange:e=>o({guest_content:e})}))),React.createElement(wp.serverSideRender,{block:n,attributes:l,EmptyResponsePlaceholder:()=>React.createElement(wp.components.Placeholder,{label:t.title,className:"agcfw-block-redeem-form"},React.createElement("p",null,"There was an error"))}))})),{redeemFormBlockTexts:l}=agcfwBlocksi18n,o={name:"acfw/gift-card-redeem-form",settings:{title:l.title,icon:"tickets-alt",category:"advancedcoupons",keywords:["gift","card","advanced","redeem"],description:l.description,supports:{align:["wide","full"],html:!1},example:{attributes:{isPreview:!0}},attributes:{title:{type:"string",default:l.defaults.title},description:{type:"string",default:l.defaults.description},tooltip_link_text:{type:"string",default:l.defaults.tooltip_link_text},tooltip_title:{type:"string",default:l.defaults.tooltip_title},tooltip_content:{type:"string",default:l.defaults.tooltip_content},input_placeholder:{type:"string",default:l.defaults.input_placeholder},button_text:{type:"string",default:l.defaults.button_text},guest_content:{type:"string",default:l.defaults.guest_content}},edit:e=>React.createElement(n,{...e}),save:()=>null}},a=e=>{if(!e)return;const{name:t,settings:n}=e;wp.blocks.registerBlockType(t,n)};wp.domReady((()=>{[o].forEach(a)}))}}}));
