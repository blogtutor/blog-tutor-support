<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

	/**
	 * NerdPress helper class.
	 *
	 * @package  NerdPress
	 * @category Core
	 * @author  Andrew Wilder, Sergio Scabuzzo
	 */
class NerdPress_Helpers {
	private static $sucuri_api_key = FALSE;
	private static $sucuri_buttons_flag = NULL;
	
	private static function set_sucuri_api() {
		if ( defined( 'SUCURI_DATA_STORAGE' ) ) {
			$input_lines = file_get_contents( SUCURI_DATA_STORAGE . '/sucuri-settings.php' );
		} else {
			$upload_dir  = wp_upload_dir( $time = null, $create_dir = null );
			$path        = $upload_dir['basedir'] . '/sucuri/sucuri-settings.php';

			if( file_exists( $path ) )  
				$input_lines = file_get_contents( $path );
			else return;
		}

		// Using # as regex delimiters since / was giving error.
		$regex = '#\"sucuriscan_cloudproxy_apikey\":\"(.{32})\\\/(.{32})#';

		preg_match_all( $regex, $input_lines, $output_array, PREG_SET_ORDER, 0 );

		if ( array_filter( $output_array ) ) {
			self::$sucuri_api_key = array(
				'api_key'    => $output_array[0][1],
				'api_secret' => $output_array[0][2]
			);
		} else self::$sucuri_api_key = array();
	}

	/**
	 * Wrapper method to retrieve the sucuri API static variable
	 */
	public static function get_sucuri_api() {
		return self::$sucuri_api_key;
	}

	/**
	 * Check email address to see if user is a member of the NerdPress team (and also an administrator).
	 */
	public static function is_nerdpress() {
		$current_user = wp_get_current_user();
		return ( current_user_can( 'administrator' ) 
			&& ( strpos( $current_user->user_email, '@blogtutor.com' ) !== false
			|| strpos( $current_user->user_email, '@nerdpress.net' ) !== false ) );
	}

	/**
	 * Disk information.
	 *
	 * @return array disk information.
	 */
	public static function get_disk_info() {
		$disk_info                    = array();
		$disk_info['disk_total']      = 'Unavailable';
		$disk_info['disk_used']       = 'Unavailable';
		$disk_info['disk_free']       = 'Unavailable';
		$disk_info['disk_percentage'] = 'Unavailable';
			
		if ( function_exists( 'disk_free_space' ) && ( disk_free_space( __DIR__ ) != false ) ) {
			/* Get disk space free (in bytes). */
			$disk_free                    = disk_free_space( __DIR__ );
			/* And get disk space total (in bytes).  */
			$disk_total                   = disk_total_space( __DIR__ );
			/* Now we calculate the disk space used (in bytes). */
			$disk_used                    = $disk_total - $disk_free;
			/* Percentage of disk used - this will be used to also set the width % of the progress bar. */
			$disk_percentage              = sprintf( '%.2f', ( $disk_used / $disk_total ) * 100 );
			$disk_info['disk_total']      = $disk_total;
			$disk_info['disk_used']       = $disk_used;
			$disk_info['disk_free']       = $disk_free;
			$disk_info['disk_percentage'] = $disk_percentage;
		}

		return $disk_info;
	}

	/**
	 * Format the argument from bytes to MB, GB, etc.
	 *
	 * @param array bytes size.
	 *
	 * @return array size from bytes to larger ammount.
	 */
	public static function format_size( $bytes ) {
		if ( $bytes == 'Unavailable' ) {
			return $bytes;
		}

		$types = array( 'B', 'KB', 'MB', 'GB', 'TB' );
		for ( $i = 0; $bytes >= 1000 && $i < ( count( $types ) - 1 );
		$bytes /= 1024, $i++ );
		return ( round( $bytes, 2 ) . ' ' . $types[ $i ] );
	}

	/**
	 * Get Cloudproxy API Keys from sucuri-settings.php
	 *
	 * @return string Sucuri API call with bare arguments
	 */
	public static function get_sucuri_api_call() {
		if( self::get_sucuri_api() === FALSE ) {
			self::set_sucuri_api();
		}

		if ( ! isset( self::$sucuri_api_key['api_key'] ) || ! isset( self::$sucuri_api_key['api_secret'] ) ) {
			return;
		} else {
			// $sucuri_api_call = 'https://waf.sucuri.net/api?&k=' . $api_key . '&s=' . $api_secret;
			$sucuri_api_call               = array();
			$sucuri_api_call['address']    = 'https://waf.sucuri.net/api?v2';
			$sucuri_api_call['k_option']   = '&k=';
			$sucuri_api_call['api_key']    = self::$sucuri_api_key['api_key'];
			$sucuri_api_call['s_option']   = '&s=';
			$sucuri_api_call['api_secret'] = self::$sucuri_api_key['api_secret'];
			return $sucuri_api_call;
		}
	}

	/**
	 * Determine whether Sucuri request header is set
	 *
	 * @return boolean. TRUE if set
	 */
	public static function is_sucuri_header_set() {
		return isset( $_SERVER['HTTP_X_SUCURI_CLIENTIP'] );
	}

	/**
	 * Determine whether Sucuri API key is set
	 *
	 * @return boolean. If the key is set
	 */
	public static function is_sucuri_firewall_api_key_set() {
		if( self::get_sucuri_api() === FALSE ) {
			self::set_sucuri_api();
		}
		return ( ! empty( self::$sucuri_api_key ) );
	}

	/**
	 * Determine whether Cloudflare Firewall option is selected
	 *
	 * @return boolean. If the option is selected
	 */
	public static function is_cloudflare_firewall_selected() {
		$option_list = get_option( 'blog_tutor_support_settings', array() );
		return ( isset( $option_list['firewall_choice'] ) && $option_list['firewall_choice'] == 'cloudflare' );
	}

