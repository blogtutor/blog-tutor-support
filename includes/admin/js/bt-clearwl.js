'use strict';

jQuery(document).ready(function($) {
	$('#clearwhitelist').click(function() {
		makeAjaxCall(this, 'clear_whitelist');
	});

	$('#clearwhitelistnp').click(function() {
		makeAjaxCall(this, 'np_clear_whitelist');
	});

	function makeAjaxCall(thisCtx, action) {
		var nText = 'One moment, please...	  ';
		$(thisCtx).val(nText);

		$.ajax({
			url: clear_whitelist.endpoint,
			type: 'post',
			data: {
				action: action,
				clear_whitelist_nonce: clear_whitelist.nonce,
			}
		}).done(function(data) {
			if(!data) $(thisCtx).text('Error!');
			else {
				$(thisCtx).text('Success!');
				window.location.reload();
			}
		});
	}
});
