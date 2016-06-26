$(document).ready(function() {
	var clear = true;
	var freq = 200;
	var node;
	var loading = '';
	var subText = '';
	var decrease = true;
	var user_id, user_type;
	remember();
	function remember() {
		user_id = readCookie('username');
		user_type = readCookie('type');
		var remember = readCookie('remember');
		if ( (user_id != null) && (user_type != null ) && (remember != null) ) {
			if ( remember.getValue() == 'y' ) {
				var data = {type:{},data:{}};
				data.type['type']='remember';
				data.data['username']=user_id.getValue();
				data.data['usertype']= user_type.getValue();
				var json = JSON.stringify(data);
				login(json).done(function(data) {
					data = $.parseJSON(data);
					var status = data['response'][0]['status'];
					status = parseInt(status);
					if ( status == 0 ) {
						window.location = 'resources';
					} else {
						welcome();
					}
				}).fail(function() {
					welcome();
				});
			} else {
				welcome();
			}
		} else {
			welcome();
		}
	}
	function login(json) {
		return $.ajax({
			type:'POST',
			url:'login.php',
			data: 'data='+json
		});
	}
	function clearFeedback() {
		loading = '';
		subText = '';
		$('#feedback').hide();
		$('#disable').hide();
	}
	function successful(data) {
		data = $.parseJSON(data);
		var status = data['response'][0]['status'];
		var msg = data['response'][0]['msg'];
		if ( status == 0 ) {
			$('#feedback').text(msg).fadeIn('fast');
			var username = data['response'][1]['id'];
			var type = data['response'][1]['type'];
			var remember = data['response'][1]['remember'];
			var valid = data['response'][1]['valid'];
			valid = parseInt(valid);
			valid += 1;
			writeCookie( new Cookie('username', username, valid));
			writeCookie( new Cookie('type', type, valid));
			writeCookie( new Cookie('remember', remember, valid));
			setTimeout(function() {
					node = $('#feedback');
					loading = 'Redirecting...';
					subText = loading;
					clear = false;
					callAgain();
			}, 1000);
			setTimeout(function() {
				clear = true;
				window.location = 'resources';
			}, 2000)
		} else {
			$('#feedback').text(msg).fadeIn('fast').delay(2000).fadeOut('slow');
		}
	}
	function failed() {
		var msg = 'Some Error Occured.'
		$('#feedback').text(msg).fadeIn('fast').delay(2000).fadeOut('slow');
	}
	function blurCheck() {
		validate(this);
	}
	function validate(element) {
		var value = element.value;
		var text = $(element).attr('help');
		if (value.length == 0 ) {
			$('#feedback').stop().text(text).fadeIn('fast').delay(2000).fadeOut('slow');
			return false;
		} else {
			$('#feedback').stop().hide().text('');
			return true;
		}
	}
	function pullDown() {
		var type = $(this).attr('type');
		$('#type').val(type);
		$('#dialog-overlay').slideDown(200, function() {
			$('#dialog-box').fadeIn(300);
			$('#username').focus();
		});
	}
	function flyOut() {
		$('#feedback').hide();
		$('#dialog-box').fadeOut(200, function() {
			$('#dialog-overlay').slideUp(300);
		});
	}
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
			var oldSrc = $('#showcase img').attr('src');
			var newSrc = $(this).attr('image');
			if ( newSrc != oldSrc ) {
				var pass = this;
				$('#showcase img').fadeTo(500, 0, showImage(pass));
			}
		}
	}
	function showImage(element) {
		loading = 'Loading...';
		$('#showcase span').html(loading);
		subText = loading;
		clear = false;
		node = $('#showcase span');
		callAgain();
		var width = $('#showcase').css('width');
		var height = $('#showcase').css('height');
		width = parseInt(width) - 60;
		height = parseInt(height) - 10;
		var image = $(element).attr('image');
		setTimeout(function(){
			$('#showcase img').attr('src', image).attr('width', ''+width+'px').attr('height', ''+height+'px').load(function() {
				
				$(this).fadeTo(500, 1, function() {
					loading = '';
					subText = '';
					clear = true;

				})
			});

		 }, 1000);
	}
	function callAgain() {
		setTimeout(loadingText, freq);
	}
	function loadingText() {
			if ( subText.length < (loading.length - 2) ) {
				decrease = false;
			} else if (subText.length == loading.length) {
				decrease = true;
			}
			if (decrease) {
				subText = loading.substring(0, (subText.length - 1));
			} else {
				subText = loading.substring(0, (subText.length + 1));
			}
			node.text(''+subText);
			if (!clear)
				callAgain();
	}
	function welcome() {
		$('body').load('welcome.php',  function() {
			$('.login').bind('click', pullDown);
			$('#dialog-overlay').bind('click', flyOut).children().bind('click', function() {
				return false;
			});
			$('#cssmenu li.active').addClass('open').children('ul').show();
			$('#cssmenu li.has-sub>a').bind('click', accordion);
			$('input').bind('blur', blurCheck);
			$('#fp').bind('click', forgotPassword);
			$('#submit').bind('click', function() {
				var status = true;
				$('input').each(function() {
					if (!validate(this)) {
						status = false;
						return false;
					}
				});
				if (status) {
					loading = 'Authenticating...';
					subText = loading;
					node = $('#feedback');
					$('#feedback').text(loading).fadeIn(200);
					$('#disable').show();
					clear = false;
					callAgain();
					var username = $('#username').val();
					var password = $('#password').val();
					var type = $('#type').val();
					var data = {type:{},data:{}};
					data.type['type']='login';
					data.data['username']=username;
					data.data['password']=password;
					data.data['type']=type;
					json = JSON.stringify(data);
					login(json).done(function(data) {
						clear = true;
						setTimeout(function() {
							clearFeedback();
							successful(data);
						}, 300);
					}).fail(function() {
						clear = true;
						setTimeout(function() {
							clearFeedback();
							failed();
						}, 300);
					});
				}
			});
		});
	}
	function forgotPassword() {
		$('#feedback').stop().hide();
		var value = $('#username').val();
		var text = $('#username').attr('help');
		if ( value.length == 0 || value == null ) {
			$('#feedback').delay(300).text(text).fadeIn('fast').delay(1000).fadeOut('slow');
		} else {
			var type = $('#type').val();
			forgot(value, type).done(function(data) {
				var result = $.parseJSON(data);
				var status = result['response'][0]['status'];
				status = parseInt(status);
				var msg = result['response'][0]['msg'];
				if ( status == 0 ) {
					loading = msg;
					subText = loading;
					clear = false;
					node = $('#feedback');
					callAgain();
					$('#feedback').fadeIn('fast');
					setTimeout(function() {
						window.location = 'password/forgot.php?user_id='+value+'&user_type='+type;
					}, 1000);
				} else {
					$('#feedback').text(msg).fadeIn('fast').delay(2000).fadeOut('slow');
				}
			}).fail(function() {
				$('#feedback').text('Connection Error!').fadeIn('fast').delay(2000).fadeOut('slow');
			});
		}
	}
	function forgot(value, type) {
		return $.ajax({
				type: 'POST',
				url: 'password/forgot.php',
				data: 'username='+value+'&usertype='+type
			});
	}
});