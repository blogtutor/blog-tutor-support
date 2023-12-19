<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_register_script(
	'disable_cfopt_js',
	esc_url( NerdPress::$plugin_dir_url . 'includes/admin/js/np-disablecfopt.js' ),
	array(),
	BT_PLUGIN_VERSION
);

wp_enqueue_script( 'disable_cfopt_js' );

?>

<input type="radio" name="<?php echo esc_attr( $menu ); ?>[<?php echo esc_attr( $id ); ?>]" value="dns1"
    <?php if ( $zone === 'dns1' ) echo 'checked'; ?> >dns1.nerdpress.net<br />
<input type="radio" name="<?php echo esc_attr( $menu ); ?>[<?php echo esc_attr( $id ); ?>]" value="dns2"
    <?php if ( $zone === 'dns2' ) echo 'checked'; ?> >dns2.nerdpress.net<br />
<input type="radio" name="<?php echo esc_attr( $menu ); ?>[<?php echo esc_attr( $id ); ?>]" value="dns3"
    <?php if ( $zone === 'dns3' ) echo 'checked'; ?> >dns3.nerdpress.net<br />