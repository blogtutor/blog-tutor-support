<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

	/**
	 * Blog_Tutor_Support helper class.
	 *
	 * @package  Blog_Tutor_Support
	 * @category Core
	 * @author  Andrew Wilder, Sergio Scabuzzo
	 */
class Blog_Tutor_Support_Helpers {

	/**
	 * Check email address to see if user is a member of the NerdPress team (and also an administrator).
	 */
	public static function is_nerdpress() {
		$current_user = wp_get_current_user();
		if ( current_user_can( 'administrator' ) && ( strpos( $current_user->user_email, '@blogtutor.com' ) !== false || strpos( $current_user->user_email, '@nerdpress.net' ) !== false ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Disk information.
	 *
	 * @return array disk information.
	 */
	public static function get_disk_info() {
		// Credit to: http://www.thecave.info/display-disk-free-space-percentage-in-php/
		/* Get disk space free (in bytes). */
		$disk_free = disk_free_space( __FILE__ );
		/* And get disk space total (in bytes).  */
		$disk_total = disk_total_space( __FILE__ );
		/* Now we calculate the disk space used (in bytes). */
		$disk_used = $disk_total - $disk_free;
		/* Percentage of disk used - this will be used to also set the width % of the progress bar. */
		$disk_percentage              = sprintf( '%.2f', ( $disk_used / $disk_total ) * 100 );
		$disk_info                    = array();
		$disk_info['disk_total']      = $disk_total;
		$disk_info['disk_used']       = $disk_used;
		$disk_info['disk_free']       = $disk_free;
		$disk_info['disk_percentage'] = $disk_percentage;

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
		if ( defined( 'SUCURI_DATA_STORAGE' ) ) {
			$input_lines = file_get_contents( SUCURI_DATA_STORAGE . '/sucuri-settings.php' );
		} else {
			$upload_dir  = wp_upload_dir( $time = null, $create_dir = null );
			$input_lines = file_get_contents( $upload_dir['basedir'] . '/sucuri/sucuri-settings.php' );
		}
		// Using # as regex delimiters since / was giving error.
		$regex = '#\"sucuriscan_cloudproxy_apikey\":\"(.{32})\\\/(.{32})#';

		preg_match_all( $regex, $input_lines, $output_array, PREG_SET_ORDER, 0 );

		if ( array_filter( $output_array ) ) {
			$api_key    = $output_array[0][1];
			$api_secret = $output_array[0][2];
		}

		if ( ! isset( $api_key ) ) {
			return;
		} else {
			// $sucuri_api_call = 'https://waf.sucuri.net/api?&k=' . $api_key . '&s=' . $api_secret;
			$sucuri_api_call               = array();
			$sucuri_api_call['address']    = 'https://waf.sucuri.net/api';
			$sucuri_api_call['k_option']   = '?&k=';
			$sucuri_api_call['api_key']    = $api_key;
			$sucuri_api_call['s_option']   = '&s=';
			$sucuri_api_call['api_secret'] = $api_secret;
			return $sucuri_api_call;
		}
	}
}
