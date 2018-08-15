<?php
/**
 * This file implements additional functional for widgets.
 *
 * This file is part of the b2evolution/evocms project - {@link http://b2evolution.net/}.
 * See also {@link https://github.com/b2evolution/b2evolution}.
 *
 * @license GNU GPL v2 - {@link http://b2evolution.net/about/gnu-gpl-license}
 *
 * @copyright (c)2003-2018 by Francois Planque - {@link http://fplanque.com/}.
 * Parts of this file are copyright (c)2004-2005 by Daniel HAHLER - {@link http://thequod.de/contact}.
 *
 * @package evocore
 *
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 *
 */
if( !defined('EVO_CONFIG_LOADED') ) die( 'Please, do not access this page directly.' );


/**
 * Add a widget to global array in order to insert it in DB by single SQL query later
 *
 * @param integer Container ID
 * @param string Type
 * @param string Code
 * @param integer Order
 * @param array|string|NULL Widget params
 * @param integer 1 - enabled, 0 - disabled
 */
function add_basic_widget( $container_ID, $code, $type, $order, $params = NULL, $enabled = 1 )
{
	global $basic_widgets_insert_sql_rows, $DB;

	if( is_null( $params ) )
	{ // NULL
		$params = 'NULL';
	}
	elseif( is_array( $params ) )
	{ // array
		$params = $DB->quote( serialize( $params ) );
	}
	else
	{ // string
		$params = $DB->quote( $params );
	}

	$basic_widgets_insert_sql_rows[] = '( '
		.$container_ID.', '
		.$order.', '
		.$enabled.', '
		.$DB->quote( $type ).', '
		.$DB->quote( $code ).', '
		.$params.' )';
}


/**
 * Insert the basic widgets for a collection
 *
 * @param integer should never be 0
 * @param array the list of skin ids which are set for the given blog ( normal, mobile and tablet skin ids )
 * @param boolean should be true only when it's called after initial install
 * fp> TODO: $initial_install is used to know if we want to trust globals like $blog_photoblog_ID and $blog_forums_ID. We don't want that.
 *           We should pass a $context array with values like 'photo_source_coll_ID' => 4.
 *           Also, checking $blog_forums_ID is unnecessary complexity. We can check the colleciton kind == forum
 * @param string Kind of blog ( 'std', 'photo', 'group', 'forum' )
 */
