<?php
/**
 * Radio Buttons view.
 *
 * @package Support_Hero/Admin/View
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<input type="radio" name="<?php echo esc_attr( $menu ) ?>[<?php echo esc_attr( $id ); ?>]" value="sucuri" <?php if( $firewall === 'sucuri' ) echo 'checked'; ?>>Sucuri Firewall<br />
<input type="radio" name="<?php echo esc_attr( $menu ) ?>[<?php echo esc_attr( $id ); ?>]" value="cloudflare" <?php if( $firewall === 'cloudflare' ) echo 'checked'; ?>>Cloudflare<br />
<input type="radio" name="<?php echo esc_attr( $menu ) ?>[<?php echo esc_attr( $id ); ?>]" value="none" <?php if( $firewall === 'none' ) echo 'checked'; ?>>None
