<?php
/**
 * Display Comments 2 Reviews admin functions
 * 
 * @author Thomas Lhotta
 */
class C2R_Admin
{
	/**
	 * @var C2R_Settings
	 */
    protected $settings;
    
    public function __construct( C2R_Settings $settings )
    {
        $this->settings = $settings;
        
        add_action( 'add_meta_boxes_comment', array( $this, 'comment_add_meta_box' ) );
        
        add_action( 'edit_comment', array( $this, 'comment_edit_metafields' ) );
        
        add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
    }
    
    /**
     * Adds the comment editing meta box to admin.-
     */
    public function comment_add_meta_box() {
        add_meta_box(
            'title',
            __( 'Rating', $this->settings->get_plugin_slug() ),
            array( $this, 'comment_meta_box' ),
            'comment',
            'normal',
            'high'
        );
    }
    
    /**
     * Adds the comment editing meta box from controls
     *
     * @param object $comment
     */
    public function comment_meta_box( $comment )
    {
        $title = get_comment_meta( $comment->comment_ID, 'title', true );
        $rating = get_comment_meta( $comment->comment_ID, 'rating', true );
        
        wp_nonce_field( 'extend_comment_update', 'extend_comment_update', false );
    
        include dirname( __FILE__ ) . '/../views/rating-selector.php';
    }
    
    /**
     * Saves changes made in admin
     *
     * @param int $comment_id
     */
    public function comment_edit_metafields( $comment_id ) {
        if ( ! isset( $_POST['extend_comment_update'] ) || ! wp_verify_nonce( $_POST['extend_comment_update'], 'extend_comment_update' ) ) return;
    
    
        if ( ( isset( $_POST['title'] ) ) && ( $_POST['title'] != '') ) {
            $title = wp_filter_nohtml_kses( $_POST['title'] );
            update_comment_meta( $comment_id, 'title', $title );
        } else {
            delete_comment_meta( $comment_id, 'title' );
        };
    
        if ( ( isset( $_POST['rating'] ) ) && ( $_POST['rating'] != '') ) {
            $rating = wp_filter_nohtml_kses( $_POST['rating'] );
            update_comment_meta( $comment_id, 'rating', $rating );
        } else {
            delete_comment_meta( $comment_id, 'rating' );
        }
    }
    
    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     *
     * Adds settings fields
     *
     * @since	1.0.0
     */
    public function add_plugin_admin_menu() {
    	$plugin_slug = $this->settings->get_plugin_slug();
    	
        $this->plugin_screen_hook_suffix = add_options_page(
        	__( 'Comments 2 Reviews', $plugin_slug ),
            __( 'Comments 2 Reviews', $plugin_slug ),
            'manage_options',
            $plugin_slug,
            array( $this, 'display_plugin_admin_page' )
        );
    
        $settings_slug = $plugin_slug . '-enabled_post_types';
    
        register_setting(
	        $settings_slug,
	        $settings_slug,
	        array( $this, 'sanitize_post_type_settings_field' )
        );
    
        add_settings_section(
	        $settings_slug, // ID
	        __( 'Create Reviews on:', $plugin_slug ), // Title
	        null,//array( $this, 'test' ), // Callback
	        $settings_slug
        );
    
        add_settings_field(
	        $settings_slug,
	        __( 'Choose the post types that reviews should be enabled on.', $plugin_slug ),
	        array( $this, 'render_post_type_settings_field' ),
	        $settings_slug,
	        $settings_slug
        );
    }
    
    /**
     * Render the settings page for this plugin.
     */
    public function display_plugin_admin_page() {
    	$slug = $this->settings->get_plugin_slug();
    	
        include_once( COMMENTS_2_REVIEWS_DIR . '/views/admin.php' );
    }
    
    
    /**
     * Renders the post type selection field.
     */
    public function render_post_type_settings_field()
    {
        $post_types = array();
    
        $settings_slug = $this->settings->get_plugin_slug() . '-enabled_post_types';
    
        foreach ( get_post_types( array('public' => true) ) as $slug => $name ) {
            $post_type = array(
                'slug' => $slug,
                'name' => get_post_type_object( $slug )->labels->name,
                'enabled' => in_array( $slug, $this->settings->get_enabled_post_types() )
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
}