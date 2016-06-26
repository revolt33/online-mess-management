$(document).ready(function () {
	$('form').submit(function() {
		return false;
	});
	$('#submit').click(function() {
		var pass = "";
		var repeat = "";
		var username = $('#auth').attr('username');
		var usertype = $('#auth').attr('usertype');
		var code = $('#auth').attr('code');
		pass = $('#pass').val();
		repeat = $('#repeat').val();
		if ( (pass.length == 0 ) || (repeat.length == 0 ) ) {
			$('#feedback').text('All fields are required.').fadeIn('fast').delay(2000).fadeOut(1000);
		} else if ( pass != repeat ) {
			$('#feedback').text('Passwords do not match.').fadeIn('fast').delay(2000).fadeOut(1000);
		} else if ( ( pass.length < 8 ) ) {
			$('#feedback').text('Minimum 8 character password required.').css({'width': '25%'}).fadeIn('fast').delay(2000).fadeOut(1000);
		} else if ( pass.length > 20 ) {
			$('#feedback').text('Maximum 20 character password allowed.').css({'width': '25%'}).fadeIn('fast').delay(2000).fadeOut(1000);
		} else {
			$('#disable').show();
			$('#feedback').text('Processing...').fadeIn('fast');
			var send = 'user_id='+username+'&user_type='+usertype+'&code='+code+'&pass='+pass+'&repeat='+repeat;
			password( $('form').attr('action'), send ).done(function(data) {
				var result = $.parseJSON(data);
				var status = parseInt(result['response'][0]['status']);
				var msg = result['response'][0]['msg'];
				if ( status == 0 ) {
					$('#feedback').text(msg).fadeIn('fast');
					setTimeout(function() {
						$('#feedback').text('Redirecting to login page...').css({'width': '25%'});
					}, 1000);
					setTimeout(function() {
						window.location = '../index.php';
					}, 2000);
				} else {
					$('#feedback').text(msg).fadeIn('fast').delay(2000).fadeOut(1000);
					$('#disable').hide();
				}
			}).fail(function() {
				$('#disable').hide();
				$('#feedback').text('Connection Error.').fadeIn('fast').delay(2000).fadeOut(1000);
			});
		}
		setTimeout(function() {
			$('#feedback').css({'width': '22%'});
		}, 3500);
	});
	function password( url, data) {
		return $.ajax({
			url: url,
			type: 'POST',
			data: data
		});
	}
});