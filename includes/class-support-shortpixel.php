<?php
if ( !defined('ABSPATH') )
	die();

	/**
	 * NerdPress_Support_ShortPixel
	 *
	 * @package  NerdPress
	 * @category Core
	 * @author Sergio Scabuzzo
	 */

class NerdPress_Support_ShortPixel {
	/**
	 * Initialize the settings.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'is_shortpixel_bulk_optimize_set' ) );
  }

	public function is_shortpixel_bulk_optimize_set() {
 		
		$options = get_option( 'blog_tutor_support_settings', array() );

		if ( NerdPress_Helpers::hide_shortpixel_settings() ) {
			add_action( 'admin_menu', function () {
				remove_submenu_page( 'upload.php', 'wp-short-pixel-bulk' );
				remove_submenu_page( 'options-general.php', 'wp-shortpixel-settings' );
			}, 20 );
		}
	}
}

new NerdPress_Support_ShortPixel();
