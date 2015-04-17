<?php
$appId = 'u_channel';
$l = new OC_L10N($appId);

# 新增管理頁
OC_App::registerAdmin($appId, 'u_channel_settings');

OC::$CLASSPATH['OC_U_Channel_Settings'] = 'apps/' . $appId . '/lib/u_channel_settings.php';
OC::$CLASSPATH['OC_U_Channel'] = 'apps/' . $appId . '/lib/u_channel.php';

//分享管理選單，放在分類為「activities」底下(分類設定放在/lib/template.php)
OC_App::addNavigationEntry(array(
    'id' => 'u_channel_index',
    'order' => 10,
    'href' => OC_Helper::linkTo($appId, 'index.php'),
    'icon' => OC_Helper::imagePath($appId, 'u_channel.png'),
    'name' => $l -> t('U-Channel')
), 'activities');
?>