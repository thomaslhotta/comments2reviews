<?php

/**
 * MyCRED hook for Reviews
 *
 * @author Thomas Lhotta
 */
class MyCRED_Review extends myCRED_Hook_Comments {
	/**
	 * @var bool
	 */
	protected $allow_execution = false;

	/**
	 * Construct
	 */
	function __construct( $hook_prefs, $type = 'mycred_default' ) {
		$args = array(
			'id'       => 'comments2reviews_review',
			'defaults' => array(
				'limits'   => array(
					'self_reply' => 0,
					'per_post'   => 10,
					'per_day'    => 0,
				),
				'approved' => array(
					'creds'  => 1,
					'log'    => '%plural% for Approved Review',
					'author' => 0,
				),
				'spam'     => array(
					'creds'  => '-5',
					'log'    => '%plural% deduction for Review marked as SPAM',
					'author' => 0,
				),
				'trash'    => array(
					'creds'  => '-1',
					'log'    => '%plural% deduction for deleted / unapproved Review',
					'author' => 0,
				),
			),
		);

		$grand_parent = new ReflectionClass( get_parent_class( get_parent_class( $this ) ) );
		$grand_parent->getConstructor()->invoke( $this, $args, $hook_prefs, $type );
	}

	/**
	 * Hook into WordPress
	 */
	public function run() {
		parent::run();

		// Prevent original mycred hook from running
		add_filter( 'mycred_comment_gets_cred', array( $this, 'disable_mycred_comment_cook' ), 10, 2 );
	}

	public function disable_mycred_comment_cook( $run, WP_Comment $comment ) {
		if ( ! $this->allow_execution && $this->is_review( $comment ) ) {
			return false;
		}

		return $run;
	}

	public function new_comment( $comment_id, $comment_status ) {
		// Marked SPAM
		if ( $comment_status === 'spam' )
			$this->comment_transitions( 'spam', 'unapproved', $comment_id );

		// Approved comment
		elseif ( $comment_status === 1 )
			$this->comment_transitions( 'approved', 'unapproved', $comment_id );

	}

	/**
	 * Comment Transitions
	 */
	public function comment_transitions( $new_status, $old_status, $comment ) {
		// Passing an integer instead of an object means we need to grab the comment object ourselves
		if ( ! is_object( $comment ) ) {
			$comment = get_comment( $comment );
		}

		if ( ! $comment instanceof WP_Comment ) {
			return;
		}

		if ( ! $this->is_review( $comment ) ) {
			return;
		}

		$this->allow_execution = true;

		parent::comment_transitions( $new_status, $old_status, $comment );

		$this->allow_execution = false;
	}

	/**
	 * @param WP_Comment $comment
	 *
	 * @return bool
	 */
	protected function is_review( WP_Comment $comment ) {
		return Comments_2_Reviews::get_instance()->comment_has_rating( $comment );
	}
}
