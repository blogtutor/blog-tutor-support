<?php
if ( !defined('ABSPATH') )
	die();

	/**
	 * NerdPress_Support_ShortPixel
	 *
	 * @package  NerdPress
	 * @category Core
	 * @author Sergio Scabuzzo
	 */

class NerdPress_Support_ShortPixel {
	/**
	 * Initialize the settings.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'is_shortpixel_bulk_optimize_set' ) );
  }

	public function is_shortpixel_bulk_optimize_set() {
 		
		$options = get_option( 'blog_tutor_support_settings', array() );

		if ( NerdPress_Helpers::hide_shortpixel_settings() ) {
			add_action( 'admin_menu', function () {
				remove_submenu_page( 'upload.php', 'wp-short-pixel-bulk' );
				remove_submenu_page( 'options-general.php', 'wp-shortpixel-settings' );
			}, 20 );
		}
	}
}

new NerdPress_Support_ShortPixel();


// a:44:{s:11:"APIKeyValid";b:1;s:12:"APICallsMade";s:14:"289,806 images";s:13:"APICallsQuota";s:14:"580,000 images";s:19:"APICallsMadeOneTime";s:17:"15,629,110 images";s:20:"APICallsQuotaOneTime";s:17:"15,913,634 images";s:19:"APICallsMadeNumeric";s:18:"289806.00000000000";s:20:"APICallsQuotaNumeric";i:580000;s:26:"APICallsMadeOneTimeNumeric";s:20:"15629110.00000000000";s:27:"APICallsQuotaOneTimeNumeric";s:20:"15913634.00000000000";s:17:"APICallsRemaining";d:574718;s:18:"APILastRenewalDate";s:19:"2021-10-21 00:01:05";s:11:"DomainCheck";s:10:"Accessible";s:4:"time";i:1635367184;s:12:"optimizePdfs";s:1:"1";s:10:"totalFiles";i:5;s:9:"mainFiles";i:2;s:19:"totalProcessedFiles";i:0;s:18:"mainProcessedFiles";i:0;s:19:"totalProcLossyFiles";i:0;s:18:"mainProcLossyFiles";i:0;s:20:"totalProcGlossyFiles";i:0;s:19:"mainProcGlossyFiles";i:0;s:22:"totalProcLosslessFiles";i:0;s:21:"mainProcLosslessFiles";i:0;s:12:"totalMlFiles";i:5;s:11:"mainMlFiles";i:2;s:21:"totalProcessedMlFiles";i:0;s:20:"mainProcessedMlFiles";i:0;s:21:"totalProcLossyMlFiles";i:0;s:20:"mainProcLossyMlFiles";i:0;s:22:"totalProcGlossyMlFiles";i:0;s:21:"mainProcGlossyMlFiles";i:0;s:24:"totalProcLosslessMlFiles";i:0;s:23:"mainProcLosslessMlFiles";i:0;s:21:"totalProcUndefMlFiles";i:0;s:20:"mainProcUndefMlFiles";i:0;s:21:"mainUnprocessedThumbs";i:0;s:7:"totalM1";i:2;s:7:"totalM2";i:0;s:7:"totalM3";i:0;s:7:"totalM4";i:0;s:15:"filesWithErrors";a:2:{i:17;a:3:{s:2:"Id";s:2:"17";s:4:"Name";s:44:"nerdpress-icon-purple-on-green-200x200-1.png";s:7:"Message";s:141:"Error: <i>There was an error and your request was not processed. (nerdpress-icon-purple-on-green-200x200-1.png: Could not download file.)</i>";}i:44;a:3:{s:2:"Id";s:2:"44";s:4:"Name";s:27:"nerdpress-logo-250x56-1.png";s:7:"Message";s:124:"Error: <i>There was an error and your request was not processed. (nerdpress-logo-250x56-1.png: Could not download file.)</i>";}}s:19:"moreFilesWithErrors";i:0;s:19:"foundUnlistedThumbs";b:0;}
