<?php
$appId = 'files_recycle';
$classPath = 'apps/' . $appId . '/lib/';

$l = new OC_L10N($appId);
OC::$CLASSPATH['OC_Recycle_DB'] = $classPath . 'db/db.php';
OC::$CLASSPATH['OC_Recycle_Config'] = $classPath . 'config.php';
OC::$CLASSPATH['OC_Recycle_Handler'] = $classPath . 'handler.php';
OC::$CLASSPATH['OC_Recycle_Helper'] = $classPath . 'helper.php';
OC::$CLASSPATH['OC_Recycle_Hooks'] = $classPath . 'hooks.php';
OC::$CLASSPATH['OC_Recycle_Item'] = $classPath . 'item.php';
OC::$CLASSPATH['OC_Recycle_Property'] = $classPath . 'property.php';
OC::$CLASSPATH['OC_Recycle'] = $classPath . 'recycle.php';
OC::$CLASSPATH['OC_Recycle_Status'] = $classPath . 'status.php';

# 左邊選單放在分類為「settings」底下(分類設定放在/lib/template.php)
OC_App::addNavigationEntry(array(
    'id' => 'recycle_manager',
    'order' => 80,
    'href' => OC_Helper::linkTo($appId, 'manager.php'),
    'icon' => OC_Helper::imagePath($appId, 'recycled.png'),
    'name' => $l -> t('Recycle')
), 'mySpace');

# 指定檔案要被刪除前， moveToRecycle 是最後一個被執行的hooks(設定為999)
OC_Hook::connect('OC_Filesystem', OC_Filesystem::signal_delete, 'OC_Recycle_Hooks', 'recyle', 999);
OC_Hook::connect('OC_Filesystem', OC_Filesystem::signal_fromUploadedFile, 'OC_Recycle_Hooks', 'recBeforeUploaded', 999);
# 在 files system 建立後，產生回收桶資料夾(for 其它不是使用網頁 的 device)
OC_Hook::connect('OC_Util', 'post_setupFS', 'OC_Recycle', 'createRecycleFolder');
OC_Recycle::createRecycleFolder();
?>