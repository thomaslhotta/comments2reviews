<?php include dirname( __FILE__ ) . '/rating.php' ?>
   
<?php if ( $title ) :?>
	<span class="review-title" itemprop="name"><?php echo esc_html( $title ) ?></span>
<?php endif;?>
<span itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
	<span itemprop="ratingValue" class="review-rating-value"><?php echo esc_html( $rating ) ?></span>
</span>
<span itemprop="description"><?php echo esc_html( $text )?></span>
<meta itemprop="itemreviewed" content="<?php echo esc_attr( get_the_title() )?>"/>

