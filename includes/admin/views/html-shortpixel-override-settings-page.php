<?php
/**
 * Settings page view.
 *
 * @package NerdPress/Admin/View
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$shortpixel_options = array(
	'wp-short-pixel-compression',
	'wp-short-pixel-resize-images',
	'wp-short-pixel-resize-width',
	'wp-short-pixel-resize-height',
	'wp-short-pixel-resize-type',
	'wp-short-process_thumbnails',
	'wp-short-backup_images',
);

foreach ( $shortpixel_options as $option ) {
	$shortpixel_options[ $option ] = get_option( $option );
}

if ( $shortpixel_options['wp-short-pixel-compression'] === '0' ) {
	$short_pixel_compression_level = 'LossLess';
} elseif ( $shortpixel_options['wp-short-pixel-compression'] === '1' ) {
	$short_pixel_compression_level = 'Lossy';
} elseif ( $shortpixel_options['wp-short-pixel-compression'] === '2' ) {
	$short_pixel_compression_level = 'Glossy';
}

if ( $shortpixel_options['wp-short-pixel-resize-images'] ) {
	$short_pixel_resize_images = 'are';
} else {
	$short_pixel_resize_images = 'are not';
}

if ( $shortpixel_options['wp-short-pixel-resize-type'] === 'outer' ) {
	$short_pixel_resize_type = ' Cover';
} elseif ( $shortpixel_options['wp-short-pixel-resize-type'] === 'inner' ) {
	$short_pixel_resize_type = ' Contain';
}

if ( $shortpixel_options['wp-short-process_thumbnails'] ) {
	$short_pixel_thumbnails = 'are';
} else {
	$short_pixel_thumbnails = 'are not';
}

if ( $shortpixel_options['wp-short-backup_images'] ) {
	$short_pixel_backups = 'are';
} else {
	$short_pixel_backups = 'are not';
}


?>

<div class="wrap">
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<p>The ShortPixel Settings are managed by NerdPress. If you have any questions, or need to make any changes, please contact us at <a href='mailto:support@nerdpress.net'>support@nerdpress.net</a>.</p>
	
	<p>Current compression setting is: <strong><?php echo $short_pixel_compression_level ?></strong>.</p>
	
  <p>Images <strong><?php echo $short_pixel_resize_images ?></strong> set to be resized, and the sizes are set to: <strong><?php echo $shortpixel_options['wp-short-pixel-resize-width'] ?></strong> x <strong><?php echo $shortpixel_options['wp-short-pixel-resize-height'] . $short_pixel_resize_type ?></strong>.</p>
	
	<p>Thumbnail images <strong><?php echo $short_pixel_thumbnails ?></strong> being optimized.</p>

	<p>Originals <strong><?php echo $short_pixel_backups ?></strong> being saved to the backups folder.</p>


</div>
<?php
