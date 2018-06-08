<?php
/**
 * Textarea field view.
 *
 * @package Support_Hero/Admin/View
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<textarea style="width: 100%; max-width: 550px; height: 120px; resize: none;" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $menu ) ?>[<?php echo esc_attr( $id ); ?>]"><?php echo $value; ?></textarea>

<?php if ( isset( $args['description'] ) ) : ?>
	<p class="description"><?php echo esc_html( $args['description'] ); ?></p>
<?php endif;
