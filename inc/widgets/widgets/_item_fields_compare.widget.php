<?php
/**
 * This file implements Item Fields Compare Widget class.
 *
 * This file is part of the evoCore framework - {@link http://evocore.net/}
 * See also {@link https://github.com/b2evolution/b2evolution}.
 *
 * @license GNU GPL v2 - {@link http://b2evolution.net/about/gnu-gpl-license}
 *
 * @copyright (c)2003-2017 by Francois Planque - {@link http://fplanque.com/}
 *
 * @package evocore
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

load_class( 'widgets/model/_widget.class.php', 'ComponentWidget' );

/**
 * ComponentWidget Class
 *
 * A ComponentWidget is a displayable entity that can be placed into a Container on a web page.
 *
 * @package evocore
 */
class item_fields_compare_Widget extends ComponentWidget
{
	var $icon = 'balance-scale';

	/**
	 * Constructor
	 */
	function __construct( $db_row = NULL )
	{
		// Call parent constructor:
		parent::__construct( $db_row, 'core', 'item_fields_compare' );
	}


	/**
	 * Get help URL
	 *
	 * @return string URL
	 */
	function get_help_url()
	{
		return get_manual_url( 'item-fields-compare-widget' );
	}


	/**
	 * Get name of widget
	 */
	function get_name()
	{
		return T_('Compare Item Fields');
	}


	/**
	 * Get a very short desc. Used in the widget list.
	 */
	function get_short_desc()
	{
		return format_to_output( $this->disp_params['title'] );
	}


  /**
	 * Get short description
	 */
	function get_desc()
	{
		return T_('Display differences between custom fields of the selected items.');
	}


  /**
   * Get definitions for editable params
   *
	 * @see Plugin::GetDefaultSettings()
	 * @param local params like 'for_editing' => true
	 */
	function get_param_definitions( $params )
	{
		global $Blog;

		$ItemTypeCache = & get_ItemTypeCache();
		$item_type_options = array(
				'default' => T_('Default types shown for this collection')
			) + $ItemTypeCache->get_option_array();

		$r = array(
				'title' => array(
					'label' => T_( 'Title' ),
					'size' => 40,
					'note' => T_('This is the title to display'),
					'defaultvalue' => '',
				),
				'items_source' => array(
					'label' => T_('Items to compare'),
					'type' => 'select',
					'options' => array(
						'all'   => T_('All from collection'),
						'param' => sprintf( T_('As specified by "%s" URL param'), 'items=' ),
						'list'  => T_('Specific IDs listed below'),
					),
					'defaultvalue' => 'param',
				),
				'items_type' => array(
					'label' => T_('Restrict to Post Type'),
					'type' => 'select',
					'options' => $item_type_options,
					'defaultvalue' => 'default',
				),
				'items' => array(
					'label' => T_('Specific Item IDs'),
					'note' => sprintf( T_('Separate Item IDs or slugs or %s or %s with %s.'), '<code>$this$</code>', '<code>$parent$</code>', '<code>,</code>' ),
					'valid_pattern' => array(
						'pattern' => '/^(([\da-z\-_]+|\$this\$|\$parent\$)+(,([\da-z\-_]+|\$this\$|\$parent\$))*)?$/',
						'error'   => sprintf( T_('Items to compare must be specified by ID, by slug or as %s or %s.'), '<code>$this$</code>', '<code>$parent$</code>' ),
					),
					'size' => 80,
				),
				'items_limit' => array(
					'label' => T_('Limit'),
					'type' => 'integer',
					'note' => T_('Max number of items that can be compared.'),
					'defaultvalue' => 10,
					'valid_range' => array(
						'min' => 0,
					),
					'allow_empty' => true,
				),
				'allow_filter' => array(
					'label' => T_('Allow filter params'),
					'type' => 'checkbox',
					'note' => sprintf( T_('Check to allow filtering/ordering with URL params such as %s etc.'), '<code>cat=</code>, <code>tag=</code>, <code>orderby=</code>' ),
					'defaultvalue' => 0,
				),
			);
		for( $order_index = 0; $order_index <= 2; $order_index++ )
		{	// Default order settings:
			$field_suffix = ( $order_index == 0 ? '' : '_'.$order_index );
			$coll_item_sort_options = get_available_sort_options( $Blog->ID, $order_index > 0, true );
			$r = array_merge( $r, array(
				'order_begin_line'.$field_suffix => array(
					'type' => 'begin_line',
					'label' => ( $order_index == 0 ? T_('Default order') : '' ),
				),
					'orderby'.$field_suffix => array(
						'type' => 'select',
						'options' => array_merge(
								array( 'coll_default' => T_('Use collection\'s default'), ),
								$coll_item_sort_options['general'],
								array( T_('Custom fields') => $coll_item_sort_options['custom'] )
							),
						'defaultvalue' => 'coll_default',
					),
					'orderdir'.$field_suffix => array(
						'type' => 'select',
						'options' => array(
							'ASC'  => T_('Ascending'),
							'DESC' => T_('Descending'),
						),
						'defaultvalue' => 'ASC',
					),
				'order_end_line'.$field_suffix => array(
					'type' => 'end_line',
				),
			) );
		}
		$r = array_merge( $r, array(
				'fields_source' => array(
					'label' => T_('Fields to show'),
					'type' => 'select',
					'options' => array(
						'all'     => T_('All'),
						'exclude' => T_('All except fields listed below'),
						'include' => T_('Only fields listed below'),
					),
					'defaultvalue' => 'all',
				),
				'fields' => array(
					'type' => 'textarea',
					'label' => '',
					'note' => T_('Enter one field name per line.'),
					'rows' => 10,
				),
				'edit_links' => array(
					'type' => 'checkbox',
					'label' => T_('Edit Links'),
					'note' => T_('Check to add a row with edit buttons for editors who have permission.'),
					'defaultvalue' => 1,
				),
			), parent::get_param_definitions( $params ) );

		return $r;
	}


