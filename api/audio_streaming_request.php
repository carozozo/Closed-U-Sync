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
		$linkUrl = OC_AudioStreaming::getStreamingSource($dir, $fileName, false);
		if ($linkUrl) {
			OC_JSON::success(array('message' => $linkUrl));
		} else {
			OC_JSON::error(array('message' => $linkUrl));
		}
		break;
	default :
		OC_JSON::error();
		break;
}
