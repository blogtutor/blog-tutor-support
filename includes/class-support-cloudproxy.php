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

        add_action( 'wp_ajax_whitelist_ip', array( $this, 'whitelist_cloudproxy_ip' ) );
        add_action( 'admin_footer', array( $this, 'bt_enqueue_scripts' ) );
	}

    public function bt_enqueue_scripts() {
        wp_register_script( 'whitelist_js', plugins_url( 'js/bt-whitelist.js', __FILE__ ), array());
        wp_localize_script( 'whitelist_js', 'sucuri_whitelist', array(
            'endpoint'              => admin_url( 'admin-ajax.php' ),
            'nonce'                 => wp_create_nonce( 'sucuri_whitelist_secure_me' ),
        ));
        wp_enqueue_script( 'whitelist_js' );
    }

	public function whitelist_cloudproxy_ip() {
        check_ajax_referer('sucuri_whitelist_secure_me', 'sucuri_whitelist_nonce');
        
		$sucuri_api_call_array = Blog_Tutor_Support_Helpers::get_sucuri_api_call();
        $return_str = 'Sucuri Plugin isn\'t active';

        if ( is_plugin_active( 'sucuri-scanner/sucuri.php' ) && is_array( $sucuri_api_call_array ) ):

            if( !user_can( get_current_user_id(), 'manage_options' ) ) {
                echo 'IP cannot be whitelisted for the current user';
                die();
            }

            $whitelisted_ips = get_option( $this->whitelist_option_name, array() );

            $client_ip = $_SERVER['REMOTE_ADDR'];
           
            $return_str = 'IP is already whitelisted'; 
            if( !in_array( $client_ip, $whitelisted_ips ) ) {
                // Get the Sucuri's Cloudproxy endpoint
				$sucuri_api_call = implode( $sucuri_api_call_array );
				$cloudproxy_whitelist = $sucuri_api_call . '&ip=' . $client_ip . '&a=whitelist&duration=' . (24 * 3600);

                $args = array( 'timeout' => 30 );

                $response = wp_remote_get( $cloudproxy_whitelist, $args );
                if( is_wp_error( $response ) ) {
                    echo 'Error: Connection to Sucuri CloudProxy API failed';
                    die();
                }

                $body = wp_remote_retrieve_body( $response );
                $return_str = $this->save_whitelist_meta( $body, $whitelisted_ips );
            }

        endif;
        echo $return_str;
        die();
	}

	public function remove_whitelist_cron() {
		delete_option( $this->whitelist_option_name );	
	}

    private function save_whitelist_meta( $body, $whitelisted_ips ) {
        // TODO: ADD ERROR CHECKING
        $ip_address = json_decode( $body, true )['output'][0];
        $whitelisted_ips[] = $ip_address;

        // Enable once the message is tested
        update_option( $this->whitelist_option_name, $whitelisted_ips );
        return 'IP Whitelisted: ' . $ip_address; 
    }
}

new Blog_Tutor_Support_Cloudproxy();
