import{a as m}from"./links.bbde6535.js";import{M as d}from"./MetaTag.db5a37b1.js";import{C as f}from"./Card.3bd391bc.js";import{C as g}from"./SettingsRow.42f6374c.js";import{x as n,c as h,z as s,l as p,o as y,a as e,A as b,t as c}from"./vue.runtime.esm-bundler.308f2021.js";import{_ as w}from"./_plugin-vue_export-helper.5bcc150c.js";import"./default-i18n.3881921e.js";import"./isArrayLikeObject.965a2149.js";import"./Tooltip.432f1167.js";import"./Caret.baea7fe0.js";import"./index.fadde3df.js";import"./Slide.22dc72dc.js";import"./Row.16199427.js";const k={setup(){return{optionsStore:m()}},components:{CoreCard:f,CoreSettingsRow:g},mixins:[d],data(){return{pagePostOptions:[],strings:{pinterest:this.$t.__("Pinterest",this.$td),description:this.$t.__("Pinterest uses Open Graph metadata just like Facebook, so be sure to keep Open Graph enabled on the Facebook tab checked if you want to optimize your site for Pinterest.",this.$td),learnHowToGetPinterestTag:this.$t.__("Learn how to get your Pinterest Verification Code",this.$td),skipStep:this.$t.__("If you have already confirmed your website with Pinterest, you can skip the step below.",this.$td),pinterestVerificationCode:this.$t.__("Pinterest Verification Code",this.$td)}}}},C={class:"aioseo-pinterest"},P={class:"aioseo-settings-row aioseo-section-description"},V=["innerHTML"],x=e("br",null,null,-1),T=e("br",null,null,-1);function S(r,o,$,i,t,L){const l=n("base-input"),u=n("core-settings-row"),_=n("core-card");return y(),h("div",C,[s(_,{slug:"pinterest","header-text":t.strings.pinterest},{default:p(()=>[e("div",P,[b(c(t.strings.description)+" ",1),e("span",{innerHTML:r.$links.getDocLink(t.strings.learnHowToGetPinterestTag,"pinterestSiteVerification",!0)},null,8,V),x,T,e("strong",null,c(t.strings.skipStep),1)]),s(u,{name:t.strings.pinterestVerificationCode,align:""},{content:p(()=>[s(l,{size:"medium",modelValue:i.optionsStore.options.webmasterTools.pinterest,"onUpdate:modelValue":o[0]||(o[0]=a=>i.optionsStore.options.webmasterTools.pinterest=a),onBlur:o[1]||(o[1]=a=>r.maybeUpdateId("pinterest"))},null,8,["modelValue"])]),_:1},8,["name"])]),_:1},8,["header-text"])])}const R=w(k,[["render",S]]);export{R as default};