	/**
	 * Display the widget!
	 *
	 * @param array MUST contain at least the basic display params
	 */
	function display( $params )
	{
		$this->init_display( $params );

		$this->disp_params = array_merge( array(
				'custom_fields_table_start'                => '<div class="evo_content_block"><table class="item_custom_fields">',
				'custom_fields_row_start'                  => '<tr>',
				'custom_fields_topleft_cell'               => '<td style="border:none"></td>',
				'custom_fields_col_header_item'            => '<th class="center">$item_link$</th>',  // Note: we will also add reverse view later: 'custom_fields_col_header_field
				'custom_fields_row_header_field'           => '<th class="$header_cell_class$">$field_title$$field_description_icon$:</th>',
				'custom_fields_description_icon_class'     => 'grey',
				'custom_fields_value_default'              => '<td class="$data_cell_class$">$field_value$</td>',
				'custom_fields_value_difference_highlight' => '<td class="$data_cell_class$ bg-warning">$field_value$</td>',
				'custom_fields_value_green'                => '<td class="$data_cell_class$ bg-success">$field_value$</td>',
				'custom_fields_value_red'                  => '<td class="$data_cell_class$ bg-danger">$field_value$</td>',
				'custom_fields_edit_link_cell'             => '<td class="center">$edit_link$</td>',
				'custom_fields_edit_link_class'            => 'btn btn-xs btn-default',
				'custom_fields_row_end'                    => '</tr>',
				'custom_fields_table_end'                  => '</table></div>',
				// Separate template for separator fields:
				// (Possible to use templates for all field types: 'numeric', 'string', 'html', 'text', 'url', 'image', 'computed', 'separator')
				'custom_fields_separator_row_header_field' => '<th class="$header_cell_class$" colspan="$cols_count$">$field_title$$field_description_icon$</th>',
			), $this->disp_params );

		// Get IDs of items which should be compared:
		$items = $this->get_items_IDs();

		// Get custom fields with compared data:
		$custom_fields = $this->get_custom_fields( $items );

		if( empty( $custom_fields ) )
		{	// Nothing to compare:
			return;
		}

		$ItemCache = & get_ItemCache();

		echo $this->disp_params['block_start'];

		$this->disp_title();

		echo $this->disp_params['block_body_start'];

		// Start a table to display differences of all custom fields for selected posts:
		echo $this->get_field_template( 'table_start' );

		if( ! isset( $this->display_item_headers ) || $this->display_item_headers !== false )
		{	// Display item headers row only when it is not disabled e.g. from child class item_custom_fields_Widget:
			echo $this->get_field_template( 'row_start' );
			echo $this->get_field_template( 'topleft_cell' );
			foreach( $items as $item_ID )
			{
				$widget_Item = & $ItemCache->get_by_ID( $item_ID, false, false );
				// Permanent post link:
				echo str_replace( '$item_link$', $widget_Item->get_title( array( 'title_field' => 'short_title,title' ) ), $this->get_field_template( 'col_header_item' ) );
			}
			echo $this->get_field_template( 'row_end' );
		}

		foreach( $custom_fields as $custom_field )
		{
			if( $custom_field['display_mode'] == 'normal' )
			{	// Display a row of one compared field between all selected items:
				// Note: skip fields with display mode "repeat" which should be displayed after specific separator below:
				$this->display_field_row_template( $custom_field, $items, $this->disp_params );
			}
			if( ! empty( $custom_field['repeat_fields'] ) )
			{	// Display the repeated fields after separator if it has them:
				foreach( $custom_field['repeat_fields'] as $repeat_field_name )
				{	// Display a row of the repeated custom field between all selected items:
					$this->display_field_row_template( $custom_fields[ $repeat_field_name ], $items, $this->disp_params );
				}
			}
		}

		if( $this->disp_params['edit_links'] && is_logged_in() )
		{	// Display buttons to edit the compared items if user has a permission:
			global $Item;
			$items_edit_links = array();
			$items_can_be_edited = false;
			foreach( $items as $item_ID )
			{
				if( isset( $Item ) && $Item->ID == $item_ID && count( $items ) == 1 )
				{	// Don't display an edit link when this is a page of currently displayed Item:
					$items_edit_links[] = '';
				}
				else
				{	// Try to display an edit link depending on permissions of current User:
					$widget_Item = & $ItemCache->get_by_ID( $item_ID, false, false );
					$items_edit_link = $widget_Item->get_edit_link( array( 'class' => $this->disp_params['custom_fields_edit_link_class'] ) );
					if( $items_edit_link === false )
					{	// The edit link is not available for current User:
						$items_edit_links[] = '';
					}
					else
					{	// The Item can be edited by current User:
						$items_edit_links[] = $items_edit_link;
						// Set flag to know at least one compared item can be edited by current User:
						$items_can_be_edited = true;
					}
				}
			}

			if( $items_can_be_edited )
			{	// Display the footer row with edit links if at least one compared item can be edited by current User:
				echo $this->get_field_template( 'row_start' );

				echo $this->get_field_template( 'topleft_cell' );

				foreach( $items_edit_links as $items_edit_link )
				{
					echo str_replace( '$edit_link$', $items_edit_link, $this->get_field_template( 'edit_link_cell' ) );
				}

				echo $this->get_field_template( 'row_end' );
			}
		}

		echo $this->get_field_template( 'table_end' );

		echo $this->disp_params['block_body_end'];

		echo $this->disp_params['block_end'];

		return true;
	}


