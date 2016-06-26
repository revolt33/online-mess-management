var image = null;
var mode = 'none';
var animate = true;
var account = null;
var code;
var password = readCookie('transact_password');
$(document).ready(function () {
	getUserList();
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
	$('form').submit(function() {
		return false;
	});
	$('#change').click(function() {
		$('#image').trigger('click');
	});
	$('#submit').click(function() {
		if ( $('#password').val().length > 0 ) {
			hideDialog();
			if ( mode == 'add_user' ) {
				addUser();
			} else if ( (mode == 'remove') || ( mode == 'close' ) || ( mode == 'activate' ) ) {
				accounts();
			} else if( (mode == 'money' ) || ( mode == 'fine' ) ) {
				moneyTransaction();
			} else if ( mode == 'add_emp' ) {
				addEmp();
			} else if ( mode == 'remove_emp' ) {
				removeEmp();
			}
		} else {
			displayFeedback('Please enter transaction password.');
		}
	});
	$('#image').on('change', function() {
		var value = $(this).val();
		var file = this.files[0];
		var feedback = "";
		if ( value != '' ) {
			var name = file.name;
			var type = file.type;
			var size = file.size;
			if ( type == 'image/jpeg' || type == 'image/jpg' || type == 'image/png' ) {
				if ( size <= (50 * 1024) ) {
					var oFReader = new FileReader();
					oFReader.readAsDataURL(document.getElementById("image").files[0]);
					oFReader.onload = function (oFREvent) {
						$('#frame img').attr('src',oFREvent.target.result);
						image = file;
					};
				} else {
					feedback = "File size cannot excced 50 KB";
				}
			} else {
				feedback = "Only image file can be uploaded.";
			}
			if ( feedback.length > 0 ) {
				$('#feedback').text(feedback).fadeIn('fast');
				setTimeout(function() {
					$('#feedback').fadeOut('slow').text('');
				} , 2000);
			}
		}
	});
	$('#dialog-overlay').click(function() {
		$('.hover').each(function(){
			$(this).stop().fadeOut('slow');
		});
		animate = true;
		hideDialog();
	}).children().each(function() {
		$(this).click(function(e) {
			e.stopPropagation();
		});
	});
	$('#add').click(function() {
		hideDialog();
		var signal = true;
		if ( $('#amount').val() < 1 ) {
			displayFeedback('Please enter a valid amount.');
		} else {
			$('#transaction-content input').val('');
			if ( password != null ) {
				if ( password.getValue() == 'c' ) {
					signal = false;
					moneyTransaction();
				}
			}
			if ( signal ) {
				setTimeout(function() {
					showTransactionBox();
					$('#password').focus();
				}, 200);
			}
		}
	});
	$('#yes').click(function() {
		var signal = true;
		$('#transaction-content input').val('');
		if ( password != null ) {
			if ( password.getValue() == 'c' ) {
				signal = false;
				if ( mode == 'add_user' ) {
					addUser();
				} else if ( ( mode == 'close' ) || ( mode == 'remove' ) || ( mode == 'activate' ) ) {
					accounts();
				} else if ( mode == 'remove_emp' ) {
					removeEmp();
				}
			}
		}
		if ( signal ) {
			setTimeout(function() {
				$('#dialog-box').hide();
				$('#transaction-box').fadeIn();
				$('#password').focus();
			}, 200);
		} else
			hideDialog();
	});
	$('#dob').datepicker({
		dateFormat: 'yy-mm-dd',
		showAnim: "fadeIn",
		changeMonth: true,
		changeYear: true,
		yearRange: "-100:+0"
	});
	$('#add_emp').click(function() {
		var name = $('#emp_name').val();
		var post = $('#emp_post').val();
		var salary = $('#emp_salary').val();
		if ( name.length > 0 ) {
			if( post.length > 0 ) {
				if ( salary > 0 ) {
					var signal = true;
					mode = 'add_emp';
					if ( password != null ) {
						if ( password.getValue() == 'c' ) {
							signal = false;
							addEmp();
						}
					}
					if ( signal ) {
						showTransactionBox();
					}
				} else {
					displayFeedback('Please fill Salary.');
				}
			} else {
				displayFeedback('Please fill post name.');
			}
		} else {
			displayFeedback('Please fill employee name.');
		}
	});
	$('#add_user').click(function() {
		var username = $('#username').val();
		var name = $('#name').val();
		var email = $('#email').val();
		var dob = $('#dob').val();
		var roll = $('#roll').val();
		var room = $('#room').val();
		var mobile = $('#mobile').val();
		var gender = $('#gender').val();
		var balance = $('#balance').val();
		var subsidized;
		$('input[type="radio"][name="subsidized"]').each(function(){
			if ( $(this).is(':checked') ) {
				subsidized = $(this).val();
			}
		});
		if ( ( username > 99 ) ) {
			if ( name.length > 0 ) {
				if ( validateEmail( email ) ) {
					if ( validateRegex( /^\d{4}-\d{2}-\d{2}$/, dob ) ) {
						if ( roll > 0 ) {
							if ( room >= 0 ) {
								if ( mobile > 0 ) {
									if ( image != null ) {
										mode = 'add_user';
										$('#dialog-content').text('Do you want to add this user?');
										showDialog();
									} else {
										displayFeedback('Please select an image first.');
									}
								} else {
									displayFeedback('Please enter a valid mobile number.');
								}
							} else {
								displayFeedback('Please enter a valid room number.');
							}
						} else {
							displayFeedback('Please enter a valid roll number.');
						}
					} else {
						displayFeedback('Please enter date in yyyy-mm-dd format.');
					}
				} else {
					displayFeedback('Please enter a valid email.');
				}
			} else {
				displayFeedback('Please Fill user\'s name.');
			}
		} else {
			displayFeedback('Username is too short.');
		}
	});
	$('#username').blur(function() {
		var username = $(this).val();
		$('#name').focus();
		if ( username > 99 ) {
			var data = {type:{},values:{}};
			data.type['name'] = 'avail';
			data.values['id'] = username;
			var json = JSON.stringify(data);
			$.ajax({
				type: 'POST',
				url: 'accounts.php',
				data: 'data='+json,
				success: function(result){
					if ( result.length > 0 ) {
						displayFeedback( result );
					}
				}
			});
		} else {
			displayFeedback('Username is too short.');
		}
	});
	$('#no').click(function() {
		allowAnimation();
		hideDialog();
	});
	function showMoneyBox() {
		$('#dialog-overlay').slideDown(100);
		$('#money-box').fadeIn(400);
	}
	function uploadImage(serial, msg) {
		var formData = new FormData();
		formData.append('serial', serial);
		formData.append('file', image);
		$.ajax({
		 	type: 'POST',
		 	url: 'saveImage.php',
		 	data: formData,
		 	success: function(data) {
		 		$('#disable').hide();
		 		displayFeedback(msg);
		 		$('#frame img').attr('src', '');
		 		image = null;
		 		getUserList();
		 		stopLoadScreen();
		 	},
		 	error: function() {
		 		$('#disable').hide();
		 		stopLoadScreen();
		 		displayFeedback('Image Uploaded failed.');
		 	},
		 	cache: false,
		 	contentType: false,
			processData: false
		});
	}
	function addUser() {
		load = true;
		startLoadScreen();
		$('#load-window').fadeIn('fast');
		var subsidized;
		var rememberPassword;
		$('input[type="radio"][name="subsidized"]').each(function(){
			if ( $(this).is(':checked') ) {
				subsidized = $(this).val();
			}
		});
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
		data.type['name'] = 'add_user';
		data.type['rememberPassword'] = rememberPassword;
		if ( rememberPassword != 'c' ) {
			data.values['password'] = $('#password').val();
		}
		data.values['username'] = $('#username').val();
		data.values['name'] = $('#name').val();
		data.values['email'] = $('#email').val();
		data.values['dob'] = $('#dob').val();
		data.values['roll'] = $('#roll').val();
		data.values['room'] = $('#room').val();
		data.values['mobile'] = $('#mobile').val();
		data.values['gender'] = $('#gender').val();
		data.values['subsidized'] = subsidized;
		data.values['balance'] = $('#balance').val();
		var json = JSON.stringify(data);
		$.ajax({
			type: 'POST',
			url: 'accounts.php',
			data: 'data='+json,
			success: function(result) {
				allowAnimation();
				$('#password').val('');
				var response = $.parseJSON(result);
				var status = response['response'][0]['status'];
				var msg = response['response'][0]['msg'];
				if ( status == 0 ) {
					var serial = response['response'][1]['username'];
					uploadImage(serial, msg);
					$('#account_form :input').each(function() {
						$(this).val('');
					});
				} else {
					stopLoadScreen();
					displayFeedback(msg);
					$('#disable').hide();
				}
				if ( ( status != 3 ) && ( ( response['response'][2]['rememberPassword'] == 'y') || ( response['response'][2]['rememberPassword'] == 'c' ) ) ) {
					writeCookie(new Cookie('transact_password', 'c', null));
				}
				password = readCookie('transact_password');
			},
			error: function() {
				allowAnimation();
				stopLoadScreen();
				displayFeedback('Connection Failed.');
			}
		});
	}
	function getUserList() {
		var data = {type:{}};
		data.type['name'] = 'userList';
		var json = JSON.stringify(data);
		$.ajax({
			type: 'POST',
			url: 'accounts.php',
			data: 'data='+json,
			success: function(response){
				$('#userList').html(response);
				getEmpList();
				$('.contents').bind({
					mouseenter: function(){
						if ( animate ) {
							$(this).find('.hover').stop().fadeIn('fast').find('.leftoptions').stop().animate({'left':'-10px'}, 'slow');
							$(this).find('.rightoptions').stop().animate({'right':'-10px'}, 'slow');
						}
					},
					mouseleave: function() {
						if ( animate ) {
							$(this).find('.hover').stop().fadeOut('slow').find('.leftoptions').stop().animate({'left':'-130px'}, 'fast');
							$(this).find('.rightoptions').stop().animate({'right':'-170px'}, 'fast');
						}
					}
				});
				$('.close').bind('click', function(e) {
					e.stopPropagation();
					animate = false;
					account = $(this).parent().attr('serial');
					mode = $(this).attr('mode');
					$('#dialog-content').text('Do you want to '+mode+' this account?');
					showDialog();
				});
				$('.remove').bind('click', function(e) {
					e.stopPropagation();
					animate = false;
					account = $(this).parent().attr('serial');
					$('#dialog-content').text('Do you want to remove this account?');
					mode = 'remove';
					showDialog();
				});
				$('.money, .fine').bind('click', function(e){
					e.stopPropagation();
					animate = false;
					account = $(this).parent().attr('serial');
					if ( this.classList[1] == 'money' ) {
						mode = 'money';
					} else if ( this.classList[1] == 'fine' ) {
						mode = 'fine';
					}
					showMoneyBox();
				});
				$('.contents').bind('click', function() {
					$('#dialog-overlay').fadeIn(100);
					$('#details').fadeIn(300);
					var serial = $(this).find('.hover').attr('serial');
					var data = {type:{},values:{}};
					data.type['name'] = 'details';
					data.values['account'] = serial;
					var json = JSON.stringify(data);
					$('#details').html('<span id=\'load\'>Loading...</span>');
					$.ajax({
						type: 'POST',
						url: 'accounts.php',
						data: 'data='+json,
						success: function(result) {
							$('#details').html(result);
						}
					});
				});
			}
		});
	}
	function accounts() {
		$('#disable').show();
		$('#feedback').text('Processing...').show();
		load = true;
		startLoadScreen();
		$('#load-window').fadeIn('fast');
		if ( password == null ) {
			if ($('#check').is(':checked')) {
				rememberPassword = 'y'; // first time checked
			} else {
				rememberPassword = 'n'; // not checked
			}
		} else {
			rememberPassword = 'c'; // checked and stored in cookie
		}
		var data = {type:{},values:{}};
		data.type['name'] = 'cr'; // close/remove
		data.values['account'] = account;
		data.values['mode'] = mode;
		data.type['rememberPassword'] = rememberPassword;
		if ( rememberPassword != 'c' ) {
			data.values['password'] = $('#password').val();
		}
		var json = JSON.stringify(data);
		$.ajax({
			type: 'POST',
			url: 'accounts.php',
			data:  'data='+json,
			success: function(result) {
				var response = $.parseJSON(result);
				var status = response['response'][0]['status'];
				var msg = response['response'][0]['msg'];
				if ( status == 0 ) {
					getUserList();
				}
				if ( ( status != 2 ) && ( ( response['response'][1]['rememberPassword'] == 'y') || ( response['response'][1]['rememberPassword'] == 'c' ) ) ) {
					writeCookie(new Cookie('transact_password', 'c', null));
				}
				password = readCookie('transact_password');
				$('#disable').hide();
				stopLoadScreen();
				displayFeedback(msg);
				allowAnimation();
			},
			error: function() {
				allowAnimation();
				stopLoadScreen();
				$('#disable').hide();
				displayFeedback('Connection Error!');
			}
		});
	}
	function moneyTransaction() {
		$('#disable').show();
		$('#feedback').text('Processing...').show();
		load = true;
		startLoadScreen();
		$('#load-window').fadeIn('fast');
		var rememberPassword;
		var amount = $('#amount').val();
		var data = {type:{}, values:{}};
		data.type['name'] = mode;
		mode = 'none';
		data.values['amount'] = $('#amount').val();
		data.values['account'] = account;
		if ( password == null ) {
			if ($('#check').is(':checked')) {
				rememberPassword = 'y'; // first time checked
			} else {
				rememberPassword = 'n'; // not checked
			}
		} else {
			rememberPassword = 'c'; // checked and stored in cookie
		}
		data.type['rememberPassword'] = rememberPassword;
		if ( rememberPassword != 'c' ) {
			data.values['password'] = $('#password').val();
		}
		var json = JSON.stringify(data);
		$.ajax({
			type: 'POST',
			url: 'accounts.php',
			data: 'data='+json,
			success: function(result){
				var response = $.parseJSON(result);
				var status = response['response'][0]['status'];
				var msg = response['response'][0]['msg'];
				if ( status == 0 ) {
					getUserList();
				}
				if ( ( status != 2 ) && ( ( response['response'][1]['rememberPassword'] == 'y') || ( response['response'][1]['rememberPassword'] == 'c' ) ) ) {
					writeCookie(new Cookie('transact_password', 'c', null));
				}
				password = readCookie('transact_password');
				$('#disable').hide();
				displayFeedback(msg);
				stopLoadScreen();
				allowAnimation();
			},
			error: function() {
				$('#disable').hide();
				allowAnimation();
				stopLoadScreen();
				displayFeedback('Connection Error!');
			}
		});
	}
	function addEmp() {
		$('#disable').show();
		$('#feedback').text('Processing...').show();
		load = true;
		startLoadScreen();
		$('#load-window').fadeIn('fast');
		var name = $('#emp_name').val();
		var post = $('#emp_post').val();
		var salary = $('#emp_salary').val();
		var data = {type:{},values:{}};
		data.type['name'] = 'add_emp';
		data.values['name'] = name;
		data.values['post'] = post;
		data.values['salary'] = salary;
		var rememberPassword;
		mode = 'none';
		if ( password == null ) {
			if ($('#check').is(':checked')) {
				rememberPassword = 'y'; // first time checked
			} else {
				rememberPassword = 'n'; // not checked
			}
		} else {
			rememberPassword = 'c'; // checked and stored in cookie
		}
		data.type['rememberPassword'] = rememberPassword;
		if ( rememberPassword != 'c' ) {
			data.values['password'] = $('#password').val();
		}
		var json = JSON.stringify(data);
		$.ajax({
			type: 'POST',
			url: 'accounts.php',
			data: 'data='+json,
			success: function(result) {
				var response = $.parseJSON(result);
				var status = response['response'][0]['status'];
				var msg = response['response'][0]['msg'];
				if ( status == 0 ) {
					$('#emp_name').val('');
					$('#emp_post').val('');
					$('#emp_salary').val('');
					getEmpList();
				}
				if ( ( status != 2 ) && ( ( response['response'][1]['rememberPassword'] == 'y') || ( response['response'][1]['rememberPassword'] == 'c' ) ) ) {
					writeCookie(new Cookie('transact_password', 'c', null));
				}
				password = readCookie('transact_password');
				$('#disable').hide();
				$('#feedback').hide();
				stopLoadScreen();
				displayFeedback(msg);
			},
			error: function() {
				$('#disable').hide();
				$('#feedback').hide();
				stopLoadScreen();
				displayFeedback('Connection Error!');
			}
		});
	}
	function allowAnimation() {
		animate = true;
		$('.hover').each(function(){
			$(this).stop().fadeOut('slow');
		});
	}
	function getEmpList() {
		var data = {type:{}};
		data.type['name'] = 'emp_list';
		var json = JSON.stringify(data);
		$.ajax({
			type: 'POST',
			url: 'accounts.php',
			data: 'data='+json,
			success: function(result) {
				$('#empList').html(result);
				$('.remove_emp').bind('click', function() {
					code = $(this).attr('code');
					mode = 'remove_emp';
					$('#dialog-content').text('Are you sure you want to remove this employee.');
					hideDialog();
					setTimeout(function() {
						showDialog();
					}, 300);
				});
			}
		});
	}
	function removeEmp() {
		$('#disable').show();
		$('#feedback').text('Processing...').show();
		load = true;
		startLoadScreen();
		$('#load-window').fadeIn('fast');
		var data = {type:{},values:{}};
		data.type['name'] = 'remove_emp';
		data.values['id'] = code;
		var rememberPassword;
		mode = 'none';
		if ( password == null ) {
			if ($('#check').is(':checked')) {
				rememberPassword = 'y'; // first time checked
			} else {
				rememberPassword = 'n'; // not checked
			}
		} else {
			rememberPassword = 'c'; // checked and stored in cookie
		}
		data.type['rememberPassword'] = rememberPassword;
		if ( rememberPassword != 'c' ) {
			data.values['password'] = $('#password').val();
		}
		var json = JSON.stringify(data);
		$.ajax({
			type: 'POST',
			url: 'accounts.php',
			data: 'data='+json,
			success: function(result) {
				var response = $.parseJSON(result);
				var status = response['response'][0]['status'];
				var msg = response['response'][0]['msg'];
				if ( status == 0 ) {
					getEmpList();
				}
				$('#disable').hide();
				$('#feedback').hide();
				displayFeedback(msg);
				stopLoadScreen();
			},
			error: function() {
				stopLoadScreen();
				$('#disable').hide();
				displayFeedback('Connection Error!');
			}
		});
	}
});
window.onload=function() {
	$('.content').each(function(){
		if ( $(this).attr('serial') == 1 ) {
			$(this).show();
		}
	});
}