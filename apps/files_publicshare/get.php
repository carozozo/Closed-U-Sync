<?php
require_once ('../../lib/base.php');
OC_JSON::checkAppEnabled('files_publicshare');
$appId = OC_PublicShare_Config::appId;

OC_Util::addScript($appId, 'get');
OC_Util::addStyle($appId, 'get');

$token = $_REQUEST['token'];
$tmpl = new OC_Template($appId, 'get', 'blank');
$tmpl -> assign("token", $token);
$tmpl -> printPage();
?>