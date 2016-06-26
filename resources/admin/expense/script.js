var mode = 'none';
var signal = false;
var file = null;
var password = readCookie('transact_password');
$(document).ready(function () {
	$('.tab').click(function() {
		var serial = $(this).attr('serial');
		$(this).parent().children('.tab').removeClass('selected').addClass('border');
		$(this).addClass('selected').removeClass('border');
		$(this).parent().next().children('.content').each(function() {
			if ( $(this).attr('serial') != serial ) {
				$(this).hide();
			} else {
				$(this).fadeIn(200);
			}
		});
	});
	$('#dialog-overlay').click(function() {
		hideDialog();
	}).children().each(function() {
		$(this).click(function(e) {
			e.stopPropagation();
		});
	});
	$(document).on('mouseenter', '.hover', function() {
		$('#hovertext').stop().text($(this).attr('hovertext')).fadeIn();
	});
	$(document).on('mousemove', '.hover', function(o) {
		$('#hovertext').css('top', o.pageY + 12).css('left', o.pageX + 12);
	});
	$(document).on('mouseleave', '.hover', function() {
		$('#hovertext').stop().fadeOut();
	});
	$(document).on( 'click', '.image', function() {
		$('#image-viewer').fadeIn('fast');
		loadScreen();
		$('#image-viewer img').removeAttr('src');
		$('#image-viewer img').attr('src', $(this).attr('location')).load(stopLoadScreen);
	});
	$('#close-button').click(function() {
		stopLoadScreen();
		$('#image-viewer').fadeOut('fast');
	});
	$(document).on( 'mouseenter', '.row', function() {
		$(this).find('.hover').stop().fadeIn('fast');
	});
	$(document).on('mouseleave', '.row', function() {
		$(this).find('.hover').stop().fadeOut('fast');
	});
	$('#exp_image_form').submit(function() {
		return false;
	});
	$('#no').click(function() {
		hideDialog();
	});
	$('#yes').click(function() {
		signal = true;
		if ( password != null ) {
			if ( password.getValue() == 'c' ){
				signal = false;
				hideDialog();
				if ( mode == 'addWage' )
					addWage();
				else if ( mode == 'addExp' )
					addExp();
				else if ( mode == 'addMisc' )
					addMisc();
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
			if ( mode == 'addWage' )
				addWage();
			else if ( mode == 'addExp' )
				addExp();
			else if ( mode == 'addMisc' )
				addMisc();
		} else {
			displayFeedback('Transaction password is necessary.');
		}
	});
	$('.hostler').on('change', function() {
		if( $(this).is(':checked') )
			$(this).next().text('(Yes)');
		else
			$(this).next().text('(No)');
	});
	$('#exp_image_select').click(function() {
		$('#exp_image').trigger('click');
	});
	$('#exp_image').change(function() {
		if ( $(this).val() != '' ) {
			var newFile = this.files[0];
			var name = newFile.name;
			var type = newFile.type;
			var size = newFile.size;
			if ( type == 'image/jpeg' || type == 'image/jpg' || type == 'image/png' ) {
				if ( size <= 512*1024 ) {
					file = newFile;
					var oFReader = new FileReader();
					oFReader.readAsDataURL(newFile);
					oFReader.onload = function (oFREvent) {
						$('#exp_image_feed img').attr('src',oFREvent.target.result);
					};
				} else
					displayFeedback('Image size limit: 0.5MB')
			} else
				displayFeedback('Allowed image format: jpeg, jpg, png')
		}
	});
	$('#add_misc').click(function() {
		if ( $.trim($('#misc_title').val()).length > 0 )
			if ( (isNaN($.trim($('#misc_amount').val())) == false) || ( $.trim($('#misc_amount')).val() > 0 ) ) {
				mode = 'addMisc';
				$('#dialog-head').text('Alert!');
				$('#dialog-content').text('Do you want to add this expense?');
				showDialog();
			} else
				displayFeedback('Please provide valid amount.');
		else
			displayFeedback('Please provide the title for expense.');
	});
	$('#add_wage').click(function() {
		mode = 'addWage';
		$('#dialog-head').text('Alert!');
		$('#dialog-content').text('Do you want to add wage?');
		showDialog();
	});
	$('#add_exp').click(function() {
		if ( $.trim($('#exp_title').val()).length > 0 )
			if ( (isNaN($.trim($('#exp_amount').val())) == false) || ( $.trim($('#exp_amount')).val() > 0 ) )
				if ( file != null ) {
					mode = 'addExp';
					$('#dialog-head').text('Alert!');
					$('#dialog-content').text('Do you want to save this bill?');
					showDialog();
				} else
					displayFeedback('Please provide an image of bill.');
			else
				displayFeedback('Please provide valid amount.');
		else
			displayFeedback('Please provide title for expense.');
	});
	$('#name').on('change', function() {
		var data = {type:{},values:{}};
		data.type['name'] = 'emp_details';
		data.values['id'] = $(this).val();
		var json = JSON.stringify(data);
		$.ajax({
			url: 'process.php',
			type: 'POST',
			data: 'data='+json,
			success: function(result) {
				var response = $.parseJSON(result);
				var status = response['response'][0]['status'];
				if ( status == 1 ) {
					$('#post').text(response['response'][1]['post']);
					$('#salary').text(response['response'][1]['salary']);
				}
			}
		});
	});
	$('.notify').focus(function() {
		$('#'+$(this).attr('id')+'_feed').text(($(this).attr('maxlength') - $(this).val().length)+' characters left');
		$('#'+$(this).attr('id')+'_feed').parent().slideDown();
	}).blur(function() {
		$('#'+$(this).attr('id')+'_feed').parent().slideUp();
	}).keyup(function() {
		$('#'+$(this).attr('id')+'_feed').text(($(this).attr('maxlength') - $(this).val().length)+' characters left');
	});
	function addExp() {
		$('#disable').show();
		$('#feedback').text('Processing...').show();
		loadScreen();
		var rememberPassword;
		if ( password == null ) {
			if ( $('#check').is(':checked') )
				rememberPassword = 'y';
			else
				rememberPassword = 'n';
		} else {
			rememberPassword = 'c';
		}
		var data = {type:{},values:{}};
		data.type['name'] = 'add_exp';
		data.type['rememberPassword'] = rememberPassword;
		if ( rememberPassword != 'c' )
			data.values['password'] = $('#password').val();
		$('#password').val('');
		data.values['title'] = $('#exp_title').val();
		data.values['amount'] = $('#exp_amount').val();
		if ( $('#exp_hostler').is(':checked') )
			data.values['hostler'] = 'y';
		else
			data.values['hostler'] = 'n';
		data.values['file'] = file.type;
		var json = JSON.stringify(data);
		$.ajax({
			type: 'POST',
			url: 'process.php',
			data: 'data='+json,
			success: function(result) {
				var response = $.parseJSON(result);
				var status = response['response'][0]['status'];
				var msg = response['response'][0]['msg'];
				if ( status != 0 ) {
					$('#disable').hide();
					stopLoadScreen();
					$('#feedback').text('').hide();
					displayFeedback(msg);
				}
				if ( status == 0 ) {
					$('#exp_title').val('');
					$('#exp_amount').val('');
					$('#exp_hostler').attr('checked', false);
					uploadPhoto(response['response'][1]['image']);
				} else if ( status == 2 ) {
					showTransactionBox();
					$('#password').focus();
				}
				if ( (status == 0 || status == 1 ) && ( ( response['response'][2]['rememberPassword'] == 'y') || ( response['response'][2]['rememberPassword'] == 'c' ) ) ) {
					writeCookie( new Cookie('transact_password', 'c', null ) );
				}
				password = readCookie('transact_password');
			},
			error: errorDisplay
		});
	}
	function uploadPhoto(image) {
		$('#feedback').text('Uploading Image');
		var formData = new FormData();
		formData.append('name', image);
		formData.append('image', file);
		$.ajax({
			url: 'saveImage.php',
			type: 'POST',
			data: formData,
			success: function(result) {
				file = null;
				if ( mode == 'addExp' ) {
					$('#exp_image_feed img').attr('src', '');
					$('#exp_form').slideUp();
					getData();
				}
				$('#feedback').hide().text('');
				displayFeedback(result);
				$('#disable').hide();
				stopLoadScreen();
			},
			error: errorDisplay,
			cache: false,
		 	contentType: false,
			processData: false
		});
	}
	function addMisc () {
		$('#disable').show();
		$('#feedback').text('Processing...').show();
		loadScreen();
		var comment = $('#misc_comment').val();
		var rememberPassword;
		if ( $.trim(comment).length == 0 )
			comment = "No Comments";
		if ( password == null ) {
			if ( $('#check').is(':checked') )
				rememberPassword = 'y';
			else
				rememberPassword = 'n';
		} else {
			rememberPassword = 'c';
		}
		var data = {type:{},values:{}};
		data.type['name'] = 'add_misc';
		data.type['rememberPassword'] = rememberPassword;
		if ( rememberPassword != 'c' )
			data.values['password'] = $('#password').val();
		$('#password').val('');
		data.values['title'] = $('#misc_title').val();
		data.values['amount'] = $('#misc_amount').val();
		data.values['comment'] = comment;
		if ( $('#misc_hostler').is(':checked') )
			data.values['hostler'] = 'y';
		else
			data.values['hostler'] = 'n';
		var json = JSON.stringify(data);
		$.ajax({
			url: 'process.php',
			type: 'POST',
			data: 'data='+json,
			success: function(result) {
				var response = $.parseJSON(result);
				var status = response['response'][0]['status'];
				var msg = response['response'][0]['msg'];
				if ( status == 0 ) {
					$('#misc_title').val('');
					$('#misc_amount').val('');
					$('#misc_comment').val('');
					$('#misc_comment').attr('checked', false).next().text('(No)');
					$('#misc_form').slideUp();
					getData();
				} else if ( status == 2 ) {
					showTransactionBox();
					$('#password').focus();
				}
				if ( (status == 0 || status == 1 ) && ( ( response['response'][1]['rememberPassword'] == 'y') || ( response['response'][1]['rememberPassword'] == 'c' ) ) ) {
					writeCookie( new Cookie('transact_password', 'c', null ) );
				}
				password = readCookie('transact_password');
				stopLoadScreen();
				$('#disable').hide();
				$('#feedback').text('').hide();
				displayFeedback(msg);
			},
			error: errorDisplay
		});
	}
	function addWage() {
		$('#disable').show();
		$('#feedback').text('Processing...').show();
		loadScreen();
		var comment = $.trim($('#wage_comment').val());
		var rememberPassword;
		if ( $.trim(comment).length == 0 )
			comment = "No Comments";
		if ( password == null ) {
			if ( $('#check').is(':checked') )
				rememberPassword = 'y';
			else
				rememberPassword = 'n';
		} else {
			rememberPassword = 'c';
		}
		var data = {type:{},values:{}};
		data.type['name'] = 'add_wage';
		data.type['rememberPassword'] = rememberPassword;
		if ( rememberPassword != 'c' )
			data.values['password'] = $('#password').val();
		$('#password').val('');
		data.values['id'] = $('#name').val();
		data.values['comment'] = comment;
		if ( $('#wage_hostler').is(':checked') )
			data.values['hostler'] = 'y';
		else
			data.values['hostler'] = 'n';
		var json = JSON.stringify(data);
		$.ajax({
			url: 'process.php',
			type: 'POST',
			data: 'data='+json,
			success: function(result) {
				var response = $.parseJSON(result);
				var status = response['response'][0]['status'];
				var msg = response['response'][0]['msg'];
				if ( status == 0 ) {
					$('#wage_comment').val('');
					$('#wage_hostler').attr('checked', false).next().text('(No)');
					$('#wage_form').slideUp();
					getData();
				} else if ( status == 2 ) {
					showTransactionBox();
					$('#password').focus();
				}
				if ( (status == 0 || status == 1 ) && ( ( response['response'][1]['rememberPassword'] == 'y') || ( response['response'][1]['rememberPassword'] == 'c' ) ) ) {
					writeCookie( new Cookie('transact_password', 'c', null ) );
				}
				password = readCookie('transact_password');
				stopLoadScreen();
				$('#disable').hide();
				$('#feedback').text('').hide();
				displayFeedback(msg);
			},
			error: errorDisplay
		});
	}
	function getData() {
		var data = {type:{}};
		data.type['name'] = 'get_data';
		var json = JSON.stringify(data);
		$.ajax({
			url: 'process.php',
			type: 'POST',
			data: 'data='+json,
			success: function(result) {
				console.log(result);
				var response = $.parseJSON(result);
				var indices = response['indices'];
				var values = response['values'];
				$('#data').children('.content').each(function() {
					var element = $(this);
					for( var index in indices ) {
						if ( indices[index] == element.attr('serial') ) {
							element.html(values[indices[index]]);
							break;
						}
					}
				});
			}
		});
	}
	function errorDisplay() {
		$('#disable').hide();
		$('#feedback').hide();
		stopLoadScreen();
		displayFeedback('Connection Error!');
	}
	function loadScreen () {
		load = true;
		$('#load-window').fadeIn('fast');
		startLoadScreen();
	}
});
window.onload=function() {
	$('.content').each(function(){
		if ( $(this).attr('serial')%10 == 1 ) {
			$(this).show();
		}
	});
}