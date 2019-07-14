<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

	/**
	 * Blog_Tutor_Support helper class.
	 *
	 * @package  Blog_Tutor_Support
	 * @category Core
	 * @author  Nathan Tyler
	 */
class Blog_Tutor_Support_Cloudproxy {

	public function __construct() {
		add_action( 'wp_login', array( $this, 'queue_cloudproxy_ip_whitelist_for_current_user' ), 10, 2 );
		add_action( 'admin_footer', array( $this, 'maybe_fire_cloudproxy_ip_whitelist' ) );
	}

	public function queue_cloudproxy_ip_whitelist_for_current_user( $user_login, $user ) {
		if ( empty ( $user->ID ) ) {
			return; // something went wrong, but this isn't mission critical so we'll just abort
		}

		if ( ! user_can( $user->ID, 'manage_options' ) ) {
			return; // we only want to whitelist administrators
		}

		$user_ids_to_whitelist = get_option( 'nerdpress_user_ids_to_whitelist', array() );
		$user_ids_to_whitelist[$user->ID] = $user->ID;
		update_option( 'nerdpress_user_ids_to_whitelist', $user_ids_to_whitelist );
	}

	public function maybe_fire_cloudproxy_ip_whitelist() {
		$user_ids_to_whitelist = get_option( 'nerdpress_user_ids_to_whitelist', array() );
		if ( empty( $user_ids_to_whitelist[get_current_user_id()] ) ) {
			return;
		}
		unset( $user_ids_to_whitelist[get_current_user_id()] );
		if ( empty( $user_ids_to_whitelist ) ) {
			delete_option( 'nerdpress_user_ids_to_whitelist' );
		} else {
			update_option( 'nerdpress_user_ids_to_whitelist', $user_ids_to_whitelist );
		}

		// To follow every best practice, the following javascript would be in a separate file that's enqueued
		// since it's a single line, it's kept here for convenience and to have all these pieces in a single file
		// if it grows in scope, it might be better to break this out to a separate file and enqueue it properly
		?>
		<script type="text/javascript">
			jQuery.ajax( jQuery( '#wp-admin-bar-bt-whitelist-cloudproxy a' ).attr('href') );
		</script>
		<?php
	}

}
new Blog_Tutor_Support_Cloudproxy();
