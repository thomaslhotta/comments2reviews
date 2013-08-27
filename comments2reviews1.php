<?php
/*
Plugin Name: Comments 2 Reviews
Version: 1.0
Plugin URI: 
Description: A plugin to add fields to the comment form.
Author: Thomas Lhotta
Author URI: http://www.speckygeek.com
*/

// Add fields after default fields above the comment box, always visible

add_action( 'comment_form_logged_in_after', 'additional_fields' );
add_action( 'comment_form_after_fields', 'additional_fields' );

function additional_fields () {
    $post = get_post();
    $user = wp_get_current_user();
    
    // Find existing ratings
    
    $query = array(
        'number' => 1,
        'post_id' => $post->ID,
        'post_author' => '',
        'post_name' => '',
        'post_parent' => '',
        'post_status' => '',
        'post_type' => '',
        'status' => '',
        'type' => '',
        'user_id' => '',
        'search' => '',
        'count' => false,
        'meta_key' => '',
        'meta_value' => '',
        'meta_query' => '',
    );
    
    
    
    die(print_r(get_post()));
    
    echo '<p class="comment-form-title">'.
            '<label for="title">' . __( 'Comment Title' ) . '</label>'.
            '<input id="title" name="title" type="text" size="30"  tabindex="5" /></p>';

    echo '<p class="comment-form-rating">'.
            '<label for="rating">'. __('Rating') . '<span class="required">*</span></label>
	<span class="commentratingbox">';

    //Current rating scale is 1 to 5. If you want the scale to be 1 to 10, then set the value of $i to 10.
    for( $i=1; $i <= 5; $i++ )
        echo '<span class="commentrating"><input type="radio" name="rating" id="rating" value="'. $i .'"/>'. $i .'</span>';

    echo'</span></p>';

}


add_filter( 'preprocess_comment', 'verify_comment_meta_data' );
function verify_comment_meta_data( $commentdata ) {
    if ( ! isset( $_POST['rating'] ) )
        wp_die( __( 'Error: You did not add a rating. Hit the Back button on your Web browser and resubmit your comment with a rating.' ) );
    return $commentdata;
}


// Save the comment meta data along with comment

add_action( 'comment_post', 'save_comment_meta_data' );
function save_comment_meta_data( $comment_id ) {
    if ( ( isset( $_POST['title'] ) ) && ( $_POST['title'] != '') )
        $title = wp_filter_nohtml_kses($_POST['title']);
    add_comment_meta( $comment_id, 'title', $title );

    if ( ( isset( $_POST['rating'] ) ) && ( $_POST['rating'] != '') )
        $rating = wp_filter_nohtml_kses($_POST['rating']);
    add_comment_meta( $comment_id, 'rating', $rating );
    
    
}


// Add the comment meta (saved earlier) to the comment text
// You can also output the comment meta values directly to the comments template

add_filter( 'comment_text', 'modify_comment');
function modify_comment( $text ){

    $plugin_url_path = WP_PLUGIN_URL;

    if( $commenttitle = get_comment_meta( get_comment_ID(), 'title', true ) ) {
        $commenttitle = '<strong>' . esc_attr( $commenttitle ) . '</strong><br/>';
        $text = $commenttitle . $text;
    }

    if( $commentrating = get_comment_meta( get_comment_ID(), 'rating', true ) ) {
        $commentrating = '<p class="comment-rating">	<img src="'. $plugin_url_path .
        '/ExtendComment/images/'. $commentrating . 'star.gif"/><br/>Rating: <strong>'. $commentrating .' / 5</strong></p>';
        $text = $text . $commentrating;
        return $text;
    } else {
        return $text;
    }
}



// Add an edit option to comment editing screen

add_action( 'add_meta_boxes_comment', 'extend_comment_add_meta_box' );
function extend_comment_add_meta_box() {
    add_meta_box( 'title', __( 'Comment Metadata - Extend Comment' ), 'extend_comment_meta_box', 'comment', 'normal', 'high' );
}

function extend_comment_meta_box ( $comment ) {
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

// Update comment meta data from comment editing screen

add_action( 'edit_comment', 'extend_comment_edit_metafields' );

function extend_comment_edit_metafields( $comment_id ) {
    if( ! isset( $_POST['extend_comment_update'] ) || ! wp_verify_nonce( $_POST['extend_comment_update'], 'extend_comment_update' ) ) return;


    if ( ( isset( $_POST['title'] ) ) && ( $_POST['title'] != '') ):
    $title = wp_filter_nohtml_kses($_POST['title']);
    update_comment_meta( $comment_id, 'title', $title );
    else :
    delete_comment_meta( $comment_id, 'title');
    endif;

    if ( ( isset( $_POST['rating'] ) ) && ( $_POST['rating'] != '') ):
    $rating = wp_filter_nohtml_kses($_POST['rating']);
    update_comment_meta( $comment_id, 'rating', $rating );
    else :
    delete_comment_meta( $comment_id, 'rating');
    endif;

}