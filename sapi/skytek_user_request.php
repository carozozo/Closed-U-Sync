<?php
require_once ('inc_server.php');
$RUNTIME_NOSETUPFS = true;
$RUNTIME_NOWEBFILES = true;
require_once ('../lib/base.php');
$action = $_REQUEST['action'];

switch ($action) {
    case 'addUser' :
        $userId = $_REQUEST['uid'];
        $nickname = (isset($_REQUEST['uname'])) ? $_REQUEST['uname'] : $userId;
        $dpid = $_REQUEST['dpid'];
        $quota = (isset($_REQUEST['quota'])) ? $_REQUEST['quota'] : 0;
        $result = OC_SkytekUser::addUser($userId, $nickname, $dpid, $quota);
        OC_JSON::success(array('result' => $result));
        break;
    case 'updateUser' :
        $userId = $_REQUEST['uid'];
        $nickname = (isset($_REQUEST['uname'])) ? $_REQUEST['uname'] : '';
        $dpid = $_REQUEST['dpid'];
        $quota = (isset($_REQUEST['quota'])) ? $_REQUEST['quota'] : 0;
        # 如果除了參數 isAdd=false 的狀況外，其餘預設為 true
        $isAdd = (isset($_REQUEST['isAdd']) and $_REQUEST['isAdd'] == 'false') ? false : true;
        $result = OC_SkytekUser::updateUser($userId, $nickname, $dpid, $quota, $isAdd);
        OC_JSON::success(array('result' => $result));
        break;
    case 'deleteUser' :
        $userId = $_REQUEST['uid'];
        $result = OC_SkytekUser::deleteUser($userId);
        OC_JSON::success(array('result' => $result));
        break;
    default :
        OC_JSON::error();
        break;
}
