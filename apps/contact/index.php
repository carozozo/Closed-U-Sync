<?php
require_once ('../../lib/base.php');

OC_Util::checkLoggedIn();
OC_Util::checkAppEnabled('contact');

# 左邊選單顯示為「作用中」
OC_App::setActiveNavigationEntry("contact_index");

$l = new OC_L10N('contact');
$newContactMessage = $l -> t('Add contact');
// $systemGroupEnabled = OC_Config::getValue('systemGroup', TRUE, 'CONTACT');
$systemGroupEnabled = OC_Contact_System_Group::systemGroupEnabled();
$siteTitle = OC_Helper::siteTitle();

if (!empty($siteTitle)) {
	$newContactMessage = $l -> t('Add') . $siteTitle . $l -> t('user contact');
}

OC_Util::addScript('contact', 'contact');
OC_Util::addScript('contact', 'contact_group');
if ($systemGroupEnabled) {
	OC_Util::addScript('contact', 'contact_system_group');
}
OC_Util::addScript('contact', 'contact_in_group');
OC_Util::addStyle('contact', 'styles');

$tmpl = new OC_Template('contact', 'index', 'user');
$tmpl -> assign('newContactMessage', $newContactMessage);
$tmpl -> assign('systemGroupEnabled', $systemGroupEnabled);
$tmpl -> printPage();
?>