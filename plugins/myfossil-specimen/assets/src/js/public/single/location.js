(function($) {
    'use strict';

    function init_map() {
        var mapNode = document.getElementById('fossil-map-container');
        if (!mapNode) return; // bail if no mapNode to make map

        var loc = new google.maps.LatLng(
            parseFloat($('#fossil-location-latitude').text()),
            parseFloat($('#fossil-location-longitude').text())
        );

        var mapOptions = {
            center: loc,
            zoom: 14,
            mapTypeId: google.maps.MapTypeId.SATELLITE
        };

        var marker = new google.maps.Marker({
            position: loc,
            title: $('#fossil-taxon-name').val(),
            clickable: false,
        }).setMap(new google.maps.Map(mapNode, mapOptions));
    }

    function init_location_edit() {
        var keys = ['latitude', 'longitude', 'country', 'state', 'county', 'city', 'not_disclosed'];
        $.map(keys, function(k) {
            $('#edit-fossil-location-' + k).val(
                $('#fossil-location-' + k).data('value')
            );

            $('#edit-fossil-location-' + k).keyup(function() {
                $('#fossil-location-' + k)
                    .text($('#edit-fossil-location-' + k).val())
                    .data('value', $('#edit-fossil-location-' + k).val());
                save_prompt();
            });

        });
    }

    function geocode(place) {
        var address = '';
        if ( place ) {
            if ( place.street_address ) address += place.street_address + " ";
            if ( place.state ) address += place.state + " ";
            if ( place.county ) address += place.county + " County ";
            if ( place.city ) address += place.city + " ";
            if ( place.zip_code ) address += place.zip_code + " ";
            if ( place.country ) address += place.country;
        } 

        return $.ajax({
            url: 'https://maps.googleapis.com/maps/api/geocode/json',
            data: { 
                'address': address
            },
            dataType: 'json',
            success: function( data ) {
                // console.log("Geocode:", place, data);
            },
            error: function ( err ) {
                console.error( err );
            }
        });
    }

    function improve_location() {
        var city           = $( 'input#edit-fossil-location-city' ).val();
        var state          = $( 'input#edit-fossil-location-state' ).val();
        var county         = $( 'input#edit-fossil-location-county' ).val();
        var country        = $( 'input#edit-fossil-location-country' ).val();
        var zip            = $( 'input#edit-fossil-location-zip' ).val();
        var latitude       = $( 'input#edit-fossil-location-latitude' ).val();
        var longitude      = $( 'input#edit-fossil-location-longitude' ).val();

        var place = {
            state: state,
            county: county,
            country: country,
            city: city,
            zip_code: zip
        };

        console.log( place );

        geocode( place )
            .then( function( data ) {
                try {
                  var results = data.results[0];
                  $( 'input#edit-fossil-location-latitude' ).val( results.geometry.location.lat );
                  $('#fossil-location-latitude')
                      .text($('#edit-fossil-location-latitude').val())
                      .data('value', $('#edit-fossil-location-latitude').val());
                  $( 'input#edit-fossil-location-longitude ' ).val( results.geometry.location.lng );
                  $('#fossil-location-longitude')
                      .text($('#edit-fossil-location-longitude').val())
                      .data('value', $('#edit-fossil-location-longitude').val());
                  results.address_components.forEach( function( ac ) {
                      ac.types.forEach( function( t ) {
                          switch ( t ) {
                              case 'locality':
                                  $( 'input#edit-fossil-location-city' ).val( ac.long_name );
                                  $('#fossil-location-city')
                                      .text($('#edit-fossil-location-city').val())
                                      .data('value', $('#edit-fossil-location-city').val());
                                  break;

                              case 'administrative_area_level_1':
                                  $( 'input#edit-fossil-location-state' ).val( ac.long_name );
                                  $('#fossil-location-state')
                                      .text($('#edit-fossil-location-state').val())
                                      .data('value', $('#edit-fossil-location-state').val());
                                  break;

                              case 'postal_code':
                                  $( 'input#edit-fossil-location-zip' ).val( ac.long_name );
                                  $('#fossil-location-zip')
                                      .text($('#edit-fossil-location-zip').val())
                                      .data('value', $('#edit-fossil-location-zip').val());
                                  break;

                              case 'administrative_area_level_2':
                                  $( 'input#edit-fossil-location-county').val( ac.long_name );
                                  $('#fossil-location-county')
                                      .text($('#edit-fossil-location-county').val())
                                      .data('value', $('#edit-fossil-location-county').val());
                                  break;

                              case 'country':
                                  $( 'input#edit-fossil-location-country').val( ac.long_name );
                                  $('#fossil-location-country')
                                      .text($('#edit-fossil-location-country').val())
                                      .data('value', $('#edit-fossil-location-country').val());
                                  break;

                              default:
                                  console.log("Address Component:", t, ac);
                                  break;
                          }
                      });
                  });
                  save_prompt();
              } catch(e) {
                console.warn("Geocode threw error", e);
                return;
              }
            });
    }


    // {{{ save_location 
    function save_location() {
            var nonce = $('#myfossil_specimen_nonce').val();
            var post_id = parseInt($('#post_id').val());
            var loc = {};

            var keys = ['latitude', 'longitude', 'country', 'state', 'county', 'city', 'not_disclosed'];
            $.map(keys, function(k) {
                loc[k] = $('#edit-fossil-location-' + k).val();
            });
            console.log($('#edit-fossil-location-not-disclosed').is(':checked'));
            $.ajax({
                async: false,
                type: 'post',
                url: ajaxurl,
                data: {
                    'action': 'myfossil_save_location',
                    'nonce': nonce,
                    'post_id': post_id,
                    'location': loc,
                    'comment': $('#edit-fossil-location-comment').val(),
                    'not_disclosed': $('#edit-fossil-location-not-disclosed').is(':checked'),
                },
                dataType: 'json',
                success: function(data) {
                    $('#fossil-location-success').show().fadeOut();
                    $('#edit-fossil-location-save-alert').fadeOut();
                      
                },
                complete: function(data) {
                    $('#fossil-location-loading').hide();
                    window.location.reload(true);
                },
                error: function(err) {
                    console.error(err);
                    $('#fossil-location-error').show().fadeOut();
                }
            });

        }
        // }}}

    function save_prompt() {
        $('#edit-fossil-location-save-alert').show();
    }

    function toggle_comment() {
        $('#edit-fossil-location-comment-form-group').toggle();
        $(this).fadeOut(400);
    }

    $(function() {
        google.maps.event.addDomListener(window, 'load', init_map);

        init_location_edit();

        $('#edit-fossil-location-save').click(save_location);
        $('#edit-fossil-location-comment-toggle > button').click(toggle_comment);
        $('#edit-fossil-location-not-disclosed').click(function() {
            save_prompt();
        });

        $('#edit-fossil-location').popup({
            type: 'tooltip',
            opacity: 1,
            background: false,
            transition: 'all 0.2s',
        });

        // Add Geocoding feature to Groups page
        $( '#improve-fossil-location' ).click( improve_location );
    });

}(jQuery));
