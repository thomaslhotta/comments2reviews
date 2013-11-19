<?php
function is_review( $comment = null ) {
	return Comments_2_Reviews::get_instance()->comment_has_rating( $comment );
}

function get_review_rating( $comment, $echo = false ) {
	return Comments_2_Reviews::get_instance()->get_comment_rating( $comment, $echo );
}

function get_post_rating ( $post = null, $echo = true, $microdata = true ) {
	return Comments_2_Reviews::get_instance()->get_post_rating( $post, $echo, $microdata );
}

function get_post_rating_stats( $post = null, $echo = true ) {
	return Comments_2_Reviews::get_instance()->get_post_rating_stats( $post, $echo );
}

