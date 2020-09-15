<?php
/**
 * This file implements the UI for file viewing.
 *
 * This file is part of the evoCore framework - {@link http://evocore.net/}
 * See also {@link https://github.com/b2evolution/b2evolution}.
 *
 * @license GNU GPL v2 - {@link http://b2evolution.net/about/gnu-gpl-license}
 *
 * @copyright (c)2003-2018 by Francois Planque - {@link http://fplanque.com/}
 * Parts of this file are copyright (c)2004-2006 by Daniel HAHLER - {@link http://thequod.de/contact}.
 *
 * @package admin
 *
 * @todo skin compliant header!
 */

/**
 * Load config, init and get the {@link $mode mode param}.
 */
require_once dirname(__FILE__).'/../conf/_config.php';
require_once $inc_path.'/_main.inc.php';


if( ! isset($GLOBALS['files_Module']) )
{
	debug_die( 'Files module is disabled or missing!' );
}

// Check permission (#1):
if( ! isset($current_User) )
{
	debug_die( 'No permissions to view file (not logged in)!' );
}

// We need this param early to check blog perms, if possible
param( 'root', 'string', true, true ); // the root directory from the dropdown box (user_X or blog_X; X is ID - 'user' for current user (default))
if( preg_match( '/^collection_(\d+)$/', $root, $perm_blog ) )
{	// OK, we got a blog ID:
	$perm_blog = $perm_blog[1];
}
else
{	// No blog ID, we will check the global group perm
	$perm_blog = NULL;
}
//pre_dump( $perm_blog );

// Check permission (#2):
$current_User->check_perm( 'files', 'view', true, $perm_blog );

// Initialize Font Awesome
init_fontawesome_icons();

// Load the other params:
param( 'viewtype', 'string', true, true );
param( 'path', 'filepath', true, true );

// Load fileroot infos
$FileRootCache = & get_FileRootCache();
$FileRoot = & $FileRootCache->get_by_ID( $root );

// Create file object
$selected_File = new File( $FileRoot->type , $FileRoot->in_type_ID, $path, true );

$action = param_action();
switch( $action )
{
	case 'rotate_90_left':
	case 'rotate_180':
	case 'rotate_90_right':
		// Check that this action request is not a CSRF hacked request:
		$Session->assert_received_crumb( 'image' );

		load_funcs( 'files/model/_image.funcs.php' );

		switch( $action )
		{
			case 'rotate_90_left':
				$degrees = 90;
				break;
			case 'rotate_180':
				$degrees = 180;
				break;
			case 'rotate_90_right':
				$degrees = 270;
				break;
		}

		if( rotate_image( $selected_File, $degrees ) )
		{	// Image was rotated successfully
			header_redirect( regenerate_url( 'action,crumb_image', 'action=reload_parent', '', '&' ) );
		}
		break;

	case 'flip_horizontal':
	case 'flip_vertical':
		// Check that this action request is not a CSRF hacked request:
		$Session->assert_received_crumb( 'image' );

		load_funcs( 'files/model/_image.funcs.php' );

		switch( $action )
		{
			case 'flip_horizontal':
				$mode = 'horizontal';
				break;
			case 'flip_vertical':
				$mode = 'vertical';
				break;
		}

		if( flip_image( $selected_File, $mode ) )
		{	// Image was rotated successfully
			header_redirect( regenerate_url( 'action,crumb_image', 'action=reload_parent', '', '&' ) );
		}
		break;

	case 'reload_parent':
		// Reload parent window to update rotated image
		$JS_additional = 'window.opener.location.reload(true);';
		break;
}

// Add CSS:
require_css( 'basic_styles.css', 'rsc_url' ); // the REAL basic styles
require_css( 'basic.css', 'rsc_url' ); // Basic styles
require_css( 'viewfile.css', 'rsc_url' );
require_css( '#bootstrap_css#', 'rsc_url' );
require_css( '#tui_color_picker_css#', 'rsc_url' );
require_css( '#tui_image_editor_css#', 'rsc_url' );


