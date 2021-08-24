<?php
if ( !defined('ABSPATH') )
	die();

	/**
	 * NerdPress_Support_Updates
	 *
	 * @package  NerdPress
	 * @category Core
	 * @author Sergio Scabuzzo
	 */

class NerdPress_Support_Updates {
	/**
	 * Initialize the settings.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'is_auto_update_set' ) );
  }

	public function is_auto_update_set() {

		$options = get_option( 'blog_tutor_support_settings', array() );
 		
		if ( ! isset( $options['auto_update_plugins'] ) && ! NerdPress_Helpers::is_nerdpress() ) {
			add_filter( 'plugins_auto_update_enabled', '__return_false' );
			add_filter( 'auto_update_plugin', '__return_false' );
  	}
		
		if ( ! isset( $options['auto_update_themes'] ) && ! NerdPress_Helpers::is_nerdpress() ) {
			add_filter( 'themes_auto_update_enabled', '__return_false' );
			add_filter( 'auto_update_theme', '__return_false' );
  	}
	}
}

new NerdPress_Support_Updates();
