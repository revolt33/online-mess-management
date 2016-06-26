var file = null;
var password = readCookie('transact_password');
$(document).ready(function () {
	var locked;
	var editor = CKEDITOR.instances.notice;
	editor.on( 'key', function( evt ){

		var currentLength = editor.getData().length,
	    maximumLength = 5000;
		if( currentLength >= maximumLength ) {
			if ( !locked ) {
			// Record the last legal content.
			editor.fire( 'saveSnapshot' ), locked = 1;
			// Cancel the keystroke.
			evt.cancel();
		} else
			// Check after this key has effected.
			setTimeout( function() {
				// Rollback the illegal one.  
				if( editor.getData().length > maximumLength )
					editor.execCommand( 'undo' );
				else
					locked = 0;
			}, 0 );
	   }
	} );
	$('.content').each(function(){
		if ( $(this).attr('serial') == 1 ) {
			$(this).show();
		}
	});
	$('#no').click(hideDialog);
	$('#yes').click(function() {
		signal = true;
		if ( password != null ) {
			if ( password.getValue() == 'c' ){
				signal = false;
				addNotice();
			}
		}
		if ( signal ) {
			$('#dialog-box').fadeOut(100);
			$('#transaction-box').delay(110).fadeIn('slow', function() {
				$('#password').focus();
			});
		}
	});
	$('#submit').click(function() {
		if( $.trim($('#password').val()).length > 0 ) {
			hideDialog();
			addNotice();
		} else {
			displayFeedback('Transaction password is necessary.');
		}
	});
	$('#dialog-overlay').click(hideDialog).children().each(function() {
		$(this).click(function(e) {
			e.stopPropagation();
		});
	});
	$('.tab').click(function() {
		var serial = $(this).attr('serial');
		$('.tab').removeClass('selected').addClass('border');
		$(this).addClass('selected').removeClass('border');
		$('.content').each(function() {
			if ( $(this).attr('serial') != serial ) {
				$(this).hide();
			} else {
				$(this).fadeIn(200);
			}
		});
	});
	$('#notice_top img').click(function() {
		$('#image').trigger('click');
	});
	$('#image').on('change', function(){
		var newFile = this.files[0];
		if ( $(this).val() != '' ) {
			if ( newFile.type == 'image/jpeg' || newFile.type == 'image/jpg' || newFile.type == 'image/png' ) {
				if ( newFile.size <= 500*1024 ) {
					file = newFile;
					$('#image_feed').text(file.name);
				} else {
					displayFeedback('File size must be less than 0.5 MB.');
				}	
			} else {
				displayFeedback('Allowed image formats are: jpg, jpeg, png.');
			}
				
		}
	});
	$('#notice_button').click(function() {
		heading = $('#heading').val();
		text = CKEDITOR.instances.notice.getData();
		if ( heading.length > 0 ) {
			if ( text.length > 0 ) {
				if ( text.length > 5000 ) {
					displayFeedback('Content exceeds limit: 5000 characters, will be altered.');
				}
				showDialog();
			} else
				displayFeedback('Please provide the content.');
		} else 
			displayFeedback('Please provide the heading.');
	});
	$('.notice_wrap').on('mouseenter', function() {
		$(this).find('.notice_view').stop().animate({ 'top': '150px' }, 400);
	}).on('mouseleave', function() {
		$(this).find('.notice_view').stop().animate({ 'top': '220px' }, 400);
	});
	$('.notice_view').click(function() {
		var image = $(this).parent().attr('imgpath');
		var heading = $(this).next().text();
		var text = $(this).next().next().next().html();
		var str = "";
		if ( image.indexOf('none') >= 0 )
			str = "<h1>"+heading+"</h1><br />"+text+"<br />";
		else
			str = "<h1>"+heading+"</h1><br />"+text+"<br /><img src='"+image+"'/>";
		$('#preview_content').html(str);
		$('#preview').fadeIn();
	});
	$('#get-preview').click(function() {
		var str = CKEDITOR.instances.notice.getData();
		var heading = $('#heading').val();
		if ( str.length > 5000 ) {
			str = str.substring(0, 5000);
		}
		if ( file != null )
			str += "<br /><img />";
		$('#preview_content').html('<h1>'+heading+'</h1><br />'+str);
		if ( file != null ) {
			var oFReader = new FileReader();
			oFReader.readAsDataURL(file);
			oFReader.onload = function (oFREvent) {
				$('#preview_content img').attr('src',oFREvent.target.result);
			};
		}
		$('#preview').fadeIn();
	});
	$('#close_preview').click(function() {
		$('#preview').fadeOut();
	});
	function addNotice() {
		hideAll();
		loadScreen();
		var  formData = new FormData();
		var urgency = 'l';
		$('#notice_top input[type=\'radio\']').each(function() {
			if ( $(this).is(':checked') ) {
				urgency = $(this).val();
				return false;
			}
		});
		var data = {type:{}, values:{}};
		var rememberPassword;
		if ( password != null )
			rememberPassword = password.getValue();
		else {
			if ( $('#check').is(':checked') )
				rememberPassword = 'y';
			else
				rememberPassword = 'n';
		}
		data.type['rememberPassword'] = rememberPassword;
		if ( rememberPassword != 'c' )
			data.values['password'] = $('#password').val();
		formData.append('passdetails', JSON.stringify(data));
		formData.append('heading', heading);
		formData.append('urgency', urgency);
		formData.append('text', text);
		var isPhoto = 'no';
		if ( file != null ) {
			isPhoto = 'yes';
			formData.append('name', file.name);
			formData.append('image', file);
		}
		formData.append('isPhoto', isPhoto);
		$.ajax({
			type: 'POST',
			url: 'process.php',
			data: formData,
			success: function(result) {
				var response = $.parseJSON(result);
				var status = response[0]['status'];
				if ( status == 0 )
					window.location.href = '';
				postProcessing(2, 1, response);
			},
			error: function() {
				hideAll();
				displayFeedback('Connection Error!');
			},
			cache: false,
		 	contentType: false,
			processData: false
		});
	}
	function loadScreen () {
		$('#disable').show();
		$('#feedback').text('Processing...').show();
		load = true;
		$('#load-window').fadeIn();
		startLoadScreen();
	}
	function hideAll() {
		hideDialog();
		$('#disable').hide();
		$('#feedback').hide();
		stopLoadScreen();
	}
	function postProcessing( code, index, response ) {
		if ( (status != code ) && ( ( response[index]['rememberPassword'] == 'y') || ( response[index]['rememberPassword'] == 'c' ) ) ) {
			writeCookie( new Cookie('transact_password', 'c', null ) );
		}
		password = readCookie('transact_password');
		hideAll();
		displayFeedback(response[0]['msg']);
	}
});