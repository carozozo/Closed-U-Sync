<?php
$appId = 'artdisk_render';
$classPath = 'apps/' . $appId . '/lib/';
$l = new OC_L10N($appId);

# 新增管理頁
OC_App::registerAdmin($appId, 'settings');

OC::$CLASSPATH['OC_ArtdiskRender'] = $classPath . 'artdisk_render.php';
OC::$CLASSPATH['OC_ArtdiskRender_Config'] = $classPath . 'config.php';
OC::$CLASSPATH['OC_ArtdiskRender_Handler'] = $classPath . 'handler.php';
OC::$CLASSPATH['OC_ArtdiskRender_Helper'] = $classPath . 'helper.php';
OC::$CLASSPATH['OC_ArtdiskRender_Hooks'] = $classPath . 'hooks.php';
OC::$CLASSPATH['OC_ArtdiskRender_Item'] = $classPath . 'item.php';
OC::$CLASSPATH['OC_ArtdiskRender_Property'] = $classPath . 'property.php';
OC::$CLASSPATH['OC_ArtdiskRender_Status'] = $classPath . 'status.php';

OC_Hook::connect("OC_Filesystem", OC_Filesystem::signal_copy, "OC_ArtdiskRender_Hooks", "copyItem");
OC_Hook::connect("OC_Filesystem", OC_Filesystem::signal_rename, "OC_ArtdiskRender_Hooks", "renameItem");
OC_Hook::connect("OC_Filesystem", OC_Filesystem::signal_delete, "OC_ArtdiskRender_Hooks", "deleteItem");

OC_Util::addScript($appId, $appId);
OC_Util::addStyle($appId, $appId);
