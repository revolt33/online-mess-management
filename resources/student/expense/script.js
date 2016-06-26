$(document).ready(function () {
	$('.tab').click(function() {
		var serial = $(this).attr('serial');
		$(this).parent().children('.tab').removeClass('selected').addClass('border');
		$(this).addClass('selected').removeClass('border');
		$(this).parent().next().children('.content').each(function() {
			if ( $(this).attr('serial') != serial ) {
				$(this).hide();
			} else {
				$(this).fadeIn(200);
			}
		});
	});
	$(document).on('mouseenter', '.hover', function() {
		$('#hovertext').stop().text($(this).attr('hovertext')).fadeIn();
	});
	$(document).on('mousemove', '.hover', function(o) {
		$('#hovertext').css('top', o.pageY + 12).css('left', o.pageX + 12);
	});
	$(document).on('mouseleave', '.hover', function() {
		$('#hovertext').stop().fadeOut();
	});
	$(document).on( 'click', '.image', function() {
		$('#image-viewer').fadeIn('fast');
		loadScreen();
		$('#image-viewer img').removeAttr('src');
		$('#image-viewer img').attr('src', $(this).attr('location')).load(stopLoadScreen);
	});
	$('#close-button').click(function() {
		stopLoadScreen();
		$('#image-viewer').fadeOut('fast');
	});
	function loadScreen () {
		load = true;
		$('#load-window').fadeIn('fast');
		startLoadScreen();
	}
});
window.onload=function() {
	$('.content').each(function(){
		if ( $(this).attr('serial')%10 == 1 ) {
			$(this).show();
		}
	});
}