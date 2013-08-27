<?php
/**
 * A plugin that turns comments into reviews.
 *
 *
 * @package   Comments 2 Reviews
 * @author    Thomas Lhotta <th.lhotta@gmail.com>
 * @link      http://example.com
 * @copyright 2013 Thomas Lhotta
 *
 * @wordpress-plugin
 * Plugin Name: Comments 2 Reviews
 * Plugin URI:  TODO
 * Description: A plugin that turns comments into reviews.
 * Version:     1.0.0
 * Author:      Thomas Lhotta
 * Author URI:  TODO
 * Text Domain: plugin-name-locale
 * License:     GPL-2.0+
 * Domain Path: /lang
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// TODO: replace `class-plugin-name.php` with the name of the actual plugin's class file
require_once( plugin_dir_path( __FILE__ ) . 'class-comments-2-reviews.php' );

// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
// TODO: replace Plugin_Name with the name of the plugin defined in `class-plugin-name.php`
//register_activation_hook( __FILE__, array( 'Plugin_Name', 'activate' ) );
//register_deactivation_hook( __FILE__, array( 'Plugin_Name', 'deactivate' ) );

// TODO: replace Plugin_Name with the name of the plugin defined in `class-plugin-name.php`
Comments_2_Reviews::get_instance();