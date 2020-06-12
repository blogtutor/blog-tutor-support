<?php
/**
 * Settings page view.
 *
 * @package Support_Hero/Admin/View
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<?php
	$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'nerdpress_settings';
	?>

	<h2 class="nav-tab-wrapper">
		<a href="?page=nerdpress-support&tab=nerdpress_settings" class="nav-tab <?php echo 'nerdpress_settings' == $active_tab ? 'nav-tab-active' : ''; ?>">NerdPress Settings</a>
		<a href="?page=nerdpress-support&tab=server_information" class="nav-tab <?php echo 'server_information' == $active_tab ? 'nav-tab-active' : ''; ?>">Server Information</a>
		<?php 
		$bt_opts = get_option( 'blog_tutor_support_settings', array() );
		if( isset( $bt_opts['firewall_choice'] ) && $bt_opts['firewall_choice'] == 'sucuri' ) {
		?>
			<a href="?page=nerdpress-support&tab=sucuri_settings" class="nav-tab <?php echo 'sucuri_settings' == $active_tab ? 'nav-tab-active' : ''; ?>">Sucuri Settings</a>
    <?php } ?>
	</h2>

	<?php
	if ( 'nerdpress_settings' == $active_tab ) {
		?>
	<form method="post" action="options.php">
		<?php
			settings_fields( 'blog_tutor_support_settings' );
			do_settings_sections( 'blog_tutor_support_settings' );
			if ( isset( $bt_opts['firewall_choice'] ) && $bt_opts['firewall_choice'] == 'cloudflare' && isset( $bt_opts['cloudflare_zone'] ) && ! isset( $bt_opts['cloudflare_token'] ) ) {
				echo '<h3>Cloudflare API Token not set! 👆</h3>';
				echo '<h4>If using the Cloudflare Enterprice option for Firewall, the API Token must be set.</h4>';
				echo '<h4>Select other Firewall option or add API Token.</h4>';
				submit_button( __( 'Break Settings?', 'textdomain' ), 'delete' );
			} else {
				submit_button();
			}
		?>
	</form>

		<?php
	} elseif ( 'server_information' == $active_tab ) {
		settings_fields( 'blog_tutor_server_information' );
		do_settings_sections( 'blog_tutor_server_information' );
	} elseif ( 'sucuri_settings' == $active_tab ) {
		settings_fields( 'blog_tutor_sucuri_settings' );
		do_settings_sections( 'blog_tutor_sucuri_settings' );
	}
	?>

</div>
<?php
