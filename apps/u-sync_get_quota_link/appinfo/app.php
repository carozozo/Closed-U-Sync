<?php
$app_id = 'u-sync_get_quota_link';
$l = new OC_L10N($app_id);

# 連結選單，放在分類為「activities」底下(分類設定放在/lib/template.php)
OC_App::addNavigationEntry(array(
    'id' => $app_id,
    'order' => 2,
    'href' => 'https://u-sync.com/home/?option=com_usync&view=quota',
    'target' => '_blank',
    'icon' => OC_Helper::imagePath('u-sync_get_quota_link', 'u-sync_get_quota_link.png'),
    'name' => $l -> t('Get More Quota'),
), 'activities');
?>