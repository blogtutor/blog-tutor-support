<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * NerdPress Widget.
 *
 * @package  NerdPress/Frontend
 * @category Widget
 * @author   Fernando Acosta
 */
class NerdPress_Widget {
	/**
	 * Initialize the widget.
	 */
	public function __construct() {
		if ( current_user_can( 'editor' ) || current_user_can( 'administrator' ) ) {
			if ( ! is_admin() ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_widget_script' ) );
			}
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_widget_script' ) );
			add_action( 'wp_footer', array( $this, 'widget' ), 50 );
			add_action( 'admin_footer', array( $this, 'widget' ), 50 );
		}
	}

	public function enqueue_widget_script() {
		wp_enqueue_script( 'nerdpress-widget-loader', 'https://d29l98y0pmei9d.cloudfront.net/js/widget.min.js?k=Y2xpZW50SWQ9MTk0OSZob3N0TmFtZT1ibG9ndHV0b3Iuc3VwcG9ydGhlcm8uaW8=' );
	}

	public function widget() {
		$options = get_option( 'blog_tutor_support_settings', array() );

		if ( is_user_logged_in() ) {
			$nerdpress_current_user = wp_get_current_user();
			?>
			<script type="text/javascript">
			jQuery( window ).on( 'load', function( $ ) {
				if ( window.supportHeroWidget != undefined ) {
					var properties = {
						articleParameters: {  // These can be embedded in articles with {variable}
							username: '<?php echo $nerdpress_current_user->user_login; ?>',
							firstname: '<?php echo $nerdpress_current_user->user_firstname; ?>',
							lastname: '<?php echo $nerdpress_current_user->user_lastname; ?>',
							displayname: '<?php echo $nerdpress_current_user->display_name; ?>',
							useremail: '<?php echo $nerdpress_current_user->user_email; ?>',
							site_url: '<?php echo site_url(); ?>',
							sitename: '<?php echo get_bloginfo( 'name' ); ?>',
							currentpage: window.location.href
						},
						custom: {  // These are used to prefill the contact form
							userEmail: '<?php echo $nerdpress_current_user->user_email; ?>',
							name: '<?php echo $nerdpress_current_user->user_firstname . ' ' . $nerdpress_current_user->user_lastname; ?>'
						}
					};
					window.supportHeroWidget.track( properties );
				}
			});
			</script>
			<?php
			if ( ! is_admin() || ( isset( $options['hide_tab'] ) ) ) {
			?>
				<style type="text/css">
				#supporthero-button { display: none !important; }
				</style>
			<?php
			}
			if ( is_admin() ) {
				?>
				<style type="text/css">
				#plugin-information #supporthero-button, .wp-admin.wp-core-ui.update-php #supporthero-button { display: none !important; }
				</style>
				<?php
			}
		}
	}
}

new NerdPress_Widget();
