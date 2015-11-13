<?php

/**
 * Class that modifies different WordPress queries
 *
 * @author Thomas Lhotta
 */
class C2R_Query {
	/**
	 * @var C2R_Settings
	 */
	protected $settings;

	public function __construct( C2R_Settings $settings ) {
		$this->settings = $settings;

		add_filter( 'pre_get_posts', array( $this, 'modify_query' ) );
		add_filter( 'the_comments', array( $this, 'comments_array' ), 10, 2 );

		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
	}

	/**
	 * Allows posts to be ordered by ratings.
	 *
	 * @param WP_Query $query
	 */
	public function modify_query( WP_Query $query ) {
		if ( $query->get( 'orderby' ) != 'rating' ) {
			return;
		}

		$query->set( 'orderby', 'has_rating' );

		// Ensure that even posts without ratings are included.
		$query->set(
			'meta_query',
			array(
				'relation'   => 'OR',
				'no_rating'  => array(
					'key'     => 'rating_mean',
					'compare' => 'NOT EXISTS',
					'type'    => 'NUMERIC',
				),
				'has_rating' => array(
					'key'     => 'rating_mean',
					'compare' => 'EXISTS',
					'type'    => 'NUMERIC',
				),
			)
		);
	}

	/**
	 * Filter comments to only show reviews.
	 *
	 * @param array $comments
	 * @param WP_Comment_Query $query
	 *
	 * @return array
	 */
	public function comments_array( $comments, WP_Comment_Query $query ) {
		$rating = get_query_var( 'rating' );
		if ( empty( $rating ) ) {
			return $comments;
		}

		if ( empty( $query->query_vars['post_id'] ) ) {
			return $comments;
		}

		if ( ! in_array( get_post_type( $query->query_vars['post_id'] ), $this->settings->get_enabled_post_types() ) ) {
			return $comments;
		}

		foreach ( $comments as $key => $comment ) {
			$meta = get_comment_meta( $comment->comment_ID, 'rating', true );
			if ( empty( $rating ) ) {
				continue;
			}

			if ( $rating != $meta ) {
				unset( $comments[ $key ] );
			}
		}

		return $comments;
	}

	public function add_query_vars( $vars ) {
		$vars[] = 'rating';

		return $vars;
	}
}
