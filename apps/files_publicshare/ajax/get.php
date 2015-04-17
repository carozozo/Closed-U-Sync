<?php
require_once ('../../../lib/base.php');

# 這是for get.php 用的ajax，不需要確認是否登入
// OC_JSON::checkLoggedIn();
OC_JSON::checkAppEnabled('files_publicshare');

$action = $_REQUEST['action'];
switch($action) {
    case 'checkByToken' :
        $token = $_REQUEST['token'];
        $resultArr = OC_PublicShare::checkByToken($token);
        OC_JSON::success(array('resultArr' => $resultArr));
        break;
    case 'getByPwdToken' :
        $pwd = $_REQUEST['pwd'];
        $token = $_REQUEST['token'];
        $property = OC_PublicShare::getByPwdToken($pwd, $token);
        OC_JSON::success(array('property' => $property));
        break;
    case 'getFileList' :
        $pwd = $_REQUEST['pwd'];
        $token = $_REQUEST['token'];
        $dirPath = $_REQUEST['dirPath'];
        $contents = OC_PublicShare::getFileList($pwd, $token, $dirPath);
        if ($contents) {
            OC_JSON::success(array('contents' => $contents));
        }
        break;
    default :
        OC_JSON::error(array('errorMessage' => 'No Action'));
        break;
}
