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

	public function __construct() {
		// Schedule a cron job that wipes all the whitelisted ips
		add_action( 'bt_remove_whitelist_cron', array( $this, 'remove_whitelist_cron' ), 9 );
		if( !wp_next_scheduled( 'bt_remove_whitelist_cron' ) )
			wp_schedule_event( time(), 'twicedaily', 'bt_remove_whitelist_cron' );

		add_action( 'admin_footer', array( $this, 'whitelist_cloudproxy_ip' ) );
	}

	public function whitelist_cloudproxy_ip() {
		if( !user_can( get_current_user_id(), 'manage_options' ) )
			return;

		$whitelisted_ips = get_option( $this->whitelist_option_name, array() );
	
		// Check if client is using a proxy to whitelist the proxy if needed	
		$client_ip = ($_SERVER['HTTP_X_FORWARDED_FOR'] 
			    ? $_SERVER['HTTP_X_FORWARDED_FOR'] 
			    : $_SERVER['REMOTE_ADDR']);
		
		if( !in_array( $client_ip, $whitelisted_ips ) ) {
			echo '<script type="text/javascript">' .
			     'jQuery.ajax( jQuery( \'#wp-admin-bar-bt-whitelist-cloudproxy a\' ).attr(\'href\') );' .
			     '</script>';
			$whitelisted_ips[] = $client_ip;
			update_option( $this->whitelist_option_name, $whitelisted_ips );
		}
	}

	public function remove_whitelist_cron() {
		delete_option( $this->whitelist_option_name );	
	}
}

new Blog_Tutor_Support_Cloudproxy();
