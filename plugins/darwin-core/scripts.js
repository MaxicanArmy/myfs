
jQuery(document).ready(function($) {

  var action = $('#action').val();

  function sendFileToServer(formData,status) {
    formData.append('upload_media_for_dwc_specimen_nonce', $('#upload_media_for_dwc_specimen_nonce').val());
    formData.append('action', $('#upload_media_action').val());
    formData.append('dwc_specimen_id', $('#dwc_specimen_id').val());

    var extraData ={}; //Extra Data.
    var jqXHR=$.ajax({
      xhr: function() {
        var xhrobj = $.ajaxSettings.xhr();
        if (xhrobj.upload) {
          xhrobj.upload.addEventListener('progress', function(event) {
            var percent = 0;
            var position = event.loaded || event.position;
            var total = event.total;
            if (event.lengthComputable) {
              percent = Math.ceil(position / total * 100);
            }
            //Set progress
            status.setProgress(percent);
          }, false);
        }
        return xhrobj;
      },
      url: ajaxurl,
      type: "POST",
      contentType:false,
      processData: false,
      cache: false,
      data: formData,
      success: function(response){
        if( !response.success ) {
          console.log(response);
          status.setProgress(0);
          alert( response.data[0].message );
        } else  {
          window.location.href= "/dwc-specimen/" + response.data;
          /*
          if (action == "ac_media_upload_dwc_specimen") {
            location.reload();  //maybe click the update button instead. this will reload page and not lose any changes the user has made but not saved
          }
          else if (action == "ac_media_upload_dwc_wizard") {
            console.log(response);
            $('#dwc_specimen_id').val(response.data);
            $("#step-1-continue").prop('disabled', false);
            $("#post_title").val("Specimen " + response.data);
          }
          else if (action == "ac_media_upload_wizard") {
            $('#ac_media_id').val(response);
            $("#step-1-continue").prop('disabled', false);
            $("#post_title").val("Media " + response.data);
          }
          */
        }
      },
      error: function(response) {
          console.log(response);
          status.setProgress(0);
          alert( response.responseJSON.data[0].message );
      }
    });

    status.setAbort(jqXHR);
  }

  var rowCount=0;

  function createStatusbar(obj) {
    rowCount++;
    var row="odd";
    if(rowCount %2 ==0) 
      row ="even";
    
    this.statusbar = $("<div class='statusbar "+row+"'></div>");
    this.filename = $("<div class='filename'></div>").appendTo(this.statusbar);
    this.size = $("<div class='filesize'></div>").appendTo(this.statusbar);
    this.progressBar = $("<div class='progressBar'><div></div></div>").appendTo(this.statusbar);
    this.abort = $("<div class='abort'>Abort</div>").appendTo(this.statusbar);
    obj.after(this.statusbar);
   
      this.setFileNameSize = function(name,size) {
      var sizeStr="";
      var sizeKB = size/1024;
      if(parseInt(sizeKB) > 1024) {
        var sizeMB = sizeKB/1024;
        sizeStr = sizeMB.toFixed(2)+" MB";
      }
      else {
        sizeStr = sizeKB.toFixed(2)+" KB";
      }
       
      this.filename.html(name);
      this.size.html(sizeStr);
    }
    this.setProgress = function(progress) {       
      var progressBarWidth =progress*this.progressBar.width()/ 100;

      this.progressBar.find('div').animate({ width: progressBarWidth }, 10).html(progress + "% ");
      if(parseInt(progress) >= 100) {
        this.abort.hide();
      }
    }
    this.setAbort = function(jqxhr) {
      var sb = this.statusbar;
      this.abort.click(function() {
        jqxhr.abort();
        sb.hide();
      });
    }
  }

  function handleFileUpload(file,obj) {
    var fd = new FormData();
    fd.append('dwc_media_file', file);

    var status = new createStatusbar(obj); //Using this we can set progress.
    status.setFileNameSize(file.name,file.size);
    sendFileToServer(fd,status);
  }

  var obj = $("#dwc-dragndrop");
  obj.on('dragenter', function (e) {
    e.stopPropagation();
    e.preventDefault();
    $(this).css('border', '2px solid #0B85A1');
  });

  obj.on('dragover', function (e) {
    e.stopPropagation();
    e.preventDefault();
  });

  obj.on('drop', function (e) {
    $(this).css('border', '2px dotted #0B85A1');
    e.preventDefault();
    var files = e.originalEvent.dataTransfer.files;

    if (files[0].size > maxUploadSize) {
      alert("File size exceeds max upload size of " + (maxUploadSize / (1024*1024)) + " MB.");
      return;
    }
    else {
      //We need to send dropped files to Server
      handleFileUpload(files[0],obj);
    }
  });

  $(document).on('dragenter', function (e) {
    e.stopPropagation();
    e.preventDefault();
  });

  $(document).on('dragover', function (e) {
    e.stopPropagation();
    e.preventDefault();
    obj.css('border', '2px dotted #0B85A1');
  });

  $(document).on('drop', function (e) {
    e.stopPropagation();
    e.preventDefault();
  });
});

