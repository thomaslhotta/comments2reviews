<?php 
// Displays the stats.
?>
<div class="rating-stats">

<?php
$total = array_sum( $stats );

$base_url = add_query_arg( 'rating', 0 );

// Remove the comment page part from the url if it exists.
if ( get_query_var( 'cpage' ) ) {
	$base_url = str_replace( '/comment-page-' . get_query_var( 'cpage' ), '', $base_url );
}

foreach ( array_reverse( $stats, true ) as $rating => $stat ) :
	if ( 0 == $total  ) {
		$val = 0;
	} else {
		$val = ( $stat / $total ) * 100;
	}
	
?>
	<?php if( $val > 0 ) : ?><a href="<?php echo esc_url( add_query_arg( 'rating', $rating, $base_url ) )?>"> <?php endif?>
		<div class="row">
			<div class="col-xs-4 stars">
			
			<?php for ( $i = 0; $i < $rating; $i++ ): ?>
				<i class="icon-star"></i>
			<?php endfor;?>
			
			</div>
			<div class="col-xs-8" >
		    	<div class="progress">
		        	<div class="progress-bar" role="progressbar" aria-valuenow="<?php echo $val?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $val?>%;">
		            </div>
			    </div>
		    </div>
	    </div>
	<?php if( $val > 0 ):?></a><?php endif;?>
<?php endforeach;?>
</div>