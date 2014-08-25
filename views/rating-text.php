<span itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
	<span class="rating-value" itemprop="ratingValue">
		<?php echo round( $rating, 1 )?>
	</span>
	<span class="rating-of">/ 5</span>
	(<span class="rating-count" itemprop="reviewCount">
		<?php echo $rating_count?>
	</span>
	<span class="rating-name">
		<?php ( 1 == $rating_count ) ? _e( 'Review', 'comments2reviews' ) : _e( 'Reviews', 'comments2reviews' )?>
	</span>)
    <meta itemprop="bestRating" content="5" />
    <meta itemprop="worstRating" content="1" />
</span>