<?php
$RUNTIME_NOSETUPFS = false;
require_once ('../lib/base.php');
OC_Util::checkAdminUser();
OC_Util::checkAppEnabled('artdisk_render');
$appId = OC_ArtdiskRender_Config::appId;

OC_Util::addScript($appId, "settings");
// OC_Util::addStyle($appId, "artdisk_render_settings");
$configs = OC_ArtdiskRender_Config::getConfigItems();
$tmpl = new OC_Template($appId, 'settings');
$tmpl -> assign("configs", $configs);
return $tmpl -> fetchPage();
?>