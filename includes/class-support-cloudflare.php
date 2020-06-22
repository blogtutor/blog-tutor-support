<?php
if ( ! defined( 'ABSPATH' ) )
	die();

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
	private static $xAuthKey = '';

	/**
	 * @var string. NerdPress CF email address
	 *
	 * @since 0.0.1
	 */
	private static $xAuthEmail = 'support@nerdpress.net';

	/**
	 * @var string. Cloudflare API base endpoint
	 *
	 * @since 0.0.1
	 */
	private static $baseUrl = 'https://api.cloudflare.com/client/';

	/**
	 * @var string. Cloudflare API version
	 *
	 * @since 0.0.1
	 */
	private static $apiVersion = 'v4/';

	/**
	 * @var string. Zones subdir string
	 *
	 * @since 0.0.1
	 */
	private static $zonesSubdir = 'zones/';

	/**
	 * @var array. Content type header
	 *
	 * @since 0.0.1
	 */
	private static $_hContentType = NULL;

	/**
	 * @var string. Cloudflare zone
	 *
	 * @since 0.0.1
	 */
	private static $_cfZone = '';

	/**
	 * @var array. Cloudflare specific headers
	 *
	 * @since 0.0.1
	 */
	private static $_hCloudflare = NULL;

	/**
	 * @var string. Target host 
	 * 
	 * @since 0.0.1
	 */
	private static $_cfTargetHost = '';

	/**
	 * @var string. Cache Clear type
	 * full vs single post (gets the value of the post's URL)
	 *
	 * @since 0.2.0
	 */
	private static $cacheType = '';

	/**
	 * @var string. Post status before
	 *
	 * @since 0.2.1
	 */
	private static $beforeStatus = '';

	/**
	 * @var string. Post status after
	 *
	 * @since 0.2.1
	 */
	private static $afterStatus = '';

	/**
	 * @var string. URL where the cache was triggered from
	 *
	 * @since 0.2.1
	 */
	private static $triggerUrl = '';

	/**
	 * NerdPress_Cloudflare_Client static initializizer
	 *
	 * @since 0.0.1
	 */
	public static function init() {
		if ( self::$_hCloudflare )
			return;
// 		@self::$_cfTargetHost = get_option( 'np_cf_ei' )['hostname'];
// 		if ( empty ( self::$_cfTargetHost ) ) {
			self::$_cfTargetHost = preg_replace( '#https?://#i', '', home_url() );
//		}

		$nerdpress_options = get_option( 'blog_tutor_support_settings' );
		if ( isset( $nerdpress_options['cloudflare_token'] ) ) {
			self::$xAuthKey = $nerdpress_options['cloudflare_token'];
		}
		self::$_hCloudflare = [
			'Authorization' => 'Bearer ' . self::$xAuthKey,
		];
		self::$_hContentType = [
			'Content-Type' => 'application/json' 
		];

		$class = __CLASS__; 
		new $class;
	}
 
	/**
	 * NerdPress_Cloudflare_Client constructor
	 *
	 * @since 0.0.1
	 */
	public function __construct() {
		$this->cfNotices = ['np_cf_cc_notice']; 

		add_action( 'init', array( $this, 'injectScripts' ), 20 );
		add_action( 'deleted_post', array( $this, 'handleDeletionCache' ), 10, 1 );
		add_action( 'delete_attachment', array( $this, 'handleDeletionCache' ), 10, 1 );
		add_action( 'transition_post_status', array( $this, 'handlePostCacheTransition' ), 10, 3 );
		add_action( 'transition_comment_status', array( $this, 'handleCommentCacheTransition' ), 9, 3 );
		add_action( 'comment_post', array( $this, 'handleCommentCache' ), 9, 3 );
		add_action( 'edit_comment', array( $this, 'handleCommentCacheEdit' ), 9, 2 );
		add_action( 'admin_notices', array( $this, 'cloudflare_notices' ) );
		add_action( 'wp_ajax_purgeCacheAjaxWrapper', array( $this, 'purgeCacheAjaxWrapper' ) );
	}

	/**
	 * Enqueue scripts
	 *
	 * @since 0.0.1
	 */
	public function injectScripts() {
		if ( ! current_user_can( 'edit_posts' ) )
			return;

		wp_register_script( 'np_cf_js', plugins_url( 'includes/js/np-cf.js', dirname( __FILE__ ) ), array( 'jquery' ), BT_PLUGIN_VERSION );
		wp_localize_script( 'np_cf_js', 'np_cf_ei', array(
			'endpoint' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'np_cf_ei_secure_me' )
		));
		wp_enqueue_script( 'np_cf_js' );  
	}

	/**
	 * Sanitize input for all the options
	 *
	 * @since 0.0.1
	 *
	 * @param string input. Option value
	 * @return string input. Sanitzed input
	 */
	public function santizeOption( $input ) {
		$options = array(
			'hostname'
		);

		foreach( $options as $option ) 
			if ( isset ( $input[$option] ) )
				$input[$option] = sanitize_text_field( $input[$option] ); 
	   
		return $input; 
	}
	
	/**
	 * Assemble the url part that's the same for all API calls
	 *
	 * @since 0.0.1
	 */
	private static function assembleUrl() {
		$nerdpress_options = get_option( 'blog_tutor_support_settings' );
		$cloudflare_zone   = $nerdpress_options['cloudflare_zone'];
		if ( $cloudflare_zone === 'dns1') {
			self::$_cfZone = 'cc0e675a7bd4f65889c85ec3134dd6f3';
		} elseif ( $cloudflare_zone === 'dns2' ){
			self::$_cfZone = 'c14d1ac2b0e38a6d49d466ae32b1a6d7';
		} elseif ( $cloudflare_zone === 'dns3' ){
			self::$_cfZone = '2f9485f19471fe4fa78fb51590513297';
		} else {
			return;
		}
		return self::$baseUrl . self::$apiVersion . self::$zonesSubdir . self::$_cfZone . '/';
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
	private static function sendAlert( $result, $methodName = '' ) {
		$hookUrl = 'https://hooks.zapier.com/hooks/catch/332669/o1lpmis/';

		if ( defined( 'NP_CALLING_CACHE_METHOD' ) ) {
			$method = NP_CALLING_CACHE_METHOD;
		}
		$error = array(
			'host'    => array( self::$_cfTargetHost ),
			'payload' => stripslashes( str_replace( array( '\n', '\r' ), '', json_encode( $result['body'] ) ) ),
			'cf-ray'  => json_encode( $result['headers']['CF-Ray'] ),
			'cleared' => self::$cacheType,
			'method'  => $method,
			'url'     => self::$triggerUrl,
			'before'  => self::$beforeStatus,
			'after'   => self::$afterStatus
		);

		$res = wp_remote_post( $hookUrl, array(
				'headers' => array(
					'Content-Type' => 'application/json'
				),
				'body'        => json_encode( $error ),
				'method'      => 'POST',
				'data_format' => 'body',
				'timeout'     => 10,
		) ); 
	}

	/**
	 * Store the response in the options table to display the
	 * dashboard notification on the next load
	 *
	 * @since 0.0.1
	 */
	private static function storeResult( $result ) {
		update_option( 'np_cf_cc_notice', $result );
	}

	/**
	 * Process response from the Cloudflare endpoint
	 *
	 * @since 0.0.1
	 *
	 * @param string methodName. Name f the method calling processResponse
	 * @param WP_Error|array. Response/error
	 *
	 * @return bool. wp_mail status
	 */
	private static function processResponse( $methodName, $result ) {
		if ( is_wp_error( $result ) ) {
			$result = array(
				'body' => json_encode( array(
					'errors' => array(
						array( 'message' => $result->get_error_message() )
					)
				) )
			);
		}

// 		self::sendAlert( $result['body'] );
		self::sendAlert( $result );
	
		if ( ! defined( 'NP_SUPPRESS_NOTIFICATION' ) )
			self::storeResult( $result['body'] );
		return 'success';
	}

	/**
	 * Purge file by tag(s)
	 *
	 * @since 0.0.1
	 *
	 * @param array= files. Files to purge. Purge full site cache if no files passed
	 * @return string. Cloudflare result id
	 */
	public static function purgeCache( $files = array() ) {
		if ( ! self::$_cfTargetHost ) {
			return 'error';
		} 

		self::$cacheType = ( empty( $files ) ? 'full' : implode(',', $files ) );

		$url = self::assembleUrl() . 'purge_cache';

		$opts = array(
			'headers' => array_merge(
				self::$_hContentType,
				self::$_hCloudflare
			),
			'body' => ( empty( $files ) ? '{ "hosts": ["' . self::$_cfTargetHost . '"] }' : '{ "files": [' . implode( ',', $files ) . '] }' )
		);

		$result = self::post( $url, $opts );
		return self::processResponse( __METHOD__, $result );
	}

	public function handlePostCacheTransition( $new_status, $old_status, $post ) {
		if ( ( $old_status != 'publish' && $new_status != 'publish' ) || defined ( 'NP_DOING_COMMENT_CACHE' ) )
			return;
		
		define( 'NP_SUPPRESS_NOTIFICATION', TRUE );
		define( 'NP_CALLING_CACHE_METHOD', __METHOD__ );

		self::$triggerUrl   = get_permalink( $post );
		self::$beforeStatus = $old_status;
		self::$afterStatus  = $new_status;

		self::purgeCache();
	} 

	/**
	 * A purge cache method wrapper for the AJAX calls
	 *
	 * @since 0.0.1
	 */
	public static function purgeCacheAjaxWrapper() {
		check_ajax_referer( 'np_cf_ei_secure_me', 'np_cf_ei_nonce' );

		define( 'NP_CALLING_CACHE_METHOD', __METHOD__ );

		if ( ! current_user_can( 'edit_posts' ) ) {
			echo 'Current user cannot clear the Cloudflare cache';
			die();
		} 

		echo self::purgeCache();
		die(); 
	}

	/**
	 * HTML for the admin notice
	 * 
	 * @since 0.0.1
	 */
	public function noticeHtml( $notice_name ) {
		$_response = get_option( $notice_name );
		if ( ! $_response )
			return;

		try {
			$response = json_decode( $_response, TRUE );
		} catch( Exception $ex ) {
			$response             = [];
			$response['success']  = FALSE;
			$response['messages'] = [ 'Broken JSON response from Cloudflare' ];
		}

		$html = '<style>.nerdpress-notice { border-left: 4px solid green }'
			  . ' .nerdpress-notice.error { border-left: 4px solid red } </style>';
		$html .= '<div class="notice nerdpress-notice' . ( ! empty( $response['success'] ) ? '' : ' error' ) . '" style="display: flex; align-items: center;">';
	  $html .= '<p><img src=' . esc_url( site_url() ) . '/wp-content/plugins/blog-tutor-support/includes/images/nerdpress-icon-250x250.png" style="max-width:45px;vertical-align:middle;"></p>';
    $html .= '<div><h2>NerdPress Notification</h2>';

		if ( ! empty( $response['success'] ) && empty( $response['messages'] ) )
			$response['messages'] = [ 'Cloudflare Enterprise cache has been successfully cleared!' ];
		// If there's an error response, messages are within the error object
		elseif ( empty( $response['success'] ) ) {
			$response['messages'] = [];
			foreach( $response['errors'] as $error )
				$response['messages'][] = $error['message'];
		}

		foreach( $response['messages'] as $message )
			$html .= '<p>' . $message . '</p>';
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
		foreach( $this->cfNotices as $notice ) {
			$this->noticeHtml( $notice );
		}
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
	public function handleCommentCacheTransition( $new_status, $old_status, $comment ) {
		define( 'NP_DOING_COMMENT_CACHE', TRUE );
		define( 'NP_SUPPRESS_NOTIFICATION', TRUE );
		define( 'NP_CALLING_CACHE_METHOD', __METHOD__ );

		// Some of these could be combined but let's leave these for clarity
		if ( ( $old_status == 'unapproved' && ( $new_status == 'trash' || $new_status == 'spam' ) )
			|| ( $new_status == 'unapproved' && ( $old_status == 'trash' || $old_status == 'spam' ) ) 
			|| ( $new_status == 'spam' && $old_status == 'trash' )
			|| ( $new_status == 'trash' && $old_status == 'spam' )
			|| ( $new_status == 'delete' && ( $old_status == 'trash' || $old_status == 'spam') )
			|| ( $new_status != 'approved' && $old_status == 'post-trashed' ) )
			return;

		$post_id = $comment->comment_post_ID;
		if ( ! $post_id )
			return;

		self::$triggerUrl   = get_permalink( $post_id );
		self::$beforeStatus = $old_status;
		self::$afterStatus  = $new_status;

		self::purgeCache( array( '"' . get_permalink( $post_id ) . '"' ) );
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
	public function handleCommentCache( $comment_id, $comment_approved, $comment_data ) {
		define( 'NP_DOING_COMMENT_CACHE', TRUE );
		define( 'NP_SUPPRESS_NOTIFICATION', TRUE );
		define( 'NP_CALLING_CACHE_METHOD', __METHOD__ );

		if ( ! $comment_approved || $comment_approved == 'spam' )
			return;

		$post_id = $comment_data['comment_post_ID'];
		if ( ! $post_id )
			return;
		
		self::$triggerUrl   = get_permalink( $post_id );
		self::$beforeStatus = 'new comment';
		self::$afterStatus  = 'approved';

		self::purgeCache( array( '"' . get_permalink( $post_id ) . '"' ) );
	}

	/**
	 * Handle the edit_comment hook
	 *
	 * @since 0.2.0
	 *
	 * @param integer $comment_id. WP_Comment ID
	 * @param array $data. Associative array with the comment's data
	 */
	public function handleCommentCacheEdit( $comment_id, $data ) {
		define( 'NP_DOING_COMMENT_CACHE', TRUE );
		define( 'NP_SUPPRESS_NOTIFICATION', TRUE );
		define( 'NP_CALLING_CACHE_METHOD', __METHOD__ );

		if ( ! $data['comment_approved'] )
			return;

		$post_id = $data['comment_post_ID'];
		if ( ! $post_id )
			return; 
		
		self::$triggerUrl   = get_permalink( $post_id );
		self::$beforeStatus = 'approved';
		self::$afterStatus  = ( $data['comment_approved'] ? $data['comment_approved'] : 'pending' );
 
		self::purgeCache( array( '"' . get_permalink( $post_id ) . '"' ) );
   }

	/**
	 * Handle the delete_post and delete_attachment hooks
	 *
	 * @since 0.2.0
	 *
	 * @param integer $post_id. Id of the post
	 */
	public function handleDeletionCache( $post_id ) {
		if ( get_post_status( $post_id ) != 'publish' )
			return;

		self::$triggerUrl   = get_permalink( $post_id );
		self::$beforeStatus = 'publish';
		self::$afterStatus  = 'delete';

		define( 'NP_SUPPRESS_NOTIFICATION', TRUE );
		define( 'NP_CALLING_CACHE_METHOD', __METHOD__ );

		self::purgeCache();
	}
}

add_action( 'init', array( 'NerdPress_Cloudflare_Client', 'init' ) );