jQuery(document).ready(function($) {
  /*
  $("#darwin-core-create-specimen").click( function() {
    var data = {
      'action': 'dwc_create_specimen',
      'dwc_create_specimen_nonce': $( '#dwc_create_specimen_nonce' ).val()
    };

    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    jQuery.post(ajaxurl, data, function(response) {
      window.location.href="/dwc-specimen/" + response;
    });
  });
  */

  $("#darwin-core-delete-specimen").click( function() {
    if (window.confirm("Are you sure?")) {
      var data = {
        'action': 'delete_dwc_specimen',
        'delete_dwc_specimen_nonce': $( '#delete_dwc_specimen_nonce' ).val(),
        'dwc_specimen_id': $( '#dwc_specimen_id' ).val()
      };

      $.ajax({
        type: "POST",
        url: ajaxurl,
        data: data,
        success: function(response){
          if( !response.success ) {
            console.log(response);
          } else  {
            window.location.href= "/dwc-specimen/";
          }
        },
        error: function(response) {
            console.log(response);
            alert( response.responseJSON.data[0].message );
        }
      });
    } 
  });

  var formInitialState = $('#dwc-specimen-update-form').serialize();
  var updateMsg = $("#darwin-core-update-msg");

  $('#dwc-specimen-update-form').change( function() {
    if (formInitialState !== $('#dwc-specimen-update-form').serialize() ) {
      updateMsg.html('There are unsaved changes.')
        .addClass('dwc-error')
        .removeClass('dwc-success')
        .css('display', 'inline-block');
    }
    else 
      $("#darwin-core-update-msg").html('');
  });

  $("#darwin-core-update-specimen").click( function() {
    var data = $('#dwc-specimen-update-form').serialize();
    updateMsg.html('Processing update... <img src="' + localizedVars.loadingGifURL + '" />')
      .addClass('dwc-info')
      .removeClass('dwc-error')
      .removeClass('dwc-success');

    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: data,
      success: function(response){
        if( !response.success ) {
          console.log(response);
        } else  {
          updateMsg.html('Update Successful!')
            .addClass('dwc-success')
            .removeClass('dwc-info')
            .fadeOut(5000);
        }
      },
      error: function(response) {
          console.log(response);
          updateMsg.html( response.responseJSON.data[0].message )
            .addClass('dwc-error')
            .removeClass('dwc-info')
          alert( response.responseJSON.data[0].message );
      }
    });
  });

  $("#dwc-upload-media-url").click( function() {
    var data = {
        'action': 'upload_media_url_for_dwc_specimen',
        'upload_media_url_for_dwc_specimen_nonce': $( '#upload_media_url_for_dwc_specimen_nonce' ).val(),
        'dwc_specimen_id': $( '#dwc_specimen_id' ).val(),
        'media_url': $('#dwc-media-url').val()
      };

    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: data,
      success: function(response){
        if( !response.success ) {
          console.log(response);
        } else  {
          window.location.href= "/dwc-specimen/" + response.data;
        }
      },
      error: function(response) {
          console.log(response);
          alert( response.responseJSON.data[0].message );
      }
    });
  });
});

