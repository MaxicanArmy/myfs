jQuery(document).ready(function($) {

	$("#ac-media-upload-url").click( function() {
		var data = {
			'action': 'upload_ac_media_url',
			'upload_ac_media_url_nonce': $( '#upload_ac_media_url_nonce' ).val(),
			'media_url': $('#ac_media_url').val()
		};

		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: data,
			success: function(response){
				if( !response.success ) {
					console.log(response);
				} else  {
					window.location.href= "/ac-media/" + response.data;
				}
			},
			error: function(response) {
				console.log(response);
				alert( response.responseJSON.data[0].message );
			}
		});
	});

	$("#audubon-core-update-media").click( function() {
		var data = $('#ac-media-update-form').serialize();

		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: data,
			success: function(response){
				if( !response.success ) {
					console.log(response);
				} else  {
					//window.location.href= "/ac-media/" + response.data;
				}
			},
			error: function(response) {
				console.log(response);
				alert( response.responseJSON.data[0].message );
			}
		});
	});

	$("#audubon-core-delete-media").click( function() {
		if (window.confirm("Are you sure?")) {
			var data = {
				'action': 'delete_ac_media',
				'delete_ac_media_nonce': $( '#delete_ac_media_nonce' ).val(),
				'ac_media_id': $( '#ac_media_id' ).val()
			};

			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: data,
				success: function(response){
					if( !response.success ) {
						console.log(response);
					} else  {
						window.location.href= "/ac-media/";
					}
				},
				error: function(response) {
					console.log(response);
					alert( response.responseJSON.data[0].message );
				}
			});
		} 
	});
/*
  $("#audubon-core-upload-url").click( function() {
    var data = {
      'action': 'ac_media_upload_resource_url',
      'ac_media_ajax_upload_resource_url_nonce': $( '#ac_media_ajax_upload_resource_url_nonce' ).val(),
      'dwc_specimen_id': $( '#dwc_specimen_id' ).val(),
      'resource_url' : $( '#ac-resource-url').val()
    };

    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    jQuery.post(ajaxurl, data, function(response) {
    	if( !response.success ) {
    		alert( response.data[0].message );
    	} else {
	    	location.reload();
    	}
    });
  });

  $("#audubon-core-delete-media").click( function() {
    var data = {
      'action': 'ac_media_delete',
      'ac_media_delete_nonce': $( '#ac_media_delete_nonce' ).val(),
      'ac_media_id': $( '#ac_media_id' ).val()
    };

    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    jQuery.post(ajaxurl, data, function(response) {
      if (response > 0) {
        //window.location.href= "/dwc_specimen/" + response;
        window.location.href= "/ac-media";
      }
      else if (response == 0) {
        window.location.href= "/ac-media";
      }
      else {
        alert('An error has occurred while processing your request.');
        return;
      }
    });
  });
  */
	$('.slick-holder').slick({
		draggable : false,
		swipe : false,
		touchMove : false
	});

	$('.slick-holder-3').slick({
		draggable : false,
		swipe : false,
		touchMove : false,
		slidesToShow: 3
	});
/*
	$("#step-1-continue").on('click', function() {
		$('div#step-1-wrapper').css('display', 'none');
		$('div#step-2-wrapper').css('display', 'block');

		if ($('input#resource_url_viewable').val() !== "") {
			$('input#resource_url').val($('input#resource_url_viewable').val());
		}
	});
*/
	$('input#resource_url_viewable').on('input', function() {
		var url_regex = /^(?:(?:(?:https?|ftp):)?\/\/)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})).?)(?::\d{2,5})?(?:[/?#]\S*)?$/i;
		var url_test = $('input#resource_url_viewable').val();
		if (url_regex.test(url_test) || url_regex.test("https://" + url_test) || url_regex.test("http://" + url_test)) {
			$("#step-1-continue").prop('disabled', false);
		}
		else {
			$("#step-1-continue").prop('disabled', true);
		}
	});
/*
	$("#audubon-core.wizard #step-2-continue").on('click', function() {
		$('div#step-2-wrapper').css('display', 'none');
		$('div#step-3-wrapper').css('display', 'block');
	});
	*/
});

jQuery(document).ready(function($) {

	var action = $('#ac_media_action').val();

	function sendFileToServer(formData,status) {
		formData.append('upload_ac_media_nonce', $('#upload_ac_media_nonce').val());
		formData.append('action', $('#upload_media_action').val());

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
					window.location.href= "/ac-media/" + response.data;
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
		fd.append('ac_media_file', file);

		var status = new createStatusbar(obj); //Using this we can set progress.
		status.setFileNameSize(file.name,file.size);
		sendFileToServer(fd,status);
	}

	var obj = $("#ac-dragndrop");
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

	var action = $('#ac_media_action').val() + "_thumb";

	function sendFileToServer(formData,status) {
		formData.append('upload_ac_media_thumb_nonce', $('#upload_ac_media_thumb_nonce').val());
		//formData.append('ac_media_update_nonce', $('#ac_media_update_nonce').val());
		formData.append('action', 'upload_ac_media_thumb');
		//formData.append('dwc_specimen_id', $('#dwc_specimen_id').val());
		formData.append('ac_media_id', $('#ac_media_id').val());

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
			success: function(data){
		        if( !response.success ) {
					console.log(response);
					status.setProgress(0);
					alert( response.data[0].message );
		        } else  {
					//window.location.href= "/ac-media/" + response.data;
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
		fd.append('ac_media_file_thumb', file);

		var status = new createStatusbar(obj); //Using this we can set progress.
		status.setFileNameSize(file.name,file.size);
		sendFileToServer(fd,status);
	}

	var obj = $("#ac-dragndrop-thumb");
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