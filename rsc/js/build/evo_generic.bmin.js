/* This file includes ALL generic files that may be used on any page of front-office and back-office */

function evo_prevent_key_enter(e){jQuery(e).keypress(function(e){if(13==e.keyCode)return!1})}function evo_render_star_rating(){jQuery("#comment_rating").each(function(e){var t=jQuery("span.raty_params",this);t&&jQuery(this).html("").raty(t)})}jQuery(document).ready(function(){if("undefined"!=typeof evo_init_datepicker&&jQuery(evo_init_datepicker.selector).datepicker(evo_init_datepicker.config),"undefined"!=typeof evo_link_position_config){var t=(_=evo_link_position_config.config).display_inline_reminder,n=_.defer_inline_reminder;jQuery(document).on("change",evo_link_position_config.selector,{url:_.url,crumb:_.crumb},function(e){"inline"==this.value&&t&&!n&&(alert(_.alert_msg),t=!1),evo_link_change_position(this,e.data.url,e.data.crumb)})}if("undefined"!=typeof evo_itemform_renderers__click&&jQuery("#itemform_renderers .dropdown-menu").on("click",function(e){e.stopPropagation()}),"undefined"!=typeof evo_commentform_renderers__click&&jQuery("#commentform_renderers .dropdown-menu").on("click",function(e){e.stopPropagation()}),"undefined"!=typeof evo_disp_download_delay_config){var e=evo_disp_download_delay_config,o=setInterval(function(){jQuery("#download_timer").html(e),0==e&&(clearInterval(o),jQuery("#download_help_url").show()),e--},1e3);jQuery("#download_timer_js").show()}if("undefined"!=typeof evo_skin_bootstrap_forum__quote_button_click&&jQuery(".quote_button").click(function(){var e=jQuery("form[id^=evo_comment_form_id_]");return 0==e.length||(e.attr("action",jQuery(this).attr("href")),e.submit(),!1)}),"undefined"!=typeof evo_ajax_form_config)for(var i=Object.values(evo_ajax_form_config),r=0;r<i.length;r++){var _=i[r];window["ajax_form_offset_"+_.form_number]=jQuery("#ajax_form_number_"+_.form_number).offset().top,window["request_sent_"+_.form_number]=!1,window["ajax_form_loading_number_"+_.form_number]=0;var a="get_form"+_.form_number;window[a]=function(){var o="#ajax_form_number_"+_.form_number;window["ajax_form_loading_number_"+_.form_number]++,jQuery.ajax({url:htsrv_url+"anon_async.php",type:"POST",data:_.json_params,success:function(e){jQuery(o).html(ajax_debug_clear(e)),"get_comment_form"==_.json_params.action&&evo_render_star_rating()},error:function(e,t,n){jQuery(".loader_ajax_form",o).after('<div class="red center">'+n+": "+e.responseText+"</div>"),window["ajax_form_loading_number_"+_.form_number]<3&&setTimeout(function(){jQuery(".loader_ajax_form",o).next().remove(),window[a]()},1e3)}})};var c="check_and_show_"+_.form_number;window[c]=function(e){if(!window["request_sent_"+_.form_number]){var t=null!=typeof e&&e;(t=t||jQuery(window).scrollTop()>=window["ajax_form_offset_"+_.form_number]-jQuery(window).height()-20)&&(window["request_sent_"+_.form_number]=!0,window[a]())}},jQuery(window).scroll(function(){window[c]()}),jQuery(window).resize(function(){window[c]()}),window[c](_.load_ajax_form_on_page_load)}if("undefined"!=typeof evo_user_func__callback_filter_userlist&&(jQuery("#country").change(function(){jQuery(this);jQuery.ajax({type:"POST",url:htsrv_url+"anon_async.php",data:"action=get_regions_option_list&ctry_id="+jQuery(this).val(),success:function(e){jQuery("#region").html(ajax_debug_clear(e)),1<jQuery("#region option").length?jQuery("#region_filter").show():jQuery("#region_filter").hide(),load_subregions(0)}})}),jQuery("#region").change(function(){load_subregions(jQuery(this).val())}),jQuery("#subregion").change(function(){load_cities(jQuery("#country").val(),jQuery("#region").val(),jQuery(this).val())}),window.load_subregions=function(t){jQuery.ajax({type:"POST",url:htsrv_url+"anon_async.php",data:"action=get_subregions_option_list&rgn_id="+t,success:function(e){jQuery("#subregion").html(ajax_debug_clear(e)),1<jQuery("#subregion option").length?jQuery("#subregion_filter").show():jQuery("#subregion_filter").hide(),load_cities(jQuery("#country").val(),t,0)}})},window.load_cities=function(e,t,n){void 0===e&&(e=0),jQuery.ajax({type:"POST",url:htsrv_url+"anon_async.php",data:"action=get_cities_option_list&ctry_id="+e+"&rgn_id="+t+"&subrg_id="+n,success:function(e){jQuery("#city").html(ajax_debug_clear(e)),1<jQuery("#city option").length?jQuery("#city_filter").show():jQuery("#city_filter").hide()}})}),"undefined"!=typeof evo_widget_param_switcher_config)for(r=0;r<evo_widget_param_switcher_config.length;r++){_=evo_widget_param_switcher_config[r];jQuery("a[data-param-switcher="+_.widget_id+"]").click(function(){var e=_.default_params,t=new RegExp("([?&])(("+jQuery(this).data("code")+"|redir)=[^&]*(&|$))+","g"),n=location.href.replace(t,"$1");for(default_param in n=n.replace(/[\?&]$/,""),n+=-1===n.indexOf("?")?"?":"&",n+=jQuery(this).data("code")+"="+jQuery(this).data("value"),e)t=new RegExp("[?&]"+default_param+"=","g"),n.match(t)||(n+="&"+default_param+"="+e[default_param]);return n+="&redir=no",window.history.pushState("","",n),jQuery("a[data-param-switcher="+_.widget_id+"]").attr("class",_.link_class),jQuery(this).attr("class",_.active_link_class),!1})}var u;"undefined"!=typeof coll_activity_stats_widget_config&&(window.resize_coll_activity_stat_widget=function(){var e=[],t=[],n=[],o=coll_activity_stats_widget_config.time_period;if(null==plot){plot=jQuery("#canvasbarschart").data("plot"),n=plot.axes.xaxis.ticks.slice(0);for(var i=0;i<plot.series.length;i++)e.push(plot.series[i].data.slice(0));if(7==e[0].length)t=e;else for(i=0;i<e.length;i++){for(var r=[],_=7,a=1;0<_;_--,a++)r.unshift([_,e[i][e[i].length-a][1]]);t.push(r)}}if(jQuery("#canvasbarschart").width()<650){if("last_week"!=o){for(i=0;i<plot.series.length;i++)plot.series[i].data=t[i];plot.axes.xaxis.ticks=n.slice(-7),o="last_week"}}else if("last_month"!=o){for(i=0;i<plot.series.length;i++)plot.series[i].data=e[i];plot.axes.xaxis.ticks=n,o="last_month"}plot.replot({resetAxes:!0})},jQuery(window).resize(function(){clearTimeout(u),u=setTimeout(resize_coll_activity_stat_widget,100)}))}),jQuery(document).ready(function(){"undefined"!=typeof evo_skin_bootstrap_forums__post_list_header&&jQuery("#evo_workflow_status_filter").change(function(){var e=location.href.replace(/([\?&])((status|redir)=[^&]*(&|$))+/,"$1"),t=jQuery(this).val();""!==t&&(e+=(-1==e.indexOf("?")?"?":"&")+"status="+t+"&redir=no"),location.href=e.replace("?&","?").replace(/\?$/,"")})}),jQuery(document).ready(function(){"undefined"!=typeof evo_comment_rating_config&&evo_render_star_rating()}),jQuery(document).ready(function(){"undefined"!=typeof evo_widget_coll_search_form&&(jQuery(evo_widget_coll_search_form.selector).tokenInput(evo_widget_coll_search_form.url,evo_widget_coll_search_form.config),void 0!==evo_widget_coll_search_form.placeholder&&jQuery("#token-input-search_author").attr("placeholder",evo_widget_coll_search_form.placeholder).css("width","100%"))}),jQuery(document).ready(function(){"undefined"!=typeof evo_autocomplete_login_config&&(jQuery("input.autocomplete_login").on("added",function(){jQuery("input.autocomplete_login").each(function(){if(!jQuery(this).hasClass("tt-input")&&!jQuery(this).hasClass("tt-hint")){var t="";t=jQuery(this).hasClass("only_assignees")?restapi_url+evo_autocomplete_login_config.url:restapi_url+"users/logins",jQuery(this).data("status")&&(t+="&status="+jQuery(this).data("status")),jQuery(this).typeahead(null,{displayKey:"login",source:function(e,o){jQuery.ajax({type:"GET",dataType:"JSON",url:t,data:{q:e},success:function(e){var t=new Array;for(var n in e.list)t.push({login:e.list[n]});o(t)}})}})}})}),jQuery("input.autocomplete_login").trigger("added"),evo_prevent_key_enter(evo_autocomplete_login_config.selector))}),jQuery(document).ready(function(){"undefined"!=typeof evo_widget_poll_initialize&&(jQuery('.evo_poll__selector input[type="checkbox"]').on("click",function(){var e=jQuery(this).closest(".evo_poll__table"),t=jQuery(".evo_poll__selector input:checked",e).length>=e.data("max-answers");jQuery(".evo_poll__selector input[type=checkbox]:not(:checked)",e).prop("disabled",t)}),jQuery(".evo_poll__table").each(function(){var e=jQuery(this);e.width()>e.parent().width()&&(jQuery(".evo_poll__title",e).css("white-space","normal"),jQuery(".evo_poll__title label",e).css({width:Math.floor(e.parent().width()/2)+"px","word-wrap":"break-word"}))}))}),jQuery(document).ready(function(){if("undefined"!=typeof evo_plugin_auto_anchors_settings){jQuery("h1, h2, h3, h4, h5, h6").each(function(){if(jQuery(this).attr("id")&&jQuery(this).hasClass("evo_auto_anchor_header")){var e=location.href.replace(/#.+$/,"")+"#"+jQuery(this).attr("id");jQuery(this).append(' <a href="'+e+'" class="evo_auto_anchor_link"><span class="fa fa-link"></span></a>')}});var t=jQuery("#evo_toolbar").length?jQuery("#evo_toolbar").height():0;jQuery(".evo_auto_anchor_link").on("click",function(){var e=jQuery(this).attr("href");return jQuery("html,body").animate({scrollTop:jQuery(this).offset().top-t-evo_plugin_auto_anchors_settings.offset_scroll},function(){window.history.pushState("","",e)}),!1})}}),jQuery(document).ready(function(){if("undefined"!=typeof evo_plugin_table_contents_settings){var n=jQuery("#evo_toolbar").length?jQuery("#evo_toolbar").height():0;jQuery(".evo_plugin__table_of_contents a").on("click",function(){var e=jQuery("#"+jQuery(this).data("anchor"));if(0==e.length||!e.prop("tagName").match(/^h[1-6]$/i))return!0;var t=jQuery(this).attr("href");return jQuery("html,body").animate({scrollTop:e.offset().top-n-evo_plugin_table_contents_settings.offset_scroll},function(){window.history.pushState("","",t)}),!1})}}),jQuery(document).ready(function(){var o,n,i,r,_;"undefined"!=typeof evo_plugin_tinymce_config__toggle_switch_warning&&(o=evo_plugin_tinymce_config__toggle_switch_warning,window.toggle_switch_warning=function(t){var e=o.activate_link,n=o.deactivate_link;return jQuery.get(t?e:n,function(e){jQuery(document).trigger("wysiwyg_warning_changed",[t])}),!1}),"undefined"!=typeof evo_plugin_tinymce_config__quicksettings&&(n=evo_plugin_tinymce_config__quicksettings,i=jQuery("#"+n.item_id),jQuery(document).on("wysiwyg_warning_changed",function(e,t){i.html(t?n.deactivate_warning_link:n.activate_warning_link)})),"undefined"!=typeof evo_plugin_tinymce_config__toggle_editor&&(r=evo_plugin_tinymce_config__toggle_editor,window.displayWarning=r.display_warning,window.confirm_switch=function(){return jQuery("input[name=hideWarning]").is(":checked")&&window.toggle_switch_warning(!1),window.tinymce_plugin_toggleEditor(r.content_id),closeModalWindow(),!1},window.tinymce_plugin_toggleEditor=function(e){var t=jQuery("#"+r.content_id);if(jQuery("[id^=tinymce_plugin_toggle_button_]").removeClass("active").attr("disabled","disabled"),!window.tinymce_plugin_init_done)return window.tinymce_plugin_init_done=!0,void window.tinymce_plugin_init_tinymce(function(){window.tinymce_plugin_toggleEditor(null)});window.tinymce.get(e)?(window.tinymce.execCommand("mceRemoveEditor",!1,e),jQuery.get(r.save_editor_state_url),jQuery("#tinymce_plugin_toggle_button_html").addClass("active"),jQuery("#tinymce_plugin_toggle_button_wysiwyg").removeAttr("disabled"),jQuery('[name="editor_code"]').attr("value","html"),jQuery(".quicktags_toolbar, .evo_code_toolbar, .evo_prism_toolbar, .b2evMark_toolbar, .evo_mermaid_toolbar").show(),jQuery("#block_renderer_evo_code, #block_renderer_evo_prism, #block_renderer_b2evMark, #block_renderer_evo_mermaid").removeClass("disabled"),jQuery("input#renderer_evo_code, input#renderer_evo_prism, input#renderer_b2evMark, input#renderer_evo_mermaid").each(function(){jQuery(this).hasClass("checked")&&jQuery(this).attr("checked","checked").removeClass("checked"),jQuery(this).removeAttr("disabled")}),e&&t.attr("data-required")&&(t.removeAttr("data-required"),t.attr("required",!0))):(window.tinymce.execCommand("mceAddEditor",!1,e),jQuery.get(r.save_editor_state_url),jQuery("#tinymce_plugin_toggle_button_wysiwyg").addClass("active"),jQuery("#tinymce_plugin_toggle_button_html").removeAttr("disabled"),jQuery('[name="editor_code"]').attr("value",r.plugin_code),jQuery(".quicktags_toolbar, .evo_code_toolbar, .evo_prism_toolbar, .b2evMark_toolbar, .evo_mermaid_toolbar").hide(),jQuery("#block_renderer_evo_code, #block_renderer_evo_prism, #block_renderer_b2evMark, #block_renderer_evo_mermaid").addClass("disabled"),jQuery("input#renderer_evo_code, input#renderer_evo_prism, input#renderer_b2evMark, input#renderer_evo_mermaid").each(function(){jQuery(this).is(":checked")&&jQuery(this).addClass("checked"),jQuery(this).attr("disabled","disabled").removeAttr("checked")}),e&&t.prop("required")&&(t.attr("data-required",!0),t.removeAttr("required")))},jQuery(document).on("wysiwyg_warning_changed",function(e,t){window.displayWarning=t}),jQuery("[id^=tinymce_plugin_toggle_button_]").click(function(){"WYSIWYG"==jQuery(this).val()&&window.displayWarning?(evo_js_lang_close=r.cancel_btn_label,openModalWindow("<p>"+r.toggle_warning_msg+'</p><form><input type="checkbox" name="hideWarning" value="1"> '+r.wysiwyg_checkbox_label+'<input type="submit" name="submit" onclick="return confirm_switch();"></form>',"500px","",!0,'<span class="text-danger">'+r.warning_text+"</span>",[r.ok_btn_label,"btn-primary"],!0)):window.tinymce_plugin_toggleEditor(r.content_id)})),"undefined"!=typeof evo_plugin_tinymce_config__init&&(_=evo_plugin_tinymce_config__init,window.autocomplete_static_options=[],jQuery(".user.login").each(function(){var e=jQuery(this).text();""!=e&&-1==window.autocomplete_static_options.indexOf(e)&&("@"==e[0]&&(e=e.substr(1)),window.autocomplete_static_options.push(e))}),window.autocomplete_static_options=window.autocomplete_static_options.join(),window.tmce_init=_.tmce_init,window.tinymce_plugin_displayed_error=!1,window.tinymce_plugin_init_done=!1,window.tinymce_plugin_init_tinymce=function(t){void 0===window.tinymce?window.tinymce_plugin_displayed_error||(alert(_.display_error_msg),window.tinymce_plugin_displayed_error=!0):(void 0!==window.tmce_init.oninit&&(t=function(){window.tmce_init.oninit(),t()}),window.tmce_init.oninit=function(){t(),window.tinymce.get(_.content_id)&&"object"==typeof b2evo_Callbacks&&(b2evo_Callbacks.register_callback("get_selected_text_for_"+_.content_id,function(e){var t=window.tinymce.get(_.content_id);return t?t.selection.getContent():null},!0),b2evo_Callbacks.register_callback("wrap_selection_for_"+_.content_id,function(e){var t=window.tinymce.get(_.content_id);if(!t)return null;var n=t.selection.getContent();if(e.replace)var o=e.before+e.after;else o=e.before+n+e.after;return t.selection.setContent(o),!0},!0),b2evo_Callbacks.register_callback("str_replace_for_"+_.content_id,function(e){var t=window.tinymce.get(_.content_id);return t?(t.setContent(t.getContent().replace(e.search,e.replace)),!0):null},!0),b2evo_Callbacks.register_callback("insert_raw_into_"+_.content_id,function(e){return window.tinymce.execInstanceCommand(_.content_id,"mceInsertRawHTML",!1,e),!0},!0));var e=jQuery("#"+_.content_id);e.prop("required")&&(e.attr("data-required",!0),e.removeAttr("required"))},window.tmce_init.init_instance_callback=function(e){if(window.shortcut_keys)for(var t=0;t<window.shortcut_keys.length;t++){var n=window.shortcut_keys[t];e.shortcuts.add(n,"b2evo shortcut key: "+n,function(){window.shortcut_handler(n)})}},window.tmce_init.setup=function(e){e.on("init",window.tmce_init.oninit)},window.tinymce.on("AddEditor",function(t){var e=jQuery("#"+_.content_id);return e.val().match(/<(p\s?|br\s?\/?)[^>]*>/i)||jQuery.ajax({type:"POST",url:_.update_content_url,data:{content:e.val()},success:function(e){t.editor.setContent(e)}}),!1}),window.tinymce.init(window.tmce_init))},_.use_tinymce&&window.tinymce_plugin_toggleEditor(_.content_id),jQuery('[name="editor_code"]').attr("value",_.editor_code))});