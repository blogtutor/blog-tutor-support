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
		add_action( 'admin_init', array( $this, 'settings_tabs' ) );
	}

	/**
	 * Add the settings page.
	 */
	public function settings_menu() {
		if ( Blog_Tutor_Support_Helpers::is_nerdpress() ) {
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
		$option = get_option( 'blog_tutor_support_settings' );
		if ( ! empty( $option['admin_notice'] ) ) {
			$site_url = get_site_url();
			?>
			<div class="notice" style="border-left-color:#0F145B">
				<p><img src="<?php echo esc_url( $site_url ); ?>/wp-content/plugins/blog-tutor-support/includes/images/nerdpress-icon-250x250.png" style="max-width:45px;vertical-align:middle;">NerdPress Notes: <strong><?php esc_html_e( $option['admin_notice'] ); ?></strong></p>
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
	 * Add Plugin Settings Tabs.
	 */
	public function settings_tabs() {
		/**
		* Plugin settings form fields.
		*/
		$settings_option = 'blog_tutor_support_settings';

		// Set Custom Fields cection.
		add_settings_section(
			'options_section',
			__( 'NerdPress Support Section', 'nerdpress-support' ),
			array( $this, 'section_options_callback' ),
			$settings_option
		);

		//   'test_mode',
		// add_settings_field(
		//   __( 'Test mode', 'nerdpress-support' ),
		//   array( $this, 'checkbox_element_callback' ),
		//   $settings_option,
		//   'options_section',
		//   array(
		//     'menu'  => $settings_option,
		//     'id'    => 'test_mode',
		//     'label' => __( 'If checked show the widget to admins only.', 'nerdpress-support' ),
		//   )
		// );

		// Add admin notice text area
		add_settings_field(
			'admin_notice',
			__( 'NerdPress Support Notice', 'nerdpress-support' ),
			array( $this, 'textarea_element_callback' ),
			$settings_option,
			'options_section',
			array(
				'menu'        => $settings_option,
				'id'          => 'admin_notice',
				'description' => __( 'Enter notice that will show for NerdPress admins only.', 'nerdpress-support' ),
			)
		);

		// Add option to hide "Need Help?" tab in dashboard.
		add_settings_field(
			'hide_tab',
			__( 'Hide Help Tab?', 'nerdpress-support' ),
			array( $this, 'checkbox_element_callback' ),
			$settings_option,
			'options_section',
			array(
				'menu'  => $settings_option,
				'id'    => 'hide_tab',
				'label' => __( 'Hides the "Need Help?" tab in the bottom of the dashboard.', 'nerdpress-support' ),
			)
		);

		// Add the choice of firewall option
		add_settings_field(
			'firewall_choice',
			__( 'Firewall', 'nerdpress-support' ),
			array( $this, 'radiobutton_element_callback' ),
			$settings_option,
			'options_section',
			array(
				'menu'  => $settings_option,
				'id'    => 'firewall_choice'
			)
		);

		// add_settings_field(
		//   'identify_users',
		//   __( 'Identify Users', 'nerdpress-support' ),
		//   array( $this, 'checkbox_element_callback' ),
		//   $settings_option,
		//   'options_section',
		//   array(
		//     'menu'  => $settings_option,
		//     'id'    => 'identify_users',
		//     'label' => __( 'If checked Blog Tutor Support widget will identify the user ID, email and display name from logged users.', 'nerdpress-support' ),
		//   )
		// );

		// Register settings.
		register_setting( $settings_option, $settings_option, array( $this, 'validate_options' ) );

		/**
		* Server Information form fields.
		*/
		$information_option = 'blog_tutor_server_information';
		// Set Custom Fields cection.
		add_settings_section(
			'information_section',
			__( 'NerdPress Server Information Section', 'nerdpress-support' ),
			array( $this, 'section_options_callback' ),
			$information_option
		);

		add_settings_field(
			'server_info',
			__( 'Server Stats', 'nerdpress-support' ),
			array( $this, 'server_info_element_callback' ),
			$information_option,
			'information_section',
			array(
				'menu'  => $information_option,
				'id'    => 'server_info',
				'label' => __( 'Showing sever stats and variables.', 'nerdpress-support' ),
			)
		);
		register_setting( $information_option, $information_option, array( $this, 'validate_options' ) );


		// Check if Securi's enabled to skip this branch since it would still execute even if the SFW tab is absent
		$bt_options = get_option( 'blog_tutor_support_settings' );
		$has_sucuri = ( isset( $bt_options['firewall_choice'] ) && $bt_options['firewall_choice'] == 'sucuri' );
		$sucuri_api_call_array = Blog_Tutor_Support_Helpers::get_sucuri_api_call();

		if ( $has_sucuri && is_array( $sucuri_api_call_array ) ) {
			/**
			* Sucuri form fields.
			*/
			$sucuri_option = 'blog_tutor_sucuri_settings';
			// Set Custom Fields cection.
			add_settings_section(
				'information_section',
				__( 'Sucuri settings Section', 'nerdpress-support' ),
				array( $this, 'section_options_callback' ),
				$sucuri_option
			);

			add_settings_field(
				'sucuri_options',
				__( 'Sucuri Options', 'nerdpress-support' ),
				array( $this, 'sucuri_options_element_callback' ),
				$sucuri_option,
				'information_section',
				array(
					'menu'  => $sucuri_option,
					'id'    => 'sucuri_options',
					'label' => __( 'Sucuri actions and options.', 'nerdpress-support' ),
				)
			);
			register_setting( $sucuri_option, $sucuri_option, array( $this, 'validate_options' ) );
		}
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
	 * Radio Button area callback
	 *
	 * @param array $args Callback arguments.
	 */
	public function radiobutton_element_callback( $args ) {
		$menu    = $args['menu'];
		$id      = $args['id'];
		$options = get_option( $menu );

		if ( isset( $options[ $id ] ) ) {
			$current = $options[ $id ];
		} else {
			$current = isset( $args['default'] ) ? $args['default'] : '0';
		}

		$firewall = '';
		if( isset( $options['firewall_choice'] ) ) { 
			$firewall = $options['firewall_choice'];
		} else {
			$firewall = 'sucuri';
			$options['firewall_choice'] = $firewall;
			update_option( 'blog_tutor_support_settings', $options );
		}
 
		include dirname( __FILE__ ) . '/views/html-radiobutton-field.php';
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
	 * Sucuri Options element fallback.
	 *
	 * @param array $args Callback arguments.
	 */
	public function sucuri_options_element_callback( $args ) {
		$menu    = $args['menu'];
		$id      = $args['id'];
		$options = get_option( $menu );

		if ( isset( $options[ $id ] ) ) {
			$value = $options[ $id ];
		} else {
			$value = isset( $args['default'] ) ? $args['default'] : '';
		}

		include dirname( __FILE__ ) . '/views/html-sucuri-options-field.php';
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

}

new Blog_Tutor_Support_Admin();
