<?php
$RUNTIME_NOSETUPFS = true;
require_once ('../../lib/base.php');

OC_Util::checkLoggedIn();
OC_Util::checkAppEnabled('u_channel');

$appId = OC_U_Channel_Settings::appId;

# 左邊選單顯示為「作用中」
OC_App::setActiveNavigationEntry("u_channel_index");

OC_Util::addscript($appId);
OC_Util::addstyle($appId);

$channelList = OC_U_Channel::getChannelList();

$tmpl = new OC_Template($appId, 'index', 'user');
$tmpl -> assign("channelList", $channelList);
$tmpl -> printPage();
?>
