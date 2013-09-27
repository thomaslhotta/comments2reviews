<div class="row rating-stats">

<?php
$total = array_sum( $stats );

foreach ( array_reverse( $stats, true ) as $rating => $stat ) :
	if ( 0 == $total  ) {
		$val = 0;
	} else {
		$val = ( $stat / $total ) * 100;
	}
	
?>
	<a href=<?php echo add_query_arg( 'rating', $rating )?>> 
		<div class="col-xs-3 stars">
		
		<?php for ($i = 0; $i < $rating; $i++): ?>
			<i class="icon-star"></i>
		<?php endfor;?>
		
		</div>
		<div class="col-xs-9" >
	    	<div class="progress">
	        	<div class="progress-bar" role="progressbar" aria-valuenow="<?php echo $val?>%" aria-valuemin="0%" aria-valuemax="100%" style="width: <?php echo $val?>%;">
	            </div>
		    </div>
	    </div>
    </a>
<?php endforeach;?>
</div>