$(document).ready(function  () {
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
	$('.notice_wrap').on('mouseenter', function() {
		$(this).find('.notice_view').stop().animate({ 'top': '150px' }, 400);
	}).on('mouseleave', function() {
		$(this).find('.notice_view').stop().animate({ 'top': '220px' }, 400);
	});
	$('.notifs_wrap').on('mouseenter', function() {
		$(this).find('.notifs_view').stop().animate({ 'top': '100px' }, 400);
	}).on('mouseleave', function() {
		$(this).find('.notifs_view').stop().animate({ 'top': '170px' }, 400);
	});
	$('.notice_view').click(function() {
		var image = $(this).parent().attr('imgpath');
		var heading = $(this).next().text();
		var text = $(this).next().next().next().html();
		var str = "";
		if ( image.indexOf('none') >= 0 )
			str = "<h1>"+heading+"</h1><br />"+text+"<br />";
		else
			str = "<h1>"+heading+"</h1><br />"+text+"<br /><img src='"+image+"'/>";
		$('#preview_content').html(str);
		$('#preview').fadeIn();
	});
	$('.notifs_view, .notice_view').click(function() {
		var parent = $(this).parent();
		if ( parent.hasClass('new') ) {
			parent.removeClass('new').addClass('old');
			$.ajax({
				url: 'process.php',
				type: 'POST',
				data: 'data='+parent.attr('serial'),
				success: function(result) {
					var response = $.parseJSON(result);
					var notifs = parseInt(response[0]);
					var notice = parseInt(response[1]);
					var total = notice+notifs;
					$('#notifs').text('Notifications'+(notifs>0?'('+notifs+')':''));
					$('#notice').text('Notice'+(notice>0?'('+notice+')':''));
					$('#notifications').text('Notifications'+(total>0?'('+total+')':''))
				}
			});
		}
	});
	$('.notifs_view').click(function() {
		var parent = $(this).parent();
		$('#preview_notifs_head').text(parent.children('.notifs_head').text());
		$('#preview_notifs_content').text(parent.children('.notifs_content').text());
		$('#overlay').fadeIn();
	});
	$('#overlay').click(function() {
		$(this).fadeOut();
	}).children().each(function() {
		$(this).click(function(e){
			e.stopPropagation();
		});
	});
	$('#close_preview').click(function() {
		$('#preview').fadeOut();
	});
});
window.onload=function() {
	$('.content').each(function(){
		if ( $(this).attr('serial') == 1 ) {
			$(this).show();
		}
	});
}