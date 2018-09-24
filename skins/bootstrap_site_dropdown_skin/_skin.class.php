<?php
/**
 * This file implements a class derived of the generic Skin class in order to provide custom code for
 * the skin in this folder.
 *
 * This file is part of the b2evolution project - {@link http://b2evolution.net/}
 *
 * @package skins
 * @subpackage bootstrap_site_navbar_skin
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

/**
 * Specific code for this skin.
 *
 * ATTENTION: if you make a new skin you have to change the class name below accordingly
 */
class bootstrap_site_dropdown_Skin extends Skin
{
	/**
	 * Skin version
	 * @var string
	 */
	var $version = '1.0.0';

	/**
	 * Do we want to use style.min.css instead of style.css ?
	 */
	var $use_min_css = true;  // true|false|'check' Set this to true for better optimization

	/**
	 * Get default name for the skin.
	 * Note: the admin can customize it.
	 */
	function get_default_name()
	{
		return 'Bootstrap Site Dropdown';
	}


	/**
	 * Get default type for the skin.
	 */
	function get_default_type()
	{
		return 'rwd';
	}


	/**
	 * Does this skin providesnormal (collection) skin functionality?
	 */
	function provides_collection_skin()
	{
		return false;
	}


	/**
	 * Does this skin provide site-skin functionality?
	 */
	function provides_site_skin()
	{
		return true;
	}


	/**
	 * What evoSkins API does has this skin been designed with?
	 *
	 * This determines where we get the fallback templates from (skins_fallback_v*)
	 * (allows to use new markup in new b2evolution versions)
	 */
	function get_api_version()
	{
		return 6;
	}


