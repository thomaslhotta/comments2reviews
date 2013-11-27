<?php 

/**
 * Class that modifies different wordpress queries
 * 
 * @author Thomas Lhotta
 */
class C2R_Query 
{
	/**
	 * @var C2R_Settings
	 */
	protected $settings;
	
    public function __construct( C2R_Settings $settings )
    {
    	$this->settings = $settings;
    	
        add_filter( 'pre_get_posts', array( $this, 'modify_query' ) );
        add_filter( 'comments_array', array( $this, 'comments_array' ) , 10 ,2 );
        
        add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
    }
    
    /**
     * Allows posts to be ordered by ratings.
     * 
     * @param WP_Query $query
     */
    public function modify_query( WP_Query $query ) 
    {
        if ( $query->get( 'orderby' ) != 'rating' ) {
            return;
        }
        
        $query->set( 'orderby' , 'meta_value_num' );
        $query->set( 'meta_key' , 'rating_mean' );
        // Ensure that even posts without ratings are included.
        $query->set(
        	'meta_query',
        	array(
        		'relation' => 'OR',
        		array(
            		'key'     => 'rating_mean',
			        'value'   => '',
			        'compare' => 'NOT EXISTS',
        		),
        		array(
        		    'key'     => 'rating_mean',
        		    'value'   => '',
        		    'compare' => 'EXISTS',
        		)
         	)
		);  

    }
    
    /**
     * Filter comments to only show reviews.
     * 
     * @param array $comments
     * @param integer $post
     * @return array
     */
    public function comments_array( $comments, $post )
    {
    	$post_types = $this->settings->get_enabled_post_types();
    	
    	$rating = get_query_var( 'rating' );
    	
    	if ( '' == $rating || !in_array( get_post_type( $post ) , $post_types ) ) {
    		return $comments;
    	}
    	
    	$review_ids = array(); 
    	
    	// Find reviews, unset non review comments
    	foreach ( $comments as $key => $comment ) {
    		if ( 0 == $comment->comment_parent ) {
    			$meta = get_comment_meta( $comment->comment_ID, 'rating', true );
    			
    			// Incude reviews if the rating matches or no rating number
    			// was given but the comment has a rating.
    			if ( ( $meta && $meta == $rating) || ( $meta && 'all' == $rating ) ) {
    				$review_ids[] = $comment->comment_ID;
    			} else {
    				unset( $comments[$key] );
    			}
    		}
    	}
    	
    	// Remove orphaned answers
    	foreach ( $comments as $key => $comment ) {
    		if ( 0 != $comment->comment_parent && !in_array( $comment->comment_parent, $review_ids ) ) {
    			unset( $comments[$key] );
    		}
    	}
    	
    	return $comments;
    }
    
    public function add_query_vars( $vars ) 
    {
    	$vars[] = 'rating';
    	return $vars;
    }
}