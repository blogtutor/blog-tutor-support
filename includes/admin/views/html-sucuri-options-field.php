<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$allowlist_ips = get_option( 'cloudproxy_allowlist_ips', array() );

if ( count( $allowlist_ips ) > 0 ) {
	wp_register_script(
		'clear_allowlist_js',
		esc_url( NerdPress::$plugin_dir_url . 'includes/admin/js/bt-clearallowlist.js' ),
		array(),
		BT_PLUGIN_VERSION
	);
	wp_localize_script(
		'clear_allowlist_js',
		'clear_allowlist',
		array(
			'endpoint' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'clear_allowlist_secure_me' ),
		)
	);
	wp_enqueue_script( 'clear_allowlist_js' );

	?><h3>Automatically Added  IPs to the Allowlist</h3>
	<?php
	foreach ( $allowlist_ips as $ip ) {
		?>
		<p> <?php echo esc_html( $ip ); ?></p>
		<?php
	}
	?>
	<input id="clearallowlist" type="submit" value="Clear Local Allowlist Cache">
	<?php
}
