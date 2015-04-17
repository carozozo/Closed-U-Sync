<?php
$RUNTIME_NOSETUPFS = true;
$RUNTIME_NOWEBFILES = true;
require_once ('../lib/base.php');
$params = OC_API::checkApiUser();
$userId = $params['userId'];
$action = $params['action'];

switch ($action) {
	case 'showConvertTimesMess' :
		$messArr = OC_MediaConvert::showConvertTimesMess();
		OC_JSON::success(array('messArr' => $messArr));
		break;
	case 'convertMedia' :
		$dir = $_REQUEST['dir'];
		$fileName = $_REQUEST['fileName'];
		$deviceType = $_REQUEST['deviceType'];
		$returnArr = OC_MediaConvert::convertMedia($dir, $fileName, $deviceType);
		if ($returnArr['status'] == 'success') {
			OC_JSON::success(array('message' => $returnArr['message']));
		} else {
			OC_JSON::error(array('message' => $returnArr['message']));
		}
		break;
	default :
		OC_JSON::error();
		break;
}
