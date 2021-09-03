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
	 */
	public function ping_relay() {

		// The HTML must be escaped to prevent JSON errors on the relay server.
		function filter_htmlspecialchars( &$value ) {
			$value = htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
		}

		// Check if get_plugins() function exists. This is required on the front end of the
		// site, since it is in a file that is normally only loaded in the admin.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( isset( get_option( 'blog_tutor_support_settings' )['relay_key'] ) ) {

			$current_plugins = get_plugins();
			array_walk_recursive( $current_plugins, "filter_htmlspecialchars" );

			$relay_url = get_option( 'blog_tutor_support_settings' )['relay_url'] . '/wp-json/wp/v2/site_snapshot';
			$relay_key                        = get_option( 'blog_tutor_support_settings' )['relay_key'];
			$user                             = parse_url( get_bloginfo( 'wpurl' ) )['host'];
			$options                          = get_option( 'blog_tutor_support_settings', array() );
			$dump                             = array();
			$dump['Free Disk Space']          = NerdPress_Helpers::format_size( NerdPress_Helpers::get_disk_info()['disk_free'] );
			$dump['Firewall Setting']         = $options['firewall_choice'];
			$dump['Domain']                   = $user;
			$dump['All Plugins']              = $current_plugins;
			$dump['Currently Active Plugins'] = get_option( 'active_plugins' );

			// The notes field is NULL on first install, so we check if it's present.
			if ( isset( get_option( 'blog_tutor_support_settings' )['admin_notice'] ) ) {
				$dump['Notes'] = get_option( 'blog_tutor_support_settings' )['admin_notice'];
			}

			// Create timestamp in PST for timestamp of the request.
			$datetime = new DateTime('NOW', new DateTimeZone('PST'));
			$dump['Last Sync']				  = $datetime->format('Y-m-d H:i:s (e)');

			if ( isset( $_GET['ping'] ) ) {
				// Make request to the relay server
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

				// Need to add error handling here, there might be a redirect problem.
				// TODO investigate with Sergio.
				if ( $api_response['response']['code'] === 201 ) {
					nocache_headers();
					wp_safe_redirect( $_SERVER['HTTP_REFERER'] );
				}
			}
		}
	}
}

add_action( 'init', array( 'NerdPress_Support_Relay', 'init' ) );
