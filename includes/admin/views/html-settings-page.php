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

	<?php settings_errors(); ?>

	<form method="post" action="options.php">
		<?php
			settings_fields( 'blog_tutor_support_settings' );
			do_settings_sections( 'blog_tutor_support_settings' );
			submit_button();
		?>
	</form>
</div>

<?php
