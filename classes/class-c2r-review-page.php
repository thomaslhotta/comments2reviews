<?php

/**
 * Creates pages width individual urls for single comments.
 * 
 * @author Thomas Lhotta
sa */
class C2R_Review_Page
{
    /**
     * @var Comments_2_Reviews
     */
	protected $main;
	
	/**
	 * @var C2R_Settings
	 */
	protected $settings;
	
	/**
	 * The translated endpoint.
	 * 
	 * @var string
	 */
	protected $endpoint;
	
	/**
	 * Stores a comment for later usage
	 * 
	 * @var stClass
	 */
	protected $comment = null;
	
	public function __construct( C2R_Settings $settings, $settings, Comments_2_Reviews $main )
	{
		$this->settings = $settings;
		$this->main = $main;
		
		// Add redirects for wordpress pages
		add_action( 'init', array( $this, 'add_review_endpoints' ) );
		add_filter( 'template_include', array( $this, 'redirect_comment_template' ) );
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
	    add_rewrite_endpoint(
	    	$this->get_endpoint(),
	    	EP_ALL
	    );
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
	    $param = $this->get_endpoint();
	    if ( !isset( $wp_query->query_vars[$param] ) or empty( $wp_query->query_vars[$param] ) ) {
	        return $redirect;
	    }
	
	    $id = $wp_query->query_vars[$param];
	    
	    // The param has already been processed. Don't do anything. 
	    if ( !is_string( $id ) ) {
	        return $redirect;
	    }
	    
	    $comment = false;
	    
	    if ( is_numeric( $id ) ) {
	        $id = intval( $id );
	        // Retrieve the comment and store it for later or return 404 if none is found.
	        $comment = get_comment( $id );	        
	    } 

	    if ( !$comment || $comment->comment_post_ID != get_the_ID() ) {
	        $wp_query->is_404 = true;
	        $wp_query->is_single = false;
	        $wp_query->is_page = false;
	        return get_404_template();
	    }
	    
	    $this->comment = $comment;
	    
	    // Add open graph meta tags.
	    add_action( 'wp_head' , array( $this, 'add_open_graph_markup' ) );
	    // Try to find template
	    $template = locate_template( 'single-review' );
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
	    if ( is_null( $this->comment ) ) {
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
	    
	    $comment_id = $this->comment->comment_ID;
	    
	    $rating = intval( get_comment_meta( $comment_id , 'rating',  true ) );
	    
	    $stars = '';
	    
	    
	    for ( $i = 1; $i < 6; $i++ ) {
	        if ( $i <= $rating ) {
	            $stars .= '★';
	        } else {
	            $stars .= '☆'; 
	        }
	    }
	    
	    // @ toto format title
	    $title = $stars . ' ' . get_comment_meta( $comment_id , 'title',  true );	 
	    
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
	
	/**
	 * Changes to comment link on reviews to point to the review page.
	 * 
	 * @param string $link
	 * @param strClass $comment
	 * @param array $args
	 * @return string
	 */
	public function modify_comment_link( $link, $comment, $args )
	{
	    if ( !$this->main->comment_has_rating( $comment->comment_ID ) ) {
	        return $link;
	    }

	    // Remove pagination link
	    if ( isset( $args['page'] ) ) {
	    	$link = str_replace( '/comment-page-' . $args['page'], '' , $link );
	    }
	    
	    return str_replace(
	        '#comment-' . $comment->comment_ID,
            $this->get_endpoint() . '/' . $comment->comment_ID,
	        $link
	    );
	    
	}
	
	/**
	 * Returns the review pages endpoint.
	 * 
	 * @return string
	 */
	protected function get_endpoint()
	{
		if ( !$this->endpoint ) {
			$this->endpoint = __( 'reviews', $this->settings->get_plugin_slug() );
		}
		
		return $this->endpoint;
	}
}