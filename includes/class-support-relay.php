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
		if ( isset( get_option( 'blog_tutor_support_settings' )['relay_key'] ) ) {
			$relay_url                    = 'https://relay.nerdpress.net/wp-json/wp/v2/site_snapshot';
			$relay_key                    = get_option( 'blog_tutor_support_settings' )['relay_key'];
			$user                         = parse_url( get_bloginfo( 'wpurl' ) )['host'];
			$options                      = get_option( 'blog_tutor_support_settings', array() );
			$dump                         = array();
			$dump['disk_percentage_used'] = Blog_Tutor_Support_Helpers::get_disk_info()['disk_percentage'];
			$dump['firewall_choice']      = $options['firewall_choice'];

			if ( isset( $_GET['ping'] ) ) {
				// Make request
				$api_response = wp_remote_post( $relay_url, array(
					'headers' => array(
						'Authorization' => 'Basic ' . base64_encode( "$user:$relay_key" ),
					),
					'body' => array(
						'title'       => parse_url( get_bloginfo( 'wpurl' ) )['host'],
						'content'     => json_encode( $dump ),
						'status'      => 'publish',
					)
				) );

				if ( $api_response['response']['code'] === 201 ) {
					nocache_headers();
					wp_safe_redirect( $_SERVER['HTTP_REFERER'] );
				}
			}
		}
	}
}

add_action( 'init', array( 'NerdPress_Support_Relay', 'init' ) );