function insert_basic_widgets( $blog_id, $skin_ids, $initial_install = false, $kind = '' )
{
	global $DB, $install_test_features, $basic_widgets_insert_sql_rows;

	// Initialize this array first time and clear after previous call of this function
	$basic_widgets_insert_sql_rows = array();

	// Load skin functions needed to get the skin containers
	load_funcs( 'skins/_skin.funcs.php' );

	// Handle all blog IDs which can go from function create_demo_contents()
	global $blog_home_ID, $blog_a_ID, $blog_b_ID, $blog_photoblog_ID, $blog_forums_ID, $blog_manual_ID, $events_blog_ID;
	$blog_home_ID = intval( $blog_home_ID );
	$blog_a_ID = intval( $blog_a_ID );
	$blog_b_ID = intval( $blog_b_ID );
	$blog_photoblog_ID = intval( $blog_photoblog_ID );
	$blog_forums_ID = intval( $blog_forums_ID );
	$blog_manual_ID = intval( $blog_manual_ID );
	$events_blog_ID = intval( $events_blog_ID );

	// Get all containers declared in the given blog's skins
	$blog_containers = get_skin_containers( $skin_ids );

	// Additional sub containers:
	$blog_containers['front_page_column_a'] = array( 'Front Page Column A', 1, 0 );
	$blog_containers['front_page_column_b'] = array( 'Front Page Column B', 2, 0 );
	$blog_containers['user_page_reputation'] = array( 'User Page - Reputation', 100, 0 );
	if( $kind == 'catalog' )
	{
		$blog_containers['product_page_column_a'] = array( 'Product Page Column A', 10, 0 );
		$blog_containers['product_page_column_b'] = array( 'Product Page Column B', 11, 0 );
	}

	// Create rows to insert for all collection containers:
	$widget_containers_sql_rows = array();
	foreach( $blog_containers as $wico_code => $wico_data )
	{
		$widget_containers_sql_rows[] = '( "'.$wico_code.'", "'.$wico_data[0].'", '.$blog_id.', '.$wico_data[1].', '.( isset( $wico_data[2] ) ? intval( $wico_data[2] ) : '1' ).' )';
	}

	// Insert widget containers records by one SQL query
	$DB->query( 'INSERT INTO T_widget__container( wico_code, wico_name, wico_coll_ID, wico_order, wico_main ) VALUES'
		.implode( ', ', $widget_containers_sql_rows ) );

	$insert_id = $DB->insert_id;
	foreach( $blog_containers as $wico_code => $wico_data )
	{
		$blog_containers[ $wico_code ]['wico_ID'] = $insert_id;
		$insert_id++;
	}

	// Init insert widget query and default params
	$default_blog_param = 's:7:"blog_ID";s:0:"";';
	if( $initial_install && ! empty( $blog_photoblog_ID ) )
	{ // In the case of initial install, we grab photos out of the photoblog (Blog #4)
		$default_blog_param = 's:7:"blog_ID";s:1:"'.intval( $blog_photoblog_ID ).'";';
	}


	/* Header */
	if( array_key_exists( 'header', $blog_containers ) )
	{
		$wico_id = $blog_containers['header']['wico_ID'];
		add_basic_widget( $wico_id, 'coll_title', 'core', 1 );
		add_basic_widget( $wico_id, 'coll_tagline', 'core', 2 );
	}


	/* Menu */
	if( array_key_exists( 'menu', $blog_containers ) )
	{
		$wico_id = $blog_containers['menu']['wico_ID'];
		if( $kind != 'main' )
		{ // Don't add widgets to Menu container for Main collections
			// Home page
			add_basic_widget( $wico_id, 'basic_menu_link', 'core', 5, array( 'link_type' => 'home' ) );
			if( $blog_id == $blog_b_ID )
			{ // Recent Posts
				add_basic_widget( $wico_id, 'basic_menu_link', 'core', 10, array( 'link_type' => 'recentposts', 'link_text' => T_('News') ) );
			}
			if( $kind == 'forum' )
			{ // Latest Topics and Replies ONLY for forum
				add_basic_widget( $wico_id, 'basic_menu_link', 'core', 13, array( 'link_type' => 'recentposts', 'link_text' => T_('Latest topics') ) );
				add_basic_widget( $wico_id, 'basic_menu_link', 'core', 15, array( 'link_type' => 'latestcomments', 'link_text' => T_('Latest replies') ) );
			}
			if( $kind == 'manual' )
			{ // Latest Topics and Replies ONLY for forum
				add_basic_widget( $wico_id, 'basic_menu_link', 'core', 13, array( 'link_type' => 'recentposts', 'link_text' => T_('Latest pages') ) );
				add_basic_widget( $wico_id, 'basic_menu_link', 'core', 15, array( 'link_type' => 'latestcomments', 'link_text' => T_('Latest comments') ) );
			}
			if( $kind == 'forum' || $kind == 'manual' )
			{	// Add menu with flagged items:
				add_basic_widget( $wico_id, 'flag_menu_link', 'core', 17, array( 'link_text' => ( $kind == 'forum' ) ? T_('Flagged topics') : T_('Flagged pages') ) );
			}
			if( $kind == 'photo' )
			{ // Add menu with Photo index
				add_basic_widget( $wico_id, 'basic_menu_link', 'core', 18, array( 'link_type' => 'mediaidx', 'link_text' => T_('Index') ) );
			}
			if( $kind == 'forum' )
			{ // Add menu with User Directory and Profile Visits ONLY for forum
				add_basic_widget( $wico_id, 'basic_menu_link', 'core', 20, array( 'link_type' => 'users' ) );
				add_basic_widget( $wico_id, 'basic_menu_link', 'core', 21, array( 'link_type' => 'users' ) );
			}
			// Pages list:
			add_basic_widget( $wico_id, 'coll_page_list', 'core', 25 );
			if( $kind == 'forum' )
			{ // My Profile
				add_basic_widget( $wico_id, 'basic_menu_link', 'core', 30, array( 'link_type' => 'myprofile' ), 0 );
			}
			if( $kind == 'std' )
			{ // Categories
				add_basic_widget( $wico_id, 'basic_menu_link', 'core', 33, array( 'link_type' => 'catdir' ) );
				// Archives
				add_basic_widget( $wico_id, 'basic_menu_link', 'core', 35, array( 'link_type' => 'arcdir' ) );
				// Latest comments
				add_basic_widget( $wico_id, 'basic_menu_link', 'core', 37, array( 'link_type' => 'latestcomments' ) );
			}
			add_basic_widget( $wico_id, 'msg_menu_link', 'core', 50, array( 'link_type' => 'messages' ), 0 );
			add_basic_widget( $wico_id, 'msg_menu_link', 'core', 60, array( 'link_type' => 'contacts', 'show_badge' => 0 ), 0 );
			add_basic_widget( $wico_id, 'basic_menu_link', 'core', 70, array( 'link_type' => 'login' ), 0 );
			if( $kind == 'forum' )
			{ // Register
				add_basic_widget( $wico_id, 'basic_menu_link', 'core', 80, array( 'link_type' => 'register' ) );
			}
		}
	}

	/* Item List */
	if( array_key_exists( 'item_list', $blog_containers ) )
	{
		$wico_id = $blog_containers['item_list']['wico_ID'];
		add_basic_widget( $wico_id, 'coll_item_list_pages', 'core', 10 );
	}

	/* Item in List */
	if( array_key_exists( 'item_in_list', $blog_containers ) )
	{
		$wico_id = $blog_containers['item_in_list']['wico_ID'];
		add_basic_widget( $wico_id, 'item_title', 'core', 10 );
		add_basic_widget( $wico_id, 'item_visibility_badge', 'core', 20 );
		add_basic_widget( $wico_id, 'item_info_line', 'core', 30 );
	}

	/* Item Single Header */
	if( array_key_exists( 'item_single_header', $blog_containers ) )
	{
		$wico_id = $blog_containers['item_single_header']['wico_ID'];
		if( $kind != 'manual' )
		{
			add_basic_widget( $wico_id, 'item_next_previous', 'core', 4 );
		}
		add_basic_widget( $wico_id, 'item_title', 'core', 5 );
		if( in_array( $kind, array( 'forum', 'group' ) ) )
		{
			add_basic_widget( $wico_id, 'item_info_line', 'core', 10, 'a:14:{s:5:"title";s:0:"";s:9:"flag_icon";i:1;s:14:"permalink_icon";i:0;s:13:"before_author";s:10:"started_by";s:11:"date_format";s:8:"extended";s:9:"post_time";i:1;s:12:"last_touched";i:1;s:8:"category";i:0;s:9:"edit_link";i:0;s:16:"widget_css_class";s:0:"";s:9:"widget_ID";s:0:"";s:16:"allow_blockcache";i:0;s:11:"time_format";s:4:"none";s:12:"display_date";s:12:"date_created";}' );
			add_basic_widget( $wico_id, 'item_tags', 'core', 20 );
			add_basic_widget( $wico_id, 'item_seen_by', 'core', 30 );
		}
		elseif( $kind != 'manual' )
		{
			add_basic_widget( $wico_id, 'item_visibility_badge', 'core', 8 );
			add_basic_widget( $wico_id, 'item_info_line', 'core', 10 );
		}
	}

	/* Item Single */
	if( array_key_exists( 'item_single', $blog_containers ) )
	{
		$wico_id = $blog_containers['item_single']['wico_ID'];
		if( in_array( $kind, array( 'manual', 'catalog' ) ) )
		{
			add_basic_widget( $wico_id, 'item_title', 'core', 5 );
		}
		if( $kind == 'catalog' )
		{
			add_basic_widget( $wico_id, 'subcontainer_row', 'core', 8, array(
				'column1_container' => 'product_page_column_a',
				'column1_class'     => ( 'col-sm-6 col-xs-12' ),
				'column2_container' => 'product_page_column_b',
				'column2_class'     => 'col-sm-6 col-xs-12',
				'widget_css_class'  => 'widget_core_subcontainer_row product_info',
			) );
		}
		add_basic_widget( $wico_id, 'item_content', 'core', 10 );
		if( $kind == 'catalog' )
		{
			add_basic_widget( $wico_id, 'item_custom_fields', 'core', 11, array( 'fields_source' => 'exclude', 'fields' => 'price_usd', 'widget_css_class' => 'product_datasheet' ) );
		}
		add_basic_widget( $wico_id, 'item_attachments', 'core', 15 );
		if( $kind != 'catalog' )
		{ // Item Link
			add_basic_widget( $wico_id, 'item_link', 'core', 17 );
		}
		if( $blog_id != $blog_a_ID && ( empty( $events_blog_ID ) || $blog_id != $events_blog_ID ) && ! in_array( $kind, array( 'forum', 'group', 'catalog' ) ) )
		{ // Item Tags
			add_basic_widget( $wico_id, 'item_tags', 'core', 20 );
		}
		if( $blog_id == $blog_b_ID )
		{ // About Author
			add_basic_widget( $wico_id, 'item_about_author', 'core', 25 );
		}
		if( ( $blog_id == $blog_a_ID || ( ! empty( $events_blog_ID ) && $blog_id == $events_blog_ID ) ) && $install_test_features )
		{ // Google Maps
			add_basic_widget( $wico_id, 'evo_Gmaps', 'plugin', 30 );
		}
		if( $blog_id == $blog_a_ID || $kind == 'manual' )
		{ // Small Print
			add_basic_widget( $wico_id, 'item_small_print', 'core', 40, array( 'format' => ( $blog_id == $blog_a_ID ? 'standard' : 'revision' ) ) );
		}
		if( ! in_array( $kind, array( 'forum', 'group', 'catalog' ) ) )
		{ // Seen by
			add_basic_widget( $wico_id, 'item_seen_by', 'core', 50 );
		}
		if( ! in_array( $kind,  array( 'forum', 'catalog' ) ) )
		{	// Item voting panel:
			add_basic_widget( $wico_id, 'item_vote', 'core', 60 );
		}
	}

	/* Product Page Column A */
	if( array_key_exists( 'product_page_column_a', $blog_containers ) )
	{
		$wico_id = $blog_containers['product_page_column_a']['wico_ID'];
		if( $kind == 'catalog' )
		{
			add_basic_widget( $wico_id, 'item_images', 'core', 10, array( 'display_type' => 'cover_with_fallback', 'image_limit' => 1, 'image_size' => 'fit-480x600', 'widget_css_class' => 'main_product_image' ) );
			add_basic_widget( $wico_id, 'item_images', 'core', 20, array( 'display_type' => 'cover', 'invert_display_type' => 1, 'image_limit' => 20, 'image_size' => 'fit-80x80', 'widget_css_class' => 'product_image_gallery' ) );
		}
	}

	/* Product Page Column B */
	if( array_key_exists( 'product_page_column_b', $blog_containers ) )
	{
		$wico_id = $blog_containers['product_page_column_b']['wico_ID'];
		if( $kind == 'catalog' )
		{
			// Item Excerpt widget
			add_basic_widget( $wico_id, 'item_excerpt', 'core', 5 );
			add_basic_widget( $wico_id, 'item_tags', 'core', 10 );
			add_basic_widget( $wico_id, 'item_vote', 'core', 15 );
			add_basic_widget( $wico_id, 'item_custom_fields', 'core', 20, array( 'fields_source' => 'include', 'fields' => 'price_usd' ) );
			add_basic_widget( $wico_id, 'cart_button', 'core', 25 );
		}
	}

	/* Item Page */
	if( array_key_exists( 'item_page', $blog_containers ) )
	{
		$wico_id = $blog_containers['item_page']['wico_ID'];
		add_basic_widget( $wico_id, 'item_content', 'core', 10 );
		add_basic_widget( $wico_id, 'item_attachments', 'core', 15 );
		add_basic_widget( $wico_id, 'item_seen_by', 'core', 50 );
		add_basic_widget( $wico_id, 'item_vote', 'core', 60 );
	}

	/* Sidebar Single */
	if( $kind == 'forum' )
	{
		if( array_key_exists( 'sidebar_single', $blog_containers ) )
		{
			$wico_id = $blog_containers['sidebar_single']['wico_ID'];
			add_basic_widget( $wico_id, 'coll_related_post_list', 'core', 1 );
		}
	}


	/* Page Top */
	if( array_key_exists( 'page_top', $blog_containers ) )
	{
		$wico_id = $blog_containers['page_top']['wico_ID'];
		add_basic_widget( $wico_id, 'user_links', 'core', 10 );
	}


	/* Sidebar */
	if( array_key_exists( 'sidebar', $blog_containers ) )
	{
		$wico_id = $blog_containers['sidebar']['wico_ID'];
		if( $kind == 'manual' )
		{
			$search_form_params = array( 'title' => T_('Search this manual:') );
			add_basic_widget( $wico_id, 'coll_search_form', 'core', 10, $search_form_params );
			add_basic_widget( $wico_id, 'content_hierarchy', 'core', 20 );
		}
		else
		{
			if( $install_test_features )
			{
				if( $kind != 'forum' && $kind != 'manual' )
				{ // Current filters widget
					add_basic_widget( $wico_id, 'coll_current_filters', 'core', 5 );
				}
				// User login widget
				add_basic_widget( $wico_id, 'user_login', 'core', 10 );
				add_basic_widget( $wico_id, 'user_greetings', 'core', 15 );
			}
			if( ( ! $initial_install || $blog_id != $blog_forums_ID ) && $kind != 'forum' )
			{ // Don't install these Sidebar widgets for blog 'Forums'
				add_basic_widget( $wico_id, 'user_profile_pics', 'core', 20 );
				if( $blog_id > $blog_a_ID )
				{
					add_basic_widget( $wico_id, 'evo_Calr', 'plugin', 30 );
				}
				add_basic_widget( $wico_id, 'coll_longdesc', 'core', 40, array( 'title' => '$title$' ) );
				add_basic_widget( $wico_id, 'coll_search_form', 'core', 50 );
				add_basic_widget( $wico_id, 'coll_category_list', 'core', 60 );

				if( $blog_id == $blog_home_ID )
				{ // Advertisements, Install only for blog #1 home blog
					$advertisement_type_ID = $DB->get_var( 'SELECT ityp_ID FROM T_items__type WHERE ityp_name = "Advertisement"' );
					add_basic_widget( $wico_id, 'coll_item_list', 'core', 70, array(
							'title' => 'Advertisement (Demo)',
							'item_type' => empty( $advertisement_type_ID ) ? '#' : $advertisement_type_ID,
							'blog_ID' => $blog_id,
							'order_by' => 'RAND',
							'limit' => 1,
							'disp_title' => false,
							'item_title_link_type' => 'linkto_url',
							'attached_pics' => 'first',
							'item_pic_link_type' => 'linkto_url',
							'thumb_size' => 'fit-160x160',
						) );
				}

				if( $blog_id != $blog_b_ID )
				{
					add_basic_widget( $wico_id, 'coll_media_index', 'core', 80, 'a:11:{s:5:"title";s:12:"Random photo";s:10:"thumb_size";s:11:"fit-160x120";s:12:"thumb_layout";s:4:"grid";s:12:"grid_nb_cols";s:1:"1";s:5:"limit";s:1:"1";s:8:"order_by";s:4:"RAND";s:9:"order_dir";s:3:"ASC";'.$default_blog_param.'s:11:"widget_name";s:12:"Random photo";s:16:"widget_css_class";s:0:"";s:9:"widget_ID";s:0:"";}' );
				}
				if( ! empty( $blog_home_ID ) && ( $blog_id == $blog_a_ID || $blog_id == $blog_b_ID ) )
				{
					$sidebar_type_ID = $DB->get_var( 'SELECT ityp_ID FROM T_items__type WHERE ityp_name = "Sidebar link"' );
					add_basic_widget( $wico_id, 'coll_item_list', 'core', 90, array(
							'blog_ID'              => $blog_home_ID,
							'item_type'            => empty( $sidebar_type_ID ) ? '#' : $sidebar_type_ID,
							'title'                => 'Linkblog',
							'item_group_by'        => 'chapter',
							'item_title_link_type' => 'auto',
							'item_type_usage'      => 'special',
						) );
				}
			}
			if( $kind == 'forum' )
			{
				add_basic_widget( $wico_id, 'user_avatars', 'core', 90, array(
						'title'           => 'Most Active Users',
						'limit'           => 6,
						'order_by'        => 'numposts',
						'rwd_block_class' => 'col-lg-3 col-md-3 col-sm-4 col-xs-6'
					) );
			}
			add_basic_widget( $wico_id, 'coll_xml_feeds', 'core', 100 );
			add_basic_widget( $wico_id, 'mobile_skin_switcher', 'core', 110 );
		}
	}


	/* Sidebar 2 */
	if( array_key_exists( 'sidebar_2', $blog_containers ) )
	{
		if( $kind != 'forum' )
		{
		$wico_id = $blog_containers['sidebar_2']['wico_ID'];
		add_basic_widget( $wico_id, 'coll_post_list', 'core', 1 );
		if( $blog_id == $blog_b_ID )
		{
			add_basic_widget( $wico_id, 'coll_item_list', 'core', 5, array(
					'title'                => 'Sidebar links',
					'order_by'             => 'RAND',
					'item_title_link_type' => 'auto',
					'item_type_usage'      => 'special',
				) );
		}
		add_basic_widget( $wico_id, 'coll_comment_list', 'core', 10 );
		add_basic_widget( $wico_id, 'coll_media_index', 'core', 15, 'a:11:{s:5:"title";s:13:"Recent photos";s:10:"thumb_size";s:10:"crop-80x80";s:12:"thumb_layout";s:4:"flow";s:12:"grid_nb_cols";s:1:"3";s:5:"limit";s:1:"9";s:8:"order_by";s:9:"datestart";s:9:"order_dir";s:4:"DESC";'.$default_blog_param.'s:11:"widget_name";s:11:"Photo index";s:16:"widget_css_class";s:0:"";s:9:"widget_ID";s:0:"";}' );
		add_basic_widget( $wico_id, 'free_html', 'core', 20, 'a:5:{s:5:"title";s:9:"Sidebar 2";s:7:"content";s:162:"This is the "Sidebar 2" container. You can place any widget you like in here. In the evo toolbar at the top of this page, select "Customize", then "Blog Widgets".";s:11:"widget_name";s:9:"Free HTML";s:16:"widget_css_class";s:0:"";s:9:"widget_ID";s:0:"";}' );
		}
	}


	/* Front Page Main Area */
	if( array_key_exists( 'front_page_main_area', $blog_containers ) )
	{
		$wico_id = $blog_containers['front_page_main_area']['wico_ID'];
		if( $kind == 'main' )
		{ // Display blog title and tagline for main blogs
			add_basic_widget( $wico_id, 'coll_title', 'core', 1 );
			add_basic_widget( $wico_id, 'coll_tagline', 'core', 2 );
		}

		if( $kind == 'main' )
		{ // Hide a title of the front intro post
			$featured_intro_params = array( 'disp_title' => 0 );
		}
		else
		{
			$featured_intro_params = NULL;
		}
		add_basic_widget( $wico_id, 'coll_featured_intro', 'core', 10, $featured_intro_params );
		if( $kind == 'main' )
		{ // Add user links widget only for main kind blogs
			add_basic_widget( $wico_id, 'user_links', 'core', 15 );
		}

		if( $kind == 'main' )
		{ // Display the posts from all other blogs if it is allowed by blogs setting "Collections to aggregate"
			$post_list_params = array(
					'blog_ID'          => '',
					'limit'            => 5,
					'layout'           => 'list',
					'thumb_size'       => 'crop-80x80',
				);
		}
		else
		{
			$post_list_params = NULL;
		}
		add_basic_widget( $wico_id, 'coll_featured_posts', 'core', 20, $post_list_params );

		if( $blog_id == $blog_b_ID )
		{	// Install widget "Poll" only for Blog B on install:
			add_basic_widget( $wico_id, 'poll', 'core', 40, array( 'poll_ID' => 1 ) );
		}

		add_basic_widget( $wico_id, 'subcontainer_row', 'core', 50, array(
				'column1_container' => 'front_page_column_a',
				'column1_class'     => ( $kind == 'main' ? 'col-xs-12' : 'col-sm-6 col-xs-12' ),
				'column2_container' => 'front_page_column_b',
				'column2_class'     => 'col-sm-6 col-xs-12',
			) );
		if( $blog_id == $blog_b_ID )
		{	// Install widget "Poll" only for Blog B on install:
			add_basic_widget( $wico_id, 'poll', 'core', 60, array( 'poll_ID' => 1 ) );
		}
	}


	/* Front Page Column A */
	if( array_key_exists( 'front_page_column_a', $blog_containers ) )
	{
		$wico_id = $blog_containers['front_page_column_a']['wico_ID'];
		add_basic_widget( $wico_id, 'coll_post_list', 'core', 10, array( 'title' => T_('More Posts'), 'featured' => 'other' ) );
	}


	/* Front Page Column B */
	if( array_key_exists( 'front_page_column_b', $blog_containers ) )
	{
		$wico_id = $blog_containers['front_page_column_b']['wico_ID'];
		if( $kind != 'main' )
		{	// Don't install the "Recent Commnets" widget for Main collections:
			add_basic_widget( $wico_id, 'coll_comment_list', 'core', 10 );
		}
	}


	/* Front Page Secondary Area */
	if( array_key_exists( 'front_page_secondary_area', $blog_containers ) )
	{
		$wico_id = $blog_containers['front_page_secondary_area']['wico_ID'];
		if( $kind == 'main' )
		{	// Install the "Organization Members" widget only for Main collections:
			add_basic_widget( $wico_id, 'org_members', 'core', 10 );
		}
		add_basic_widget( $wico_id, 'coll_flagged_list', 'core', 20 );
		if( $kind == 'main' )
		{	// Install the "Content Block" widget only for Main collections:
			add_basic_widget( $wico_id, 'content_block', 'core', 30, array( 'item_slug' => 'this-is-a-content-block' ) );
		}
	}


	/* Forum Front Secondary Area */
	if( array_key_exists( 'forum_front_secondary_area', $blog_containers ) )
	{
		$wico_id = $blog_containers['forum_front_secondary_area']['wico_ID'];
		if( $kind == 'forum' )
		{
			add_basic_widget( $wico_id, 'coll_activity_stats', 'core', 10 );
		}
	}


	/* Compare Main Area */
	if( array_key_exists( 'compare_main_area', $blog_containers ) )
	{
		$wico_id = $blog_containers['compare_main_area']['wico_ID'];
		add_basic_widget( $wico_id, 'item_fields_compare', 'core', 10, array( 'items_source' => 'all' ) );
	}


	/* 404 Page */
	if( array_key_exists( '404_page', $blog_containers ) )
	{
		$wico_id = $blog_containers['404_page']['wico_ID'];
		add_basic_widget( $wico_id, 'page_404_not_found', 'core', 10 );
		add_basic_widget( $wico_id, 'coll_search_form', 'core', 20 );
		add_basic_widget( $wico_id, 'coll_tag_cloud', 'core', 30 );
	}


	/* Login Required */
	if( array_key_exists( 'login_required', $blog_containers ) )
	{
		$wico_id = $blog_containers['login_required']['wico_ID'];
		add_basic_widget( $wico_id, 'content_block', 'core', 10, array( 'item_slug' => 'login-required' ) );
		add_basic_widget( $wico_id, 'user_login', 'core', 20, array(
				'title'               => T_( 'Log in to your account' ),
				'login_button_class'  => 'btn btn-success btn-lg',
				'register_link_class' => 'btn btn-primary btn-lg pull-right',
			) );

	}


	/* Access Denied */
	if( array_key_exists( 'access_denied', $blog_containers ) )
	{
		$wico_id = $blog_containers['access_denied']['wico_ID'];
		add_basic_widget( $wico_id, 'content_block', 'core', 10, array( 'item_slug' => 'access-denied' ) );
	}


	/* Help */
	if( array_key_exists( 'help', $blog_containers ) )
	{
		$wico_id = $blog_containers['help']['wico_ID'];
		add_basic_widget( $wico_id, 'content_block', 'core', 10, array(
				'item_slug' => 'help-content',
				'title'     => T_('Personal Data & Privacy'),
			) );
	}


	/* Register */
	if( array_key_exists( 'register', $blog_containers ) )
	{
		$wico_id = $blog_containers['register']['wico_ID'];
		add_basic_widget( $wico_id, 'user_register_standard', 'core', 10 );
		add_basic_widget( $wico_id, 'content_block', 'core', 20, array( 'item_slug' => 'register-content' ) );
	}


	/* Mobile Footer */
	if( array_key_exists( 'mobile_footer', $blog_containers ) )
	{
		$wico_id = $blog_containers['mobile_footer']['wico_ID'];
		add_basic_widget( $wico_id, 'coll_longdesc', 'core', 10 );
		add_basic_widget( $wico_id, 'mobile_skin_switcher', 'core', 20 );
	}


	/* Mobile Navigation Menu */
	if( array_key_exists( 'mobile_navigation_menu', $blog_containers ) )
	{
		$wico_id = $blog_containers['mobile_navigation_menu']['wico_ID'];
		add_basic_widget( $wico_id, 'coll_page_list', 'core', 10 );
		add_basic_widget( $wico_id, 'basic_menu_link', 'core', 20, array( 'link_type' => 'ownercontact' ) );
		add_basic_widget( $wico_id, 'basic_menu_link', 'core', 30, array( 'link_type' => 'home' ) );
		if( $kind == 'forum' )
		{ // Add menu with User Directory
			add_basic_widget( $wico_id, 'basic_menu_link', 'core', 40, array( 'link_type' => 'users' ) );
		}
	}


	/* Mobile Tools Menu */
	if( array_key_exists( 'mobile_tools_menu', $blog_containers ) )
	{
		$wico_id = $blog_containers['mobile_tools_menu']['wico_ID'];
		add_basic_widget( $wico_id, 'basic_menu_link', 'core', 10, array( 'link_type' => 'login' ) );
		add_basic_widget( $wico_id, 'msg_menu_link', 'core', 20, array( 'link_type' => 'messages' ) );
		add_basic_widget( $wico_id, 'msg_menu_link', 'core', 30, array( 'link_type' => 'contacts', 'show_badge' => 0 ) );
		add_basic_widget( $wico_id, 'basic_menu_link', 'core', 50, array( 'link_type' => 'logout' ) );
	}


	/* User Profile - Left */
	if( array_key_exists( 'user_profile_left', $blog_containers ) )
	{
		$wico_id = $blog_containers['user_profile_left']['wico_ID'];
		// User Profile Picture(s):
		add_basic_widget( $wico_id, 'user_profile_pics', 'core', 10, array(
				'link_to'           => 'fullsize',
				'thumb_size'        => 'crop-top-320x320',
				'anon_thumb_size'   => 'crop-top-320x320-blur-8',
				'anon_overlay_show' => '1',
				'widget_css_class'  => 'evo_user_profile_pics_main',
			) );
		// User info / Name:
		add_basic_widget( $wico_id, 'user_info', 'core', 20, array(
				'info'             => 'name',
				'widget_css_class' => 'evo_user_info_name',
			) );
		// User info / Nickname:
		add_basic_widget( $wico_id, 'user_info', 'core', 30, array(
				'info'             => 'nickname',
				'widget_css_class' => 'evo_user_info_nickname',
			) );
		// User info / Login:
		add_basic_widget( $wico_id, 'user_info', 'core', 40, array(
				'info'             => 'login',
				'widget_css_class' => 'evo_user_info_login',
			) );
		// Separator:
		add_basic_widget( $wico_id, 'separator', 'core', 60 );
		// User info / :
		add_basic_widget( $wico_id, 'user_info', 'core', 70, array(
				'info'             => 'gender_age',
				'widget_css_class' => 'evo_user_info_gender',
			) );
		// User info / Location:
		add_basic_widget( $wico_id, 'user_info', 'core', 80, array(
				'info'             => 'location',
				'widget_css_class' => 'evo_user_info_location',
			) );
		// Separator:
		add_basic_widget( $wico_id, 'separator', 'core', 90 );
		// User action / Edit my profile:
		add_basic_widget( $wico_id, 'user_action', 'core', 100, array(
				'button'           => 'edit_profile',
			) );
		// User action / Send Message:
		add_basic_widget( $wico_id, 'user_action', 'core', 110, array(
				'button'           => 'send_message',
			) );
		// User action / Add to Contacts:
		add_basic_widget( $wico_id, 'user_action', 'core', 120, array(
				'button'           => 'add_contact',
			) );
		// User action / Block Contact & Report User:
		add_basic_widget( $wico_id, 'user_action', 'core', 130, array(
				'button'           => 'block_report',
				'widget_css_class' => 'btn-group',
			) );
		// User action / Edit in Back-Office:
		add_basic_widget( $wico_id, 'user_action', 'core', 140, array(
				'button'           => 'edit_backoffice',
			) );
		// User action / Delete & Delete Spammer:
		add_basic_widget( $wico_id, 'user_action', 'core', 150, array(
				'button'           => 'delete',
				'widget_css_class' => 'btn-group',
			) );
		// Separator:
		add_basic_widget( $wico_id, 'separator', 'core', 160 );
		// User info / Organizations:
		add_basic_widget( $wico_id, 'user_info', 'core', 170, array(
				'info'             => 'orgs',
				'title'            => T_('Organizations').':',
				'widget_css_class' => 'evo_user_info_orgs',
			) );
	}


	/* User Profile - Right */
	if( array_key_exists( 'user_profile_right', $blog_containers ) )
	{
		$wico_id = $blog_containers['user_profile_right']['wico_ID'];
		// User Profile Picture(s):
		add_basic_widget( $wico_id, 'user_profile_pics', 'core', 10, array(
				'display_main'     => 0,
				'display_other'    => 1,
				'link_to'          => 'fullsize',
				'thumb_size'       => 'crop-top-80x80',
				'widget_css_class' => 'evo_user_profile_pics_other',
			) );
		// User fields:
		add_basic_widget( $wico_id, 'user_fields', 'core', 20 );
		// Reputation:
		add_basic_widget( $wico_id, 'subcontainer', 'core', 30, array(
				'title'     => T_('Reputation'),
				'container' => 'user_page_reputation',
			) );
	}

	/* User Page - Reputation */
	if( array_key_exists( 'user_page_reputation', $blog_containers ) )
	{
		$wico_id = $blog_containers['user_page_reputation']['wico_ID'];
		// User info / Joined:
		add_basic_widget( $wico_id, 'user_info', 'core', 10, array(
				'title' => T_('Joined'),
				'info'  => 'joined',
			) );
		// User info / Last Visit:
		add_basic_widget( $wico_id, 'user_info', 'core', 20, array(
				'title' => T_('Last seen on'),
				'info'  => 'last_visit',
			) );
		// User info / Number of posts:
		add_basic_widget( $wico_id, 'user_info', 'core', 30, array(
				'title' => T_('Number of posts'),
				'info'  => 'posts',
			) );
		// User info / Comments:
		add_basic_widget( $wico_id, 'user_info', 'core', 40, array(
				'title' => T_('Comments'),
				'info'  => 'comments',
			) );
		// User info / Photos:
		add_basic_widget( $wico_id, 'user_info', 'core', 50, array(
				'title' => T_('Photos'),
				'info'  => 'photos',
			) );
		// User info / Audio:
		add_basic_widget( $wico_id, 'user_info', 'core', 60, array(
				'title' => T_('Audio'),
				'info'  => 'audio',
			) );
		// User info / Other files:
		add_basic_widget( $wico_id, 'user_info', 'core', 70, array(
				'title' => T_('Other files'),
				'info'  => 'files',
			) );
		// User info / Spam fighter score:
		add_basic_widget( $wico_id, 'user_info', 'core', 80, array(
				'title' => T_('Spam fighter score'),
				'info'  => 'spam',
			) );
	}

	// Check if there are widgets to create
	if( ! empty( $basic_widgets_insert_sql_rows ) )
	{ // Insert the widget records by single SQL query
		$DB->query( 'INSERT INTO T_widget__widget( wi_wico_ID, wi_order, wi_enabled, wi_type, wi_code, wi_params ) '
		           .'VALUES '.implode( ', ', $basic_widgets_insert_sql_rows ) );
	}
}


