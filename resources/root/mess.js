var file = null;
var mode = 'none';
var add = false;
var addMsg = "";
var load = false;
$(document).ready(function () {
	$('#cssmenu li.active').addClass('open').children('ul').show();
	$('#cssmenu li.has-sub>a').bind('click', accordion);
	$('#logout').click(function() {
		eraseCookie('username');
		eraseCookie('remember');
		eraseCookie('type');
	});
	function startLoadScreen() {
		$('#floating-bar').css( 'left', -90 );
		if ( load == true )
			$('#floating-bar').animate({ left: "+=290px" }, { duration: 1300, easing: "linear", complete: startLoadScreen });
	}
	function stopLoadScreen() {
		load = false;
		$('#load-window').fadeOut('fast');
	}
	$('#addMess').click(function() {
		add = true;
		addMess().done(function(data) {
			$('#display').html(data);
			$('#imageForm').bind('submit', function() {
				return false;
			});
			$('#mess').bind('submit', function() {
				return false;
			});
			$('#add').bind('click', function() {
				mode = 'add';
				checkMess();
			});
		}).fail(function() {
			$('#feedback').text('Error encountered, try later.');
		});
	});
	$('#yes').click(function() {
		if ( mode =='save' ) {
			saveMess().done(function(data) {
				response(data);
			}).fail(function() {
				$('#feedback').text('Failed to save.').fadeIn('fast').delay(1000).fadeOut('slow');
			});
		} else if ( mode == 'delete' ) {
			deleteMess().done(function(data) {
				$('#disable').hide();
				$('#feedback').hide();
				stopLoadScreen();
				response(data);
			}).fail(function() {
				$('#feedback').text('Failed to delete.').fadeIn('fast').delay(1000).fadeOut('slow');
			});
		} else if ( mode == 'add' ) {
			saveAddMess().done(function(data) {
				var response = $.parseJSON(data);
				var status = response['response'][0]['status'];
				var msg = response['response'][0]['msg'];
				var serial;
				if ( status == 0 ) {
					serial = response['response'][1]['serial'];
					$('#serial').val(serial);
					addMsg = msg;
					uploadImage();
				} else {
					$('#feedback').text(msg).show().delay(1000).fadeOut('slow');
				}
			}).fail(function() {
				$('#feedback').text('Failed to add.').fadeIn('fast').delay(1000).fadeOut('slow');
			});
		}
		hideDialog();
	});
	$(document).on('keyup', '.feed', function() {
		remaining($(this));
	});
	$(document).on('focus', '.feed', function() {
		var element = $(this);
		var max_length = element.attr('len');
		var span = $('#rem_'+element.attr('rem'));
		var current = element.val().length;
		var remaining = max_length - current;
		span.text(''+remaining+' characters left.');
		span.parent().slideDown('slow');
	});
	$('#no').click(hideDialog);
	$(document).on('change', '#uploadImage' , function() {
		var value = $(this).val();
		var newFile = this.files[0];
		var feedback = "";
		if ( value != '' ) {
			var name = newFile.name;
			var type = newFile.type;
			var size = newFile.size;
			if ( type == 'image/jpeg' || type == 'image/jpg' || type == 'image/png' ) {
				if ( size <= (512 * 1024) ) {
					file = newFile;
					var oFReader = new FileReader();
					oFReader.readAsDataURL(newFile);
					oFReader.onload = function (oFREvent) {
						$('#frame img').attr('src',oFREvent.target.result);
						if ( !add )
							setTimeout(function() {
								$('#upload').slideDown(300);
							}, 700);
					};
				} else {
					feedback = "File size cannot excced 0.5 MB";
				}
			} else {
				feedback = "Only image file can be uploaded.";
			}
			if ( feedback.length > 0 ) {
				$('#feedback').text(feedback).fadeIn('fast');
				setTimeout(function() {
					$('#feedback').fadeOut('slow').text('');
				} , 2000);
			}
		} else {
			$('#upload').slideUp(300);
		}
	});
	$(document).on('focus', '#pass', function() {
		$(this).attr('type', 'text');
	});
	$(document).on('blur', '#pass', function() {
		$(this).attr('type', 'password');
	});
	$(document).on('blur', '.feed', function() {
		var span = $('#rem_'+$(this).attr('rem'));
		span.parent().slideUp('slow');
		span.text('');
	});
	$('.view').bind('click', function() {
		add = false;
		var serial = $(this).attr('serial');
		viewMess(serial).done(function(data) {
			$('#display').html(data);
			$('#imageForm').bind('submit', function() {
				return false;
			});
			$('#mess').bind('submit', function() {
				return false;
			});
			$('#delete').bind('click', function() {
				$('#dialog-head').text('Alert');
				$('#dialog-content').text('Are you sure you want to delete this mess?');
				mode = 'delete';
				showDialog();
			});
			$('#status').bind('change', function() {
				if ( $(this).is(':checked') ) {
					$(this).val('e');
					$(this).next().text('(Enabled)');
				} else {
					$(this).val('d');
					$(this).next().text('(Disabled)');
				}
			});
			$('#save').bind('click', function() {
				mode = 'save';
				checkMess();
			});
			$('#upload').bind('click', uploadImage);
		}).fail(function() {
			$('#display').html('Loading failed.');
		});
	});
	$('.view').each(function() {
		$(this).trigger('click');
		return false;
	});
	function remaining(element) {
		var max_length = element.attr('len');
		var span = $('#rem_'+element.attr('rem'));
		var current = element.val().length;
		var remaining = max_length - current;
		span.text(''+remaining+' characters left.');
	}
	function viewMess(serial) {
		return $.ajax({
			type:'POST',
			url: 'viewMess.php',
			data: 'serial='+serial,
			cache: false
		});
	}
	function accordion() {
		$(this).removeAttr('href');
		var element = $(this).parent('li');
		if (element.hasClass('open')) {
			element.removeClass('open');
			element.find('li').removeClass('open');
			element.find('ul').slideUp(200);
		}
		else {
			element.addClass('open');
			element.children('ul').slideDown(200);
			element.siblings('li').children('ul').slideUp(200);
			element.siblings('li').removeClass('open');
			element.siblings('li').find('li').removeClass('open');
			element.siblings('li').find('ul').slideUp(200);
		}
	}
	function uploadImage() {
		var formData = new FormData();
		var serial = $('#serial').val();
		formData.append('serial', serial);
		formData.append('file', file);
		if ( !add )
			$('#progress').slideDown('slow');
		$.ajax({
        	url: 'saveImage.php',
        	type: 'POST',
        	xhr: function() {  // Custom XMLHttpRequest
            	var myXhr = $.ajaxSettings.xhr();
            	if ( !add )
	            	if(myXhr.upload){ // Check if upload property exists
	                	myXhr.upload.addEventListener('progress',progressHandlingFunction, false); // For handling the progress of the upload
	           		}
            	return myXhr;
        	},
        	//Ajax events
        	success: function(response) {
        		console.log(response)
        		if ( add ) {
        			$('#disable').hide();
        			stopLoadScreen();
        			$('#feedback').text(addMsg).show().delay(1000).fadeOut('slow');
        			setTimeout(function(){

        				window.location = 'mess.php';
        			}, 1300);
        		} else {
        			uploaded();
        		}
        	},
        	error: uploadFailed,
        	// Form data
        	data: formData,
        	//Options to tell jQuery not to process data or worry about content-type.
        	cache: false,
        	contentType: false,
       		processData: false
    	});
	}
	function progressHandlingFunction(e){
    	if(e.lengthComputable){
        	$('progress').attr({value:e.loaded,max:e.total});
    	}
	}
	function uploadFailed() {
		$('#disable').hide();
		$('#feedback').text('Image upload failed.').fadeIn('fast');
		setTimeout(function () {
			$('#feedback').text('').fadeOut('slow');
		}, 2000);
	}
	function uploaded() {
		$('#feedback').text('Image uploaded.').fadeIn('fast');
		$('#progress').slideUp(200);
		setTimeout(function () {
			$('#feedback').text('').fadeOut('slow');
		}, 2000);
	}
	function checkMess() {
		var name = $('#name').val();
		var detail = $('#detail').val();
		var password = $('#pass').val();
		if ( (name.length != 0) && ( detail.length != 0 ) && ( password.length != 0 ) ) {
			if ( mode == 'save' ) {
				$('#dialog-head').text('Alert!');
				$('#dialog-content').text('Save changes to mess?');
				showDialog();
			} else if ( mode == 'add' ) {
				if ( file == null ) {
					$('#feedback').text('Please select an image first.').fadeIn('fast').delay(1000).fadeOut('slow');
				} else {
					$('#dialog-head').text('Alert');
					$('#dialog-content').text('Do you want to add this Mess?');
					showDialog();
				}
			}
		} else {
			$('#feedback').text('All fields are required.').fadeIn('fast').delay(1000).fadeOut('slow');
		}
	}
	function showDialog() {
		$('#dialog-overlay').slideDown('fast');
		$('#dialog-box').fadeIn(500);
	}
	function hideDialog() {
		$('#dialog-box').fadeOut('fast');
		$('#dialog-overlay').slideUp('fast');
	}
	function saveMess() {
		var name = $('#name').val();
		var detail = $('#detail').val();
		var password = $('#pass').val();
		var status ='d';
		if ( $('#status').is(':checked') ) {
			status = 'e';
		} else {
			status = 'd';
		}
		var serial = $('#form_serial').val();
		var data = {type:{}, values:{}};
		data.type['name'] = 'edit';
		data.values['serial'] = serial;
		data.values['name'] = name;
		data.values['detail'] = detail;
		data.values['password'] = password;
		data.values['status'] = status;
		json = JSON.stringify(data);
		return $.ajax({
			type: 'POST',
			url: 'processMess.php',
			data: 'data='+json,
			cache: false
		});
	}
	function deleteMess() {
		$('#disable').show();
		$('#feedback').text('Processing please wait...').fadeIn('fast');
		load = true;
		$('#load-window').fadeIn();
		startLoadScreen();
		var serial = $('#form_serial').val();
		var data = {type:{}, values:{}};
		data.type['name'] = 'delete';
		data.values['serial'] = serial;
		json = JSON.stringify(data);
		return $.ajax({
			type: 'POST',
			url: 'processMess.php',
			data: 'data='+json
		});
	}
	function response(data) {
		var response = $.parseJSON(data);
		var status = response['response'][0]['status'];
		var msg = response['response'][0]['msg'];
		$('#feedback').text(msg).fadeIn('fast').delay(1000).fadeOut('slow');
		if ( status == 0 ) {
			setTimeout(function() {
				window.location = 'mess.php';
			} , 1300);
		}
	}
	function addMess() {
		return $.ajax({
			type: 'POST',
			url: 'addMess.php',
			data: 'request=addMess'
		});
	}
	function saveAddMess() {
		$('#disable').show();
		load = true;
		$('#load-window').fadeIn();
		startLoadScreen();
		$('#feedback').text('Processing please wait...').fadeIn('fast');
		var name = $('#name').val();
		var detail = $('#detail').val();
		var password = $('#pass').val();
		var data = {type:{}, values:{}};
		data.type['name'] = 'add';
		data.values['name'] = name;
		data.values['detail'] = detail;
		data.values['password'] = password;
		json = JSON.stringify(data);
		return $.ajax({
			type: 'POST',
			url: 'processMess.php',
			data: 'data='+json
		});
	}
});