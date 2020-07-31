<?php
/**
 * This file implements the Slug class.
 *
 * This file is part of the b2evolution/evocms project - {@link http://b2evolution.net/}.
 * See also {@link https://github.com/b2evolution/b2evolution}.
 *
 * @license GNU GPL v2 - {@link http://b2evolution.net/about/gnu-gpl-license}
 *
 * @copyright (c)2003-2020 by Francois Planque - {@link http://fplanque.com/}.
*
 * @license http://b2evolution.net/about/license.html GNU General Public License (GPL)
 *
 * @package evocore
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

load_class( '_core/model/dataobjects/_dataobject.class.php', 'DataObject' );


/**
 * Slug Class
 *
 * @package evocore
 */
class Slug extends DataObject
{
	var $title;

	var $type;

	var $itm_ID;

	var $cat_ID;

	/**
	 * Constructor
	 *
	 * @param object table Database row
	 */
	function __construct( $db_row = NULL )
	{
		// Call parent constructor:
		parent::__construct( 'T_slug', 'slug_', 'slug_ID' );

		if( $db_row != NULL )
		{
			$this->ID = $db_row->slug_ID;
			$this->title = $db_row->slug_title;
			$this->type = $db_row->slug_type;
			$this->itm_ID = $db_row->slug_itm_ID;
			$this->cat_ID = $db_row->slug_cat_ID;
		}
	}


	/**
	 * Get delete restriction settings
	 *
	 * @return array
	 */
	static function get_delete_restrictions()
	{
		return array(
				array( 'table'=>'T_items__item', 'fk'=>'post_canonical_slug_ID', 'fk_short'=>'canonical_slug_ID', 'msg'=>T_('%d related post') ),
				array( 'table'=>'T_items__item', 'fk'=>'post_tiny_slug_ID', 'fk_short'=>'tiny_slug_ID', 'msg'=>T_('%d related post') ),
				array( 'table'=>'T_categories', 'fk'=>'cat_canonical_slug_ID', 'fk_short'=>'canonical_slug_ID', 'msg'=>T_('%d related category') ),
			);
	}


	/**
	 * Load data from Request form fields.
	 *
	 * @return boolean true if loaded data seems valid.
	 */
	function load_from_Request()
	{
		global $Messages;

		// Get old data for checking:
		$old_object = $this->get_object();
		$old_type = $this->get( 'type' );
		$old_cat_ID = $this->get( 'cat_ID' );
		$old_itm_ID = $this->get( 'itm_ID' );

		// title
		$slug_title = param( 'slug_title', 'string', true );
		if( empty( $this->ID ) || $slug_title != $this->get( 'title' ) )
		{	// Check unique slug title only for new creating slug and if title was really changed:
			$slug_title = urltitle_validate( $slug_title, $slug_title, 0, true, 'slug_title', 'slug_ID', 'T_slug' );
			// Update the submitted param with validated value in order to correct checking by regexp below:
			set_param( 'slug_title', $slug_title );
		}
		if( $this->dbexists( 'slug_title', $slug_title ) )
		{
			$Messages->add( sprintf( T_('The slug &laquo;%s&raquo; already exists.'), $slug_title ), 'error' );
		}
		// Added in May 2017; but old slugs are not converted yet.
		else
		{	// Display error if slug title doesn't contain at least 1 non-numeric character:
			param_check_regexp( 'slug_title', '#^[^a-z0-9]*[0-9]*[^a-z0-9]*$#i', T_('All slugs must contain at least 1 non-numeric character.'), NULL, false, false );
		}
		$this->set( 'title', $slug_title );

		// type
		$this->set_string_from_param( 'type', true );

		// object ID:
		$object_id = param( 'slug_object_ID', 'string' );
		// All DataObject ID must be a number
		if( ! is_number( $object_id ) && $this->get( 'type' ) != 'help' )
		{ // not a number
			$Messages->add( T_('Object ID must be a number!'), 'error' );
			return false;
		}

		switch( $this->get( 'type' ) )
		{
			case 'cat':
				// Category slug:
				$ChapterCache = & get_ChapterCache();
				if( $ChapterCache->get_by_ID( $object_id, false, false ) )
				{	// Set new category ID and reset item ID to NULL:
					$this->set( 'itm_ID', NULL, true );
					$this->set_from_Request( 'cat_ID', 'slug_object_ID', true );
				}
				else
				{	// Wrong category:
					$Messages->add( T_('Object ID must be a valid Category ID!'), 'error' );
				}
				break;

			case 'item':
				// Item slug:
				$ItemCache = & get_ItemCache();
				if( $ItemCache->get_by_ID( $object_id, false, false ) )
				{	// Set new item ID and reset category ID to NULL:
					$this->set( 'cat_ID', NULL, true );
					$this->set_from_Request( 'itm_ID', 'slug_object_ID', true );
				}
				else
				{	// Wrong item:
					$Messages->add( T_('Object ID must be a valid Post ID!'), 'error' );
				}
				break;

			case 'help':
				// Help slug:
				// Reset category and item IDs to NULL:
				$this->set( 'cat_ID', NULL, true );
				$this->set( 'itm_ID', NULL, true );
				break;
		}

		if( $this->ID > 0 &&
		    $old_object->get( 'canonical_slug_ID' ) == $this->ID &&
		    ( $old_type != $this->get( 'type' ) ||
		      $old_cat_ID != $this->get( 'cat_ID' ) ||
		      $old_itm_ID != $this->get( 'itm_ID' )
		    ) )
		{	// If object has been changed but this slug is used as canonical slug and cannot be deleted and changed to another object:
			$Messages->add( T_('You cannot change an object of this slug because it is used as canonical slug!'), 'error' );
		}

		return ! param_errors_detected();
	}


