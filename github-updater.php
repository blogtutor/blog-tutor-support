<?php

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'NerdPress_GHU_Core' ) ) {
	class NerdPress_GHU_Core
	{
		public $update_data = array();
		public $active_plugins = array();


		function __construct() {
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_filter( 'plugins_api', array( $this, 'plugins_api' ), 10, 2 );
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'set_update_data' ) );
			add_filter( 'upgrader_source_selection', array( $this, 'upgrader_source_selection' ), 10, 4 );
			add_filter( 'extra_plugin_headers', array( $this, 'extra_plugin_headers' ) );
		}


		function admin_init() {
			$now = strtotime( 'now' );
			$last_checked = (int) get_option( 'ghu_last_checked' );
			$check_interval = apply_filters( 'ghu_check_interval', ( 60 * 60 * 12 ) );
			$this->update_data = (array) get_option( 'ghu_update_data' );
			$active = (array) get_option( 'active_plugins' );

			foreach ( $active as $plugin_path ) {
				$this->active_plugins[ $plugin_path ] = true;
			}

			// transient expiration
			if ( ( $now - $last_checked ) > $check_interval ) {
				$this->update_data = $this->get_github_updates();

				update_option( 'ghu_update_data', $this->update_data );
				update_option( 'ghu_last_checked', $now );
			}
		}


		/**
		 * Fetch the latest GitHub tags and build the plugin data array
		 */
		function get_github_updates() {
			$output = array();
			$plugins = get_plugins();

			foreach ( $plugins as $plugin_path => $info ) {

				if ( 'blog-tutor-support/blog-tutor-support.php' !== $plugin_path ) {
					continue;
				}

				if ( isset( $this->active_plugins[ $plugin_path ] ) && ! empty( $info['GitHub URI'] ) ) {
					$temp = array(
						'plugin'            => $plugin_path,
						'slug'              => trim( dirname( $plugin_path ), '/' ),
						'name'              => $info['Name'],
						'github_repo'       => $info['GitHub URI'],
						'description'       => $info['Description'],
					);

					// get plugin tags
					list( $owner, $repo ) = explode( '/', $temp['github_repo'] );
					$request = wp_remote_get( "https://api.github.com/repos/$owner/$repo/tags" );

					// WP error or rate limit exceeded
					if ( is_wp_error( $request ) || 200 != wp_remote_retrieve_response_code( $request ) ) {
						break;
					}

					$json = json_decode( $request['body'], true );

					if ( is_array( $json ) && ! empty( $json ) ) {
						$latest_tag = $json[0];
						$temp['new_version'] = $latest_tag['name'];
						$temp['url'] = "https://github.com/$owner/$repo/";
						$temp['package'] = $latest_tag['zipball_url'];
						$output[ $plugin_path ] = $temp;
					}
				}
			}

			return $output;
		}


		/**
		 * Get plugin info for the "View Details" popup
		 * NOTE: View Details & View Version x.x.x Details link only show up if an update is available.
		 *
		 * $args->slug = "edd-no-logins"
		 * $plugin_path = "edd-no-logins/edd-no-logins.php"
		 */
		function plugins_api( $action, $args ) {
			if ( 'plugin_information' == $action ) {
				foreach ( $this->update_data as $plugin_path => $info ) {
					if ( $info['slug'] == $args->slug ) {

					  $changelog_url = 'https://api.github.com/repos/blogtutor/blog-tutor-support/releases';
					  $changelog = wp_safe_remote_get( esc_url_raw( $changelog_url ) );
					  $changelog_output = '';

					  // Check that API responded okay, else use a fallback link.
					  if ( ! is_wp_error( $changelog ) && wp_remote_retrieve_response_code( $changelog ) == '200' ) {
						$changelog = json_decode( wp_remote_retrieve_body( $changelog ), true );
						// Parse and format the Github API Response
						foreach ( $changelog as $note => $release_note ) {
						  $changelog_output .= '<h4>' . $changelog[$note]['tag_name'] . ' - ' . date ( "F j, Y", strtotime( $changelog[$note]['published_at'] ) ) . '</h4>';
						  $changelog_output .= '<p>' . $changelog[$note]['name'] . '</p>';
						  if ( $changelog[$note]['body'] != '' ) {
							$changelog_output .= '<blockquote>' . nl2br( $changelog[$note]['body'] ) . '</blockquote>';
						  }
						}
					  } else {
						$changelog_output = '<a href="https://github.com/blogtutor/blog-tutor-support/releases" target="_blank">View the changelog here</a>.';
					  }

					  return (object) array(
						  'name'          => $info['name'],
						  'slug'          => $info['slug'],
						  'version'       => $info['new_version'],
						  'download_link' => $info['package'],
						  'sections' => array(
							  'description' => $info['description'],
							  'changelog' =>  $changelog_output
						  ),
						  'banners' => array(
							  'low' => 'https://www.nerdpress.net/wp-content/uploads/2019/01/nerdpress-support-plugin-header-small.jpg',
							  'high' => 'https://www.nerdpress.net/wp-content/uploads/2019/01/nerdpress-support-plugin-header-large.jpg'
						  )
					  );
					}
				}
			}

			return false;
		}


		function set_update_data( $transient ) {
			foreach ( $this->update_data as $plugin_path => $info ) {
				if ( isset( $this->active_plugins[ $plugin_path ] ) ) {
					$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_path );
					$version = $plugin_data['Version'];

					if ( version_compare( $version, $info['new_version'], '<' ) ) {
						$transient->response[ $plugin_path ] = (object) $info;
					}
				}
			}

			return $transient;
		}


		/**
		 * Rename the plugin folder
		 */
		function upgrader_source_selection( $source, $remote_source, $upgrader, $hook_extra = null ) {
			global $wp_filesystem;

			$plugin_path = isset( $hook_extra['plugin'] ) ? $hook_extra['plugin'] : false;
			if ( isset( $this->update_data[ $plugin_path ] ) ) {
				$new_source = trailingslashit( $remote_source ) . dirname( $plugin_path );
				$wp_filesystem->move( $source, $new_source );
				return trailingslashit( $new_source );
			}

			return $source;
		}


		/**
		 * Parse the "GitHub URI" config too
		 */
		function extra_plugin_headers( $headers ) {
			$headers[] = 'GitHub URI';
			return $headers;
		}
	}

	new NerdPress_GHU_Core();
}
