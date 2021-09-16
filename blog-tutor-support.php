<?php

/**
 * Plugin Name: NerdPress Support
 * Description: Helps your site work with our custom Cloudflare Enterprise setup or the Sucuri Firewall, and adds the NerdPress "Need Help?" support tab to your dashboard.
 * Version:     1.2-beta2
 * Author:      NerdPress
 * Author URI:  https://www.nerdpress.net
 * GitHub URI: 	blogtutor/blog-tutor-support
 * License: 	  GPLv2
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// GitHub updater
include( dirname( __FILE__ ) . '/github-updater.php' );

// Load Admin menu
include( dirname( __FILE__ ) . '/includes/admin-menu.php' );

if ( ! defined( 'BT_PLUGIN_VERSION' ) ) {
	define( 'BT_PLUGIN_VERSION', '1.2-beta2' );
}

if ( ! class_exists( 'NerdPress' ) ) :
	/**
	 * NerdPress main class.
	 *
	 * @package  NerdPress
	 * @category Core
	 * @author   Fernando Acosta, Andrew Wilder, Sergio Scabuzzo
	 */
class NerdPress {
	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin.
	 */
	private function __construct() {
		// Include classes.
		$this->includes();

		if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
			$this->admin_includes();
		}

	}

	/**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Include admin actions.
	 */
	protected function admin_includes() {
		include dirname( __FILE__ ) . '/includes/admin/class-support-admin.php';
	}

	/**
	 * Include plugin functions.
	 */
	protected function includes() {
		include_once dirname( __FILE__ ) . '/includes/class-support-helpers.php';
		include_once dirname( __FILE__ ) . '/includes/class-support-widget.php';
		include_once dirname( __FILE__ ) . '/includes/class-support-overrides.php';
		include_once dirname( __FILE__ ) . '/includes/class-support-shortpixel.php';
		
		if ( NerdPress_Helpers::is_sucuri_header_set() || NerdPress_Helpers::is_sucuri_firewall_selected() ) {
			include_once dirname( __FILE__ ) . '/includes/class-support-cloudproxy.php';
			include_once dirname( __FILE__ ) . '/includes/class-support-clearcache.php';
		}

		if ( NerdPress_Helpers::is_cloudflare_firewall_selected() ) {
			include_once dirname( __FILE__ ) . '/includes/class-support-cloudflare.php';
		}
		include_once dirname( __FILE__ ) . '/includes/class-support-relay.php';
		include_once dirname( __FILE__ ) . '/includes/class-support-cron.php';
	}
}

	function blogtutor_support_deactivation() {

		wp_clear_scheduled_hook( 'nerdpress_ping_relay_on_cron' );

	}

/**
 * Init the plugin.
 */
	add_action( 'plugins_loaded', array( 'NerdPress', 'get_instance' ) );

	// Register our custom action ping_relay so that it can be scheduled by cron
	add_action( 'init', function () {

		if ( ! has_action( 'nerdpress_ping_relay_on_cron' ) ) {

			function nerdpress_ping_relay_on_cron() {
				$cron_instance_relay = new NerdPress_Support_Relay;
				$cron_instance_relay->ping_relay_headless();
			}

			add_action( 'nerdpress_ping_relay_on_cron', 'nerdpress_ping_relay_on_cron', 10, 2 );
		}
	} );

	register_deactivation_hook( __FILE__, 'blogtutor_support_deactivation' );

endif;
