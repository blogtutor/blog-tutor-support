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
	 * Send a request to the relay without an HTTP request
	 *
	 * @since 1.0.0
	 */
	public static function ping_relay_headless() {

		$dump         = self::assemble_dump();
		$api_response = self::send_request_to_relay( $dump );

	}

	/**
	 * Ping the relay server if the PING is set in the GET request.
	 *
	 * @since 0.8.2
	 */
	public static function ping_relay() {

		// If the request is a one-time call from the dashboard.
		if ( isset( $_GET['ping'] ) ) {

			$dump = self::assemble_dump();

			$api_response = self::send_request_to_relay( $dump );

			if ( $api_response['response']['code'] === 201 ) {
				nocache_headers();
				wp_safe_redirect( $_SERVER['HTTP_REFERER'] );
			}
		}
	}

	/**
	 * Send the request to the relay server useing wp_remote_post.
	 *
	 * @since 0.8.2
	 */
	public static function send_request_to_relay( $dump ) {

			$relay_url = get_option( 'blog_tutor_support_settings' )['relay_url'] . '/wp-json/wp/v2/site_snapshot';
			$relay_key = get_option( 'blog_tutor_support_settings' )['relay_key'];
			$user      = parse_url( get_bloginfo( 'wpurl' ) )['host'];

			// Make request to the relay server.
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

			return $api_response;
	}


	/**
	 * Assemble the data to send to the relay server.
	 *
	 * @since 0.8.2
	 */
	public static function assemble_dump() {
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
			$current_theme   = wp_get_theme();
			array_walk_recursive( $current_plugins, 'filter_htmlspecialchars' );
			require ABSPATH . WPINC . '/version.php';

			$user                             = parse_url( get_bloginfo( 'wpurl' ) )['host'];
			$options                          = get_option( 'blog_tutor_support_settings', array() );
			$dump                             = array();
			$dump['Free Disk Space']          = NerdPress_Helpers::format_size( NerdPress_Helpers::get_disk_info()['disk_free'] );
			$dump['Firewall Setting']         = $options['firewall_choice'];
			$dump['Domain']                   = $user;
			$dump['All Plugins']              = $current_plugins;
			$dump['Currently Active Plugins'] = get_option( 'active_plugins' );
			$dump['Active Theme']             = $current_theme['Name'];
			$dump['Active Theme Version']     = $current_theme['Version'];
			$dump['Plugin Update Data']       = get_option( '_site_transient_update_plugins' )->response;
			$dump['WordPress Version']        = $wp_version;
			$dump['Inactive Themes Data']     = wp_get_themes();

			// Removing the active theme from the theme data.
			$i = -1;
			foreach ( $dump['Inactive Themes Data'] as $key => $value ) {
				$i++;
				if ( $value['Name'] == $current_theme['Name'] ) {
					unset( $dump['Inactive Themes Data'][ $key ] );
				}
			}

			// The notes field is NULL on first install, so we check if it's present.
			if ( isset( get_option( 'blog_tutor_support_settings' )['admin_notice'] ) ) {
				$dump['Notes'] = get_option( 'blog_tutor_support_settings' )['admin_notice'];
			}

			// Create timestamp in PST for timestamp of the request.
			$datetime = new DateTime('NOW', new DateTimeZone('PST'));
			$dump['Last Sync']				  = $datetime->format('Y-m-d H:i:s (e)');
			return $dump;
		}
	}
}

add_action( 'init', array( 'NerdPress_Support_Relay', 'init' ) );
