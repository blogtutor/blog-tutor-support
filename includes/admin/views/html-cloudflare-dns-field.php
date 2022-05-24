<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$maybe_disable = '';

if ( ! NerdPress_Helpers::is_cloudflare_firewall_selected() ) {
  $maybe_disable = 'style="opacity:0.5; pointer-events:none;" ';
}
?>

<input type="radio" name="<?php echo esc_attr( $menu ) ?>[<?php echo esc_attr( $id ); ?>]" value="dns1" <?php echo $maybe_disable; if( $zone === 'dns1' ) echo 'checked'; ?>>dns1.nerdpress.net<br />
<input type="radio" name="<?php echo esc_attr( $menu ) ?>[<?php echo esc_attr( $id ); ?>]" value="dns2" <?php echo $maybe_disable; if( $zone === 'dns2' ) echo 'checked'; ?>>dns2.nerdpress.net<br />
<input type="radio" name="<?php echo esc_attr( $menu ) ?>[<?php echo esc_attr( $id ); ?>]" value="dns3" <?php echo $maybe_disable; if( $zone === 'dns3' ) echo 'checked'; ?>>dns3.nerdpress.net<br />