(function($) {
    'use strict';

    function dwc_init_map() {
        var mapNode = document.getElementById('dwc-fossil-map-container');
        if (!mapNode) return; // bail if no mapNode to make map
        var lat = $('#Location_decimalLatitude').val();
        var long = $('#Location_decimalLongitude').val();
        
        if (lat == '' || long == '') {
          $( "div#dwc-fossil-map-container" ).html('<p>Latitude and Longitude must be set to display the location map.</p>');
          return;
        }

        var loc = new google.maps.LatLng(
            parseFloat(lat),
            parseFloat(long)
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

    function dwc_geocode(place) {
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
                console.log("Geocode:", place, data);
            },
            error: function ( err ) {
                console.error( err );
            }
        });
    }

    function dwc_improve_location() {
        var city           = $( '#darwin-core input#Location_locality' ).val();
        var state          = $( '#darwin-core input#Location_stateProvince' ).val();
        var county         = $( '#darwin-core input#Location_county' ).val();
        var country        = $( '#darwin-core input#Location_country' ).val();
        //var zip            = $( '#darwin-core input#Location_zip' ).val();
        var latitude       = $( '#darwin-core input#Location_decimalLatitude' ).val();
        var longitude      = $( '#darwin-core input#Location_decimalLongitude' ).val();

        var place = {
            state: state,
            county: county,
            country: country,
            city: city
        };

        console.log( place );

        dwc_geocode( place )
            .then( function( data ) {
                try {
                  var results = data.results[0];
                  $( '#darwin-core input#Location_decimalLatitude' ).val( results.geometry.location.lat );
                  /*$('#fossil-location_latitude')
                      .text($('#edit-fossil-location-latitude').val())
                      .data('value', $('#edit-fossil-location-latitude').val());*/
                  $( '#darwin-core input#Location_decimalLongitude' ).val( results.geometry.location.lng );
                  /*$('#fossil-location-longitude')
                      .text($('#edit-fossil-location-longitude').val())
                      .data('value', $('#edit-fossil-location-longitude').val());*/
                  results.address_components.forEach( function( ac ) {
                      ac.types.forEach( function( t ) {
                          switch ( t ) {
                              case 'locality':
                                  $( '#darwin-core input#Location_locality' ).val( ac.long_name );
                                  /*$('#fossil-location-city')
                                      .text($('#edit-fossil-location-city').val())
                                      .data('value', $('#edit-fossil-location-city').val());*/
                                  break;

                              case 'administrative_area_level_1':
                                  $( '#darwin-core input#Location_stateProvince' ).val( ac.long_name );
                                  /*$('#fossil-location-state')
                                      .text($('#edit-fossil-location-state').val())
                                      .data('value', $('#edit-fossil-location-state').val());*/
                                  break;

                              case 'postal_code':
                                  $( '#darwin-core input#Location_zip' ).val( ac.long_name );
                                  /*$('#fossil-location-zip')
                                      .text($('#edit-fossil-location-zip').val())
                                      .data('value', $('#edit-fossil-location-zip').val());*/
                                  break;

                              case 'administrative_area_level_2':
                                  $( '#darwin-core input#Location_county').val( ac.long_name );
                                  /*$('#fossil-location-county')
                                      .text($('#edit-fossil-location-county').val())
                                      .data('value', $('#edit-fossil-location-county').val());*/
                                  break;

                              case 'country':
                                  $( '#darwin-core input#Location_country').val( ac.long_name );
                                  /*$('#fossil-location-country')
                                      .text($('#edit-fossil-location-country').val())
                                      .data('value', $('#edit-fossil-location-country').val());*/
                                  break;

                              default:
                                  console.log("Address Component:", t, ac);
                                  break;
                          }
                      });
                      dwc_init_map();
                  });
                  //save_prompt();
              } catch(e) {
                console.warn("Geocode threw error", e);
                return;
              }
            });
    }

    $(function() {
        google.maps.event.addDomListener(window, 'load', dwc_init_map);

        // Add Geocoding feature to Groups page
        $( '#dwc-improve-fossil-location' ).click( dwc_improve_location );
    });

}(jQuery));

