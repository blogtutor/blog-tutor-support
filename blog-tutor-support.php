<?php

/**
 * Plugin Name: NerdPress Support
 * Description: Helps your site work with our custom Cloudflare Enterprise setup or the Sucuri Firewall, and adds the "NerdPress Help" button in your dashboard.
 * Version:     2.2
 * Author:      NerdPress
 * Author URI:  https://www.nerdpress.net
 * GitHub URI:  blogtutor/blog-tutor-support
 * License:     GPLv2
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// GitHub updater
include( dirname( __FILE__ ) . '/github-updater.php' );

// Load Admin menu
include( dirname( __FILE__ ) . '/includes/admin-menu.php' );

if ( ! class_exists( 'NerdPress' ) ) {
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
		 * NerdPress plugin root URL.
		 */
		public static $plugin_dir_url = '';

		/**
		 * Initialize the plugin.
		 */
		private function __construct() {
			// Include classes.
			$this->includes();

			if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
				$this->admin_includes();
			}

			self::$plugin_dir_url = plugin_dir_url( __FILE__ );
		}

		/**
		 * Remove mu-plugin on uninstall.
		 */
		public static function on_uninstall() {
			$muPluginPath = WPMU_PLUGIN_DIR . '/np-disable-plugin.php';
			if (file_exists($muPluginPath)) {
				unlink($muPluginPath);
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
			include_once dirname( __FILE__ ) . '/includes/class-support-mu.php';

			if ( NerdPress_Helpers::is_relay_server_configured() ) {
				include_once dirname( __FILE__ ) . '/includes/class-support-snapshot.php';
			}

			if ( NerdPress_Helpers::is_sucuri_header_set() || NerdPress_Helpers::is_sucuri_firewall_selected() ) {
				include_once dirname( __FILE__ ) . '/includes/class-support-cloudproxy.php';
				include_once dirname( __FILE__ ) . '/includes/class-support-clearcache.php';
			}

			if ( NerdPress_Helpers::is_cloudflare_firewall_selected() ) {
				include_once dirname( __FILE__ ) . '/includes/class-support-cloudflare.php';
			}
		}
	}

	/**
	 * Init the plugin.
	 */
	add_action( 'plugins_loaded', array( 'NerdPress', 'get_instance' ) );
}
register_uninstall_hook(__FILE__, ['NerdPress', 'on_uninstall']);
