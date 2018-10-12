(function($) {
    'use strict';

    function status_loading() {
        // $('#upload-button').html('<span class="loading"><i class="fa fa-circle-o-notch fa-spin fa-6"></i></span> Uploading images...');
    }

    function status_error(data) {
        if (data.post_id && data.post_id[0]) {
            alert(data.post_id[0].error);
        } else {
            alert('Please upload images of type JPEG or PNG');
        }
    }

    $(function() {
        var post_id = $('#post_id').val();
        var nonce = $('#myfossil_specimen_nonce').val();
        var n_files = 0, n_uploaded = 0;

        $('#fossil-upload-image').fileupload({
            dataType: 'json',
            formData: {
                action: 'myfossil_upload_fossil_image',
                nonce: nonce,
                post_id: post_id,
            },
            url: ajaxurl,
            start: function(e, data) {
                $('#progress-bar').show();
            },
            progress: function(e, data) {
            },
            send: function(e, data) {
                n_files++;
            },
            success: function() {
                n_uploaded++;

                var p = parseInt(n_uploaded / n_files * 100, 10);
                $('.progress-bar')
                    .attr('aria-valuenow', p)
                    .css('width', p + '%');
                if (n_uploaded >= n_files) {
                    location.reload();
                }
            }
        });

        $('.fossil-delete-image').click(function() {
            var post_id = $('#post_id').val();
            $(this).html('<span class="loading"><i class="fa fa-circle-o-notch fa-spin fa-6"></i></span> Deleting image...');

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'myfossil_delete_fossil_image',
                    'nonce': nonce,
                    'post_id': post_id,
                    'image_id': $(this).data('attachment-id')
                },
                success: function(data) {
                    if (data == '1') {
                        location.reload();
                    }
                },
                error: function(err) {
                    console.error(err);
                }
            });
        });

        $('.fossil-feature-image').click(function() {
            var post_id = $('#post_id').val();

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'myfossil_feature_fossil_image',
                    'nonce': nonce,
                    'post_id': post_id,
                    'image_id': $(this).data('attachment-id')
                },
                success: function(data) {
                    if (data == '1') {
                        location.reload();
                    } else {
                        console.error(data);
                    }
                },
                error: function(err) {
                    console.error(err);
                }
            });
        });

    });

}(jQuery));
