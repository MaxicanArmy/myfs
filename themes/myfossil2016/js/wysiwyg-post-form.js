(function($) {
	$(document).ready(function(){
    /* necessary to get @ mentions working in the tinyMCE on the forums */
    window.onload = function() { 
          my_timing = setInterval(function(){myTimer();},1000);
          function myTimer() {
            if (typeof window.tinyMCE !== 'undefined' && window.tinyMCE.activeEditor !== null && typeof window.tinyMCE.activeEditor !== 'undefined') {  
    	        $( window.tinyMCE.activeEditor.contentDocument.activeElement )
    				.atwho( 'setIframe', $( '.wp-editor-wrap iframe' )[0] )
    				.bp_mentions( bp.mentions.users );
    			 window.clearInterval(my_timing);
    	    }
          }
          myTimer();
    };

    // necessary to get the custom post-form working
  	//Deregister buddypress built in actions
  	$('#whats-new').off("focus");
  	$('#whats-new-form').off("focusout");
  	$('#whats-new-options').show();
  	$('#aw-whats-new-submit').off('click');

    // This makes new posts submit using JS
  	$('#aw-whats-new-submit').on( 'click', function() {
  		var editor = tinymce.get('whats-new');
  		editor.save();

  		var last_date_recorded = 0,
  			button = $(this),
  			form   = button.closest('form#whats-new-form'),
  			inputs = {}, post_data;

  		// Get all inputs and organize them into an object {name: value}
  		$.each( form.serializeArray(), function( key, input ) {
  			// Only include public extra data
  			if ( '_' !== input.name.substr( 0, 1 ) && 'whats-new' !== input.name.substr( 0, 9 ) ) {
  				if ( ! inputs[ input.name ] ) {
  					inputs[ input.name ] = input.value;
  				} else {
  					// Checkboxes/dropdown list can have multiple selected value
  					if ( ! $.isArray( inputs[ input.name ] ) ) {
  						inputs[ input.name ] = new Array( inputs[ input.name ], input.value );
  					} else {
  						inputs[ input.name ].push( input.value );
  					}
  				}
  			}
  		} );

  		form.find( '*' ).each( function() {
  			if ( $.nodeName( this, 'textarea' ) || $.nodeName( this, 'input' ) ) {
  				$(this).prop( 'disabled', true );
  			}
  		} );

  		// Remove any errors
  		$('div.error').remove();
  		button.addClass('loading');
  		button.prop('disabled', true);
  		form.addClass('submitted');

  		// Default POST values
  		object = '';
  		item_id = $('#whats-new-post-in').val();
  		content = $('#whats-new').val();
  		firstrow = $( '#buddypress ul.activity-list li' ).first();
  		activity_row = firstrow;
  		timestamp = null;

  		// Checks if at least one activity exists
  		if ( firstrow.length ) {

  			if ( activity_row.hasClass( 'load-newest' ) ) {
  				activity_row = firstrow.next();
  			}

  			timestamp = activity_row.prop( 'class' ).match( /date-recorded-([0-9]+)/ );
  		}

  		if ( timestamp ) {
  			last_date_recorded = timestamp[1];
  		}

  		// Set object for non-profile posts
  		if ( item_id > 0 ) {
  			object = $('#whats-new-post-object').val();
  		}

  		post_data = $.extend( {
  			action: 'post_update',
  			'cookie': bp_get_cookies(),
  			'_wpnonce_post_update': $('#_wpnonce_post_update').val(),
  			'content': content,
  			'object': object,
  			'item_id': item_id,
  			'since': last_date_recorded,
  			'_bp_as_nonce': $('#_bp_as_nonce').val() || ''
  		}, inputs );

  		$.post( ajaxurl, post_data, function( response ) {
  			form.find( '*' ).each( function() {
  				if ( $.nodeName( this, 'textarea' ) || $.nodeName( this, 'input' ) ) {
  					$(this).prop( 'disabled', false );
  				}
  			});

  			// Check for errors and append if found.
  			if ( response[0] + response[1] === '-1' ) {
  				form.prepend( response.substr( 2, response.length ) );
  				$( '#' + form.attr('id') + ' div.error').hide().fadeIn( 200 );
  			} else {
  				if ( 0 === $('ul.activity-list').length ) {
  					$('div.error').slideUp(100).remove();
  					$('#message').slideUp(100).remove();
  					$('div.activity').append( '<ul id="activity-stream" class="activity-list item-list">' );
  				}

  				if ( firstrow.hasClass( 'load-newest' ) ) {
  					firstrow.remove();
  				}

  				$('#activity-stream').prepend(response);

  				if ( ! last_date_recorded ) {
  					$('#activity-stream li:first').addClass('new-update just-posted');
  				}

  				if ( 0 !== $('#latest-update').length ) {
  					var l   = $('#activity-stream li.new-update .activity-content .activity-inner p').html(),
  						v     = $('#activity-stream li.new-update .activity-content .activity-header p a.view').attr('href'),
  						ltext = $('#activity-stream li.new-update .activity-content .activity-inner p').text(),
  						u     = '';

  					if ( ltext !== '' ) {
  						u = l + ' ';
  					}

  					u += '<a href="' + v + '" rel="nofollow">' + BP_DTheme.view + '</a>';

  					$('#latest-update').slideUp(300,function(){
  						$('#latest-update').html( u );
  						$('#latest-update').slideDown(300);
  					});
  				}

  				$('li.new-update').hide().slideDown( 300 );
  				$('li.new-update').removeClass( 'new-update' );
  				$('#whats-new').val('');
  				form.get(0).reset();

  				// reset vars to get newest activities
  				newest_activities = '';
  				activity_last_recorded  = 0;
  			}

  			//$('#whats-new-options').slideUp();
  			$('#whats-new-form textarea').animate({
  				height:'2.2em'
  			});
  			$('#aw-whats-new-submit').removeClass('loading');
  			$( '#whats-new-content' ).removeClass( 'active' );
  		});

  		return false;
  	});

    //Copy the values in the tinymce editor over to the textarea so it gets posted with photos (needs to be written like this because the submit button is hidden(or doesn't exist) on page load)
    $("body").on('mousedown', 'input#bpfb_submit', function() {
      $('#whats-new').val(tinymce.activeEditor.getContent());
      
      $(document).ajaxComplete(function (event, xhr, options) {
        if (options.data.indexOf('bpfb_update_activity_contents') >= 0)
          $('form#whats-new-form').get(0).reset();
      });
    });

    /* This opens the actual visual editor when you click on the placeholder */
    $('#show-homepage-whats-new').click(function () {
      $('#homepage-whats-new-mask').css('display','none');
      $('#homepage-whats-new').css('display','block');
      $('#wp-whats-new-editor-container').css('height','247px');
      tinyMCE.activeEditor.focus();
    });
  });
})(jQuery);