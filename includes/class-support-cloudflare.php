<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * class NerdPress_Cloudflare_Client
 *
 * @since 0.0.1
 */
class NerdPress_Cloudflare_Client {
	/**
	 * @var string. NerdPress CF API Key
	 *
	 * @since 0.0.1
	 */
	private static $cloudflare_api_key = '';

	/**
	 * @var string. NerdPress CF email address
	 *
	 * @since 0.0.1
	 */
	private static $cloudflare_email = 'support@nerdpress.net';

	/**
	 * @var string. Cloudflare API base endpoint
	 *
	 * @since 0.0.1
	 */
	private static $cloudflare_api_url = 'https://api.cloudflare.com/client/';

	/**
	 * @var string. Cloudflare API version
	 *
	 * @since 0.0.1
	 */
	private static $cloudflare_api_version = 'v4/';

	/**
	 * @var string. Zones subdir string
	 *
	 * @since 0.0.1
	 */
	private static $cloudflare_zones_directory = 'zones/';

	/**
	 * @var array. Content type header
	 *
	 * @since 0.0.1
	 */
	private static $header_content_type = null;

	/**
	 * @var string. Cloudflare zone
	 *
	 * @since 0.0.1
	 */
	private static $cloudflare_zone = '';

	/**
	 * @var array. Cloudflare specific headers
	 *
	 * @since 0.0.1
	 */
	private static $header_cloudflare = null;

	/**
	 * @var string. Target host
	 *
	 * @since 0.0.1
	 */
	private static $custom_hostname = '';

	/**
	 * @var string. Cache Clear type
	 * full vs single post (gets the value of the post's URL)
	 *
	 * @since 0.2.0
	 */
	private static $cache_clear_type = '';

	/**
	 * @var string. Post status before
	 *
	 * @since 0.2.1
	 */
	private static $status_before = '';

	/**
	 * @var string. Post status after
	 *
	 * @since 0.2.1
	 */
	private static $status_after = '';

	/**
	 * @var string. URL where the cache was triggered from
	 *
	 * @since 0.2.1
	 */
	private static $cache_trigger_url = '';

	/**
	 * @var strin. Gets what method your are in so we can pass to other methods and eventually Slack through Zapier.
	 *
	 * @since 0.8.2
	 */
	private static $which_cloudflare_method = '';

	/**
	 * @var bool. Suppress NerdPress Notification or not.
	 *
	 * @since 0.8.3
	 */
	private static $suppress_notification = false;

	/**
	 * @var bool. Are we already clearing any comment cache?
	 *
	 * @since 0.9.0
	 */
	private static $clearing_comment_cache = false;

	/**
	 * @var string. The hook tag used for the cron job that powers the cache purge debouncing.
	 */
	private static $cron_hook_tag = 'nerdpress_cf_cache_clear';

	/**
	 * NerdPress_Cloudflare_Client static initializizer
	 *
	 * @since 0.0.1
	 */
	public static function init() {
		if ( self::$header_cloudflare ) {
			return;
		}
		// Strip home_url() of protocol and anything after the first /
		preg_match( '#https?://([^/]*)#i', home_url(), $base_url );
		self::$custom_hostname = $base_url[1];

		$nerdpress_options = get_option( 'blog_tutor_support_settings' );
		if ( isset( $nerdpress_options['cloudflare_token'] ) ) {
			self::$cloudflare_api_key = $nerdpress_options['cloudflare_token'];
		}
		self::$header_cloudflare   = [ 'Authorization' => 'Bearer ' . self::$cloudflare_api_key ];
		self::$header_content_type = [ 'Content-Type' => 'application/json' ];

		$class = __CLASS__;
		new $class;
	}

