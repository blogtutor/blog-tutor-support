'use strict';

jQuery(document).ready(function($) {
	$('#wp-admin-bar-bt-clear-cloudproxy a').click(makeAjaxCall);

	function makeAjaxCall() {
		var nText = 'One moment, please...';
		$(this).text(nText);

		$('.hover').children().css('display', 'block');
		$('#wp-admin-bar-nerdpress-menu').off('hover');

		var self = this;
		$.ajax({
			url: sucuri_clearcache.endpoint,
			type: 'post',
			data: {
				action: 'sucuri_clearcache',
				sucuri_clearcache_nonce: sucuri_clearcache.nonce,
			}
		}).done(function(data) {
			// Reload the page if it's the admin dashboard
			if(isDashboard()) {
				window.location = injectParam('np_clear_sucuri=true');
			} else {
				if(!data) $(self).text('Error!');
				else $(self).text('Success!');
				$(document).click(function() {
					$('#wp-admin-bar-nerdpress-menu').children('.ab-sub-wrapper').css('display', 'none');
					$('#wp-admin-bar-nerdpress-menu').hover(function() {
						$(this).children('.ab-sub-wrapper').css('display', 'block');
					}, function() {
						$(this).children('.ab-sub-wrapper').css('display', 'none');
					});
					$(this).off( 'click' );
				});
			}
		});
	}

	function isDashboard() {
		return window.location.href.includes('/wp-admin/');
	}

	function injectParam(paramStr) {
		if(window.location.href.includes('?')) {
			var parts = window.location.href.split('?');
			parts[1] = paramStr + '&' + parts[1];
			return parts.join('?').replace(/#/g, '');
		} else return window.location.href.replace(/#/g, '') + '?' + paramStr;
	}
});
