/**
 * This file is part of the evoCore framework - {@link http://evocore.net/}
 * See also {@link https://github.com/b2evolution/b2evolution}.
 */


// Event for styled button to browse files
jQuery( document ).on( 'change', '.btn-file :file', function()
{
	var label = jQuery( this ).val().replace( /\\/g, '/' ).replace( /.*\//, '' );
	jQuery( this ).parent().next().html( label );
} );


/**
 * Fades the relevant object to provide feedback, in case of success.
 *
 * Used only on BACK-office in the following files:
 *  - _misc_js.funcs.php
 *  - blog_widgets.js
 *  - src/evo_links.js
 *
 * @param jQuery selector
 */
function evoFadeSuccess( selector )
{
	evoFadeBg(selector, new Array("#ddff00", "#bbff00"));
}


/**
 * Fades the relevant object to provide feedback, in case of failure.
 *
 * Used only in BACK-office in the following files:
 *  - _misc_js.funcs.php
 *  - src/evo_links.js
 *
 * @param jQuery selector
 */
function evoFadeFailure( selector )
{
	evoFadeBg(selector, new Array("#9300ff", "#ff000a", "#ff0000"));
}


/**
 * Fades the relevant object to provide feedback, in case of highlighting
 * e.g. for items the file manager get called for ("#fm_highlighted").
 *
 * Used only on BACK-office in the following file:
 *  - _file_list.inc.php
 *
 * @param jQuery selector
 */
function evoFadeHighlight( selector )
{
	evoFadeBg(selector, new Array("#ffbf00", "#ffe79f"));
}


/**
 * Fade jQuery selector via backgrounds colors (bgs), back to original background
 * color and then remove any styles (from animations and others)
 *
 * Used only on BACK-office in the following files:
 *  - _misc_js.funcs.php
 *  - blog_widgets.js
 *  - src/evo_links.js
 *  - _file_list.inc.php
 *
 * @param string|jQuery
 * @param Array
 * @param object Options ("speed")
 */
function evoFadeBg( selector, bgs, options )
{
	var origBg = jQuery(selector).css("backgroundColor");
	var speed = options && options.speed || '"slow"';

	var toEval = 'jQuery(selector).animate({ backgroundColor: ';
	for( e in bgs )
	{
		if( typeof( bgs[e] ) != 'string' )
		{ // Skip wrong color value
			continue;
		}
		toEval += '"'+bgs[e]+'"'+'}, '+speed+' ).animate({ backgroundColor: ';
	}
	toEval += 'origBg }, '+speed+', "", function(){jQuery( this ).css( "backgroundColor", "" );});';

	eval(toEval);
}


/**
 * Flash evobar via backgrounds colors (bgs), back to original background
 * color and then remove any styles (from animations and others)
 *
 * Used only by hotkeys
 *
 * @param Array
 * @param object Options ("speed")
 */
function evobarFlash( bgs, options )
{
	var evobar = '#evo_toolbar';
	var menus = '#evo_toolbar .evobar-menu a';
	var origBg = jQuery( evobar ).css( "backgroundColor" );

	jQuery( menus ).css( "backgroundColor", "inherit" );
	var speed = options && options.speed || '"fast"';

	var toEval = 'jQuery( evobar ).animate({ backgroundColor: ';
	for( e in bgs )
	{
		if( typeof( bgs[e] ) != 'string' )
		{ // Skip wrong color value
			continue;
		}
		toEval += '"' + bgs[e] + '"' + '}, ' + speed + ' ).animate({ backgroundColor: ';
	}
	toEval += 'origBg }, '+speed+', "", function(){ jQuery( this ).css( "backgroundColor", "" ); jQuery( menus ).css( "backgroundColor", "" ); } );';

	eval( toEval );
}


/**
 * Open the item in a preview window (a new window with target 'b2evo_preview'), by changing
 * the form's action attribute and target temporarily.
 *
 * fp> This is gonna die...
 */
function b2edit_open_preview( form_selector, new_action_url, preview_block )
{
	var form = jQuery( form_selector );

	if( form.length == 0 )
	{	// Form is not detected on the current page by requested selector:
		// Redirect to new URL without form submitting:
		location.href = new_action_url;
		return false;
	}

	if( form.attr( 'target' ) == 'b2evo_preview' )
	{	// Avoid a double-click on the Preview button:
		return false;
	}

	if( typeof preview_block != undefined && preview_block === true )
	{	// Enable debug blocks of included content-block Items by short tag [include:]:
		form.find('input[name=preview_block]').val( '1' );
	}

	// Set new form action URL:
	var saved_action_url = form.attr( 'action' );
	form.attr( 'action', new_action_url );

	// Submit a form with a preview action to new opened window:
	form.attr( 'target', 'b2evo_preview' );
	preview_window = window.open( '', 'b2evo_preview' );
	preview_window.focus();
	form.submit();

	// Revert action URL and target of the form to original values:
	form.attr( 'action', saved_action_url );
	form.attr( 'target', '_self' );
	form.find('input[name=preview_block]').val( '0' );

	// Don't submit the original form:
	return false;
}


/**
 * Submits the form after setting its action attribute to "newaction" and the blog value to "blog" (if given).
 *
 * This is used to switch to another blog or tab, but "keep" the input in the form.
 */
function b2edit_reload( form_selector, new_action_url, blog, params, reset )
{
	var form = jQuery( form_selector );

	if( form.length == 0 || form.find( 'input[type=hidden][name^=crumb_]' ).length == 0 )
	{	// Form is not detected on the current page by requested selector
		// or form is not loaded completely because of slow code:
		// Redirect to new URL without form submitting:
		location.href = new_action_url;
		return false;
	}

	// Set new form action URL:
	form.attr( 'action', new_action_url );

	var hidden_action_set = false;

	// Set the new form "action" HIDDEN value:
	if( form.find( '[name="actionArray[update]"]' ).length > 0 )
	{	// Is an editing mode?
		form.append( '<input type="hidden" name="action" value="edit_switchtab" />' );
		hidden_action_set = true;
	}
	else if( form.find( '[name="actionArray[create]"]' ).length > 0 )
	{	// Is a creating mode?
		form.append( '<input type="hidden" name="action" value="new_switchtab" />' );
		hidden_action_set = true;
	}
	else
	{	// Other modes:
		form.append( '<input type="hidden" name="action" value="switchtab" />' );
		hidden_action_set = true;
	}

	if( hidden_action_set && ( typeof params != 'undefined' ) )
	{
		for( param in params )
		{
			form.append( '<input type="hidden" name="' + param + '" value="' + params[param] + '" />' );
		}
	}

	// Set the blog we are switching to:
	if( typeof blog != 'undefined' && blog != 'undefined' )
	{
		if( blog == null )
		{ // Set to an empty string, otherwise POST param value will be 'null' in IE and it cause issues
			blog = '';
		}
		form.find( '[name="blog"]' ).val( blog );
	}

	// disable bozo validator if active:
	// TODO: dh> this seems to actually delete any events attached to beforeunload, which can cause problems if e.g. a plugin hooks this event
	window.onbeforeunload = null;

	if( typeof( reset ) != 'undefined' && reset == true )
	{ // Reset the form:
		form.reset();
	}

	// Submit the form:
	form.submit();

	return false;
}


/**
 * Submits the form after clicking on link to change item type
 *
 * This is used to switch to another blog or tab, but "keep" the input in the form.
 */
function b2edit_type( msg, newaction, submit_action )
{
	var reset = false;
	if( typeof( bozo ) && bozo.nb_changes > 0 )
	{ // Ask about saving of the changes in the form
		reset = ! confirm( msg );
	}

	return b2edit_reload( '#item_checkchanges', newaction, null, { action: submit_action }, reset );
}


/**
 * Ask to submit the form after clicking on action button
 *
 * This is used to the button "Extract tags"
 */
function b2edit_confirm( msg, newaction, submit_action )
{
	if( typeof( bozo ) && bozo.nb_changes > 0 )
	{	// Ask about saving of the changes in the form:
		if( ! confirm( msg ) )
		{
			return false;
		}
	}

	return b2edit_reload( '#item_checkchanges', newaction, null, { action: submit_action }, false );
}


/**
 * Request WHOIS information
 *
 * Opens a modal window displaying results of WHOIS query
 */
function get_whois_info( ip_address )
{
	var window_height = jQuery( window ).height();
	var margin_size_height = 20;
	var modal_height = window_height - ( margin_size_height * 2 );

	openModalWindow(
			'<span id="spinner" class="loader_img loader_user_report absolute_center" title="' + evo_js_lang_whois_title + '"></span>',
			'90%', modal_height + 'px', true, 'WHOIS - ' + ip_address, true, true );

	jQuery.ajax(
	{
		type: 'GET',
		url: htsrv_url + 'async.php',
		data: {
			action: 'get_whois_info',
			query: ip_address,
			window_height: modal_height
		},
		success: function( result )
		{
			if( ajax_response_is_correct( result ) )
			{
				result = ajax_debug_clear( result );
				openModalWindow( result, '90%', modal_height + 'px', true, 'WHOIS - ' + ip_address, true );
			}
		}
	} );

	return false;
}


/**
 * Open and highlight selected template
 */
function b2template_list_highlight( obj )
{
	var link = jQuery( obj );
	var select = link.prevAll( 'select' );
	var selected_template = select.find( ':selected' ).val();
	var link_url = link.attr('href');

	if( selected_template )
	{
		link_url += '&highlight=' + selected_template;
	}

	var new_target = link.attr('target');
	
	if ( new_target === undefined ) 
	{
		if( window.self !== window.top )
		{
			window.top.location = link_url;
		}
		else
		{
			window.location = link_url;
		}
	}
	else
	{
		window.open( link_url, new_target );
	}

	return false;
}


/**
 * Copy text of element to clipboard
 *
 * @param string Element ID
 * @param string Optional text, use this to copy instead of content of the Element
 */
function evo_copy_to_clipboard( id, custom_text )
{
	if( typeof( custom_text ) == 'undefined' )
	{	// Copy text from Element:
		var text_obj = document.getElementById( id );
	}
	else
	{	// Copy a provided Text:
		var text_obj = document.createElement( 'span' );
		text_obj.innerHTML = custom_text;
		document.body.appendChild( text_obj );
	}

	// Create range to select element by ID:
	var range = document.createRange();
	range.selectNode( text_obj );
	// Clear current selection:
	window.getSelection().removeAllRanges();
	// Select text of the element temporary:
	window.getSelection().addRange( range );
	// Copy to clipboard:
	document.execCommand( 'copy' );
	// Deselect:
	window.getSelection().removeAllRanges();
	// Highlight copied element:
	evoFadeBg( '#' + id, new Array( '#ffbf00' ), { speed: 100 } );

	if( typeof( custom_text ) != 'undefined' )
	{	// Remove temp object what was used only for copying above:
		document.body.removeChild( text_obj );
	}

	return false;
}