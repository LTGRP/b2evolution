<?php
/**
 * This file implements the UI view for the Advanced blog properties.
 *
 * This file is part of the b2evolution/evocms project - {@link http://b2evolution.net/}.
 * See also {@link https://github.com/b2evolution/b2evolution}.
 *
 * @license GNU GPL v2 - {@link http://b2evolution.net/about/gnu-gpl-license}
 *
 * @copyright (c)2003-2018 by Francois Planque - {@link http://fplanque.com/}.
 * Parts of this file are copyright (c)2004-2005 by Daniel HAHLER - {@link http://thequod.de/contact}.
 *
 * @package admin
 *
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

/**
 * @var Blog
 */
global $edited_Blog;

global $Plugins, $Settings;

global $basepath, $rsc_url, $admin_url;

$Form = new Form( NULL, 'blogadvanced_checkchanges' );

$Form->begin_form( 'fform' );

$Form->add_crumb( 'collection' );
$Form->hidden_ctrl();
$Form->hidden( 'action', 'update' );
$Form->hidden( 'tab', 'advanced' );
$Form->hidden( 'blog', $edited_Blog->ID );


$Form->begin_fieldset( T_('After each new post...').get_manual_link('after-each-new-post') );
	if( $edited_Blog->get_setting( 'allow_access' ) == 'users' )
	{
		echo '<p class="center orange">'.T_('This collection is for logged in users only.').' '.T_('The ping plugins can be enabled only for public collections.').'</p>';
	}
	elseif( $edited_Blog->get_setting( 'allow_access' ) == 'members' )
	{
		echo '<p class="center orange">'.T_('This collection is for members only.').' '.T_('The ping plugins can be enabled only for public collections.').'</p>';
	}
	$ping_plugins = preg_split( '~\s*,\s*~', $edited_Blog->get_setting( 'ping_plugins' ), -1, PREG_SPLIT_NO_EMPTY );

	$available_ping_plugins = $Plugins->get_list_by_event( 'ItemSendPing' );
	$displayed_ping_plugin = false;
	if( $available_ping_plugins )
	{
		foreach( $available_ping_plugins as $loop_Plugin )
		{
			if( empty( $loop_Plugin->code ) )
			{ // Ping plugin needs a code
				continue;
			}
			$displayed_ping_plugin = true;

			$checked = in_array( $loop_Plugin->code, $ping_plugins );
			$Form->checkbox( 'blog_ping_plugins[]', $checked,
				isset( $loop_Plugin->ping_service_setting_title ) ? $loop_Plugin->ping_service_setting_title : sprintf( /* TRANS: %s is a ping service name */ T_('Ping %s'), $loop_Plugin->ping_service_name ),
				$loop_Plugin->ping_service_note, '', $loop_Plugin->code,
				// Disable ping plugins for not public collection:
				$edited_Blog->get_setting( 'allow_access' ) != 'public' );

			while( ( $key = array_search( $loop_Plugin->code, $ping_plugins ) ) !== false )
			{
				unset( $ping_plugins[$key] );
			}
		}
	}
	if( ! $displayed_ping_plugin )
	{
		echo '<p>'.T_('There are no ping plugins activated.').'</p>';
	}

	// Provide previous ping services as hidden fields, in case the plugin is temporarily disabled:
	foreach( $ping_plugins as $ping_plugin_code )
	{
		$Form->hidden( 'blog_ping_plugins[]', $ping_plugin_code );
	}
$Form->end_fieldset();


$Form->begin_fieldset( T_('External Feeds').get_manual_link('external-feeds') );

	$Form->text_input( 'atom_redirect', $edited_Blog->get_setting( 'atom_redirect' ), 50, T_('Atom Feed URL'),
	T_('Example: Your Feedburner Atom URL which should replace the original feed URL.').'<br />'
			.sprintf( T_( 'Note: the original URL was: %s' ), url_add_param( $edited_Blog->get_item_feed_url( '_atom' ), 'redir=no' ) ),
	array('maxlength'=>255, 'class'=>'large') );

	$Form->text_input( 'rss2_redirect', $edited_Blog->get_setting( 'rss2_redirect' ), 50, T_('RSS2 Feed URL'),
	T_('Example: Your Feedburner RSS2 URL which should replace the original feed URL.').'<br />'
			.sprintf( T_( 'Note: the original URL was: %s' ), url_add_param( $edited_Blog->get_item_feed_url( '_rss2' ), 'redir=no' ) ),
	array('maxlength'=>255, 'class'=>'large') );

$Form->end_fieldset();

