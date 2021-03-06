jQuery(document).ready(function($) {
	var origin_class = 0;

  $("#dwc-ingest-myfossil").click( function() {
    var data = $('#dwc-ingest-myfossil-form').serialize();

    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: data,
      success: function(response){
        if( !response.success ) {
          console.log(response);
        } else  {
          console.log(response);
        }
      },
      error: function(response) {
          console.log(response);
          alert( response.responseJSON.data[0].message );
      }
    });
  });

  $("#dwc-publish-image-specimens").click( function() {
    var data = $('#dwc-publish-image-specimens-form').serialize();
		console.log(data);
    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: data,
      success: function(response){
        if( !response.success ) {
          console.log(response);
        } else  {
          console.log(response);
        }
      },
      error: function(response) {
          console.log(response);
          alert( response.responseJSON.data[0].message );
      }
    });
  });

  $("#dwc-create-bp-activity").click( function() {
    var data = $('#dwc-create-bp-activity-form').serialize();
		console.log(data);
    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: data,
      success: function(response){
        if( !response.success ) {
          console.log(response);
        } else  {
          console.log(response);
        }
      },
      error: function(response) {
          console.log(response);
          alert( response.responseJSON.data[0].message );
      }
    });
  });

  $("#dwc-set-specimen-post-content").click( function() {
    var data = $('#dwc-set-specimen-post-content-form').serialize();
		console.log(data);
    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: data,
      success: function(response){
        if( !response.success ) {
          console.log(response);
        } else  {
          console.log(response);
        }
      },
      error: function(response) {
          console.log(response);
          alert( response.responseJSON.data[0].message );
      }
    });
  });

	$( 'table.dwc-terms tbody' ).sortable({
		connectWith: 'table.dwc-terms tbody',
        start: function(event, ui) {
            origin_class = $(this).attr("id");
        },
	    receive: function(event, ui) {
            $(this).find('input.layout-parent').val($(this).attr("id"));

            $( '#'+$(this).attr("id") ).children('tr.dwc-term-placeholder').remove();

            var count = 0;
            var dst_highest = 0;
            $( '#'+$(this).attr("id") ).children('tr').each(function () {
            	$(this).find('input.layout-order').val(++count);
            	dst_highest = count+1;
			});
			$( 'input.new-layout-order-'+$(this).attr("id") ).val(dst_highest);
	    },
	    stop: function(event, ui) {
            var count = 0;
            var src_highest = 0;
            $( '#'+origin_class ).children('tr').each(function () {
            	$(this).find('input.layout-order').val(++count);
            	src_highest = count+1;
			});
			$( 'input.new-layout-order-'+origin_class ).val(src_highest);

			if ($( '#'+origin_class ).children().length == 0) {
				$( '#'+origin_class ).append("<tr class='dwc-term-placeholder'><td></td><td></td><td></td><td></td></tr>");
			}
	    }
    }).disableSelection();


	$( 'ul.class-sort' ).sortable({
		connectWith: 'ul.class-sort',
	    stop: function(event, ui) {
            var count = 1;
            for (col_count=0; col_count < 3; col_count++) {
            	count = 1;
	            $( '#col-'+col_count ).children('li').each(function () {
	            	$(this).find('input.layout').val(col_count+','+count++);
				});
	        }
	    }
    }).disableSelection();
});