	/**
	 * Get custom fields between the requested items with additional compare data
	 *
	 * @param array Item IDs, updated by referene
	 * @return array
	 */
	function get_custom_fields( & $items )
	{
		if( empty( $items ) )
		{	// No items to compare:
			return false;
		}

		$ItemCache = & get_ItemCache();

		// Load all requested posts into the cache:
		$ItemCache->clear();
		$ItemCache->load_list( $items );

		$fields_source = $this->disp_params['fields_source'];

		if( $fields_source == 'exclude' || $fields_source == 'include' )
		{	// Check what fields should be excluded/included for this widget:
			$widget_fields = trim( $this->disp_params['fields'] );
			$widget_fields = empty( $widget_fields ) ? array() : preg_split( '#[\s\n\r]+#', $widget_fields );
		}

		$all_custom_fields = array();
		foreach( $items as $i => $item_ID )
		{
			if( ! $widget_Item = & $ItemCache->get_by_ID( $item_ID, false, false ) )
			{	// Skin wrong post:
				unset( $items[ $i ] );
				continue;
			}

			// Load all custom fields of the Item in cache:
			$item_custom_fields = $widget_Item->get_custom_fields_defs();

			// Use either all custom fields of the Item or only specified fields in config of this Widget:
			switch( $fields_source )
			{
				case 'exclude':
					// Exclude custom fields from the specific list:
					$search_custom_fields = array_diff( array_keys( $item_custom_fields ), $widget_fields );
					break;
				case 'include':
					// Include custom fields from the specific list:
					$search_custom_fields = $widget_fields;
					break;
				case 'all':
				default:
					// Use all custom fields:
					$search_custom_fields = array_keys( $item_custom_fields );
					break;
			}

			foreach( $search_custom_fields as $search_custom_field_key )
			{
				$search_custom_field_name = $search_custom_field_key;
				$field_options = '';
				if( $fields_source == 'include' && strpos( $search_custom_field_key, '+' ) !== false )
				{	// Parse additional field options, e.g. separators may have names as 'separator+repeat', 'separator+fields', 'separator+repeat+fields':
					$search_custom_field_options = explode( '+', $search_custom_field_key, 2 );
					if( isset( $search_custom_field_options[1] ) )
					{	// The field has additional options:
						$field_options = $search_custom_field_options[1];
					}
					// Set real name of separator field from key like 'separator+repeat+fields':
					$search_custom_field_name = $search_custom_field_options[0];
				}
				if( ! isset( $all_custom_fields[ $search_custom_field_name ] ) )
				{	// Initialize array to keep fields in the requested order:
					$all_custom_fields[ $search_custom_field_name ] = array();
				}
				if( ! isset( $item_custom_fields[ $search_custom_field_name ] ) )
				{	// Skip because the post has no this custom field:
					continue;
				}
				$item_custom_field = $item_custom_fields[ $search_custom_field_name ];
				if( ! $item_custom_field['public'] )
				{	// Skip not public custom field:
					continue;
				}
				if( isset( $all_custom_fields[ $search_custom_field_key ]['display_mode'] ) &&
				    $all_custom_fields[ $search_custom_field_key ]['display_mode'] == 'repeat' )
				{	// Reinitialize custom field with correct order if it was initialized before as repeat field of some separator above this custom field:
					unset( $all_custom_fields[ $search_custom_field_key ] );
				}
				if( empty( $all_custom_fields[ $search_custom_field_key ] ) )
				{	// Initialize array to store items which really have this custom field:
					$all_custom_fields[ $search_custom_field_key ] = $item_custom_field;
					$all_custom_fields[ $search_custom_field_key ]['display_mode'] = 'normal';
					$all_custom_fields[ $search_custom_field_key ]['items'] = array();
				}
				if( ! in_array( $item_ID , $all_custom_fields[ $search_custom_field_key ]['items'] ) )
				{	// Store ID of the post which has this custom field:
					$all_custom_fields[ $search_custom_field_key ]['items'][] = $item_ID;
				}

				if( $item_custom_field['type'] == 'separator' )
				{	// Initialize the repeat fields and fields under separator field until next separtor:
					if( ! empty( $item_custom_field['format'] ) &&
					    ( strpos( $field_options, 'repeat' ) !== false || // if field is requested with name like 'separator+repeat' or 'separator+repeat+fields'
					      $fields_source != 'include' // also get all repeat fields when fields list is full
					    )
					  )
					{	// Try to find the repeat fields:
						$separator_format = explode( ':', $item_custom_field['format'] );
						if( $separator_format[0] != 'repeat' || empty( $separator_format[1] ) )
						{	// Skip wrong separator format:
							continue;
						}
						$repeat_fields = explode( ',', $separator_format[1] );
					}
					else
					{	// The separator has no repeat fields:
						$repeat_fields = array();
					}

					if( $fields_source == 'include' &&
					    strpos( $field_options, 'fields' ) !== false ) // if field is requested with name like 'separator+fields' or 'separator+repeat+fields'
					{	// Try to find fields under separator only when we request a specific fields list,
						// in full list the fields under separator are displayed automatically, se we should not get them to avoid duplicated view:
						$is_under_separator_field = false;
						foreach( $item_custom_fields as $ic_field_name => $ic_field )
						{
							if( $ic_field_name == $search_custom_field_name )
							{	// We found the current separtor, set flag to use next fields:
								$is_under_separator_field = true;
								continue;
							}
							if( $is_under_separator_field )
							{	// This is a field under current separator:
								if( $ic_field['type'] == 'separator' )
								{	// Stop here because it is another separator:
									break;
								}
								$repeat_fields[] = $ic_field_name;
							}
						}
					}

					foreach( $repeat_fields as $r => $repeat_field_name )
					{
						$repeat_field_name = trim( $repeat_field_name );
						if( ! isset( $item_custom_fields[ $repeat_field_name ] ) )
						{	// Skip unknown field:
							unset( $repeat_fields[ $r ] );
							continue;
						}
						$repeat_fields[ $r ] = $repeat_field_name;
						$item_custom_field = $item_custom_fields[ $repeat_field_name ];
						if( empty( $all_custom_fields[ $repeat_field_name ] ) )
						{	// Initialize array to store items which really have this custom field:
							$all_custom_fields[ $repeat_field_name ] = $item_custom_field;
							$all_custom_fields[ $repeat_field_name ]['display_mode'] = 'repeat'; // Special display mode in order to display this only after the separator
							$all_custom_fields[ $repeat_field_name ]['items'] = array();
						}
						if( ! in_array( $item_ID , $all_custom_fields[ $repeat_field_name ]['items'] ) )
						{	// Store ID of the post which has this custom field:
							$all_custom_fields[ $repeat_field_name ]['items'][] = $item_ID;
						}
					}
					if( ! isset( $all_custom_fields[ $search_custom_field_key ]['repeat_fields'] ) &&
					    ! empty( $repeat_fields ) )
					{	// Initialize array to store the repeat fields of the separator:
						$all_custom_fields[ $search_custom_field_key ]['repeat_fields'] = $repeat_fields;
					}
				}
			}
		}

		// Clear array to remove fields which are not used by any post,
		// for case when field doesn't exist or not public:
		foreach( $all_custom_fields as $a => $all_custom_field )
		{
			if( empty( $all_custom_field ) )
			{
				unset( $all_custom_fields[ $a ] );
			}
		}

		if( $fields_source == 'all' || $fields_source == 'exclude' )
		{	// Sort custom fields from all requested posts by custom field order:
			uasort( $all_custom_fields, array( $this, 'sort_custom_fields' ) );
		}

		if( empty( $all_custom_fields ) )
		{	// Don't display widget if all selected items have no custom fields:
			return false;
		}

		// Compare custom field values:
		$items_count = count( $items );
		foreach( $all_custom_fields as $c => $custom_field )
		{
			if( $custom_field['type'] == 'separator' )
			{	// Separator fields have no values:
				continue;
			}

			$all_custom_fields[ $c ]['is_different'] = false;
			$is_numeric_type = in_array( $custom_field['type'], array( 'double', 'computed' ) );
			if( $is_numeric_type )
			{	// Compare only numeric fields:
				$all_custom_fields[ $c ]['highest_value'] = NULL;
				$all_custom_fields[ $c ]['lowest_value'] = NULL;
			}
			if( $items_count != count( $custom_field['items'] ) )
			{	// If some post has no field then it is a different:
				$all_custom_fields[ $c ]['is_different'] = true;
			}

			// Compare values:
			$prev_custom_field_value = NULL;
			$i = 0;
			$all_string_values_are_empty = ( $fields_source == 'all' || $fields_source == 'exclude' );
			foreach( $custom_field['items'] as $item_ID )
			{
				$widget_Item = & $ItemCache->get_by_ID( $item_ID, false, false );
				$custom_field_value = $widget_Item->get_custom_field_value( $custom_field['name'] );

				if( $all_string_values_are_empty &&
				    ( ! empty( $custom_field_value ) || $custom_field['type'] == 'separator' ) )
				{	// At least one field is not empty:
					$all_string_values_are_empty = false;
				}

				// Check if the values are different from given line:
				if( ! $all_custom_fields[ $c ]['is_different'] && $i > 0 )
				{	// Don't search differences in all fields if at least two fields are different:
					switch( $custom_field['type'] )
					{
						case 'image':
							// Special comparing for image fields:
							$LinkCache = & get_LinkCache();
							if( $prev_Link = & $LinkCache->get_by_ID( $prev_custom_field_value, false, false ) &&
									$prev_File = & $prev_Link->get_File() &&
									$cur_Link = & $LinkCache->get_by_ID( $custom_field_value, false, false ) &&
									$cur_File = & $cur_Link->get_File() )
							{	// Compare hashes of the two files:
								$is_different = $prev_File->get( 'hash' ) != $cur_File->get( 'hash' );
							}
							else
							{	// If at least one field has a wrong link ID then compare IDs:
								$is_different = $custom_field_value != $prev_custom_field_value;
							}
							break;

						default:
							$is_different = $custom_field_value != $prev_custom_field_value;
							break;
					}
					if( $is_different )
					{	// Mark this field is different:
						$all_custom_fields[ $c ]['is_different'] = true;
					}
				}
				$prev_custom_field_value = $custom_field_value;

				if( $is_numeric_type && is_numeric( $custom_field_value ) )
				{	// Compare only numeric values:
					// Search the highest value:
					if( $all_custom_fields[ $c ]['highest_value'] === NULL ||
							$custom_field_value > $all_custom_fields[ $c ]['highest_value'] )
					{
						$all_custom_fields[ $c ]['highest_value'] = $custom_field_value;
					}

					// Search the lowest value:
					if( $all_custom_fields[ $c ]['lowest_value'] === NULL ||
							$custom_field_value < $all_custom_fields[ $c ]['lowest_value'] )
					{
						$all_custom_fields[ $c ]['lowest_value'] = $custom_field_value;
					}
				}
				$i++;
			}

			if( $all_string_values_are_empty && $items_count > 1 )
			{	// Don't display row of custom field if values from all compared items are empty,
				// But display all empty fields when only single items is displayed, e.g. in child item_custom_fields_Widget:
				unset( $all_custom_fields[ $c ] );
			}
		}

		return $all_custom_fields;
	}


