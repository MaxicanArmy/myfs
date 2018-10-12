(function($) {
    'use strict';

    function post_comment() {
        var post_id = $('input#fossil-id').val();
        var comment = $('textarea#fossil-comment-textarea').val();
        var nonce = $('#myfossil_specimen_nonce').val();

        $.ajax({
            type: 'post',
            url: ajaxurl,
            data: {
                'action': 'myfossil_fossil_comment',
                'nonce': nonce,
                'post_id': post_id,
                'comment': comment
            },
            dataType: 'html',
            success: function(data) {
              location.reload();
            },
            complete: function(data) {},
            error: function(err) {
                console.error(err);
            }
        });
    }

    $(function() {
        $('#fossil-comment-submit').click(post_comment);
    });

}(jQuery));
