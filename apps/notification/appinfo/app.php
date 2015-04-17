<?php
$appId = 'notification';
OC_APP::register(array('order' => 90, 'id' => $appId, 'name' => 'Notification'));

OC::$CLASSPATH['OC_Notification'] = 'apps/' . $appId . '/lib/notification.php';
OC::$CLASSPATH['OC_Notification_DB'] = 'apps/' . $appId . '/lib/notification_db.php';

OC_Util::addScript($appId, $appId);
OC_Util::addStyle($appId, $appId);

//註冊系統發佈者名稱
// $l = new OC_L10N('notification');
// OC_Notification::registPublisher('System', $l -> t('System'));
?>