<?php

/**
 * MyCRED hook for Reviews 
 * 
 * Based on MyCred Commen hook.
 * 
 * @author Thomas Lhotta
 *
 */
class MyCREDReview extends myCRED_Hook
{
    /**
     * Construct
     */
    function __construct( $hook_prefs ) {
        
        parent::__construct( array(
            'id'       => 'comments2reviews_review',
            'defaults' => array(
					'limits'   => array(
						'self_reply' => 0,
						'per_post'   => 10,
						'per_day'    => 0
					),
					'approved' => array(
						'creds'   => 1,
						'log'     => '%plural% for Approved Comment'
					),
					'spam'     => array(
						'creds'   => '-5',
						'log'     => '%plural% deduction for Comment marked as SPAM'
					),
					'trash'    => array(
						'creds'   => '-1',
						'log'     => '%plural% deduction for deleted / unapproved Comment'
					)
				)
			), $hook_prefs ); 
    }
    
    
    /**
     * Hook into WordPress
     */
    public function run() {
        add_action( 'comments2reviews_review',  array( $this, 'review' ), 10, 2 );
        add_action( 'transition_comment_status', array( $this, 'transition_comment_status' ), 9, 3 );
        
    }
    
    
    /**
     * Check if the user qualifies for points
     */
    public function review( $comment_id, $comment_status ) {
        $this->new_comment($comment_id, $comment_status);
    
        // Prevent standard comments hook.
        $hook = $this->get_comment_hook($GLOBALS['wp_filter'], 'comment_post');
        if ( !is_null( $hook ) ) {
            remove_action('comment_post', array( $hook, 'new_comment' ));
        }
    }
    
    
    /**
     * @param string $new_status
     * @param string $old_status
     * @param integer $comment
     */
    public function transition_comment_status( $new_status, $old_status, $comment ) {
        $this->comment_transitions($new_status, $old_status, $comment);
        
        // Prevent standard comments transition hook.
        $hook = $this->get_comment_hook($GLOBALS['wp_filter'], 'transition_comment_status');
        if ( !is_null( $hook ) ) {
            remove_action('transition_comment_status', array( $hook, 'comment_transitions' ));
        }
        
    }
    
    
    /**
	 * If reviews are approved without moderation, we apply the corresponding method
	 * or else we will wait till the appropriate instance.
	 */
	public function new_comment( $comment_id, $comment_status ) {
		// Marked SPAM
		if ( $comment_status === 'spam' && $this->prefs['spam'] != 0 )
			$this->comment_transitions( 'spam', 'unapproved', $comment_id );
		// Approved comment
		elseif ( $comment_status == '1' && $this->prefs['approved'] != 0 )
			$this->comment_transitions( 'approved', 'unapproved', $comment_id );
	}
	
