<?php
require_once('../../../lib/base.php');

// OC_JSON::checkLoggedIn();
OC_JSON::checkAppEnabled('check_email_nickname');

if(!OC_User::isLoggedIn()){
	# 還沒登入，所以不檢查
	print "pass";
	exit();
}
$email = OC_Preferences::getValue(OC_User::getUser(), 'settings', 'email', '');
$nickname = OC_Preferences::getValue(OC_User::getUser(), 'settings', 'nickname', '');
if ($email != "" && $nickname != "") {
	print "pass";
} else {
	print "no pass";
}
?>