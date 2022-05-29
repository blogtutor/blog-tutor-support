<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<input type="radio" name="<?php echo esc_attr( $menu ) ?>[<?php echo esc_attr( $id ); ?>]" value="dns1" class="<?php echo NerdPress_Helpers::$maybe_disable_cloudflare_settings; ?> " <?php if( $zone === 'dns1' ) echo 'checked'; ?>>dns1.nerdpress.net<br />
<input type="radio" name="<?php echo esc_attr( $menu ) ?>[<?php echo esc_attr( $id ); ?>]" value="dns2" class="<?php echo NerdPress_Helpers::$maybe_disable_cloudflare_settings; ?> " <?php if( $zone === 'dns2' ) echo 'checked'; ?>>dns2.nerdpress.net<br />
<input type="radio" name="<?php echo esc_attr( $menu ) ?>[<?php echo esc_attr( $id ); ?>]" value="dns3" class="<?php echo NerdPress_Helpers::$maybe_disable_cloudflare_settings; ?> " <?php if( $zone === 'dns3' ) echo 'checked'; ?>>dns3.nerdpress.net<br />
