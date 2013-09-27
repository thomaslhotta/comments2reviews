<?php
/**
 * Represents the view for the public-facing component of the plugin.
 *
 * This typically includes any information, if any, that is rendered to the
 * frontend of the theme when the plugin is activated.
 *
 */
?>
<div class="rating" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
    <?php include dirname(__FILE__) .  "/rating.php" ?>
    
    <span class="rating-value" itemprop="ratingValue"><?php echo round($rating, 1)?></span>
    <span class="rating-of">/5</span>
    (<span class="rating-count" itemprop="reviewCount"><?php echo $rating_count?></span>
    <span class="rating-name">
    <?php 
        if ( 1 == $rating_count) {
            _e('Review', 'comments2reviews');
        } else {
            _e('Reviews', 'comments2reviews');
        }
    ?>
    </span>)
</div>









