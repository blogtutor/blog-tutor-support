'use strict';

jQuery(document).ready(function($) {
	$('#wp-admin-bar-bt-clear-cloudproxy a').click(makeAjaxCall);


	function pad(padLen) {
		var padStr = '';
		for(var i = 0; i < padLen; i++)
			padStr += ' ';
		
		return padStr;
	}

	function makeAjaxCall() {
		var nText = 'One moment, please...	  ';
		var len = $(this).text().length - nText.length;
		$(this).text(nText + pad(len));

		$.ajax({
			url: sucuri_clearcache.endpoint,
			type: 'post',
			data: {
				action: 'sucuri_clearcache',
				sucuri_clearcache_nonce: sucuri_clearcache.nonce,
			}
		}).done(function(data) {
			window.location.reload();
		});
	}
});
