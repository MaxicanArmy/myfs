( function( $ ) {
    'use strict';

    /**
     * Instruct WordPress to load default data
     *
     * @see \myFOSSIL\Plugins\Resources\myFOSSIL_Resources_Admin
     */
    function load_data() {
        var button_text = 'Load default data',
            spinner_tpl = '<i class="fa fa-fw fa-circle-o-notch fa-spin"></i>';

        $( '#load' ).prepend( spinner_tpl );

        $.ajax({
            type: 'post',
            url: ajaxurl,
            data: { 'action': 'myfossil_resources_load_data' },
            success: function( resp ) {
                if ( parseInt( resp ) == 1 ) {
                    $( '#message' ).html( 
                            '<p>Loaded default data into Places successfully.</p>'
                        ).addClass( 'updated' );
                } else {
                    $( '#message' ).html( 
                            '<p>Failed to load default data into Places.</p>'
                        ).addClass( 'error' );
                    console.log( resp );
                }
            },
            error: function( err ) {
                console.log( err );
            }
        });

        $( '#load' ).text( button_text );
    }

    /**
     * Instruct WordPress to reset all data
     *
     * @see \myFOSSIL\Plugins\Resources\myFOSSIL_Resources_Admin
     */
    function reset_data() {
        $.post( ajaxurl, { action: 'reset' }, 
            function( response ) { console.log( response ); }
        );
    }

    $( function() {
        $( '#load' ).click( load_data );
    } );

}( jQuery ) );
