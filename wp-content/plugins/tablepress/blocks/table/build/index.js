!function(){"use strict";var e={n:function(t){var r=t&&t.__esModule?function(){return t.default}:function(){return t};return e.d(r,{a:r}),r},d:function(t,r){for(var a in r)e.o(r,a)&&!e.o(t,a)&&Object.defineProperty(t,a,{enumerable:!0,get:r[a]})},o:function(e,t){return Object.prototype.hasOwnProperty.call(e,t)}},t=window.wp.blocks,r=window.wp.shortcode,a=e.n(r),n=JSON.parse('{"u2":"tablepress/table"}');const l=e=>{let t=Object.entries(e.named).map((([e,t])=>{let r="";return t=t.replace(/“([^”]*)”/g,"$1"),(/\s/.test(t)||""===t)&&(r='"'),t.includes('"')&&(r="'"),`${e}=${r}${t}${r}`})).join(" ");return e.numeric.forEach((e=>{/\s/.test(e)?t+=' "'+e+'"':t+=" "+e})),t},s=function(e){let r=a().next(tp.table.shortcode,e).shortcode.attrs;r={named:{...r.named},numeric:[...r.numeric]};const s=r.named.id;delete r.named.id;let o=l(r);return o=o.replace(/=“([^”]*)”/g,'="$1"'),(0,t.createBlock)(n.u2,{id:s,parameters:o})};var o={from:[{type:"shortcode",tag:tp.table.shortcode,attributes:{id:{type:"string",shortcode:({named:{id:e=""}})=>e},parameters:{type:"string",shortcode:e=>(delete(e={named:{...e.named},numeric:[...e.numeric]}).named.id,l(e))}}},{type:"enter",regExp:a().regexp(tp.table.shortcode),transform:({content:e})=>s(e)},{type:"block",blocks:["core/shortcode"],transform:({text:e})=>s(e),isMatch:({text:e})=>void 0!==a().next(tp.table.shortcode,e),isMultiBlock:!1}],to:[{type:"block",blocks:["core/shortcode"],transform:({id:e,parameters:r})=>{""!==(r=r.trim())&&(r+=" ");const a=`[${tp.table.shortcode} id=${e} ${r}/]`;return(0,t.createBlock)("core/shortcode",{text:a})}}]},c=window.React,i=window.wp.i18n,d=window.wp.serverSideRender,p=e.n(d),b=window.wp.blockEditor,m=window.wp.components;const u=Object.entries(tp.tables).map((([e,t])=>({value:e,label:(0,i.sprintf)((0,i.__)("ID %1$s: “%2$s”","tablepress"),e,t)}))),h=function(){return""!==tp.url&&(0,c.createElement)(m.ExternalLink,{href:tp.url},(0,i.__)("Manage your tables.","tablepress"))};var w=window.wp.element;let f=null;const _=Object.keys(tp.tables);_.length&&(f={attributes:{id:_[Math.floor(Math.random()*_.length)],parameters:""}});var g=f;(0,t.registerBlockType)(n.u2,{transforms:o,edit:function({attributes:e,setAttributes:t}){const r=(0,b.useBlockProps)();let s;if(e.id&&tp.tables.hasOwnProperty(e.id))s=(0,c.createElement)("div",{...r},tp.load_block_preview&&(0,c.createElement)(p(),{block:n.u2,attributes:e,className:"render-wrapper"}),(0,c.createElement)("div",{className:"table-overlay"},(0,i.sprintf)((0,i.__)("TablePress table %1$s: “%2$s”","tablepress"),e.id,tp.tables[e.id])));else{let t=0<u.length?(0,i.__)("Select the TablePress table that you want to embed in the Settings sidebar.","tablepress"):(0,i.__)("There are no TablePress tables on this site yet.","tablepress");e.id&&(t=(0,i.sprintf)((0,i.__)("There is a problem: The TablePress table with the ID “%1$s” could not be found.","tablepress"),e.id)+" "+t),s=(0,c.createElement)("div",{...r},(0,c.createElement)(m.Placeholder,{icon:(0,c.createElement)(m.Icon,{icon:"list-view"}),label:(0,i.__)("TablePress table","tablepress"),instructions:t},(0,c.createElement)(h,null)))}const o=(0,c.createElement)(c.Fragment,null,(0,c.createElement)(b.InspectorControls,null,(0,c.createElement)(m.PanelBody,{opened:!0},0<u.length?(0,c.createElement)(m.ComboboxControl,{label:(0,i.__)("Table:","tablepress"),help:(0,c.createElement)(c.Fragment,null,(0,i.__)("Select the TablePress table that you want to embed.","tablepress"),""!==tp.url&&" ",(0,c.createElement)(h,null)),value:e.id,options:u,onChange:e=>{var r;null!==(r=e)&&void 0!==r||(e=""),t({id:e.replace(/[^0-9a-zA-Z-_]/g,"")})}}):(0,c.createElement)(c.Fragment,null,(0,i.__)("There are no TablePress tables on this site yet.","tablepress"),""!==tp.url&&" ",(0,c.createElement)(h,null)))),e.id&&tp.tables.hasOwnProperty(e.id)&&(0,c.createElement)(b.InspectorAdvancedControls,null,(0,c.createElement)(m.TextControl,{label:(0,i.__)("Configuration parameters:","tablepress"),help:(0,i.__)("These additional parameters can be used to modify specific table features.","tablepress")+" "+(0,i.__)("See the TablePress Documentation for more information.","tablepress"),value:e.parameters,onChange:e=>{e=(e=a().replace(tp.table.shortcode,e,(({attrs:e})=>(delete(e={named:{...e.named},numeric:[...e.numeric]}).named.id," "+l(e)+" ")))).replace(/=“([^”]*)”/g,'="$1"'),t({parameters:e})},onBlur:e=>{const r=e.target.value.trim();t({parameters:r})}})));return(0,c.createElement)(c.Fragment,null,s,o)},save:function({attributes:{id:e="",parameters:t=""}}){return""===e?"":(""!==(t=t.trim())&&(t+=" "),(0,c.createElement)(w.RawHTML,null,`[${tp.table.shortcode} id=${e} ${t}/]`))},example:g})}();