// Quick Chat 4.13 - load
var quick_chat=jQuery.extend(quick_chat||{},{script_suffix:1==quick_chat.debug_mode?".dev":"",private_current_name:"quick_chat_private_current_"+quick_chat.user_id,get_script:function(b,c,a){a=jQuery.extend(a||{},{crossDomain:1==quick_chat.debug_mode?!0:!1,dataType:"script",cache:!0,success:c,url:b});return jQuery.ajax(a)},load:function(){(0!=jQuery("div.quick-chat-container").length||jQuery.cookie(quick_chat.private_current_name)&&"{}"!=jQuery.cookie(quick_chat.private_current_name))&&quick_chat.get_script(quick_chat.url+
"js/quick-chat-init"+quick_chat.script_suffix+".js?"+quick_chat.version)}});/(chrome|webkit)[ \/]([\w.]+)/.test(window.navigator.userAgent.toLowerCase())?jQuery(window).load(quick_chat.load()):jQuery(document).ready(quick_chat.load());