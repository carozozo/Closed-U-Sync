<?php
$appId = 'user_info';
OC::$CLASSPATH['OC_UserInfo'] = 'apps/' . $appId . '/lib/user_info.php';
OC_Util::addScript($appId, $appId);
?>