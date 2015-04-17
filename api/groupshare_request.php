<?php
$RUNTIME_NOSETUPFS = true;
$RUNTIME_NOWEBFILES = true;
require_once ('../lib/base.php');
$params = OC_API::checkApiUser();
$userId = $params['userId'];
$action = $params['action'];

header('Content-Type: application/json; charset=utf-8');
$source = (!empty($_REQUEST['source'])) ? "/" . $userId . "/files" . $_REQUEST['source'] : "";

switch($action) {
    case "getGroupShareList" :
        if ($source) {
            $groupShareList = OC_GroupShare_Handler::getGroupShareList($userId, $source);
            echo json_encode($groupShareList);
            //var_dump($groupShareList);
        }
        break;
    case "updateGroupShare" :
        $gids = $_REQUEST['gids'];
        $uids = $_REQUEST['uids'];
        if ($source) {
            # 由 IOS 呼叫時，可能會包含'dav/webdav.php/'，所以要移除
            $source = str_replace('dav/webdav.php/', '', $source);
            $source = rtrim($source,'/');
            echo json_encode(OC_GroupShare_Handler::updateGroupShare($userId, $source, $gids, $uids));
        }
        break;
    case "updatePermission" :
        $permission = $_REQUEST['permission'];
        if ($source && !is_null($permission)) {
            echo json_encode(OC_GroupShare_Manager::updatePermission($userId, $source, $permission));
        }
        break;
    case "getGroupShareManagerList" :
        echo json_encode(OC_GroupShare_Manager::getGroupShareManagerList($userId));
        //var_dump(OC_GroupShare_Manager::getGroupShareManagerList($userId));
        break;
    case "removeGroupShare" :
        if ($source) {
            echo json_encode(OC_GroupShare_Manager::removeGroupShare($userId, $source));
        }
        break;
    case "getGroupShareByUidSharedWith" :
        echo json_encode(OC_GroupShare::getGroupShareByUidSharedWith($userId));
        break;
    default :
        break;
}
