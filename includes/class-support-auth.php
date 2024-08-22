<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * NerdPress Auth.
 *
 * @package  NerdPress
 * @category Auth
 * @author   Brian Routzong
 */
class NerdPress_Auth {
	/**
	 * Initialize the auth class.
	 */
	public function __construct() {
        add_action( 'admin_init', array( $this, 'redirect_if_not_authenticated' ) );
    }

    /**
     * Redirect if the user is not authenticated.
     */
    function redirect_if_not_authenticated() {
        // if we have a url parameter
        if ( is_admin() && is_user_logged_in() && NerdPress_Helpers::is_nerdpress() && ! $this->has_valid_cf_authorization_cookie() ) {

            if ( isset( $_GET['set_cookie'] ) && $_GET['set_cookie'] === 'true' ) {
                //verify the nonce to prevent CSRF attacks

                $nonce = wp_verify_nonce( $_GET['nonce'], 'cloudflare_cookie_redirect_'. get_current_user_id() );
                if ( ! $nonce ) {
                    wp_die( 'Invalid nonce when trying to set the NerdPress team member cookie.', 'NerdPress', array( 'response' => 403 ) );
                }

                // Set the cookie for 12 hours
                setcookie( 'NP_CF_Authorization', md5( get_current_user_id() ), time() + 12 * 60 * 60, '/', null, false, true );

                // remove set_cookie and nonce from the URL and reload the page
                wp_safe_redirect( remove_query_arg( array( 'set_cookie', 'nonce' ), $_SERVER['REQUEST_URI'] ) );
                exit;
            }
            // send the path and base domain as url paramaters to access.nerdpress.net
            // Example: https://access.nerdpress.net?path=/wp-admin&base=example.com
            $path = $_SERVER['REQUEST_URI'];
            $base = parse_url( home_url(), PHP_URL_HOST );
            $nonce = wp_create_nonce( 'cloudflare_cookie_redirect_' . get_current_user_id() );
            $redirect_url = 'https://access.nerdpress.net?path='. urlencode( $path ). '&base_domain='. urlencode( $base ) . '&nonce='. $nonce;

            // Redirect to the NerdPress access site
            wp_redirect( $redirect_url );
            exit;
        }
    }

    /**
     * Check if the user has a valid CF_Authorization cookie.
     *
     * @return bool True if the user has a valid CF_Authorization cookie, false otherwise.
     */
    function has_valid_cf_authorization_cookie() {
        if ( isset( $_COOKIE['NP_CF_Authorization'] ) ) {
            return false;
        }

        if ( md5( get_current_user_id() ) === $_COOKIE['NP_CF_Authorization'] ) {
            return true;
        }

        return false;

    }
}

new NerdPress_Auth();
