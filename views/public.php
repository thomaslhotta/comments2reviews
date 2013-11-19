<?php
/**
 * Represents the view for the public-facing component of the plugin.
 *
 * This typically includes any information, if any, that is rendered to the
 * frontend of the theme when the plugin is activated.
 *
 */

?>
<div class="rating"<?echo $microdata ? '  itemprop="rating" itemscope itemtype="http://data-vocabulary.org/Rating"' :'' ?>>
    <?php include dirname( __FILE__ ) .  "/rating.php" ?>
    <span class="rating-value"<?php echo $microdata ? ' itemprop="value"' : ''?>><?php echo round( $rating, 1 )?></span>
    <span class="rating-of">/ 5</span>
    (<span class="rating-count"<?php echo $microdata ? ' itemprop="count"' : ''?>><?php echo $rating_count?></span>
    <span class="rating-name"><?php ( 1 == $rating_count ) ? _e( 'Review', 'comments2reviews' ) : _e( 'Reviews', 'comments2reviews' )?></span>)
</div>