/**
 * Get WidgetContainer object from the widget list view widget container fieldset id
 * Note: It is used during creating and reordering widgets
 *
 * @return WidgetContainer
 */
function & get_widget_container( $coll_ID, $container_fieldset_id )
{
	$WidgetContainerCache = & get_WidgetContainerCache();

	if( substr( $container_fieldset_id, 0, 10 ) == 'wico_code_' )
	{ // The widget contianer fieldset id was given by the container code because probably it was not created in the database yet
		$container_code = substr( $container_fieldset_id, 10 );
		$WidgetContainer = $WidgetContainerCache->get_by_coll_and_code( $coll_ID, $container_code );
		if( ! $WidgetContainer )
		{ // The skin container didn't contain any widget before, and it was not saved in the database
			$WidgetContainer = new WidgetContainer();
			$WidgetContainer->set( 'code', $container_code );
			$WidgetContainer->set( 'name', $container_code );
			$WidgetContainer->set( 'coll_ID', $coll_ID );
		}
	}
	elseif( substr( $container_fieldset_id, 0, 8 ) == 'wico_ID_' )
	{ // The widget contianer fieldset id contains the container database ID
		$container_ID = substr( $container_fieldset_id, 8 );
		$WidgetContainer = $WidgetContainerCache->get_by_ID( $container_ID );
	}
	else
	{ // The received fieldset id is not valid
		debug_die( 'Invalid container fieldset id received' );
	}

	return $WidgetContainer;
}