	/**
	 * Get definitions for editable params
	 *
	 * @see Plugin::GetDefaultSettings()
	 * @param local params like 'for_editing' => true
	 * @return array
	 */
	function get_param_definitions( $params )
	{
		$r = array_merge( array(
				'section_layout_start' => array(
					'layout' => 'begin_fieldset',
					'label'  => T_('CSS files')
				),
					'css_files' => array(
						'label' => T_('CSS files'),
						'note' => '',
						'type' => 'checklist',
						'options' => array(
								array( 'style.css',      'style.css', 0 ),
								array( 'style.min.css',  'style.min.css', 1 ), // default
								array( 'custom.css',     'custom.css', 0 ),
								array( 'custom.min.css', 'custom.min.css', 0 ),
							)
					),
				'section_layout_end' => array(
					'layout' => 'end_fieldset',
				),

				'section_header_start' => array(
					'layout' => 'begin_fieldset',
					'label'  => T_('Header')
				),
					'grouping' => array(
						'label' => T_('Grouping'),
						'note' => T_('Check to group collections into tabs'),
						'type' => 'checkbox',
						'defaultvalue' => 1,
					),

					'section_topmenu_start' => array(
						'layout' => 'begin_fieldset',
						'label'  => T_('Top menu settings')
					),
						'menu_bar_bg_color' => array(
							'label' => T_('Menu bar background color'),
							'note' => T_('E-g: #ff0000 for red'),
							'defaultvalue' => '#f8f8f8',
							'type' => 'color',
						),
						'menu_bar_border_color' => array(
							'label' => T_('Menu bar border color'),
							'note' => T_('E-g: #0000ff for blue'),
							'defaultvalue' => '#e7e7e7',
							'type' => 'color',
						),
						'menu_bar_logo_padding' => array(
							'label' => T_('Menu bar logo padding'),
							'input_suffix' => ' px ',
							'note' => T_('Set the padding around the logo.'),
							'defaultvalue' => '2',
							'type' => 'integer',
							'size' => 1,
						),
						'tab_text_color' => array(
							'label' => T_('Tab text color'),
							'note' => T_('E-g: #ff0000 for red'),
							'defaultvalue' => '#777',
							'type' => 'color',
						),
						'hover_tab_bg_color' => array(
							'label' => T_('Hover tab color'),
							'note' => T_('E-g: #00ff00 for green'),
							'defaultvalue' => '#f8f8f8',
							'type' => 'color',
						),
						'hover_tab_text_color' => array(
							'label' => T_('Hover tab text color'),
							'note' => T_('E-g: #0000ff for blue'),
							'defaultvalue' => '#333',
							'type' => 'color',
						),
						'selected_tab_bg_color' => array(
							'label' => T_('Selected tab color'),
							'note' => T_('E-g: #ff0000 for red'),
							'defaultvalue' => '#e7e7e7',
							'type' => 'color',
						),
						'selected_tab_text_color' => array(
							'label' => T_('Selected tab text color'),
							'note' => T_('E-g: #00ff00 for green'),
							'defaultvalue' => '#555',
							'type' => 'color',
						),
					'section_topmenu_end' => array(
						'layout' => 'end_fieldset',
					),

					'section_submenu_start' => array(
						'layout' => 'begin_fieldset',
						'label'  => T_('Submenu settings')
					),
						'sub_tab_bg_color' => array(
							'label' => T_('Menu bar background color'),
							'note' => T_('E-g: #ff0000 for red'),
							'defaultvalue' => '#fff',
							'type' => 'color',
						),
						'sub_tab_border_color' => array(
							'label' => T_('Menu bar border color'),
							'note' => T_('E-g: #00ff00 for green'),
							'defaultvalue' => '#ddd',
							'type' => 'color',
						),
						'sub_tab_text_color' => array(
							'label' => T_('Tab text color'),
							'note' => T_('E-g: #0000ff for blue'),
							'defaultvalue' => '#555',
							'type' => 'color',
						),
						'sub_hover_tab_bg_color' => array(
							'label' => T_('Hover tab color'),
							'note' => T_('E-g: #ff0000 for red'),
							'defaultvalue' => '#f5f5f5',
							'type' => 'color',
						),
						'sub_hover_tab_text_color' => array(
							'label' => T_('Hover tab text color'),
							'note' => T_('E-g: #0000ff for blue'),
							'defaultvalue' => '#555',
							'type' => 'color',
						),
						'sub_selected_tab_bg_color' => array(
							'label' => T_('Selected tab color'),
							'note' => T_('E-g: #ff0000 for red'),
							'defaultvalue' => '#eee',
							'type' => 'color',
						),
						'sub_selected_tab_text_color' => array(
							'label' => T_('Selected tab text color'),
							'note' => T_('E-g: #0000ff for blue'),
							'defaultvalue' => '#555',
							'type' => 'color',
						),
					'section_submenu_end' => array(
						'layout' => 'end_fieldset',
					),

				'section_header_end' => array(
					'layout' => 'end_fieldset',
				),
				
				'section_floating_nav_start' => array(
					'layout' => 'begin_fieldset',
					'label'  => T_('Floating navigation settings')
				),
						'back_to_top_button' => array(
							'label' => T_('"Back to Top" button'),
							'note' => T_('Check to enable "Back to Top" button'),
							'defaultvalue' => 1,
							'type' => 'checkbox',
						),
				'section_floating_nav_end' => array(
					'layout' => 'end_fieldset',
				),

				'section_footer_start' => array(
					'layout' => 'begin_fieldset',
					'label'  => T_('Footer settings')
				),
					'footer_bg_color' => array(
						'label' => T_('Background color'),
						'note' => T_('E-g: #ff0000 for red'),
						'defaultvalue' => '#f5f5f5',
						'type' => 'color',
					),
					'footer_text_color' => array(
						'label' => T_('Text color'),
						'note' => T_('E-g: #00ff00 for green'),
						'defaultvalue' => '#777',
						'type' => 'color',
					),
					'footer_link_color' => array(
						'label' => T_('Link color'),
						'note' => T_('E-g: #0000ff for blue'),
						'defaultvalue' => '#337ab7',
						'type' => 'color',
					),
				'section_footer_end' => array(
					'layout' => 'end_fieldset',
				),

			), parent::get_param_definitions( $params ) );

		return $r;
	}


