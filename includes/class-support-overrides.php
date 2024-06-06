<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

	/**
	 * NerdPress_Support_Overrides
	 *
	 * @package  NerdPress
	 * @category Core
	 * @author Sergio Scabuzzo
	 */



class NerdPress_Support_Overrides {
	private static $options_array     = 'blog_tutor_support_settings';
	private static $nerdpress_options = '';

	/**
	 * Initialize the settings.
	 */
	public function __construct() {
		self::$nerdpress_options = get_option( self::$options_array, array() );
		add_action( 'init', array( $this, 'is_auto_update_set' ) );
		add_action( 'init', array( $this, 'check_default_options' ) );
		add_filter( 'wp_mail', array( $this, 'nerdpress_override_alert_email' ) );
		add_action( 'admin_head-link-checker_page_blc_local', array( $this, 'broken_link_checker_hide_link' ) );
    	add_action( 'admin_head-users.php', array( $this, 'hide_delete_all_content' ) );
		add_action( 'admin_menu', array( $this, 'hide_logtivity_settings' ) );
		if ( ! is_admin() && ! isset( self::$nerdpress_options['exclude_wp_rocket_delay_js'] ) ) {
			add_filter( 'rocket_delay_js_exclusions', array( $this, 'nerdpress_override_rocket_delay_js_exclusions' ) );
		}
		if ( ! is_admin() && ! isset( self::$nerdpress_options['exclude_perfmatters_delay_js'] )  ) {
			add_filter( 'perfmatters_delay_js_exclusions', array( $this, 'nerdpress_override_perfmatters_delay_js_exclusions' ) );
		}
		if ( ! isset( self::$nerdpress_options['exclude_wp_rocket_delay_js'] ) ) {
			add_action( 'settings_page_wprocket', array( $this, 'np_wprocket_scripts' ) );
		}
		if ( ! isset( self::$nerdpress_options['exclude_perfmatters_delay_js'] )  ) {
			add_action( 'settings_page_perfmatters', array( $this, 'np_perfmatters_scripts' ) );
		}
		if ( class_exists( '\Imagify\Plugin' ) ) {
			$imagify_options = get_option( 'imagify_settings' );
			if ( ( ! isset( $imagify_options["display_nextgen"] ) || 0 === $imagify_options["display_nextgen"] ) && ! isset( self::$nerdpress_options['imagify_deactivate_nextgen_images'] ) ) {
				add_filter( 'imagify_nextgen_images_formats', array( $this, 'nerdpress_override_imagify_nextgen_images' ) );
			}
		}
		if ( class_exists( 'WooCommerce' ) ) {
			add_filter( 'woocommerce_background_image_regeneration', '__return_false' );
		}
		function remove_background_updates_test( $tests ) {
			unset( $tests['direct']['persistent_object_cache'], $tests['direct']['yoast-page-comments-check'], $tests['async']['background_updates'] );
			return $tests;
		}
		add_filter( 'site_status_tests', 'remove_background_updates_test', 99 );
	}

	public function hide_logtivity_settings() {
		if ( ! NerdPress_Helpers::is_nerdpress() ) {
			remove_submenu_page( 'logs', 'logtivity-settings' );
			remove_submenu_page( 'lgtvy-logs', 'logtivity-settings' );
			add_filter( 'logtivity_hide_settings_page', '__return_true' );
		}
	}

	public function hide_delete_all_content() {
		?>
		<style type="text/css">
			#delete_option0,
			#delete_option1,
			label[for=delete_option0],
			form#updateusers div.wrap fieldset ul:first-child li label
			{display: none;}
		</style>
		<?php
	}

	public function broken_link_checker_hide_link() {
		?>
		<style type="text/css">
			#blc-links .column-used-in .trash,
			#blc-links .column-used-in .delete,
			#blc-bulk-action-form option[value="bulk-trash-sources"]
			{display: none;}
		</style>
		<?php
	}

	public function is_auto_update_set() {
		if ( ! isset( self::$nerdpress_options['auto_update_core'] ) ) {
			add_filter( 'allow_major_auto_core_updates', '__return_false' );
		}

		if ( ! isset( self::$nerdpress_options['auto_update_plugins'] ) && ! NerdPress_Helpers::is_nerdpress() ) {
			add_filter( 'plugins_auto_update_enabled', '__return_false' );
			add_filter( 'auto_update_plugin', '__return_false' );
		}

		if ( ! isset( self::$nerdpress_options['auto_update_themes'] ) && ! NerdPress_Helpers::is_nerdpress() ) {
			add_filter( 'themes_auto_update_enabled', '__return_false' );
			add_filter( 'auto_update_theme', '__return_false' );
		}

	}

	/**
	 * Check mandatory options, set to default if not present
	 */
	public function check_default_options() {
		$nerdpress_default_options['firewall_choice'] = 'none';
		$nerdpress_default_options['cloudflare_zone'] = 'dns1';

		foreach ( $nerdpress_default_options as $key => $val ) {
			if ( ! array_key_exists( $key, self::$nerdpress_options ) || ! isset( self::$nerdpress_options[ $key ] ) ) {
				self::$nerdpress_options[ $key ] = $val;
			}
		}

		update_option( self::$options_array, self::$nerdpress_options );
	}

