<?php include dirname( __FILE__ ) . "/rating.php" ?>
   
<?php if ( $title ):?>
	<a href="<?php echo get_comment_link()?>">
		<span class="review-title" itemprop="name"><?php echo $title?></span>
    </a>
<?php endif;?>
<span itemprop="rating" itemscope itemtype="http://data-vocabulary.org/Rating">
	<span class="review-rating-value" itemprop="rating"><?php echo $rating ?></span>
</span>
<span itemprop="description"><?php echo $text?></span>
<meta itemprop="itemreviewed" content="<?php echo get_the_title()?>"/>

