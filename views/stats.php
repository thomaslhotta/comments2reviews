<?php
// Displays the stats.
?>
<table class="rating-stats">
	<tr>
		<td>
			<?php require __DIR__ . '/rating.php';?>
		</td>
		<td>
			<?php require __DIR__ . '/rating-text.php'; ?>
		</td>
	</tr>
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

	$url = esc_url( add_query_arg( 'rating', $rating, $base_url ) );
?>

		<tr>
			<td class="stars">
				<?php if ( $val > 0 ) : ?><a href="<?php echo $url?>"><?php endif;?>
			<?php for ( $i = 0; $i < $rating; $i++ ) : ?>
				<i class="fa fa-star"></i>
			<?php endfor;?>
				<?php if ( $val > 0 ) : ?></a><?php endif;?>
			</td>
			<td style="width:100%">
				<?php if ( $val > 0 ) : ?><a href="<?php echo $url?>"><?php endif;?>
					<div class="progress">
		        		<div class="progress-bar" role="progressbar" aria-valuenow="<?php echo $val?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $val?>%;">
		            	</div>
					</div>
				<?php if ( $val > 0 ) : ?></a><?php endif;?>
		    </td>
	    </tr>
<?php endforeach;?>
</table>