<?php
/**
 * This is the template that displays the links to the last comments for a blog
 *
 * This file is not meant to be called directly.
 * It is meant to be called by an include in the _main.php template.
 * To display a feedback, you should call a stub AND pass the right parameters
 * For example: /blogs/index.php?disp=comments
 *
 * b2evolution - {@link http://b2evolution.net/}
 * Released under GNU GPL License - {@link http://b2evolution.net/about/license.html}
 * @copyright (c)2003-2006 by Francois PLANQUE - {@link http://fplanque.net/}
 *
 * @package evoskins
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );


$CommentList = & new CommentList( $blog, "'comment','trackback','pingback'", array('published'), '',	'',	'DESC',	'',	20 );

$CommentList->display_if_empty( '<div class="bComment"><p>'.T_('No comment yet...').'</p></div>' );

while( $Comment = & $CommentList->get_next() )
{ // Loop through comments:
	// Load comment's Item object:
	$Comment->get_Item();
	?>
	<!-- ========== START of a COMMENT ========== -->
	<?php $Comment->anchor() ?>
	<div class="bComment">
		<h3 class="bTitle">
			<?php echo T_('In response to:') ?>
			<?php $Comment->Item->permanent_link( '#title#' ) ?>
		</h3>
		<div class="bCommentTitle">
			<?php $Comment->author() ?>
			<?php $Comment->author_url( '', ' &middot; ', '' ) ?>
		</div>
		<div class="bCommentText">
			<?php $Comment->content() ?>
		</div>
		<div class="bCommentSmallPrint">
			<?php $Comment->permanent_link( '#', '#', 'permalink_right' ); ?>
			<?php $Comment->date() ?> @ <?php $Comment->time( 'H:i' ) ?>
			<?php $Comment->edit_link( ' &middot; ' ) /* Link to backoffice for editing */ ?>
			<?php $Comment->delete_link( ' &middot; ' ); /* Link to backoffice for deleting */ ?>
		</div>
	</div>
	<!-- ========== END of a COMMENT ========== -->
	<?php
}	// End of comment loop.


/*
 * $Log$
 * Revision 1.29  2006/12/14 21:56:25  fplanque
 * minor optimization
 *
 * Revision 1.28  2006/11/20 22:15:50  blueyed
 * whitespace/comment format
 *
 * Revision 1.27  2006/07/06 19:56:29  fplanque
 * no message
 *
 * Revision 1.26  2006/05/30 20:32:57  blueyed
 * Lazy-instantiate "expensive" properties of Comment and Item.
 *
 * Revision 1.25  2006/04/19 13:05:22  fplanque
 * minor
 *
 * Revision 1.24  2006/04/18 19:29:52  fplanque
 * basic comment status implementation
 *
 * Revision 1.23  2006/04/11 21:22:26  fplanque
 * partial cleanup
 *
 */
?>