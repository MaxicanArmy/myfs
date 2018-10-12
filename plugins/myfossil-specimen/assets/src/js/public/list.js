(function($) {
    'use strict';

    function enable_hover_hand() {
        $('tr.hover-hand').on('click', function() {
            if ($(this).data('href') !== undefined)
                document.location = $(this).data('href');
        });
    }

    function enable_create_fossil_button() {
            var btn = $('button#fossil-create-new');
            btn.removeClass('disabled');

            btn.click(function() {
                console.info('clicked');
                var nonce = $('#myfossil_specimen_nonce').val();

                $.ajax({
                    async: false,
                    type: 'post',
                    url: ajaxurl,
                    data: {
                        'action': 'myfossil_create_fossil',
                        'nonce': nonce,
                    },
                    dataType: 'json',
                    success: function(data) {
                        var post_id = parseInt(data);
                        if (post_id <= 0) {
                            console.error('Error creating new post');
                            return;
                        }

                        document.location = '/fossils/' + post_id;
                    },
                    error: function(err) {
                        console.error(err);
                    }
                });

            });

        }
        // }}}

    $(function() {
        enable_hover_hand();
        enable_create_fossil_button();
    });

}(jQuery));