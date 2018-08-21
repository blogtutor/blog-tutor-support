<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

// Add Admin Bar Menu Items

function bt_custom_toolbar_links( $wp_admin_bar ) {

  if ( current_user_can('editor') || current_user_can('administrator') ) {

      // Add "Blog Tutor" parent menu Items
      $args = array(
      'id'      => 'blog-tutor-menu',
      'title'   =>  'Blog Tutor',
      'parent'  => false,
      );
      $wp_admin_bar->add_node( $args );

      // Add Child Menu Items
      // Add a Clear Cloudproxy link to the Admin Bar
      if ( file_exists($_SERVER[ "DOCUMENT_ROOT" ] . '/wp-content/uploads/sucuri/sucuri-settings.php' ) ) {

        // Get Cloudproxy API Keys
        $input_lines = file_get_contents( $_SERVER[ "DOCUMENT_ROOT" ] . '/wp-content/uploads/sucuri/sucuri-settings.php' );
        // Using # as regex delimiters since / was giving error
        $regex = "#\"sucuriscan_cloudproxy_apikey\":\"(.{32})\\\/(.{32})#";

        preg_match_all( $regex, $input_lines, $output_array, PREG_SET_ORDER, 0 );

        if ( array_filter($output_array) ) {
          $API_KEY = $output_array[0][1];
          $API_SECRET = $output_array[0][2];
        }

        if ( isset ( $API_KEY ) ) {
          // Build the Clear Cache & Whitelist links (Cloudproxy API v1) and add it to the admin bar
          $Cloudproxy_clear = "https://waf.sucuri.net/api?&k=" . $API_KEY . "&s=" . $API_SECRET . "&a=clearcache";
          $args = array(
              'id' => 'bt-clear-cloudproxy',
              'title' => 'Clear Cloudproxy Cache',
              'href' => $Cloudproxy_clear,
              'parent'  => 'blog-tutor-menu',
              'meta' => array(
                  'class' => 'btButton',
                  'target' => 'blank',
                  'title' => 'Clear the Cloudproxy cache',
                  )
          );
          $wp_admin_bar->add_node( $args );
          $Cloudproxy_whitelist = "https://waf.sucuri.net/api?&k=" . $API_KEY . "&s=" . $API_SECRET . "&a=whitelist";
          $args = array(
              'id' => 'bt-whitelist-cloudproxy',
              'title' => 'Whitelist Your IP Address',
              'href' => $Cloudproxy_whitelist,
              'parent'  => 'blog-tutor-menu',
              'meta' => array(
                  'class' => 'btButton',
                  'target' => 'blank',
                  'title' => 'Whitelist your current IP address, in case Cloudproxy is blocking you.',
                  'parent'  => 'blog-tutor-menu'
                  )
          );
          $wp_admin_bar->add_node( $args );
        } else {
            $args = array(
              'id' => 'bt-cloudproxy-api-not-set',
              'title' => 'Cloudproxy API Key is not set',
              'parent'  => 'blog-tutor-menu',
              'meta' => array(
                  'class' => 'btButton',
                  'title' => 'Your Cloudproxy API key is not set in the Sucuri Plugin. If your site should be configured to use Cloudproxy, please contact us!'
                  )
          );
          $wp_admin_bar->add_node( $args );
        }
      } else {
          $args = array(
            'id' => 'bt-sucuri-missing',
            'title' => 'The Sucuri Plugin is missing!',
            'parent'  => 'blog-tutor-menu',
            'meta' => array(
                'class' => 'btButton',
                'title' => 'Your Sucuri Plugin is not configured. Please contact us!'
                )
        );
       $wp_admin_bar->add_node( $args );
      }

      // "Get Help" link to open the Support Hero widget
        $args = array(
            'id' => 'bt-get-help',
            'title' => 'Get Help',
            'href' => '#',
            'parent'  => 'blog-tutor-menu',
            'meta' => array(
                'class' => 'btButton',
                'title' => 'Click to open our knowledge base and contact form.',
                'onclick' => 'window.supportHeroWidget.show();'
                )
        );
        $wp_admin_bar->add_node( $args );

  }
}
add_action( 'admin_bar_menu', 'bt_custom_toolbar_links', 99 );
