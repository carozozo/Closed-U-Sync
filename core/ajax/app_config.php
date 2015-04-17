<?php
$RUNTIME_NOAPPS = true;
require_once ('../../lib/base.php');

if (!OC_User::isLoggedIn() || !isset($_REQUEST['action'])) {
	exit ;
}

$action = $_REQUEST['action'];
switch($action) {
	case'getValue' :
		$key = $_REQUEST['key'];
		$defaultVal = $_REQUEST['defaultVal'];
		$appId = $_REQUEST['appId'];
		$result = OC_Appconfig::getValue($appId, $key, $defaultVal);
		OC_JSON::success(array('result' => $result));
		break;
	default :
		break;
}
?>
