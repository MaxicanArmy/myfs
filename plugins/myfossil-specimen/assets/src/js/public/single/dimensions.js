(function($) {
    'use strict';

    function init_dimensions() {
        var dims = ['length', 'width', 'height'];

        $.map(dims, function(dim) {
            var input = $('input#edit-fossil-dimension-' + dim);
            var view = $('td#fossil-dimension-' + dim);

            input.val(view.data('value') * 100.);

            input.keyup(function() {
                if ($.isNumeric(input.val()))
                    view.text(input.val() + ' cm').data('value', input.val());
                save_prompt();
            });
        });
    }

    function save_dimensions() {
        var post_id = $('#post_id').val(),
            nonce = $('#myfossil_specimen_nonce').val(),
            length = $('#edit-fossil-dimension-length').val(),
            width = $('#edit-fossil-dimension-width').val(),
            height = $('#edit-fossil-dimension-height').val(),
            comment = $('#edit-fossil-dimension-comment').val();

        $.ajax({
            async: false,
            type: 'post',
            url: ajaxurl,
            dataType: 'json',
            data: {
                'action': 'myfossil_save_dimensions',
                'nonce': nonce,
                'post_id': post_id,
                'length': length,
                'width': width,
                'height': height,
                'comment': comment
            },
            success: function(data) {
                console.log(data);
                console.debug(post_id, nonce, length, width, height, comment);
                $('#fossil-dimensions-success').show().fadeOut();
                $('#edit-fossil-dimension-save-alert').fadeOut();

               
            },
            complete: function(data) {
                $('#fossil-dimensions-loading').hide();
                init_dimensions();
                   window.location.reload(true);
            },
            error: function(err) {
                console.error(err);
                $('#fossil-dimensions-error').show().fadeOut();
            }
        });
    }

    function save_prompt() {
        $('#edit-fossil-dimension-save-alert').show();
    }

    function toggle_comment() {
        $('#edit-fossil-dimension-comment-form-group').toggle();
        $(this).fadeOut(400);
        // $( '#edit-fossil-dimension-comment-toggle > button' ).click( toggle_comment );
    }

    $(function() {
        init_dimensions();

        $('#edit-fossil-dimension-save').click(save_dimensions);
        $('#edit-fossil-dimension-comment-toggle > button').click(toggle_comment);

        $('#edit-fossil-dimensions').popup({
            type: 'tooltip',
            opacity: 1,
            background: false,
            transition: 'all 0.2s',
        });

    });

}(jQuery));
