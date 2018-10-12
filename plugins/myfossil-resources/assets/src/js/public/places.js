( function( $ ) {
    'use strict';

    var places_cache;

    var icon_url = "/wp-content/themes/myfossil/static/img/map/";
    var ch = {
        'other'           : icon_url + 'marker-Other.png',
        'city-park'       : icon_url + 'marker-City-Park.png',
        'fossil-club'     : icon_url + 'marker-Club.png',
        'collecting-site' : icon_url + 'marker-Collecting-Site.png',
        'group'           : icon_url + 'marker-Interest-Group.png',
        'museum'          : icon_url + 'marker-Museum.png',
        'national-park'   : icon_url + 'marker-National-Park.png',
        'organization'    : icon_url + 'marker-Organization.png',
        'society'         : icon_url + 'marker-Society.png',
        'state-park'      : icon_url + 'marker-State-Park.png'
    };

    var markers = {};
    for ( var k in ch ) {
        markers[k] = [];
    }


    // {{{ get_places 
    function get_places() {
        if ( places_cache ) 
            return places_cache;

        var nonce = $( '#myfossil_resources_filter_nonce' ).val(); 
        var json = null;

        $.ajax({
            async: false,
            type: 'post',
            url: ajaxurl,
            data: { 
                'action': 'myfossil_resources_list_places',
                'nonce': nonce,
            },
            dataType: 'json',
            success: function( data ) {
                json = data;
                //console.log("Places:", json);
            },
            error: function ( err ) {
                console.error( err );
            }
        });

        places_cache = json;
        return json;
    }
    // }}}

    // {{{ init_places
    function init_places() {
        var tpl_src = $( '#tpl-places' ).html();
        var tpl = Handlebars.compile( tpl_src );
        $( '#places-list' ).html( tpl( { 'places': get_places() } ) );
    }
    // }}}

    // {{{ init_places_filters_state
    function init_places_filters_state() {
        var tpl;
        var nonce = $( '#myfossil_resources_filter_nonce' ).val(); 

        // toggle loading
        $( '#state-filter' ).prop( 'disabled', true );
        $( '#state-filter' ).append( '<option id="loading-states">Loading...</option>' ); 

        $.ajax({
            type: 'post',
            url: ajaxurl,
            data: { 
                'action': 'myfossil_resources_list_states',
                'nonce': nonce,
            },
            dataType: 'json',
            success: function( states ) {
                $( '#state-filter' ).append( '<option>United States</option>' );

                states.forEach( function( state ) {
                    tpl = '<option value="' + state + '">' + state + '</option>';
                    $( '#state-filter' ).append( tpl );
                });

                $( '#state-filter' ).prop( 'disabled', false );
                $( '#loading-states' ).remove();
            },
            error: function( err ) {
                console.log( err );
                $( '#loading-states' ).text( 'Error' );
            }
        });

        return 1;
    }
    // }}}

    // {{{ init_places_filters_type
    function init_places_filters_type() {
        var tpl;
        var nonce = $( '#myfossil_resources_filter_nonce' ).val(); 

        // toggle loading
        $( '#types-selected' ).append( 
                '<div id="loading-types"><i class="fa fa-fw fa-spinner fa-spin"></i> Loading...</div>' );

        $.ajax({
            type: 'post',
            url: ajaxurl,
            data: { 
                'action': 'myfossil_resources_list_types',
                'nonce': nonce,
            },
            dataType: 'json',
            success: function( types ) {
                types.forEach( function( type ) {
                    tpl = '<div class="checkbox"><label>';
                    tpl += '<input type="checkbox" value="' + type + '" checked="checked">';
                    tpl += type + '</label></div>';
                    $( '#types-selected' ).append( tpl );
                });

                $( '#loading-types' ).remove();
            },
            error: function( err ) {
                console.log( err );
                $( '#loading-types' ).html( '<i class="fa fa-fw fa-warning"></i> Error' );
            }
        });

        return 1;
    }
    // }}}

    // {{{ filter_places
    function filter_places() {
        var tpl,
            tpl_state = '[data-place-state="%state%"]',
            tpl_types = '[data-place-type="%type%"]',
            state = $( '#state-filter' ).val();

        // Reset, hide all.
        $( 'div.place' ).hide();

        // Hide map markers
        for ( var k in markers ) {
            markers[k].forEach( function( m ) {
                m.setVisible(false);
            } );
        }

        // Filter by type and state where ( type && state ).
        $( 'input:checkbox:checked' )
            .map( function() { return this.value; } )
            .get()
            .forEach(
                function( type ) { 
                    tpl = 'div';
                    if ( state !== 'United States' )
                        tpl += tpl_state.replace( /%state%/, state );
                    tpl += tpl_types.replace( /%type%/, type );
                    $( tpl ).show();

                    // show markers
                    markers[type].forEach( function( m ) {
                        if ( state == 'United States' || state == m.state ) {
                            m.setVisible(true);
                        }
                    } );
                }
            );
    }
    // }}}
    
    // {{{ geocode
    function geocode(place) {
        var address = '';
        if ( place ) {
            if ( place.street_address ) address += place.street_address + " ";
            if ( place.state ) address += place.state + " ";
            if ( place.city ) address += place.city + " ";
            if ( place.zip_code ) address += place.zip_code;
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
    // }}}

    // {{{ init_places_map
    function init_places_map() {
        var places = get_places();

        var mapOptions = {
                center: { 
                    lat: 39.50, 
                    lng: -98.35
                },
                zoom: 4
            };

        var map = new google.maps.Map( document.getElementById("map-canvas"), mapOptions );
	
        var prevInfoWindow;

        // Add a marker for each place on the map.
        places.forEach(
            function( place ) {
                // geocode(place);

                // Create an info pop-up window for the marker.
                var info = new google.maps.InfoWindow(
                        { content: place.content }
                    );

                // Produce the marker on the map.
                var marker = new google.maps.Marker(
                        {
                            position: {
                                lat: parseFloat( place.latitude ),
                                lng: parseFloat( place.longitude )
                            },
                            map: map,
                            title: place.title,
                            icon: {
                                url: ch[ place.type ],
                            },
                            state: place.state
                        }
                    );

                // Track this marker for filtering
                markers[place.type].push( marker );

                // Show additional information when clicked.
                ( function( marker, place ) {
                    google.maps.event.addListener( marker, 'click', function() {
                        if( prevInfoWindow ) {
                            prevInfoWindow.close();
                        }

                        info.setContent( 
                            '<h3>' + place.name + '</h3>' +
                            '<p>' + place.description + '</p>' 
                        ),

                        info.open( map, marker );
                        prevInfoWindow = info;
                    } );
                } )( marker, place );
            }
        );
    }
    // }}}

    // {{{ clear_place_filters
    function clear_place_filters() {
        $('#clear-filters').click(function() {
            // Reset state dropdown
            $('#state-filter').val($('#state-filter option:first').val());

            // Reset type checkboxes
            var checkboxes = $('#types-selected').find('input');
            checkboxes.each(function() {
                $(this).prop('checked', true);
            });

            // Trigger update of Places
            filter_places();
        });
    }
    // }}}

    function improve_location() {
        var street_address = $( 'input#street_address' ).val();
        var city           = $( 'input#city' ).val();
        var state          = $( 'input#state' ).val();
        var zip            = $( 'input#zip' ).val();
        var latitude       = $( 'input#latitude' ).val();
        var longitude      = $( 'input#longitude' ).val();

        var place = {
            street_address: street_address,
            state: state,
            city: city,
            zip_code: zip
        };

        geocode( place )
            .then( function( data ) {
                var results = data.results[0];
                $( 'input#latitude' ).val( results.geometry.location.lat );
                $( 'input#longitude ' ).val( results.geometry.location.lng );
                $( 'input#street_address' ).val( results.formatted_address );
                results.address_components.forEach( function( ac ) {
                    ac.types.forEach( function( t ) {
                        switch ( t ) {
                            case 'locality':
                                $( 'input#city' ).val( ac.long_name );
                                break;

                            case 'administrative_area_level_1':
                                $( 'input#state' ).val( ac.long_name );
                                break;

                            case 'postal_code':
                                $( 'input#zip' ).val( ac.long_name );
                                break;

                            default:
                                // console.log("Address Component:", ac);
                                break;
                        }
                    });
                });

            });
    }

    $( function() {
        if ( !! $( '#places-list' ).length ) {
            // Initialize Places
            init_places();
            
            // Setup filters and listeners
            init_places_filters_state();

            init_places_filters_type();  // the extra function call for issue #8

            $( '#state-filter' ).change( filter_places );
            $( '#types-selected' ).on( 'click', 'input[type=checkbox]', filter_places );

            // Load Google Map with markers
            google.maps.event.addDomListener( window, 'load', init_places_map );

            // Initialize Place filters
            clear_place_filters();
        }

        // Add Geocoding feature to Groups page
        if ( !! $( '#improve-location' ).length ) {
            $( '#improve-location' ).click( improve_location );
        }
    } );

}( jQuery ) );
