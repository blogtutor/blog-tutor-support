<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sucuri_api_call_array = Blog_Tutor_Support_Helpers::get_sucuri_api_call();

if ( is_array( $sucuri_api_call_array ) ) {
	?>
	<form method="get" class="form-loader" target="_blank" action="<?php echo htmlspecialchars( $sucuri_api_call_array['address'] ); ?>">
		<div class="input-group">
			<input type="hidden" id="k" name="k" value="<?php echo $sucuri_api_call_array['api_key']; ?>">
			<input type="hidden" id="s" name="s" value="<?php echo $sucuri_api_call_array['api_secret']; ?>">
			<input type="hidden" id="a" name="a" value="whitelist">
			<input type="text" id="ip" name="ip" placeholder="Add new IP...">
			<input type="submit" value="Whitelist">
		</div>
	</form>
	<?php
} else {
	echo '<h2> Cloudproxy API Key is not found. </h2>';
}
