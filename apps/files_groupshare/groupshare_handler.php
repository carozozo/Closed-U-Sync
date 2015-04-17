<?php
require_once ('../../lib/base.php');

OC_Util::checkLoggedIn();
OC_Util::checkAppEnabled('contact');
OC_Util::checkAppEnabled('files_groupshare');

$systemGroupEnabled = OC_Contact_System_Group::systemGroupEnabled();
$source = (isset($_REQUEST['source'])) ? $_REQUEST['source'] : '';
OC_Util::addScript("files_groupshare", "groupshare_manager");

OC_Util::addScript("files_groupshare", "groupshare_handler/contact_group");
if ($systemGroupEnabled) {
	OC_Util::addScript("files_groupshare", "groupshare_handler/contact_system_group");
}
OC_Util::addScript("files_groupshare", "groupshare_handler/contact_in_group");
OC_Util::addScript("files_groupshare", "groupshare_handler/contact");
OC_Util::addScript("files_groupshare", "groupshare_handler/contact_for_groupshare");
OC_Util::addScript("files_groupshare", "groupshare_handler/share_btn");
OC_Util::addScript("files_groupshare", "groupshare_handler/permission");
OC_Util::addScript("files_groupshare", "groupshare_handler/shared");
OC_Util::addStyle('files_groupshare', "groupshare_handler");

$tmpl = new OC_Template('files_groupshare', 'groupshare_handler', 'blank');
$tmpl -> assign("source", $source);
$tmpl -> assign("systemGroupEnabled", $systemGroupEnabled);
$tmpl -> printPage();
?>