<?php 
    if ( !isset( $title ) ) {
        $title = null;
    }
    
    if ( !isset( $rating ) ) {
        $rating = null;
    }
    

?>

<div class="rating-selector clearfix form-group">
    <label for="rating-box"><?php _e('Rating', 'comments2reviews')?></label>
    <span class="rating-box" id="rating-box">
    <?php for( $i=5; $i >= 1; $i-- ): ?>    
    
    	
        <input <?php echo ( $rating == $i ) ? 'checked' : ''?>  type="radio" name="rating" id="rating-<?php echo $i ?>" value="<?php echo $i ?>"/>
        <label for="rating-<?php echo $i ?>" class="rating-star">
            <span class="rating-text"><?php echo $i?></span>
            <i class="icon-star-empty"></i>
            <i class="icon-star"></i>
        </label>
    <?php endfor?>
    </span>
</div>

<div class="review-title form-group">
    <label for="review-title"><?php _e( 'Title', 'comments2reviews' )?></label>
	<input id="review-title" name="title" type="text" class="form-control" size="30" <?php echo $title ? 'value="' . $title .'"' : ''  ?>/>
</div>

