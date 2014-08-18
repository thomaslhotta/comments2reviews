<span class="rating-value" itemprop="value"><?php echo round( $rating, 1 )?></span>
<span class="rating-of">/ 5</span>
(<span class="rating-count" itemprop="count"><?php echo $rating_count?></span>
<span class="rating-name"><?php ( 1 == $rating_count ) ? _e( 'Review', 'comments2reviews' ) : _e( 'Reviews', 'comments2reviews' )?></span>)