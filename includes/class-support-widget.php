<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Blog Tutor Support Widget.
 *
 * @package  Blog_Tutor_Support/Frontend
 * @category Widget
 * @author   Fernando Acosta
 */
class Blog_Tutor_Support_Widget {
	/**
	 * Initialize the widget.
	 */
	public function __construct() {
		if ( current_user_can( 'editor' ) || current_user_can( 'administrator' ) ) {
			add_action( 'wp_footer', array( $this, 'widget' ), 50 );
			add_action( 'admin_footer', array( $this, 'widget' ), 50 );
		}
	}

	public function widget() {
		$options = get_option( 'blog_tutor_support_settings', array() );

			// Load Support Hero Widget.
		echo '<script async data-cfasync="false" src="https://d29l98y0pmei9d.cloudfront.net/js/widget.min.js?k=Y2xpZW50SWQ9MTk0OSZob3N0TmFtZT1ibG9ndHV0b3Iuc3VwcG9ydGhlcm8uaW8="></script>';

		if ( is_user_logged_in() ) {
			$ajw_current_user = wp_get_current_user();
      ?>
			<script type="text/javascript">
			jQuery( window ).load( function( $ ) {
				if ( window.supportHeroWidget != undefined ) {
					var properties = {
						articleParameters: {  // These can be embedded in articles with {variable}
							username: '<?php echo $ajw_current_user->user_login; ?>',
							firstname: '<?php echo $ajw_current_user->user_firstname; ?>',
							lastname: '<?php echo $ajw_current_user->user_lastname; ?>',
							displayname: '<?php echo $ajw_current_user->display_name; ?>',
							useremail: '<?php echo $ajw_current_user->user_email; ?>',
							site_url: '<?php echo site_url(); ?>',
							sitename: '<?php echo get_bloginfo( 'name' ); ?>',
							currentpage: window.location.href
							},
							custom: {  // These are used to prefill the contact form
								customerId: 1234,
								userEmail: '<?php echo $ajw_current_user->user_email; ?>',
								name: '<?php echo $ajw_current_user->user_firstname . ' ' . $ajw_current_user->user_lastname; ?>'
							}
						};
						window.supportHeroWidget.track( properties );

					<?php
          if ( ! is_admin() ) { // Hide help tab on front end; still loading widget code so help link in menu still works.
          ?>
						window.supportHeroWidget.hideWidget();
					<?php
          }
          ?>
				}
			});
			</script>

			<?php
      if ( ! is_admin() ) {
        ?>
				<style type="text/css">
				#supporthero-button { display: none !important; }
				</style>
        <?php
        }
        ?>
			<?php
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

new Blog_Tutor_Support_Widget();
