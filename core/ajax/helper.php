<?php
$RUNTIME_NOAPPS = true;
require_once ('../../lib/base.php');

if (!OC_User::isLoggedIn() || !isset($_REQUEST['action'])) {
	exit ;
}

$action = $_REQUEST['action'];
switch($action) {
	case'randomPassword' :
		$length = $_REQUEST['length'];
		$ifEng = $_REQUEST['ifEng'];
		$ifNum = $_REQUEST['ifNum'];
		$ifUpper = $_REQUEST['ifUpper'];
		$result = OC_Helper::randomPassword($length, $ifEng, $ifNum, $ifUpper);
		OC_JSON::success(array('result' => $result));
		break;
	case'audioTypeArr' :
		$result = OC_Helper::audioTypeArr();
		OC_JSON::success(array('result' => $result));
		break;
	case'mediaTypeArr' :
		$result = OC_Helper::mediaTypeArr();
		OC_JSON::success(array('result' => $result));
		break;
	default :
		break;
}
?>