(function ($) {
  "use strict";

  var ranks = ["common", "kingdom", "phylum", "class", "order", "family", "genus", "species"];

  function dwc_load_taxa(taxon_name) {
    var url = "//paleobiodb.org/data1.1/taxa/list.json?name=" +
      taxon_name + "&rel=all_parents&vocab=pbdb";

    $.ajax({
      type: "post",
      url: url,
      dataType: "json",
      success: function (resp) {
        resp.records.forEach(function (taxon) {
          taxon = dwc_normalize_taxon(taxon);
          $("#darwin-core #Taxon_" + taxon.rank)
            .val(taxon.taxon_name);
        });
      },
      complete: function (data) {
        
      },
      error: function (err) {
        
      }
    });
  }

  function dwc_get_taxon_img(taxon_no) {
    if (taxon_no <= 0) return;
    var url = "//paleobiodb.org/data1.1/taxa/single.json" +
      "?show=img&vocab=pbdb&id=" + taxon_no;
    var img_url = "//paleobiodb.org/data1.1/taxa/thumb.png?id=";
    var img = $("<img />")
      .addClass("phylopic");
    // Query the PBDB with the taxon id.
    $.ajax({
      url: url,
      type: "GET",
      dataType: "json",
      success: function (data) {
        var taxon = data.records.pop();
        if (taxon.image_no) {
          img.attr("src", img_url + taxon.image_no);
        }
      }
    });
    return img;
  }

  function dwc_set_taxon(taxon) {
    dwc_reset_taxa();
    $("#darwin-core #Taxon_" + taxon.rank)
      .val(taxon.taxon_name);
    dwc_load_taxa(taxon.taxon_name);
  }

  function dwc_reset_taxa() {
    $.map(ranks, function (rank) {
      $("#darwin-core #Taxon_" + rank).val("");
    });
  }

  function dwc_get_confirmation_div(taxon) {
    return $("<div />")
      .attr("id", "edit-fossil-taxon-confirmation")
      .attr("class", "alert alert-danger")
      .css("margin-top", "10px")
      .css("max-width", "215px")
      .append(
          $("<p />")
            .text("This will overwrite your currently defined taxonomy!")
      )
      .append(
          $("<button />")
            .attr("id", "overwrite-button")
            .attr("class", "btn btn-default btn-sm form-control")
            .append(
              $("<i />")
                .attr("class", "fa fa-fw fa-exclamation-triangle")
            )
            .append(
              $("<span />")
                .text("Overwrite")
            )
            .click(function () {
              $("ul#edit-fossil-taxon-results").empty();
              $("#edit-fossil-taxon-confirmation").remove();
              $("#improve-fossil-taxon").popup('hide');
              dwc_set_taxon(taxon);
            })
      );
  }

  function dwc_autocomplete_taxon() {
    var input = this;

    // PBDB auto-complete requires least 3 characters before returning a
    // response.
    if (parseInt($(input)
        .val()
        .length) < 3) return;

    // Auto-complete unordered list.
    var ul = $("ul#edit-fossil-taxon-results");

    // @todo Make the PBDB URL some kind of constant.
    var url = "//paleobiodb.org/data1.1/taxa/auto.json" +
      "?limit=20&vocab=pbdb&name=" + $(this)
      .val();

    var results = [];

    // Query the PBDB with the current taxon name partial.
    $.ajax({
      url: url,
      type: "GET",
      dataType: "json",
      success: function (data) {
        // Remove current taxa from the auto-complete list.
        ul.empty();

        // foreach taxon result from the auto-complete
        $.map(data.records, function (taxon) {
          taxon = dwc_normalize_taxon(taxon);

          // Filter out misspellings.
          if (!!taxon.misspelling) return true;

          // Deduplicate
          if ($.inArray(taxon.taxon_name, results) !== -1)
            return true;
          else
            results.push(taxon.taxon_name);

          // Build list item, including phylopic.
          var taxon_li = $("<li></li>")
            .addClass("hover-hand")
            .append(dwc_get_taxon_img(taxon.taxon_no))
            .append(" ")
            .append(taxon.taxon_name)
            .click(function () {
              $("#edit-fossil-taxon-confirmation").remove();
              $(this).append(dwc_get_confirmation_div(taxon));
            });

          // Add list item to the results.
          ul.append(taxon_li);
        });
      },
      error: function (err) {
        //console.log(err);
      }
    });
  }

  $(function () {
    $("#dwc-edit-fossil-taxon-name")
      .keyup(dwc_autocomplete_taxon);

    $("#improve-fossil-taxon")
      .popup({
        type: "tooltip",
        opacity: 1,
        background: false,
        transition: "all 0.2s"
      });

    $("#dwc-improve-fossil-taxon-open").click(function() {
      $("#dwc-improve-fossil-taxon").popup('show');
    });
  });

  function dwc_normalize_taxon(taxon) {
    if (taxon.rank) taxon.rank = _dwc_taxon_normalize_rank(taxon.rank);
    else taxon.rank = taxon.taxon_rank;
    if (taxon.taxon_rank) taxon.taxon_rank = _dwc_taxon_normalize_rank(
      taxon.taxon_rank);
    else taxon.taxon_rank = taxon.rank;
    return taxon;
  }

  function _dwc_taxon_normalize_rank(rank) {
    var _rank = rank.split("");
    if (_rank.slice(0, 3) === "sub") return _rank.slice(3)
      .join("");
    if (_rank.slice(0, 4) === "infra") return _rank.slice(4)
      .join("");
    if (_rank.slice(0, 5) === "super") return _rank.slice(5)
      .join("");
    return rank;
  }

})(jQuery);



