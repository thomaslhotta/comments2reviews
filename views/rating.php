<div class="stars">
    <?php for ( $i = 1; $i < (6) ; $i++ ): 
        if ( $i <= $rating ) {
            $class = 'icon-star';
        } elseif ( $i > ceil( $rating ) ) {
            $class = 'icon-star-empty';
        } else {
            $class = 'icon-star-half-empty';
        }
    ?>
    <i class="<?php echo $class?>"></i>
    <?php endfor;?>
</div>
