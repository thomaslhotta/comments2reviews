<?php 
/*
 * @todo Translations
 */

?>

<p class="rating-selector clearfix">
    <label for="rating"><?php echo __('Rating') ?> <span class="">*</span></label>
    <span class="rating-box">
    <?php for( $i=5; $i >= 1; $i-- ): ?>    	
        <input type="radio" name="rating" id="rating-<?php echo $i ?>" value="<?php echo $i ?>"/>
        <label for="rating-<?php echo $i ?>" class="rating-star">
            <span class="rating-text"><?php echo $i?></span>
            <i class="icon-star-empty"></i>
            <i class="icon-star"></i>
        </label>
    <?php endfor?>
    </span>
</p>
