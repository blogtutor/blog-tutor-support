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
		if ( ( current_user_can( 'editor' ) || current_user_can( 'administrator' ) ) && isset( $_GET['page'] ) && $_GET['page'] !== 'feast-support' ) {
			if ( ! is_admin() ) {
				add_action( 'wp_footer', array( $this, 'widget' ), 50 );
			} else {
				add_action( 'admin_footer', array( $this, 'widget' ), 50 );
			}
		}
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
	}
}

new NerdPress_Widget();
