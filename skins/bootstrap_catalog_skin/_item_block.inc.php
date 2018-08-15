<?php
/**
 * This is the template that displays the item block: title, author, content (sub-template), tags, comments (sub-template)
 *
 * This file is not meant to be called directly.
 * It is meant to be called by an include in the main.page.php template (or other templates)
 *
 * b2evolution - {@link http://b2evolution.net/}
 * Released under GNU GPL License - {@link http://b2evolution.net/about/gnu-gpl-license}
 * @copyright (c)2003-2016 by Francois Planque - {@link http://fplanque.com/}
 *
 * @package evoskins
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

global $Item, $Skin, $app_version;

// Default params:
$params = array_merge( array(
		'feature_block'              => false,			// fp>yura: what is this for??
		// Classes for the <article> tag:
		'item_class'                 => 'evo_post evo_content_block',
		'item_type_class'            => 'evo_post__ptyp_',
		'item_status_class'          => 'evo_post__',
		'item_style'                 => '',
		// Controlling the title:
		'disp_title'                 => true,
		'item_title_line_before'     => '<div class="evo_post_title">',	// Note: we use an extra class because it facilitates styling
			'item_title_before'          => '<h2 class="center">',
			'item_title_after'           => '</h2>',
			'item_title_single_before'   => '<h1>',	// This replaces the above in case of disp=single or disp=page
			'item_title_single_after'    => '</h1>',
		'item_title_line_after'      => '</div>',
		// Controlling the content:
		'content_mode'               => 'auto',		// excerpt|full|normal|auto -- auto will auto select depending on $disp-detail
		'image_class'                => 'img-responsive',
		'image_size'                 => 'fit-1280x720',
		'author_link_text'           => 'auto',
	), $params );

/* Beginning of post display */
?>
<div class="evo_content_block<?php echo !$Item->is_intro() ? ' ' . $Skin->get_post_columns_count() : ''; ?>">
<article id="<?php $Item->anchor_id() ?>" class="<?php $Item->div_classes( $params ) ?>" lang="<?php $Item->lang() ?>"<?php
	echo empty( $params['item_style'] ) ? '' : ' style="'.format_to_output( $params['item_style'], 'htmlattr' ).'"' ?>>

<?php
if( $disp == 'single' )
{
?>
<nav>
	<ol class="breadcrumb">
		<li><a href="http://localhost/b2evolution-core-css/index.php/a/">Public Blog</a></li>
		<li class="active">Fun</li>
	</ol>
</nav>

<?php }