/**
 * @param string Title of the container. This gets passed to T_()!
 * @param boolean Is included in collection skin
 * @param array Params
 */
function display_container( $WidgetContainer, $is_included = true, $params = array() )
{
	global $Collection, $Blog, $admin_url, $embedded_containers, $mode;
	global $Session;

	$params = array_merge( array(
			'table_layout'  => NULL, // Possible values: 'accordion_table', NULL(for default 'Results')
			'group_id'      => NULL,
			'group_item_id' => NULL,
		), $params );

	$Table = new Table( $params['table_layout'] );

	// Table ID - fp> needs to be handled cleanly by Table object
	if( isset( $WidgetContainer->ID ) && ( $WidgetContainer->ID > 0 ) )
	{
		$widget_container_id = 'wico_ID_'.$WidgetContainer->ID;
		$add_widget_url = regenerate_url( '', 'action=new&amp;wico_ID='.$WidgetContainer->ID.'&amp;container='.$widget_container_id );
		$destroy_container_url = url_add_param( $admin_url, 'ctrl=widgets&amp;action=destroy_container&amp;wico_ID='.$WidgetContainer->ID.'&amp;'.url_crumb('widget_container') );
	}
	else
	{
		$wico_code = $WidgetContainer->get( 'code' );
		$widget_container_id = 'wico_code_'.$wico_code;
		$add_widget_url = regenerate_url( '', 'action=new&amp;wico_code='.$wico_code.'&amp;container='.$widget_container_id );
		$destroy_container_url = url_add_param( $admin_url, 'ctrl=widgets&amp;action=destroy_container&amp;wico_code='.$wico_code.'&amp;'.url_crumb('widget_container') );
	}

	if( $mode == 'customizer' )
	{
		$destroy_container_url .= '&amp;mode='.$mode;
	}

	if( ! $is_included )
	{	// Allow to destroy sub-container when it is not included into the selected skin:
		$Table->global_icon( T_('Destroy sub-container'), 'delete', $destroy_container_url, T_('Destroy sub-container'), $mode == 'customizer' ? 0 : 3, $mode == 'customizer' ? 0 : 4 );
	}

	$widget_container_name = T_( $WidgetContainer->get( 'name' ) );
	if( $mode == 'customizer' )
	{	// Customizer mode:
		$Table->title = '<span class="container_name" data-wico_id="'.$widget_container_id.'">'.$widget_container_name.'</span>';
		if( ! empty( $WidgetContainer->ID ) )
		{	// Link to edit current widget container:
			$Table->global_icon( T_('Edit widget container'), 'edit', $admin_url.'?ctrl=widgets&amp;blog='.$Blog->ID.'&amp;action=edit_container&amp;wico_ID='.$WidgetContainer->ID.'&amp;mode='.$mode, T_('Edit widget container'), 0, 0 );
		}
	}
	else
	{	// Normal/back-office mode:
		if( ! empty( $WidgetContainer->ID ) )
		{
			$widget_container_name = '<a href="'.$admin_url.'?ctrl=widgets&amp;blog='.$Blog->ID.'&amp;action=edit_container&amp;wico_ID='.$WidgetContainer->ID.( $mode == 'customizer' ? '&amp;mode='.$mode : '' ).'">'.$widget_container_name.'</a>';
		}
		$Table->title = '<span class="dimmed">'.$WidgetContainer->get( 'order' ).'</span> '
			.'<span class="container_name" data-wico_id="'.$widget_container_id.'">'.$widget_container_name.'</span> '
			.'<span class="dimmed">'.$WidgetContainer->get( 'code' ).'</span>';

		$add_widget_link_params = array( 'class' => 'action_icon btn-primary' );
		if( $mode == 'customizer' )
		{	// Set special url to add new widget on customizer mode:
			$add_widget_url = $admin_url.'?ctrl=widgets&blog='.$Blog->ID.'&skin_type='.$Blog->get_skin_type().'&action=add_list&container='.urlencode( $WidgetContainer->get( 'name' ) ).'&container_code='.urlencode( $WidgetContainer->get( 'code' ) ).'&mode=customizer';
		}
		else
		{	// Add id for link to initialize JS code of opening modal window only for not customizer mode,
			// because in customizer mode we should open this as simple link in the same left customizer panel:
			$add_widget_link_params['id'] = 'add_new_'.$widget_container_id;
		}
		$Table->global_icon( T_('Add a widget...'), 'new', $add_widget_url, /* TRANS: ling used to add a new widget */ T_('Add widget').' &raquo;', 3, 4, $add_widget_link_params );
	}

	if( $params['table_layout'] == 'accordion_table' )
	{	// Set ID for current widget container for proper work of accordion style:
		$params['group_item_id'] = 'container_'.$widget_container_id;
	}

	$Table->display_init( array_merge( array(
			'list_start' => '<div class="panel panel-default">',
			'list_end'   => '</div>',
		), $params ) );

	$Table->display_list_start();

	// TITLE / COLUMN HEADERS:
	$Table->display_head();

	if( $params['table_layout'] == 'accordion_table' )
	{	// Start of accordion body of current item:
		$is_selected_widget_container = empty( $params['selected_wico_ID'] ) || empty( $WidgetContainer ) || $WidgetContainer->ID != $params['selected_wico_ID'];
		echo '<div id="'.$params['group_item_id'].'" class="panel-collapse '.( $is_selected_widget_container ? 'collapse' : '' ).'">';
	}

	// BODY START:
	echo '<ul id="container_'.$widget_container_id.'" class="widget_container">';

	/**
	 * @var WidgetCache
	 */
	$WidgetCache = & get_WidgetCache();
	$Widget_array = & $WidgetCache->get_by_container_ID( $WidgetContainer->ID );

	if( ! empty( $Widget_array ) )
	{
		$widget_count = 0;
		foreach( $Widget_array as $ComponentWidget )
		{
			$widget_count++;
			$enabled = $ComponentWidget->get( 'enabled' );
			$disabled_plugin = ( $ComponentWidget->type == 'plugin' && $ComponentWidget->get_Plugin() == false );

			if( $ComponentWidget->get( 'code' ) == 'subcontainer' )
			{
				$container_code = $ComponentWidget->get_param( 'container' );
				if( ! isset( $embedded_containers[$container_code] ) ) {
					$embedded_containers[$container_code] = true;
				}
			}

			// START Widget row:
			echo '<li id="wi_ID_'.$ComponentWidget->ID.'" class="draggable_widget">';

			// Checkbox:
			if( $mode != 'customizer' )
			{	// Don't display on customizer mode:
				echo '<span class="widget_checkbox'.( $enabled ? ' widget_checkbox_enabled' : '' ).'">'
						.'<input type="checkbox" name="widgets[]" value="'.$ComponentWidget->ID.'" />'
					.'</span>';
			}

			// State:
			echo '<span class="widget_state">';
			if( $disabled_plugin )
			{	// If widget's plugin is disabled:
				echo get_icon( 'warning', 'imgtag', array( 'title' => T_('Inactive / Uninstalled plugin') ) );
			}
			else
			{	// If this is a normal widget or widget's plugin is enabled:
				echo '<a href="#" onclick="return toggleWidget( \'wi_ID_'.$ComponentWidget->ID.'\' );">'
						.get_icon( ( $enabled ? 'bullet_green' : 'bullet_empty_grey' ), 'imgtag', array( 'title' => ( $enabled ? T_('The widget is enabled.') : T_('The widget is disabled.') ) ) )
					.'</a>';
			}
			echo '</span>';

			// Name:
			$ComponentWidget->init_display( array() );
			echo '<span class="widget_title">'
					.'<a href="'.regenerate_url( 'blog', 'action=edit&amp;wi_ID='.$ComponentWidget->ID.( $mode == 'customizer' ? '&amp;mode=customizer' : '' ) ).'" class="widget_name"'
						.( $mode == 'customizer' ? '' : ' onclick="return editWidget( \'wi_ID_'.$ComponentWidget->ID.'\' )"' )
						.'>'
						.$ComponentWidget->get_desc_for_list()
					.'</a> '
					.$ComponentWidget->get_help_link()
				.'</span>';

			// Cache:
			if( $mode != 'customizer' )
			{	// Don't display on customizer mode:
				echo'<span class="widget_cache_status">';
				$widget_cache_status = $ComponentWidget->get_cache_status( true );
				switch( $widget_cache_status )
				{
					case 'disallowed':
						echo get_icon( 'block_cache_disabled', 'imgtag', array( 'title' => T_( 'This widget cannot be cached.' ), 'rel' => $widget_cache_status ) );
						break;

					case 'denied':
						echo action_icon( T_( 'This widget could be cached but the block cache is OFF. Click to enable.' ),
							'block_cache_denied',
							$admin_url.'?ctrl=coll_settings&amp;tab=advanced&amp;blog='.$Blog->ID.'#fieldset_wrapper_caching', NULL, NULL, NULL,
							array( 'rel' => $widget_cache_status ) );
						break;

					case 'enabled':
						echo action_icon( T_( 'Caching is enabled. Click to disable.' ),
							'block_cache_on',
							regenerate_url( 'blog', 'action=cache_disable&amp;wi_ID='.$ComponentWidget->ID.'&amp;'.url_crumb( 'widget' ) ), NULL, NULL, NULL,
							array(
									'rel'     => $widget_cache_status,
									'onclick' => 'return toggleCacheWidget( \'wi_ID_'.$ComponentWidget->ID.'\', \'disable\' )',
								) );
						break;

					case 'disabled':
						echo action_icon( T_( 'Caching is disabled. Click to enable.' ),
							'block_cache_off',
							regenerate_url( 'blog', 'action=cache_enable&amp;wi_ID='.$ComponentWidget->ID.'&amp;'.url_crumb( 'widget' ) ), NULL, NULL, NULL,
							array(
									'rel'     => $widget_cache_status,
									'onclick' => 'return toggleCacheWidget( \'wi_ID_'.$ComponentWidget->ID.'\', \'enable\' )',
								) );
						break;
				}
				echo '</span>';
			}

			// Actions:
			echo '<span class="widget_actions">';
			if( $disabled_plugin )
			{	// If widget's plugin is disabled:
				// Display a space same as the enable/disable icons:
				echo action_icon( '', 'deactivate', '#', NULL, NULL, NULL, array( 'style' => 'visibility:hidden', 'class' => 'toggle_action' ) );
			}
			else
			{	// If this is a normal widget or widget's plugin is enabled:
					// Enable/Disable:
					echo action_icon( ( $enabled ? T_('Disable this widget!') : T_('Enable this widget!') ),
							( $enabled ? 'deactivate' : 'activate' ),
							regenerate_url( 'blog', 'action=toggle&amp;wi_ID='.$ComponentWidget->ID.'&amp;'.url_crumb('widget') ), NULL, NULL, NULL,
							array( 'onclick' => 'return toggleWidget( \'wi_ID_'.$ComponentWidget->ID.'\' )', 'class' => 'toggle_action' )
						);
			}
					// Edit:
					if( $mode != 'customizer' )
					{	// Don't display on customizer mode:
						echo action_icon( T_('Edit widget settings!'),
							'edit',
							regenerate_url( 'blog', 'action=edit&amp;wi_ID='.$ComponentWidget->ID ), NULL, NULL, NULL,
							array( 'onclick' => 'return editWidget( \'wi_ID_'.$ComponentWidget->ID.'\' )', 'class' => '' )
						);
					}
					// Remove:
					echo action_icon( T_('Remove this widget!'),
							'delete',
							regenerate_url( 'blog', 'action=delete&amp;wi_ID='.$ComponentWidget->ID.'&amp;'.url_crumb( 'widget' ) ), NULL, NULL, NULL,
							array( 'onclick' => 'return deleteWidget( \'wi_ID_'.$ComponentWidget->ID.'\' )', 'class' => '' )
						)
				.'</span>';

			// END Widget row:
			echo '</li>';
		}
	}

	// BODY END:
	echo '</ul>';

	if( $params['table_layout'] == 'accordion_table' )
	{	// End of accordion body of current item:
		echo '</div>';
	}

	$Table->display_list_end();
}


