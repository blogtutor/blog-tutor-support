'use strict';

const radiosFw = document.getElementsByName('blog_tutor_support_settings[firewall_choice]');

radiosFw.forEach( fwRadio => fwRadio.addEventListener("change", changeCF ));

function changeCF() {
    const cfopt = document.querySelectorAll('.np-cfopt');
    const radiosCF = document.getElementsByName('blog_tutor_support_settings[cloudflare_zone]');
    const cfToken = document.getElementById('cloudflare_token');

    let fwSet = checkFirewall();

    if (fwSet == 'cloudflare'){
        cfopt.forEach( opt => opt.style.opacity = 1 );
        radiosCF.forEach( function(radio){
            if(radio.hasAttribute("disabled")){
                radio.removeAttribute("disabled");
            }
        });
        if(cfToken.hasAtribute("disabled")){
            cfToken.removeAttribute("disabled");
        }
        return;
    } else {
        cfopt.forEach( opt => opt.style.opacity = 0.6 );
        radiosCF.forEach( radio => radio.setAttribute("disabled", "disabled"));
        cfToken.setAttribute("disabled", "");
    }
}

function checkFirewall () {
    for ( var i = 0; i < radiosFw.length; ++i ) {
        if ( radiosFw[i].checked ) {
            return radiosFw[i].value;
        }
    } return;
    
}

changeCF();