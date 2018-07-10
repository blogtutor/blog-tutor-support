<<<<<<< HEAD
<?php

/**
 * Plugin Name: Blog Tutor Support
 * Description: Adds the Blog Tutor support widget to your WordPress dashboard for easy access to our knowledge base and contact form.
 * Version: 	0.1.1
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
  const VERSION = '1.0.0';

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
=======
<?php

/**
 * Plugin Name: Blog Tutor Support
 * Description: Adds the Blog Tutor support widget to your WordPress dashboard for easy access to our knowledge base and contact form.
 * Version: 	0.1.1
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

// add a link to the WP Toolbar
function custom_toolbar_link( $wp_admin_bar ) {
  $str = file_get_contents( $_SERVER[ "DOCUMENT_ROOT" ] . '/wp-content/uploads/sucuri/sucuri-settings.php' );
  $re = '/(.{32})\\\\\/(.{32})/';

  // Get Cloudproxy API Keys
  preg_match_all( $re, $str, $matches, PREG_SET_ORDER, 0 );
  $API_KEY = $matches[0][1];
  $API_SECRET = $matches[0][2];

  $Cloudproxy_clear = "https://waf.sucuri.net/api?v2&k=" . $API_KEY . "&s=" . $API_SECRET . "&a=clear_cache";
  $args = array(
      'id' => 'btButton',
      'title' => 'Clear Cloudproxy',
      'href' => $Cloudproxy_clear,
      'meta' => array(
          'class' => 'btButton',
          'title' => 'Easily Clear the Caches'
          )
  );
  $wp_admin_bar->add_node( $args );
}
add_action( 'admin_bar_menu', 'custom_toolbar_link', 99 );

// GitHub updater
include( dirname( __FILE__ ) . '/github-updater.php' );

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
  const VERSION = '1.0.0';

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
>>>>>>> 3274efdfe24044230be2702a5235e6fd7c3ec6c7
