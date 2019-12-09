<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// Add Admin Bar Menu Items.
function bt_custom_toolbar_links( $wp_admin_bar ) {
	if ( ! is_admin() ) {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	if ( current_user_can( 'editor' ) || current_user_can( 'administrator' ) ) {
		// Add "NerdPress" parent menu Items.
		$args = array(
			'id'     => 'nerdpress-menu',
			'title'  => 'NerdPress',
			'parent' => false,
		);
		$wp_admin_bar->add_node( $args );

		// Add Child Menu Items.
		// Add a Clear Cloudproxy link to the Admin Bar.
		if ( is_plugin_active( 'sucuri-scanner/sucuri.php' ) ) {

			$sucuri_api_call_array = Blog_Tutor_Support_Helpers::get_sucuri_api_call();

			// If is array then the api key excists.
			if ( is_array( $sucuri_api_call_array ) ) {
				$sucuri_api_call = implode( $sucuri_api_call_array );
				// Build the Clear Cache & Whitelist links (Cloudproxy API v1) and add it to the admin bar.
				$cloudproxy_clear = $sucuri_api_call . '&a=clearcache';
				$args             = array(
					'id'     => 'bt-clear-cloudproxy',
					'title'  => 'Clear Sucuri Firewall Cache',
					'href'   => '#',
					'parent' => 'nerdpress-menu',
					'meta'   => array(
                        'id'     => 'btClearcache',
						'class'  => 'btButton',
						'title'  => 'Clear the Sucuri Firewall Cache',
					),
				);
				$wp_admin_bar->add_node( $args );

				if ( ! is_admin() ) {
					// Clear current page from Cloudproxy cache.
					$path                 = $_SERVER['REQUEST_URI'];
					$cloudproxy_clear_uri = $sucuri_api_call . '&a=clearcache&file=' . $path;
					$args                 = array(
						'id'     => 'bt-clear-uri-cloudproxy',
						'title'  => 'Clear Sucuri Firewall Cache',
						'href'   => '#',
						'parent' => 'nerdpress-menu',
						'meta'   => array(
							'class'  => 'btButton',
							'target' => 'blank',
							'title'  => 'Clear this page from Sucuri Firewall Cache.',
							'parent' => 'nerdpress-menu',
						),
					);
					$wp_admin_bar->add_node( $args );
				}
			} else {
				$args = array(
					'id'     => 'bt-cloudproxy-api-not-set',
					'title'  => 'Sucuri Firewall API Key is not set',
					'parent' => 'nerdpress-menu',
					'meta'   => array(
						'class' => 'btButton',
						'title' => 'Your Sucuri Firewall API key is not set in the Sucuri Plugin. If your site should be configured to use Sucuri Firewall, please contact us!',
					),
				);
				$wp_admin_bar->add_node( $args );
			}
		} elseif ( is_plugin_inactive( 'sucuri-scanner/sucuri.php' ) ) {
			$args = array(
				'id'     => 'bt-sucuri-inactive',
				'title'  => 'The Sucuri Plugin is inactive!',
				'href'   => '#',
				'parent' => 'nerdpress-menu',
				'meta'   => array(
					'class'   => 'btButton',
					'title'   => 'Your Sucuri Plugin is not activated. Please contact us!',
					'onclick' => 'window.supportHeroWidget.show();',
				),
			);
			$wp_admin_bar->add_node( $args );
		} else {
			$args = array(
				'id'     => 'bt-sucuri-missing',
				'title'  => 'The Sucuri Plugin is missing!',
				'href'   => '#',
				'parent' => 'nerdpress-menu',
				'meta'   => array(
					'class'   => 'btButton',
					'title'   => 'Your Sucuri Plugin is not configured. Please contact us!',
					'onclick' => 'window.supportHeroWidget.show();',
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
				'href'   => get_site_url() . '/wp-admin/options-general.php?page=nerdpress-support',
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
			$cpu_loads = sys_getloadavg();

			if ( $cpu_loads ) {
				$cpu_load_info = '<p>Load: ' . $cpu_loads[0] . ' &nbsp;' . $cpu_loads[1] . ' &nbsp;' . $cpu_loads[2] . '  &nbsp; ';
			} else {
				$cpu_load_info = '';
			}

			$disk_space_info = 'Free Disk: ' . Blog_Tutor_Support_Helpers::format_size( Blog_Tutor_Support_Helpers::get_disk_info()['disk_free'] ) . '</p>';
			$cpu_disk_info   = $cpu_load_info . $disk_space_info;
			$args            = array(
				'id'    => 'cpu-disk-info',
				'title' => $cpu_disk_info,
				'href'  => get_site_url() . '/wp-admin/options-general.php?page=nerdpress-support&tab=server_information',
				'meta'  => array(
					'class' => 'btButton',
					'title' => 'Open NerdPress Support plugin settings.',
				),
			);
			$wp_admin_bar->add_node( $args );
		}
		add_action( 'admin_bar_menu', 'serverinfo_admin_menu_item', 1000 );
	}
}
add_action( 'admin_bar_menu', 'bt_custom_toolbar_links', 99 );
