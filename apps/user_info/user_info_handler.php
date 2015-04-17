<?php
require_once ('../../lib/base.php');
OC_Util::checkLoggedIn();
OC_Util::checkAppEnabled('user_info');
$appId = OC_UserInfo::appId;

OC_Util::addScript($appId, "user_info_handler");
OC_Util::addStyle($appId, "user_info_handler");

$email = OC_User::getUserEmail();
$nickname = OC_User::getUserNickname();

# 是否已驗證的文字訊息
$vaildateMess = OC_UserInfo::emailIsVaildated();
# 如果是「已驗證」
$vaildateMessColor = 'red';
if (strtolower($vaildateMess) == 'vaildated') {
	$vaildateMessColor = 'green';
}


# 將回傳的訊息做語系轉換
$l = new OC_L10N(OC_UserInfo::appId);
$vaildateMess = $l -> t($vaildateMess);
# 如果是p2 server(U-Sync的話，則秀出提示訊息
$defaultMes = (OC_Helper::serverType()=='p2') ? $l ->t('You can get free 500MB quota after email vaildated') : '';


$tmpl = new OC_Template($appId, 'user_info_handler', 'blank');
$tmpl -> assign("email", $email);
$tmpl -> assign("nickname", $nickname);
$tmpl -> assign("vaildateMess", $vaildateMess);
$tmpl -> assign("vaildateMessColor", $vaildateMessColor);
$tmpl -> assign("defaultMes", $defaultMes);
$tmpl -> printPage();
?>
