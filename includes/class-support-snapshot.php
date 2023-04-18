<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * NerdPress_Support_Snapshot
 *
 * @package  NerdPress
 * @category Core
 * @author Apsis Labs
 */

class NerdPress_Support_Snapshot {
	public static function init() {
		$class = __CLASS__;
		new $class();
	}

	public function __construct() {
		add_action( 'wp_loaded', array( $this, 'ping_relay' ) );
		add_action( 'wp_loaded', array( $this, 'schedule_snapshot_cron' ) );

		add_action( 'np_scheduled_snapshot', array( $this, 'take_snapshot' ) );
	}

	/**
	 * Ping the relay server if the PING is set in the GET request.
	 */
	public static function ping_relay() {
		// If the request is a one-time call from the relay.
		if (
			isset( $_REQUEST['_snapshot_nonce'] )
			&& wp_verify_nonce( $_REQUEST['_snapshot_nonce'], 'np_snapshot' )
			&& isset( $_GET['np_snapshot'] )
			&& NerdPress_Helpers::is_relay_server_configured()
		) {
			self::take_snapshot();
			if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
				wp_safe_redirect( wp_unslash( $_SERVER['HTTP_REFERER'] ) );
			}
			die;
		}
	}

	public function schedule_snapshot_cron() {
		if ( ! wp_next_scheduled( 'np_scheduled_snapshot' ) ) {
			wp_schedule_event( time(), 'twicedaily', 'np_scheduled_snapshot' );
		}

		if ( ! NerdPress_Helpers::is_relay_server_configured() ) {
			if ( wp_next_scheduled( 'np_scheduled_snapshot' ) ) {
				wp_clear_scheduled_hook( 'np_scheduled_snapshot' );
			}
		}
	}

	public static function take_snapshot() {
		$dump         = self::assemble_snapshot();
		$api_response = self::send_request_to_relay( $dump );

		return $api_response;
	}

	public static function send_request_to_relay( $dump ) {

		if ( defined( 'SSLVERIFY_DEV' ) && SSLVERIFY_DEV === false ) {
			$status = false;
		} else {
			$status = true;
		}

		$relay_url = NerdPress_Helpers::relay_server_url() . 'wp-json/nerdpress/v1/snapshot';
		$api_token = NerdPress_Helpers::relay_server_api_token();

		$args = array(
			'headers'   => array(
				'Authorization' => "Bearer $api_token",
				'Content-Type'  => 'application/json',
				'Domain'        => site_url(),				
			),
			'body'      => wp_json_encode( $dump ),
			// Bypass SSL verification when using self
			// signed cert. Like when in a local dev environment.
			'sslverify' => $status,
		);

		// Make request to the relay server.
		$api_response = wp_remote_post( $relay_url, $args );

		return $api_response;
	}


	public static function assemble_snapshot() {
		// The HTML must be escaped to prevent JSON errors on the relay server.
		function filter_htmlspecialchars( &$value ) {
			$value = htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
		}

		// Check if get_plugins() function exists. This is required on the front end of the
		// site, since it is in a file that is normally only loaded in the admin.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$current_plugins       = get_plugins();
		$mu_plugins            = get_mu_plugins();
		$current_theme         = wp_get_theme();
		$active_plugins_option = get_option( 'active_plugins' );
		$active_plugins        = self::filter_active_plugins( get_plugins(), $active_plugins_option );
		$inactive_plugins      = self::filter_inactive_plugins( get_plugins(), $active_plugins_option );

		array_walk_recursive( $current_plugins, 'filter_htmlspecialchars' );
		array_walk_recursive( $mu_plugins, 'filter_htmlspecialchars' );
		array_walk_recursive( $active_plugins, 'filter_htmlspecialchars' );
		array_walk_recursive( $inactive_plugins, 'filter_htmlspecialchars' );

		require ABSPATH . WPINC . '/version.php';

		$user                              = wp_parse_url( get_bloginfo( 'wpurl' ) )['host'];
		$options                           = get_option( 'blog_tutor_support_settings', array() );
		$disk_space                        = NerdPress_Helpers::get_disk_info()['disk_free'];
		$dump                              = array();
		$dump['free_disk_space']           = $disk_space === 'Unavailable' ? null : $disk_space;
		$dump['firewall_setting']          = self::format_firewall_choice( $options );
		$dump['document_root']             = isset( $_SERVER['DOCUMENT_ROOT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['DOCUMENT_ROOT'] ) ) : null;
		$dump['php_version']               = phpversion();
		$dump['domain']                    = $user;
		$dump['all_plugins']               = $current_plugins;
		$dump['mu_plugins']                = $mu_plugins;
		$dump['active_plugins']            = $active_plugins;
		$dump['inactive_plugins']          = $inactive_plugins;
		$dump['active_theme']              = $current_theme['Name'];
		$dump['active_theme_version']      = $current_theme['Version'];
		$dump['plugin_update_data']        = get_option( '_site_transient_update_plugins' )->response;
		$dump['wordpress_version']         = $wp_version;
		$dump['notes']                     = $options['admin_notice'];
		$dump['server_software']           = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : null;
		$dump['sucuri_api_key']            = NerdPress_Helpers::is_sucuri_firewall_api_key_set() ? implode( '/', NerdPress_Helpers::get_sucuri_api() ) : null;
		$dump['sucuri_notification_email'] = NerdPress_Helpers::is_sucuri_notification_email_set() ? NerdPress_Helpers::get_sucuri_notification_email() : null;
		$dump['wp_blog_public']            = ! ! get_option( 'blog_public' );
		$dump['wp_users_can_register']     = ! ! get_option( 'users_can_register' );
		$dump['wp_default_role']           = get_option( 'default_role' );
		$dump['inactive_themes_data']      = wp_get_themes();

		// Removing the active theme from the theme data.
		$i = -1;
		foreach ( $dump['inactive_themes_data'] as $key => $value ) {
			$i++;
			if ( $value['Name'] === $current_theme['Name'] ) {
				unset( $dump['inactive_themes_data'][ $key ] );
			}
		}

		// The notes field is NULL on first install, so we check if it's present.
		if ( isset( get_option( 'blog_tutor_support_settings' )['admin_notice'] ) ) {
			$dump['notes'] = get_option( 'blog_tutor_support_settings' )['admin_notice'];
		}

		return $dump;
	}

	private static function format_firewall_choice( $options ) {
		if ( $options['firewall_choice'] === 'cloudflare' ) {
			$firewall = 'Cloudflare';
			$zone     = '';

			if ( $options['cloudflare_zone'] === 'dns1' ) {
				$zone = '1';
			} elseif ( $options['cloudflare_zone'] === 'dns2' ) {
				$zone = '2';
			} elseif ( $options['cloudflare_zone'] === 'dns3' ) {
				$zone = '3';
			}

			return $firewall . ' ' . $zone;
		}

		if ( $options['firewall_choice'] === 'sucuri' ) {
			return 'Sucuri';
		}

		return 'None/Other';
	}


	private static function filter_active_plugins( $all_plugins, $active_plugins ) {
		return array_filter( $all_plugins, fn ( $key ) => in_array( $key, $active_plugins, true ), ARRAY_FILTER_USE_KEY );
	}

	private static function filter_inactive_plugins( $all_plugins, $active_plugins ) {
		return array_filter( $all_plugins, fn ( $key ) => ! in_array( $key, $active_plugins, true ), ARRAY_FILTER_USE_KEY );
	}
}

add_action( 'init', array( 'NerdPress_Support_Snapshot', 'init' ) );