/**
 * Display containers
 *
 * @param string Skin type: 'normal', 'mobile', 'tablet'
 * @param boolean TRUE to display main containers, FALSE - sub containers
 * @param array Params
 */
function display_containers( $skin_type, $main = true, $params = array() )
{
	global $Blog, $blog_container_list, $skins_container_list, $embedded_containers;

	// Display containers for current skin:
	$displayed_containers = array();
	$ordered_containers = array();
	$embedded_containers = array();
	$WidgetContainerCache = & get_WidgetContainerCache();
	foreach( $skins_container_list as $container_code => $container_data )
	{
		$WidgetContainer = & $WidgetContainerCache->get_by_coll_and_code( $Blog->ID, $container_code );
		if( ! $WidgetContainer )
		{
			$WidgetContainer = new WidgetContainer();
			$WidgetContainer->set( 'code', $container_code );
			$WidgetContainer->set( 'name', $container_data[0] );
			$WidgetContainer->set( 'coll_ID', $Blog->ID );
			$WidgetContainer->set( 'order', 0 );
		}
		if( $WidgetContainer->get( 'skin_type' ) != $skin_type ||
		    ( $main && ! $WidgetContainer->get( 'main' ) ) ||
		    ( ! $main && $WidgetContainer->get( 'main' ) ) )
		{	// Skip this container because another type is requested:
			continue;
		}

		$ordered_containers[] = array( $WidgetContainer, true );
		if( $WidgetContainer->ID > 0 )
		{ // Container exists in the database
			$displayed_containers[$container_code] = $WidgetContainer->ID;
		}
	}

	// Display embedded containers
	reset( $embedded_containers );
	while( count( $embedded_containers ) > 0 )
	{
		// Get the first item key, and remove the first item from the array
		$container_code = key( $embedded_containers );
		array_shift( $embedded_containers );
		if( isset( $displayed_containers[$container_code] ) )
		{ // This container was already displayed
			continue;
		}

		if( $WidgetContainer = & $WidgetContainerCache->get_by_coll_and_code( $Blog->ID, $container_code ) )
		{ // Confirmed that it is part of the blog's containers in the database
			if( ( $main && ! $WidgetContainer->get( 'main' ) ) ||
			    ( ! $main && $WidgetContainer->get( 'main' ) ) )
			{	// Skip this container because another type is requested:
				continue;
			}
			$ordered_containers[] = array( $WidgetContainer, true );
			$displayed_containers[$container_code] = $WidgetContainer->ID;
		}
	}

	// Display other blog containers which are not in the current skin
	foreach( $blog_container_list as $container_ID )
	{
		if( in_array( $container_ID, $displayed_containers ) )
		{
			continue;
		}

		$WidgetContainer = & $WidgetContainerCache->get_by_ID( $container_ID );
		if( ( $main && ! $WidgetContainer->get( 'main' ) ) ||
		    ( ! $main && $WidgetContainer->get( 'main' ) ) )
		{	// Skip this container because another type is requested:
			continue;
		}
		$ordered_containers[] = array( $WidgetContainer, false );
	}

	// Sort widget containers by order and name:
	usort( $ordered_containers, 'callback_sort_widget_containers' );

	// Display the ordered containers:
	foreach( $ordered_containers as $container_data )
	{
		$WidgetContainer = & $container_data[0];
		// Is included in collection skin?
		$is_included = $container_data[1];
		// Display a container with widgets:
		display_container( $WidgetContainer, $is_included, $params  );
	}
}


