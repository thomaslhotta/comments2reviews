<?php

/**
 * Deals with the plugin config.
 *
 * @author Thomas Lhotta
 *
 */
class C2R_Settings {
	protected $enabled_post_types = null;

	protected $slug = 'comments2reviews';

	/**
	 * Returns an array of enabled post types.
	 *
	 * @return array
	 */
	public function get_enabled_post_types() {
		if ( is_array( $this->enabled_post_types ) ) {
			return $this->enabled_post_types;
		}

		$post_types = get_option( $this->get_plugin_slug() . '-enabled_post_types', array() );

		if ( ! is_array( $post_types ) ) {
			$post_types = array();
		}

		$this->enabled_post_types = $post_types;

		return $this->enabled_post_types;
	}

	/**
	 * Returns the plugin slug
	 *
	 * @return string
	 */
	public function get_plugin_slug() {
		return $this->slug;
	}
}
