<?php
/**
 * This is the site header include template.
 *
 * If enabled, this will be included at the top of all skins to provide a common identity and site wide navigation.
 * NOTE: each skin is responsible for calling siteskin_include( '_site_body_header.inc.php' );
 *
 * @package foyer
 * @subpackage custom_site_skin
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

global $baseurl, $Settings, $Blog, $disp, $current_User, $site_Skin;

$notification_logo_file_ID = intval( $Settings->get( 'notification_logo_file_ID' ) );
if( $notification_logo_file_ID > 0 &&
    ( $FileCache = & get_FileCache() ) &&
    ( $File = $FileCache->get_by_ID( $notification_logo_file_ID, false ) ) &&
    $File->is_image() )
{	// Display site logo image if the file exists in DB and it is an image:
	$site_title = $Settings->get( 'notification_long_name' ) != '' ? ' title="'.$Settings->dget( 'notification_long_name', 'htmlattr' ).'"' : '';
	$site_name_text = '<img src="'.$File->get_url().'" alt="'.$Settings->dget( 'notification_short_name', 'htmlattr' ).'"'.$site_title.' />';
	$site_title_class = ' swhead_logo';
	$site_has_logo_file = true;
}
else
{	// Display only short site name if the logo file cannot be used by some reason above:
	$site_name_text = $Settings->get( 'notification_short_name' );
	$site_title_class = '';
	$site_has_logo_file = false;
}
?>

<div class="swhead_wrapper">

		<?php if( $site_has_logo_file ) { ?>
			<div class="swhead_sitename<?php echo $site_title_class; ?>">
				<a href="<?php echo $baseurl; ?>"><?php echo $site_name_text; ?></a>
			</div>
		<?php } ?>
	<div class="swhead_menus">
		<div class="container-fluid level1">

			<nav>
				<div class="pull-right">
	<?php
		// Optional display params for widgets below
		$right_menu_params = array(
				'block_start' => '',
				'block_end' => '',
				'block_display_title' => false,
				'list_start' => '',
				'list_end' => '',
				'item_start' => '',
				'item_end' => '',
				'item_selected_start' => '',
				'item_selected_end' => '',
				'link_selected_class' => 'btn btn-default active btn-sm ',
				'link_default_class' => 'btn btn-default btn-sm ',
			);

		if( is_logged_in() )
		{ // Display the following menus when current user is logged in

			// Profile link:
			// Call widget directly (without container):
			skin_widget( array_merge( $right_menu_params, array(
				// CODE for the widget:
				'widget' => 'profile_menu_link',
				// Optional display params
				'profile_picture_size' => 'crop-top-32x32',
			) ) );

			// Messaging link:
			// Call widget directly (without container):
			skin_widget( array_merge( $right_menu_params, array(
				// CODE for the widget:
				'widget' => 'msg_menu_link',
				// Optional display params
				'link_type' => 'messages',
			) ) );

			// Logout link:
			// Call widget directly (without container):
			skin_widget( array_merge( $right_menu_params, array(
				// CODE for the widget:
				'widget' => 'basic_menu_link',
				// Optional display params
				'link_type' => 'logout',
			) ) );
		}
		else
		{ // Display the following menus when current user is NOT logged in

			// Login link:
			// Call widget directly (without container):
			skin_widget( array_merge( $right_menu_params, array(
				// CODE for the widget:
				'widget' => 'basic_menu_link',
				// Optional display params
				'link_type' => 'login',
			) ) );

			// Register link:
			// Call widget directly (without container):
			skin_widget( array_merge( $right_menu_params, array(
				// CODE for the widget:
				'widget' => 'basic_menu_link',
				// Optional display params
				'link_type' => 'register',
				'link_selected_class' => $right_menu_params['link_selected_class'],
				'link_default_class' => $right_menu_params['link_default_class'],
			) ) );
		}
	?>
				</div>

				<ul class="nav nav-tabs pull-left">
<?php
				if( ! $site_has_logo_file )
				{	// Display site name:
?>
					<li class="swhead_sitename no_logo<?php echo $site_title_class; ?>">
						<a href="<?php echo $baseurl; ?>"><?php echo $site_name_text; ?></a>
					</li>
<?php
				}

			if( $site_Skin->get_setting( 'grouping' ) )
			{	// Display the grouped header tabs:
				$header_tabs = $site_Skin->get_header_tabs();

				foreach( $header_tabs as $s => $header_tab )
				{	// Display level 0 tabs:
?>
					<li<?php echo ( $site_Skin->header_tab_active === $s ? ' class="active"' : '' ); ?>>
						<a href="<?php echo $header_tab['url']; ?>"><?php echo $header_tab['name']; ?></a>
					</li>
<?php
				}
			}
			else
			{	// Display not grouped header tabs:

				// --------------------------------- START OF COLLECTION LIST --------------------------------
				// Call widget directly (without container):
				skin_widget( array(
									// CODE for the widget:
									'widget' => 'colls_list_public',
									// Optional display params
									'block_start' => '',
									'block_end' => '',
									'block_display_title' => false,
									'list_start' => '',
									'list_end' => '',
									'item_start' => '<li>',
									'item_end' => '</li>',
									'item_selected_start' => '<li class="active">',
									'item_selected_end' => '</li>',
									'link_selected_class' => 'active',
									'link_default_class' => '',
							) );
				// ---------------------------------- END OF COLLECTION LIST ---------------------------------

				if( $Settings->get( 'info_blog_ID' ) > 0 )
				{	// We have a collection for shared content blocks:
					// --------------------------------- START OF PAGES LIST --------------------------------
					// Call widget directly (without container):
					skin_widget( array(
									// CODE for the widget:
									'widget' => 'coll_page_list',
									// Optional display params
									'block_start' => '',
									'block_end' => '',
									'block_display_title' => false,
									'list_start' => '',
									'list_end' => '',
									'item_start' => '<li>',
									'item_end' => '</li>',
									'item_selected_start' => '<li class="active">',
									'item_selected_end' => '</li>',
									'link_selected_class' => 'active',
									'link_default_class' => '',
									'blog_ID' => $Settings->get( 'info_blog_ID' ),
									'item_group_by' => 'none',
									'order_by' => 'order',		// Order (as explicitly specified)
							) );
					// ---------------------------------- END OF PAGES LIST ---------------------------------
				}

				// --------------------------------- START OF CONTACT LINK --------------------------------
				// Call widget directly (without container):
				skin_widget( array(
									// CODE for the widget:
									'widget' => 'basic_menu_link',
									// Optional display params
									'block_start' => '',
									'block_end' => '',
									'block_display_title' => false,
									'list_start' => '',
									'list_end' => '',
									'item_start' => '<li>',
									'item_end' => '</li>',
									'item_selected_start' => '<li class="active">',
									'item_selected_end' => '</li>',
									'link_selected_class' => 'active',
									'link_default_class' => '',
									'link_type' => 'ownercontact',
							) );
				// --------------------------------- END OF CONTACT LINK --------------------------------
			}
?>
				</ul>
			</nav>

		</div><?php // END OF <div class="container-fluid level1"> ?>

<?php
if( $site_Skin->get_setting( 'grouping' ) &&
    isset( $header_tabs[ $site_Skin->header_tab_active ]['items'] ) &&
    count( $header_tabs[ $site_Skin->header_tab_active ]['items'] ) > 1 )
{	// Display sub menus of the selected level 0 tab only when at least two exist:
?>
<div class="container-fluid level2">
	<nav>
		<ul class="nav nav-pills">
<?php
	foreach( $header_tabs[ $site_Skin->header_tab_active ]['items'] as $menu_item )
	{
		if( is_array( $menu_item ) )
		{	// Display menu item for collection:
?>
			<li<?php echo ( $menu_item['active'] ? ' class="active"' : '' ); ?>>
				<a href="<?php echo $menu_item['url']; ?>"><?php echo $menu_item['name']; ?></a>
			</li>
<?php
		}
		elseif( $menu_item == 'pages' )
		{	// Display menu item for Pages of the info/shared collection:
			// --------------------------------- START OF PAGES LIST --------------------------------
			// Call widget directly (without container):
			skin_widget( array(
							// CODE for the widget:
							'widget' => 'coll_page_list',
							// Optional display params
							'block_start' => '',
							'block_end' => '',
							'block_display_title' => false,
							'list_start' => '',
							'list_end' => '',
							'item_start' => '<li>',
							'item_end' => '</li>',
							'item_selected_start' => '<li class="active">',
							'item_selected_end' => '</li>',
							'blog_ID' => $Settings->get( 'info_blog_ID' ),
							'item_group_by' => 'none',
							'order_by' => 'order',		// Order (as explicitly specified)
					) );
			// ---------------------------------- END OF PAGES LIST ---------------------------------
		}
	}
?>
		</ul>
	</nav>
</div><?php // END OF <div class="container-fluid level2"> ?>
<?php
}
?>

	</div><?php // END OF <div class="swhead_menus"> ?>
</div><?php // END OF <div class="swhead_wrapper"> ?>

<?php if( $site_Skin->get_setting( 'back_to_top_button' ) )
{ // Check if "Back to Top" button is enabled
?>
<a href="#" class="btn btn-primary slide-top<?php echo ( is_logged_in() ? ' logged_in_margin_top' : '' ); ?>"><i class="fa fa-angle-double-up"></i></a>

<script type="text/javascript">
	// Scroll to Top
	// ======================================================================== /
	// browser window scroll ( in pixels ) after which the "scroll to top" link is show
	var offset = 400,
	// browser window scroll (in pixels) after which the "scroll to top" link opacity is reduced
	offset_opacity = 1200,
	// duration of the top scrolling animatiion (in ms)
	scroll_top_duration = 700,
	// grab the "back to top" link
	$slide_top = jQuery( '.slide-top' );
	
	// hide or show the "scroll to top" link
	jQuery( window ).scroll( function()
	{
		( jQuery( this ).scrollTop() > offset ) ? $slide_top.addClass( 'slide-top-visible' ) : $slide_top.removeClass( 'slide-top-visible' );
	});

	// Smooth scroll to top
	$slide_top.on( 'click', function(event)
	{
		event.preventDefault();
		jQuery( 'body, html' ).animate(
		{
			scrollTop: 0,
		}, scroll_top_duration );
	} );
</script>
<?php } ?>