	/**
	 * Display a row of one compared field between the requested items
	 *
	 * @param array Custom field data
	 * @param array IDs of the compared items
	 * @param array Additional parameters
	 */
	function display_field_row_template( $custom_field, $items, $params = array() )
	{
		$ItemCache = & get_ItemCache();

		echo $this->get_field_template( 'row_start', $custom_field['type'] );

		if( empty( $custom_field['description'] ) )
		{	// The custom field has no description:
			$field_description_icon = '';
		}
		else
		{	// Display a description in tooltip of the help icon:
			$field_description_icon = ' '.get_icon( 'help', 'imgtag', array(
					'data-toggle' => 'tooltip',
					'title'       => nl2br( $custom_field['description'] ),
					'class'       => $params['custom_fields_description_icon_class'],
				) ).' ';
		}

		// Render special masks like #yes#, (+), #stars/3# and etc. in value with template:
		$custom_field_label = render_custom_field( $custom_field['label'], $params );

		// Custom field title:
		echo str_replace( array( '$field_title$', '$cols_count$', '$field_description_icon$', '$header_cell_class$' ),
			array( $custom_field_label, count( $items ) + 1, $field_description_icon, $custom_field['header_class'] ),
			$this->get_field_template( 'row_header_field', $custom_field['type'] ) );

		if( $custom_field['type'] != 'separator' )
		{	// Separator fields have no values:
			foreach( $items as $item_ID )
			{
				// Custom field value per each post:
				if( in_array( $item_ID, $custom_field['items'] ) )
				{	// Get a formatted value if post has this custom field:
					$widget_Item = & $ItemCache->get_by_ID( $item_ID, false, false );
					$custom_field_value = $widget_Item->get_custom_field_formatted( $custom_field['name'], $params );
					$custom_field_orig_value = $widget_Item->get_custom_field_value( $custom_field['name'] );
				}
				else
				{	// This post has no this custom field:
					$custom_field_value = '';
					$custom_field_orig_value = false;
				}

				// Default template for field value:
				$field_value_template = $this->get_field_template( 'value_default', $custom_field['type'] );

				if( ( $custom_field['is_different'] && $custom_field['line_highlight'] == 'differences' ) ||
				    ( $custom_field['line_highlight'] == 'always' && count( $items ) > 1 ) )
				{	// Mark the field value as different only when it is defined in the settings of the custom field:
					$field_value_template = $this->get_field_template( 'value_difference_highlight', $custom_field['type'] );
				}

				if( in_array( $custom_field['type'], array( 'double', 'computed' ) ) &&
						is_numeric( $custom_field_orig_value ) )
				{	// Compare only numeric values:
					if( $custom_field_orig_value === $custom_field['highest_value'] &&
							$custom_field_orig_value !== $custom_field['lowest_value'] )
					{	// Check if we should mark the highest field:
						if( $custom_field['green_highlight'] == 'highest' )
						{	// The highest value must be marked as green:
							$field_value_template = $this->get_field_template( 'value_green', $custom_field['type'] );
						}
						elseif( $custom_field['red_highlight'] == 'highest' )
						{	// The highest value must be marked as red:
							$field_value_template = $this->get_field_template( 'value_red', $custom_field['type'] );
						}
					}

					if( $custom_field_orig_value === $custom_field['lowest_value'] &&
							$custom_field_orig_value !== $custom_field['highest_value'] )
					{	// Check if we should mark the lowest field:
						if( $custom_field['green_highlight'] == 'lowest' )
						{	// The lowest value must be marked as green:
							$field_value_template = $this->get_field_template( 'value_green', $custom_field['type'] );
						}
						elseif( $custom_field['red_highlight'] == 'lowest' )
						{	// The lowest value must be marked as red:
							$field_value_template = $this->get_field_template( 'value_red', $custom_field['type'] );
						}
					}
				}

				echo str_replace( array( '$data_cell_class$', '$field_value$' ), array( $custom_field['cell_class'], $custom_field_value ), $field_value_template );
			}
		}

		echo $this->get_field_template( 'row_end', $custom_field['type'] );
	}


