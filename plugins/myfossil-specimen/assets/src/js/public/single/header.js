(function($) {
    'use strict';

    function toggle_post_status() {
        var post_id = $('#post_id').val(),
            nonce = $('#myfossil_specimen_nonce').val(),
            post_status = $('input.post_status:checked').val();

        $.ajax({
            type: 'post',
            url: ajaxurl,
            dataType: 'json',
            data: {
                action: 'myfossil_save_status',
                nonce: nonce,
                post_id: post_id,
                post_status: post_status
            },
            success: function(data) {
                console.info(data);
		
		if($('.alert').hasClass('alert-info'))
        {
            $('.alert').removeClass('alert-info');
            $('.alert').addClass('alert-success');
            $('.alert-content').html('This specimen is currently <strong>published</strong> and visible to the public.');  
        }
        else
        {
            $('.alert').removeClass('alert-success');
            $('.alert').addClass('alert-info');
            $('.alert-content').html('This specimen is currently <strong>unpublished</strong> and visible only to you.');  
        }   		
		
		
		
		
            },
            error: function(err) {
                console.error(err);
            }
        });
    }

    $(function() {
        $('#draft').change(toggle_post_status);
        $('#published').change(toggle_post_status);
    });

}(jQuery));
