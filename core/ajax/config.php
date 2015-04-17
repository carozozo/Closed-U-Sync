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
		$configName = $_REQUEST['configName'];
		# 只要前端要抓的不是主要設定
		if ($configName != 'CONFIG') {
			$result = OC_Config::getValue($key, $defaultVal, $configName);
			OC_JSON::success(array('result' => $result));
		}
		break;
	default :
		break;
}
?>