	/**
	 * Get IDs of items which are used by this widget depending on settings
	 *
	 * @return array
	 */
	function get_items_IDs()
	{
		global $Collection, $Blog, $Item;

		switch( $this->disp_params['items_source'] )
		{
			case 'all':
				// Use all items from current collection,
				// They are loaded by ItemList below:
				$items = 'all';
				break;

			case 'param':
				// Use items from param:
				$items = param( 'items', '/^[\d,]*$/' );
				$items = trim( $items, ',' );
				$items = empty( $items ) ? false : explode( ',', $items );
				break;

			case 'list':
				// Use items from specific list:
				$items = trim( $this->disp_params['items'], ',' );
				$items = empty( $items ) ? false : explode( ',', $items );
				break;

			default:
				// Stop here, because unknown items source.
				return array();
		}

		if( empty( $items ) && $items != 'all' )
		{	// No items to compare:
			return array();
		}

		$ItemCache = & get_ItemCache();

		$items_limit = intval( $this->disp_params['items_limit'] );
		$items_limit = $items_limit > 0 ? $items_limit : NULL;

		if( is_array( $items ) )
		{	// Check item IDs which are loaded from URL param 'items=' or from specific widget settings list:
			foreach( $items as $i => $item_ID )
			{
				$item_ID = trim( $item_ID );
				if( empty( $item_ID ) )
				{	// Remove wrong item ID:
					unset( $items[ $i ] );
				}
				if( $item_ID == '$this$' )
				{	// Try to get a current post ID:
					if( isset( $Item ) && $Item instanceof Item )
					{	// Use ID of current post:
						$items[ $i ] = $Item->ID;
					}
					else
					{	// Remove it because no current post:
						unset( $items[ $i ] );
					}
				}
				elseif( $item_ID == '$parent$' )
				{	// Try to get a parent post ID:
					if( isset( $Item ) && $Item instanceof Item && $Item->get( 'parent_ID' ) > 0 )
					{	// Use ID of parent post:
						$items[ $i ] = $Item->get( 'parent_ID' );
					}
					else
					{	// Remove it because no parent post:
						unset( $items[ $i ] );
					}
				}
				elseif( ! is_number( $item_ID ) )
				{	// Try to get a post ID by slug:
					if( $widget_Item = & $ItemCache->get_by_urltitle( $item_ID, false, false ) )
					{	// Use ID of post detected by slug:
						$items[ $i ] = $widget_Item->ID;
					}
					else
					{	// Remove it because cannot find post by slug:
						unset( $items[ $i ] );
					}
				}
			}

			if( $items_limit > 0 )
			{	// Limit items by widget setting:
				$items = array_slice( $items, 0, $items_limit );
			}
		}

		if( $this->disp_params['allow_filter'] || $items == 'all' )
		{	// Use ItemList when we need a filter or when all items are requested from collection:
			$ItemList = new ItemList2( $Blog, $Blog->get_timestamp_min(), $Blog->get_timestamp_max(), $items_limit );
			// Set additional debug info prefix for SQL queries in order to know what code executes it:
			$ItemList->query_title_prefix = get_class().' #'.$this->ID;

			if( $this->disp_params['items_type'] == 'default' )
			{	// Exclude items with types which are hidden by collection setting "Show post types":
				$filter_item_type = $Blog->get_setting( 'show_post_types' ) != '' ? '-'.$Blog->get_setting( 'show_post_types' ) : NULL;
			}
			else
			{	// Filter by selected Item Type:
				$filter_item_type = intval( $this->disp_params['items_type'] );
			}

			// Set default orders:
			$default_orders = array();
			$default_dirs = array();
			for( $order_index = 0; $order_index <= 2; $order_index++ )
			{
				$field_suffix = ( $order_index == 0 ? '' : '_'.$order_index );
				$widget_orderby = $this->disp_params['orderby'.$field_suffix];
				if( $widget_orderby == 'coll_default' )
				{	// Use order from collection:
					$coll_orderby = $Blog->get_setting( 'orderby'.$field_suffix );
					if( ! empty( $coll_orderby ) )
					{
						$default_orders[] = $coll_orderby;
						$default_dirs[] = $Blog->get_setting( 'orderdir'.$field_suffix );
					}
				}
				elseif( ! empty( $widget_orderby ) )
				{	// Use order from widget settings:
					$default_orders[] = $widget_orderby;
					$default_dirs[] = $this->disp_params['orderdir'.$field_suffix];
				}
			}

			// Set default filters:
			$ItemList->set_default_filters( array(
				'types'        => $filter_item_type,
				'post_ID_list' => is_array( $items ) ? implode( ',', $items ) : NULL,
				'orderby'      => implode( ',', $default_orders ),
				'order'        => implode( ',', $default_dirs ),
			) );

			if( $this->disp_params['allow_filter'] )
			{	// Filter items from request:
				$ItemList->load_from_Request( false );
			}

			// Run query:
			$ItemList->query();

			// Get IDs of items filtered by $ItemList:
			$items = $ItemList->get_page_ID_array();
		}

		return $items;
	}