	public function nerdpress_override_alert_email( $atts ) {
		$email_list         = !is_array( $atts['to'] ) ? [ $atts['to'] ] : $atts['to'];
		$is_nerdpress_alert = false;
		foreach ( $email_list as $email ) {
			if ( ( str_contains( $email, 'alerts@nerdpress.net' ) != false) || ( str_contains( $email, 'alerts@blogtutor.com' ) != false ) ) {
				$is_nerdpress_alert = true;
			}
		}
		if ( $is_nerdpress_alert ) {

			$sitename = wp_parse_url( network_home_url(), PHP_URL_HOST );
			if ( 'www.' === substr( $sitename, 0, 4 ) ) {
				$sitename = substr( $sitename, 4 );
			}

			$replyto_email = 'wordpress@' . $sitename;

			$atts['headers'][] = 'Reply-To: ' . get_bloginfo( 'name' ) . '<' . $replyto_email . '>';
			$atts['headers'][] = 'X-Auto-Response-Suppress: AutoReply';
		}

		return $atts;
	}

	public function nerdpress_override_rocket_delay_js_exclusions( $excluded_strings = array() ) {
		// MUST ESCAPE PERIODS AND PARENTHESES!
		$excluded_strings[] = 'google-analytics';
		$excluded_strings[] = '/gtag/';
		$excluded_strings[] = '/gtm\.js';
		$excluded_strings[] = '/gtm-';
		$excluded_strings[] = "ga\( '";
		$excluded_strings[] = "ga\('";
		$excluded_strings[] = 'gtag\(';
		$excluded_strings[] = 'gtagTracker'; // Monster Insights
		$excluded_strings[] = 'mediavine';
		$excluded_strings[] = 'adthrive';
		$excluded_strings[] = 'ads\.min\.js';
		$excluded_strings[] = 'nutrifox';
		$excluded_strings[] = 'flodesk';
		$excluded_strings[] = 'cp-popup\.js'; // ConvertPro
		$excluded_strings[] = 'slickstream';
		$excluded_strings[] = 'slick-film-strip';
		$excluded_strings[] = 'social-pug';
		$excluded_strings[] = 'shemedia';
		$excluded_strings[] = 'blogherads';
		$excluded_strings[] = 'sheknows-infuse\.js';
		$excluded_strings[] = 'adt_ei';
		$excluded_strings[] = '/wp-content/plugins/wp-recipe-maker-premium/dist/public-pro\.js';
		$excluded_strings[] = '/wp-content/plugins/wp-recipe-maker-premium/dist/public-elite\.js';
		$excluded_strings[] = '/wp-content/plugins/wp-recipe-maker/dist/public-modern\.js';
		$excluded_strings[] = 'gpt\.js';
		return $excluded_strings;
	}

	public function nerdpress_override_perfmatters_delay_js_exclusions( $pexcluded_strings = array() ) {
		$pexcluded_strings[] = 'google-analytics';
		$pexcluded_strings[] = '/gtag/';
		$pexcluded_strings[] = '/gtm.js';
		$pexcluded_strings[] = '/gtm-';
		$pexcluded_strings[] = "ga( '";
		$pexcluded_strings[] = "ga('";
		$pexcluded_strings[] = 'gtag(';
		$pexcluded_strings[] = 'gtagTracker'; // Monster Insights
		$pexcluded_strings[] = 'mediavine';
		$pexcluded_strings[] = 'adthrive';
		$pexcluded_strings[] = 'ads.min.js';
		$pexcluded_strings[] = 'nutrifox';
		$pexcluded_strings[] = 'flodesk';
		$pexcluded_strings[] = 'cp-popup.js'; // ConvertPro
		$pexcluded_strings[] = 'slickstream';
		$pexcluded_strings[] = 'slick-film-strip';
		$pexcluded_strings[] = 'social-pug';
		$pexcluded_strings[] = 'shemedia';
		$pexcluded_strings[] = 'blogherads';
		$pexcluded_strings[] = 'sheknows-infuse.js';
		$pexcluded_strings[] = 'adt_ei';
		$pexcluded_strings[] = 'gpt.js';
		return $pexcluded_strings;
	}

	public function nerdpress_override_imagify_nextgen_images( $formats ) {
		if ( isset( $formats['webp'] ) ) {
			unset( $formats['webp'] );
		}

		return $formats;
	}

	public function np_wprocket_scripts() {
		wp_enqueue_script( 'jquery' );
		wp_register_script(
			'wprocket_js',
			esc_url( NerdPress::$plugin_dir_url . 'includes/js/np-wprocket.js' ),
			array(),
			BT_PLUGIN_VERSION
		);
		wp_enqueue_script( 'wprocket_js' );
	}
	public function np_perfmatters_scripts() {
		wp_enqueue_script( 'jquery' );
		wp_register_script(
			'perfmatters_js',
			esc_url( NerdPress::$plugin_dir_url . 'includes/js/np-perfmatters.js' ),
			array(),
			BT_PLUGIN_VERSION
		);
		wp_enqueue_script( 'perfmatters_js' );
	}

}

new NerdPress_Support_Overrides();
