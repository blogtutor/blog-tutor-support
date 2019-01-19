<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * NerdPress Support Admin.
 *
 * @package  Blog_Tutor_Support/Admin
 * @category Admin
 * @author   Fernando Acosta
 */
class Blog_Tutor_Support_Admin {

	/**
	 * Initialize the settings.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'settings_menu' ), 59 );
		add_action( 'admin_init', array( $this, 'plugin_settings' ) );
	}

	public static function is_nerdpress() {
		$current_user = wp_get_current_user();
		if ( current_user_can( 'administrator' ) && ( strpos( $current_user->user_email, '@blogtutor.com' ) !== false || strpos( $current_user->user_email, '@nerdpress.net' ) !== false ) ) {
		 return true;
	 }	else {
		 return false;
	 }
	}

	/**
	 * Add the settings page.
	 */
	public function settings_menu() {
		if ( $this->is_nerdpress() ) {
			add_action( 'admin_notices', array( $this, 'blog_tutor_support_message' ), 59 );
			add_options_page(
				'NerdPress Support',
				'NerdPress Support',
				'manage_options',
				'nerdpress-support',
				array( $this, 'html_settings_page' )
			);
		}
	}

	public function blog_tutor_support_message() {
		$option = get_option('blog_tutor_support_settings');
		if ( ! empty( $option['admin_notice'] ) ) {
			?>
			<div class="notice notice-success">
				<p>NerdPress Support Notice: <strong><?php esc_html_e( $option['admin_notice'] ); ?></strong></p>
			</div>
			<?php
		}
	}

	/**
	 * Render the settings page for this plugin.
	 */
	public function html_settings_page() {
		include dirname( __FILE__ ) . '/views/html-settings-page.php';
	}

	/**
	 * Plugin settings form fields.
	 */
	public function plugin_settings() {
		$option = 'blog_tutor_support_settings';

		// Set Custom Fields cection.
		add_settings_section(
			'options_section',
			__( 'NerdPress Support Section', 'nerdpress-support' ),
			array( $this, 'section_options_callback' ),
			$option
		);

		//   'test_mode',
		// add_settings_field(
		//   __( 'Test mode', 'nerdpress-support' ),
		//   array( $this, 'checkbox_element_callback' ),
		//   $option,
		//   'options_section',
		//   array(
		//     'menu'  => $option,
		//     'id'    => 'test_mode',
		//     'label' => __( 'If checked show the widget to admins only.', 'nerdpress-support' ),
		//   )
		// );

// Add admin notice text area
		add_settings_field(
			'admin_notice',
			__( 'NerdPress Support Notice', 'nerdpress-support' ),
			array( $this, 'textarea_element_callback' ),
			$option,
			'options_section',
			array(
				'menu'        => $option,
				'id'          => 'admin_notice',
				'description' => __( 'Enter notice that will show for NerdPress admins only.', 'nerdpress-support' ),
			)
		);

// Add option to hide "Need Help?" tab in dashboard.
		 add_settings_field(
			 'hide_tab',
		   __( 'Hide Help Tab?', 'nerdpress-support' ),
		   array( $this, 'checkbox_element_callback' ),
		   $option,
		   'options_section',
		   array(
		     'menu'  => $option,
		     'id'    => 'hide_tab',
		     'label' => __( 'Hides the "Need Help?" tab in the bottom of the dashboard.', 'nerdpress-support' ),
		   )
		 );

		// add_settings_field(
		//   'identify_users',
		//   __( 'Identify Users', 'nerdpress-support' ),
		//   array( $this, 'checkbox_element_callback' ),
		//   $option,
		//   'options_section',
		//   array(
		//     'menu'  => $option,
		//     'id'    => 'identify_users',
		//     'label' => __( 'If checked Blog Tutor Support widget will identify the user ID, email and display name from logged users.', 'nerdpress-support' ),
		//   )
		// );

		add_settings_field(
		  'server_info',
		  __( 'Server Stats', 'nerdpress-support' ),
		  array( $this, 'server_info_element_callback' ),
		  $option,
		  'options_section',
		  array(
		    'menu'  => $option,
		    'id'    => 'server_info',
		    'label' => __( 'Showing sever stats and variables.', 'nerdpress-support' ),
		  )
		);

		// Register settings.
		register_setting( $option, $option, array( $this, 'validate_options' ) );
	}

