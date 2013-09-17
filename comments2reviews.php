<?php
/**
 * A plugin that turns comments into reviews.
 *
 *
 * @package   Comments 2 Reviews
 * @author    Thomas Lhotta <th.lhotta@gmail.com>
 * @link      http://www.github.com/thomaslhotta
 * @copyright 2013 Thomas Lhotta
 *
 * @wordpress-plugin
 * Plugin Name: Comments 2 Reviews
 * Plugin URI:  TODO
 * Description: A plugin that turns comments into reviews.
 * Version:     1.0.0
 * Author:      Thomas Lhotta
 * Author URI:  http://www.github.com/thomaslhotta
 * Text Domain: plugin-name-locale
 * License:     GPL-2.0+
 * Domain Path: /lang
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'COMMENTS_2_REVIEWS_DIR', dirname( __FILE__ ) );

require_once( dirname( __FILE__ ) . '/classes/class-comments-2-reviews.php' );

Comments_2_Reviews::get_instance();