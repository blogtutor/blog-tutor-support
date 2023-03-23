<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get Memory usage info from /proc/meminfo.
function get_system_mem_info() {
	$data     = explode( PHP_EOL, file_get_contents( '/proc/meminfo' ) );
	$mem_info = array();
	foreach ( $data as $line ) {
		list( $key, $val ) = explode( ':', $line );
		$mem_info[ $key ]  = trim( $val ?? '' );
	}
	return $mem_info;
}

function string_to_bytes( $string ) {
	return $string = intval( preg_replace( '~\D~', '', $string ) ) * 1024;
}

?>

<link rel="stylesheet" href="<?php echo NerdPress_Plugin::$plugin_dir_url . 'includes/css/html-serverinfo-field-style.css'; ?>" type="text/css" media="all">

<?php
$disk_total = NerdPress_Helpers::format_size( NerdPress_Helpers::get_disk_info()['disk_total'] );
$disk_used  = NerdPress_Helpers::format_size( NerdPress_Helpers::get_disk_info()['disk_used'] );
$disk_free  = NerdPress_Helpers::format_size( NerdPress_Helpers::get_disk_info()['disk_free'] );

if ( function_exists( 'sys_getloadavg' ) ) {
	$loads = sys_getloadavg();
	if ( $loads ) {
		?>
		<article>
			<h2 style="margin-top: 0;">Load Averages: &nbsp; <?php echo esc_html( $loads[0] ) . ' &nbsp; ' . esc_html( $loads[1] ) . ' &nbsp; ' . esc_html( $loads[2] ); ?></h2>
		</article>
		<?php
	}
}

?>

<article>
	<h2>Disk Space:</h2>
	<p>Total: <?php echo esc_html( $disk_total ); ?></p>
	<div class='progress'>
		<div class='prgtext <?php if ( NerdPress_Helpers::get_disk_info()['disk_percentage'] > 90 ) echo 'prgtext-danger'; ?>'><?php echo esc_html( NerdPress_Helpers::get_disk_info()['disk_percentage'] ); ?>% Used</div>
		<div class='prgbar-disk <?php if ( NerdPress_Helpers::get_disk_info()['disk_percentage'] > 90 ) echo 'prgbar-danger'; ?>' style="width: <?php echo esc_html( NerdPress_Helpers::get_disk_info()['disk_percentage'] ); ?>%;"></div>
		<div class='prginfo'>
			<span style='float: left;'><?php echo esc_html( $disk_used ) . ' used'; ?></span>
			<span style='float: right;'><?php echo esc_html( $disk_free ) . ' free'; ?></span>
			<span style='clear: both;'></span>
		</div>
	</div>
</article>
<br>

<?php
$mem_info = array_filter( @get_system_mem_info() );

if ( array_key_exists( 'MemTotal', $mem_info ) ) {
	$mem_total            = NerdPress_Helpers::format_size( string_to_bytes( $mem_info['MemTotal'] ) );
	$mem_available        = NerdPress_Helpers::format_size( string_to_bytes( $mem_info['MemAvailable'] ) );
	$mem_used_unformatted = string_to_bytes( $mem_info['MemTotal'] ) - string_to_bytes( $mem_info['MemAvailable'] );
	$mem_used             = NerdPress_Helpers::format_size( $mem_used_unformatted );
	$mem_percentage       = sprintf( '%.2f', ( $mem_used_unformatted / string_to_bytes( $mem_info['MemTotal'] ) ) * 100 );

	if ( $mem_percentage > 90 ) {
		$prgtext_danger = 'prgtext-danger';
		$prgbar_danger  = 'prgbar-danger';
	} else {
		$prgtext_danger = '';
		$prgbar_danger  = '';
	}
?>
	<article>
		<h2>Memory Usage:</h2>
		<p>Total: <?php echo esc_html( $mem_total ); ?></p>
		<div class='progress'>
			<div class='prgtext <?php echo esc_html( $prgtext_danger ); ?>'><?php echo esc_html( $mem_percentage ); ?>% Used</div>
			<div class='prgbar-mem <?php echo esc_html( $prgbar_danger ); ?>' style='width: <?php echo esc_html( $mem_percentage ); ?>%;'></div>
			<div class='prginfo'>
				<span style='float: left;'><?php echo esc_html( $mem_used ); ?> used</span>
				<span style='float: right;'><?php echo esc_html( $mem_available ); ?> free</span>
				<span style='clear: both;'></span>
			</div>
		</div>
	</article>
	<br>
	<br>
<?php } ?>

<article>
	<h2>Server Information:</h2>
	<p><strong>PHP VERSION:</strong> <?php echo esc_html( phpversion() ); ?></p>
	<p><strong>SERVER_SOFTWARE:</strong> <?php if ( ! empty( $_SERVER['SERVER_SOFTWARE'] ) ) { echo esc_html( $_SERVER['SERVER_SOFTWARE'] ); } ?></p>
	<p><strong>SERVER_PROTOCOL:</strong> <?php if ( ! empty( $_SERVER['SERVER_PROTOCOL'] ) ) { echo esc_html( $_SERVER['SERVER_PROTOCOL'] ); } ?></p>
	<p><strong>DOCUMENT_ROOT:</strong> <?php if ( ! empty( $_SERVER['DOCUMENT_ROOT'] ) ) { echo esc_html( $_SERVER['DOCUMENT_ROOT'] ); } ?></p>
	<p><strong>REMOTE_ADDR:</strong> <?php if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) { echo esc_html( $_SERVER['REMOTE_ADDR'] ); } ?></p>
	<p><strong>HTTP_UPGRADE_INSECURE_REQUESTS:</strong> <?php if ( ! empty( $_SERVER['HTTP_UPGRADE_INSECURE_REQUESTS'] ) ) { echo esc_html( $_SERVER['HTTP_UPGRADE_INSECURE_REQUESTS'] ); } ?></p>
	<P><strong>HTTP_CLIENT_IP:</strong> <?php if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) { echo esc_html( $_SERVER['HTTP_CLIENT_IP'] ); } else { echo '(not set)'; } ?></p>
	<p><strong>HTTP_X_SUCURI_CLIENTIP:</strong> <?php if ( ! empty( $_SERVER['HTTP_X_SUCURI_CLIENTIP'] ) ) { echo esc_html( $_SERVER['HTTP_X_SUCURI_CLIENTIP'] ); } ?></p>
	<p><strong>HTTP_X_FORWARDED_FOR:</strong> <?php if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) { echo esc_html( $_SERVER['HTTP_X_FORWARDED_FOR'] ); } ?></p>
	<p><strong>HTTP_X_REAL_IP:</strong> <?php if ( ! empty( $_SERVER['HTTP_X_REAL_IP'] ) ) { echo esc_html( $_SERVER['HTTP_X_REAL_IP'] ); } ?></p>
	<p><strong>SERVER_ADDR:</strong> <?php if ( ! empty( $_SERVER['SERVER_ADDR'] ) ) { echo esc_html( $_SERVER['SERVER_ADDR'] ); } ?></p>
</article>

<br>
<article>
	<details>
		<summary>All Server Information:</summary>
		<pre style="width: 70vw ; white-space: pre-line;">
			<?php print_r( $_SERVER ); ?>
		</pre>
	</details>
</article>

<?php