	/**
	 * Section null fallback.
	 */
	public function section_options_callback() {}

	/**
	 * Checkbox element fallback.
	 *
	 * @param array $args Callback arguments.
	 */
	public function checkbox_element_callback( $args ) {
		$menu    = $args['menu'];
		$id      = $args['id'];
		$options = get_option( $menu );

		if ( isset( $options[ $id ] ) ) {
			$current = $options[ $id ];
		} else {
			$current = isset( $args['default'] ) ? $args['default'] : '0';
		}

		include dirname( __FILE__ ) . '/views/html-checkbox-field.php';
	}

	/**
	 * Textarea element fallback.
	 *
	 * @param array $args Callback arguments.
	 */
	public function textarea_element_callback( $args ) {
		$menu    = $args['menu'];
		$id      = $args['id'];
		$options = get_option( $menu );

		if ( isset( $options[ $id ] ) ) {
			$value = $options[ $id ];
		} else {
			$value = isset( $args['default'] ) ? $args['default'] : '';
		}

		include dirname( __FILE__ ) . '/views/html-textarea-field.php';
	}

	/**
	 * Serverinfo element fallback.
	 *
	 * @param array $args Callback arguments.
	 */
	public function server_info_element_callback( $args ) {
		$menu    = $args['menu'];
		$id      = $args['id'];
		$options = get_option( $menu );

		if ( isset( $options[ $id ] ) ) {
			$value = $options[ $id ];
		} else {
			$value = isset( $args['default'] ) ? $args['default'] : '';
		}

		include dirname( __FILE__ ) . '/views/html-serverinfo-field.php';
	}

	/**
	 * Valid options.
	 *
	 * @param  array $input options to valid.
	 *
	 * @return array        validated options.
	 */
	public function validate_options( $input ) {
		$output = array();

		// Loop through each of the incoming options.
		foreach ( $input as $key => $value ) {
			// Check to see if the current option has a value. If so, process it.
			if ( isset( $input[ $key ] ) && ! empty( $input[ $key ] ) ) {
				$output[ $key ] = $input[ $key ];
			}
		}

		return $output;
	}

	/**
	 * Disk information.
	 *
	 * @return array disk information.
	 */
	public static function get_disk_info() {
		// Credit to: http://www.thecave.info/display-disk-free-space-percentage-in-php/
		/* Get disk space free (in bytes). */
		$disk_free = disk_free_space( __FILE__ );
		/* And get disk space total (in bytes).  */
		$disk_total = disk_total_space( __FILE__ );
		/* Now we calculate the disk space used (in bytes). */
		$disk_used = $disk_total - $disk_free;
		/* Percentage of disk used - this will be used to also set the width % of the progress bar. */
		$disk_percentage = sprintf( '%.2f', ( $disk_used / $disk_total ) * 100 );
		$disk_info  = array();
		$disk_info['disk_total']       = $disk_total;
		$disk_info['disk_used']        = $disk_used ;
		$disk_info['disk_free']        = $disk_free ;
		$disk_info['disk_percentage']  = $disk_percentage ;

		return $disk_info;
	}

	/**
	 * Format the argument from bytes to MB, GB, etc.
	 *
	 * @param array bytes size.
	 *
	 * @return array size from bytes to larger ammount.
	 *
	 */
	public static function format_size( $bytes ) {
		$types = array( 'B', 'KB', 'MB', 'GB', 'TB' );
		for ( $i = 0; $bytes >= 1000 && $i < ( count( $types ) - 1 ); $bytes /= 1024, $i++ );
		return ( round( $bytes, 2 ) . ' ' . $types[ $i ] );
	}
}

new Blog_Tutor_Support_Admin();
