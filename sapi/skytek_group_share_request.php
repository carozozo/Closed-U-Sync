<?php
require_once ('inc_server.php');
$RUNTIME_NOSETUPFS = true;
$RUNTIME_NOWEBFILES = true;
require_once ('../lib/base.php');
$params = OC_API::checkApiUser();
$userId = $params['userId'];
$action = $params['action'];

$source = (!empty($_REQUEST['source'])) ? "/" . $userId . "/files" . $_REQUEST['source'] : "";

switch ($action) {
    case "getGroupShareList" :
        if ($source) {
            $groupShareList = OC_GroupShare_Handler::getGroupShareList($userId, $source);
            OC_JSON::success(array('result' => $groupShareList));
        }
        break;
    case "updateGroupShare" :
        if ($source) {
            # 由 IOS 呼叫時，可能會包含'dav/webdav.php/'，所以要移除
            $source = str_replace('dav/webdav.php/', '', $source);
            $source = rtrim($source, '/');

            # 這邊傳送過來的是 系統群組的 dpid
            $dpids = $_REQUEST['dpids'];
            $uids = $_REQUEST['uids'];
            $gids = (isset($_REQUEST['gids'])) ? $_REQUEST['gids'] : '';

            # 將傳送過來的 dpid 字串列 轉為 gid 字串列
            $dpidArr = OC_Helper::strToArr($dpids, ';');
            foreach ($dpidArr as $dpid) {
                $gid = OC_SkytekSystemGrop::getSystemGroupIdByDpid($dpid);
                $gid = 's' . $gid;
                if (!$gids) {
                    $gids = $gid;
                    continue;
                }
                $gids .= ';' . $gid;
            }
            OC_Log::write('gids=', $gids, 1);
            $result = OC_GroupShare_Handler::updateGroupShare($userId, $source, $gids, $uids);
            OC_JSON::success(array('result' => $result));
        }
        break;
    case "updatePermission" :
        $permission = $_REQUEST['permission'];
        if ($source && !is_null($permission)) {
            $result = OC_GroupShare_Manager::updatePermission($userId, $source, $permission);
            OC_JSON::success(array('result' => $result));
        }
        break;
    case "getGroupShareManagerList" :
        $groupShareList = OC_GroupShare_Manager::getGroupShareManagerList($userId);
        OC_JSON::success(array('result' => $groupShareList));
        break;
    case "removeGroupShare" :
        if ($source) {
            $result = OC_GroupShare_Manager::removeGroupShare($userId, $source);
            OC_JSON::success(array('result' => $result));
        }
        break;
    case "getGroupShareByUidSharedWith" :
        $result = OC_GroupShare::getGroupShareByUidSharedWith($userId);
        OC_JSON::success(array('result' => $result));
        break;
    default :
        break;
}
