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
		if ( current_user_can( 'editor' ) || current_user_can( 'administrator' ) ) {
			if ( ! is_admin() ) {
				add_action( 'wp_footer', array( $this, 'widget' ), 50 );
			} else {
				add_action( 'admin_footer', array( $this, 'widget' ), 50 );
			}
			add_action( 'init', array( $this, 'maybe_show_widget' ), 20 );
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
			}
			(window, document, window.Beacon || function() {});
		</script>
		<?php
		if ( is_admin() ) {
			?>
			<script type = "text/javascript">
				window.Beacon('init', '85b7b97c-d6a0-4ff9-a392-8344155cc991')
			</script>
			<?php
		}
	}

	public function maybe_show_widget() {
		if ( ! is_admin() || ( isset( $options['hide_tab'] ) ) ) {
			?>
			<style type="text/css">
				#beacon-container button[aria-expanded="false"] { display: none !important; }
			</style>
			<?php
		}
	}
}

new NerdPress_Widget();
