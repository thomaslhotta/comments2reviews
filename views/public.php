<?php
/**
 * Represents the view for the public-facing component of the plugin.
 *
 * This typically includes any information, if any, that is rendered to the
 * frontend of the theme when the plugin is activated.
 *
 * @package   Plugin_Name
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2013 Your Name or Company Name
 */
?>
<div class="rating" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
    <?php include dirname(__FILE__) .  "/rating.php" ?>
    
    <span itemprop="ratingValue"><?php echo round($rating, 1)?></span>/5
    (<span itemprop="reviewCount"><?php echo $rating_count?></span>
    <?php 
        if ( 1 ==$rating_count) {
            echo __('Review');
        } else {
            echo __('Reviews');
        }
        
    ?>
    
    )
</div>









