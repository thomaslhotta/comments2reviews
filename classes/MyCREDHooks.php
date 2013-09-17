<?php
/**
 * Loads myCRED hooks
 * 
 * @author Thomas Lhotta
 * @package Comment 2 Reviews
 *
 */
class MyCREDHooks
{
    
    /**
     * Loads the mycred hooks.
     */
    public function load()
    {
        require_once dirname(__FILE__) . '/MyCREDReview.php';
        add_filter( 'mycred_setup_hooks', array( $this, 'add_hooks' ) );
    }
    
    /**
     * Add hooks to myCRED.
     * 
     * @param array $hooks
     * @return array
     */
    public function add_hooks( $hooks )
    {
        $slug = Comments_2_Reviews::get_instance()->get_plugin_slug();
        
        
        $hooks['comments2reviews_review'] = array(
            'title'       => __( '%plural% for creating a review' ),
            'description' => __( 'Triggered when a user creates a review.'),
            'callback'    => array( 'MycredReview' )
        );
    
        return $hooks;
    }
}