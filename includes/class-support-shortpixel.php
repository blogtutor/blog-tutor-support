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
		add_action( 'admin_head', array( $this, 'hide_shortpixel_bulk_button' ) );
  }

	public function is_shortpixel_bulk_optimize_set() {
 		
		$options = get_option( 'blog_tutor_support_settings', array() );

		if ( ! isset( $options['shortpixel_bulk_optimize'] ) && ! NerdPress_Helpers::is_nerdpress() && defined( 'SHORTPIXEL_HIDE_API_KEY' ) ) {
			add_action( 'admin_menu', function () {
				remove_submenu_page( 'upload.php', 'wp-short-pixel-bulk' );
			}, 20 );
  	}
	}

	public function hide_shortpixel_bulk_button() {

		$current_screen = get_current_screen();
		$options = get_option( 'blog_tutor_support_settings', array() );
		
		if ( ! isset( $options['shortpixel_bulk_optimize'] ) && $current_screen->id === 'settings_page_wp-shortpixel-settings' && ! NerdPress_Helpers::is_nerdpress()  && defined( 'SHORTPIXEL_HIDE_API_KEY' ) ) {
			echo '<style type="text/css">.wp-shortpixel-options #bulk {display: none;}</style>';
		}
	}
}

new NerdPress_Support_ShortPixel();	


