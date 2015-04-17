<?php
$RUNTIME_NOAPPS = true;
require_once ('../../lib/base.php');

if (!OC_User::isLoggedIn() || !isset($_REQUEST['action'])) {
	exit ;
}

$action = $_REQUEST['action'];
switch($action) {
	case'isEnabled' :
		$appId = $_REQUEST['appId'];
		if ($isEnabled = OC_App::isEnabled($appId)) {
			OC_JSON::success();
		} else {
			OC_JSON::error();
		}
		break;
	default :
		break;
}
?>
