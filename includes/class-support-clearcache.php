<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

	/**
	 * NerdPress Clear Cache
	 *
	 * @package  NerdPress
	 * @category Core
	 * @author Andrey Kalashnikov
	 */

class NerdPress_Clearcache {

	public function __construct() {
		add_action( 'admin_notices', array( $this, 'nerdpress_clearcache_message' ), 59 );
		add_action( 'wp_ajax_sucuri_clearcache', array( $this, 'sucuri_clearcache' ) );
		add_action( 'init', array( $this, 'bt_enqueue_scripts' ) );
	}

	public function nerdpress_clearcache_message() {
		if ( ! array_key_exists( 'np_clear_sucuri', $_GET ) ) {
			return;
		}

		$clearcache_msg = get_option( 'clear_cache_msg' );
		delete_option( 'clear_cache_msg' );
		NerdPress_Helpers::display_notification( $clearcache_msg );
	}

	public function sucuri_clearcache() {
		check_ajax_referer( 'sucuri_clearcache_secure_me', 'sucuri_clearcache_nonce' );

		$sucuri_api_call_array = NerdPress_Helpers::get_sucuri_api_call();
		if ( is_array( $sucuri_api_call_array ) ) {
			$sucuri_api_call  = implode( $sucuri_api_call_array );
			$cloudproxy_clear = $sucuri_api_call . '&a=clearcache';
			$args             = array( 'timeout' => 30 );
			$response         = wp_remote_get( $cloudproxy_clear, $args );
			if ( is_wp_error( $response ) ) {
				echo false;
				die();
			}

			$body = wp_remote_retrieve_body( $response );
			try {
				$message_body = json_decode( $body, true );
			} catch ( Exception $e ) {
				// TODO: Notfiy us when this happens.
				// Overrwrite the returned object
				$message_body           = array();
				$message_body['status'] = 0;
			}

			$message = (
				$message_body['status'] === 0
				? 'There was a problem clearing the Sucuri Firewall cache. Please try again, and if it still doesn\'t work please contact support@nerdpress.net.'
				: $message_body['messages']['0']
			);

			$option_payload = array(
				'msg'    => sanitize_text_field( $message ),
				'status' => sanitize_text_field( $message_body['status'] ),
			);
			update_option( 'clear_cache_msg', $option_payload );

			// Send message to awaiting JS
			echo ( $message_body['status'] === 0 ? false : $message );
			die();
		}

		die();
	}

	public function bt_enqueue_scripts() {
		if ( user_can( get_current_user_id(), 'edit_posts' ) ) {
			wp_enqueue_script( 'jquery' );
			wp_register_script( 'clearcache_js', esc_url( NerdPress::$plugin_dir_url . 'includes/js/bt-clearcache.js' ), array(), BT_PLUGIN_VERSION );
			wp_localize_script(
				'clearcache_js',
				'sucuri_clearcache',
				array(
					'endpoint' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'sucuri_clearcache_secure_me' ),
				)
			);
			wp_enqueue_script( 'clearcache_js' );
		}
	}
}

new NerdPress_Clearcache();
