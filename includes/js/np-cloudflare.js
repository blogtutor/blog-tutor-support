jQuery( document ).ready( function( $ ) {
  'use strict';

  $( '#wp-admin-bar-purge-full a' ).click( function () {
		var nText = 'One moment, please...	  ';
		$(this).text(nText);
		
		$('.hover').children().css('display', 'block');
		$('#wp-admin-bar-nerdpress-menu').off('hover');
		
		$.ajax({
			url: np_cf_ei.endpoint,
			type: 'POST',
			data: {
				action: 'purge_cloudflare_full',
				np_cf_ei_nonce: np_cf_ei.nonce,
			}
		}).done( function( data ) {
			if( data == 'error' )
				alert( 'Please enter the host name in the Nerdpress Cloudflare Enterprise Integration Plugin\'s settings!' );
			window.location.reload();
		});
  });
  	
  $( '#wp-admin-bar-purge-url a' ).click( function () {
		var nText = 'One moment, please...	  ';
		$(this).text(nText);
			
		$('.hover').children().css('display', 'block');
		$('#wp-admin-bar-nerdpress-menu').off('hover');
			
		$.ajax({
			url: np_cf_ei.endpoint,
			type: 'POST',
			data: {
				url: np_cf_ei.url_to_purge,
				action: 'purge_cloudflare_url',
				np_cf_ei_nonce: np_cf_ei.nonce, 
			}
		}).done( function( data ) { 
			if( data == 'error' )
				alert( 'Please enter the host name in the Nerdpress Cloudflare Enterprise Integration Plugin\'s settings!' );
			window.location.reload(); 
		});
  });    
});
