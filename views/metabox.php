<p>
    <label for="title"><?php esc_html_e( 'Title', $this->get_plugin_slug() )?>:</label>
    <input type="text" name="title" value="<?php echo esc_attr( $title ); ?>" class="widefat" />
</p>
<p>
    <label for="rating"><?php esc_html_e( 'Rating', $this->get_plugin_slug() ); ?>:</label>
	<span class="commentratingbox">
	<?php for ( $i = 1; $i <= 5; $i++ ) {
		echo '<span class="commentrating"><input type="radio" name="rating" id="rating" value="'. $i .'"';
		if ( $rating == $i ) echo ' checked="checked"';
		echo ' />'. $i .' </span>';
		}
	?>
	</span>
</p>