?>
	<header>

		<?php
		if( ! $Item->is_intro() ) :
		// Do not display "Sale" icon on Intro posts ?>
		<div class="floatright price_note"><span class="note status_private" data-toggle="tooltip" data-placement="top" title="This article is on sale!"><span>Sale!</span></span></div>
		<?php endif;

		$Item->locale_temp_switch(); // Temporarily switch to post locale (useful for multilingual blogs)

		if( ! $Item->is_intro() && ! in_array( $disp, array( 'single', 'page' ) ) )
		{
			if( $Item->get_number_of_images() )
			{	// If current item has cover image
					$Item->images( array(
						'before_images'            => '<div class="evo_post_images">',
						'before_image'             => '<div class="evo_post_images"><figure class="evo_image_block cover_image_wrapper">',
						'before_image_legend'      => '<figcaption class="evo_image_legend">',
						'after_image_legend'       => '</figcaption>',
						'after_image'              => '</figure></div>',
						'after_images'             => '</div>',
						'image_class'              => 'img-responsive',
						'image_size'               => $Skin->get_setting( 'thumbnail_size'),
						'limit'                    => 1,
						'image_link_to'            => 'original', // Can be 'original', 'single' or empty          <i class="fa fa-link" aria-hidden="true"></i>

						// We DO NOT want to display galleries here, only one cover image
						'gallery_image_limit'      => 0,
						'gallery_colls'            => 0,

						// We want ONLY cover image to display here
						'restrict_to_image_position' => 'cover,teaser,teaserperm,teaserlink,aftermore,inline',

						'links_sql_select'           => ', CASE '
								.'WHEN link_position = "cover" THEN "1" '
								.'WHEN link_position IN ("teaser","teaserperm","teaserlink") THEN "2" '
								.'WHEN link_position = "aftermore" THEN "3" '
								.'WHEN link_position = "inline" THEN "4" '
								// .'ELSE "99999999"' // Use this line only if you want to put the other position types at the end
							.'END AS position_order',
						'links_sql_orderby'          => 'position_order, link_order',
					) );
			}
			else
			{	// If current item does not have cover image
				echo '<figure class="evo_image_block"><img src="no-image.jpg"></figure>';
				// echo '<div class="noimage_wrapper"><i class="fa fa-file-image-o" aria-hidden="true"></i></div>';
			}
		}


		// ------- Title -------
		if( ! in_array( $disp, array( 'single', 'page' ) ) && $params['disp_title'] )
		{
			echo $params['item_title_line_before'];

			if( $disp == 'page' )
			{
				$title_before = $params['item_title_single_before'];
				$title_after = $params['item_title_single_after'];
			}
			else
			{
				$title_before = $params['item_title_before'];
				$title_after = $params['item_title_after'];
			}

			if( $disp != 'single' && $disp != 'posts' )
			{
				// POST TITLE:
				$Item->title( array(
						'before'    => $title_before,
						'after'     => $title_after,
						'link_type' => '#'
					) );

				// EDIT LINK
				$Item->edit_link( array(
						'before' => '<div class="'.button_class( 'group' ).'">',
						'after'  => '</div>',
						'text'   => $Item->is_intro() ? get_icon( 'edit' ).' '.T_('Edit Intro') : get_icon( 'edit' ).' '.T_('Edit'),
						'class'  => button_class( 'text' ),
					) );

				echo $params['item_title_line_after'];
			}
			else
			{
				// POST TITLE:
				$Item->title( array(
						'before'    => $title_before,
						'link_type' => '#'
					) );

			echo $title_after . $params['item_title_line_after'];
			}
		}
	?>
	</header>

	<?php
	if( $disp == 'single' )
	{
		?>
		<div class="evo_container evo_container__item_single">
		<?php
		// ------------------------- "Item Single" CONTAINER EMBEDDED HERE --------------------------
		// Display container contents:
		widget_container( 'item_single', array(
			'widget_context' => 'item',	// Signal that we are displaying within an Item
			// The following (optional) params will be used as defaults for widgets included in this container:
			// This will enclose each widget in a block:
			'block_start' => '<div class="evo_widget $wi_class$">',
			'block_end' => '</div>',
			// This will enclose the title of each widget:
			'block_title_start' => '<h3>',
			'block_title_end' => '</h3>',
			// Template params for "Item Title" widget
			'widget_item_title_params'  => array(
				'before' => '<div class="evo_post_title">'.( in_array( $disp, array( 'single', 'page' ) ) ? '<h1>' : '<h2>' ),
				'after' => ( in_array( $disp, array( 'single', 'page' ) ) ? '</h1>' : '</h2>' ).'</div>',
			),
			// Template params for "Item Excerpt" widget
			'widget_item_excerpt_params' => array(
					'excerpt_more_text' => NULL
				),
			// Template params for "Item Tags" widget
			'widget_item_tags_before'    => '<nav class="small post_tags">',
			'widget_item_tags_after'     => '</nav>',
			// Template params for subcontainer
			'override_params_for_subcontainer_row' => array(
					'override_params_for_item_custom_fields' => array(
							'custom_fields_table_start'      => '<table>',
							'custom_fields_table_end'        => '</table>',
							'custom_fields_row_header_field' => '<th>$field_title$$field_description_icon$:</th>',
							'custom_fields_value_default'    => '<td>$field_value$</td>',
						)
				),

			// Params for skin file "_item_content.inc.php"
			'widget_item_content_params' => array(
					'content_start_full_text'    => $disp == 'single' ? '<div class="evo_post__full_text panel panel-default clearfix">
							<div class="panel-heading"><h4 class="panel-title">'.T_('More Info').'</h4></div>
							<div class="panel-body">' : '',
					'content_end_full_text'      => $disp == 'single' ? '</div></div>' : '',
				),
			// Template params for "Custom Fields" widget:
			'custom_fields_table_start' => '<div class="evo_content_block panel panel-default">
					<div class="panel-heading"><h4 class="panel-title">'.T_('Data Sheet').'</h4></div>
					<table class="table table-hover">',
			'custom_fields_table_end'   => '</table></div>',

			'custom_fields_topleft_cell'               => '<td style="border:none"></td>',
			'custom_fields_col_header_item'            => '<th>$item_link$</th>',  // Note: we will also add reverse view later: 'custom_fields_col_header_field
			'custom_fields_row_header_field'           => '<th>$field_title$$field_description_icon$:</th>',
			'custom_fields_value_default'              => '<td>$field_value$</td>',
			'custom_fields_value_difference_highlight' => '<td class="$class$ bg-warning">$field_value$</td>',
			'custom_fields_value_green'                => '<td class="$class$ bg-success">$field_value$</td>',
			'custom_fields_value_red'                  => '<td class="$class$ bg-danger">$field_value$</td>',
			'custom_fields_row_end'                    => '</tr>',

			'custom_fields_description_icon_class'     => 'grey',
			// Template params for "Item Attachments" widget:
			'widget_item_attachments_params' => array(
					'limit_attach'       => 1000,
					'before'             => '<div class="evo_post_attachments"><h3>'.T_('Attachments').':</h3><ul class="evo_files">',
					'after'              => '</ul></div>',
					'before_attach'      => '<li class="evo_file">',
					'after_attach'       => '</li>',
					'before_attach_size' => ' <span class="evo_file_size">(',
					'after_attach_size'  => ')</span>',
				),
		) );
		// ----------------------------- END OF "Item Single" CONTAINER -----------------------------
		?>
		</div>
		<?php
	}
	else
	{
	// this will create a <section>
		// ---------------------- POST CONTENT INCLUDED HERE ----------------------
		skin_include( '_item_content.inc.php', $params );
		// Note: You can customize the default item content by copying the generic
		// /skins/_item_content.inc.php file into the current skin folder.
		// -------------------------- END OF POST CONTENT -------------------------
	// this will end a </section>
	}
	?>

	<?php
	if( ! in_array( $disp, array( 'single', 'page' ) ) )
	{
	?>
	<footer>

		<?php if( ! $Item->is_intro() )
		{ // Do not display "Sale" icon on Intro posts ?>
		<div class="evo_post__price center">
			<span class="regularprice"><?php echo '$'.$Item->get_setting( 'custom:price_usd' ).' ';?></span>
			<span class="oldprice">$19.99 </span>
			<span class="newprice">$12.99 </span>
			<?php
			$Item->edit_link( array(
					'before' => '<div class="'.button_class( 'group' ).'">',
					'after'  => '</div>',
					'text'   => $Item->is_intro() ? get_icon( 'edit' ).' '.T_('Edit Intro') : get_icon( 'edit' ),
					'class'  => button_class( 'text' ),
				) );
			?>
		</div>
		<?php }	?>

		<!--<nav class="post_comments_link">
		<?php
		/*
			// Link to comments, trackbacks, etc.:
			$Item->feedback_link( array(
				'type' => 'comments',
				'link_before' => '',
				'link_after' => '',
				'link_text_zero' => '#',
				'link_text_one' => '#',
				'link_text_more' => '#',
				'link_title' => '#',
				// fp> WARNING: creates problem on home page: 'link_class' => 'btn btn-default btn-sm',
				// But why do we even have a comment link on the home page ? (only when logged in)
			) );

			// Link to comments, trackbacks, etc.:
			$Item->feedback_link( array(
				'type' => 'trackbacks',
				'link_before' => ' &bull; ',
				'link_after' => '',
				'link_text_zero' => '#',
				'link_text_one' => '#',
				'link_text_more' => '#',
				'link_title' => '#',
			) );
			*/
		?>
		</nav>-->
		<?php
			// if( ! $Item->is_intro() && $disp != 'posts' ) : // Link to edit
			// $Item->edit_link( array( // Link to backoffice for editing
				// 'before' => '<div class="edit-link-wrapper"><div class="'.button_class( 'group' ).'">',
				// 'after'  => '</div></div>',
				// 'text'   => get_icon( 'edit' ).' '.T_('Edit'),
				// 'class'  => button_class( 'text' ),
			// ) );
			// endif;
		?>
	</footer>
	<?php } ?>

	<?php
		// ------------------ FEEDBACK (COMMENTS/TRACKBACKS) INCLUDED HERE ------------------
		skin_include( '_item_feedback.inc.php', array_merge( array(
				'before_section_title' => '<div class="clearfix"></div><h3 class="evo_comment__list_title">',
				'after_section_title'  => '</h3>',
			), $params ) );
		// Note: You can customize the default item feedback by copying the generic
		// /skins/_item_feedback.inc.php file into the current skin folder.
		// ---------------------- END OF FEEDBACK (COMMENTS/TRACKBACKS) ---------------------
	?>

	<?php
	if( evo_version_compare( $app_version, '6.7' ) >= 0 )
	{	// We are running at least b2evo 6.7, so we can include this file:
		// ------------------ WORKFLOW PROPERTIES INCLUDED HERE ------------------
		skin_include( '_item_workflow.inc.php' );
		// ---------------------- END OF WORKFLOW PROPERTIES ---------------------
	}
	?>

	<?php
	if( evo_version_compare( $app_version, '6.7' ) >= 0 )
	{	// We are running at least b2evo 6.7, so we can include this file:
		// ------------------ META COMMENTS INCLUDED HERE ------------------
		skin_include( '_item_meta_comments.inc.php', array(
				'comment_start'         => '<article class="evo_comment evo_comment__meta panel panel-default">',
				'comment_end'           => '</article>',
				'comment_post_display'	=> false,	// Do we want ot display the title of the post we're referring to?
				'comment_post_before'   => '<h3 class="evo_comment_post_title">',
				'comment_post_after'    => '</h3>',
				'comment_title_before'  => '<div class="panel-heading"><h4 class="evo_comment_title panel-title">',
				'comment_title_after'   => '</h4></div><div class="panel-body">',
				'comment_avatar_before' => '<span class="evo_comment_avatar">',
				'comment_avatar_after'  => '</span>',
				'comment_rating_before' => '<div class="evo_comment_rating">',
				'comment_rating_after'  => '</div>',
				'comment_text_before'   => '<div class="evo_comment_text">',
				'comment_text_after'    => '</div>',
				'comment_info_before'   => '<footer class="evo_comment_footer clear text-muted"><small>',
				'comment_info_after'    => '</small></footer></div>',
			) );
		// ---------------------- END OF META COMMENTS ---------------------
	}
	?>

	<?php
		locale_restore_previous();	// Restore previous locale (Blog locale)
	?>
</article>

<?php echo '</div>'; // End of post display ?>