( function( $ ) {
    'use strict';

    // {{{ get_events
    function get_events() {
        var nonce = $( '#myfossil_resources_filter_nonce' ).val(); 
        var json = null;

        $.ajax({
            async: false,
            type: 'post',
            url: ajaxurl,
            data: { 
                'action': 'myfossil_resources_list_events',
                'nonce': nonce,
            },
            dataType: 'json',
            success: function( data ) {
                console.log( '-- begin events --' );
                console.log( data );
                console.log( '-- end events --' );
                json = data;
            },
            error: function ( err ) {
                console.log( err );
            }
        });

        return json;
    } 
    // }}}

    // {{{ init_events
    function init_events() {
        var tpl_src = $( '#tpl-events' ).html();
        var tpl = Handlebars.compile( tpl_src );
        var nonce = $( '#myfossil_resources_filter_nonce' ).val(); 

        // Populate Events list
        $( '#events-list' ).html( tpl( get_events() ) );

        // {{{ Start date picker
        $( '#start-date-picker' ).datepicker({
            onSelect: function() {
                var json;

                $.ajax({ 
                    type: 'post',
                    url: ajaxurl,
                    data: { 
                            'action': 'myfossil_resources_filter_start_date',
                            'nonce': nonce,
                            'start_date': $('#start-date-picker').val(),
                            'end_date': $('#end-date-picker').val()
                        },
                    dataType: 'json',
                    success: function(response) {
                            console.log(response);
                            $('#events-list').html(tpl(response));
                        },
                    failure: function(response) {
                            console.log('failure');
                            console.log(response);
                        },
                    error: function(jqXHR, textStatus, errorThrown) {
                            console.log(jqXHR);
                            console.log(textStatus);
                            console.log(errorThrown);
                        }
                });
            }
        });
        // }}}

        // {{{ End date picker
        $( '#end-date-picker' ).datepicker({
            onSelect: function() {
                var json;

                $.ajax({ 
                    type: 'post',
                    url: ajaxurl,
                    data: { 
                        'action': 'myfossil_resources_filter_end_date',
                        'nonce': nonce,
                        'start_date': $('#start-date-picker').val(),
                        'end_date': $('#end-date-picker').val()
                    },
                    dataType: 'json',
                    success: function(response) {
                            console.log(response);
                            $('#events-list').html(tpl(response));
                        },
                    failure: function(response) {
                            console.log('failure');
                            console.log(response);
                        },
                    error: function(jqXHR, textStatus, errorThrown) {
                            console.log(jqXHR);
                            console.log(textStatus);
                            console.log(errorThrown);
                        }
                });
            }
        });
        // }}}

    }
    // }}}

    // {{{ filter_events
    function filter_events() {
        var tpl,
            tpl_state = '[data-event-state="%state%"]',
            tpl_types = '[data-event-type="%type%"]',
            tpl_dates = '[data-event-date="%date%"]',
            state = $( '#state' ).val(),
            dt = $( '#month-year' ).val();

        // Reset, hide all.
        $( 'div.event' ).hide();

        // Filter by type and state where ( type && state ).
        $( 'input:checkbox:checked' )
            .map( function() { return this.value; } )
            .get()
            .forEach(
                function( type ) { 
                    tpl = 'div';
                    if ( state !== 'United States' )
                        tpl += tpl_state.replace( /%state%/, state );
                    if ( dt !== 'All time' )
                        tpl += tpl_dates.replace( /%date%/, dt );
                    tpl += tpl_types.replace( /%type%/, type.toLowerCase() );
                    console.log( tpl );
                    $( tpl ).show();
                }
            );
    }
    // }}}

    // {{{ init_events_filters_state
    function init_events_filters_state() {
        var tpl;
        var nonce = $( '#myfossil_resources_filter_nonce' ).val(); 

        // toggle loading
        $( '#state' ).prop( 'disabled', true );
        $( '#state' ).append( '<option id="loading-states">Loading...</option>' ); 

        $.ajax({
            type: 'post',
            url: ajaxurl,
            data: { 
                'action': 'myfossil_resources_list_events_states',
                'nonce': nonce,
            },
            dataType: 'json',
            success: function( states ) {
                console.info("States:", states);
                $( '#state' ).append( '<option>United States</option>' );
                states.forEach( function( state ) {
                    tpl = '<option value="' + state + '">' + state + '</option>';
                    $( '#state' ).append( tpl );
                });
                $( '#state' ).prop( 'disabled', false );
                $( '#loading-states' ).remove();
            },
            error: function( err ) {
                console.error( err );
                $( '#loading-states' ).text( 'Error' );
            }
        });

        return 1;
    }
    // }}}

    // {{{ init_events_filters_month_year
    function init_events_filters_month_year() {
        var tpl;
        var nonce = $( '#myfossil_resources_filter_nonce' ).val(); 

        // toggle loading
        $( '#month-year' ).prop( 'disabled', true );
        $( '#month-year' ).append( '<option id="loading-dates">Loading...</option>' ); 

        $.ajax({
            type: 'post',
            url: ajaxurl,
            data: { 
                'action': 'myfossil_resources_list_events_month_years',
                'nonce': nonce,
            },
            dataType: 'json',
            success: function( resp ) {
                $( '#month-year' ).append( '<option>All time</option>' );
                console.log( resp );

                for ( var key in resp ) {
                    if ( resp.hasOwnProperty( key ) ) {
                        tpl = '<option value="' + key + '">' + resp[key] + '</option>';
                        $( '#month-year' ).append( tpl );
                    }
                }
                $( '#month-year' ).prop( 'disabled', false );
                $( '#loading-dates' ).remove();
            },
            error: function( err ) {
                console.log( err );
                $( '#loading-dates' ).text( 'Error' );
            }
        });

        return 1;
    }
    // }}}

    // {{{ init_events_filters_type
    function init_events_filters_type() {
        var tpl;
        var nonce = $( '#myfossil_resources_filter_nonce' ).val(); 

        // toggle loading
        $( '#types-selected' ).append( 
                '<div id="loading-types"><i class="fa fa-fw fa-spinner fa-spin"></i> Loading...</div>' );

        $.ajax({
            type: 'post',
            url: ajaxurl,
            data: { 
                'action': 'myfossil_resources_list_events_types',
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
                $( '#loading-types' ).text( '<i class="fa fa-fw fa-warning"></i> Error' );
            }
        });

        return 1;
    }
    // }}}

    // {{{ init_events_map
    function init_events_map() {
        var events = get_events().events;

        var icon_url = "http://maps.google.com/mapfiles/ms/icons/";
        var ch = {
                "Meeting"       : icon_url + "red-dot.png",
                "Workshop"      : icon_url + "blue-dot.png",
                "Lecture"       : icon_url + "green-dot.png",
                "Other"         : icon_url + "yellow-dot.png",
            };

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
        events.forEach(
            function( event ) {
                // Create an info pop-up window for the marker.
                var info = new google.maps.InfoWindow(
                        { content: event.content }
                    );

                // Produce the marker on the map.
                var marker = new google.maps.Marker(
                        {
                            position: {
                                lat: parseFloat( event.place[0].latitude ),
                                lng: parseFloat( event.place[0].longitude )
                            },
                            map: map,
                            title: event.title,
                            icon: ch[ event.type ],
                        }
                    );

                // Show additional information when clicked.
                ( function( marker, event ) {
                    google.maps.event.addListener( marker, 'click', 
                            function() {
				if(prevInfoWindow) 
				    prevInfoWindow.close();
                                info.setContent( 
                                    '<h3>' + event.title + '</h3>' +
                                    '<p>' + event.content + '</p>' 
                                ),
                                info.open( map, marker );
				prevInfoWindow = info;
                            }
                        );
                } )( marker, event );
            }
        );
    }
    // }}}

    // {{{ clear_event_filters
    function clear_event_filters() {
        $('#clear-filters').click(function() {
            // Reset state and date dropdowns
            $('#state').val($('#state option:first').val());
            $('#month-year').val($('#month-year option:first').val());

            // Reset type checkboxes
            var checkboxes = $('#types-selected').find('input');
            checkboxes.each(function() {
                $(this).prop('checked', true);
            });

            // Trigger update of Events
            filter_events();
        });
    }
    // }}}

    $( function() {
        if ( !! $( '#events-list' ).length ) {
            // Initialize Events
            init_events();

            // Setup filters
            init_events_filters_state();
            init_events_filters_type();
            init_events_filters_month_year();

            // Setup listeners
            $( '#state' ).change( filter_events );
            $( '#month-year' ).change( filter_events );
            $( '#types-selected' ).on( 'click', 'input[type=checkbox]', filter_events );

            // Load up Google Map with markers
            // google.maps.event.addDomListener(window, 'load', init_events_map);
       
            // Reset filters
            clear_event_filters();
        }
    } );

}( jQuery ) );
