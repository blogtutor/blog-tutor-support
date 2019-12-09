<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sucuri_api_call_array = Blog_Tutor_Support_Helpers::get_sucuri_api_call();

if ( is_array( $sucuri_api_call_array ) ) {
	?>
	<h2>Whitelist IP:</h2>
	<p>Whitelists defined IP address so it won't be blocked by some of Sucuri's Firewall security rules.</p>
	<form method="get" class="form-loader" target="_blank" action="<?php echo htmlspecialchars( $sucuri_api_call_array['address'] ); ?>">
		<div class="input-group">
			<input type="hidden" id="k" name="k" value="<?php echo $sucuri_api_call_array['api_key']; ?>">
			<input type="hidden" id="s" name="s" value="<?php echo $sucuri_api_call_array['api_secret']; ?>">
			<input type="hidden" id="a" name="a" value="whitelist">
			<input type="text" id="ip" name="ip" placeholder="Add new IP...">
			<input type="submit" value="Whitelist">
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
} else {
	echo '<h2> Sucuri Firewall API Key is not found. </h2>';
}