	/**
	 * Get ready for displaying the site skin.
	 *
	 * This may register some CSS or JS...
	 */
	function siteskin_init()
	{
		// Include the enabled skin CSS files relative current SITE skin folder:
		$css_files = $this->get_setting( 'css_files' );
		if( is_array( $css_files ) && count( $css_files ) )
		{
			foreach( $css_files as $css_file_name => $css_file_is_enabled )
			{
				if( $css_file_is_enabled )
				{
					require_css( $css_file_name, 'siteskin' );
				}
			}
		}

		// Add custom styles:
		// Top menu:
		$menu_bar_bg_color = $this->get_setting( 'menu_bar_bg_color' );
		$menu_bar_border_color = $this->get_setting( 'menu_bar_border_color' );
		$menu_bar_logo_padding = $this->get_setting( 'menu_bar_logo_padding' );
		$tab_text_color = $this->get_setting( 'tab_text_color' );
		$hover_tab_bg_color = $this->get_setting( 'hover_tab_bg_color' );
		$hover_tab_text_color = $this->get_setting( 'hover_tab_text_color' );
		$selected_tab_bg_color = $this->get_setting( 'selected_tab_bg_color' );
		$selected_tab_text_color = $this->get_setting( 'selected_tab_text_color' );
		// Sub menu:
		$sub_tab_bg_color = $this->get_setting( 'sub_tab_bg_color' );
		$sub_tab_border_color = $this->get_setting( 'sub_tab_border_color' );
		$sub_tab_text_color = $this->get_setting( 'sub_tab_text_color' );
		$sub_hover_tab_bg_color = $this->get_setting( 'sub_hover_tab_bg_color' );
		$sub_hover_tab_text_color = $this->get_setting( 'sub_hover_tab_text_color' );
		$sub_selected_tab_bg_color = $this->get_setting( 'sub_selected_tab_bg_color' );
		$sub_selected_tab_text_color = $this->get_setting( 'sub_selected_tab_text_color' );
		// Footer:
		$footer_bg_color = $this->get_setting( 'footer_bg_color' );
		$footer_text_color = $this->get_setting( 'footer_text_color' );
		$footer_link_color = $this->get_setting( 'footer_link_color' );


		add_css_headline( '
.bootstrap_site_navbar_header .navbar {
	background-color: '.$menu_bar_bg_color.';
	border-color: '.$menu_bar_border_color.';
}
.bootstrap_site_navbar_header .navbar .navbar-collapse .nav.navbar-right {
	border-color: '.$menu_bar_border_color.';
}
.bootstrap_site_navbar_header .navbar-brand img {
	padding: '.$menu_bar_logo_padding.'px;
}
.bootstrap_site_navbar_header .navbar .nav > li:not(.active) > a {
	color: '.$tab_text_color.';
}
.bootstrap_site_navbar_header .navbar .nav > li:not(.active) > a:hover {
	background-color: '.$hover_tab_bg_color.';
	color: '.$hover_tab_text_color.';
}
.bootstrap_site_navbar_header .navbar .nav > li.active > a {
	background-color: '.$selected_tab_bg_color.';
	color: '.$selected_tab_text_color.';
}

.bootstrap_site_navbar_header .navbar .nav ul.dropdown-menu {
	background-color: '.$sub_tab_bg_color.';
	border-color: '.$sub_tab_border_color.';
}
.bootstrap_site_navbar_header .navbar .nav ul.dropdown-menu li:not(.active) a {
	color: '.$sub_tab_text_color.';
}
.bootstrap_site_navbar_header .navbar .nav ul.dropdown-menu li:not(.active) a:hover {
	background-color: '.$sub_hover_tab_bg_color.';
	color: '.$sub_hover_tab_text_color.';
}
.bootstrap_site_navbar_header .navbar .nav ul.dropdown-menu li.active a {
	background-color: '.$sub_selected_tab_bg_color.';
	color: '.$sub_selected_tab_text_color.';
}

footer.bootstrap_site_navbar_footer {
	background-color: '.$footer_bg_color.';
	color: '.$footer_text_color.';
}
footer.bootstrap_site_navbar_footer .container a {
	color: '.$footer_link_color.';
}
');
	}


	/**
	 * Get header tabs
	 *
	 * @return array
	 */
	function get_header_tabs()
	{
		global $Blog, $disp, $Settings;

		$header_tabs = array();

		// Get disp from request string if it is not initialized yet:
		$current_disp = isset( $_GET['disp'] ) ? $_GET['disp'] : ( isset( $disp ) ? $disp : NULL );

		// Get current collection ID:
		$current_blog_ID = isset( $Blog ) ? $Blog->ID : NULL;

		// Load all sections except of "No Section" because collections of this section are displayed as separate tabs at the end:
		$SectionCache = & get_SectionCache();
		$SectionCache->clear();
		$SectionCache->load_where( 'sec_ID != 1' );

		$this->header_tab_active = NULL;
		$level0_index = 0;
		foreach( $SectionCache->cache as $Section )
		{
			$tab_items = array();
			$group_blogs = $Section->get_blogs();

			$level0_is_active = false;

			// Check each collection if it can be viewed by current user:
			foreach( $group_blogs as $i => $group_Blog )
			{
				$coll_is_active = false;
				if( $current_blog_ID == $group_Blog->ID &&
						( $Settings->get( 'info_blog_ID' ) != $current_blog_ID || ( $current_disp != 'page' && $current_disp != 'msgform' ) ) )
				{	// Mark this menu as active:
					$coll_is_active = true;
				}

				$coll_data = array(
						'name'   => $group_Blog->get( 'name' ),
						'url'    => $group_Blog->get( 'url' ),
						'active' => ( $current_blog_ID == $group_Blog->ID )
					);

				// Get value of collection setting "Show in front-office list":
				$in_bloglist = $group_Blog->get( 'in_bloglist' );

				if( $in_bloglist == 'public' )
				{	// Everyone can view this collection, Keep this in menu:
					$tab_items[] = $coll_data;
					if( $coll_is_active )
					{
						$this->header_tab_active = $level0_index;
					}
					continue;
				}

				if( $in_bloglist == 'never' )
				{	// Nobody can view this collection, Skip it:
					continue;
				}

				if( ! is_logged_in() )
				{	// Only logged in users have an access to this collection, Skip it:
					continue;
				}

				if( $in_bloglist == 'member' &&
						! $current_User->check_perm( 'blog_ismember', 'view', false, $skin_coll_ID ) )
				{	// Only members have an access to this collection, Skip it:
					continue;
				}

				$tab_items[] = $coll_data;
				if( $coll_is_active )
				{
					$this->header_tab_active = $level0_index;
				}
			}

			if( ! empty( $tab_items ) )
			{	// Display section only if at least one collection is allowed for current display:
				$header_tabs[] = array(
						'name'  => $Section->get_name(),
						'url'   => $tab_items[0]['url'],
						'items' => $tab_items
					);

				$level0_index++;
			}
		}

		// Load all collection from "No Section" and put them after all section tabs:
		$BlogCache = & get_BlogCache();
		$BlogCache->clear();
		$BlogCache->load_where( 'blog_sec_ID = 1' );

		foreach( $BlogCache->cache as $nosec_Blog )
		{
			$header_tabs[] = array(
					'name' => $nosec_Blog->get( 'shortname' ),
					'url'  => $nosec_Blog->get( 'url' ),
				);

			if( $current_blog_ID == $nosec_Blog->ID )
			{	// Mark this tab as active if this is a current collection:
				$this->header_tab_active = $level0_index;
			}

			$level0_index++;
		}

		// Additional tab with pages and contact links:
		if( isset( $Blog ) )
		{
			$tab_items = array( 'pages' );

			if( $current_disp == 'msgform' )
			{	// Mark this menu as active:
				$this->header_tab_active = $level0_index;
			}

			if( $current_disp == 'page' && $Settings->get( 'info_blog_ID' ) == $Blog->ID )
			{	// If this menu contains the links to pages of the info/shared collection:
				$this->header_tab_active = $level0_index;
			}

			if( $contact_url = $Blog->get_contact_url( true ) )
			{	// If contact page is allowed for current collection:
				$tab_items[] = array(
						'name'   => T_('Contact'),
						'url'    => $contact_url,
						'active' => ( $current_disp == 'msgform' )
					);
			}

			if( ! empty( $contact_url ) )
			{	// Display additional tabs with static pages only user has an access to contact page:
				$header_tabs[] = array(
						'name'   => 'About',
						'url'    => $contact_url,
						'items'  => $tab_items
					);
			}
		}

		return $header_tabs;
	}
}

?>