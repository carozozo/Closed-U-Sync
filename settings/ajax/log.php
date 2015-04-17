<?php

// Init owncloud
require_once ('../../lib/base.php');
OC_JSON::checkAdminUser();
// OC_JSON::setContentTypeHeader();

$action = $_REQUEST['action'];
switch($action) {
	case 'getLog' :
		$entries = OC_Log::getEntries();
		OC_JSON::success(array('entries' => $entries));
		break;
	case 'clearLog' :
		OC_Log::clearLog();
		break;
	default :
		OC_JSON::success(array('entries' => $entries));
		break;
}
?>