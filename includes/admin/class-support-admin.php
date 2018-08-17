<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Blog Tutor Support Admin.
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

  /**
   * Add the settings page.
   */
  public function settings_menu() {
    $current_user = wp_get_current_user();
    if ( strpos( $current_user->user_email, '@blogtutor.com' ) !== false OR strpos( $current_user->user_email, '@nerdpress.net' ) !== false ) {
      add_options_page(
        'Blog Tutor Support',
        'Blog Tutor Support',
        'manage_options',
        'blog-tutor-support',
        array( $this, 'html_settings_page' )
      );
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
      __( 'Blog Tutor Support Widget', 'blog-tutor-support' ),
      array( $this, 'section_options_callback' ),
      $option
    );

    // add_settings_field(
    //   'test_mode',
    //   __( 'Test mode', 'blog-tutor-support' ),
    //   array( $this, 'checkbox_element_callback' ),
    //   $option,
    //   'options_section',
    //   array(
    //     'menu'  => $option,
    //     'id'    => 'test_mode',
    //     'label' => __( 'If checked show the widget to admins only.', 'blog-tutor-support' ),
    //   )
    // );

    add_settings_field(
      'embed_code',
      __( 'Blog Tutor Support embed code', 'blog-tutor-support' ),
      array( $this, 'textarea_element_callback' ),
      $option,
      'options_section',
      array(
        'menu' => $option,
        'id' => 'embed_code',
        'description' => __( 'DEPRECATED! Get your widget embed code inside your Blog Tutor Support dashboard.', 'blog-tutor-support' ),
      )
    );

    // add_settings_field(
    //   'identify_users',
    //   __( 'Identify Users', 'blog-tutor-support' ),
    //   array( $this, 'checkbox_element_callback' ),
    //   $option,
    //   'options_section',
    //   array(
    //     'menu'  => $option,
    //     'id'    => 'identify_users',
    //     'label' => __( 'If checked Blog Tutor Support widget will identify the user ID, email and display name from logged users.', 'blog-tutor-support' ),
    //   )
    // );

    // Register settings.
    register_setting( $option, $option, array( $this, 'validate_options' ) );
  }

  /**
   * Section null fallback.
   */
  public function section_options_callback() {

  }

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

new Blog_Tutor_Support_Admin;
