var add_meal = true;
var add_extra = true;
var add_off = true;
var add_scheduled_off = true;
var weekly_off = null;
var scheduled_off = null;
var mealSerial = -1;
var extraSerial = -1;
var mode = 'none';
var menu_input = "";
var allowed_off = "";
var password = readCookie('transact_password');
$(document).ready(function () {
	bindAll();
	bindDate();
	$('#dialog-overlay').click(function() {
		hideDialog();
	}).children().each(function() {
		$(this).click(function(e) {
			e.stopPropagation();
		});
	});
	$('.time').click(function() {
		if ( $(this).text() == 'AM' ) {
			$(this).text('PM');
		} else if ( $(this).text() == 'PM' ) {
			$(this).text('AM');
		}
	});
	$('#no').click(function() {
		mode = 'none';
		hideDialog();
	});
	$('#yes').click(function() {
		var signal = true;
		if ( password != null ) {
			if ( password.getValue() == 'c' ) {
				signal = false;
				hideDialog();
				if ( mode == 'add_meal' )
					addMeal();
				else if ( mode == 'remove_meal' )
					removeMeal();
				else if ( mode == 'add_extra' )
					addExtra();
				else if ( mode == 'remove_extra' )
					removeExtra();
				else if ( mode == 'add_off' )
					addOff();
				else if ( mode == 'remove_weekly_off' )
					removeWeeklyOff();
				else if ( mode == 'add_scheduled_off' )
					addScheduledOff();
				else if ( mode == 'remove_scheduled_off' )
					removeScheduledOff();
				else if ( mode == 'allowed_off' )
					allowedOff();
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
			if ( mode == 'add_meal' )
				addMeal();
			else if ( mode == 'remove_meal' )
				removeMeal();
			else if ( mode == 'add_extra' )
				addExtra();
			else if ( mode == 'remove_extra' )
				removeExtra();
			else if ( mode == 'add_off' )
				addOff();
			else if ( mode == 'remove_weekly_off' )
				removeWeeklyOff();
			else if ( mode == 'add_scheduled_off' )
				addScheduledOff();
			else if ( mode == 'remove_scheduled_off' )
				removeScheduledOff();
			else if ( mode == 'menu_input' )
				addMenuItem();
			else if ( mode == 'allowed_off' )
				allowedOff();
		} else {
			displayFeedback('Transaction password is necessary.');
		}
	});
	$('#extra_button').click(function() {
		if ( $('#add_extra').val().length > 0 ) {
			if ( $('#extra_edit_cost').val() > 0 ) {
				mode = 'add_extra';
				$('#dialog-content').text('Do you want to add this extra?');
				showDialog();
			} else {
				displayFeedback('Please provide the cost of extra!');
			}
		} else {
			displayFeedback('Name of extra is required!');
		}
	});
	$(document).on( 'click', '#scheduled_off_button', function() {
		var start = $('#scheduled_off_date_from').val();
		var end = $('#scheduled_off_date_to').val();
		if ( validateRegex( /^\d{4}-\d{2}-\d{2}$/, start ) && validateRegex( /^\d{4}-\d{2}-\d{2}$/, end ) ) {
			if ( ( (new Date(end)) - (new Date(start)) )/86400000 >= 0 ) {
				mode = 'add_scheduled_off';
				$('#dialog-content').text('Do you want to schedule this Off?');
				showDialog();
			} else
				displayFeedback('Incorrect dates provided.');
		} else
			displayFeedback('Acceptable date format is: YYYY-MM-DD');
	});
	$('#off_button').click(function() {
		if ( $('#select_offs').val() != null ) {
			if ( $('.selected').length > 0 ) {
				mode = 'add_off';
				$('#dialog-content').text('Do you want to add this Weekly Off?');
				showDialog();
			} else {
				displayFeedback('Please select a day!');
			}
		} else {
			displayFeedback('Please add some meals first!');
		}
		
	});
	$('#meal_button').click(function() {
		var format = /^[\d]{1,2}:[\d]{1,2}/;
		if ( $('#add_meal').val().length > 0 ) {
			var start = $('#meal_edit_start').val();
			var end = $('#meal_edit_end').val();
			var startTime = start.split(':');
			var endTime = end.split(':');
			if ( validateRegex(format, start) && validateRegex(format, end) ) {
				if ( ( startTime[0] <= 12 ) && ( startTime[1] < 60 ) && ( startTime[0] >= 0 ) && ( startTime[1] >= 0) && ( endTime[0] <= 12 ) && ( endTime[1] < 60 ) && ( endTime[0] >= 0 ) && ( endTime[1] >= 0) ) {
					if ( $('#meal_edit_points').val().length > 0 ) {
						if ( $('#meal_edit_cost').val().length > 0 ) {
							mode = 'add_meal';
							$('#dialog-content').text('Do you want to add this meal?');
							showDialog();
						} else {
							displayFeedback('Please provide meal costs.');
						}
					} else {
						displayFeedback('Please provide meal points.');
					}
				} else {
					displayFeedback('Incorrect time provided!');
				}
			} else {
				displayFeedback('Time format should be: HH:MM');
			}
		} else {
			displayFeedback('Please provide name of meal.');
		}
	});
	function addScheduledOff() {
		loadScreen();
		var data = prepareJson('add_scheduled_off');
		data.values['start_meal'] = $('#select_scheduled_off_from').val();
		data.values['start_date'] = $('#scheduled_off_date_from').val();
		data.values['end_meal'] = $('#select_scheduled_off_to').val();
		data.values['end_date'] = $('#scheduled_off_date_to').val();
		var json = JSON.stringify(data);
		$.ajax({
			url: 'process.php',
			type: 'POST',
			data: 'data='+json,
			success: function(result) {
				var response = $.parseJSON(result);
				var status = response['response'][0]['status'];
				if ( status == 0 ) {
					add_scheduled_off = false;
					getContent();
				}
				postProcessing(2, 1, response);
			},
			error: errorDisplay
		});
	}
	function addExtra() {
		loadScreen();
		var data = prepareJson('add_extra');
		data.values['name'] = $('#add_extra').val();
		data.values['cost'] = $('#extra_edit_cost').val();
		var json = JSON.stringify(data);
		add_extra = true;
		$.ajax({
			type: 'POST',
			url: 'process.php',
			data: 'data='+json,
			success: function(result) {
				var response = $.parseJSON(result);
				var status = response['response'][0]['status'];
				if ( status == 0 ) {
					$('#extra_edit_cost').val('');
					$('#extra_edit_name').text('');
					add_extra = false;
					$('#add_extra_input').trigger('click');
					getContent();
				}
				postProcessing( 3, 1, response);
			},
			error: errorDisplay
		});
	}
	function addOff() {
		loadScreen();
		var data = prepareJson('add_weekly_off');
		data.values['meal'] = $('#select_offs').val();
		data.values['day'] = $('.selected').attr('value');
		var json = JSON.stringify(data);
		$.ajax({
			type: 'POST',
			url: 'process.php',
			data: 'data='+json,
			success: function(result) {
				var response = $.parseJSON(result);
				var status = response['response'][0]['status'];
				if ( status == 0 ) {
					add_off = false;
					$('#add_weekly_off_input').trigger('click');
					getContent();
				}
				postProcessing(2, 1, response);
			},
			error: errorDisplay
		});
	}
	function addMeal() {
		loadScreen();
		var data = prepareJson('add_meal');
		data.values['name'] = $('#add_meal').val();
		var start = '', end = '';
		if ( $('#meal_edit_start_feed').text() == 'PM' ) {
			if ( parseInt($('#meal_edit_start').val().split(':')[0]) == 12 )
				start = $('#meal_edit_start').val();
			else
				start = ( parseInt($('#meal_edit_start').val().split(':')[0]) + 12 ) + ":" + $('#meal_edit_start').val().split(':')[1];
		} else {
			if ( parseInt($('#meal_edit_start').val().split(':')[0]) == 12 )
				start = 0 + ":" + $('#meal_edit_start').val().split(':')[1];
			else
				start = $('#meal_edit_start').val();
		}
		if ( $('#meal_edit_end_feed').text() == 'PM' ) {
			if ( parseInt($('#meal_edit_end').val().split(':')[0]) == 12 )
				end = $('#meal_edit_end').val();
			else
				end = ( parseInt($('#meal_edit_end').val().split(':')[0]) + 12 ) + ":" + $('#meal_edit_end').val().split(':')[1];
		} else {
			if ( parseInt($('#meal_edit_end').val().split(':')[0]) == 12 )
				end = 0 + ":" + $('#meal_edit_end').val().split(':')[1];
			else
				end = $('#meal_edit_end').val();
		}
		data.values['start'] = start;
		data.values['end'] = end;
		data.values['cost'] = $('#meal_edit_cost').val();
		data.values['pts'] = $('#meal_edit_points').val();
		var json = JSON.stringify(data);
		add_meal = true;
		$.ajax({
			type: 'POST',
			url: 'process.php',
			data: 'data='+json,
			success: function(result) {
				var response = $.parseJSON(result);
				var status = response['response'][0]['status'];
				if ( status == 0 ) {
					$('#meal_edit_name').text('');
					$('#meal_edit_cost').val('');
					$('#meal_edit_points').val('');
					$('#meal_edit_start').val('');
					$('#meal_edit_end').val('');
					add_meal = false;
					$('#add_meal_input').trigger('click');
					getContent();
				}
				postProcessing(3, 1, response);
			},
			error: errorDisplay
		});
	}
	function addMenuItem() {
		loadScreen();
		var data = prepareJson('menu_input');
		data.values['menu_input'] = menu_input.val();
		data.values['day'] = menu_input.attr('day');
		data.values['meal'] = menu_input.attr('meal');
		var json = JSON.stringify(data);
		$.ajax({
			type: 'POST',
			url: 'process.php',
			data: 'data='+json,
			success: function(result) {
				var response = $.parseJSON(result);
				var status = response['response'][0]['status'];
				if ( status == 0 ) {
					getMenu();
				}
				postProcessing( 2, 1, response );
			},
			error: errorDisplay
		});
	}
	function allowedOff() {
		loadScreen();
		var data = prepareJson('allowed_off');
		data.values['points'] = allowed_off;
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
					$('#allowed_off_input').val('');
				}
				postProcessing(2, 1, response);
			},
			error: errorDisplay
		});
	}
	function changePassword() {
		loadScreen();
		var data = {type:{}, values:{}};
		data.type['name'] = 'change_password';
		data.values['old_password'] = $('#old_password').val();
		data.values['new_password_1'] = $('#new_password_1').val();
		data.values['new_password_2'] = $('#new_password_2').val();
		var json = JSON.stringify(data);
		$.ajax({
			type: 'POST',
			url: 'process.php',
			data: 'data='+json,
			success: function(result) {
				var response = $.parseJSON(result);
				var msg = response[0]['msg'];
				$('#old_password').val('');
				$('#new_password_1').val('');
				$('#new_password_2').val('');
				hideAll();
				displayFeedback(msg);
			}
		});
	}
	function removeExtra() {
		loadScreen();
		var data = prepareJson('remove_extra');
		data.values['serial'] = extraSerial;
		var json = JSON.stringify(data);
		$.ajax({
			type: 'POST',
			url: 'process.php',
			data: 'data='+json,
			success: function(result) {
				var response = $.parseJSON(result);
				var status = response['response'][0]['status'];
				if ( status == 0 ) {
					getContent();
				}
				postProcessing(2, 1, response);
			},
			error: errorDisplay
		});
	}
	function removeWeeklyOff(){
		loadScreen();
		var data = prepareJson('remove_weekly_off');
		data.values['meal'] = weekly_off.attr('meal');
		data.values['day'] = weekly_off.attr('day');
		var json = JSON.stringify(data);
		$.ajax({
			type: 'POST',
			url: 'process.php',
			data: 'data='+json,
			success: function(result) {
				var response = $.parseJSON(result);
				var status = response['response'][0]['status'];
				if ( status == 0 ) {
					getContent();
				}
				postProcessing(2, 1, response);
			},
			error: errorDisplay
		});
	}
	function removeScheduledOff() {
		loadScreen();
		var data = prepareJson('remove_scheduled_off');
		data.values['meal'] = $(scheduled_off).attr('meal');
		data.values['date'] = $(scheduled_off).attr('date');
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
					hideDialog();
					$('#scheduled_off_display').text('');
				}
				postProcessing( 2, 1, response );
			},
			error: errorDisplay
		});
	}
	function removeMeal() {
		loadScreen();
		var data = prepareJson('remove_meal');
		data.values['serial'] = mealSerial;
		var json = JSON.stringify(data);
		$.ajax({
			type: 'POST',
			url: 'process.php',
			data: 'data='+json,
			success: function(result) {
				var response = $.parseJSON(result);
				var status = response['response'][0]['status'];
				if ( status == 0 ) {
					getContent();
				}
				postProcessing( 2, 1, response );
			},
			error: errorDisplay
		});
	}
	function refresh() {
		var data = {type:{}, values:{}};
		data.type['name'] = 'refresh';
		if ( $('#remember_me').is(':checked') ) {
			data.values['check'] = true;
		} else {
			data.values['check'] = false;
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
				if ( status == 0 || status == 1 ) {
					var validity = response['response'][1]['validity'];
					writeCookie(new Cookie( 'username', response['response'][1]['username'], validity ));
					writeCookie(new Cookie( 'type', response['response'][1]['type'], validity ));
					writeCookie(new Cookie( 'remember', response['response'][1]['remember'], validity ));
					$('#remaining').text(msg);
				} else {
					displayFeedback(msg);
				}
			},
			error: function() {
				displayFeedback('Connection Error!');
			}
		});
	}
	function postProcessing( code, index, response ) {
		if ( (status != code ) && ( ( response['response'][index]['rememberPassword'] == 'y') || ( response['response'][index]['rememberPassword'] == 'c' ) ) ) {
			writeCookie( new Cookie('transact_password', 'c', null ) );
		}
		password = readCookie('transact_password');
		hideAll();
		displayFeedback(response['response'][0]['msg']);
		mode = 'none';
	}
	function getScheduledOff() {
		var data = {type:{}, values:{}};
		data.type['name'] = 'get_scheduled_off';
		var json = JSON.stringify(data);
		$.ajax({
			type: 'POST',
			url: 'process.php',
			data: 'data='+json,
			success: function(result) {
				$('#scheduled_off_display').html(result);
			},
			error: function() {
				displayFeedback('Connection Error!');
			}
		});
	}
	function getContent() {
		var data = {type:{}, values:{}};
		data.type['name'] = 'get_data';
		var json = JSON.stringify(data);
		$.ajax({
			type: 'POST',
			url: 'process.php',
			data: 'data='+json,
			success: function(result) {
				$('#container').html(result);
				bindDate();
			}
		});
	}
	function getMenu() {
		var data = {type:{}};
		data.type['name'] = 'get_menu';
		var json = JSON.stringify(data);
		$.ajax({
			type: 'POST',
			url: 'process.php',
			data: 'data='+json,
			success: function(result) {
				$('#menu').html(result);
			}
		});
	}
	function prepareJson(type) {
		var data = {type:{},values:{}};
		data.type['name'] = type;
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
		return data;
	}
	function loadScreen() {
		$('#disable').show();
		$('#feedback').text('Processing...').show();
		load = true;
		$('#load-window').fadeIn();
		startLoadScreen();
	}
	function errorDisplay() {
		hideAll();
		displayFeedback('Connection Error!');
	}
	function hideAll() {
		$('#disable').hide();
		$('#feedback').hide();
		stopLoadScreen();
	}
	function bindDate() {
		$('#scheduled_off_date_from, #scheduled_off_date_to').datepicker({
			dateFormat: 'yy-mm-dd',
			showAnim: "fadeIn",
			changeMonth: true,
			changeYear: true,
			yearRange: "-1:+1"
		});
	}
	function bindAll() {
		$(document).on('click', '#add_extra_input', function() {
			closeOptions([0,2,3]);
			if ( add_extra ) {
				$(this).before('<input id="add_extra" class="add_item" type="text" placeholder="New Item" maxlength="20" />');
				$('#extra_edit').css( 'top', getPosition(document.getElementById('add_extra')).y+40 ).css('left', getPosition(document.getElementById('add_extra')).x ).slideDown(100);
				$('#add_extra').bind('keyup', function() {
					$('#extra_edit_name').text($(this).val());
				});
				$(this).addClass('rotate');
				add_extra = false;
			} else {
				$('#add_extra').remove();
				$('#extra_edit').fadeOut();
				$(this).removeClass('rotate');
				add_extra = true;
			}
		});
		$(document).on('blur', '#add_extra', function() {
			$('#extra_edit_cost').focus();
		});
		$(document).on('click', '#add_scheduled_off_input', function() {
			closeOptions([0,1,2]);
			if ( add_scheduled_off ) {
				$('#scheduled_off_edit').show(200);
				$(this).addClass('rotate');
				add_scheduled_off = false;
			} else {
				$(this).removeClass('rotate');
				$('#scheduled_off_edit').hide(200);
				add_scheduled_off = true;
			}
		});
		$(document).on('click', '#add_weekly_off_input', function() {
			closeOptions([0,1,3]);
			if ( add_off ) {
				$('#select_offs').stop().show();
				$('#off_edit').css('top', getPosition(document.getElementById('select_offs')).y+30).css('left', getPosition(document.getElementById('select_offs')).x).slideDown(100);
				$(this).addClass('rotate');
				add_off = false;
			} else {
				$('#select_offs').hide();
				$('#off_edit').fadeOut();
				$(this).removeClass('rotate');
				add_off = true;
			}
		});
		$(document).on('click', '#add_meal_input', function() {
			closeOptions([1,2,3]);
			if ( add_meal ) {
				$(this).before('<input id="add_meal" class="add_item" type="text" placeholder="New Item" maxlength="20" />');
				$('#meal_edit').css('top', getPosition(document.getElementById('add_meal')).y+40).css('left', getPosition(document.getElementById('add_meal')).x).slideDown(100);
				$('#add_meal').bind('keyup', function() {
					$('#meal_edit_name').text($(this).val());
				});
				$(this).addClass('rotate');
				add_meal = false;
			} else {
				$('#add_meal').remove();
				$('#meal_edit').fadeOut();
				$(this).removeClass('rotate');
				add_meal = true;
			}
		});
		$(document).on('click', '#view_scheduled_off', function() {
			$('#dialog-overlay').fadeIn(100);
			$('#scheduled_off_display').fadeIn(300);
			$('#scheduled_off_display').html('<span id=\'load\'>Loading...</span>');
			getScheduledOff();
		});
		$(document).on('blur', '#add_meal', function() {
			$('#meal_edit_start').focus();
		});
		$(document).on('click', '.days', function() {
			var signal = $(this).hasClass('selected');
			$('.days').each(function() {
				$(this).removeClass('selected');
				$(this).find('.marked').fadeOut(200, function() {
					$(this).remove();
				});
			});
			if ( !signal ) {
				$(this).addClass('selected').append("<div class='marked'>&#x2714</div>");
				$(this).find('.marked').fadeIn(300);
			}
		});
		$(document).on('click', '#design_menu', function() {
			$('#menu_wrap').fadeIn();
			$('#menu').html('<span id=\'load_text\'>Loading...</span>');
			getMenu();
		});
		$(document).on('click', '#close', function() {
			$('#menu_wrap').fadeOut();
		});
		$(document).on('mouseenter', '.meal', function() {
			$(this).find('.remove').stop().fadeIn();
			$('#meal_feed').html('<span class=\'green\'>Start Time: '+getFormattedTime($(this).attr('start'))+'</span><br /><span class=\'red\'>End Time: '+getFormattedTime($(this).attr('end'))+'</span><br /><span class=\'blue\'>Cost: '+$(this).attr('cost')+'<br />Points: '+$(this).attr('pts')+'</span>').css('top', getPosition(this).y+40).css('left', getPosition(this).x).stop().fadeIn(200);
		});
		$(document).on('mouseleave', '.meal', function() {
			$(this).find('.remove').stop().fadeOut();
			$('#meal_feed').stop().fadeOut();
		});
		$(document).on('mouseenter', '.extra', function() {
			$(this).find('.remove').stop().fadeIn();
			$('#extra_feed').html('<span class=\'blue\'>Cost: '+$(this).attr('cost')+'</span>').css('top', getPosition(this).y+40).css('left', getPosition(this).x).stop().fadeIn(200);
		});
		$(document).on('mouseleave', '.extra', function() {
			$(this).find('.remove').stop().fadeOut();
			$('#extra_feed').stop().fadeOut();
		});
		$(document).on('mouseenter', '.weekly_off', function() {
			$(this).find('.remove').stop().fadeIn();
		});
		$(document).on('mouseleave', '.weekly_off', function() {
			$(this).find('.remove').stop().fadeOut();
		});
		$(document).on('mouseenter', '.menu_item', function() {
			$(this).parent().find('.ask_edit').fadeIn();
		});
		$(document).on('mouseleave', '.ask_edit', function() {
			$(this).fadeOut();
		});
		$(document).on('click', '.ask_edit', function() {
			$(this).hide().parent().hide();
			$(this).parent().parent().find('.menu_input').fadeIn();
		})
		$(document).on('blur', '.menu_input textarea', function() {
			$(this).parent().find('.ask_save').fadeIn();
		});
		$(document).on('click', '.decline', function() {
			$(this).parent().hide().parent().hide().parent().find('.menu_item').fadeIn();
		});
		$(document).on('click', '.accept', function() {
			menu_input = $(this).parent().next();
			if ( $(menu_input).get(0).value != $(menu_input).get(0).defaultValue ) {
				mode = 'menu_input';
				if ( password == null ) {
					showTransactionBox();
				} else if ( password.getValue() != 'c' ) {
					showTransactionBox();
				} else {
					addMenuItem();
				}
			} else {
				$(this).parent().hide().parent().hide().parent().find('.menu_item').fadeIn();
				displayFeedback('No changes made!');
			}
		});
		$(document).on('click', '.rm_extra', function() {
			mode = 'remove_extra';
			extraSerial = $(this).parent().attr('serial');
			$('#dialog-content').text('Do you want to remove this extra?');
			showDialog();
		});
		$(document).on('click', '.rm_meal', function() {
			mode = 'remove_meal';
			mealSerial = $(this).parent().attr('serial');
			$('#dialog-content').text('Do you want to remove this meal?');
			showDialog();
		});
		$(document).on('click', '.rm_weekly_off', function() {
			mode = 'remove_weekly_off';
			weekly_off = $(this).parent();
			$('#dialog-content').text('Do you want to remove this Weekly Off?');
			showDialog();
		});
		$(document).on('click', '#allowed_off_button', function() {
			var input = $('#allowed_off_input').val();
			if ( input != null && input > 0 ) {
				mode = 'allowed_off';
				allowed_off = input;
				$('#dialog-content').text('Do you want to update the Maximum Allowed Off?');
				showDialog();
			} else
				displayFeedback('Please provide valid input!');
		});
		$(document).on('click', '#change_password_button', function(){
			if ( $('#old_password').val().length > 0 ) {
				if ( $('#new_password_1').val().length > 0 ) {
					if ( $('#new_password_1').val() == $('#new_password_2').val() ) {
						changePassword();
					} else
						displayFeedback('New passwords are not same.');
				} else
					displayFeedback('Please provide new password.');
			} else
				displayFeedback('Old password is required.');
		});
		$(document).on('click', '#refresh', refresh);
	}
	function closeOptions(arr) {
		for ( var serial in arr ) {
			switch(arr[serial]) {
				case 0:
					if ( !add_meal ) {
						add_meal = false;
						$('#add_meal_input').trigger('click');
					}
					break;
				case 1:
					if ( !add_extra ) {
						add_extra = false;
						$('#add_extra_input').trigger('click');
					}
					break;
				case 2:
					if ( !add_off ) {
						add_off = false;
						$('#add_weekly_off_input').trigger('click');
					}
					break;
				case 3:
					if ( !add_scheduled_off ) {
						add_scheduled_off = false;
						$('#add_scheduled_off_input').trigger('click');
					}
					break;
			}
		}
	}
	function getFormattedTime(time) {
		var result, arr = time.split(':');
		if ( parseInt(arr[0]) == 0 ) {
			result = 12+':'+arr[1]+' AM';
		} else if ( (parseInt(arr[0]) > 0) && ( parseInt( arr[0] ) < 12 ) ) {
			result = time.substring(0, 5)+' AM';
		}  else if ( parseInt(arr[0]) == 12 ) {
			result = time.substring(0, 5)+' PM';
		}else if ( parseInt(arr[0]) > 12 ) {
			result = (parseInt(arr[0])-12)+':'+arr[1]+' PM';
		}
		return result;
	}
	window.onresize = scale;
	window.onscroll = scale;
	function scale() {
		if ( !add_meal ) {
			$('#meal_edit').css('top', getPosition(document.getElementById('add_meal')).y+40).css('left', getPosition(document.getElementById('add_meal')).x);
		}
		if ( !add_extra )
			$('#extra_edit').css('top', getPosition(document.getElementById('add_extra')).y+40).css('left', getPosition(document.getElementById('add_extra')).x);
		if ( !add_off )
			$('#off_edit').css('top', getPosition(document.getElementById('select_offs')).y+30).css('left', getPosition(document.getElementById('select_offs')).x);
	}
});
function removeScheduledOffPrompt(element) {
	$('#scheduled_off_display').hide();
	mode  = 'remove_scheduled_off';
	$('#dialog-content').text('Do you want to remove this Scheduled Off?');
	scheduled_off = element;
	showDialog();
}