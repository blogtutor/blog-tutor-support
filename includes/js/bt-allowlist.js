'use strict';

jQuery(document).ready(function($) {
	makeAjaxCall();  

	function makeAjaxCall() {
		$.ajax({
			url: sucuri_allowlist.endpoint,
			type: 'post',
			data: {
				action: 'allowlist_ip',
				sucuri_allowlist_nonce: sucuri_allowlist.nonce,
			}
		}).done(function(data) {
			if(data.includes('np_no_message')) return;
			if(data && !data.includes('already on the allowlist')) {
				var ip;
				var npMsgString = null;
				try {
					ip = data.match(/((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)/)[0];
				} catch(err) {
					npMsgString = '<strong>⚠  Heads up!</strong><br />Automatically adding your IP address to the Sucuri Firewall allowlist didn\'t work.<br />'
						    + 'If the problem persists, please contact us at <a '
						    + ' style="text-decoration:none;color:#0F145B;" href="mailto:support@nerdpress.net">support@nerdpress.net</a>.';
				}
				
				if(npMsgString === null)
					npMsgString = 'Your IP (' + ip +  ') has been automatically<br />added to the Sucuri Firewall allowlist for the next 24 hours.';

				allowlistNotify(npMsgString);
			}
		});
	}

	function allowlistNotify(message) {
		window.crNerdPressNotification(message);
	} 
});

window.crNerdPressNotification = function(message) {
		var npBox = document.createElement('div');
		npBox.innerHTML = '<h4><img src="/wp-content/plugins/blog-tutor-support/includes/images/nerdpress-icon-250x250.png" width="50" '
						+ 'style="vertical-align:middle" />NerdPress Notification</h4>' + message;

		npBox.style.padding = '0.3rem 1rem 1rem 1rem';
		npBox.style.backgroundColor = 'rgb(152, 79, 159)';
		npBox.style.color = '#fff';
		npBox.style.position = 'absolute';
		npBox.style.top = '3rem';
		npBox.style.right = '320px';
		npBox.style.display = 'none';

		document.body.appendChild(npBox);

		jQuery(npBox).fadeIn(1500).delay(6000).fadeOut(2000); 
}
