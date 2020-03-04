/* This includes 11 files: build/evo_generic.bmin.js, src/evo_modal_window.js, src/evo_images.js, src/evo_user_crop.js, src/evo_user_report.js, src/evo_user_contact_groups.js, src/evo_rest_api.js, src/evo_item_flag.js, src/evo_links.js, src/evo_forms.js, ajax.js */

function evo_prevent_key_enter(e){jQuery(e).keypress(function(e){if(13==e.keyCode)return!1})}function openModalWindow(e,t,r,o,a,n){var i="overlay_page_active";void 0!==o&&1==o&&(i="overlay_page_active_transparent"),void 0===t&&(t="560px");var s="";void 0!==r&&(0<r||""!=r)&&(s=' style="height:'+r+'"'),0<jQuery("#overlay_page").length?jQuery("#overlay_page").html(e):(jQuery("body").append('<div id="screen_mask"></div><div id="overlay_wrap" style="width:'+t+'"><div id="overlay_layout"><div id="overlay_page"'+s+"></div></div></div>"),jQuery("#screen_mask").fadeTo(1,.5).fadeIn(200),jQuery("#overlay_page").html(e).addClass(i),jQuery(document).on("click","#close_button, #screen_mask, #overlay_page",function(e){if("overlay_page"!=jQuery(this).attr("id"))return closeModalWindow(),!1;var t=jQuery("#overlay_page form");if(t.length){var r=t.position().top+jQuery("#overlay_wrap").position().top,o=r+t.height();e.clientY>r&&e.clientY<o||closeModalWindow()}return!0}))}function closeModalWindow(e){return void 0===e&&(e=window.document),jQuery("#overlay_page",e).hide(),jQuery(".action_messages",e).remove(),jQuery("#server_messages",e).insertBefore(".first_payload_block"),jQuery("#overlay_wrap",e).remove(),jQuery("#screen_mask",e).remove(),!1}function user_crop_avatar(e,t,r){void 0===r&&(r="avatar");var o=750,a=320,n=jQuery(window).width(),i=jQuery(window).height(),s=i/n,_=10,u=10;_=a<(n=o<n?o:n<a?a:n)-2*_?10:0,u=a<(i=o<i?o:i<a?a:i)-2*u?10:0;var l=o<n?o:n,c=o<i?o:i;openModalWindow('<span id="spinner" class="loader_img loader_user_report absolute_center" title="'+evo_js_lang_loading+'"></span>',l+"px",c+"px",!0,evo_js_lang_crop_profile_pic,[evo_js_lang_crop,"btn-primary"],!0);var d=jQuery("div.modal-dialog div.modal-body").length?jQuery("div.modal-dialog div.modal-body"):jQuery("#overlay_page"),p=parseInt(d.css("paddingTop")),y=parseInt(d.css("paddingRight")),f=parseInt(d.css("paddingBottom")),v=parseInt(d.css("paddingLeft")),h=(jQuery("div.modal-dialog div.modal-body").length?parseInt(d.css("min-height")):c-100)-(p+f),j={user_ID:e,file_ID:t,aspect_ratio:s,content_width:l-(v+y),content_height:h,display_mode:"js",crumb_user:evo_js_crumb_user};return evo_js_is_backoffice?(j.ctrl="user",j.user_tab="crop",j.user_tab_from=r):(j.blog=evo_js_blog,j.disp="avatar",j.action="crop"),jQuery.ajax({type:"POST",url:evo_js_user_crop_ajax_url,data:j,success:function(e){openModalWindow(e,l+"px",c+"px",!0,evo_js_lang_crop_profile_pic,[evo_js_lang_crop,"btn-primary"])}}),!1}function user_report(e,t){openModalWindow('<span class="loader_img loader_user_report absolute_center" title="'+evo_js_lang_loading+'"></span>',"auto","",!0,evo_js_lang_report_user,[evo_js_lang_report_this_user_now,"btn-danger"],!0);var r={action:"get_user_report_form",user_ID:e,crumb_user:evo_js_crumb_user};return evo_js_is_backoffice?(r.is_backoffice=1,r.user_tab=t):r.blog=evo_js_blog,jQuery.ajax({type:"POST",url:evo_js_user_report_ajax_url,data:r,success:function(e){openModalWindow(e,"auto","",!0,evo_js_lang_report_user,[evo_js_lang_report_this_user_now,"btn-danger"])}}),!1}function user_contact_groups(e){return openModalWindow('<span class="loader_img loader_user_report absolute_center" title="'+evo_js_lang_loading+'"></span>',"auto","",!0,evo_js_lang_contact_groups,evo_js_lang_save,!0),jQuery.ajax({type:"POST",url:evo_js_user_contact_groups_ajax_url,data:{action:"get_user_contact_form",blog:evo_js_blog,user_ID:e,crumb_user:evo_js_crumb_user},success:function(e){openModalWindow(e,"auto","",!0,evo_js_lang_contact_groups,evo_js_lang_save)}}),!1}function evo_rest_api_request(url,params_func,func_method,method){var params=params_func,func=func_method;"function"==typeof params_func&&(func=params_func,params={},method=func_method),void 0===method&&(method="GET"),jQuery.ajax({contentType:"application/json; charset=utf-8",type:method,url:restapi_url+url,data:params}).then(function(data,textStatus,jqXHR){"object"==typeof jqXHR.responseJSON&&eval(func)(data,textStatus,jqXHR)})}function evo_rest_api_print_error(e,t,r){if("string"!=typeof t&&void 0===t.code&&(t=void 0===t.responseJSON?t.statusText:t.responseJSON),void 0===t.code)var o='<h4 class="text-danger">Unknown error: '+t+"</h4>";else{o='<h4 class="text-danger">'+t.message+"</h4>";r&&(o+="<div><b>Code:</b> "+t.code+"</div><div><b>Status:</b> "+t.data.status+"</div>")}evo_rest_api_end_loading(e,o)}function evo_rest_api_start_loading(e){jQuery(e).addClass("evo_rest_api_loading").append('<div class="evo_rest_api_loader">loading...</div>')}function evo_rest_api_end_loading(e,t){jQuery(e).removeClass("evo_rest_api_loading").html(t).find(".evo_rest_api_loader").remove()}function evo_link_initialize_fieldset(r){if(0<jQuery("#"+r+"attachments_fieldset_table").length){var e=jQuery("#"+r+"attachments_fieldset_table").height();e=320<e?320:e<97?97:e,jQuery("#"+r+"attachments_fieldset_wrapper").height(e),jQuery("#"+r+"attachments_fieldset_wrapper").resizable({minHeight:80,handles:"s",resize:function(e,t){jQuery("#"+r+"attachments_fieldset_wrapper").resizable("option","maxHeight",jQuery("#"+r+"attachments_fieldset_table").height()),evo_link_update_overlay(r)}}),jQuery(document).on("click","#"+r+"attachments_fieldset_wrapper .ui-resizable-handle",function(){var e=jQuery("#"+r+"attachments_fieldset_table").height(),t=jQuery("#"+r+"attachments_fieldset_wrapper").height()+80;jQuery("#"+r+"attachments_fieldset_wrapper").css("height",e<t?e:t),evo_link_update_overlay(r)})}}function evo_link_update_overlay(e){jQuery("#"+e+"attachments_fieldset_overlay").length&&jQuery("#"+e+"attachments_fieldset_overlay").css("height",jQuery("#"+e+"attachments_fieldset_wrapper").closest(".panel").height())}function evo_link_fix_wrapper_height(e){var t=void 0===e?"":e,r=jQuery("#"+t+"attachments_fieldset_table").height();jQuery("#"+t+"attachments_fieldset_wrapper").height()!=r&&jQuery("#"+t+"attachments_fieldset_wrapper").height(jQuery("#"+t+"attachments_fieldset_table").height())}function evo_link_change_position(r,e,t){var o=r,a=r.value,n=r.id.substr(17);return jQuery.get(e+"anon_async.php?action=set_object_link_position&link_ID="+n+"&link_position="+a+"&crumb_link="+t,{},function(e,t){"OK"==(e=ajax_debug_clear(e))?(evoFadeSuccess(jQuery(o).closest("tr")),jQuery(o).closest("td").removeClass("error"),"cover"==a&&jQuery("select[name=link_position][id!="+r.id+"] option[value=cover]:selected").each(function(){jQuery(this).parent().val("aftermore"),evoFadeSuccess(jQuery(this).closest("tr"))})):(jQuery(o).val(e),evoFadeFailure(jQuery(o).closest("tr")),jQuery(o.form).closest("td").addClass("error"))}),!1}function evo_link_insert_inline(e,t,r,o,a,n){if(null==o&&(o=0),void 0!==n){var i="["+e+":"+t;r.length&&(i+=":"+r),i+="]",void 0!==a&&!1!==a&&(i+=a+"[/"+e+"]");var s=jQuery("#display_position_"+t);0!=s.length&&"inline"!=s.val()?(deferInlineReminder=!0,evo_rest_api_request("links/"+t+"/position/inline",function(e){s.val("inline"),evoFadeSuccess(s.closest("tr")),s.closest("td").removeClass("error"),textarea_wrap_selection(n,i,"",o,window.document)},"POST"),deferInlineReminder=!1):textarea_wrap_selection(n,i,"",o,window.document)}}function evo_link_delete(o,a,n,e){return evo_rest_api_request("links/"+n,{action:e},function(e){if("item"==a||"comment"==a||"emailcampaign"==a||"message"==a){var t=window.b2evoCanvas;if(null!=t){var r=new RegExp("\\[(image|file|inline|video|audio|thumbnail):"+n+":?[^\\]]*\\]","ig");textarea_str_replace(t,r,"",window.document)}}jQuery(o).closest("tr").remove(),evo_link_fix_wrapper_height()},"DELETE"),!1}function evo_link_change_order(_,e,u){return evo_rest_api_request("links/"+e+"/"+u,function(e){var t=jQuery(_).closest("tr"),r=t.find("span[data-order]");if("move_up"==u){var o=r.attr("data-order"),a=jQuery(t.prev()).find("span[data-order]"),n=a.attr("data-order");t.prev().before(t),r.attr("data-order",n),a.attr("data-order",o)}else{o=r.attr("data-order");var i=jQuery(t.next()).find("span[data-order]"),s=i.attr("data-order");t.next().after(t),r.attr("data-order",s),i.attr("data-order",o)}evoFadeSuccess(t)},"POST"),!1}function evo_link_attach(e,t,r,o,a){return evo_rest_api_request("links",{action:"attach",type:e,object_ID:t,root:r,path:o},function(e){void 0===a&&(a="");var t=jQuery("#"+a+"attachments_fieldset_table .results table",window.parent.document),r=jQuery(e.list_content);t.replaceWith(jQuery("table",r)).promise().done(function(e){setTimeout(function(){window.parent.evo_link_fix_wrapper_height()},10)})}),!1}function evo_link_ajax_loading_overlay(){var e=jQuery("#attachments_fieldset_table"),t=!1;return 0==e.find(".results_ajax_loading").length&&(t=jQuery('<div class="results_ajax_loading"><div>&nbsp;</div></div>'),e.css("position","relative"),t.css({width:e.width(),height:e.height()}),e.append(t)),t}function evo_link_refresh_list(e,t,r){var o=evo_link_ajax_loading_overlay();return o&&evo_rest_api_request("links",{action:void 0===r?"refresh":"sort",type:e.toLowerCase(),object_ID:t},function(e){jQuery("#attachments_fieldset_table").html(e.html),o.remove(),evo_link_fix_wrapper_height()}),!1}function evo_link_sort_list(r){var o,a=jQuery("#"+r+"attachments_fieldset_table tbody.filelist_tbody tr");a.sort(function(e,t){var r=parseInt(jQuery("span[data-order]",e).attr("data-order")),o=parseInt(jQuery("span[data-order]",t).attr("data-order"));return(r=r||a.length)<(o=o||a.length)?-1:o<r?1:0}),$.each(a,function(e,t){o=(0===e?jQuery(t).prependTo("#"+r+"attachments_fieldset_table tbody.filelist_tbody"):jQuery(t).insertAfter(o),t)})}function ajax_debug_clear(e){return e=(e=e.replace(/<!-- Ajax response end -->/,"")).replace(/(<div class="jslog">[\s\S]*)/i,""),jQuery.trim(e)}function ajax_response_is_correct(e){return!!e.match(/<!-- Ajax response end -->/)&&""!=(e=ajax_debug_clear(e))}jQuery(document).ready(function(){if("undefined"!=typeof evo_init_datepicker&&jQuery(evo_init_datepicker.selector).datepicker(evo_init_datepicker.config),"undefined"!=typeof evo_link_position_config){var t=evo_link_position_config.config,r=t.display_inline_reminder,o=t.defer_inline_reminder;jQuery(document).on("change",evo_link_position_config.selector,{url:t.url,crumb:t.crumb},function(e){"inline"==this.value&&r&&!o&&(alert(t.alert_msg),r=!1),evo_link_change_position(this,e.data.url,e.data.crumb)})}"undefined"!=typeof evo_itemform_renderers__click&&jQuery("#itemform_renderers .dropdown-menu").on("click",function(e){e.stopPropagation()}),"undefined"!=typeof evo_commentform_renderers__click&&jQuery("#commentform_renderers .dropdown-menu").on("click",function(e){e.stopPropagation()}),"undefined"!=typeof evo_skin_bootstrap_forum__quote_button_click&&jQuery(".quote_button").click(function(){var e=jQuery("form[id^=evo_comment_form_id_]");return 0==e.length||(e.attr("action",jQuery(this).attr("href")),e.submit(),!1)}),"undefined"!=typeof evo_user_func__callback_filter_userlist&&(jQuery("#country").change(function(){jQuery(this),jQuery.ajax({type:"POST",url:htsrv_url+"anon_async.php",data:"action=get_regions_option_list&ctry_id="+jQuery(this).val(),success:function(e){jQuery("#region").html(ajax_debug_clear(e)),1<jQuery("#region option").length?jQuery("#region_filter").show():jQuery("#region_filter").hide(),load_subregions(0)}})}),jQuery("#region").change(function(){load_subregions(jQuery(this).val())}),jQuery("#subregion").change(function(){load_cities(jQuery("#country").val(),jQuery("#region").val(),jQuery(this).val())}),window.load_subregions=function(t){jQuery.ajax({type:"POST",url:htsrv_url+"anon_async.php",data:"action=get_subregions_option_list&rgn_id="+t,success:function(e){jQuery("#subregion").html(ajax_debug_clear(e)),1<jQuery("#subregion option").length?jQuery("#subregion_filter").show():jQuery("#subregion_filter").hide(),load_cities(jQuery("#country").val(),t,0)}})},window.load_cities=function(e,t,r){void 0===e&&(e=0),jQuery.ajax({type:"POST",url:htsrv_url+"anon_async.php",data:"action=get_cities_option_list&ctry_id="+e+"&rgn_id="+t+"&subrg_id="+r,success:function(e){jQuery("#city").html(ajax_debug_clear(e)),1<jQuery("#city option").length?jQuery("#city_filter").show():jQuery("#city_filter").hide()}})})}),jQuery(document).ready(function(){"undefined"!=typeof evo_skin_bootstrap_forums__post_list_header&&jQuery("#evo_workflow_status_filter").change(function(){var e=location.href.replace(/([\?&])((status|redir)=[^&]*(&|$))+/,"$1"),t=jQuery(this).val();""!==t&&(e+=(-1==e.indexOf("?")?"?":"&")+"status="+t+"&redir=no"),location.href=e.replace("?&","?").replace(/\?$/,"")})}),jQuery(document).ready(function(){"undefined"!=typeof evo_comment_rating_config&&jQuery("#comment_rating").html("").raty(evo_comment_rating_config)}),jQuery(document).ready(function(){"undefined"!=typeof evo_widget_coll_search_form&&(jQuery(evo_widget_coll_search_form.selector).tokenInput(evo_widget_coll_search_form.url,evo_widget_coll_search_form.config),void 0!==evo_widget_coll_search_form.placeholder&&jQuery("#token-input-search_author").attr("placeholder",evo_widget_coll_search_form.placeholder).css("width","100%"))}),jQuery(document).ready(function(){"undefined"!=typeof evo_autocomplete_login_config&&(jQuery("input.autocomplete_login").on("added",function(){jQuery("input.autocomplete_login").each(function(){if(!jQuery(this).hasClass("tt-input")&&!jQuery(this).hasClass("tt-hint")){var t="";t=jQuery(this).hasClass("only_assignees")?restapi_url+evo_autocomplete_login_config.url:restapi_url+"users/logins",jQuery(this).data("status")&&(t+="&status="+jQuery(this).data("status")),jQuery(this).typeahead(null,{displayKey:"login",source:function(e,o){jQuery.ajax({type:"GET",dataType:"JSON",url:t,data:{q:e},success:function(e){var t=new Array;for(var r in e.list)t.push({login:e.list[r]});o(t)}})}})}})}),jQuery("input.autocomplete_login").trigger("added"),evo_prevent_key_enter(evo_autocomplete_login_config.selector))}),jQuery(document).ready(function(){"undefined"!=typeof evo_widget_poll_initialize&&(jQuery('.evo_poll__selector input[type="checkbox"]').on("click",function(){var e=jQuery(this).closest(".evo_poll__table"),t=jQuery(".evo_poll__selector input:checked",e).length>=e.data("max-answers");jQuery(".evo_poll__selector input[type=checkbox]:not(:checked)",e).prop("disabled",t)}),jQuery(".evo_poll__table").each(function(){var e=jQuery(this);e.width()>e.parent().width()&&(jQuery(".evo_poll__title",e).css("white-space","normal"),jQuery(".evo_poll__title label",e).css({width:Math.floor(e.parent().width()/2)+"px","word-wrap":"break-word"}))}))}),jQuery(document).ready(function(){if("undefined"!=typeof evo_plugin_auto_anchors_settings){jQuery("h1, h2, h3, h4, h5, h6").each(function(){if(jQuery(this).attr("id")&&jQuery(this).hasClass("evo_auto_anchor_header")){var e=location.href.replace(/#.+$/,"")+"#"+jQuery(this).attr("id");jQuery(this).append(' <a href="'+e+'" class="evo_auto_anchor_link"><span class="fa fa-link"></span></a>')}});var t=jQuery("#evo_toolbar").length?jQuery("#evo_toolbar").height():0;jQuery(".evo_auto_anchor_link").on("click",function(){var e=jQuery(this).attr("href");return jQuery("html,body").animate({scrollTop:jQuery(this).offset().top-t-evo_plugin_auto_anchors_settings.offset_scroll},function(){window.history.pushState("","",e)}),!1})}}),jQuery(document).ready(function(){if("undefined"!=typeof evo_plugin_table_contents_settings){var r=jQuery("#evo_toolbar").length?jQuery("#evo_toolbar").height():0;jQuery(".evo_plugin__table_of_contents a").on("click",function(){var e=jQuery("#"+jQuery(this).data("anchor"));if(0==e.length||!e.prop("tagName").match(/^h[1-6]$/i))return!0;var t=jQuery(this).attr("href");return jQuery("html,body").animate({scrollTop:e.offset().top-r-evo_plugin_table_contents_settings.offset_scroll},function(){window.history.pushState("","",t)}),!1})}}),jQuery(document).keyup(function(e){27==e.keyCode&&closeModalWindow()}),jQuery(document).ready(function(){jQuery("img.loadimg").each(function(){jQuery(this).prop("complete")?(jQuery(this).removeClass("loadimg"),""==jQuery(this).attr("class")&&jQuery(this).removeAttr("class")):jQuery(this).on("load",function(){jQuery(this).removeClass("loadimg"),""==jQuery(this).attr("class")&&jQuery(this).removeAttr("class")})})}),jQuery(document).on("click","a.evo_post_flag_btn",function(){var t=jQuery(this),e=parseInt(t.data("id"));return 0<e&&(t.data("status","inprogress"),jQuery("span",jQuery(this)).addClass("fa-x--hover"),evo_rest_api_request("collections/"+t.data("coll")+"/items/"+e+"/flag",function(e){e.flag?(t.find("span:first").show(),t.find("span:last").hide()):(t.find("span:last").show(),t.find("span:first").hide()),jQuery("span",t).removeClass("fa-x--hover"),setTimeout(function(){t.removeData("status")},500)},"PUT")),!1}),jQuery(document).on("mouseover","a.evo_post_flag_btn",function(){"inprogress"!=jQuery(this).data("status")&&jQuery("span",jQuery(this)).addClass("fa-x--hover")}),jQuery(document).on("keydown","textarea, input",function(e){!e.metaKey&&!e.ctrlKey||13!=e.keyCode&&10!=e.keyCode||jQuery(this).closest("form").submit()});