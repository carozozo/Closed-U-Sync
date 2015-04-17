<?php
require_once ('../../../lib/base.php');

OC_Util::checkLoggedIn();
OC_JSON::checkAppEnabled('media_streaming');

$action = $_REQUEST['action'];
switch ($action) {
	case 'getStreamingSource' :
		$dir = $_REQUEST['dir'];
		$fileName = $_REQUEST['fileName'];
		# v1.6版的streaming沒有deviceType參數
		$deviceType = ($_REQUEST['deviceType']) ? $_REQUEST['deviceType'] : '';
		$returnArr = OC_MediaStreaming::getStreamingSource($dir, $fileName, $deviceType);
		if ($returnArr['status'] == 'success') {
			OC_JSON::success(array('message' => $returnArr['message']));
		} else {
			OC_JSON::error(array('message' => $returnArr['message']));
		}
		break;
	case 'checkM3u8Exists' :
		$hlsUrl = $_REQUEST['hlsUrl'];
		$result = OC_MediaStreaming::checkM3u8Exists($hlsUrl);
		if ($result) {
			# $result = 1 or -1
			OC_JSON::success(array('message' => $result));
		} else {
			# $result = null
			OC_JSON::error();
		}
		break;
	default :
		OC_JSON::error();
		break;
}
?>