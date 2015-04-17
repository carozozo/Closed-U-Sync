<?php
require_once ('../../lib/base.php');
OC_Util::checkLoggedIn();
OC_Util::checkAppEnabled('files_publicshare');
$appId = OC_PublicShare_Config::appId;

OC_Util::addScript($appId, 'ui');
OC_Util::addStyle($appId, 'ui');

$sourcePath = $_REQUEST['sourcePath'];
$property = OC_PublicShare::insert($sourcePath);
$nickName = OC_User::getUserNickname();

$tmpl = new OC_Template($appId, 'ui', 'blank');
$tmpl -> assign('property', $property);
$tmpl -> assign('nickName', $nickName);
$tmpl -> printPage();
?>