<?php
require_once ('../../../lib/base.php');

OC_JSON::checkLoggedIn();
OC_JSON::checkAppEnabled('files_publicshare');

$action = $_REQUEST['action'];
switch($action) {
    case 'getBySource' :
        $source = $_REQUEST['source'];
        $property = OC_PublicShare::getBySource($source);
        OC_JSON::success(array('property' => $property));
        break;
    case 'insert' :
        $source = $_REQUEST['source'];
        $property = OC_PublicShare::insert($source);
        OC_JSON::success(array('property' => $property));
        break;
    case "getListByUser" :
        $sortBy = ($_REQUEST['sortBy']) ? $_REQUEST['sortBy'] : 'time';
        $sort = ($_REQUEST['sort']) ? $_REQUEST['sort'] : 'ASC';
        $items = OC_PublicShare::getListByUser($sortBy, $sort);
        OC_JSON::success(array('items' => $items, ));
        break;
    case 'getByToken' :
        $token = $_REQUEST['token'];
        $property = OC_PublicShare::getByToken($token);
        OC_JSON::success(array('property' => $property));
        break;
    case 'updateByToken' :
        $deadline = $_REQUEST['deadline'];
        $pwd = $_REQUEST['pwd'];
        $token = $_REQUEST['token'];
        $property = OC_PublicShare::updateByToken($token, null, $pwd, $deadline);
        OC_JSON::success(array('property' => $property));
        break;
    case 'deleteByToken' :
        $token = $_REQUEST['token'];
        $items = OC_PublicShare::deleteByToken($token);
        OC_JSON::success(array('items' => $items, ));
        break;
    default :
        break;
}
