<?php

/**
 * Plugin Name: NerdPress Support
 * Description: Helps your site work with our custom Cloudflare Enterprise setup or the Sucuri Firewall, and adds the NerdPress "Need Help?" support tab to your dashboard.
 * Version:     2.0-beta5
 * Author:      NerdPress
 * Author URI:  https://www.nerdpress.net
 * GitHub URI:  blogtutor/blog-tutor-support
 * License:     GPLv2
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! defined( 'BT_PLUGIN_VERSION' ) ) {
	define( 'BT_PLUGIN_VERSION', '2.0-beta5' );
}
if ( ! defined( 'BT_PLUGIN_FILE' ) ) {
	define( 'BT_PLUGIN_FILE', __FILE__ );
}

require __DIR__ . '/autoload.php';
// TODO Move admin menu to a class.
require __DIR__ . '/includes/admin-menu.php';

// GitHub updater
new NerdPress_GHU_Core();

// Load Admin menu
add_action( 'admin_bar_menu', 'bt_custom_toolbar_links', 99 );

/**
 * Init the plugin.
 */
add_action( 'plugins_loaded', array( 'NerdPress_Plugin', 'get_instance' ) );
