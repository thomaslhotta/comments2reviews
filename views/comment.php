    <?php include dirname(__FILE__) . "/rating.php" ?>
    
    <?php if ( $title ):?>
        <span class="review-title" itemprop="name"><?php echo $title?></span>
    <?php endif;?>
    <span class="review-rating-value" itemprop="ratingValue"><?php echo $rating ?></span>
    <span itemprop="description"><?php echo $text?></span>

