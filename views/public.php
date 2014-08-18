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
    <?php include __DIR__  .  "/rating.php" ?>
    <?php include __DIR__  .  "/rating-text.php" ?>
</div>









