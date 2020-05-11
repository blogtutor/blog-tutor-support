<?php
if( !defined( 'ABSPATH' ) ) {
	exit;
}

	/**
	 * Blog_Tutor_Support helper class
	 *
	 * @package  Blog_Tutor_Support
	 * @category Core
	 * @author Andrey Kalashnikov
	 */

class Blog_Tutor_Support_Cloudproxy {
	private $whitelist_option_name = 'cloudproxy_wl_ips';
	private $err_counter_option = 'nerdpress_whitelist_errors'; 

	public static function init() {
		if( ! current_user_can( 'edit_posts' ) )
			return;
		$class = __CLASS__;
		new $class; 
	}

	public function __construct() {
		// Schedule a cron job that wipes all the whitelisted ips
		if( !wp_next_scheduled( 'bt_remove_whitelist_cron' ) )
			wp_schedule_event( time(), 'twicedaily', 'bt_remove_whitelist_cron' );

		add_action( 'wp_ajax_whitelist_ip', array( $this, 'whitelist_cloudproxy_ip' ) );
		add_action( 'wp_ajax_clear_whitelist', array( $this, 'clear_whitelist' ) );
		add_action( 'admin_footer', array( $this, 'bt_enqueue_scripts' ) );
	}

	public function bt_enqueue_scripts() {
		wp_register_script( 'whitelist_js', plugins_url( 'js/bt-whitelist.js', __FILE__ ), array(), BT_PLUGIN_VERSION );
		wp_localize_script( 'whitelist_js', 'sucuri_whitelist', array(
			'endpoint' => admin_url( 'admin-ajax.php' ),
			'nonce'	   => wp_create_nonce( 'sucuri_whitelist_secure_me' ),
		));
		wp_enqueue_script( 'whitelist_js' );
	}

	/**
	 * Whitelist the user's ip with the Sucuri Firewall
	 */
	public function whitelist_cloudproxy_ip() {
		// Since the method is called from the JS's AJAX context
		// verify the nonce
		check_ajax_referer('sucuri_whitelist_secure_me', 'sucuri_whitelist_nonce');

		// Terminate the operation, if the user is a nerdpress admin
		if( Blog_Tutor_Support_Helpers::is_nerdpress() ) {
			echo 'np_no_message';
			die();
		}

		$return_str = FALSE;
		$npSuffix = '';

		$client_ip = $_SERVER['HTTP_X_SUCURI_CLIENTIP'];
		$sucuri_api_call_array = Blog_Tutor_Support_Helpers::get_sucuri_api_call();
		$errors = get_option( $this->err_counter_option ); 
		if ( $client_ip && is_array( $sucuri_api_call_array ) && $errors[$client_ip] < 3 ) {
			// Make sure the option isn't cached
			if ( wp_cache_get ( $this->whitelist_option_name, 'options' ) )
				wp_cache_delete ( $this->whitelist_option_name, 'options' );

			// In case the option was empty and get_option returned an empty string
			if ( ( $whitelisted_ips = get_option( $this->whitelist_option_name, array() ) ) === 0 )
				$whitelisted_ips = array();
		   
			$return_str = 'IP is already whitelisted';
			// Whitelist if not in the whitelist list
			if ( ! in_array( $client_ip, $whitelisted_ips ) ) {
				// Get the Sucuri's Cloudproxy endpoint
				$sucuri_api_call = implode( $sucuri_api_call_array );
				$cloudproxy_endpoint = $sucuri_api_call . '&ip=' . $client_ip . '&a=whitelist&duration=' . (24 * 3600);
				$args = array( 'timeout' => 15 );

				$response = wp_remote_get( $cloudproxy_endpoint, $args );
				if( is_wp_error( $response ) ) {
					/**
					 * Start storing the errors, once the error count for the same
					 * IP exceeds 3, send an alert
					 */
					$this->processWLError( $response, $client_ip, 'Timeout exceeded for Sucuri whitelisting endpoint' );
					echo 'Error';
					die();
				}

				$body = wp_remote_retrieve_body( $response );
				try {
					$message = json_decode($body, TRUE);
					$return_str = $message['messages'][0];
					$this->save_whitelist_meta( $body, $whitelisted_ips );
					if( $return_str == 'Invalid domain' ) {
						$this->processWLError( $message, $client_ip, 'Invalid Sucuri API Key' );
					}
				} catch(Exception $e) {
					echo 'Error parsing JSON response from Sucuri';
					die();
				}
			}

		}
		echo $return_str . $npSuffix;
		die();
	}

	/**
	 * Clear/remove the whitelist option from the table
	 * Remove the attempt count for the whitelist
	 *
	 * @since
	 */
	public function remove_whitelist_cron() {
		delete_option( $this->whitelist_option_name );	
		delete_option( $this->err_counter_option );
	}

	/**
	 * Clear the whitelist option in the options table
 	 * This method is used by a JS AJAX call
	 *
	 * @since 
	 */
	public function clear_whitelist() {
		check_ajax_referer('clear_whitelist_secure_me', 'clear_whitelist_nonce');
		$this->remove_whitelist_cron();
	}

	/**
	 * Save all whitelisted ips to the options table
 	 *
	 * @since
	 *
	 * @param string $body. JSON payload sent by Sucuri
	 * @param array $whitelisted_ips. An array of IPv4/IPv6 address strings
	 *
	 */ 
	private function save_whitelist_meta( $body, $whitelisted_ips ) {
		$ip_address = filter_var( json_decode( $body, true )['output'][0], FILTER_VALIDATE_IP );
		if( ! $ip_address ) return;

		$whitelisted_ips[] = $ip_address;
		update_option( $this->whitelist_option_name, $whitelisted_ips );
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
		$error['ip'] = $_SERVER['HTTP_X_SUCURI_CLIENTIP'];
		if ( ! isset( $error['error_msg'] ) )
			$error['error_msg'] = 'Unknown Error';
		$error['user'] = $current_user->user_login;

		// Make request
		wp_remote_post( $url, array(
			'headers' => array( 
				'Content-Type' => 'application/json'
			),
			'body' => json_encode($error),
			'method' => 'POST',
			'data_format' => 'body'
		) ); 
	}

	private function processWLError( $response, $client_ip, $msg ) {
		$errors = get_option( $this->err_counter_option, array() );
		if ( isset( $errors[$client_ip] ) && 3 > ++$errors[$client_ip] ) {
			// ignore
		} elseif ( ! isset( $errors[$client_ip] ) ) {
			$errors[$client_ip] = 1;
		} else {
			$this->alert_hook( array( 'error_msg' => $msg ) );
		}

		$errors['last_response'] = serialize( $response );
		update_option( $this->err_counter_option, $errors );
	}
}

function remove_whitelist_cron() {
    delete_option( 'cloudproxy_wl_ips' );	
}

add_action( 'init', array( 'Blog_Tutor_Support_Cloudproxy', 'init' ) );
add_action( 'bt_remove_whitelist_cron', 'remove_whitelist_cron' );
