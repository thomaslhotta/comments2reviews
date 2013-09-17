<?php
/**
 * Comments 2 Reviews.
 *
 * @package   Comments 2 Reviews
 * @author	Thomas Lhotta <th.lhotta@gmail.com>
 * @license   GPL-2.0+
 * @link	  http://www.github.com/thomaslhotta
 * @copyright 2013 Thomas Lhotta
 */

/**
 * A plugin that turn reviews into ratings.
 *
 * @package Comments_2_Reviews
 * @author  Thomas Lhotta <th.lhotta@gmail.com>
 */
class Comments_2_Reviews {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var	 string
	 */
	protected $version = '1.0.0';

	/**
	 * Unique identifier for your plugin.
	 *
	 *
	 * @since	1.0.0
	 * @var	  string
	 */
	protected $plugin_slug = 'comments2reviews';

	/**
	 * Instance of this class.
	 *
	 * @since	1.0.0
	 * @var	  Comments_2_Reviews
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since	1.0.0
	 * @var	  string
	 */
	protected $plugin_screen_hook_suffix = null;
	
	/**
	 * An array of enabled post types.
	 * 
	 * @var array|null
	 */
	protected $enabled_post_types = null;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since	 1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );

		// Add ratings field to comment.
		add_action( 'comment_form_logged_in_after', array( $this, 'add_rating_fields_to_comment' ) );
		
		//add_filter( 'preprocess_comment', array( $this, 'verify_comment_meta_data' ) );
		
		add_action( 'comment_post', array( $this, 'save_comment_meta_data' ), 9, 2 );
		
		// Add the rating markup to the comment text on display
		add_filter( 'comment_text', array( $this, 'modify_comment' ) , 1000 );
		
		// Modify comment author
		add_filter( 'get_comment_author' , array( $this, 'get_comment_author' ) );
		
		
		// Add a class to rated comments on display and injects microformat
		add_filter( 'comment_class', array( $this, 'add_comment_class' ) , 9999, 3 );
		
		
		
		// Handle comment deletion
		add_action( 'comment_approved_to_trash', array( $this, 'delete_comment' ) );
		add_action( 'comment_approved_to_unapproved', array( $this, 'delete_comment' ) );
		
		add_action( 'comment_unapproved_to_approved', array( $this, 'restore_comment' ) );
		add_action( 'comment_trash_to_approved', array( $this, 'restore_comment' ) );
		
	
		// Add review pages
		require_once dirname( __FILE__ ) . '/class-c2r-review-page.php';
		new C2R_Review_Page( $this );
		
		require_once dirname( __FILE__ ) . '/class-c2r-query.php';
		new C2R_Query();
		
		require_once dirname( __FILE__ ) . '/class-c2r-admin.php';
		new C2R_Admin( $this );
		
		
		// Integrate width buddypress
		add_filter( 'bp_blogs_activity_new_comment_action', array( $this, 'buddypress_rename_comment_activity'), 10, 3 );
		
		//@todo Needs fix, buddypress strips html attributes.
		// add_filter( 'bp_blogs_activity_new_comment_content', array( $this, 'modify_comment'), 10, 2) ;

		
		// Add myCRED hooks
		
		add_filter( 'mycred_modules', array( $this, 'mycred_hooks' ) );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since	 1.0.0
	 * @return	object	A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since	1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->get_plugin_slug();
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
		
		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		$test = load_plugin_textdomain( $domain, true, basename( COMMENTS_2_REVIEWS_DIR ) . '/lang/' );
	}


	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since	1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->get_plugin_slug() . '-plugin-styles', WP_PLUGIN_URL . '/' . basename( COMMENTS_2_REVIEWS_DIR ) . '/css/public.css', array(), $this->version );
	}

	
	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * Adds settings fields
	 *
	 * @since	1.0.0
	 */
	public function add_plugin_admin_menu() {
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'Comments 2 Reviews', $this->get_plugin_slug() ),
			__( 'Comments 2 Reviews', $this->get_plugin_slug() ),
			'manage_options',
			$this->get_plugin_slug(),
			array( $this, 'display_plugin_admin_page' )
		);
		
		$settings_slug = $this->get_plugin_slug() . '-enabled_post_types';
		
		register_setting(
			$settings_slug,
			$settings_slug,
			array( $this, 'sanitize_post_type_settings_field' )
		);
		
		add_settings_section(
			$settings_slug, // ID
			__( 'Create Reviews on:', $this->get_plugin_slug() ), // Title
			null,//array( $this, 'test' ), // Callback
			$settings_slug
		);
		
		add_settings_field(
			$settings_slug,
			__( 'Choose the post types that reviews should be enabled on.', $this->get_plugin_slug() ),
			array( $this, 'render_post_type_settings_field' ),
			$settings_slug,
			$settings_slug
		);
	}
	
	
	/**
	 * Render the settings page for this plugin.
	 *
	 * @since 1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( COMMENTS_2_REVIEWS_DIR . '/views/admin.php' );
	}
	
	
	/**
	 * Renders the post type selection field.
	 */
	public function render_post_type_settings_field()
	{
		$post_types = array();
		
		$settings_slug = $this->get_plugin_slug() . '-enabled_post_types';
		
		foreach ( get_post_types( array('public' => true) ) as $slug => $name ) {
			$post_type = array(
				'slug' => $slug,
				'name' => get_post_type_object( $slug )->labels->name,
				'enabled' => in_array( $slug, $this->get_enabled_post_types() )
			);
			$post_types[] = $post_type;
		}
		
		include COMMENTS_2_REVIEWS_DIR . '/views/field-post-type.php';
	}
	
	
	/**
	 * Sanitizes the enabled posts option.
	 * 
	 * @param array|null $input
	 * @return array|null
	 */
	public function sanitize_post_type_settings_field( $input )
	{
		// Only array are valid as input.
		if ( !is_array( $input ) ) {
			return null;
		}
		
		// Make sure only existing post types are used.
		$sanitized = array();
		foreach ( $input as $post_type ) {
			if ( get_post_type_object( $post_type ) ) {
				$sanitized[] = $post_type;
			}
		}
		
		return $sanitized;
	}
	
	
	/**
	 * Returns an array of enabled post types.
	 * 
	 * @return array
	 */
	public function get_enabled_post_types()
	{
		if ( is_array( $this->enabled_post_types ) ) {
			return $this->enabled_post_types;
		}
		
		$post_types = get_option( $this->get_plugin_slug() . '-enabled_post_types' );
		
		if ( !is_array( $post_types ) ) {
			$post_types = array();
		}
		
		$this->enabled_post_types = $post_types;
		
		return $this->enabled_post_types;
	}

	
	/**
	 * Adds the rating form fields to the comments field.
	 */
	public function add_rating_fields_to_comment() {
		$post = get_post();
		
		// Don't to anything if comments are not enabled for this post type.
		if ( !in_array( $post->post_type, $this->get_enabled_post_types() ) ) {
			return;
		}
		
		$user = wp_get_current_user();
	
		// Find existing ratings
		$args = array(
			'post_id' => $post->ID,
			'user_id' => $user->ID,
			'count' => false,
			'meta_query' => array(
				array(
					'key' => 'rating',
					'compare' => 'EXISTS',
				),
			),
		);
		
	
		$query = new WP_Comment_Query( $query );
		$existing_ratings = $query->query( $args );
		
		if ( empty( $existing_ratings ) ) {
			include COMMENTS_2_REVIEWS_DIR . '/views/rating-selector.php';
		}
	}
	
	/**
	 * Returns the posts rating.
	 */
	public function get_post_rating( $id = null, $echo = true )
	{
		$post = get_post( $id );
		
		$rating_total = intval( get_post_meta( $post->ID, 'rating_total', true ) );
		$rating_count = intval( get_post_meta( $post->ID, 'rating_count', true ) );
		
		if ( 0 == $rating_count ) {
    		if ( $echo ) {
    		    include( COMMENTS_2_REVIEWS_DIR . '/views/public-no-review.php' );
    		    return;
    		} else {
    		    return $rating;
    		}
		}
		
		$rating = floatval( get_post_meta( $post->ID, 'rating_mean', true ) );
		
		if ( $echo ) {
		    include( COMMENTS_2_REVIEWS_DIR . '/views/public.php' );
		} else {
		    return $rating;
		}
	}
	
	public function save_comment_meta_data( $comment_id, $status ) {
		if ( ( isset( $_POST['title'] ) ) && ( $_POST['title'] != '') ) {
			$title = wp_filter_nohtml_kses( $_POST['title'] );
			add_comment_meta( $comment_id, 'title', $title );
		}
		 
	
		if ( ( isset( $_POST['rating'] ) ) && ( $_POST['rating'] != '') ) {
			$rating = wp_filter_nohtml_kses( $_POST['rating'] );
			$rating = intval( $rating );

			// Sanitize ratings
			if ( $rating > 5 ) {
				$rating = 5;
			}
			
			if ( $rating < 1 ) {
				$rating = 1;
			}
			
			add_comment_meta( $comment_id, 'rating', $rating );
			
			$comment = get_comment( $comment_id );

			$this->update_post_rating( $comment->comment_post_ID, $rating );
			
			do_action( 'comments2reviews_review', $comment_id, $status );
		}
	}
	