	/**
	 * NerdPress_Cloudflare_Client constructor
	 *
	 * @since 0.0.1
	 */
	public function __construct() {
		$nerdpress_options = get_option( 'blog_tutor_support_settings' );
		$firewall_choice   = $nerdpress_options['firewall_choice'];
		if ( ( $firewall_choice === 'cloudflare' ) && isset( $nerdpress_options['cloudflare_token'] ) ) {

			$this->cloudflare_notices = [ 'nerdpress_cloudflare_notice' ];

			add_action( 'wp_enqueue_scripts', array( $this, 'inject_scripts' ), 20 );
			add_action( 'admin_enqueue_scripts', array( $this, 'inject_scripts' ), 20 );
			add_action( 'deleted_post', array( $this, 'handle_deletion_cache' ), 10, 1 );
			add_action( 'delete_attachment', array( $this, 'handle_deletion_cache' ), 10, 1 );
			add_action( 'transition_post_status', array( $this, 'handle_post_cache_transition' ), 10, 3 );
			add_action( 'transition_comment_status', array( $this, 'handle_comment_cache_transition' ), 9, 3 );
			add_action( 'comment_post', array( $this, 'handle_comment_cache' ), 9, 3 );
			add_action( 'edit_comment', array( $this, 'handle_comment_cache_edit' ), 9, 2 );
			add_action( 'admin_notices', array( $this, 'cloudflare_notices' ) );
			add_action( 'wp_ajax_purge_cloudflare_full', array( $this, 'purge_cloudflare_full' ) );
			add_action( 'wp_ajax_purge_cloudflare_url', array( $this, 'purge_cloudflare_url' ) );
			add_action( self::$cron_hook_tag, array( $this, 'debounce_purge_cloudflare_cache_handler' ), 10, 1 );
		}
	}