	/**
	 * Get field template depending on type of the custom field
	 *
	 * @param string Template name
	 * @param string Custom field type: 'double', 'varchar', 'html', 'text', 'url', 'image', 'computed', 'separator'
	 * @return string HTML template
	 */
	function get_field_template( $template_name, $field_type = '' )
	{
		// Convert field types to non-devs names:
		$field_type = ( $field_type == 'double' ? 'numeric' : ( $field_type == 'varchar' ? 'string' : $field_type ) );

		if( isset( $this->disp_params['custom_fields_'.$field_type.'_'.$template_name] ) )
		{	// Use special template for current type if it is defined:
			return $this->disp_params['custom_fields_'.$field_type.'_'.$template_name];
		}
		elseif( isset( $this->disp_params['custom_fields_'.$template_name] ) )
		{	// Use generic template for all types:
			return $this->disp_params['custom_fields_'.$template_name];
		}

		// Unknown template:
		return '';
	}


	/**
	 * Callback function to sort custom fields by order field
	 *
	 * @param array Custom field A
	 * @param array Custom field B
	 * @return boolean
	 */
	function sort_custom_fields( $custom_field_a, $custom_field_b )
	{
		return $custom_field_a['order'] > $custom_field_b['order'];
	}


	/**
	 * Maybe be overriden by some widgets, depending on what THEY depend on..
	 *
	 * @return array of keys this widget depends on
	 */
	function get_cache_keys()
	{
		global $Collection, $Blog, $Item;

		// Get IDs of items which should be compared:
		$items = $this->get_items_IDs();

		$cache_keys = array(
				'wi_ID'        => $this->ID, // Have the widget settings changed ?
				'set_coll_ID'  => $Blog->ID, // Have the settings of the blog changed ? (ex: new skin)
				'item_ID'      => isset( $Item ) ? $Item->ID : NULL, // Has the Item page changed? (this is important for disp=single|page because $this$ and $parent$ resolve differently depending on item ID)
				'items'        => implode( ',', $items ), // Have the compared items changed? (Check firstly widget setting and then param from request) (this is important in case the same items are compared in different order)
				'meta_settings'=> 1, // Have meta settings(any item type) changed?
			);

		if( $this->disp_params['edit_links'] )
		{	// When the edit links setting is enabled we should invalidate keys per user,
			// because each user may has different permissions to edit the compared posts:
			global $current_User;
			$cache_keys['user_ID'] = ( is_logged_in() ? $current_User->ID : 0 ); // Has the current User changed?
		}

		// Add 1 cache key for each item that is being compared, in order to detect changes on each one:
		foreach( $items as $item_ID )
		{
			// 1 is a dummy value, only the key name is really important
			$cache_keys['item_'.$item_ID] = 1;
		}

		return $cache_keys;
	}
}

?>