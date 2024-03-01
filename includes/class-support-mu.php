<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'BT_PLUGIN_VERSION' ) ) {
	define( 'BT_PLUGIN_VERSION', '2.2' );
}

// MU Plugin Registrar Class
class NerdPress_Support_MUPluginRegistrar {

    /**
     * Generates the content for the MU-plugin.
     *
     * @return string The PHP code for the MU-plugin.
     */
    public function getMuPluginContent() {
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

function user_friendly_disable_plugins(\$plugins) {
    if (!empty(\$_GET['disable_plugin'])) {
        \$disable_plugin_name = sanitize_text_field(\$_GET['disable_plugin']);

        foreach (\$plugins as \$key => \$plugin_path) {
            \$plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . \$plugin_path);
            \$plugin_name = sanitize_title_with_dashes(\$plugin_data['Name']);

            if (\$disable_plugin_name === \$plugin_name) {
                unset(\$plugins[\$key]);
                break; // Stop the loop after finding and disabling the target plugin
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
     * @param string $loaderName The filename for the MU-plugin.
     * @param string $muPluginContent The content of the MU-plugin.
     */
    public function registerMustUse($loaderName, $muPluginContent) {
        $mustUsePluginDir = untrailingslashit(WPMU_PLUGIN_DIR);
        $loaderPath = $mustUsePluginDir . '/' . $loaderName;

        // Check if the current file matches the provided content.
        if (file_exists($loaderPath) && md5($muPluginContent) === md5_file($loaderPath)) {
            return; // No update needed.
        }

        // Create the MU-plugins directory if it doesn't exist.
        if (!is_dir($mustUsePluginDir) && !mkdir($mustUsePluginDir, 0755, true)) {
            throw new Exception('Unable to create the MU-plugins directory.');
        }

        // Ensure the directory is writable.
        if (!is_writable($mustUsePluginDir)) {
            throw new Exception('MU-plugin directory is not writable.');
        }

        // Write the MU-plugin content to the file.
        if (false === file_put_contents($loaderPath, $muPluginContent)) {
            throw new Exception('Unable to write the MU-plugin file.');
        }
    }
}



function nerdpress_register_mu_plugin() {
    if (version_compare(BT_PLUGIN_VERSION, '2.2', '<=')) {
        $muPluginRegistrar = new NerdPress_Support_MUPluginRegistrar();
        $muPluginContent = $muPluginRegistrar->getMuPluginContent();
        $loaderName = 'np-disable-plugin.php';

        try {
            $muPluginRegistrar->registerMustUse($loaderName, $muPluginContent);
        } catch (Exception $e) {
            error_log('Error registering MU-plugin: ' . $e->getMessage());
        }
    }
}

add_action('init', 'nerdpress_register_mu_plugin', 9);
