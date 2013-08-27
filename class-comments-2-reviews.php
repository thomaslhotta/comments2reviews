<?php
/**
 * Plugin Name.
 *
 * @package   Comments 2 Reviews
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2013 Thomas Lhotta
 */

/**
 * Plugin class.
 *
 * TODO: Rename this class to a proper name for your plugin.
 *
 * @package Plugin_Name
 * @author  Your Name <email@example.com>
 */
class Comments_2_Reviews {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	protected $version = '1.0.0';

	/**
	 * Unique identifier for your plugin.
	 *
	 * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
	 * match the Text Domain file header in the main plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'comments2reviews';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Add the options page and menu item.
		// add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Load admin style sheet and JavaScript.
		//add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		//add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Define custom functionality. Read more about actions and filters: http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		//add_action( 'TODO', array( $this, 'action_method_name' ) );
		//add_filter( 'TODO', array( $this, 'filter_method_name' ) );
		
		
		add_action( 'comment_form_logged_in_after', array( $this, 'additional_fields' ) );
		
		//add_filter( 'preprocess_comment', array( $this, 'verify_comment_meta_data' ) );
		
		add_action( 'comment_post', array( $this, 'save_comment_meta_data' ) );
		
		add_filter( 'comment_text', array ( $this, 'modify_comment' ) , 1000);
		
		add_action( 'add_meta_boxes_comment', array( $this, 'extend_comment_add_meta_box' ) );
		
		add_action( 'edit_comment', array( $this, 'extend_comment_edit_metafields' ) );
		
		add_action( 'comment_approved_to_trash', array( $this, 'delete_comment' ) );
		add_action( 'comment_approved_to_hold', array( $this, 'delete_comment' ) );
		
		add_action( 'comment_hold_to_approved', array( $this, 'restore_comment' ) );
		add_action( 'comment_trash_to_approved', array( $this, 'restore_comment' ) );

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public static function activate( $network_wide ) {
		// TODO: Define activation functionality here
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Deactivate" action, false if WPMU is disabled or plugin is deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {
		// TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen->id == $this->plugin_screen_hook_suffix ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'css/admin.css', __FILE__ ), array(), $this->version );
		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen->id == $this->plugin_screen_hook_suffix ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), $this->version );
		}

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'css/public.css', __FILE__ ), array(), $this->version );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'js/public.js', __FILE__ ), array( 'jquery' ), $this->version );
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		/*
		 * TODO:
		 *
		 * Change 'Page Title' to the title of your plugin admin page
		 * Change 'Menu Text' to the text for menu item for the plugin settings page
		 * Change 'plugin-name' to the name of your plugin
		 */
		$this->plugin_screen_hook_suffix = add_plugins_page(
			__( 'Page Title', $this->plugin_slug ),
			__( 'Menu Text', $this->plugin_slug ),
			'read',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * NOTE:  Actions are points in the execution of a page or process
	 *        lifecycle that WordPress fires.
	 *
	 *        WordPress Actions: http://codex.wordpress.org/Plugin_API#Actions
	 *        Action Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */
	public function action_method_name() {
		// TODO: Define your action hook callback here
	}

	/**
	 * NOTE:  Filters are points of execution in which WordPress modifies data
	 *        before saving it or sending it to the browser.
	 *
	 *        WordPress Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *        Filter Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 */
	public function filter_method_name() {
		// TODO: Define your filter hook callback here
	}
	
	
	public function additional_fields () {
	    $post = get_post();
	    $user = wp_get_current_user();
	    
	
	    // Find existing ratings
	
	    $args = array(
	        //'number' => 1,
	        'post_id' => $post->ID,
	        //'post_author' => '',
	        //'post_name' => '',
	        //'post_parent' => '',
	        //'post_status' => '',
	        //'post_type' => '',
	        //'status' => '',
	        //'type' => '',
	        'user_id' => $user->ID,
	        //'search' => '',
	        'count' => false,
	        'meta_query' => array(
	            //'relation' => 'AND',
	            array(
	                'key' => 'rating',
	                //'type' => 'numeric',
	                //'value' => 2
	                'compare' => 'EXISTS'
	            )
	        ),
	    );
	
	    $query = new WP_Comment_Query($query);
	    
	
	    $existing_ratings = $query->query($args);
	    
	    echo '<p class="comment-form-title">'.
	            '<label for="title">' . __( 'Comment Title' ) . '</label>'.
	            '<input id="title" name="title" type="text" size="30"  tabindex="5" /></p>';
	
	    if ( empty( $existing_ratings ) ) {
	        include 'views/rating-selector.php';
	    }
	
	}
	
	public function get_post_rating() {
	    $post = get_post();
	    
	    $rating_total = intval(get_post_meta($post->ID, 'rating_total', true));
	    $rating_count = intval(get_post_meta($post->ID, 'rating_count', true));
	    
	    if ( 0 == $rating_count ) {
	        return;
	    }
	    
	    $rating = $rating_total / $rating_count;
	    //die('rating ' . $rating);
	    include( 'views/public.php' );
	    
	}
	
	public function verify_comment_meta_data( $commentdata ) {
	    if ( ! isset( $_POST['rating'] ) )
	        wp_die( __( 'Error: You did not add a rating. Hit the Back button on your Web browser and resubmit your comment with a rating.' ) );
	    return $commentdata;
	}
	
	public function save_comment_meta_data( $comment_id ) {
	    if ( ( isset( $_POST['title'] ) ) && ( $_POST['title'] != '') ) {
	        $title = wp_filter_nohtml_kses($_POST['title']);
	        add_comment_meta( $comment_id, 'title', $title );
	    }
	     
	
	    if ( ( isset( $_POST['rating'] ) ) && ( $_POST['rating'] != '') ) {
	        $rating = wp_filter_nohtml_kses($_POST['rating']);
	        add_comment_meta( $comment_id, 'rating', $rating );
	        
	        $comment = get_comment($comment_id);
	        //die('rating ' . $rating );
	        $this->update_post_rating($comment->comment_post_ID, $rating);
	    }
	       
	}
	
	public function modify_comment( $text ){
	
	    // Don't modify for child comments
	    $comment = array();
	    $comment = get_comment($comment);
	    if ( is_object( $comment ) ) {
	        if ( 0 != $comment->comment_parent) {
	            return $text;
	        } 
	    }
	    
	    
	    $title = get_comment_meta( get_comment_ID(), 'title', true );
	    $rating = get_comment_meta( get_comment_ID(), 'rating', true );
	    
	    ob_start();
	    
	    include "views/comment.php";
	    
	    $return = ob_get_contents();
	    ob_end_clean();
	            
	    return $return;
	}
	
	
	public function extend_comment_add_meta_box() {
	    add_meta_box( 'title', __( 'Comment Metadata - Extend Comment' ), array( $this, 'extend_comment_meta_box' ) , 'comment', 'normal', 'high' );
	}
	
	public function extend_comment_meta_box ( $comment ) {
	    $phone = get_comment_meta( $comment->comment_ID, 'phone', true );
	    $title = get_comment_meta( $comment->comment_ID, 'title', true );
	    $rating = get_comment_meta( $comment->comment_ID, 'rating', true );
	    wp_nonce_field( 'extend_comment_update', 'extend_comment_update', false );
	    ?>
	    <p>
	        <label for="phone"><?php _e( 'Phone' ); ?></label>
	        <input type="text" name="phone" value="<?php echo esc_attr( $phone ); ?>" class="widefat" />
	    </p>
	    <p>
	        <label for="title"><?php _e( 'Comment Title' ); ?></label>
	        <input type="text" name="title" value="<?php echo esc_attr( $title ); ?>" class="widefat" />
	    </p>
	    <p>
	        <label for="rating"><?php _e( 'Rating: ' ); ?></label>
				<span class="commentratingbox">
				<?php for( $i=1; $i <= 5; $i++ ) {
					echo '<span class="commentrating"><input type="radio" name="rating" id="rating" value="'. $i .'"';
					if ( $rating == $i ) echo ' checked="checked"';
					echo ' />'. $i .' </span>';
					}
				?>
				</span>
	    </p>
	    <?php
	}
	
	public function extend_comment_edit_metafields( $comment_id ) {
	    if( ! isset( $_POST['extend_comment_update'] ) || ! wp_verify_nonce( $_POST['extend_comment_update'], 'extend_comment_update' ) ) return;
	
	
	    if ( ( isset( $_POST['title'] ) ) && ( $_POST['title'] != '') ) {
            $title = wp_filter_nohtml_kses($_POST['title']);
            update_comment_meta( $comment_id, 'title', $title );
        } else {
    	    delete_comment_meta( $comment_id, 'title');
	    };
	
	    if ( ( isset( $_POST['rating'] ) ) && ( $_POST['rating'] != '') ) {
            $rating = wp_filter_nohtml_kses($_POST['rating']);
            update_comment_meta( $comment_id, 'rating', $rating );
        } else {
            delete_comment_meta( $comment_id, 'rating');
        }
	   
	
	}
	
	public function delete_comment( $comment ) {
        $this->update_post_rating($comment->comment_post_ID, $this->get_comment_rating($comment) * -1, -1);
    }	
    
    public function restore_comment( $comment ) {
        $this->update_post_rating($comment->comment_post_ID, $this->get_comment_rating($comment));
    }
    
    
    protected function get_comment_rating( $id ) {
        if (is_object($id)) {
            $id = $id->comment_ID;    
        }
        
        $return = get_comment_meta($id, 'rating' ,true);
        
        if ( empty( $return ) ) {
            return null;
        }
        
        return intval($return);
    }
   
    
    protected function update_post_rating($post, $rating, $count = 1) {

        $post = get_post( $post );
        
        $rating_count = intval(get_post_meta($post->ID, 'rating_count', true));
        
        echo ' rating-count  ' . $rating_count . ' ';
        
        update_post_meta($post->ID, 'rating_count' , $rating_count + $count);
         
        $rating_total = (get_post_meta($post->ID, 'rating_total', true));
        
        echo ' rating-total  ' . $rating_total . ' ';
        
        update_post_meta($post->ID, 'rating_total' , $rating_total + $rating);
    }


}