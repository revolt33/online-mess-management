mode = 'none';
$(document).ready(function () {
	$('#logout').click(function() {
		eraseCookie('username');
		eraseCookie('remember');
		eraseCookie('type');
	});
	$('#refresh').click(refresh);
	$('form').submit(function(){
		return false;
	});
	$('#change').click(function() {
		$('#change_email').slideToggle(300);
	});
	$('#pass_text').click(function() {
		$('#pass_change').slideToggle(300);
	});
	$('#dialog-overlay').bind('click', hideDialog).children().bind('click', function() {
				return false;
			});
	$('#change_pass_button').click(function() {
		var new_pass = $('#new_pass').val();
		var repeat_pass = $('#repeat_pass').val();
		if ( ( new_pass.length != 0 ) && ( repeat_pass.length != 0 ) ) {
			if ( new_pass == repeat_pass ) {
				mode = 'pass';
				showDialog();
			} else {
				$('#feedback').text('Passwords do not match.').fadeIn('fast').delay(1000).fadeOut('slow');
			}
		} else {
			$('#feedback').text('Passwords cannot be empty.').fadeIn('fast').delay(1000).fadeOut('slow');
		}
	});
	$('#change_email_button').click(function() {
		var email = $('#email').val();
		if ( validateEmail( email ) ) {
			mode = 'email';
			showDialog();
		} else {
			$('#feedback').text('Invalid email.').fadeIn('fast').delay(1000).fadeOut('slow');
		}
		
	});
	$('#submit').click(function() {
		if ( mode == 'email' ) {
			hideDialog();
			changeEmail();
			mode = 'none';
		} else if ( mode == 'pass' ) {
			hideDialog();
			changePassword();
			mode = 'none';
		}
	});
	function changeEmail() {
		var email = $('#email').val();
		var pass = $('#password').val();
		$('#disable').show();
		$('#feedback').text('Processing...').show();
		var data = {type:{},values:{}};
		data.type['name'] = 'email';
		data.values['email'] = email;
		data.values['pass'] = pass;
		var json = JSON.stringify(data);
		$.ajax({
			type: 'POST',
			url: 'settings.php',
			data: 'data='+json,
			success: function(result) {
				var response = $.parseJSON(result);
				var status = response['response'][0]['status'];
				var msg = response['response'][0]['msg'];
				if ( status == 0 ) {
					var res_email = response['response'][1]['email'];
					$('#email_feed').text(res_email);
					$('#email').val('');
					$('#change_email').slideUp(300);
				}
				$('#disable').hide();
				$('#feedback').text(msg);
				setTimeout(function(){
					$('#feedback').fadeOut('slow');
				}, 1000);
				$('#password').val('');
			},
			error: errorFunction
		});
	}
	function changePassword() {
		var pass = $('#password').val();
		var new_pass = $('#new_pass').val();
		var repeat_pass = $('#repeat_pass').val();
		$('#disable').show();
		$('#feedback').text('Processing...').show();
		var data = {type:{},values:{}};
		data.type['name'] = 'password';
		data.values['pass'] = pass;
		data.values['new_pass'] = new_pass;
		data.values['repeat_pass'] = repeat_pass;
		var json = JSON.stringify(data);
		$.ajax({
			type: 'POST',
			url: 'settings.php',
			data: 'data='+json,
			success: function(result) {
				var response = $.parseJSON(result);
				var status = response['response'][0]['status'];
				var msg = response['response'][0]['msg'];
				if ( status == 0 ) {
					$('#pass_change').slideUp(300);
					$('#new_pass').val('');
					$('#repeat_pass').val('');
				}
				$('#disable').hide();
				$('#feedback').text(msg);
				setTimeout(function(){
					$('#feedback').fadeOut('slow');
				}, 1000);
				$('#password').val('');
			},
			error: errorFunction
		});
	}
	function refresh() {
		var check = false;
		if ( $('#check').is(':checked') ) {
			check = true
		}
		var data = {type:{},values:{}};
		data.type['name'] = 'refresh';
		data.values['check'] = check;
		var json = JSON.stringify(data);
		$.ajax({
			type: 'POST',
			url: 'settings.php',
			data: 'data='+json,
			success: function(result) {
				var response = $.parseJSON(result);
				var status = response['response'][0]['status'];
				var msg = response['response'][0]['msg'];
				var valid = 0;
				if ( status == 0 ) {
					$('#remaining').text(msg);
					writeCookie(new Cookie( 'username', response['response'][1]['username'], 7 ));
					writeCookie(new Cookie( 'type', response['response'][1]['type'], 7 ));
					writeCookie(new Cookie( 'remember', response['response'][1]['remember'], 7 ));
				} else if ( status == 1 ) {
					$('#remaining').text(msg);
					$('#logout').trigger('click');
				} else if ( status == 2 ) {
					$('#feedback').text(msg).fadeIn('fast').delay(1000).fadeOut('slow');
				}
			},
			error: errorFunction
		});
	}
	function showDialog() {
		$('#dialog-overlay').slideDown('fast');
		$('#dialog-box').fadeIn(500);
	}
	function hideDialog() {
		$('#dialog-box').fadeOut('fast');
		$('#dialog-overlay').slideUp('fast');
	}
	function errorFunction() {
		$('#disable').hide();
		$('#feedback').show().text('Connection failed.');
		setTimeout(function(){
			$('#feedback').fadeOut('slow');
		}, 1000);
		$('#password').val('');
	}
});