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
	$('#old_pass').blur(function() {
		if( $(this).val().length == 0 ) {
			displayFeedback('Old Password is required.');
		}
	});
	$('#new_pass').blur(function() {
		if( $(this).val().length == 0 ) {
			displayFeedback('New Password is required.');
		}
	});
	$('#repeat_pass').blur(function() {
		if( $(this).val() != $('#new_pass').val() ) {
			displayFeedback('Passwords should be same.');
		}
	});
	$('#change').submit(function() {
		$('#feedback').stop();
		if ( $('#old_pass').val().length > 0 ) {
			if ( $('#new_pass').val().length > 7 ) {
				if ( $('#repeat_pass').val() == $('#new_pass').val() ) {
					loadScreen();
					var data = {head:{},values:{}};
					data.head['type'] = 'change_pass';
					data.values['old_pass'] = $('#old_pass').val();
					data.values['new_pass'] = $('#new_pass').val();
					data.values['repeat_pass'] = $('#repeat_pass').val();
					var json = JSON.stringify(data);
					$.ajax({
						url: 'process.php',
						type: 'POST',
						data: 'data='+json,
						success: function(result) {
							hideAll();
							console.log(result);
							$('#password_container input').val('');
							displayFeedback(result);
						},
						error: function() {
							displayFeedback('Connenction error!');
						}
					});
				} else {
					displayFeedback('Passwords should be same.');
				}
			} else {
				displayFeedback('New password should be atleast 8 characters long.');
			}
		} else {
			displayFeedback('Old Password is required.');
		}
		return false;
	});
	function loadScreen() {
		$('#disable').show();
		$('#feedback').text('Processing...').show();
		load = true;
		$('#load-window').fadeIn();
		startLoadScreen();
	}
	function hideAll() {
		$('#disable').hide();
		$('#feedback').hide();
		stopLoadScreen();
	}
});
window.onload=function() {
	$('.content').each(function(){
		if ( $(this).attr('serial') == 1 ) {
			$(this).show();
		}
	});
}