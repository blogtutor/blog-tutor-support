<?php
/**
 * Checkbox field view.
 *
 * @package Support_Hero/Admin/View
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $menu ) ?>[<?php echo esc_attr( $id ); ?>]" value="1" <?php checked( 1, $current, true ) ?> />
<?php if ( isset( $args['label'] ) ) : ?>
	<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $args['label'] ); ?></label>
<?php endif; ?>

