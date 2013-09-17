<?php 
class C2R_Query 
{
    public function __construct()
    {
        add_filter('pre_get_posts', array( $this, 'modify_query' ) );
    }
    
    
    public function modify_query( WP_Query $query ) 
    {
        if ( $query->get( 'orderby' ) != 'rating' ) {
            return;
        }
        
        $query->set( 'orderby' , 'meta_value');
        $query->set( 'meta_key' , 'rating_mean' );
        
     
    }
}
