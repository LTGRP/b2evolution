<?php
/**
 * This is the template that displays the site map (the real one, not the XML thing) for a blog
 *
 * This file is not meant to be called directly.
 * It is meant to be called by an include in the main.page.php template.
 * To display the archive directory, you should call a stub AND pass the right parameters
 * For example: /blogs/index.php?disp=postidx
 *
 * b2evolution - {@link http://b2evolution.net/}
 * Released under GNU GPL License - {@link http://b2evolution.net/about/gnu-gpl-license}
 * @copyright (c)2003-2018 by Francois Planque - {@link http://fplanque.com/}
 *
 * @package evoskins
 * @subpackage bootstrap_forums
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

echo '<div class="forums_table_search">';

// --------------------------------- START OF COMMON LINKS --------------------------------
skin_widget( array(
		// CODE for the widget:
		'widget' => 'coll_search_form',
		// Optional display params
		'block_start'                => '<div class="panel panel-default"><div class="panel-heading">',
		'block_end'                  => '</div></div>',
		'block_display_title'        => false,
		'disp_search_options'        => 0,
		'search_class'               => 'extended_search_form',
		'search_input_before'        => '<div class="col-sm-12"><div class="input-group">',
		'search_input_after'         => '',
		'search_submit_before'       => '<span class="input-group-btn">',
		'search_submit_after'        => '</span></div></div>',
		'search_input_author_before' => '<div class="col-sm-12 col-md-12 col-lg-5">',
		'search_input_author_after'  => '</div>',
		'search_input_age_before'    => '<div class="col-sm-12 col-md-12 col-lg-4">',
		'search_input_age_after'     => '</div>',
		'search_input_type_before'   => '<div class="col-sm-12 col-md-12 col-lg-3">',
		'search_input_type_after'    => '</div>',
		'search_line_before'         => '<div style="text-align: left; margin: .5em 0;" class="row">',
		'search_line_after'          => '</div>',
		'search_template'            => '$input_keywords$$button_search$'."\n".'$input_author$$input_age$$input_content_type$',
		'use_search_disp'            => 1,
		'button'                     => T_('Search')
	) );
// ---------------------------------- END OF COMMON LINKS ---------------------------------

// Display the search result
search_result_block( array(
		'title_prefix_post'     => T_('Topic: '),
		'title_prefix_comment'  => /* TRANS: noun */ T_('Reply:'),
		'title_prefix_category' => T_('Forum').': ',
		'title_prefix_tag'      => /* TRANS: noun */ T_('Tag').': ',
		'block_start' => '<div class="evo_search_list">',
		'block_end'   => '</div>',
		'row_start'   => '<div class="evo_search_list__row">',
		'row_end'     => '</div>',
		'pagination'  => $params['pagination']
	) );

echo '</div>';
?>