<?php
require_once ('../../../lib/base.php');

OC_JSON::checkLoggedIn();
OC_JSON::checkAppEnabled('audio_streaming');

$action = $_REQUEST['action'];
switch ($action) {
	case 'getStreamingSource' :
		$dir = $_REQUEST['dir'];
		$fileName = $_REQUEST['fileName'];
		$linkUrl = OC_AudioStreaming::getStreamingSource($dir, $fileName);
		if ($linkUrl) {
			OC_JSON::success(array('message' => $linkUrl));
		} else {
			OC_JSON::error();
		}
		break;
	default :
		break;
}
?>