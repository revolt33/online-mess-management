var mode  = 'none';
var serial = '';
var password = readCookie('transact_password');
$(document).ready(function () {
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
	$('#dialog-overlay').click(function() {
		hideDialog();
	}).children().each(function() {
		$(this).click(function(e) {
			e.stopPropagation();
		});
	});
	$('#yes').click(function() {
		var signal = true;
		if ( password != null ) {
			if ( password.getValue() == 'c' ) {
				signal = false;
				hideDialog();
				if ( mode == 'add_extra' )
					addExtra();
				else if (mode == 'remove_extra'  )
					removeExtra();
			}
		}
		if ( signal ) {
			$('#dialog-box').fadeOut(100, function() {
				$('#transaction-box').fadeIn();
				$('#password').focus();
			});
		}
	});
	$('#submit').click(function() {
		if( $.trim($('#password').val()).length > 0 ) {
			hideDialog();
			if ( mode == 'add_extra' )
				addExtra();
			else if (mode == 'remove_extra'  )
				removeExtra();
		} else {
			displayFeedback('Transaction password is necessary.');
		}
	});
	$('#no').click(hideDialog);
	$(document).on('click', '#add', function() {
		if ( $('#extra_date').val().length > 0 && validateRegex( /^\d{4}-\d{2}-\d{2}$/, $('#extra_date').val() ) ) {
			mode = 'add_extra';
			$('#dialog-content').text('Do you want ot add extras?');
			showDialog();
		} else {
			displayFeedback('Please provide valid date!');
		}
	});
	$('.date').each(function() {
		$(this).datepicker({
			dateFormat: 'yy-mm-dd',
			showAnim: "fadeIn",
			changeMonth: true,
			changeYear: true,
			yearRange: "-1:+1"
		});
	});
	$('.users a').click(function(e) {
		e.stopPropagation();
		var data = {type:{},values:{}};
		data.type['name'] = 'get_data';
		data.values['id'] = $(this).parent().parent().attr('serial');
		$('#dialog-overlay').fadeIn(200);
		$('#details').html('<span id=\'load\'>Loading...</span>').fadeIn(200);
		var json = JSON.stringify(data);
		$.ajax({
			url: 'process.php',
			type: 'POST',
			data: 'data='+json,
			success: function(result) {
				$('#details').html(result);
			},
			error: function() {
				$('#details').html('<span id=\'load\'>Connection Failed...</span>');
				displayFeedback('Connection Error!');
			}
		});
	});
	function addExtra() {
		loadScreen();
		var users = $('.added');
		var data = {type:{},values:{}};
		data.type['name'] = 'add_extra';
		data.type['total'] = users.length;
		data.values['meal'] = $('#meals').val().split(':')[0];
		data.values['type'] = $('#meals').val().split(':')[1];
		data.values['date'] = $('#extra_date').val();
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
		data.values['users'] = {};
		users.each(function(index) {
			data.values['users'][index] = $(this).attr('serial');
		});
		var json = JSON.stringify(data);
		$.ajax({
			url: 'process.php',
			type: 'POST',
			data: 'data='+json,
			success: function(result) {
				var response = $.parseJSON(result);
				console.log(response[0]['error']);
				var status = response[0]['status'];
				var msg = response[0]['msg'];
				if ( status == 1 ) {
					$('.added').removeClass('added').children('.tick').fadeOut(150);
				}
				postProcessing(3, 1, response);
			},
			error: function() {
				displayFeedback('Connection Error!');
			}
		});
	}
	function removeExtra() {
		loadScreen();
		var data = {type:{},values:{}};
		data.type['name'] = 'remove_extra';
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
		data.values['serial'] = serial.split(':')[0];
		data.values['id'] = serial.split(':')[1];
		var json = JSON.stringify(data);
		$.ajax({
			url: 'process.php',
			type: 'POST',
			data: 'data='+json,
			success: function(result) {
				var response = $.parseJSON(result);
				var status = response[0]['status'];
				var msg = response[0]['msg'];
				postProcessing(3, 1, response);
			},
			error: function() {
				displayFeedback('Connection Error!');
			}
		});
	}
	function loadScreen() {
		$('#disable').show();
		$('#feedback').text('Processing...').show();
		load = true;
		$('#load-window').fadeIn();
		startLoadScreen();
	}
	function postProcessing( code, index, response ) {
		if ( (status != code ) && ( ( response[index]['rememberPassword'] == 'y') || ( response[index]['rememberPassword'] == 'c' ) ) ) {
			writeCookie( new Cookie('transact_password', 'c', null ) );
		}
		password = readCookie('transact_password');
		hideAll();
		displayFeedback(response[0]['msg']);
		mode = 'none';
	}
	function hideAll() {
		$('#disable').hide();
		$('#feedback').hide();
		stopLoadScreen();
	}
});
window.onload = function () {
	$('.content').each(function(){
		if ( $(this).attr('serial') == 1 ) {
			$(this).show();
		}
	});
}
function preRemove(element) {
	serial = $(element).attr('serial');
	mode = 'remove_extra';
	$('#details').hide();
	$('#dialog-content').text('Do you want to remove this extra?');
	showDialog();
}
function processList(element) {
	$(element).toggleClass('added').children('.tick').fadeToggle(150);
}