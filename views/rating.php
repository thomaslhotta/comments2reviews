<?php
	if ( !isset( $rating ) ) {
		$rating = 0;
	}
?>
<div class="stars">
    <?php for ( $i = 1; $i < (6) ; $i++ ): 
        if ( $i <= $rating ) {
            $class = 'fa-star';
        } elseif ( $i > ceil( $rating ) ) {
            $class = 'fa-star-o';
        } else {
            $class = 'fa-star-half-empty';
        }
    ?>
    <i class="fa <?php echo $class?>"></i>
    <?php endfor;?>
</div>
