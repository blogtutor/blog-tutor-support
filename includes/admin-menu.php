<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Add Admin Bar Menu Items.
function bt_custom_toolbar_links( $wp_admin_bar ) {

	if ( current_user_can( 'editor' ) || current_user_can( 'administrator' ) ) {
		// Add "NerdPress" parent menu Items.
		$args = array(
			'id'     => 'nerdpress-menu',
			'title'  => 'NerdPress',
			'parent' => false,
		);
		$wp_admin_bar->add_node( $args );
		$site_url = get_site_url();

		// Add Child Menu Items.
		// Add a Clear Cloudproxy link to the Admin Bar.
		if ( file_exists( $_SERVER['DOCUMENT_ROOT'] . '/wp-content/uploads/sucuri/sucuri-settings.php' ) ) {

			// Get Cloudproxy API Keys.
			$input_lines = file_get_contents( $_SERVER['DOCUMENT_ROOT'] . '/wp-content/uploads/sucuri/sucuri-settings.php' );
			// Using # as regex delimiters since / was giving error.
			$regex = "#\"sucuriscan_cloudproxy_apikey\":\"(.{32})\\\/(.{32})#";

			preg_match_all( $regex, $input_lines, $output_array, PREG_SET_ORDER, 0 );

			if ( array_filter( $output_array ) ) {
				$api_key    = $output_array[0][1];
				$api_secret = $output_array[0][2];
			}

			if ( isset( $api_key ) ) {
				// Build the Clear Cache & Whitelist links (Cloudproxy API v1) and add it to the admin bar.
				$cloudproxy_clear = 'https://waf.sucuri.net/api?&k=' . $api_key . '&s=' . $api_secret . '&a=clearcache';
				$args             = array(
					'id'     => 'bt-clear-cloudproxy',
					'title'  => 'Clear Cloudproxy Cache',
					'href'   => $cloudproxy_clear,
					'parent' => 'nerdpress-menu',
					'meta'   => array(
						'class'  => 'btButton',
						'target' => 'blank',
						'title'  => 'Clear the Cloudproxy cache',
					),
				);
				$wp_admin_bar->add_node( $args );

				if ( ! is_admin() ) {
					// Clear current page from Cloudproxy cache.
					$path                 = $_SERVER['REQUEST_URI'];
					$cloudproxy_clear_uri = 'https://waf.sucuri.net/api?&k=' . $api_key . '&s=' . $api_secret . '&a=clearcache&file=' . $path;
					$args                 = array(
						'id'     => 'bt-clear-uri-cloudproxy',
						'title'  => 'Clear this page from Cloudproxy',
						'href'   => $cloudproxy_clear_uri,
						'parent' => 'nerdpress-menu',
						'meta'   => array(
							'class'  => 'btButton',
							'target' => 'blank',
							'title'  => 'Clear this page from Cloudproxy cache.',
							'parent' => 'nerdpress-menu',
						),
					);
					$wp_admin_bar->add_node( $args );
				}

				$cloudproxy_whitelist = 'https://waf.sucuri.net/api?&k=' . $api_key . '&s=' . $api_secret . '&a=whitelist';
				$args                 = array(
					'id'     => 'bt-whitelist-cloudproxy',
					'title'  => 'Whitelist Your IP Address',
					'href'   => $cloudproxy_whitelist,
					'parent' => 'nerdpress-menu',
					'meta'   => array(
						'class'  => 'btButton',
						'target' => 'blank',
						'title'  => 'Whitelist your current IP address, in case Cloudproxy is blocking you.',
						'parent' => 'nerdpress-menu',
					),
				);
				$wp_admin_bar->add_node( $args );
			} else {
				$args = array(
					'id'     => 'bt-cloudproxy-api-not-set',
					'title'  => 'Cloudproxy API Key is not set',
					'parent' => 'nerdpress-menu',
					'meta'   => array(
						'class' => 'btButton',
						'title' => 'Your Cloudproxy API key is not set in the Sucuri Plugin. If your site should be configured to use Cloudproxy, please contact us!',
					),
				);
				$wp_admin_bar->add_node( $args );
			}
		} else {
			$args = array(
				'id'     => 'bt-sucuri-missing',
				'title'  => 'The Sucuri Plugin is missing!',
				'parent' => 'nerdpress-menu',
				'meta'   => array(
					'class' => 'btButton',
					'title' => 'Your Sucuri Plugin is not configured. Please contact us!',
				),
			);
			$wp_admin_bar->add_node( $args );
		}

		// "Get Help" link to open the Support Hero widget
		$args = array(
			'id'     => 'bt-get-help',
			'title'  => 'Get Help',
			'href'   => '#',
			'parent' => 'nerdpress-menu',
			'meta'   => array(
				'class'   => 'btButton',
				'title'   => 'Click to open our knowledge base and contact form.',
				'onclick' => 'window.supportHeroWidget.show();',
			),
		);
		$wp_admin_bar->add_node( $args );

		if ( Blog_Tutor_Support_Helpers::is_nerdpress() ) {

			// "Plugin Settings" link to open the NerdPress Support settings page.
			$args = array(
				'id'     => 'bt-settings',
				'title'  => 'Plugin Settings',
				'href'   => $site_url . '/wp-admin/options-general.php?page=nerdpress-support',
				'parent' => 'nerdpress-menu',
				'meta'   => array(
					'class' => 'btButton',
					'title' => 'Open NerdPress Support plugin settings.',
				),
			);
			$wp_admin_bar->add_node( $args );
		}
	}

	if ( Blog_Tutor_Support_Helpers::is_nerdpress() ) {
		// add cpu load to admin menu.
		function serverinfo_admin_menu_item( $wp_admin_bar ) {
			$loads         = sys_getloadavg();
			if ( $loads ) {
				$cpu_load_info = '<p>Load: ' . $loads[0] . ' &nbsp;' . $loads[1] . ' &nbsp;' . $loads[2] . '  &nbsp; Free Disk: ' . Blog_Tutor_Support_Helpers::format_size( Blog_Tutor_Support_Helpers::get_disk_info()['disk_free'] ) . '</p>';
				$args          = array(
					'id'    => 'cpu-load',
					'title' => $cpu_load_info,
					'href'  => $site_url . '/wp-admin/options-general.php?page=nerdpress-support',
					'meta'  => array(
						'class' => 'btButton',
						'title' => 'Open NerdPress Support plugin settings.',
					),
				);
				$wp_admin_bar->add_node( $args );
			}
		}
		add_action( 'admin_bar_menu', 'serverinfo_admin_menu_item', 1000 );
	}
}
add_action( 'admin_bar_menu', 'bt_custom_toolbar_links', 99 );
