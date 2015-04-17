<?php
require_once('../../lib/base.php');
//require_once('lib_share.php');

OC_Util::checkLoggedIn();
OC_Util::checkAppEnabled('contact');
OC_Util::checkAppEnabled('files_groupshare');

# 左邊選單顯示為「作用中」
OC_App::setActiveNavigationEntry("files_groupshare_manager");

OC_Util::addScript("files_groupshare", "groupshare_manager");
OC_Util::addStyle( 'files_groupshare', "groupshare_manager" );

$tmpl = new OC_Template("files_groupshare", "groupshare_manager", "user");
//$tmpl->assign("groupShareList", OC_GroupShare_Manager::getGroupShareList());
$tmpl->printPage();

?>