	/**
	 * Enqueue scripts
	 *
	 * @since 0.0.1
	 */
	public function inject_scripts() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}
		global $wp;
		wp_register_script( 'np_cf_js', esc_url( NerdPress::$plugin_dir_url . 'includes/js/np-cloudflare.js' ), array( 'jquery' ), BT_PLUGIN_VERSION );
			wp_enqueue_script( 'np_cf_js' );
		wp_localize_script(
			'np_cf_js',
			'np_cf_ei',
			array(
				'endpoint'     => admin_url( 'admin-ajax.php' ),
				'nonce'        => wp_create_nonce( 'np_cf_ei_secure_me' ),
				'url_to_purge' => home_url( add_query_arg( array(), $wp->request ) ),
			)
		);
	}

	/**
	 * Sanitize input for all the options
	 *
	 * @since 0.0.1
	 *
	 * @param string input. Option value
	 * @return string input. Sanitzed input
	 */
	public function sanitize_option( $input ) {
		$options = array(
			'hostname',
		);

		foreach ( $options as $option ) {
			if ( isset( $input[ $option ] ) ) {
				$input[ $option ] = sanitize_text_field( $input[ $option ] );
			}
		}

		return $input;
	}

	/**
	 * Assemble the url part that's the same for all API calls
	 *
	 * @since 0.0.1
	 */
	private static function assemble_url() {
		$nerdpress_options = get_option( 'blog_tutor_support_settings' );
		$cloudflare_zone   = $nerdpress_options['cloudflare_zone'];
		if ( $cloudflare_zone === 'dns1' ) {
			self::$cloudflare_zone = 'cc0e675a7bd4f65889c85ec3134dd6f3';
		} elseif ( $cloudflare_zone === 'dns2' ) {
			self::$cloudflare_zone = 'c14d1ac2b0e38a6d49d466ae32b1a6d7';
		} elseif ( $cloudflare_zone === 'dns3' ) {
			self::$cloudflare_zone = '2f9485f19471fe4fa78fb51590513297';
		} else {
			return;
		}
		return self::$cloudflare_api_url . self::$cloudflare_api_version . self::$cloudflare_zones_directory . self::$cloudflare_zone . '/';
	}

	/**
	 * Execute a post request
	 *
	 * @since 0.0.1
	 *
	 * @param string url. Url to post to
	 * @param array opts. Associative options array
	 * @return WP_Error|array. Response from the endpoint
	 */
	private static function post( $url, $opts ) {
		$opts['timeout'] = 8;

		return wp_remote_post( $url, $opts );
	}

	/**
	 * Process error received from the CF endpoint
	 *
	 * @since 0.0.1
	 *
	 * @param WP_ERROR result. WP_ERROR object
	 * @param string methodname. Name of the method that sent the originating request
	 */
	private static function send_alert( $result ) {
		$hookUrl      = 'https://hooks.zapier.com/hooks/catch/332669/o1lpmis/';
		$current_user = wp_get_current_user();

		$error = array(
			'host'    => array( self::$custom_hostname ),
			'payload' => stripslashes( str_replace( array( '\n', '\r' ), '', wp_json_encode( $result['body'] ) ) ),
			'cf-ray'  => wp_json_encode( $result['headers']['CF-Ray'] ),
			'user'    => "$current_user->user_login ($current_user->display_name)",
			'cleared' => self::$cache_clear_type,
			'method'  => self::$which_cloudflare_method,
			'url'     => self::$cache_trigger_url,
			'before'  => self::$status_before,
			'after'   => self::$status_after,
		);

		$res = wp_remote_post(
			$hookUrl,
			array(
				'headers'     => array(
					'Content-Type' => 'application/json',
				),
				'body'        => wp_json_encode( $error ),
				'method'      => 'POST',
				'data_format' => 'body',
				'timeout'     => 10,
			)
		);
	}

	/**
	 * Store the response in the options table to display the
	 * dashboard notification on the next load
	 *
	 * @since 0.0.1
	 */
	private static function store_result( $result ) {
		update_option( 'nerdpress_cloudflare_notice', $result );
	}

	/**
	 * Process response from the Cloudflare endpoint
	 *
	 * @since 0.0.1
	 *
	 * @param string methodName. Name f the method calling process_response
	 * @param WP_Error|array. Response/error
	 *
	 * @return bool. wp_mail status
	 */
	private static function process_response( $result ) {
		if ( is_wp_error( $result ) ) {
			$result = array(
				'body' => wp_json_encode(
					array(
						'errors' => array(
							array( 'message' => $result->get_error_message() ),
						),
					)
				),
			);
		}

		self::send_alert( $result );

		if ( ! self::$suppress_notification ) {
			self::store_result( $result['body'] );
		}
		return 'success';
	}

	/**
	 * Debounce cache clears (unique to the passed $prefixes)
	 *
	 * @param array $prefixes The prefixes to clear.
	 * @return void
	 */
	public static function debounce_purge_cloudflare_cache( $prefixes = array() ) {
		$debounce_threshold  = 60; // TODO: Make customizable via UI.
		$cache_clear_id      = md5( serialize( $prefixes ) );
		$transient_id        = 'nerdpress_cf_cache_clear_' . $cache_clear_id;
		$cache_clear_payload = get_transient( $transient_id );

		// If there's no transient, a cache clear has not happened within the
		// past $debounce_threshold, so no debouncing is necessary. Clear the
		// cache and set the flag to debounce any additional requests.
		if ( false === $cache_clear_payload ) {
			self::purge_cloudflare_cache( $prefixes );

			$payload = [
				time() + $debounce_threshold, // The next time the cache can be flushed.
				serialize( $prefixes ),       // Store the prefixes for future retrieval.
			];

			set_transient( $transient_id, implode( '|', $payload ), $debounce_threshold );
		} else {
			// Break apart our payload into [0] our execution timestamp and [1] our cache prefixes.
			$cache_clear_payload = explode( '|', $cache_clear_payload );

			// Normalize our data.
			$timestamp = $cache_clear_payload[0];
			$prefixes  = unserialize( $cache_clear_payload[1] );

			// Ensure that our cron hook is clear, taking into consideration our cache clear prefixes.
			wp_clear_scheduled_hook( self::$cron_hook_tag, array( $prefixes ) );

			// Schedule our cache clear event to happen at our desired timestamp.
			wp_schedule_single_event( $timestamp, self::$cron_hook_tag, array( $prefixes ) );
		}
	}

	/**
	 * Callback for cron job set up to handle debounced cache clears.
	 *
	 * @param mixed $prefixes The stored prefixes to pass along to the cache clear.
	 * @return void
	 */
	public function debounce_purge_cloudflare_cache_handler( $prefixes ) {
		self::purge_cloudflare_cache( $prefixes );
	}

	/**
	 * Purge URLs by tag(s)
	 *
	 * @since 0.0.1
	 *
	 * @param array= prefixes. URLs to purge. Purge full site cache if no prefixes passed
	 * @return string. Cloudflare result id
	 */
	public static function purge_cloudflare_cache( $prefixes = array() ) {
		if ( ! self::$custom_hostname ) {
			return 'error';
		}

		if ( ! NerdPress_Helpers::is_production( home_url( '/' ) ) ) {
				return 'skip_cache_clearing';
		}

		if ( empty( $prefixes ) ) {
			self::$cache_clear_type = 'full';
			$body                   = '{ "hosts": ["' . self::$custom_hostname . '"] }';
			$time                   = time();

			if ( get_option( 'nerdpress_full_cache_clear_time' ) === false ) {
				add_option( 'nerdpress_full_cache_clear_time', $time, '', false );
			}

			$last_cloudflare_cache_clear_time = get_option( 'nerdpress_full_cache_clear_time' );
			$time_since_cache_clearing        = $time - $last_cloudflare_cache_clear_time;

			if ( $time_since_cache_clearing < 10 ) {
				return 'cache_clear_rate_limited';
			}

		} else {

			foreach ($prefixes as $prefix) {
			// Removing all query strings if lang= exist.
				if ( strpos( $prefix, 'lang=' ) !== false ) {
					$prefix = preg_replace( '#\?.*$#', '', $prefix );
				}
			}

			// Bypassing cache clearing if another query string exists.
			if ( NerdPress_Helpers::cache_clear_bypass_on_string( $prefixes ) ) {
				return 'skip_cache_clearing';
			}

			// Removing http(s):// because Cloudflare API "prefixes" cache clear requires it
			$prefixes_no_protocol   = preg_replace( '#https?://#', '', $prefixes );

			self::$cache_clear_type = implode( ',', $prefixes_no_protocol );
			$body                   = '{ "prefixes": ["' . implode( ',', $prefixes_no_protocol ) . '"] }';
		}

		$url  = self::assemble_url() . 'purge_cache';
		$opts = array(
			'headers' => array_merge(
				self::$header_content_type,
				self::$header_cloudflare
			),
			'body'    => $body,
		);

		$result = self::post( $url, $opts );

		if (
			self::$cache_clear_type === 'full'
			&& ! is_wp_error( $result )
		) {
			$api_call_status = json_decode( $result['body'] );
			if ( $api_call_status->success ) {
				update_option( 'nerdpress_full_cache_clear_time', $time, false );
			}
		}

		return self::process_response( $result );
	}

	public function handle_post_cache_transition( $new_status, $old_status, $post ) {
		if ( ( $old_status !== 'publish' && $new_status !== 'publish' ) || self::$clearing_comment_cache ) {
			return;
		}

		self::$suppress_notification   = true;
		self::$which_cloudflare_method = __METHOD__;
		self::$cache_trigger_url       = get_permalink( $post );
		self::$status_before           = $old_status;
		self::$status_after            = $new_status;

		self::debounce_purge_cloudflare_cache();
	}

	/**
	 * A purge cache method wrapper for the AJAX calls
	 *
	 * @since 0.0.1
	 */
	public static function purge_cloudflare_full() {
		check_ajax_referer( 'np_cf_ei_secure_me', 'np_cf_ei_nonce' );

		self::$which_cloudflare_method = __METHOD__;

		if ( ! current_user_can( 'edit_posts' ) ) {
			echo 'Current user cannot clear the Cloudflare cache';
			die();
		}

		echo self::debounce_purge_cloudflare_cache();
		die();
	}

	/**
	 * HTML for the admin notice
	 *
	 * @since 0.0.1
	 */
	public function cloudflare_admin_notice( $notice_name ) {
		$_response = get_option( $notice_name );
		if ( ! $_response ) {
			return;
		}

		try {
			$response = json_decode( $_response, true );
		} catch ( Exception $ex ) {
			$response             = [];
			$response['success']  = false;
			$response['messages'] = [ 'Broken JSON response from Cloudflare' ];
		}

		$html  = '<style>.nerdpress-notice { border-left: 4px solid green } .nerdpress-notice.error { border-left: 4px solid red } </style>';
		$html .= '<div class="notice nerdpress-notice' . ( ! empty( $response['success'] ) ? '' : ' error' ) . '" style="display: flex; align-items: center;">';
		$html .= '<p><img src="' . esc_url( plugins_url( 'includes/images/nerdpress-icon-250x250.png', dirname( __FILE__ ) ) ) . '" style="max-width:45px;vertical-align:middle;"></p>';
		$html .= '<div><h2>NerdPress Notice:</h2>';

		if ( ! empty( $response['success'] ) && empty( $response['messages'] ) ) {
			$response['messages'] = [ 'Cloudflare Enterprise cache has been successfully cleared!' ];
			// If there's an error response, messages are within the error object
		} elseif ( empty( $response['success'] ) ) {
			$response['messages'] = [];
			foreach ( $response['errors'] as $error ) {
				$response['messages'][] = $error['message'];
			}
		}

		foreach ( $response['messages'] as $message ) {
			$html .= '<p>' . $message . '</p>';
		}
		$html .= '</div></div>';

		delete_option( $notice_name );
		echo $html;
	}

	/**
	 * Display cloudflate status notices
	 *
	 * @since 0.0.1
	 */
	public function cloudflare_notices() {
		foreach ( $this->cloudflare_notices as $notice ) {
			$this->cloudflare_admin_notice( $notice );
		}
	}

	/**
	 * Clear current URL from front end admin menu.
	 *
	 * @since 0.7.2:
	 */
	public function purge_cloudflare_url() {
		check_ajax_referer( 'np_cf_ei_secure_me', 'np_cf_ei_nonce' );

		self::$which_cloudflare_method = __METHOD__;

		if ( ! current_user_can( 'edit_posts' ) ) {
			echo 'Current user cannot clear the Cloudflare cache';
			die();
		}

		$url_clear               = esc_url( $_POST['url'] );
		self::$cache_trigger_url = $url_clear;
		echo self::debounce_purge_cloudflare_cache( array( $url_clear ) );
		die();
	}

	/**
	 * Handle comments' status transitions
	 *
	 * @since 0.2.0
	 *
	 * @param string $new_status. The current status of the comment
	 * @param string $old_status. Prev value of the comment
	 * @param object $comment. WP_Comment object
	 */
	public function handle_comment_cache_transition( $new_status, $old_status, $comment ) {
		self::$clearing_comment_cache  = true;
		self::$suppress_notification   = true;
		self::$which_cloudflare_method = __METHOD__;

		// Some of these could be combined but let's leave these for clarity
		if (
			( $old_status === 'unapproved' && ( $new_status === 'trash' || $new_status === 'spam' ) )
			|| ( $new_status === 'unapproved' && ( $old_status === 'trash' || $old_status === 'spam' ) )
			|| ( $new_status === 'spam' && $old_status === 'trash' )
			|| ( $new_status === 'trash' && $old_status === 'spam' )
			|| ( $new_status === 'delete' && ( $old_status === 'trash' || $old_status === 'spam' ) )
			|| ( $new_status !== 'approved' && $old_status === 'post-trashed' )
		) {
			return;
		}

		$post_id = $comment->comment_post_ID;
		if ( ! $post_id ) {
			return;
		}

		self::$cache_trigger_url = get_permalink( $post_id );
		self::$status_before     = $old_status;
		self::$status_after      = $new_status;

		self::debounce_purge_cloudflare_cache( array( get_permalink( $post_id ) ) );
	}

	/**
	 * Handle a comment submitted from the front end
	 *
	 * @since 0.2.0
	 *
	 * @param integer $comment_id. WP_Comment ID
	 * @param mixed $comment_approved. If the comment approved or not, also may carry "spam" status.
	 * @param array $comment_data. Associative array with comment data
	 */
	public function handle_comment_cache( $comment_id, $comment_approved, $comment_data ) {
		self::$clearing_comment_cache  = true;
		self::$suppress_notification   = true;
		self::$which_cloudflare_method = __METHOD__;

		if (
			! $comment_approved
			|| $comment_approved === 'spam'
			|| $comment_approved === 'trash'
		) {
			return;
		}

		$post_id = $comment_data['comment_post_ID'];
		if ( ! $post_id ) {
			return;
		}

		self::$cache_trigger_url = get_permalink( $post_id );
		self::$status_before     = 'new comment';
		self::$status_after      = 'approved';

		self::debounce_purge_cloudflare_cache( array( get_permalink( $post_id ) ) );
	}

	/**
	 * Handle the edit_comment hook
	 *
	 * @since 0.2.0
	 *
	 * @param integer $comment_id. WP_Comment ID
	 * @param array $data. Associative array with the comment's data
	 */
	public function handle_comment_cache_edit( $comment_id, $data ) {
		self::$clearing_comment_cache  = true;
		self::$suppress_notification   = true;
		self::$which_cloudflare_method = __METHOD__;

		if ( ! $data['comment_approved'] ) {
			return;
		}

		$post_id = $data['comment_post_ID'];
		if ( ! $post_id ) {
			return;
		}

		self::$cache_trigger_url = get_permalink( $post_id );
		self::$status_before     = 'approved';
		self::$status_after      = ( $data['comment_approved'] ? $data['comment_approved'] : 'pending' );

		self::debounce_purge_cloudflare_cache( array( get_permalink( $post_id ) ) );
	}

	/**
	 * Handle the delete_post and delete_attachment hooks
	 *
	 * @since 0.2.0
	 *
	 * @param integer $post_id. Id of the post
	 */
	public function handle_deletion_cache( $post_id ) {
		if ( get_post_status( $post_id ) !== 'publish' ) {
			return;
		}

		self::$cache_trigger_url       = get_permalink( $post_id );
		self::$status_before           = 'publish';
		self::$status_after            = 'delete';
		self::$suppress_notification   = true;
		self::$which_cloudflare_method = __METHOD__;

		self::debounce_purge_cloudflare_cache();
	}
}

add_action( 'init', array( 'NerdPress_Cloudflare_Client', 'init' ) );
