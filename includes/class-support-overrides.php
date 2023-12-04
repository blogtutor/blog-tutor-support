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

		if ( class_exists( 'WooCommerce' ) ) {
			add_filter( 'woocommerce_background_image_regeneration', '__return_false' );
		}
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
		if ( ( strpos( $atts['to'], 'alerts@nerdpress.net' ) ) || ( strpos( $atts['to'], 'alerts@blogtutor.com' ) ) ) {

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
		$excluded_strings[] = 'wp-recipe-maker';
		$excluded_strings[] = 'slickstream';
		$excluded_strings[] = 'slick-film-strip';
		$excluded_strings[] = 'social-pug';
		return $excluded_strings;
	}
}

new NerdPress_Support_Overrides();