$Form->begin_fieldset( T_('Template').get_manual_link('collection-template') );
	$Form->checkbox_input( 'blog_allow_duplicate', $edited_Blog->get_setting( 'allow_duplicate' ), T_('Allow duplication'), array( 'note' => T_('Check to allow anyone to duplicate this collection.') ) );
$Form->end_fieldset();


if( $current_User->check_perm( 'blog_admin', 'edit', false, $edited_Blog->ID ) )
{	// Permission to edit advanced admin settings

	$Form->begin_fieldset( T_('Caching').get_admin_badge().get_manual_link('collection-cache-settings'), array( 'id' => 'caching' ) );
		$Form->checklist( array(
				array( 'ajax_form_enabled', 1, T_('Comment, Contact & Quick registration forms will be fetched by javascript'), $edited_Blog->get_setting( 'ajax_form_enabled' ) ),
				array( 'ajax_form_loggedin_enabled', 1, T_('Also use JS forms for logged in users'), $edited_Blog->get_setting( 'ajax_form_loggedin_enabled' ), ! $edited_Blog->get_setting( 'ajax_form_enabled' ) ),
			), 'ajax_form', T_('Enable AJAX forms') );

		$Form->checkbox_input( 'cache_enabled', $edited_Blog->get_setting('cache_enabled'), get_icon( 'page_cache_on' ).' '.T_('Enable page cache'), array( 'note'=>T_('Cache rendered blog pages') ) );
		$Form->checkbox_input( 'cache_enabled_widgets', $edited_Blog->get_setting('cache_enabled_widgets'), get_icon( 'block_cache_on' ).' '.T_('Enable widget/block cache'), array( 'note'=>T_('Cache rendered widgets') ) );
	$Form->end_fieldset();

	$Form->begin_fieldset( T_('In-skin Actions').get_admin_badge().get_manual_link('in-skin-action-settings'), array( 'id' => 'inskin_actions' ) );
		if( $login_Blog = & get_setting_Blog( 'login_blog_ID', $edited_Blog ) )
		{ // The login blog is defined in general settings
			$Form->info( T_( 'In-skin login' ), sprintf( T_('All login/registration functions are delegated to the collection: %s'), '<a href="'.$admin_url.'?ctrl=collections&tab=site_settings">'.$login_Blog->get( 'shortname' ).'</a>' ) );
		}
		else
		{ // Allow to select in-skin login for this blog
			$Form->checkbox_input( 'in_skin_login', $edited_Blog->get_setting( 'in_skin_login' ), T_( 'In-skin login' ), array( 'note' => T_( 'Use in-skin login form every time it\'s possible' ) ) );
		}
		$Form->checkbox_input( 'in_skin_editing', $edited_Blog->get_setting( 'in_skin_editing' ), T_( 'In-skin editing' ), array( 'note' => sprintf( T_('See more options in Features &gt; <a %s>Posts</a>'), 'href="'.$admin_url.'?ctrl=coll_settings&amp;tab=features&amp;blog='.$edited_Blog->ID.'#post_options"' ) ) );
		$Form->checkbox_input( 'in_skin_change_proposal', $edited_Blog->get_setting( 'in_skin_change_proposal' ), T_( 'In-skin change proposal' ) );
	$Form->end_fieldset();

	$Form->begin_fieldset( T_('Media directory location').get_admin_badge().get_manual_link('media-directory-location'), array( 'id' => 'media_dir_location' ) );
	global $media_path;
	$Form->radio( 'blog_media_location', $edited_Blog->get( 'media_location' ),
			array(
				array( 'none', T_('None') ),
				array( 'default', T_('Default'), $media_path.'blogs/'.$edited_Blog->urlname.'/' ),
				array( 'subdir', T_('Subdirectory of media folder').':',
					'',
					' <span class="nobr"><code>'.$media_path.'</code><input
						type="text" name="blog_media_subdir" class="form_text_input form-control" size="20" maxlength="255"
						class="'.( param_has_error('blog_media_subdir') ? 'field_error' : '' ).'"
						value="'.$edited_Blog->dget( 'media_subdir', 'formvalue' ).'" /></span>', '' ),
				array( 'custom',
					T_('Custom location').':',
					'',
					'<fieldset class="form-group">'
					.'<div class="label control-label col-lg-2">'.T_('directory').':</div><div class="input controls col-xs-8"><input
						type="text" class="form_text_input form-control" name="blog_media_fullpath" size="50" maxlength="255"
						class="'.( param_has_error('blog_media_fullpath') ? 'field_error' : '' ).'"
						value="'.$edited_Blog->dget( 'media_fullpath', 'formvalue' ).'" /></div>'
					.'<div class="clear"></div>'
					.'<div class="label control-label col-lg-2">'.T_('URL').':</div><div class="input controls col-xs-8"><input
						type="text" class="form_text_input form-control" name="blog_media_url" size="50" maxlength="255"
						class="'.( param_has_error('blog_media_url') ? 'field_error' : '' ).'"
						value="'.$edited_Blog->dget( 'media_url', 'formvalue' ).'" /></div></fieldset>' )
			), T_('Media directory'), true
		);
	$Form->info( T_('URL preview'), '<span id="blog_media_url_preview">'.$edited_Blog->get_media_url().'</span>'
		.' <a href="'.$admin_url.'?ctrl=coll_settings&tab=urls&blog='.$edited_Blog->ID.'" class="small">'.T_('CDN configuration').'</a>' );
	$Form->end_fieldset();

}

$Form->begin_fieldset( T_('Meta data').get_manual_link('blog-meta-data') );
	// TODO: move stuff to coll_settings
	$shortdesc_chars_count = utf8_strlen( html_entity_decode( $edited_Blog->get( 'shortdesc' ) ) );
	$Form->text( 'blog_shortdesc', $edited_Blog->get( 'shortdesc' ), 60, T_('Short Description'), T_('This is is used in meta tag description and RSS feeds. NO HTML!')
		.' ('.sprintf( T_('%s characters'), '<span id="blog_shortdesc_chars_count">'.$shortdesc_chars_count.'</span>' ).')', 250, 'large' );
	$Form->text( 'blog_keywords', $edited_Blog->get( 'keywords' ), 60, T_('Keywords'), T_('This is is used in meta tag keywords. NO HTML!'), 250, 'large' );
	$publisher_logo_params = array( 'file_type' => 'image', 'max_file_num' => 1, 'window_title' => T_('Select publisher logo'), 'root' => 'shared_0', 'size_name' => 'fit-320x320' );
	$Form->fileselect( 'blog_publisher_logo_file_ID', $edited_Blog->get_setting( 'publisher_logo_file_ID' ), T_('Publisher logo'), T_('This is used to add Structured Data to your pages.'), $publisher_logo_params );
	$Form->text( 'blog_publisher_name', $edited_Blog->get_setting( 'publisher_name' ), 60, T_('Publisher name'), T_('This is used to add Structured Data to your pages.'), 250, 'large' );
	$Form->text( 'blog_footer_text', $edited_Blog->get_setting( 'blog_footer_text' ), 60, T_('Blog footer'), sprintf(
		T_('Use &lt;br /&gt; to insert a line break. You might want to put your copyright or <a href="%s" target="_blank">creative commons</a> notice here.'),
		'http://creativecommons.org/license/' ), 1000, 'large' );
	$Form->textarea( 'single_item_footer_text', $edited_Blog->get_setting( 'single_item_footer_text' ), 2, T_('Single post footer'),
		T_('This will be displayed after each post in single post view.').' '.sprintf( T_('Available variables: %s.'), '<b>$perm_url$</b>, <b>$title$</b>, <b>$excerpt$</b>, <b>$author$</b>, <b>$author_login$</b>' ), 50 );
	$Form->textarea( 'xml_item_footer_text', $edited_Blog->get_setting( 'xml_item_footer_text' ), 2, T_('Post footer in RSS/Atom'),
		T_('This will be appended to each post in your RSS/Atom feeds.').' '.sprintf( T_('Available variables: %s.'), T_('same as above') ), 50 );
	$Form->textarea( 'blog_notes', $edited_Blog->get( 'notes' ), 5, T_('Notes'),
		T_('Additional info. Appears in the backoffice.'), 50 );
$Form->end_fieldset();

$Form->begin_fieldset( T_('Software credits').get_manual_link('software-credits') );
	$max_credits = $edited_Blog->get_setting( 'max_footer_credits' );
	$note = T_('You get the b2evolution software for <strong>free</strong>. We do appreciate you giving us credit. <strong>Thank you for your support!</strong>');
	if( $max_credits < 1 )
	{
		$note = '<img src="'.$rsc_url.'smilies/icon_sad.gif" alt="" class="bottom"> '.$note;
	}
	$Form->text( 'max_footer_credits', $max_credits, 1, T_('Max footer credits'), $note, 1 );
$Form->end_fieldset();


if( $current_User->check_perm( 'blog_admin', 'edit', false, $edited_Blog->ID ) )
{	// Permission to edit advanced admin settings

	$Form->begin_fieldset( T_('Skin and style').get_admin_badge().get_manual_link('skin-and-style') );
		$Form->checkbox( 'blog_allowblogcss', $edited_Blog->get( 'allowblogcss' ), T_('Allow customized blog CSS file'), T_('You will be able to customize the blog\'s skin stylesheet with a file named style.css in the blog\'s media file folder.') );
		$Form->checkbox( 'blog_allowusercss', $edited_Blog->get( 'allowusercss' ), T_('Allow user customized CSS file for this blog'), T_('Users will be able to customize the blog and skin stylesheets with a file named style.css in their personal file folder.') );
		$Form->textarea( 'blog_head_includes', $edited_Blog->get_setting( 'head_includes' ), 5, T_('Custom meta tag/css section (before &lt;/head&gt;)'),
			T_('Add custom meta tags and/or css styles to the &lt;head&gt; section. Example use: website verification, Google+, favicon image...'), 50 );
		$Form->textarea( 'blog_body_includes', $edited_Blog->get_setting( 'body_includes' ), 5, T_('Custom javascript section (after &lt;body&gt;)'),
			T_('Add custom javascript after the opening &lt;body&gt; tag.<br />Example use: tracking scripts, javascript libraries...'), 50 );
		$Form->textarea( 'blog_footer_includes', $edited_Blog->get_setting( 'footer_includes' ), 5, T_('Custom javascript section (before &lt;/body&gt;)'),
			T_('Add custom javascript before the closing &lt;/body&gt; tag in order to avoid any issues with page loading delays for visitors with slow connection speeds.<br />Example use: tracking scripts, javascript libraries...'), 50 );
	$Form->end_fieldset();

}


$Form->end_form( array( array( 'submit', 'submit', T_('Save Changes!'), 'SaveButton' ) ) );

?>

<script>
	jQuery( 'input[name=ajax_form_enabled]' ).click( function()
	{
		var checked = jQuery( this ).prop( 'checked' );
		jQuery( 'input[name=ajax_form_loggedin_enabled]' ).prop( 'disabled', ! checked );
		if( ! checked )
		{
			jQuery( 'input[name=cache_enabled]' ).prop( 'checked', false );
		}
	} );
	jQuery( '#cache_enabled' ).click( function()
	{
		if( jQuery( this ).prop( 'checked' ) )
		{
			jQuery( 'input[name=ajax_form_enabled]' ).prop( 'checked', true );
			jQuery( 'input[name=ajax_form_loggedin_enabled]' ).prop( 'disabled', false );
		}
	} );
	jQuery( '#advanced_perms' ).click( function()
	{
		if( ! jQuery( this ).is( ':checked' ) && jQuery( 'input[name=blog_allow_access][value=members]' ).is( ':checked' ) )
		{
			jQuery( 'input[name=blog_allow_access][value=users]' ).attr( 'checked', true );
		}
	} );
	jQuery( 'input[name=blog_allow_access][value=members]' ).click( function()
	{
		if( jQuery( this ).is( ':checked' ) )
		{
			jQuery( '#advanced_perms' ).attr( 'checked', true );
		}
	} );

	function update_blog_media_url_preview()
	{
		var url_preview = '';
		switch( jQuery( 'input[name=blog_media_location]:checked' ).val() )
		{
			case 'default':
				url_preview = '<?php echo format_to_js( $edited_Blog->get_local_media_url().'blogs/'.$edited_Blog->urlname.'/' ); ?>';
				break;
			case 'subdir':
				url_preview = '<?php echo format_to_js( $edited_Blog->get_local_media_url() ); ?>' + jQuery( 'input[name=blog_media_subdir]' ).val();
				break;
			case 'custom':
				url_preview = jQuery( 'input[name=blog_media_url]' ).val();
				switch( '<?php echo $edited_Blog->get_setting( 'http_protocol' ) ?>' )
				{	// Force base URL to http or https for the edited collection:
					case 'always_http':
						url_preview = url_preview.replace( /^https:/, 'http:' );
						break;
					case 'always_https':
						url_preview = url_preview.replace( /^http:/, 'https:' );
						break;
				}
				break;
		}
		jQuery( '#blog_media_url_preview' ).html( url_preview );
	}
	jQuery( 'input[name=blog_media_location]' ).click( function() { update_blog_media_url_preview(); } );
	jQuery( 'input[name=blog_media_subdir], input[name=blog_media_url]' ).keyup( function() { update_blog_media_url_preview(); } );
</script>