/**
 * Callback function to sort widget containers array by fields order and name
 *
 * @param array Widget data
 * @param array Widget data
 */
function callback_sort_widget_containers( $a, $b )
{
	if( $a[0]->get( 'order' ) == $b[0]->get( 'order' ) )
	{	// Sort by name if orders are equal:
		return strnatcmp( $a[0]->get( 'name' ), $b[0]->get( 'name' ) );
	}
	else
	{	// Sort by order if they are different:
		return $a[0]->get( 'order' ) > $b[0]->get( 'order' );
	}
}


/**
 * Display action buttons to work with sereral widgets in list
 *
 * @param object Form
 */
function display_widgets_action_buttons( & $Form )
{
	echo '<span class="btn-group">';
	$Form->button( array(
			'value' => get_icon( 'check_all' ).' '.T_('Check all'),
			'id'    => 'widget_button_check_all',
			'tag'   => 'button',
			'type'  => 'button'
		) );
	$Form->button( array(
			'value' => get_icon( 'uncheck_all' ).' '.T_('Uncheck all'),
			'id'    => 'widget_button_uncheck_all',
			'tag'   => 'button',
			'type'  => 'button'
		) );
	echo '</span>';

	echo '<span class="btn-group">';
	$Form->button( array(
			'value' => get_icon( 'check_all' ).' '.get_icon( 'bullet_green' ).' '.T_('Check Active'),
			'id'    => 'widget_button_check_active',
			'tag'   => 'button',
			'type'  => 'button'
		) );
	$Form->button( array(
			'value' => get_icon( 'check_all' ).' '.get_icon( 'bullet_empty_grey' ).' '.T_('Check Inactive'),
			'id'    => 'widget_button_check_inactive',
			'tag'   => 'button',
			'type'  => 'button'
		) );
	echo '</span>';

	echo ' '.T_('With checked do:');
	echo '<span class="btn-group">';
	$Form->button( array(
			'value' => get_icon( 'bullet_green' ).' '.T_('Activate'),
			'name'  => 'actionArray[activate]',
			'tag'   => 'button',
			'type'  => 'submit'
		) );
	$Form->button( array(
			'value' => get_icon( 'bullet_empty_grey' ).' '.T_('Deactivate'),
			'name'  => 'actionArray[deactivate]',
			'tag'   => 'button',
			'type'  => 'submit'
		) );
	echo '</span>';
}
?>