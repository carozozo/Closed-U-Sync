<?php
require_once ('../../../lib/base.php');

OC_JSON::checkLoggedIn();
OC_JSON::checkAppEnabled('files_groupshare');

$user = OC_User::getUser();
$userDirectory = "/" . $user . "/files";
$action = $_REQUEST['action'];
$source = $_REQUEST['source'];

try {
	//Make sure file exists and can be shared
	// if ( $source && OC_FILESYSTEM::file_exists($source) && OC_FILESYSTEM::is_readable($source) ) {
	if ( $source && OC_FILESYSTEM::file_exists($source)) {
		$source = $source;
		$source = $userDirectory . $source;
		$result = "";
		switch($action) {
			case "getGroupShareList" :
				$result = OC_GroupShare_Handler::getGroupShareList(NULL, $source);
				break;
			case "updateGroupShare" :
				$gids = $_REQUEST['gids'];
				$uids = $_REQUEST['uids'];
				$result = OC_GroupShare_Handler::updateGroupShare(NULL, $source, $gids, $uids);
				break;
			case "getGroupShareCount" :
				$result = OC_GroupShare_Handler::getGroupShareCount(NULL, $source);
				break;
			default :
				break;
		}
		OC_JSON::success(array('result' => $result));
		// If the file doesn't exist, it may be shared with the current user
	} else {
		OC_Log::write('files_groupshare', 'Source not exists:' . $source, OC_Log::WARN);
		OC_JSON::error();
		exit ;
	}
} catch (Exception $exception) {
	OC_Log::writeException('files_groupshare', 'ajax goupshare_handler', $e);
	OC_JSON::error();
}
?>