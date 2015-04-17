<?php
require_once ('../../lib/base.php');
OC_Util::checkLoggedIn();
OC_Util::checkAppEnabled('files_recycle');
$appId = OC_Recycle_Config::appId;

# 左邊選單顯示為「作用中」
OC_App::setActiveNavigationEntry('recycle_manager');

OC_Util::addScript($appId, 'manager');
OC_Util::addStyle($appId, 'manager');

$tmpl = new OC_Template($appId, 'manager', 'user');
$tmpl -> printPage();
?>