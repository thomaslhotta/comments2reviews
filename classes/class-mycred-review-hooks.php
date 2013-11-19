<?php
/**
 * Loads myCRED hooks
 * 
 * @author Thomas Lhotta
 * @package Comment 2 Reviews
 *
 */

class MyCRED_Review_Hooks
{
    
    /**
     * Loads the mycred hooks.
     */
    public function load()
    {
        require_once dirname( __FILE__ ) . '/class-mycred-review.php';
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
        $slug = Comments_2_Reviews::get_instance()->get_settings()->get_plugin_slug();
        
        $hooks['comments2reviews_review'] = array(
            'title'       => __( '%plural% for creating a review', $slug ),
            'description' => __( 'Triggered when a user creates a review.', $slug ),
            'callback'    => array( 'Mycred_Review' )
        );
    
        return $hooks;
    }
}