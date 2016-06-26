$(document).ready(function () {
	$('.off').click(function() {
		$(this).toggleClass('select');
		var parent = $(this).parent(); 
		parent.toggleClass('selected');
		if ( parent.hasClass('selected') ) {
			parent.children('.off').text('Remove Off');
		} else {
			parent.children('.off').text('Turn Off');
		}
		parent.toggleClass('changed');
	});
	$('#button').click(function() {
		var changed = $('.changed').length>0?true:false;
		if ( changed ) {
			showDialog();
		} else {
			displayFeedback('No changes made!');
		}
	});
	$('#yes').click(function() {
		hideDialog();
		loadScreen();
		var data = {head:{}, values:[]};
		var elements =  $('.changed');
		data.head['length'] = elements.length;
		elements.each(function(index, element) {
			data.values[index] = {};
			data.values[index]['day'] = $(element).attr('day');
			data.values[index]['meal'] = $(element).attr('meal');
			data.values[index]['operation'] = $(element).hasClass('selected')?'add_off':'remove_off';
		});
		var json = JSON.stringify(data);
		$.ajax({
			type: 'POST',
			url: 'process.php',
			data: 'data='+json,
			success: function(result) {
				console.log(result);
				var response = $.parseJSON(result);
				var status = response['status'];
				var msg = response['msg'];
				if ( status == 0 ) {
					setTimeout(function() {
						window.location.href='';
					}, 1500);
				} else {
					$('#disable').hide();
				}
				displayFeedback(msg);
				stopLoadScreen();
			}
		});
	});
	$('#no').click(function() {
		mode = 'none';
		hideDialog();
	});
	$('#dialog-overlay').click(function() {
		hideDialog();
	}).children().each(function() {
		$(this).click(function(e) {
			e.stopPropagation();
		});
	});
	function loadScreen() {
		$('#disable').show();
		$('#feedback').text('Processing...').show();
		load = true;
		$('#load-window').fadeIn();
		startLoadScreen();
	}
});