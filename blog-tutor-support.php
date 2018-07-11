<?php

/**
 * Plugin Name: Blog Tutor Support
 * Description: Adds the Blog Tutor support widget to your WordPress dashboard for easy access to our knowledge base and contact form.
 * Version: 	0.3.1
 * Author:      Blog Tutor
 * Author URI:  https://blogtutor.com
 * GitHub URI: 	blogtutor/blog-tutor-support
 * License: 	GPLv2 or later
 * Text Domain: blog-tutor
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

// GitHub updater
include( dirname( __FILE__ ) . '/github-updater.php' );

// Add Admin Bar Menu Items
function bt_custom_toolbar_links( $wp_admin_bar ) {

  // Add "Blog Tutor" parent menu Items
  $args = array(
  'id'      => 'blog-tutor-menu',
  'title'   =>  'Blog Tutor',
  'parent'  => false,
  );
  $wp_admin_bar->add_node( $args );

  // Add a "Get Help" link to open the Support Hero widget
  $args = array(
      'id' => 'bt-get-help',
      'title' => 'Get Help',
      'href' => '#',
      'parent'  => 'blog-tutor-menu',
      'meta' => array(
          'class' => 'btButton',
          'title' => 'Click to open our knowledge base and contact form.',
          'parent'  => 'blog-tutor-menu',
          'onclick' => 'window.supportHeroWidget.show();'
          )
  );
  $wp_admin_bar->add_node( $args );

  // Add a Clear Cloudproxy link to the Admin Bar
  if ( file_exists($_SERVER[ "DOCUMENT_ROOT" ] . '/wp-content/uploads/sucuri/sucuri-settings.php' ) ) {

    // Get Cloudproxy API Keys
    $str = file_get_contents( $_SERVER[ "DOCUMENT_ROOT" ] . '/wp-content/uploads/sucuri/sucuri-settings.php' );
    $re = '/(.{32})\\\\\/(.{32})/';

    preg_match_all( $re, $str, $matches, PREG_SET_ORDER, 0 );
    $API_KEY = $matches[0][1];
    $API_SECRET = $matches[0][2];

    if ( $API_KEY != '') {
      // Build the Clear Cache link (Cloudproxy API v1) and add it to the admin bar
      $Cloudproxy_clear = "https://waf.sucuri.net/api?&k=" . $API_KEY . "&s=" . $API_SECRET . "&a=clearcache";
      $args = array(
          'id' => 'bt-clear-cloudproxy',
          'title' => 'Clear Cloudproxy Cache',
          'href' => $Cloudproxy_clear,
          'parent'  => 'blog-tutor-menu',
          'meta' => array(
              'class' => 'btButton',
              'target' => 'blank',
              'title' => 'Clear the Cloudproxy cache',
              'parent'  => 'blog-tutor-menu'
              )
      );
      $wp_admin_bar->add_node( $args );
    } else {
      $args = array(
          'id' => 'bt-cloudproxy-api-not-set',
          'title' => 'Cloudproxy API Key is not set!',
          'meta' => array(
              'class' => 'btButton',
              'title' => 'Your Cloudproxy API key is not configured in the Sucuri Plugin. Please contact us!'
              )
      );
      $wp_admin_bar->add_node( $args );
    }
  } else {
    $args = array(
        'id' => 'bt-sucuri-missing',
        'title' => 'The Sucuri Plugin is missing!',
        'parent'  => 'blog-tutor-menu',
        'meta' => array(
            'class' => 'btButton',
            'title' => 'Your Sucuri Plugin is not configured. Please contact us!'
            )
    );
    $wp_admin_bar->add_node( $args );
  }
}
add_action( 'admin_bar_menu', 'bt_custom_toolbar_links', 99 );

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
   * Plugin version.
   *
   * @var string
   */
  const VERSION = '0.3';

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
    include_once dirname( __FILE__ ) . '/includes/class-support-widget.php';
  }
}

/**
 * Init the plugin.
 */
add_action( 'plugins_loaded', array( 'Blog_Tutor_Support', 'get_instance' ) );

endif;
