$(document).ready(function() {
	$.post(OC.linkTo('check_email_nickname', 'ajax/check_email_nickname.php'), function(data) {
		var personalPath = "/settings/personal.php";
		if (data != "pass" && window.location.pathname.indexOf(personalPath) < 0 ) {
			var message = t('check_email_nickname','Please insert Email and Nickname');
			window.location.href = OC.webroot + personalPath;
		}
	});
});