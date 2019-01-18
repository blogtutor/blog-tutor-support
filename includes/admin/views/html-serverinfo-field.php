<?php
// credit to: http://www.thecave.info/display-disk-free-space-percentage-in-php/
/* get disk space free (in bytes) */
$disk_free = disk_free_space( __FILE__ );
/* and get disk space total (in bytes)  */
$disk_total = disk_total_space( __FILE__ );
/* now we calculate the disk space used (in bytes) */
$disk_used = $disk_total - $disk_free;
/* percentage of disk used - this will be used to also set the width % of the progress bar */
$disk_percentage = sprintf( '%.2f', ( $disk_used / $disk_total ) * 100 );
/* and we format the size from bytes to MB, GB, etc. */
$disk_free  = format_size( $disk_free );
$disk_used  = format_size( $disk_used );
$disk_total = format_size( $disk_total );
function format_size( $bytes ) {
	$types = array( 'B', 'KB', 'MB', 'GB', 'TB' );
	for ( $i = 0; $bytes >= 1000 && $i < ( count( $types ) - 1 ); $bytes /= 1024, $i++ );
	return ( round( $bytes, 2 ) . ' ' . $types[ $i ] );
}

// Get Memory usage info from /proc/meminfo.
function get_system_mem_info() {
	$data    = explode( "\n", trim( file_get_contents( '/proc/meminfo' ) ) );
	$meminfo = array();
	foreach ( $data as $line ) {
		list( $key, $val ) = explode( ':', $line );
		$meminfo[ $key ]   = trim( $val );
	}
	return $meminfo;
}

function string_to_bytes( $string ) {
	return $string = preg_replace( '~\D~', '', $string ) * 1024;
}

$mem_info             = get_system_mem_info();
$mem_total            = format_size( string_to_bytes( $mem_info['MemTotal'] ) );
$mem_available        = format_size( string_to_bytes( $mem_info['MemAvailable'] ) );
$mem_used_unformatted = string_to_bytes( $mem_info['MemTotal'] ) - string_to_bytes( $mem_info['MemAvailable'] );
$mem_used             = format_size( $mem_used_unformatted );
$mem_percentage       = sprintf( '%.2f', ( $mem_used_unformatted / string_to_bytes( $mem_info['MemTotal'] ) ) * 100 );

if ( $mem_percentage > 90 ) {
	$prgtext_danger = 'prgtext-danger';
	$prgbar_danger  = 'prgbar-danger';
} else {
	$prgtext_danger = '';
	$prgbar_danger  = '';
}
?>

<link rel="stylesheet" href="<?php echo plugins_url( 'css/html-serverinfo-field-style.css', dirname( __FILE__, 2 ) ); ?>" type="text/css" media="all">
<article>
	<h2>Disc Space:</h2>
	<p>Total: <?php echo $disk_total; ?></p>
	<div class='progress'>
		<div class='prgtext <?php if ( $disk_percentage>90 )echo 'prgtext-danger'; ?>'><?php echo $disk_percentage; ?>% Used</div>
		<div class='prgbar-disk <?php if ( $disk_percentage>90 )echo 'prgbar-danger'; ?>' style='width: <?php echo $disk_percentage; ?>%;'></div>
		<div class='prginfo'>
			<span style='float: left;'><?php echo "$disk_used used"; ?></span>
			<span style='float: right;'><?php echo "$disk_free free"; ?></span>
			<span style='clear: both;'></span>
		</div>
	</div>
</article>
<br>
<br>

<?php
$loads = sys_getloadavg();
?>
<article>
	<h2>CPU Load Average:</h2>
	<p>1 Minute: <?php echo $loads[0]; ?></p>
	<p>5 Minutes: <?php echo $loads[1]; ?></p>
	<p>15 Minutes: <?php echo $loads[2]; ?></p>
</article>
<br>
<br>

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

<article>
	<h2>Server Information:</h2>
	<p>SERVER_SOFTWARE:</p>
	<p> <?php echo $_SERVER['SERVER_SOFTWARE']; ?></p>
	<br>
	<p>SERVER_PROTOCOL:</p>
	<p> <?php echo $_SERVER['SERVER_PROTOCOL']; ?></p>
	<br>
	<p>DOCUMENT_ROOT:</p>
	<p> <?php echo $_SERVER['DOCUMENT_ROOT']; ?></p>
	<br>
	<p>REMOTE_ADDR:</p>
	<p> <?php echo $_SERVER['REMOTE_ADDR']; ?></p>
	<br>
	<p>HTTP_X_REAL_IP:</p>
	<p> <?php echo $_SERVER['HTTP_X_REAL_IP']; ?></p>
	<br>
</article>
<?php
