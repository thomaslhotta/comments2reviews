<?php

/**
 * Creates pages width individual urls for single comments.
 * 
 * @author tom
 *
 */
class C2R_Review_Page
{
    /**
     * @var Comments_2_Reviews
     */
	protected $main;
	
	protected $comment = null;
	
	public function __construct( Comments_2_Reviews $main )
	{
		$this->main = $main;
		
		// Add redirects for wordpress pages
		add_action( 'init', array( $this, 'add_review_endpoints' ) );
		add_filter( 'template_redirect', array( $this, 'redirect_comment_template' ) );
		add_filter( 'comments_array', array( $this, 'comments_array' ) , 999 , 2  );
		add_filter( 'get_comment_link', array( $this, 'modify_comment_link' ), 10, 3 );
	}
	
	/**
	 * Returns the wp_query object
	 * 
	 * @return WP_Query
	 */
	public function get_wp_query()
	{
	    global $wp_query;
	    return $wp_query;
	}
	
	/**
	 * Adds the reviews endpoint.
	 */
	public function add_review_endpoints()
	{
	    add_rewrite_endpoint( $this->main->__( 'reviews' ) , EP_ALL );
	}
	
	/**
	 * Redirects template to special single comments template if it exists in the theme folder.
	 * 
	 * @param string $redirect
	 * @return string|string
	 */
	public function redirect_comment_template( $redirect )
	{
	    // This is only relevant for single pages
	    if ( !is_single() ) {
	        return $redirect;
	    }
	    
	    // Return if the parameter was not given
	    $wp_query = $this->get_wp_query();
	    $param = $this->main->__( 'reviews' );
	    if ( !isset( $wp_query->query_vars[$param] ) or empty( $wp_query->query_vars[$param] ) ) {
	        return $redirect;
	    }
	
	    
	    // Retrieve the comment and store it for later or return 404 if none is found.
	    $url = intval( $wp_query->query_vars[$param] );
	    
	    $comment = get_comment( $url );
	    
	    if ( !$comment && $comment->comment_post_ID !== get_the_ID() ) {
	        $wp_query->is_404 = true;
	        $wp_query->is_single = false;
	        $wp_query->is_page = false;
	         
	        return locate_template('404');
	    }
	    $this->comment = $comment;
	    
	    // Add open graph meta tags.
	    add_action( 'wp_head' , array( $this, 'add_open_graph_markup' ) );
	    
	    // Try to find template
	    $template = locate_template( 'single-comment' );
	
	    // Use default if none is found.
	    if ( !empty( $template) ) {
	        return $template;
	    }
	     
	    return $redirect;
	}
	
	/**
	 * Ensures that only one comment is listed for single comment url.
	 * 
	 * @param array $comments
	 * @return array
	 */
	public function comments_array( array $comments, $post )
	{
	    if ( is_null ( $this->comment ) ) {
	        return $comments;
	    }
	    
	    $comments = array( $this->comment );
	    return $comments;
	}
	
	/**
	 * Adds facebook open graph markup.
	 * 
	 * @todo Define title and content
	 * 
	 * 
	 */
	public function add_open_graph_markup()
	{
	    global $wp;
	    
	    $url = home_url( add_query_arg( array(), $wp->request ) );
	    
	    $rating = intval( get_comment_meta( $this->comment->comment_ID , 'rating',  true ) );
	    
	    $stars = '';
	    
	    
	    for ($i = 1; $i < 6; $i++) {
	        if ( $i <= $rating ) {
	            $stars .= '★';
	        } else {
	            $stars .= '☆'; 
	        }
	    }
	    
	    $title = $rating . $stars . ' test';	 
	    
	    $image = wp_get_attachment_image_src( get_post_thumbnail_id() );   
	    
	    echo sprintf(
    	    '<meta property="og:title" content="%s" />' .
    	    '<meta property="og:type" content="%s" />' .
    	    '<meta property="og:url" content="%s" />' .
    	    '<meta property="og:image" content="%s" />' ,
	        $title,
	        'website',
	        $url,
	        reset( $image )
	    );    
	}
	
	public function modify_comment_link( $link, $comment, $args )
	{
	    if ( !$this->main->comment_has_rating( $comment->comment_ID ) ) {
	        return $link;
	    }
	    
	    return str_replace( 
	        '#comment-' . $comment->comment_ID,
            $this->main->__( 'reviews' ) . '/' . $comment->comment_ID,
	        $link
	    );
	    
	}
}