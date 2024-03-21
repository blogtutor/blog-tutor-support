<?php
/**
 * Plugin handler for NerdPress MU Plugin Registrar.
 *
 * This script handles the registration of a must-use plugin which
 * disables specified plugins via a query string.
 *
 * @package NerdPressSupport
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'BT_PLUGIN_VERSION' ) ) {
	define( 'BT_PLUGIN_VERSION', '2.2' );
}

/**
 * MU Plugin Registrar Class.
 *
 * Handles the dynamic creation and registration of a must-use plugin.
 */
class NerdPress_Support_MU_Plugin_Registrar {

	/**
	 * Generates the content for the MU-plugin.
	 *
	 * @return string The PHP code for the MU-plugin.
	 */
	public function get_mu_plugin_content() {
		return <<<PHP
		<?php
		/**
		 * Plugin Name: NerdPress Disable Plugins via Query String
		 * Description: MU Plugin to disable plugin with query string.
		 * Version:     1.0
		 * Author:      NerdPress
		 * Author URI:  https://www.nerdpress.net
		 * GitHub URI:  blogtutor/blog-tutor-support
		 * License:     GPLv2
		 */
		// Ensure get_plugin_data is available
		if (!function_exists('get_plugin_data')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		add_filter('option_active_plugins', 'user_friendly_disable_plugins');
		function user_friendly_disable_plugins( \$plugins ) {
			if ( ! empty( \$_GET['disable_plugin'] ) ) {
				// Split the received plugin slugs into an array
				\$disable_plugin_slugs = explode(',', sanitize_text_field( \$_GET['disable_plugin'] ) );
		
				// Trim whitespace from all slugs
				\$disable_plugin_slugs = array_map('trim', \$disable_plugin_slugs);
		
				foreach ( \$plugins as \$key => \$plugin_path ) {
					\$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . \$plugin_path );
					\$plugin_name = sanitize_title_with_dashes( \$plugin_data['Name'] );
		
					// Check if the current plugin is in the array of plugins to disable
					if ( in_array( \$plugin_name, \$disable_plugin_slugs ) ) {
						unset( \$plugins[\$key] );
						// Do not break to allow checking of all plugins
					}
				}
			}
		
			return \$plugins;
		} 
		PHP;
	}

	/**
	 * Registers the MU-plugin by creating or updating the loader file in the MU-plugins directory.
	 *
	 * @param string $loader_name The filename for the MU-plugin.
	 * @param string $mu_plugin_content The content of the MU-plugin.
	 * @throws Exception If unable to create directory or write the file.
	 */
	public function register_must_use( $loader_name, $mu_plugin_content ) {
		$must_use_plugin_dir = untrailingslashit( WPMU_PLUGIN_DIR );
		$loader_path         = $must_use_plugin_dir . '/' . $loader_name;

		if ( file_exists( $loader_path ) && md5( $mu_plugin_content ) === md5_file( $loader_path ) ) {
			return; // No update needed.
		}

		if ( ! is_dir( $must_use_plugin_dir ) && ! mkdir( $must_use_plugin_dir, 0755, true ) ) {
			throw new Exception( 'Unable to create the MU-plugins directory.' );
		}

		if ( ! is_writable( $must_use_plugin_dir ) ) {
			throw new Exception( 'MU-plugin directory is not writable.' );
		}

		if ( false === file_put_contents( $loader_path, $mu_plugin_content ) ) {
			throw new Exception( 'Unable to write the MU-plugin file.' );
		}
	}
}

/**
 * Registers the MU plugin on init.
 */
function nerdpress_register_mu_plugin() {
	if ( version_compare( BT_PLUGIN_VERSION, '2.2', '<=' ) ) {
		$mu_plugin_registrar = new NerdPress_Support_MU_Plugin_Registrar();
		$mu_plugin_content   = $mu_plugin_registrar->get_mu_plugin_content();
		$loader_name         = 'np-disable-plugin.php';

		try {
			$mu_plugin_registrar->register_must_use( $loader_name, $mu_plugin_content );
		} catch ( Exception $e ) {
			error_log( 'Error registering MU-plugin: ' . $e->getMessage() );
		}
	}
}

add_action( 'init', 'nerdpress_register_mu_plugin', 9 );
