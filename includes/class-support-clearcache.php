<?php
if ( !defined('ABSPATH') )
	die();

	/**
	 * Blog_Tutor_Support Clear Cache
	 *
	 * @package  Blog_Tutor_Support
	 * @category Core
	 * @author Andrey Kalashnikov
	 */

class Blog_Tutor_Support_Clearcache {
	
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'blog_tutor_clearcache_message' ), 59 );
		add_action( 'wp_ajax_sucuri_clearcache', array( $this, 'sucuri_clearcache' ) );
		add_action( 'init', array( $this, 'bt_enqueue_scripts' ) );
	}

	public function blog_tutor_clearcache_message() {
        if( ! array_key_exists( 'clearcache', $_GET ) ) return;
		?>
			<div class="notice" style="border-left-color:#0F145B">
				<p><img src="<?php echo esc_url( site_url() ); ?>/wp-content/plugins/blog-tutor-support/includes/images/nerdpress-icon-250x250.png" style="max-width:45px;vertical-align:middle;"><strong>The cache is being cleared. Note that it may take up to two minutes for it to be fully flushed.</strong></p>
			</div>
			<?php
	}

	public function sucuri_clearcache() {
		check_ajax_referer('sucuri_clearcache_secure_me', 'sucuri_clearcache_nonce');

		$sucuri_api_call_array = Blog_Tutor_Support_Helpers::get_sucuri_api_call();
		if ( is_array( $sucuri_api_call_array ) ) {
			$sucuri_api_call = implode( $sucuri_api_call_array );
			$cloudproxy_clear = $sucuri_api_call . '&a=clearcache';
			
			$args = array( 'timeout' => 30 );
			
			$response = wp_remote_get( $cloudproxy_clear, $args );
			if( is_wp_error( $response ) ) {
				echo false;
				die();
			}
			
			$body = wp_remote_retrieve_body( $response );
			try {
				$message_body = json_decode($body, TRUE);
			} catch(Exception $e) {
				echo false;
				die();
			}

			$message = ($message_body['status'] == 0
				    ? 'There was a problem clearing the Sucuri Firewall cache. Please try again, and if it still doesn\'t work please contact support@nerdpress.net.'
				    : $message_body['messages']['0']);

			echo ($message_body['status'] == 0 ? false : $message);
			die();
		}
		
		die();
	}

	public function bt_enqueue_scripts() {
		if( user_can( get_current_user_id(), 'edit_posts' ) ) {
			wp_enqueue_script('jquery'); 
			wp_register_script( 'clearcache_js', plugins_url( 'js/bt-clearcache.js', __FILE__ ), array(), BT_PLUGIN_VERSION );
			wp_localize_script( 'clearcache_js', 'sucuri_clearcache', array(
				'endpoint'			  => admin_url( 'admin-ajax.php' ),
				'nonce'				 => wp_create_nonce( 'sucuri_clearcache_secure_me' ),
			));
			wp_enqueue_script( 'clearcache_js' );
		}
	}
}

new Blog_Tutor_Support_Clearcache(); 