/*
 *  Comment output modifications
 */
	
	
	/**
	 * Adds the class has-rating to the comment if is a rating commen.
	 *
	 * @param array $classes
	 * @return array
	 */
	public function add_comment_class( $classes, $class, $comment_id )
	{
		if ( !$this->comment_has_rating( $comment_id ) ) {
			return $classes;
		}

		$classes[] = 'has-rating';
		
		// Inject microformat
		$classes[] = '" itemprop="review" itemscope itemtype="http://schema.org/Review"';
		
		return $classes;
	}
	
	
	/**
	 * Adds itemprop to comment author.
	 * 
	 * @param string $author
	 * @return string
	 */
	public function get_comment_author( $author ) 
	{
		if ( !$this->comment_has_rating() or is_admin() ) {
			return $author;
		} 
		
		return '<span itemprop="name">' . $author . '</span>';
	}
	
	/**
	 * Adds the rating box to the comment
	 * 
	 * @param string $text
	 * @return integer|object
	 */
	
	public function modify_comment( $text, $comment = null )
	{
		// Sanitize comment object input
		if ( !is_object( $comment ) ) {
			if ( is_array( $comment ) ) {
				$comment = $comment['comment_ID'];
			} else {
				$comment = array();
			}
			$comment = get_comment( $comment );
		}
		
		// Don't modify for child comments
		if ( is_object( $comment ) ) {
			if ( 0 != $comment->comment_parent ) {
				return $text;
			} 
		}
		
		$rating = get_comment_meta( $comment->comment_ID, 'rating', true );
		
		// If no rating was found.
		if ( empty( $rating ) ) {
			return $text;
		}
		
		$title = get_comment_meta( $comment->comment_ID, 'title', true );
		
		$author = $comment->comment_author;
		
		ob_start();
		
		include COMMENTS_2_REVIEWS_DIR . '/views/comment.php';
		
		$return = ob_get_contents();
		ob_end_clean();
		
				
		return $return;
	}
	

	
	
	
	/**
	 * Updates the post rating when a comment is deleted.
	 * 
	 * @param object $comment
	 */
	public function delete_comment( $comment ) {
		$this->update_post_rating(
			$comment->comment_post_ID,
			$this->get_comment_rating( $comment ) * -1, -1
		);
	}	
	
	/**
	 * Updates the post rating when a comment is restored.
	 * 
	 * @param comment $comment
	 */
	public function restore_comment( $comment ) {
		$this->update_post_rating(
			$comment->comment_post_ID, 
			$this->get_comment_rating( $comment )
		);
	}
	
	/**
	 * Returns the rating value of the comment or null if the commen is no rating.
	 * 
	 * @param int|object $id
	 * @return NULL|number
	 */
	protected function get_comment_rating( $id ) {
		if ( is_object( $id ) ) {
			$id = $id->comment_ID;	
		}
		
		$return = get_comment_meta( $id, 'rating' ,true );
		
		if ( empty( $return ) ) {
			return null;
		}
		
		return intval( $return );
	}
   
	
	/**
	 * Updates the rating of a post
	 * 
	 * @param integer $post
	 * @param integer $rating
	 * @param number $count
	 */
	protected function update_post_rating( $post, $rating, $count = 1 )
	{
		$post = get_post( $post );
		
		$rating_count = intval( get_post_meta( $post->ID, 'rating_count', true ) );
		$new_count = $rating_count + $count;
		
		if ( $new_count >= 0 ) {
			update_post_meta( $post->ID, 'rating_count' , $new_count );
		}
		
		$rating_total =  intval(get_post_meta( $post->ID, 'rating_total', true ) );
		$new_total =  $rating_total + $rating;
		
		if ( $new_total >= 0 ) {
			update_post_meta( $post->ID, 'rating_total' , $new_total );
		}
		
		
		// Get updated values
		$rating_count = intval( get_post_meta( $post->ID, 'rating_count', true ) );
		$rating_total = ( get_post_meta( $post->ID, 'rating_total', true ) );
		
		// No reviews left, stop here.
		if ( $rating_count <= 0 ) {
		    return;
		}
		
		$mean = round( $rating_total / $rating_count, 2 );
		
		// Update mean
		update_post_meta( $post->ID, 'rating_mean' , $mean );
		
	}
	
	
	/**
	 * Returns true if the given comment has a rating.
	 * 
	 * @return boolean
	 */
	public function comment_has_rating( $id = null )
	{
		if ( is_null( $id ) ) {
			$array = array();
			$id = get_comment( $array );
		}
		
		if ( is_object( $id ) ) {
			$id = $id->comment_ID;	
		}
		
		$return = get_comment_meta( $id, 'rating' ,true );
		
		if ( empty( $return ) ) {
			return false;
		}
		
		return true;
	}

	
	/**
	 * Returns the plugin slug
	 * 
	 * @return string
	 */
	public function get_plugin_slug()
	{
		return $this->plugin_slug;
	}
	
	

	
	
	/**
	 * Changes the activity string in buddypress
	 *
	 * @param string $activity_action
	 * @param $recorded_comment
	 * @param string $is_approved
	 * @return string
	 */
	public function buddypress_rename_comment_activity( $activity_action, $recorded_comment, $is_approved = true )
	{
	    $user = get_user_by( 'email', $recorded_comment->comment_author_email );
	    $user_id = (int) $user->ID;
	    $post_permalink = get_permalink( $recorded_comment->comment_post_ID );
	
	    $single = '%1$s posted a review on %2$s';
	    $multi  = 'on the site %1$s';
	     
	
	    $string = sprintf(
	            __( $single, $this->get_plugin_slug() ),
	            bp_core_get_userlink( $user_id ),
	            '<a href="' . $post_permalink . '">' . apply_filters( 'the_title', $recorded_comment->post->post_title ) . '</a>'
	    );
	
	    if ( is_multisite() ) {
	        $string .= ',' . sprintf(
	                __( $multi, $this->get_plugin_slug() ),
	                '<a href="' . get_blog_option( $blog_id, 'home' ) . '">' . get_blog_option( $blog_id, 'blogname' ) . '</a>'
	        );
	    }
	
	    return $string;
	}
	
	public function mycred_hooks( $modules )
	{
	    return array_merge(
	            $modules,
	            array(
	                'reviews' => array(
	                    'class' => 'MyCREDHooks',
	                    'file'  => dirname( __FILE__ ) . '/MyCREDHooks.php',
	                )
	            )
	    );
	}
	
	public function __( $text )
	{
	    return __( $text, $this->get_plugin_slug() );
	}
}