	/**
	 * Comment Transitions
	 */
	public function comment_transitions( $new_status, $old_status, $comment ) {
		// Passing an integer instead of an object means we need to grab the comment object ourselves
		if ( !is_object( $comment ) )
			$comment = get_comment( $comment );

		// Ignore Pingbacks or Trackbacks
		if ( !empty( $comment->comment_type ) ) return;

		// Logged out users miss out
		if ( $comment->user_id == 0 ) return;

		// Check if user should be excluded
		if ( $this->core->exclude_user( $comment->user_id ) === true ) return;

		// Check if we are allowed to comment our own comment
		if ( $this->prefs['limits']['self_reply'] != 0 && $comment->comment_parent != 0 ) {
			$parent = get_comment( $comment->comment_parent );
			if ( $parent->user_id == $comment->user_id ) return;
		}

		$reference = '';

		// Approved comments
		if ( $this->prefs['approved']['creds'] != 0 && $new_status == 'approved' ) {
			// New approved comment
			if ( $old_status == 'unapproved' || $old_status == 'hold' ) {
				// Enforce limits
				if ( $this->user_exceeds_limit( $comment->user_id, $comment->comment_post_ID ) ) return;

				$reference = 'approved_comment';
				$points = $this->prefs['approved']['creds'];
				$log = $this->prefs['approved']['log'];
			}

			// Marked as "Not Spam"
			elseif ( $this->prefs['spam']['creds'] != 0 && $old_status == 'spam' ) {
				$reference = 'approved_comment';

				// Reverse points
				if ( $this->prefs['spam']['creds'] < 0 )
					$points = abs( $this->prefs['spam']['creds'] );
				else
					$points = $this->prefs['spam']['creds'];

				$log = $this->prefs['approved']['log'];
			}

			// Returned comment from trash
			elseif ( $this->prefs['trash']['creds'] != 0 && $old_status == 'trash' ) {
				$reference = 'approved_comment';
				// Reverse points
				if ( $this->prefs['trash']['creds'] < 0 )
					$points = abs( $this->prefs['trash']['creds'] );
				else
					$points = $this->prefs['trash']['creds'];
				
				$log = $this->prefs['approved']['log'];
			}
		}

		// Spam comments
		elseif ( $this->prefs['spam'] != 0 && $new_status == 'spam' ) {
			$reference = 'spam_comment';
			$points = $this->prefs['spam']['creds'];
			$log = $this->prefs['spam']['log'];
		}

		// Trashed comments
		elseif ( $this->prefs['trash'] != 0 && $new_status == 'trash' ) {
			$reference = 'deleted_comment';
			$points = $this->prefs['trash']['creds'];
			$log = $this->prefs['trash']['log'];
		}

		// Unapproved comments
		elseif ( $new_status == 'unapproved' && $old_status == 'approved' ) {
			$reference = 'deleted_comment';
			// Reverse points
			if ( $this->prefs['approved']['creds'] < 0 )
				$points = abs( $this->prefs['approved']['creds'] );
			else
				$points = $this->prefs['approved']['creds'];

			$log = $this->prefs['trash']['log'];
		}

		if ( empty( $reference ) ) return;

		// Execute
		$this->core->add_creds(
			$reference,
			$comment->user_id,
			$points,
			$log,
			$comment->comment_ID,
			array( 'ref_type' => 'comment' )
		);
	}

	/**
	 * Check if user exceeds limit
	 */
	public function user_exceeds_limit( $user_id = NULL, $post_id = NULL ) {
		if ( !isset( $this->prefs['limits'] ) ) return false;

		// Prep
		$today = date_i18n( 'Y-m-d' );

		// First we check post limit
		if ( $this->prefs['limits']['per_post'] > 0 ) {
			$post_limit = 0;
			// Grab limit
			$limit = get_user_meta( $user_id, 'mycred_comment_limit_post', true );
			// Apply default if none exist
			if ( empty( $limit ) ) $limit = array( $post_id => $post_limit );

			// Check if post_id is in limit array
			if ( array_key_exists( $post_id, $limit ) ) {
				$post_limit = $limit[$post_id];

				// Limit is reached
				if ( $post_limit >= $this->prefs['limits']['per_post'] ) return true;
			}

			// Add / Replace post_id counter with an incremented value
			$limit[$post_id] = $post_limit+1;
			// Save
			update_user_meta( $user_id, 'mycred_comment_limit_post', $limit );
		}

		// Second we check daily limit
		if ( $this->prefs['limits']['per_day'] > 0 ) {
			$daily_limit = 0;
			// Grab limit
			$limit = get_user_meta( $user_id, 'mycred_comment_limit_day', true );
			// Apply default if none exist
			if ( empty( $limit ) ) $limit = array();

			// Check if todays date is in limit
			if ( array_key_exists( $today, $limit ) ) {
				$daily_limit = $limit[$today];

				// Limit is reached
				if ( $daily_limit >= $this->prefs['limits']['per_day'] ) return true;
			}
			// Today is not in limit array so we reset to remove other dates
			else {
				$limit = array();
			}

			// Add / Replace todays counter with an imcremented value
			$limit[$today] = $daily_limit+1;
			// Save
			update_user_meta( $user_id, 'mycred_comment_limit_day', $limit );
		}

		return false;
	}

