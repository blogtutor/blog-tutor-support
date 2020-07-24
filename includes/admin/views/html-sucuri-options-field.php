<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<h2>Add IP to the allowlist:</h2>
<p>Adds defined IP address to the allowlist so it won't be blocked by some of Sucuri's Firewall security rules.</p>
<form method="get" class="form-loader" target="_blank" action="<?php echo htmlspecialchars( $sucuri_api_call_array['address'] ); ?>">
	<div class="input-group">
		<input type="hidden" id="k" name="k" value="<?php echo $sucuri_api_call_array['api_key']; ?>">
		<input type="hidden" id="s" name="s" value="<?php echo $sucuri_api_call_array['api_secret']; ?>">
		<input type="hidden" id="a" name="a" value="allowlist">
		<input type="text" id="ip" name="ip" placeholder="Add new IP...">
		<input type="submit" value="Allowlist">
	</div>
</form>
<h2>Clear Cache per File:</h2>
<p>This option can be used to remove a file from the Sucuri Firewall cache.</p>
<p>This will reflect live as soon as you click the clear cache button.</p>
<form method="get" class="form-loader" target="_blank" action="<?php echo htmlspecialchars( $sucuri_api_call_array['address'] ); ?>">
	<div class="input-group">
		<input type="hidden" id="k" name="k" value="<?php echo $sucuri_api_call_array['api_key']; ?>">
		<input type="hidden" id="s" name="s" value="<?php echo $sucuri_api_call_array['api_secret']; ?>">
		<input type="hidden" id="a" name="a" value="clearcache">
		<input type="text" id="file" name="file" placeholder="File Path...">
		<input type="submit" value="Clear File">
	</div>
</form>

<?php
$allowlist_ips = get_option( 'cloudproxy_allowlist_ips', array() );

if( count( $allowlist_ips ) > 0 ) {	
	wp_register_script( 'clear_allowlist_js', plugins_url( '../js/bt-clearallowlist.js', __FILE__ ), array(), BT_PLUGIN_VERSION );
	wp_localize_script( 'clear_allowlist_js', 'clear_allowlist', array(
		'endpoint' => admin_url( 'admin-ajax.php' ),
		'nonce'	   => wp_create_nonce( 'clear_allowlist_secure_me' ),
	));
	wp_enqueue_script( 'clear_allowlist_js' );

	?><h3>Automatically Added  IPs to the Allowlist</h3><?php
	foreach( $allowlist_ips as $ip ) {
		?> <p><?php echo $ip; ?></p> <?php
	}
	?>
	<input id="clearallowlist" type="submit" value="Clear Local Allowlist Cache">
	<?php
}
