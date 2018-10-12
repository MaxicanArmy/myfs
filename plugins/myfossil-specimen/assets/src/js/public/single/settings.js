(function($) {
    'use strict';

    function delete_fossil() {
        var nonce = $('#myfossil_specimen_nonce').val();
        var post_id = parseInt($('#post_id').val());

        $.ajax({
            async: false,
            type: 'post',
            url: ajaxurl,
            data: {
                'action': 'myfossil_fossil_delete',
                'nonce': nonce,
                'post_id': post_id,
            },
            dataType: 'json',
            success: function(data) {
                window.location.href = '/fossils';
            },
            complete: function(data) {},
            error: function(err) {
                console.error(err);
            }
        });

    }

    function delete_prompt() {
        $('button#delete-fossil')
            .text('Delete this fossil forever')
            .removeClass('btn-default')
            .css('border', '3px solid red')
            .css('background-color', 'red')
            .css('color', 'white')
            .click(delete_fossil);
    }

    $(function() {
        $('button#delete-fossil').click(delete_prompt);
    });

}(jQuery));