		/**
		 * Preferences for Commenting Hook
		 * @since 0.1
		 * @version 1.0
		 */
		public function preferences() {
		    $slug = $slug = Comments_2_Reviews::get_instance()->get_plugin_slug();
		    
			$prefs = $this->prefs;
		    ?>

				<label class="subheader" for="<?php echo $this->field_id( array( 'approved' => 'creds' ) ); ?>"><?php _e( 'Approved Review', $slug ); ?></label>
				<ol>
					<li>
						<div class="h2"><input type="text" name="<?php echo $this->field_name( array( 'approved' => 'creds' ) ); ?>" id="<?php echo $this->field_id( array( 'approved' => 'creds' ) ); ?>" value="<?php echo $this->core->format_number( $prefs['approved']['creds'] ); ?>" size="8" /></div>
					</li>
					<li class="empty">&nbsp;</li>
					<li>
						<label for="<?php echo $this->field_id( array( 'approved' => 'log' ) ); ?>"><?php _e( 'Log template', $slug ); ?></label>
						<div class="h2"><input type="text" name="<?php echo $this->field_name( array( 'approved' => 'log' ) ); ?>" id="<?php echo $this->field_id( array( 'approved' => 'log' ) ); ?>" value="<?php echo $prefs['approved']['log']; ?>" class="long" /></div>
						<span class="description"><?php _e( 'Available template tags: General, Review', $slug ); ?></span>
					</li>
				</ol>
				<label class="subheader" for="<?php echo $this->field_id( array( 'spam' => 'creds' ) ); ?>"><?php _e( 'Review Marked SPAM', $slug ); ?></label>
				<ol>
					<li>
						<div class="h2"><input type="text" name="<?php echo $this->field_name( array( 'spam' => 'creds' ) ); ?>" id="<?php echo $this->field_id( array( 'spam' => 'creds' ) ); ?>" value="<?php echo $this->core->format_number( $prefs['spam']['creds'] ); ?>" size="8" /></div>
					</li>
					<li class="empty">&nbsp;</li>
					<li>
						<label for="<?php echo $this->field_id( array( 'spam' => 'log' ) ); ?>"><?php _e( 'Log template', $slug ); ?></label>
						<div class="h2"><input type="text" name="<?php echo $this->field_name( array( 'spam' => 'log' ) ); ?>" id="<?php echo $this->field_id( array( 'spam' => 'log' ) ); ?>" value="<?php echo $prefs['spam']['log']; ?>" class="long" /></div>
						<span class="description"><?php _e( 'Available template tags: General, Review', $slug ); ?></span>
					</li>
				</ol>
				<label class="subheader" for="<?php echo $this->field_id( array( 'trash' => 'creds' ) ); ?>"><?php _e( 'Trashed / Unapproved Reviews', $slug ); ?></label>
				<ol>
					<li>
						<div class="h2"><input type="text" name="<?php echo $this->field_name( array( 'trash' => 'creds' ) ); ?>" id="<?php echo $this->field_id( array( 'trash' => 'creds' ) ); ?>" value="<?php echo $this->core->format_number( $prefs['trash']['creds'] ); ?>" size="8" /></div>
					</li>
					<li class="empty">&nbsp;</li>
					<li>
						<label for="<?php echo $this->field_id( array( 'trash' => 'log' ) ); ?>"><?php _e( 'Log template', $slug ); ?></label>
						<div class="h2"><input type="text" name="<?php echo $this->field_name( array( 'trash' => 'log' ) ); ?>" id="<?php echo $this->field_id( array( 'trash' => 'log' ) ); ?>" value="<?php echo $prefs['trash']['log']; ?>" class="long" /></div>
						<span class="description"><?php _e( 'Available template tags: General, Review', $slug ); ?></span>
					</li>
				</ol>
        <?php		unset( $this );
	}
		
    
    
    /**
     * Returns the existing myCRED comment hook
     * 
     * @param array $filters
     * @return object|null
     */
    public function get_comment_hook( $filters, $tag )
    {
        foreach ( $filters[$tag][10] as $filter) {
            foreach ($filter as $entry) {
                if ( is_array($entry) ) {
                    $o =  $entry[0];
                    if ( $o instanceof myCRED_Hook_Comments ) {
                       return $o;
                    }
                }
                 
            }
        }
        
        return null;
    }
}
 