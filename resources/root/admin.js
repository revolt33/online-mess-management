var mode= 'none';
var current;
var deleteUserAgent;
$(document).ready(function () {
	$('#logout').click(function() {
		eraseCookie('username');
		eraseCookie('remember');
		eraseCookie('type');
	});
	$('#yes').click(function() {
		if ( mode == 'add' ) {
			$('#form').fadeOut(200);
			setTimeout(function() {
				$('#bottomFeed').slideDown('fast');
			}, 410);
			hideDialog();
			$('#disable').show();
			addAdmin();
		} else if ( mode == 'delete' ) {
			hideDialog();
			$('#disable').show();
			deleteAdmin();
		}
	});
	$('#no').click(hideDialog);
	$('#cssmenu li.active').addClass('open').children('ul').show();
	$('#cssmenu li.has-sub>a').bind('click', accordion);
	openFirst();
	function accordion() {
		$(this).removeAttr('href');
		var element = $(this).parent('li');
		if (element.hasClass('open')) {
			element.removeClass('open');
			element.find('li').removeClass('open');
			element.find('ul').slideUp(200);
		}
		else {
			element.addClass('open');
			element.children('ul').slideDown(200);
			element.siblings('li').children('ul').slideUp(200);
			element.siblings('li').removeClass('open');
			element.siblings('li').find('li').removeClass('open');
			element.siblings('li').find('ul').slideUp(200);
		}
	}
	$('.view').click(function() {
		current = $(this).attr('serial');
		getAdminList(current);
	});
	$(document).on('submit', '#form form', function(){
		return false;
	});
	$(document).on('blur', '#username', function() {
		var username = $(this).val();
		var data = {type:{},values:{}};
		data.type['name'] = 'avail';
		data.values['id'] = username;
		var json = JSON.stringify(data);
		$.ajax({
			type: 'POST',
			url: 'processAdmin.php',
			data: 'data='+json,
			success: function(result) {
				var response = $.parseJSON(result);
				var status = response['response'][0]['status'];
				var msg = response['response'][0]['msg'];
				if ( status == 0 || status == 1 || status == 3 ) {
					$('#feedback').text(msg).fadeIn('fast').delay(1000).fadeOut('slow');
				}
			}
		});
	});
	$(document).on('click', '.del',  function() {
		mode = 'delete';
		deleteUserAgent = $(this).attr('serial');
		$('#dialog-head').text('Confirmation');
		$('#dialog-content').text('Are you sure you want to delete this admin?');
		showDialog();
	});
	$(document).on('click', '#submit',  checkForm);
	function openFirst() {
		var serial = 0;
		$('.view').each(function() {
			var thisSerial = $(this).attr('serial');
			serial = serial==0?thisSerial:serial>thisSerial?thisSerial:serial;
		});
		if ( serial > 0 ) {
			getAdminList(serial);
		}
		current = serial;
	}
	function getAdminList(serial) {
		var data = {type:{}, values:{}};
		data.type['name'] = 'list';
		data.values['serial'] = serial;
		var json = JSON.stringify(data);
		$.ajax({
			type: 'POST',
			url: 'processAdmin.php',
			data: 'data='+json,
			cache: false,
			success: function(data) {
				$('#display').html(data);
				$('#addButton').bind('click', function() {
					$('#add').fadeOut(200);
					setTimeout(function() {
						$('#form').slideDown('fast');
					}, 210);
				});
			},
			error: function() {
				$('#feedback').text('Connection failed.').fadeIn('fast').delay(1000).fadeOut('slow');
			}
		});
	}
	function checkForm() {
		var username = $('#username').val();
		var name = $('#name').val();
		var email = $('#email').val();
		if ( ( username.length > 0 ) && ( name.length > 0 ) && ( email.length > 0 ) ) {
			if ( validateEmail(email) ) {
				mode = 'add';
				$('#dialog-head').text('Alert!');
				$('#dialog-content').text('Do you want to add this user?');
				showDialog();
			} else {
				$('#feedback').text('Please provide a valid email.').fadeIn('fast').delay(1000).fadeOut('slow');
			}
		} else {
			$('#feedback').text('All fields are required.').fadeIn('fast').delay(1000).fadeOut('slow');
		}
	}
	function showDialog() {
		$('#dialog-overlay').slideDown('fast');
		$('#dialog-box').fadeIn(500);
	}
	function hideDialog() {
		$('#dialog-box').fadeOut('fast');
		$('#dialog-overlay').slideUp('fast');
	}
	function addAdmin() {
		var username = $('#username').val();
		var name = $('#name').val();
		var email = $('#email').val();
		var serial = $('#serial').val();
		var data = {type:{}, values:{}};
		data.type['name'] = 'add';
		data.values['username'] = username;
		data.values['name'] = name;
		data.values['email'] = email;
		data.values['serial'] = serial;
		var json = JSON.stringify(data);
		$.ajax({
			type: 'POST',
			url: 'processAdmin.php',
			data: 'data='+json,
			success: function(result) {
				var response = $.parseJSON(result);
				var status = response['response'][0]['status'];
				var msg = response['response'][0]['msg'];
				setTimeout(function(){
					$('#bottomFeed').fadeOut('fast');
				}, 1500);
				$('#disable').hide();
				if ( status == 0 ) {
					$('#username').val('');
					$('#name').val('');
					$('#email').val('');
					setTimeout(function(){
						$('#add').slideDown('fast');
					}, 2000);
					setTimeout(function(){
						getAdminList(current);
					}, 2300);
				} else {
					setTimeout(function(){
						$('#form').slideDown('fast');
					}, 2000);
				}
				$('#feedback').text(msg).fadeIn('fast').delay(1000).fadeOut('slow');
			},
			error: function() {
				$('#disable').hide();
				$('#bottomFeed').hide();
				$('#form').slideDown('fast');
				$('#feedback').text('Connection failed.').fadeIn('fast').delay(1000).fadeOut('slow');	
			}
		});
	}
	function deleteAdmin() {
		var data = {type:{},values:{}};
		data.type['name'] = 'delete';
		data.values['id'] = deleteUserAgent;
		var json = JSON.stringify(data);
		$.ajax({
			type: 'POST',
			url: 'processAdmin.php',
			data: 'data='+json,
			success: function(result) {
				var response = $.parseJSON(result);
				var status = response['response'][0]['status'];
				var msg = response['response'][0]['msg'];
				if ( status == 0 ) {
					$('#feedback').text(msg).fadeIn('fast').delay(1000).fadeOut('slow');
					setTimeout(function(){
						getAdminList(current);
					}, 1700);
				} else {
					$('#feedback').text(msg).fadeIn('fast').delay(1000).fadeOut('slow');
				}
			},
			error: function() {
				$('#feedback').text('Connection failed.').fadeIn('fast').delay(1000).fadeOut('slow');
			}
		});
	}
});