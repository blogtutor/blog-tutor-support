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

		// Check if get_plugins() function exists. This is required on the front end of the
		// site, since it is in a file that is normally only loaded in the admin.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( isset( get_option( 'blog_tutor_support_settings' )['relay_key'] ) ) {

			$relay_url = get_option( 'blog_tutor_support_settings' )['relay_url'] . '/wp-json/wp/v2/site_snapshot';
			$relay_key                        = get_option( 'blog_tutor_support_settings' )['relay_key'];
			$user                             = parse_url( get_bloginfo( 'wpurl' ) )['host'];
			$options                          = get_option( 'blog_tutor_support_settings', array() );
			$dump                             = array();
			$dump['Free Disk Space']          = NerdPress_Helpers::format_size(NerdPress_Helpers::get_disk_info()['disk_free']);
			$dump['Firewall Setting']         = $options['firewall_choice'];
			$dump['Domain']                   = $user;
			$dump['All Plugins']              = get_plugins();
			$dump['Currently Active Plugins'] = get_option('active_plugins');

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
					),
					// Bypass SSL verification in self-signed environments
					//'sslverify' => false
				) );

				// Need to add error handling here 

				if ( $api_response['response']['code'] === 201 ) {
					nocache_headers();
					wp_safe_redirect( $_SERVER['HTTP_REFERER'] );
				}
			}
		}
	}
}

add_action( 'init', array( 'NerdPress_Support_Relay', 'init' ) );