	/**
	 * Determine whether Sucuri Firewall option is selected
	 *
	 * @return boolean. If the option is selected
	 */
	public static function is_sucuri_firewall_selected() {
		$option_list = get_option( 'blog_tutor_support_settings', array() );
		return ( isset( $option_list['firewall_choice'] ) && $option_list['firewall_choice'] == 'sucuri' );
	}

	/**
	 * Determine whether Sucuri Plugin is active
	 *
	 * @return boolean. If the plugin is active
	 */
	public static function is_sucuri_plugin_active() {
		return is_plugin_active( 'sucuri-scanner/sucuri.php' );
	}

	/**
	 * Determine whether Sucuri Plugin is installed on the site
	 *
	 * @return boolean. If the plugin is installed
	 */
	public static function is_sucuri_plugin_installed() {
		return file_exists( plugin_dir_path( __FILE__ ) . '/../../sucuri-scanner/sucuri.php' );
	}

	/**
	 * Determine whether the api key is set and Sucuri firewall setting
	 * is selected
	 *
	 * @return boolean. TRUE if the key is set and the firewall is selected
	 */
	public static function is_sucuri_api_and_settings_set() {
		if( self::$sucuri_buttons_flag === NULL ) {
			self::$sucuri_buttons_flag = (
				self::is_sucuri_firewall_api_key_set() 
				&& self::is_sucuri_firewall_selected() 
			);
		}
		return self::$sucuri_buttons_flag;
	}

	/**
	 * If the sucuri plugin is inactive but should be active
	 *
	 * @return boolean. TRUE if inactive but should be active
	 */
	public static function is_sucuri_inactive() {
		return ( ! self::is_sucuri_firewall_api_key_set() &&
			self::is_sucuri_firewall_selected() );
	}

	/**
	 * Determine whether Sucuri API key is missing
	 *
	 * @return boolean. TRUE if the key is missing
	 */
	public static function is_sucuri_key_missing() {
		return ( ! self::is_sucuri_firewall_api_key_set() &&
				self::is_sucuri_firewall_selected() );
	}

	/**
	 * Display NerdPress Notification
	 * @param string $msg. String to display on the notification
	 * @return void
	 */
	public static function display_notification( $msg ) {
		if ( ! is_array( $msg ) ) {
			$msg = array( 'status' => 1, 'msg' => $msg );
		}
		
		// Exit if message is empty
		if ( $msg['msg'] == '') {
			return;
		}

		$msg_class = ( $msg['status'] ? 'np-notice' : 'error np-notice' );
		?>
			<link rel="stylesheet" href="<?php echo esc_url( NerdPress::$plugin_dir_url . 'includes/css/html-notifications-style.css' ); ?>" type="text/css" media="all">
			<div class="notice <?php echo $msg_class; ?>">
				<p><img src="<?php echo esc_url( NerdPress::$plugin_dir_url . 'includes/images/nerdpress-icon-250x250.png' ); ?>" style="max-width:45px;vertical-align:middle;"><strong><?php echo $msg['msg']; ?></strong></p>
			</div>
		<?php
  }
	
	/**
 	 * Bypass clearing Cloudflare cache for non-production domains.
 	 * @param string $domain. URL to be cleared
 	 * @return boolean. TRUE if any of the strings match, or the WP_ENVIRONMENT_TYPE constant is set to staging or development
 	 */
	public static function is_production( $home_url ) {
		$domain_bypass_strings = array(
			'development',
			'staging',
			'local',
			'localhost',
			'yawargenii',
			'iwillfixthat',
			'wpstagecoach',
			'bigscoots-staging',
			'dev',
			'test',
			'flywheelsites',
			'closte',
			'runcloud',
			'kinsta',
			'cloudwaysapp',
			'pantheonsite',
			'sg-host',
			'onrocket',
			'pressdns',
			'wpengine',
			'wpstage',
		);

		if ( function_exists( 'wp_get_environment_type' ) && wp_get_environment_type() !== 'production' ) {
			return FALSE;
		}

		foreach ( $domain_bypass_strings as $string ) {
			// Is $string prepended and appended by a / or . in $home_url.
			if ( preg_match( '#([/.]' . $string . '[/.])#m', $home_url ) ) {
				return FALSE;
			}
		}

		return TRUE;
	}

	/**
 	 * Bypass clearing Cloudflare cache for non-production domains and NERDPRESS_CACHE_CLEAR_BYPASS constant.
 	 * @param array $prefixes. URL(S) to be cleared
 	 * @return boolean. TRUE if any of the strings match, or the NERDPRESS_CACHE_CLEAR_BYPASS constant matches
 	 */
	public static function cache_clear_bypass_on_string( $prefixes ) {
		$bypass_strings = array(
			'wc_user_membership',
			'shop_subscription',
		);
		
		if ( defined( 'NERDPRESS_CACHE_CLEAR_BYPASS' ) ) {
			$bypass_strings[] = NERDPRESS_CACHE_CLEAR_BYPASS; 		
		}

		foreach ( $bypass_strings as $string ) {
			foreach ( $prefixes as $prefix ) {
				if ( strpos( $prefix, $string ) !== FALSE ) {
					return TRUE;
				}
			}
		}

		return FALSE;
	}

	/**
	 * Determine whether we are hiding ShortPixel settings.
	 *
	 * @return boolean. If the option is selected
	 */
	public static function hide_shortpixel_settings() {
		$option_list = get_option( 'blog_tutor_support_settings', array() );
		return ( ! isset( $options['shortpixel_bulk_optimize'] ) && ! self::is_nerdpress() && defined( 'SHORTPIXEL_HIDE_API_KEY' ) );
	}
}
