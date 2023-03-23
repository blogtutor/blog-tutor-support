<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * NerdPress helper class
 *
 * @package  NerdPress
 * @category Core
 * @author Andrey Kalashnikov
 */
class NerdPress_Cloudproxy {
	private $allowlist_option_name = 'cloudproxy_allowlist_ips';
	private $err_counter_option    = 'nerdpress_allowlist_errors';

	public static function init() {
		$class = __CLASS__;
		new $class;
	}

	public function __construct() {
		add_action( 'bt_remove_allowlist_cron', array( $this, 'remove_allowlist_cron' ) );

		if ( current_user_can( 'edit_posts' ) ) {
			add_action( 'wp_ajax_allowlist_ip', array( $this, 'allowlist_cloudproxy_ip' ) );
			add_action( 'wp_ajax_clear_allowlist', array( $this, 'clear_allowlist' ) );
			add_action( 'admin_footer', array( $this, 'bt_enqueue_scripts' ) );
			if ( ! wp_next_scheduled( 'bt_remove_allowlist_cron' ) ) {
				wp_schedule_event( time(), 'twicedaily', 'bt_remove_allowlist_cron' );
			}
		}
	}

	public function bt_enqueue_scripts() {
		wp_register_script( 'allowlist_js', esc_url( NerdPress_Plugin::$plugin_dir_url . 'includes/js/bt-allowlist.js' ), array(), BT_PLUGIN_VERSION );
		wp_localize_script(
			'allowlist_js',
			'sucuri_allowlist',
			array(
				'endpoint' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'sucuri_allowlist_secure_me' ),
			)
		);
		wp_enqueue_script( 'allowlist_js' );
	}

	/**
	 * Add the user's ip to the Sucuri Firewall allowlist
	 */
	public function allowlist_cloudproxy_ip() {
		// Since the method is called from the JS's AJAX context
		// verify the nonce
		check_ajax_referer( 'sucuri_allowlist_secure_me', 'sucuri_allowlist_nonce' );

		// Terminate the operation, if the user is a nerdpress admin
		if ( NerdPress_Helpers::is_nerdpress() ) {
			echo 'np_no_message';
			die();
		}

		$return_str            = false;
		$npSuffix              = '';
		$client_ip             = $_SERVER['HTTP_X_SUCURI_CLIENTIP'];
		$sucuri_api_call_array = NerdPress_Helpers::get_sucuri_api_call();
		$errors                = get_option( $this->err_counter_option );
		if ( $errors === false ) {
			$error_count = 0;
		} else {
			$error_count = $errors[ $client_ip ];
		}
		if ( $client_ip && is_array( $sucuri_api_call_array ) && $error_count < 3 ) {
			// Make sure the option isn't cached
			if ( wp_cache_get( $this->allowlist_option_name, 'options' ) ) {
				wp_cache_delete( $this->allowlist_option_name, 'options' );
			}

			// In case the option was empty and get_option returned an empty string
			if ( ( $allowlist_ips = get_option( $this->allowlist_option_name, array() ) ) === 0 ) {
				$allowlist_ips = array();
			}

			$return_str = 'IP is already on the allowlist';
			// Add to the allowlist if not in list
			if ( ! in_array( $client_ip, $allowlist_ips ) ) {
				// Get the Sucuri's Cloudproxy endpoint
				$sucuri_api_call     = implode( $sucuri_api_call_array );
				$cloudproxy_endpoint = $sucuri_api_call . '&ip=' . $client_ip . '&a=whitelist&duration=' . ( 24 * 3600 );
				$args                = array( 'timeout' => 15 );
				$response            = wp_remote_get( $cloudproxy_endpoint, $args );
				if ( is_wp_error( $response ) ) {
					/**
					 * Start storing the errors, once the error count for the same
					 * IP exceeds 3, send an alert
					 */
					$this->process_allowlist_error( $response, $client_ip, 'Timeout exceeded for Sucuri API endpoint' );
					echo 'Error';
					die();
				}

				$body = wp_remote_retrieve_body( $response );
				try {
					$message    = json_decode( $body, true );
					$return_str = $message['messages'][0];
					$this->save_allowlist_meta( $body, $allowlist_ips );
					if ( $return_str === 'Invalid domain' ) {
						$this->process_allowlist_error( $message, $client_ip, 'Invalid Sucuri API Key' );
					}
				} catch ( Exception $e ) {
					echo 'Error parsing JSON response from Sucuri';
					die();
				}
			}
		}
		echo esc_textarea( $return_str . $npSuffix );
		die();
	}

	/**
	 * Clear/remove the allowlist option from the table
	 * Remove the attempt count for the allowlist
	 *
	 * @since
	 */
	public function remove_allowlist_cron() {
		delete_option( $this->allowlist_option_name );
		delete_option( $this->err_counter_option );
	}

	/**
	 * Clear the allowlist option in the options table
	 * This method is used by a JS AJAX call
	 *
	 * @since
	 */
	public function clear_allowlist() {
		check_ajax_referer( 'clear_allowlist_secure_me', 'clear_allowlist_nonce' );
		$this->remove_allowlist_cron();
	}

	/**
	 * Save all ips on the allowlist to the options table
	 *
	 * @since
	 *
	 * @param string $body. JSON payload sent by Sucuri
	 * @param array $allowlist_ips. An array of IPv4/IPv6 address strings
	 *
	 */
	private function save_allowlist_meta( $body, $allowlist_ips ) {
		$ip_address = filter_var( json_decode( $body, true )['output'][0], FILTER_VALIDATE_IP );
		if ( ! $ip_address ) {
			return;
		}

		$allowlist_ips[] = $ip_address;
		update_option( $this->allowlist_option_name, $allowlist_ips );
	}

	/**
	 * Assemble and send the error to a Zapier endpoint
	 *
	 * @since 0.6.5
	 *
	 * @global WP_User $current_user. Current User
	 *
	 * @param array $error Optional. If there are error other error fields you want to pass
	 */
	private function alert_hook( $error = array() ) {
		global $current_user;

		$url = 'https://hooks.zapier.com/hooks/catch/332669/odd68kq/';

		// Set error object
		$error['domain'] = get_site_url();
		$error['ip']     = $_SERVER['HTTP_X_SUCURI_CLIENTIP'];
		if ( ! isset( $error['error_msg'] ) ) {
			$error['error_msg'] = 'Unknown Error';
		}
		$error['user'] = $current_user->user_login;

		// Make request
		wp_remote_post(
			$url,
			array(
				'headers'     => array(
					'Content-Type' => 'application/json',
				),
				'body'        => wp_json_encode( $error ),
				'method'      => 'POST',
				'data_format' => 'body',
			)
		);
	}

	private function process_allowlist_error( $response, $client_ip, $msg ) {
		$errors = get_option( $this->err_counter_option, array() );
		if ( isset( $errors[ $client_ip ] ) && 3 > ++$errors[ $client_ip ] ) {
			// ignore
		} elseif ( ! isset( $errors[ $client_ip ] ) ) {
			$errors[ $client_ip ] = 1;
		} else {
			$this->alert_hook( array( 'error_msg' => $msg ) );
		}

		$errors['last_response'] = serialize( $response );
		update_option( $this->err_counter_option, $errors );
	}
}
