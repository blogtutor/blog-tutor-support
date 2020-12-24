<?php

/**
 * Plugin Name: NerdPress Support
 * Description: Helps your site work with our custom Cloudflare Enterprise setup or the Sucuri Firewall, and adds the NerdPress "Need Help?" support tab to your dashboard.
 * Version:     0.8.2
 * Author:      NerdPress
 * Author URI:  https://www.nerdpress.net
 * GitHub URI: 	blogtutor/blog-tutor-support
 * License: 	  GPLv2
 * Text Domain: blog-tutor
 * Domain Path: /languages/
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// GitHub updater
include( dirname( __FILE__ ) . '/github-updater.php' );

// Load Admin menu
include( dirname( __FILE__ ) . '/includes/admin-menu.php' );

if ( ! defined( 'BT_PLUGIN_VERSION' ) ) {
	define( 'BT_PLUGIN_VERSION', '0.8.2' );
}

if ( ! class_exists( 'Blog_Tutor_Support' ) ) :
	/**
	 * Blog_Tutor_Support main class.
	 *
	 * @package  Blog_Tutor_Support
	 * @category Core
	 * @author   Fernando Acosta, Andrew Wilder, Sergio Scabuzzo
	 */
class Blog_Tutor_Support {
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
		// Load plugin text domain.
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'init', array( $this, 'check_options' ) );
		if ( class_exists( 'WooCommerce' ) ) {
			add_filter( 'woocommerce_background_image_regeneration', '__return_false' );
		}
	
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
	 * Check mandatory options, set to default if not present
	 */
	public function check_options() {
		// Perform the check only if logged in
		if( !is_admin() ) {
			return;
		}

		$option_array = 'blog_tutor_support_settings';
		$default_opts = array(
			'firewall_choice' => 'none',
			'cloudflare_zone' => 'dns1',
		); 

		$bt_opts = get_option( $option_array, array() );

		foreach( $default_opts as $key => $val ) {
			if( !array_key_exists( $key, $bt_opts ) || !isset( $bt_opts[$key] ) )
				$bt_opts[$key] = $val; 
		}

		update_option( $option_array, $bt_opts );
	} 

	/**
	 * Load the plugin text domain for translation.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'blog-tutor-support', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
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
		include_once dirname( __FILE__ ) . '/includes/class-support-cloudproxy.php';
		include_once dirname( __FILE__ ) . '/includes/class-support-clearcache.php';
		include_once dirname( __FILE__ ) . '/includes/class-support-cloudflare.php';
		include_once dirname( __FILE__ ) . '/includes/class-support-updates.php';
		include_once dirname( __FILE__ ) . '/includes/class-support-shortpixel.php';
		include_once dirname( __FILE__ ) . '/includes/class-support-relay.php';
	}
}

/**
 * Init the plugin.
 */
add_action( 'plugins_loaded', array( 'Blog_Tutor_Support', 'get_instance' ) );

endif;
