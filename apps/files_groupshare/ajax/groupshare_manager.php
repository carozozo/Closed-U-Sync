<?php
require_once ('../../../lib/base.php');

OC_JSON::checkLoggedIn();
OC_JSON::checkAppEnabled('files_groupshare');

$user = OC_User::getUser();
$userDirectory = "/" . $user . "/files";
$action = $_REQUEST['action'];
$source = "";
$permission = "";

if (!empty($_REQUEST['source'])) {
    $source = $_REQUEST['source'];
}

try {
    $result = "";
    if ($action == "getGroupShareManagerList") {
        $sortBy = ($_REQUEST['sortBy']) ? $_REQUEST['sortBy'] : 'path';
        $sort = ($_REQUEST['sort']) ? $_REQUEST['sort'] : 'ASC';
        $result = OC_GroupShare_Manager::getGroupShareManagerList(null, $sortBy, $sort);
    } else if ($action == "updatePermission") {
        $source = $userDirectory . $source;
        $permission = $_REQUEST['permission'];
        $result = OC_GroupShare_Manager::updatePermission(NULL, $source, $permission);
    } else if ($action == "removeGroupShare") {
        $source = $userDirectory . $source;
        $result = OC_GroupShare_Manager::removeGroupShare(NULL, $source);
    }

    OC_JSON::success(array('result' => $result));
} catch (Exception $exception) {
    OC_Log::writeException('files_groupshare', 'ajax goupshare_manager', $e);
    OC_JSON::error();
}
?>