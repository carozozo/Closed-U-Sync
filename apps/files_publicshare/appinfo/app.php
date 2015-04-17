<?php
$appId = 'files_publicshare';
$classPath = 'apps/' . $appId . '/lib/';

$l = new OC_L10N($appId);
OC_Util::addScript($appId, 'publicshare');
OC_Util::addStyle($appId, 'publicshare');

# 新增管理頁
OC_App::registerAdmin($appId, 'settings');

OC::$CLASSPATH['OC_PublicShare_DB'] = $classPath . 'db/db.php';
OC::$CLASSPATH['OC_PublicShare_DB_Download'] = $classPath . 'db/download.php';
OC::$CLASSPATH['OC_PublicShare_Config'] = $classPath . 'config.php';
OC::$CLASSPATH['OC_PublicShare_Download'] = $classPath . 'download.php';
OC::$CLASSPATH['OC_PublicShare_Helper'] = $classPath . 'helper.php';
OC::$CLASSPATH['OC_PublicShare_Hooks'] = $classPath . 'hooks.php';
OC::$CLASSPATH['OC_PublicShare_Item'] = $classPath . 'item.php';
OC::$CLASSPATH['OC_PublicShare_Property'] = $classPath . 'property.php';
OC::$CLASSPATH['OC_PublicShare'] = $classPath . 'publicshare.php';

# 分享管理選單，放在分類為「settings」底下(分類設定放在/lib/template.php)
OC_App::addNavigationEntry(array(
    'id' => 'publicshare_manager_index',
    'order' => 4,
    'href' => OC_Helper::linkTo($appId, 'manager.php'),
    'icon' => OC_Helper::imagePath($appId, 'manager.png'),
    'name' => $l -> t('Public Share Manager')
),'settings');

OC_Hook::connect('OC_Filesystem', OC_Filesystem::signal_post_delete, 'OC_PublicShare_Hooks', 'deleteItem');
OC_Hook::connect('OC_Filesystem', OC_Filesystem::signal_rename, 'OC_PublicShare_Hooks', 'renameItem');
?>