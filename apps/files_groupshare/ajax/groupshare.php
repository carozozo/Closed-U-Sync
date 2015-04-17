<?php
require_once ('../../../lib/base.php');

OC_JSON::checkLoggedIn();
OC_JSON::checkAppEnabled('files_groupshare');

$user = OC_User::getUser();
$userDirectory = "/" . $user . "/files";
$action = $_REQUEST['action'];
$source = "";
$permission = "";

try {
	$result = "";
	if ($action == "getGroupShareByUidSharedWith") {
		$result = OC_GroupShare::getGroupShareByUidSharedWith();
	}
	OC_JSON::success(array('result' => $result));
} catch (Exception $exception) {
	OC_Log::writeException('files_groupshare', 'ajax goupshare', $e);
	OC_JSON::error();
}
?>