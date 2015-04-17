<?php
require_once ('../../../lib/base.php');

OC_JSON::checkLoggedIn();
OC_JSON::checkAppEnabled('files_recycle');

$action = $_REQUEST['action'];

switch ($action) {
    case 'getRecList' :
        $sn = $_REQUEST['sn'];
        $assignPath = $_REQUEST['assignPath'];
        $sortBy = ($_REQUEST['sortBy']) ? $_REQUEST['sortBy'] : 'time';
        $sort = ($_REQUEST['sort']) ? $_REQUEST['sort'] : 'ASC';
        $items = OC_Recycle::getRecList($sn, $assignPath, $sortBy, $sort);
        OC_JSON::success(array('items' => $items));
        break;
    case 'revRec' :
        $sn = $_REQUEST['sn'];
        $assignPath = $_REQUEST['assignPath'];
        $items = OC_Recycle::revert($sn, $assignPath);
        OC_JSON::success(array('items' => $items));
        break;
    case 'delRec' :
        $sn = (isset($_REQUEST['sn'])) ? $_REQUEST['sn'] : null;
        $assignPath = (isset($_REQUEST['assignPath'])) ? $_REQUEST['assignPath'] : null;
        $items = OC_Recycle::delete($sn, $assignPath);
        OC_JSON::success(array('items' => $items));
        break;
    default :
        OC_JSON::error();
        break;
}
