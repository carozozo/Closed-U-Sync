<?php
$RUNTIME_NOSETUPFS = true;
require_once ('../lib/base.php');
$appId = OC_U_Channel_Settings::appId;
OC_Util::checkAdminUser();
OC_Util::checkAppEnabled($appId);

OC_Util::addScript($appId, $appId."_settings");
OC_Util::addStyle($appId, $appId."_settings");

$configs = OC_U_Channel_Settings::getConfigItems();
$tmpl = new OC_Template($appId, $appId.'_settings');
$tmpl -> assign("configs", $configs);
return $tmpl -> fetchPage();
?>