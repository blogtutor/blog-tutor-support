'use strict';

jQuery(document).ready(function($) {
	$('#clearwhitelist').click(makeAjaxCall);

	function makeAjaxCall() {
		var nText = 'One moment, please...	  ';
		$(this).val(nText);

		var self = this;
		$.ajax({
			url: clear_whitelist.endpoint,
			type: 'post',
			data: {
				action: 'clear_whitelist',
				clear_whitelist_nonce: clear_whitelist.nonce,
			}
		}).done(function(data) {
			if(!data) $(self).text('Error!');
			else {
				$(self).text('Success!');
				window.location.reload();
			}
		});
	}
});
