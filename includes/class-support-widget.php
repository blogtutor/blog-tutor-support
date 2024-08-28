<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * NerdPress Widget.
 *
 * @package  NerdPress/Frontend
 * @category Widget
 * @author   Sergio Scabuzzo
 */
class NerdPress_Widget {
	/**
	 * Initialize the widget.
	 */
	public function __construct() {
		if ( current_user_can( 'edit_others_posts' ) ) {
			if ( ! is_admin() ) {
				add_action( 'wp_footer', array( $this, 'widget' ), 50 );
			} else {
				if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'feast-support' ) {
					add_action( 'admin_footer', array( $this, 'widget' ), 50 );
				}
			}
		}

		add_action( 'wp_dashboard_setup', array( $this, 'register_widget' ) );
		add_action( 'rest_api_init', array( $this, 'register_traffic_frontend_api' ) );


	}

	public function register_widget() {
		$user = wp_get_current_user();
		$allowed_roles = array('administrator');

		if (
			NerdPress_Helpers::is_relay_server_configured()
			&& array_intersect( $allowed_roles, $user->roles )
			&& NerdPress_Helpers::is_nerdpress()
			) {
			wp_add_dashboard_widget(
				'nerdpress_widget',
				'<span class="ab-icon"></span>NerdPress Stats',
				array($this, 'render_widget'),
				null,
				null,
				'side',
				'high'
			);
		}
    }

	public function render_widget() {
		?>
		<div class="nerdpress-widget-content" id="nerdpress-widget-loading">
			<div class="dots-nerdpress-graph-loading"></div>
		</div>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				$.ajax({
					url: '<?php echo rest_url( 'nerdpress/v1/client-display-traffic' ); ?>',
					type: 'GET',
					timeout: 10000,
					dataType: "json",
					contentType: "application/json; charset=utf-8",
				}).done(function(response) {
					$('#nerdpress-widget-loading').html(response.html);
				}).fail(function(jqXHR, textStatus, errorThrown) {
					$('#nerdpress-widget-loading').html('<span>Oh no! We\'ve hit a snag fetching the data. 😭</span><span>Please try again later. If this continues to be an <a  href="#" onclick="window.Beacon(\'init\', \'85b7b97c-d6a0-4ff9-a392-8344155cc991\');Beacon(\'open\');Beacon(\'navigate\', \'/ask\');">issue please let us know.<a/></span>');
				});
			});
		</script>
		<?php
		wp_enqueue_script( 'chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '2.9.4', true );
}

	public function widget() {
		$options                = get_option( 'blog_tutor_support_settings', array() );
		$nerdpress_current_user = wp_get_current_user();
		?>
		<script type = "text/javascript">
			! function(e, t, n) {
				function a() {
					var e = t.getElementsByTagName("script")[0],
						n = t.createElement("script");
					n.type = "text/javascript", n.async = !0, n.src = "https://beacon-v2.helpscout.net", e.parentNode.insertBefore(n, e)
				}
				if (e.Beacon = n = function(t, n, a) {
						e.Beacon.readyQueue.push({
							method: t,
							options: n,
							data: a
						})
					}, n.readyQueue = [], "complete" === t.readyState) return a();
				e.attachEvent ? e.attachEvent("onload", a) : e.addEventListener("load", a, !1)
				e.Beacon('prefill', {
					name: '<?php echo esc_html( sanitize_text_field( $nerdpress_current_user->user_firstname . ' ' . $nerdpress_current_user->user_lastname ) ); ?>',
					email: '<?php echo esc_html( sanitize_text_field( $nerdpress_current_user->user_email ) ); ?>'
				})

			}(window, document, window.Beacon || function() {});
		</script>
		<?php
		if ( is_admin() && ( ! isset( $options['hide_tab'] ) ) && ! defined('IFRAME_REQUEST') && ! NerdPress_Helpers::is_nerdpress() ) {
			?>
			<script type = "text/javascript">
				<?php echo NerdPress_Helpers::$help_scout_widget_init; ?>
			</script>
			<?php
		}
	}

	public static function send_request_to_relay( WP_REST_Request $request ) {

		if ( defined( 'SSLVERIFY_DEV' ) && SSLVERIFY_DEV === false ) {
			$status = false;
		} else {
			$status = true;
		}

		$relay_url = esc_url( add_query_arg( array( 'timezone' => wp_timezone_string() ), NerdPress_Helpers::relay_server_url() . 'wp-json/nerdpress/v1/client-display-traffic' ) );
		$api_token = NerdPress_Helpers::relay_server_api_token();

		$args = array(
			'headers'   => array(
				'Authorization' => "Bearer $api_token",
				'Content-Type'  => 'application/json',
				'Domain'        => site_url(),
			),
			// Bypass SSL verification when using self-signed cert.
			// Like when in a local dev environment.
			'sslverify' => $status,
			'timeout' => 10
		);

		// Make request to the relay server.
		$api_response = wp_remote_get( $relay_url, $args );

		// Check if the request was successful.
		if ( is_wp_error( $api_response ) ) {
			// Handle error.
			$error_message = $api_response->get_error_message();
			error_log( "API request failed: $error_message" );

			// Return an error response.
			return new \WP_REST_Response(
				array(
					'error' => $error_message,
				),
				500
			);
		}

		// Parse the response body.
		$response_body = json_decode( $api_response['body'], true );

		// Check if the response body is valid JSON.
		if ( $response_body === null ) {
			// Handle invalid JSON response.
			error_log( "Invalid JSON response from API" );

			// Return an error response.
			return new \WP_REST_Response(
				array(
					'error' => 'Invalid JSON response from API',
				),
				500
			);
		}

		// Extract the necessary data from the response body.
		$data = [
			'html' => $response_body['html'],
		];

		// Return the JSON response.
		return new \WP_REST_Response(
			$data,
			200
		);
	}

	function register_traffic_frontend_api() {
		register_rest_route(
			'nerdpress/v1',
			'/client-display-traffic',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'send_request_to_relay' ),
			)
		);
	}
}

new NerdPress_Widget();
