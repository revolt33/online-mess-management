var load = false;
function hideDialog() {
	$('#dialog-overlay').children().each(function() {
		$(this).fadeOut('fast');
	});
	$('#dialog-overlay').slideUp('fast');
}
function showDialog() {
	$('#dialog-overlay').slideDown('fast');
	$('#dialog-box').fadeIn(400);
}
function showTransactionBox() {
	$('#dialog-overlay').slideDown('fast');
	$('#transaction-box').fadeIn(400);
}
function displayFeedback( msg ) {
	$('#feedback').text(msg).fadeIn('fast').delay(1500).fadeOut('slow');
}
function startLoadScreen() {
	$('#floating-bar').css( 'left', -90 );
	if ( load == true )
		$('#floating-bar').animate({ left: "+=290px" }, { duration: 1300, easing: "linear", complete: startLoadScreen });
}
function stopLoadScreen() {
	load = false;
	$('#load-window').fadeOut('fast');
}
$(document).scroll(function() {
	if ( ($(this).scrollTop() - window.innerHeight/2 ) > 0 ) {
		$('#scroll').fadeIn();
	} else {
		$('#scroll').fadeOut();
	}
});
$('#scroll').click(function() {
	$('html, body').animate({ scrollTop: 0}, 1500 );
});
$('#logout').click(function() {
	eraseCookie('username');
	eraseCookie('remember');
	eraseCookie('type');
	eraseCookie('transact_password');
});
function getPosition(element) {
    var xPosition = 0;
    var yPosition = 0;
  
    while(element) {
        xPosition += (element.offsetLeft - element.scrollLeft + element.clientLeft);
        yPosition += (element.offsetTop - element.scrollTop + element.clientTop);
        element = element.offsetParent;
    }
    return { x: xPosition, y: yPosition };
}