jQuery( document ).ready( function( $ ) {
    'use strict';

    $( '#wp-admin-bar-hostname' ).find( '.ab-item' ).css( 'cursor', 'pointer' );
    $( '#wp-admin-bar-hostname' ).click( clearCache );

    function clearCache() {
        this.innerHTML = 'Clearing cache...';
        $.ajax({
            url: np_cf_ei.endpoint,
            type: 'POST',
            data: {
                action: 'purgeCacheAjaxWrapper',
                np_cf_ei_nonce: np_cf_ei.nonce, 
            }
        }).done( function( data ) { 
            if( data == 'error' )
                alert( 'Please enter the host name in the Nerdpress Cloudflare Enterprise Integration Plugin\'s settings!' );
            window.location.reload(); 
        });
    }    
});
