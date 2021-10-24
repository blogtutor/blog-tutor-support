<?php
/**
 * Checkbox field view.
 *
 * @package NerdPress/Admin/View
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $menu ) ?>[<?php echo esc_attr( $id ); ?>]" value="1" <?php checked( 1, $current, true ) ?> />
<?php if ( isset( $args['label'] ) ) : ?>
	<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $args['label'] ); ?></label>
<?php endif; ?>
<?php if ( isset( $args['description'] ) ) : ?>
  <p class="description"><?php echo esc_html( $args['description'] ); 
	if ( defined( 'WP_DEBUG' ) && constant( 'WP_DEBUG' ) ) {
		echo 'is set to true.';
	} else {
		echo 'is set to false.';
	}
  ?>
  </p>
<?php endif;
