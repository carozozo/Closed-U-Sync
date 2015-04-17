<?php
$RUNTIME_NOSETUPFS = true;
$RUNTIME_NOWEBFILES = true;
require_once ('../lib/base.php');
$params = OC_API::checkApiUser();
$userId = $params['userId'];
$action = $params['action'];

switch ($action) {
	case 'getStreamingSource' :
		$dir = $_REQUEST['dir'];
		$fileName = $_REQUEST['fileName'];
		$deviceType = (isset($_REQUEST['deviceType'])) ? $_REQUEST['deviceType'] : '';
		$returnArr = OC_MediaStreaming::getStreamingSource($dir, $fileName, $deviceType);
		$message = $returnArr['message'];
		if ($returnArr['status'] == 'success') {
			# device無法播放https的網址,所以將https改為http
			$message = preg_replace('#' . preg_quote('https://') . '#', 'http://', $message);
			OC_JSON::success(array('message' => $message));
		} else {
			OC_JSON::error(array('message' => $message));
		}
		break;
	case 'checkM3u8Exists' :
		$hlsUrl = $_REQUEST['hlsUrl'];
		$result = OC_MediaStreaming::checkM3u8Exists($hlsUrl);
		if ($result) {
			//$result = 1 or -1
			OC_JSON::success(array('message' => $result));
		} else {
			//$result = null
			OC_JSON::error();
		}
		break;
	default :
		OC_JSON::error();
		break;
}
