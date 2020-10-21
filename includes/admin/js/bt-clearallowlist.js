'use strict';

jQuery(document).ready(function($) {
	$('#clearallowlist').click(function() {
		makeAjaxCall(this, 'clear_allowlist');
	});

	function makeAjaxCall(thisCtx, action) {
		var nText = 'One moment, please...	  ';
		$(thisCtx).val(nText);

		$.ajax({
			url: clear_allowlist.endpoint,
			type: 'post',
			data: {
				action: action,
				clear_allowlist_nonce: clear_allowlist.nonce,
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
