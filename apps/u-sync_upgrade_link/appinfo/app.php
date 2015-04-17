<?php
$app_id = 'u-sync_upgrade_link';
$l = new OC_L10N($app_id);

//分享管理選單，放在分類為「activities」底下(分類設定放在/lib/template.php)
OC_App::addNavigationEntry(array(
    'id' => $app_id,
    'order' => 1,
    'href' => 'https://u-sync.com/home/%E7%AB%8B%E5%8D%B3%E8%B2%B7.html',
    'icon' => OC_Helper::imagePath('u-sync_upgrade_link', 'u-sync_upgrade_link.png'),
    'name' => $l -> t('Upgrade U-Sync')
), 'activities');
?>