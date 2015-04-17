<?php
$RUNTIME_NOSETUPFS = false;
require_once ('../lib/base.php');
$appId = OC_PublicShare_Config::appId;
OC_Util::checkAdminUser();
OC_Util::checkAppEnabled('files_publicshare');

OC_Util::addScript($appId, "settings");
OC_Util::addStyle($appId, "settings");

$configs = OC_PublicShare_Config::getConfigItems();
$tmpl = new OC_Template($appId, 'settings');
$tmpl -> assign("configs", $configs);
return $tmpl -> fetchPage();
?>