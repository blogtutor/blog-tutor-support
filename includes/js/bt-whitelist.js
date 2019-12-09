'use strict';

jQuery(document).ready(function($) {
    makeAjaxCall();  

    $(document).on('tinymce-editor-init.autosave', makeAjaxCall);

    function makeAjaxCall() {
        $.ajax({
            url: sucuri_whitelist.endpoint,
            type: 'post',
            data: {
                action: 'whitelist_ip',
                sucuri_whitelist_nonce: sucuri_whitelist.nonce,
            }
        }).done(function(data) {
            var successStr = 'IP Whitelisted: ';
            if(data.includes(successStr)) {
                var parts = data.split(successStr);
                var ip = parts[parts.length - 1];
                wlNotify(ip);
            }
        });
    }

    function wlNotify(ip) {
        var html = 'Your IP (' + ip + ') has been automatically whitelisted '
                        + '<br />on the Sucuri Firewall for the next 24 hours.';

        window.crNerdPressNotification(html);
    } 
});

window.crNerdPressNotification = function(html) {
        var npBox = document.createElement('div');
        npBox.innerHTML = '<h4><img src="/wp-content/plugins/blog-tutor-support/includes/images/nerdpress-icon-250x250.png" width="50" '
                        + 'style="vertical-align:middle" />NerdPress Notification</h4>' + html;
        
        npBox.style.padding = '0.3rem 1rem 1rem 1rem';
        npBox.style.backgroundColor = 'rgb(152, 79, 159)';
        npBox.style.color = '#fff';
        npBox.style.position = 'absolute';
        npBox.style.top = '3rem';
        npBox.style.right = '5rem';
        npBox.style.display = 'none';
       
        document.body.appendChild(npBox);

        jQuery(npBox).fadeIn(2000).delay(10000).fadeOut(6000); 
}
