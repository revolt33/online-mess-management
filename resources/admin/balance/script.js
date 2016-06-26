var password = readCookie('transact_password');
var mode = 'none';
$(document).ready(function() {
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
	$('#submit').click(function() {
		if ( $('#password').val() != '' ) {
			hideDialog();
			switch(mode) {
				case 'start':
					startSession();
					break;
			}
		} else {
			displayFeedback('Please provide transaction password.');
		}
	});
	$('#yes').click(function() {
		hideDialog();
		var signal = true;
		$('#transaction-content input').val('');
		if ( password != null ) {
			if ( password.getValue() == 'c' ) {
				signal = false;
				switch(mode) {
					case 'start':
						startSession();
						break;
				}
			}
		}
		if ( signal ) {
			setTimeout(function() {
				showTransactionBox();
				$('#password').focus();
			}, 200);
		}
	});
	$('#dialog-overlay').click(function() {
		hideDialog();
	}).children().each(function() {
		$(this).click(function(e) {
			e.stopPropagation();
		});
	});
	$('#start').click(function() {
		$('#dialog-content').text('Do you want to start the Session?');
		mode = 'start';
		showDialog();
	});
	function startSession() {
		var rememberPassword;
		loadScreen();
		if ( password == null ) {
			if ($('#check').is(':checked')) {
				rememberPassword = 'y'; // first time checked
			} else {
				rememberPassword = 'n'; // not checked
			}
		} else {
			rememberPassword = 'c'; // checked and stored in cookie
		}
		$('#disable').show();
		$('#feedback').text('Processing...').show();
		var data = {type:{},values:{}};
		data.type['name'] = 'start';
		data.type['rememberPassword'] = rememberPassword;
		if ( rememberPassword != 'c' ) {
			data.values['password'] = $('#password').val();
		}
		var json = JSON.stringify(data);
		$.ajax({
			type: 'POST',
			url: 'process.php',
			data: 'data='+json,
			success: function(result) {
				var response = $.parseJSON(result);
				var status = response['response'][0]['status'];
				var msg = response['response'][0]['msg'];
				if ( status == 0 ) {
					window.location = '';
				} else {
					postProcessing();
					displayFeedback(msg);
				}
				if ( ( status != 2 ) && ( ( response['response'][1]['rememberPassword'] == 'y') || ( response['response'][2]['rememberPassword'] == 'c' ) ) ) {
					writeCookie(new Cookie('transact_password', 'c', null));
				}
				password = readCookie('transact_password');
				stopLoadScreen();
			},
			error: function() {
				postProcessing();
				displayFeedback('Connection Error!');
			}
		});
	}
	function postProcessing() {
		mode = 'none';
		$('#disable').hide();
		$('#feedback').hide();
		stopLoadScreen();
	}
	function loadScreen () {
		load = true;
		$('#load-window').fadeIn('fast');
		startLoadScreen();
	}
});
window.onload=function() {
	$('.content').each(function(){
		if ( $(this).attr('serial') == 1 ) {
			$(this).show();
		}
	});
}