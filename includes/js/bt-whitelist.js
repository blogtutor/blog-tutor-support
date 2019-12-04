'use strict';

jQuery(document).ready(function($) {
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

    function wlNotify(ip) {
        var wlBox = document.createElement('div');
        wlBox.innerHTML = 'IP ' + ip + ' has been whitelisted';
        
        wlBox.style.padding = '1rem';
        wlBox.style.backgroundColor = 'rgb(152, 79, 159)';
        wlBox.style.color = '#fff';
        wlBox.style.position = 'absolute';
        wlBox.style.top = '3rem';
        wlBox.style.right = '5rem';
        wlBox.style.display = 'none';
       
        document.body.appendChild(wlBox);

        $(wlBox).fadeIn(5000).fadeOut(6000); 
    } 
});
