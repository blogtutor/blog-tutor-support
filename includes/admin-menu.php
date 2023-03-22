<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// Add Admin Bar Menu Items.
function bt_custom_toolbar_links( $wp_admin_bar ) {

	if ( current_user_can( 'editor' ) || current_user_can( 'administrator' ) ) {

		// On front end, load plugin.php so we can check for Sucuri Plugin status.
		if ( ! is_admin() ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		?>
			<link rel="stylesheet" href="<?php echo NerdPress::$plugin_dir_url . 'includes/css/html-admin-menu.css'; ?>" type="text/css" media="all">
		<?php

		// Add "NerdPress" parent menu items.
		$args = array(
			'id'     => 'nerdpress-menu',
			'title'  => '<span class="ab-icon"></span><span class="ab-label">' . __( 'NerdPress', 'nerdpress-support' ) . '</span>',
			'parent' => false,
		);
		$wp_admin_bar->add_node( $args );

		if ( NerdPress_Helpers::is_cloudflare_firewall_selected() ) {
			$nerdpress_settings = get_option( 'blog_tutor_support_settings', array() );
			if ( isset( $nerdpress_settings['cloudflare_zone'] ) && isset( $nerdpress_settings['cloudflare_token'] ) ) {
				if ( NerdPress_Helpers::is_production( home_url( '/' ) ) ) {
					$args = array(
						'id'     => 'nerdpress-purge-full',
						'title'  => 'Clear Cloudflare Cache',
						'href'   => '#',
						'parent' => 'nerdpress-menu',
						'meta'   => array(
							'tabindex' => 9999,
							'id'       => 'cfClearcache',
							'class'    => 'btButton',
							'title'    => 'Clear everything from the Cloudflare cache',
						),
					);
					$wp_admin_bar->add_node( $args );

					if (
						! is_admin() &&
						! is_front_page() &&
						(
							! NerdPress_Helpers::cache_clear_bypass_on_string( array( $_SERVER['REQUEST_URI'] ) ) !== false ||
							strpos( $_SERVER['REQUEST_URI'], 'lang=' ) !== false
						)
					) {
						$args = array(
							'id'     => 'nerdpress-purge-url',
							'title'  => 'Purge this URL from Cloudflare',
							'href'   => '#',
							'parent' => 'nerdpress-menu',
							'meta'   => array(
								'tabindex' => 9999,
								'id'       => 'cfClearurl',
								'class'    => 'btButton',
								'title'    => 'Purge just this URL from Cloudflare',
							),
						);
						$wp_admin_bar->add_node( $args );
					}
				} else {
					$args = array(
						'id'     => 'nerdpress-is-not-production',
						'title'  => 'Cloudflare Cache Clearing Unavailable',
						'href'   => '#',
						'parent' => 'nerdpress-menu',
						'meta'   => array(
							'tabindex' => 9999,
							'class'    => 'btButton',
							'title'    => "This appears to be a non-production site, so we've disabled cache clearing. Please contact us with any questions.",
							'onclick'  => NerdPress_Helpers::$help_scout_widget_menu_init,
						),
					);
					$wp_admin_bar->add_node( $args );
				}
			} else {
				$args = array(
					'id'     => 'nerdpress-cloudflare-configuration-error',
					'title'  => 'There is a problem with your Cloudflare Enterprise settings! Please contact us.',
					'href'   => '#',
					'parent' => 'nerdpress-menu',
					'meta'   => array(
						'tabindex' => 9999,
						'class'    => 'btButton',
						'title'    => 'There is a problem with your Cloudflare Enterprise settings! Please contact us.',
						'onclick'  => NerdPress_Helpers::$help_scout_widget_menu_init,
					),
				);
				$wp_admin_bar->add_node( $args );
			}
		}

		if ( ! NerdPress_Helpers::is_sucuri_plugin_installed() ) {
			$args = array(
				'id'     => 'bt-sucuri-not-installed',
				'title'  => 'The Sucuri Plugin is not installed! Please contact us.',
				'href'   => '#',
				'parent' => 'nerdpress-menu',
				'meta'   => array(
					'class'   => 'btButton',
					'title'   => 'The Sucuri Plugin is not installed! Please contact us.',
					'onclick' => NerdPress_Helpers::$help_scout_widget_menu_init,
				),
			);
			$wp_admin_bar->add_node( $args );

		} elseif ( is_plugin_inactive( 'sucuri-scanner/sucuri.php' ) ) {
			$args = array(
				'id'     => 'bt-sucuri-inactive',
				'title'  => 'The Sucuri Plugin is inactive! Please contact us.',
				'href'   => '#',
				'parent' => 'nerdpress-menu',
				'meta'   => array(
					'class'   => 'btButton',
					'title'   => 'Your Sucuri Plugin is not activated. Please contact us!',
					'onclick' => NerdPress_Helpers::$help_scout_widget_menu_init,
				),
			);
			$wp_admin_bar->add_node( $args );
		}

		if ( NerdPress_Helpers::is_sucuri_header_set() ||
			NerdPress_Helpers::is_sucuri_firewall_selected() ) {

			// If is array then the api key exists.
			$sucuri_api_call_array = NerdPress_Helpers::get_sucuri_api_call();
			if ( is_array( $sucuri_api_call_array ) ) {
				// Build the Clear Cache & Allowlist links (Cloudproxy API v1) and add it to the admin bar.
				$sucuri_api_call  = implode( $sucuri_api_call_array );
				$cloudproxy_clear = $sucuri_api_call . '&a=clearcache';
				$args             = array(
					'id'     => 'bt-clear-cloudproxy',
					'title'  => 'Clear Sucuri Firewall Cache',
					'href'   => '#',
					'parent' => 'nerdpress-menu',
					'meta'   => array(
						'id'    => 'btClearcache',
						'class' => 'btButton',
						'title' => 'Clear the Sucuri Firewall Cache',
					),
				);
				$wp_admin_bar->add_node( $args );

				// Clear current page from Cloudproxy cache.
				if ( ! is_admin() ) {
					$path                 = $_SERVER['REQUEST_URI'];
					$cloudproxy_clear_uri = $sucuri_api_call . '&a=clearcache&file=' . $path;
					$args                 = array(
						'id'     => 'bt-clear-uri-cloudproxy',
						'title'  => 'Clear this page from Sucuri Firewall',
						// Keep using the v1 API for this menu item
						'href'   => str_replace( '?&k', '?k', str_replace( 'api?v2', 'api?', $cloudproxy_clear_uri ) ),
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

				if ( NerdPress_Helpers::is_nerdpress() ) {
					$cloudproxy_allowlist = $sucuri_api_call . '&a=whitelist&duration=3600';
					$allowlist_args       = array(
						'id'     => 'bt-allowlist-cloudproxy',
						'title'  => 'Add Your IP Address to the Allowlist',
						'href'   => $cloudproxy_allowlist,
						'parent' => 'nerdpress-menu',
						'meta'   => array(
							'class'  => 'btButton',
							'target' => 'Blank',
							'title'  => 'Add your current IP address to the allowlist, in case Cloudproxy is blocking you.',
							'parent' => 'nerdpress-menu',
						),
					);
					$wp_admin_bar->add_node( $allowlist_args );
				}
			} else {
				$args = array(
					'id'     => 'bt-cloudproxy-api-not-set',
					'title'  => 'Missing Firewall API Keys! Please contact us',
					'parent' => 'nerdpress-menu',
					'meta'   => array(
						'class'   => 'btButton',
						'title'   => 'Missing Firewall API Keys! Please contact us',
						'onclick' => NerdPress_Helpers::$help_scout_widget_menu_init,
					),
				);
				$wp_admin_bar->add_node( $args );
			}
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
				'onclick' => NerdPress_Helpers::$help_scout_widget_menu_init,
			),
		);
		$wp_admin_bar->add_node( $args );

		if ( NerdPress_Helpers::is_nerdpress() && NerdPress_Helpers::is_relay_server_configured() ) {
			$args = array(
				'id'     => 'bt-send-snapshot',
				'title'  => 'Send Snapshot to Relay',
				'href'   => add_query_arg( array(
					'np_snapshot'     => '1',
					'_snapshot_nonce' => wp_create_nonce( 'np_snapshot' ),
				) ),
				'parent' => 'nerdpress-menu',
				'meta'   => array(
					'class' => 'btButton',
					'title' => 'Send Site Snapshot to NerdPress Relay.',
				),
			);

			$wp_admin_bar->add_node( $args );
		}

		if ( NerdPress_Helpers::is_nerdpress() ) {

			// "Plugin Settings" link to open the NerdPress Support settings page.
			$args = array(
				'id'     => 'bt-settings',
				'title'  => 'Plugin Settings',
				'href'   => admin_url( 'options-general.php?page=nerdpress-support' ),
				'parent' => 'nerdpress-menu',
				'meta'   => array(
					'class' => 'btButton',
					'title' => 'Open NerdPress Support plugin settings.',
				),
			);
			$wp_admin_bar->add_node( $args );
		}

		if ( NerdPress_Helpers::is_nerdpress() ) {
			// add cpu load to admin menu.
			function serverinfo_admin_menu_item( $wp_admin_bar ) {

				$cpu_load_info = '';

				if ( function_exists( 'sys_getloadavg' ) ) {
					$cpu_loads = sys_getloadavg();
					if ( $cpu_loads ) {
						$cpu_load_info = '<span>Load: ' . esc_html( $cpu_loads[0] ) . ' &nbsp;' . esc_html( $cpu_loads[1] ) . ' &nbsp;' . esc_html( $cpu_loads[2] ) . '  &nbsp; ';
					}
				}

				$disk_space_info = 'Free Disk: ' . esc_html( NerdPress_Helpers::format_size( NerdPress_Helpers::get_disk_info()['disk_free'] ) ) . '</span>';
				$cpu_disk_info   = $cpu_load_info . $disk_space_info;
				$args            = array(
					'id'    => 'cpu-disk-info',
					'title' => $cpu_disk_info,
					'href'  => admin_url( 'options-general.php?page=nerdpress-support&tab=server_information' ),
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
}
add_action( 'admin_bar_menu', 'bt_custom_toolbar_links', 99 );
