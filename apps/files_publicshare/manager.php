<?php
require_once ('../../lib/base.php');

OC_Util::checkLoggedIn();
OC_Util::checkAppEnabled('contact');
OC_Util::checkAppEnabled('files_publicshare');
$appId = OC_PublicShare_Config::appId;

# 左邊選單顯示為「作用中」
OC_App::setActiveNavigationEntry("publicshare_manager_index");

OC_Util::addScript($appId, "manager");
OC_Util::addStyle($appId, "manager");

$tmpl = new OC_Template($appId, "manager", "user");
//$tmpl->assign("groupShareList", OC_GroupShare_Manager::getGroupShareList());
$tmpl -> printPage();
?>