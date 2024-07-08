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

	}

	public function register_widget() {
		if (NerdPress_Helpers::is_relay_server_configured()) {
			wp_add_dashboard_widget(
				'nerdpress_widget',
				'<span class="ab-icon"></span>NerdPress Stats <span class="small">powered by:</span><span class="cf-logo small ab-icon"></span>',
				array($this, 'render_widget'),
				null,
				null,
				'side',
				'high'
			);
		}
    }

	public function render_widget() {
		$html = self::send_request_to_relay();
		if ( is_wp_error( $html ) ) {
            echo 'Error when fetching data from Cloudflare. Please try again later.' ;
            return;
        }
        ?>
        <div class="nerdpress-widget-content">
			<?php echo json_decode($html['body'])->html;?>
        </div>
        <?php
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
		if ( is_admin() && ( ! isset( $options['hide_tab'] ) ) && ! defined('IFRAME_REQUEST') ) {
			?>
			<script type = "text/javascript">
				<?php echo NerdPress_Helpers::$help_scout_widget_init; ?>
			</script>
			<?php
		}
		wp_enqueue_script( 'chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '2.9.4', true );

	}

		public static function send_request_to_relay() {

		if ( defined( 'SSLVERIFY_DEV' ) && SSLVERIFY_DEV === false ) {
			$status = false;
		} else {
			$status = true;
		}

		$relay_url = NerdPress_Helpers::relay_server_url() . 'wp-json/nerdpress/v1/client-display-traffic';
		$api_token = NerdPress_Helpers::relay_server_api_token();

		$args = array(
			'headers'   => array(
				'Authorization' => "Bearer $api_token",
				'Content-Type'  => 'application/json',
				'Domain'        => site_url(),
			),
			// Bypass SSL verification when using self
			// signed cert. Like when in a local dev environment.
			'sslverify' => $status,
			'timeout' => 10
		);

		// Make request to the relay server.
		$api_response = wp_remote_get( $relay_url, $args );

		return $api_response;
	}
}

new NerdPress_Widget();
