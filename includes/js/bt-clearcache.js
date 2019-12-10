'use strict';

jQuery(document).ready(function($) {
	$('#wp-admin-bar-bt-clear-cloudproxy a').click(makeAjaxCall);

	function makeAjaxCall() {
		var nText = 'One moment, please...	  ';
		$(this).text(nText);
		$('#wp-admin-bar-nerdpress-menu').off();

		var self = this;
		$.ajax({
			url: sucuri_clearcache.endpoint,
			type: 'post',
			data: {
				action: 'sucuri_clearcache',
				sucuri_clearcache_nonce: sucuri_clearcache.nonce,
			}
		}).done(function(data) {
			if(isDashboard()) window.location.reload();
			else {
				if(!data) $(self).text('Error!');
				else $(self).text('Success!');
			}
		});
	}

	function isDashboard() {
		return window.location.href.includes('/wp-admin/');
	}
});