(function($) {
    'use strict';

    var DWC_SCALES = {
        1: 'earliestEonOrLowestEonothem',
        2: 'earliestEraOrLowestErathem',
        3: 'earliestPeriodOrLowestSystem',
        4: 'earliestEpochOrLowestSeries',
        5: 'earliestAgeOrLowestStage'
    };

    var DWC_SCALES_NAMES = ['eon', 'era', 'period', 'epoch', 'age'];

    var GEO_DATA;

    var selected = false;

    function dwc_reset_geochronology() {
        $.map(DWC_SCALES_NAMES, function(level) {
            $('#fossil-geochronology-' + level).html(
                '<span class="unknown">Unknown</span>'
            )
            .css('background-color', '')
            .css('color', '');
        });
    }

    function dwc_load_geochronology() {
        var url = "//paleobiodb.org/data1.1/intervals/list.json" +
            "?scale=1&vocab=pbdb";

        $.ajax({
            type: 'post',
            url: url,
            dataType: 'json',

            /**
             * Re-organize results from the PBDB.
             * 
             * Certain features are not currently supported by PBDB, so
             * that's why we're doing it here in the client.
             */
            success: function(resp) {
                GEO_DATA = resp.records;

                // Sort data by age ascending
                GEO_DATA.sort(function(a, b) {
                    if (a.early_age < b.early_age)
                        return 1;
                    if (a.early_age > b.early_age)
                        return -1;
                    return 0;
                });

                /* Load the data into the interface */
                dwc_populate_geochronology_ui(GEO_DATA);

                /* Load the data into the select box */
                dwc_populate_geochronology_select(GEO_DATA);

                /* Let everyone know that we're good to go... */
                $('#fossil-geochronology-success').show().fadeOut();
            },
            complete: function(data) {
                $('#fossil-geochronology-loading').hide();
            },
            error: function(err) {
                console.log(err);
                $('#fossil-geochronology-error').show().fadeOut();
            }
        });
    }

    function dwc_populate_geochronology_ui(data) {
        var intervals = [], match = false, highest_level = 0;

        data.forEach(function(interval) {
            intervals[interval.interval_no] = interval;
            if ($('#darwin-core #Geochronology_'+DWC_SCALES[interval.level]).val() === interval.interval_name && interval.level > highest_level) {
              highest_level = interval.level;
              match = interval.interval_no;
            }
        });
        
        /**
         * Populate parents of the time interval
         */
        var current_interval = match ? intervals[match] : null;
        selected = match ? intervals[match].interval_name : false;
        while (current_interval) {
            $('#darwin-core #Geochronology_' + DWC_SCALES[current_interval.level])
                .val(current_interval.interval_name)
                .css('background-color', current_interval.color)
                .css('color', (parseInt(current_interval.color.slice(1), 16) > 0xffffff / 2) ? '#000' : '#fff');
            current_interval = intervals[current_interval.parent_no];
        }
    }

    function dwc_populate_geochronology_select(data) {
        var select = $('select#dwc-edit-fossil-geochronology').empty();

        var optgroups = {}, scale_label;
        optgroups[0] = $('<optgroup />').attr('label', 'Unknown');
        for (var level = 1; level <= 5; level++) {
            scale_label = DWC_SCALES[level].charAt(0).toUpperCase() + DWC_SCALES[level].slice(1);
            optgroups[level] = $('<optgroup />').attr('label', scale_label);
        };

        var default_option;
        default_option = $('<option></option>')
            .val(0)             //maybe make null or Unknown?
            .text('Unknown')
            .data('color', null)
            .data('early_age', null)
            .data('late_age', null)
            .data('pbdbid', null)
            .data('parent_pbdbid', null)
            .data('reference_pbdbid', null)
            .data('level', null)
            .data('name', null);

        optgroups[0].append(default_option);

        data.forEach(function(time_interval) {
            var option = $('<option></option>')
                .val(time_interval.interval_name)
                .text(time_interval.interval_name)
                .data('color', time_interval.color)
                .data('early_age', parseFloat(time_interval.early_age))
                .data('late_age', parseFloat(time_interval.late_age))
                .data('pbdbid', time_interval.interval_no)
                .data('parent_pbdbid', time_interval.parent_no)
                .data('reference_pbdbid', time_interval.reference_no.pop())
                .data('level', DWC_SCALES[time_interval.level])
                .data('name', time_interval.interval_name);

            // Add to optgroup
            optgroups[time_interval.level].append(option);
        });

        for (var level in optgroups) {
            select.append(optgroups[level]);
        }

        if (selected) {
            $('select#dwc-edit-fossil-geochronology option[value="' + selected + '"]').attr('selected','selected');
        } 

        select.change(function() {
            var option = $('select#dwc-edit-fossil-geochronology option:selected');
            $('#darwin-core #Geochronology_' + option.data('level')).val(option.data('name'));

            //reset_geochronology();
            $('div#improve-fossil-geochronology').popup("hide");
            dwc_populate_geochronology_ui(GEO_DATA);
            //save_prompt();
        });
    }
    
    $(function() {
        dwc_load_geochronology();

        //$('#dwc-edit-fossil-geochronology-save').click(save_geochronology);
        //$('#dwc-edit-fossil-geochronology-comment-toggle > button').click(toggle_comment);

        $('#improve-fossil-geochronology').popup({
            type: 'tooltip',
            opacity: 1,
            background: false,
            transition: 'all 0.2s',
        });
    });

}(jQuery));