<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

	/**
	 * Blog_Tutor_Support helper class
	 *
	 * @package  Blog_Tutor_Support
	 * @category Core
	 * @author Sergio Scabuzzo
	 */

class NerdPress_Support_Relay {

	public static function init() {
		$class = __CLASS__;
		new $class; 
	}

	public function __construct() {
		add_action( 'wp_loaded', array( $this, 'ping_relay' ) );
	}

 	/**
	 * Assemble and send data dump ping to relay API endpoint
 	 *
	 * @since 0.8.2
	 *
	 */
	public function ping_relay() {
		$relay_url = 'https://relay.nerdpress.net/wp-json/wp/v2/posts';
		$user      = parse_url( get_bloginfo( 'wpurl' ) )['host'];
		$password  = isset( get_option( 'blog_tutor_support_settings' )['relay_key'] );
		$dump      = $_SERVER; 

		if ( isset( $_GET['ping'] ) ) {
			// Make request
			$api_response = wp_remote_post( $relay_url, array(
				'headers' => array( 
					'Authorization' => 'Basic ' . base64_encode( "$user:$password" ),
				),
				'body' => array(
					'title'       => parse_url( get_bloginfo( 'wpurl' ) )['host'],
					'content'     => json_encode( $dump ),
				)
			) );
			var_dump( $api_response );
		}
	}
}

add_action( 'init', array( 'NerdPress_Support_Relay', 'init' ) );