	/**
	 * Create a link to the related oject.
	 *
	 * @param string Display text - if NULL, will get the object title
	 * @param string type values:
	 * 		- 'admin_view': link to this object admin interface view
	 * 		- 'public_view': link to this object public interface view (on blog)
	 * 		- 'edit': link to this object edit screen
	 * @return string link to related object, or empty if no related object, or url does not exist.
	 */
	function get_link_to_object( $link_text = NULL, $type = 'admin_view' )
	{
		if( $object = $this->get_object() )
		{
			if( ! isset( $link_text ) )
			{ // link_text is not set -> get object title for link text
				$link_text = $this->get_object_title();
			}
			// get respective url
			$link_url = $this->get_url_to_object( $type );
			if( $link_url != '' )
			{ // URL exists
				// add link title
				if( $type == 'public_view' || $type == 'admin_view' )
				{
					$link_title = ' title="'.sprintf( T_('View this %s...'), $this->get( 'type') ).'"';
				}
				elseif( $type == 'edit' )
				{
					$link_title = ' title="'.sprintf( T_('Edit this %s...'), $this->get( 'type') ).'"';
				}
				else
				{
					$link_title = '';
				}
				// return created link
				return '<a href="'.$link_url.'"'.$link_title.'>'.$link_text.'</a>';
			}
		}
		return '';
	}


	/**
	 * Create a link to the related oject (in the admin!).
	 *
	 * @param string type values:
	 * 		- 'admin_view': url to this item admin interface view
	 * 		- 'public_view': url to this item public interface view (on blog)
	 * 		- 'edit': url to this item edit screen
	 * @return string URL to related object, or empty if no related object or URL does not exist.
	 */
	function get_url_to_object( $type = 'admin_view' )
	{
		if( $object = $this->get_object() )
		{ // related object exists
			// asimo> Every slug target class need to have get_url() function
			return $object->get_url( $type );
		}
		return '';
	}


	/**
	 * Get title of current object
	 *
	 * @return string
	 */
	function get_object_title()
	{
		if( ! ( $object = & $this->get_object() ) )
		{	// No object:
			return '';
		}

		switch( $this->get( 'type' ) )
		{
			case 'cat':
				return $object->get( 'name' );

			case 'item':
				return $object->get( 'title' );
		}
	}


	/**
	 * Get link to restricted object
	 *
	 * Used when try to delete a slug, which is another object slug
	 *
	 * @param array restriction
	 * @return string|boolean Message with link to objects,
	 *                        Empty string if no restriction for current table,
	 *                        FALSE - if no rule for current table
	 */
	function get_restriction_link( $restriction )
	{
		if( $object = & $this->get_object() )
		{	// If object(Chapter or Item) exists:
			// Check if this is a restriction for this slug or not!
			if( ( ( $this->get( 'type' ) == 'cat' && $restriction['table'] == 'T_categories' ) ||
			      ( $this->get( 'type' ) == 'item' && $restriction['table'] == 'T_items__item' ) ) &&
			    $object->get( $restriction['fk_short'] ) == $this->ID )
			{
				$restriction_link = $this->get_link_to_object();
			}
		}
		if( isset( $restriction_link ) )
		{	// There are restrictions:
			return sprintf( $restriction['msg'].'<br/>'.str_replace( '%', '%%', $restriction_link ), 1 );
		}

		// No restriction:
		return '';
	}


	/**
	 * Get linked object.
	 * @return object
	 */
	function & get_object()
	{
		switch( $this->get( 'type' ) )
		{ // can be different type of object
			case 'cat':
				$ChapterCache = & get_ChapterCache();
				$Chapter = & $ChapterCache->get_by_ID( $this->get( 'cat_ID' ), false, false );
				return $Chapter;

			case 'item':
				$ItemCache = & get_ItemCache();
				$Item = & $ItemCache->get_by_ID( $this->get( 'itm_ID' ), false, false );
				return $Item;

			case 'help':
				$r = false;
				return $r;

			default:
				// not defined restriction
				debug_die( 'Slug::get_object: Unhandled object type: '.$this->dget( 'type', 'htmlspecialchars' ) );
		}
	}


	/**
	 * Check if this slug may be deleted
	 *
	 * @return boolean
	 */
	function may_be_deleted()
	{
		if( empty( $this->ID ) )
		{	// Slug must be stored in DB:
			return false;
		}

		// Get object(Chapter or Item) of this slug:
		$object = $this->get_object();

		switch( $this->get( 'type' ) )
		{
			case 'cat':
				// This slug cannot be deleted when it is used as canonical slug for the Chapter/Catagory:
				return ( $object->get( 'canonical_slug_ID' ) != $this->ID );

			case 'item':
				// This slug cannot be deleted when it is used as canonical or tiny slug for the Item/Post:
				return ( $object->get( 'canonical_slug_ID' ) != $this->ID && $object->get( 'tiny_slug_ID' ) != $this->ID );

			default:
				return true;
		}
	}
}

?>