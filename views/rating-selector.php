<?php 
    if ( !isset( $title ) ) {
        $title = null;
    }
    
    if ( !isset( $rating ) ) {
        $rating = null;
    }
    
    $labels = array(
        1 => __( 'I hate it', 'comments2reviews' ),
    	2 => __( 'I don\'t like it', 'comments2reviews' ),
    	3 => __( 'It\'s ok', 'comments2reviews' ),
    	4 => __( 'I like it', 'comments2reviews' ),
    	5 => __( 'I love it', 'comments2reviews' ),
    );

?>

<div class="rating-selector clearfix form-group">
    <label for="rating-box" class="sr-only"><?php _e('Rating', 'comments2reviews')?></label>
    <span class="rating-box" id="rating-box">
    <?php for( $i=5; $i >= 1; $i-- ): ?>    
    
    	
        <input <?php echo ( $rating == $i ) ? 'checked' : ''?>  type="radio" name="rating" id="rating-<?php echo $i ?>" value="<?php echo $i ?>"/>
        <label for="rating-<?php echo $i ?>" class="rating-star" title="<?php echo esc_attr( $labels[$i] )  ?>">
            <span class="rating-text"><?php echo $i?></span>
            <i class="icon-star-empty"></i>
            <i class="icon-star"></i>
        </label>
    <?php endfor?>
    </span>
</div>

<div class="review-title form-group">
    <label for="review-title" class="sr-only"><?php _e( 'Title', 'comments2reviews' )?></label>
	<input id="review-title" name="title" type="text" class="form-control" size="30" placeholder="<?php _e( 'Enter a title for this review.', 'comments2reviews' )?>" <?php echo $title ? 'value="' . $title .'"' : ''  ?>/>
</div>

