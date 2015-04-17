<?php
/**
 * 此api已整合到sapi/contact_request.php中，待其它device ready後將刪除
 */
require_once ('inc_server.php');
$RUNTIME_NOSETUPFS = true;
$RUNTIME_NOWEBFILES = true;
require_once ('../lib/base.php');
$action = $_REQUEST['action'];

switch ($action) {
    case'getSystemGroupList' :
        $result = OC_Contact_System_Group::getSystemGroupList();
        OC_JSON::success(array('result' => $result));
        break;
    case'getContactListBySystemGroupId' :
        // 取得系統群組底下的聯絡人清單
        $dpId = $_REQUEST['dpid'];
        $result = OC_Contact_System_Group::getContactListBySystemGroupId($dpId);
        OC_JSON::success(array('result' => $result));
        break;
    case 'addSystemGroup' :
        $dpId = $_REQUEST['dpid'];
        $dpName = $_REQUEST['dpname'];
        $result = OC_SkytekSystemGrop::addSystemGroup($dpId, $dpName);
        OC_JSON::success(array('result' => $result));
        break;
    case 'updateSystemGroupName' :
        $dpId = $_REQUEST['dpid'];
        $dpName = $_REQUEST['dpname'];
        $result = OC_SkytekSystemGrop::updateSystemGroupName($dpId, $dpName);
        OC_JSON::success(array('result' => $result));
        break;
    case 'deleteSystemGroup' :
        $dpId = $_REQUEST['dpid'];
        $result = OC_SkytekSystemGrop::deleteSystemGroup($dpId);
        OC_JSON::success(array('result' => $result));
        break;
    default :
        OC_JSON::error();
        break;
}
