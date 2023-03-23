<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * NerdPress_Plugin main class.
 *
 * @package  NerdPress
 * @category Core
 * @author   Fernando Acosta, Andrew Wilder, Sergio Scabuzzo
 */
class NerdPress_Plugin {
	/**
	 * Instance of this class.
	 *
	 * @var object|null
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

		self::$plugin_dir_url = plugin_dir_url( BT_PLUGIN_FILE );
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
		new NerdPress_Admin();
	}

	/**
	 * Include plugin functions.
	 */
	protected function includes() {
		new NerdPress_Widget();
		new NerdPress_Support_Overrides();
		new NerdPress_Support_ShortPixel();

		if ( NerdPress_Helpers::is_relay_server_configured() ) {
			add_action( 'init', array( 'NerdPress_Support_Snapshot', 'init' ) );
		}

		if ( NerdPress_Helpers::is_sucuri_header_set() || NerdPress_Helpers::is_sucuri_firewall_selected() ) {
			add_action( 'init', array( 'NerdPress_Cloudproxy', 'init' ) );
			new NerdPress_Clearcache();
		}

		if ( NerdPress_Helpers::is_cloudflare_firewall_selected() ) {
			add_action( 'init', array( 'NerdPress_Cloudflare_Client', 'init' ) );
		}
	}
}
