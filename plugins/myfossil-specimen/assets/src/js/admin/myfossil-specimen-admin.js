(function($) {
    'use strict';

    /**
     * Instruct WordPress to load default taxonomic data
     */
    function load_taxonomy_terms() {
        var button_text = 'Load WordPress Taxonomies',
            spinner_tpl = '<i class="fa fa-fw fa-circle-o-notch fa-spin"></i>';
        var nonce = $('#myfossil_nonce').val();

        $('#load-taxonomies').prepend(spinner_tpl);

        $.ajax({
            type: 'post',
            url: ajaxurl,
            data: {
                'action': 'myfossil_load_terms',
                'nonce': nonce
            },
            success: function(resp) {
                if (parseInt(resp) === 1) {
                    $('#message').html(
                        '<p>Loaded default terms into taxonomies successfully.</p>'
                    ).addClass('updated');
                } else {
                    $('#message').html(
                        '<p>Failed to load default data into taxonomies.</p>'
                    ).addClass('error');
                    console.log(resp);
                }
            },
            error: function(err) {
                console.log(err);
            }
        });

        $('#load-taxonomies').text(button_text);
    }

    /**
     * Instruct WordPress to load default Geochronologies (time intervals)
     */
    function load_geochronology() {
        var button_text = 'Load Geochronology',
            spinner_tpl = '<i class="fa fa-fw fa-circle-o-notch fa-spin"></i>';
        var nonce = $('#myfossil_nonce').val();

        $('#load-geochronology').prepend(spinner_tpl);

        $.ajax({
            type: 'post',
            url: ajaxurl,
            data: {
                'action': 'myfossil_load_geochronology',
                'nonce': nonce
            },
            success: function(resp) {
                if (parseInt(resp) === 1) {
                    $('#message').html(
                        '<p>Loaded default time intervals into geochronology.</p>'
                    ).addClass('updated');
                } else {
                    $('#message').html(
                        '<p>Failed to load default data into geochronology.</p>'
                    ).addClass('error');
                    console.log(resp);
                }
            },
            error: function(err) {
                console.log(err);
            }
        });

        $('#load-geochronology').text(button_text);
    }

    /**
     * Instruct WordPress to load default Geochronologies (time intervals)
     */
    function load_fossils() {
        var button_text = 'Load Fossils',
            spinner_tpl = '<i class="fa fa-fw fa-circle-o-notch fa-spin"></i>';
        var nonce = $('#myfossil_nonce').val();

        $('#load-fossils').prepend(spinner_tpl);

        $.ajax({
            type: 'post',
            url: ajaxurl,
            data: {
                'action': 'myfossil_load_default_fossils',
                'nonce': nonce
            },
            success: function(resp) {
                if (parseInt(resp) === 1) {
                    $('#message').html(
                        '<p>Loaded default time intervals into fossils.</p>'
                    ).addClass('updated');
                } else {
                    $('#message').html(
                        '<p>Failed to load default data into fossils.</p>'
                    ).addClass('error');
                    console.log(resp);
                }
            },
            error: function(err) {
                console.error(err);
            }
        });

        $('#load-fossils').text(button_text);
    }

    /**
     * Instruct WordPress to reset all taxonomy terms
     */
    function reset_taxonomy_terms() {
        $.post(ajaxurl, {
                action: 'reset'
            },
            function(response) {
                console.log(response);
            }
        );
    }

    $(function() {
        $('#load-taxonomies').click(load_taxonomy_terms);
        $('#load-geochronology').click(load_geochronology);
        $('#load-fossils').click(load_fossils);
    });

}(jQuery));