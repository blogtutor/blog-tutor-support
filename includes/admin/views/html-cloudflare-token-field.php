<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$options = get_option( 'blog_tutor_support_settings' );

if ( isset( $options['cloudflare_token'] ) ) {
	$placeholder = $options['cloudflare_token'];
} else {
	$placeholder = '';
}

$maybe_disable = '';

if ( ! NerdPress_Helpers::is_cloudflare_firewall_selected() ) {
  $maybe_disable = 'style="opacity:0.5; pointer-events:none;" ';
}
?>

<input type="text" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $menu ) ?>[<?php echo esc_attr( $id ); ?>]" value="<?php echo esc_attr( $placeholder ); ?>" <?php echo $maybe_disable; ?> placeholder="<?php echo esc_attr( $placeholder ); ?>">
