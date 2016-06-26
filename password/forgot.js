$(document).ready(function () {
	$('form').submit(function() {
		return false;
	});
	$('#submit').click(function() {
		var email = $('#email').val();
		var valid = validateEmail(email);
		if ( valid ) {
			$('#feedback').text('Processing...').fadeIn('fast');
			var send = 'user_id='+$('#auth').attr('username')+"&user_type="+$('#auth').attr('usertype')+"&email="+email;
			sendData(send).done(function(data) {
				// alert(data);
				var result = $.parseJSON(data);
				var status = result['response'][0]['status'];
				status = parseInt(status);
				var msg = result['response'][0]['msg'];
				if ( status == 0 ) {
					$('#feedback').text(msg).show();
					setTimeout(function() {
						$('#feedback').text('Redirecting...')
					}, 1000);
					setTimeout(function(){
						window.location = '../index.php';
					}, 2200);
				} else {
					$('#feedback').text(msg).show().delay(2000).fadeOut('slow');
				}
			}).fail(function() {
				$('#feedback').text('Connection Error!').fadeIn('fast').delay(2000).fadeOut('slow');
			});
		} else {
			$('#feedback').text('Please enter a valid email').fadeIn('fast').delay(2000).fadeOut('fast');
		}
	});
	function sendData (send) {
		return $.ajax({
			url: 'send.php',
			type: 'POST',
			data: send
		});
	}
});