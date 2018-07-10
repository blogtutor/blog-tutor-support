<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Blog Tutor Support Widget.
 *
 * @package  Blog_Tutor_Support/Frontend
 * @category Widget
 * @author   Fernando Acosta
 */
class Blog_Tutor_Support_Widget {
  /**
   * Initialize the widget.
   */
  public function __construct() {
    add_action( 'admin_footer', array( $this, 'widget' ), 50 );
  }

  public function widget() {
  $options = get_option( 'blog_tutor_support_settings', array() );

    // test mode check
    if ( isset( $options['test_mode'] ) && ! current_user_can( 'manage_options' ) ) {
      return;
    }

    if ( isset( $options['embed_code'] ) ) {
      echo $options['embed_code'];
	  			  echo 'TEST TEST TEST';
    }

    if ( is_user_logged_in() && isset( $options['identify_users'] ) ) {
      $ajw_current_user = wp_get_current_user();
    ?>
      <script type="text/javascript">
      jQuery( window ).load( function( $ ) {
        if ( window.supportHeroWidget != undefined ) {
          var properties = {
				 articleParameters: {  // These can be embedded in articles with {variable}
					 username: '<?php echo $ajw_current_user->user_login; ?>',
					 firstname: '<?php echo $ajw_current_user->user_firstname; ?>',
					 lastname: '<?php echo $ajw_current_user->user_lastname; ?>',
					 displayname: '<?php echo $ajw_current_user->display_name; ?>',					 
					 useremail: '<?php echo $ajw_current_user->user_email; ?>',
					 site_url: '<?php echo site_url(); ?>',
					 sitename: '<?php echo get_bloginfo( 'name' ); ?>',
					 currentpage: window.location.href
				 },
				 custom: {  // These are used to prefill the contact form
					 customerId: 1234,
					 userEmail: '<?php echo $ajw_current_user->user_email; ?>',
					 name: '<?php echo $ajw_current_user->user_firstname . ' ' . $ajw_current_user->user_lastname; ?>' 			
				}	
         };
         window.supportHeroWidget.track( properties );
        }
      });
      </script>
    <?php
    }
  }
}

new Blog_Tutor_Support_Widget();
