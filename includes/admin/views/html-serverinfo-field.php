<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get Memory usage info from /proc/meminfo.
function get_system_mem_info() {
	$data = explode( "\n", trim( file_get_contents( '/proc/meminfo' ) ) );
	$mem_info = array();
	foreach ( $data as $line ) {
		list( $key, $val ) = explode( ':', $line );
		$mem_info[ $key ]  = trim( $val );
	}
	return $mem_info;
}

function string_to_bytes( $string ) {
	return $string = preg_replace( '~\D~', '', $string ) * 1024;
}

$mem_show = array_filter( @get_system_mem_info() );
if ( $mem_show ) {
	$mem_info             = get_system_mem_info();
	$mem_total            = Blog_Tutor_Support_Helpers::format_size( string_to_bytes( $mem_info['MemTotal'] ) );
	$mem_available        = Blog_Tutor_Support_Helpers::format_size( string_to_bytes( $mem_info['MemAvailable'] ) );
	$mem_used_unformatted = string_to_bytes( $mem_info['MemTotal'] ) - string_to_bytes( $mem_info['MemAvailable'] );
	$mem_used             = Blog_Tutor_Support_Helpers::format_size( $mem_used_unformatted );
	$mem_percentage       = sprintf( '%.2f', ( $mem_used_unformatted / string_to_bytes( $mem_info['MemTotal'] ) ) * 100 );

	if ( $mem_percentage > 90 ) {
		$prgtext_danger = 'prgtext-danger';
		$prgbar_danger  = 'prgbar-danger';
	} else {
		$prgtext_danger = '';
		$prgbar_danger  = '';
	}
}
?>

<link rel="stylesheet" href="<?php echo plugins_url(); ?>/blog-tutor-support/includes/css/html-serverinfo-field-style.css" type="text/css" media="all">

<?php
$disk_total = Blog_Tutor_Support_Helpers::format_size( Blog_Tutor_Support_Helpers::get_disk_info()['disk_total'] );
$disk_used  = Blog_Tutor_Support_Helpers::format_size( Blog_Tutor_Support_Helpers::get_disk_info()['disk_used'] );
$disk_free  = Blog_Tutor_Support_Helpers::format_size( Blog_Tutor_Support_Helpers::get_disk_info()['disk_free'] );

$loads = sys_getloadavg();
if ( $loads ) {
	?>
	<article>
		<h2 style="margin-top: 0;">Load Averages: &nbsp; <?php echo $loads[0] . ' &nbsp; ' . $loads[1] . ' &nbsp; ' . $loads[2]; ?></h2>
	</article>
<?php } ?>

<article>
	<h2>Disk Space:</h2>
	<p>Total: <?php echo $disk_total; ?></p>
	<div class='progress'>
		<div class='prgtext <?php if ( Blog_Tutor_Support_Helpers::get_disk_info()['disk_percentage']>90 )echo 'prgtext-danger'; ?>'><?php echo Blog_Tutor_Support_Helpers::get_disk_info()['disk_percentage']; ?>% Used</div>
		<div class='prgbar-disk <?php if ( Blog_Tutor_Support_Helpers::get_disk_info()['disk_percentage']>90 )echo 'prgbar-danger'; ?>' style="width: <?php echo Blog_Tutor_Support_Helpers::get_disk_info()['disk_percentage']; ?>%;"></div>
		<div class='prginfo'>
			<span style='float: left;'><?php echo $disk_used . ' used'; ?></span>
			<span style='float: right;'><?php echo $disk_free . ' free'; ?></span>
			<span style='clear: both;'></span>
		</div>
	</div>
</article>
<br>

<?php if ( $mem_show ) { ?>
	<article>
		<h2>Memory Usage:</h2>
		<p>Total: <?php echo $mem_total; ?></p>
		<div class='progress'>
			<div class='prgtext <?php echo $prgtext_danger; ?>'><?php echo $mem_percentage; ?>% Used</div>
			<div class='prgbar-mem <?php echo $prgbar_danger; ?>' style='width: <?php echo $mem_percentage; ?>%;'></div>
			<div class='prginfo'>
				<span style='float: left;'><?php echo "$mem_used used"; ?></span>
				<span style='float: right;'><?php echo "$mem_available free"; ?></span>
				<span style='clear: both;'></span>
			</div>
		</div>
	</article>
	<br>
	<br>
<?php } ?>

<article>
	<h2>Server Information:</h2>
	<p><strong>PHP VERSION:</strong> <?php echo phpversion(); ?></p>
	<p><strong>SERVER_SOFTWARE:</strong> <?php echo $_SERVER['SERVER_SOFTWARE']; ?></p>
	<p><strong>SERVER_PROTOCOL:</strong> <?php echo $_SERVER['SERVER_PROTOCOL']; ?></p>
	<p><strong>DOCUMENT_ROOT:</strong> <?php echo $_SERVER['DOCUMENT_ROOT']; ?></p>
	<p><strong>REMOTE_ADDR:</strong> <?php echo $_SERVER['REMOTE_ADDR']; ?></p>
	<p><strong>HTTP_UPGRADE_INSECURE_REQUESTS:</strong> <?php echo $_SERVER['HTTP_UPGRADE_INSECURE_REQUESTS']; ?></p>
	<P><strong>HTTP_CLIENT_IP:</strong> <?php if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) { echo $_SERVER['HTTP_CLIENT_IP']; } else { echo '(not set)'; } ?></p>
	<p><strong>HTTP_X_SUCURI_CLIENTIP:</strong> <?php if ( !empty( $_SERVER['HTTP_X_SUCURI_CLIENTIP'] ) ) { echo $_SERVER['HTTP_X_SUCURI_CLIENTIP']; } ?></p>
	<p><strong>HTTP_X_FORWARDED_FOR:</strong> <?php echo $_SERVER['HTTP_X_FORWARDED_FOR']; ?></p>
	<p><strong>HTTP_X_REAL_IP:</strong> <?php echo $_SERVER['HTTP_X_REAL_IP']; ?></p>
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
