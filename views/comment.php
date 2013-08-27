<div itemprop="review" itemscope itemtype="http://schema.org/Review">
    <?php include dirname(__FILE__) .  "/rating.php" ?>
    
    <?php if ( isset( $title ) ):?>
        <span itemprop="name"><?php echo $title?></span>
    <?php endif;?>
    <span itemprop="ratingValue"><?php echo $rating ?></span>
    <span itemprop="description"><?php echo $text?></span>
</div>