// Add JS:
global $rsc_url;
add_js_headline('var rsc_url = \''.format_to_js( $rsc_url ).'\';' );
require_js( '#jquery#', 'rsc_url' );
require_js( '#fabricjs#', 'rsc_url' );
require_js( '#tui_code_snippet#', 'rsc_url' );
require_js( '#tui_color_picker#', 'rsc_url' );
require_js( '#tui_image_editor#', 'rsc_url' );
require_js( 'toastui/white-theme.js', 'rsc_url' );
require_js( 'backoffice.js', 'rsc_url' );


// Send the predefined cookies:
evo_sendcookies();

headers_content_mightcache( 'text/html' );		// In most situations, you do NOT want to cache dynamic content!
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xml:lang="<?php locale_lang() ?>" lang="<?php locale_lang() ?>">
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<title><?php echo $selected_File->dget('name').' ('./* TRANS: Noun */ T_('Preview').')'; ?></title>
	<?php include_headlines() /* Add javascript and css files included by plugins and skin */ ?>
<?php if( isset( $JS_additional ) ) { ?>
	<script><?php echo $JS_additional; ?></script>
<?php } ?>
</head>

<body>
	<?php

switch( $viewtype )
{
	case 'image':
		/*
		 * Image file view:
		 */

		$imgSize = $selected_File->get_image_size( 'widthheight' );
		$Filetype = & $selected_File->get_Filetype();
		$FileRoot = & $selected_File->get_FileRoot();

		switch( $Filetype->mimetype )
		{
			case 'image/jpeg':
				$mimetype = 'jpeg';
				break;

			default:
			case 'image/png':
				$mimetype = 'png';
				break;
		}

		// The advanced image editor is allowed only sinve PHP 7:
		$advanced_image_editor_is_allowed = version_compare( phpversion(), '7', '>=' );

		?>
		<div class="editor_toggle">
			<div class="btn-group">
				<button id="advanced_button" class="btn btn-default<?php echo $advanced_image_editor_is_allowed ? ' active' : '' ?>" onclick="toggle_editor('advanced');"><?php echo T_('Advanced edit');?></button>
				<button id="quick_button" class="btn btn-default<?php echo $advanced_image_editor_is_allowed ? '' : ' active' ?>" onclick="toggle_editor('quick');"><?php echo T_('Quick edit');?></button>
			</div>
				<script>
					function toggle_editor( mode )
					{
						var advanced_editor = jQuery( '#advanced_editor' ),
								advanced_button = jQuery( '#advanced_button' ),
								quick_editor = jQuery( '#quick_editor' ),
								quick_button = jQuery( '#quick_button' );

						switch( mode )
						{
							case 'advanced':
								advanced_editor.show();
								advanced_button.addClass( 'active' );
								quick_editor.hide();
								quick_button.removeClass( 'active' );
								reloadImage( 'advanced' );
								break;

							case 'quick':
								advanced_editor.hide();
								advanced_button.removeClass( 'active' );
								quick_editor.show();
								quick_button.addClass( 'active' );
								reloadImage( 'quick' );
								break;
						}
					}
				</script>
		</div>
		<div id="advanced_editor"<?php echo $advanced_image_editor_is_allowed ? '' : ' style="display:none"' ?>>
			<?php if( $advanced_image_editor_is_allowed )
			{	// Initialize advanced image editor only on PHP >= 7:
			?>
			<div id="image-editor"></div>
			<script>
			var saveInProgress = false;
			var ImageEditor = tui.ImageEditor;
			var instance = new ImageEditor('#image-editor', {
					includeUI: {
						loadImage: {
							path: '<?php echo format_to_js( $selected_File->get_url() );?>',
							name: '<?php echo format_to_js( $selected_File->get_name() );?>',
						},
						theme: whiteTheme,
						menuBarPosition: 'bottom',
						menu: ['crop', 'flip', 'rotate', 'draw', 'shape', 'text', 'mask', 'filter'],
					},
					cssMaxWidth: window.innerWidth - 100,
					cssMaxHeight: window.innerHeight - 200,
					selectionStyle: {
						cornerSize: 20,
						rotatingPointOffset: 70
					}
				});

			// This is a simple way to extend the interface and add a save button
			document.querySelector('.tui-image-editor-header-buttons .tui-image-editor-save-btn').addEventListener('click', this.saveImage);

			function reloadImage( mode )
			{
				switch( mode )
				{
					case 'advanced':
						instance.loadImageFromURL( '<?php echo format_to_js( $selected_File->get_url() );?>', '<?php echo format_to_js( $selected_File->get_name() );?>' );
						break;

					case 'quick':
						$request = jQuery.ajax( {
									type: 'GET',
									url: '<?php echo format_to_js( get_htsrv_url().'async.php' );?>',
									data: {
										action: 'reload_image',
										root: '<?php echo format_to_js( $root );?>',
										path: '<?php echo format_to_js( $path );?>',
										crumb_image: '<?php echo format_to_js( get_crumb('image') );?>',
									}
							} );

						$request.done( function( data ) {
							data = JSON.parse( data );
							if( data.status == 'ok' )
							{
								jQuery( '.img_wrapper' ).html( data.content );
							}
							else if( data.status == 'error' )
							{
								alert( data.error_msg );
							}
						} );
						break;
				}
			}

			function saveImage()
			{
				if( !saveInProgress )
				{
					saveInProgress = true;

					// Set save button style to indicate we are processing the request:
					var saveButton = jQuery( '.tui-image-editor-save-btn' );
					saveButton.removeClass( 'tui-error' );
					saveButton.html( 'Saving...' ).addClass( 'tui-processing' );

					// Generate the image data
					var imgData = instance.toDataURL( { format: '<?php echo format_to_js( $mimetype );?>' } );
					imgData = dataURItoBlob( imgData );

					var fd = new FormData();
					fd.append( 'action', 'save_image' );
					fd.append( 'crumb_image', '<?php echo format_to_js( get_crumb( 'image' ) );?>' );
					fd.append( 'root', '<?php echo format_to_js( $FileRoot->type.'_'.$FileRoot->in_type_ID );?>' );
					fd.append( 'path', '<?php echo format_to_js( $selected_File->get_rdfp_rel_path() );?>' );
					fd.append( 'qquuid', '<?php echo format_to_js( bin2hex( random_bytes( 16 ) ) );?>' ); // Just random stuff but we need this for the UploadHandler to work
					fd.append( 'image_data', imgData );

					// Send image data to Server
					$request = jQuery.ajax( {
								type: 'POST',
								url: '<?php echo format_to_js( get_htsrv_url().'async.php' );?>',
								data: fd,
								processData: false,
								contentType: false,
						} );

					// Response received
					$request.done( function( data ) {
							saveInProgress = false;
							data = JSON.parse( data );
							if( data.status == 'ok' )
							{
								saveButton.html( 'Save OK' );
								setTimeout( function() {
									saveButton.html( 'Save' );
									saveButton.removeClass( 'tui-processing' );
								}, 3000 );
							}
							else if( data.status == 'error' )
							{
								saveButton.removeClass( 'tui-processing' );
								saveButton.addClass( 'tui-error' );
								saveButton.html( 'Save Error' );
								setTimeout( function() {
									saveButton.html( 'Save' );
								}, 3000 );
								if( data.error_msg )
								{
									alert( data.error_msg );
								}
								else
								{
									alert( 'An unknown error has occurred while trying to upload the image.' );
								}
							}
						} );
				}
			}
			</script>
			<?php
			}
			else
			{	// Display error when adnvanced image editor is not allowed:
				echo '<p class="alert alert-danger" style="margin:56px 15px 0 15px">'.T_('This feature requires PHP 7.').'</p>';
			}
			?>
		</div>

		<div id="quick_editor"<?php echo $advanced_image_editor_is_allowed ? ' style="display:none"' : '' ?>>
			<div class="img_preview content-type-image">

			<?php
			if( $imgSize = $selected_File->get_image_size( 'widthheight' ) )
			{
				echo '<div class="img_wrapper">';
				echo '<img ';
				if( $alt = $selected_File->dget( 'alt', 'htmlattr' ) )
				{
					echo 'alt="'.$alt.'" ';
				}
				if( $title = $selected_File->dget( 'title', 'htmlattr' ) )
				{
					echo 'title="'.$title.'" ';
				}
				echo 'src="'.$selected_File->get_url().'"'
							.' width="'.$imgSize[0].'" height="'.$imgSize[1].'" />';
				echo '</div>';

				$url_rotate_90_left = regenerate_url( '', 'action=rotate_90_left'.'&'.url_crumb('image') );
				$url_rotate_180 = regenerate_url( '', 'action=rotate_180'.'&'.url_crumb('image') );
				$url_rotate_90_right = regenerate_url( '', 'action=rotate_90_right'.'&'.url_crumb('image') );
				$url_flip_horizontal = regenerate_url( '', 'action=flip_horizontal'.'&'.url_crumb('image') );
				$url_flip_vertical = regenerate_url( '', 'action=flip_vertical'.'&'.url_crumb('image') );

				echo '<div class="center">';
				echo action_icon( T_('Rotate this picture 90&deg; to the left'), 'rotate_left', $url_rotate_90_left, '', 0, 0, array( 'style' => 'margin-right:4px', 'onclick' => 'return quick_edit(\'rotate_90_left\');' ) );
				echo action_icon( T_('Rotate this picture 180&deg;'), 'rotate_180', $url_rotate_180, '', 0, 0, array( 'style' => 'margin-right:4px', 'onclick' => 'return quick_edit(\'rotate_180\');' ) );
				echo action_icon( T_('Rotate this picture 90&deg; to the right'), 'rotate_right', $url_rotate_90_right, '', 0, 0, array( 'style' => 'margin-right:4px', 'onclick' => 'return quick_edit(\'rotate_90_right\');' ) );
				echo action_icon( T_('Flip this picture horizontally'), 'flip_horizontal', $url_flip_horizontal, '', 0, 0, array( 'style' => 'margin-right:4px', 'onclick' => 'return quick_edit(\'flip_horizontal\');' ) );
				echo action_icon( T_('Flip this picture vertically'), 'flip_vertical', $url_flip_vertical, '', 0, 0, array( 'onclick' => 'return quick_edit(\'flip_vertical\');') );
				echo '</div>';

				echo '<div class="subline">';
				echo '<p><strong>'.$selected_File->dget( 'title' ).'</strong></p>';
				echo '<p>'.$selected_File->dget( 'desc' ).'</p>';
				echo '<p>'.$selected_File->dget('name').' &middot; ';
				echo $selected_File->get_image_size().' &middot; ';
				echo $selected_File->get_size_formatted().'</p>';
				echo '</div>';

				?>
				<script>
				var editInProgress = false;
				function quick_edit(action)
				{
					if( ! editInProgress )
					{
						editInProgress = true;
						$request = jQuery.ajax( {
									type: 'POST',
									url: '<?php echo format_to_js( get_htsrv_url().'async.php' );?>',
									data: {
										action: action,
										root: '<?php echo format_to_js( $root );?>',
										path: '<?php echo format_to_js( $path );?>',
										crumb_image: '<?php echo format_to_js( get_crumb('image') );?>',
									}
							} );

							$request.done( function( data ) {
								editInProgress = false;
								data = JSON.parse( data );
								if( data.status == 'ok' )
								{
									jQuery( '.img_wrapper' ).html( data.content );
								}
								else if( data.status == 'error' )
								{
									alert( data.error_msg );
								}
							} );
					}

					return false;
				}
				</script>
				<?php
			}
			else
			{
				echo 'error';
			}
			?>
			</div>
		</div>
		<?php
		break;

	case 'text':
		echo '<div class="content-type-text">';
 		/*
		 * Text file view:
		 */
		if( ( $buffer = @file( $selected_File->get_full_path() ) ) !== false )
		{ // Display raw file
			param( 'showlinenrs', 'integer', 0 );

			$buffer_lines = count( $buffer );

			echo '<div class="fileheader">';

			echo '<p>';
			echo T_('File').': <strong>'.$selected_File->dget('name').'</strong>';
			echo ' &middot; ';
			echo T_('Title').': <strong>'.$selected_File->dget( 'title' ).'</strong>';
			echo '</p>';

	 		echo '<p>';
			echo T_('Description').': '.$selected_File->dget( 'desc' );
			echo '</p>';


			if( !$buffer_lines )
			{
				echo '<p>** '.T_('Empty file').'! ** </p></div>';
			}
			else
			{
				echo '<p>';
				printf( T_('%d lines'), $buffer_lines );

				$linenr_width = strlen( $buffer_lines+1 );

				echo ' [';
				?>
				<noscript>
					<a href="<?php echo $selected_File->get_url().'&amp;showlinenrs='.(1-$showlinenrs); ?>">

					<?php echo $showlinenrs ? T_('Hide line numbers') : T_('Show line numbers');
					?></a>
				</noscript>
				<script>
					<!--
					document.write('<a id="togglelinenrs" href="javascript:toggle_linenrs()">toggle</a>');

					showlinenrs = <?php var_export( !$showlinenrs ); ?>;

					toggle_linenrs();

					function toggle_linenrs()
					{
						if( showlinenrs )
						{
							var replace = document.createTextNode('<?php echo TS_('Show line numbers') ?>');
							showlinenrs = false;
							var text = document.createTextNode( '' );
							for( var i = 0; i<document.getElementsByTagName("span").length; i++ )
							{
								if( document.getElementsByTagName("span")[i].hasChildNodes() )
									document.getElementsByTagName("span")[i].firstChild.data = '';
								else
								{
									document.getElementsByTagName("span")[i].appendChild( text );
								}
							}
						}
						else
						{
							var replace = document.createTextNode('<?php echo TS_('Hide line numbers') ?>');
							showlinenrs = true;
							for( var i = 0; i<document.getElementsByTagName("span").length; i++ )
							{
								var text = String(i+1);
								var upto = <?php echo $linenr_width ?>-text.length;
								for( var j=0; j<upto; j++ ){ text = ' '+text; }
								if( document.getElementsByTagName("span")[i].hasChildNodes() )
									document.getElementsByTagName("span")[i].firstChild.data = ' '+text+' ';
								else
									document.getElementsByTagName("span")[i].appendChild( document.createTextNode( ' '+text+' ' ) );
							}
						}

						document.getElementById('togglelinenrs').replaceChild(replace, document.getElementById( 'togglelinenrs' ).firstChild);
					}
					-->
				</script>
				<?php

				echo ']</p>';
				echo '</div>';

				echo '<pre class="rawcontent">';

				for( $i = 0; $i < $buffer_lines; $i++ )
				{
					echo '<span name="linenr" class="linenr">';
					if( $showlinenrs )
					{
						echo ' '.str_pad($i+1, $linenr_width, ' ', STR_PAD_LEFT).' ';
					}
					echo '</span>'.htmlspecialchars( str_replace( "\t", '  ', $buffer[$i] ) );  // TODO: customize tab-width
				}

	  		echo '</pre>';

				echo '<div class="eof">** '.T_('End Of File').' **</div>';
			}
		}
		else
		{
			echo '<p class="error">'.sprintf( T_('The file &laquo;%s&raquo; could not be accessed!'), $selected_File->get_rdfs_rel_path( $selected_File ) ).'</p>';
		}
		echo '</div>';
		break;

	default:
		echo '<p class="error">'.sprintf( T_('The file &laquo;%s&raquo; could not be accessed!'), $selected_File->dget('name') ).'</p>';
		break;
}
?>

</body>
</html>