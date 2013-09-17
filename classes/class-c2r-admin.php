<?php
class C2R_Admin
{
    protected $main;
    
    
    public function __construct( $main )
    {
        $this->main = $main;
        
        add_action( 'add_meta_boxes_comment', array( $this, 'comment_add_meta_box' ) );
        
        add_action( 'edit_comment', array( $this, 'comment_edit_metafields' ) );
    }
    
    /**
     * Adds the comment editing meta box to admin.-
     */
    public function comment_add_meta_box() {
        add_meta_box(
            'title',
            __( 'Rating', $this->get_plugin_slug() ),
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
    
    
    
    public function get_plugin_slug()
    {
        return $this->main->get_plugin_slug();
    }
    
}