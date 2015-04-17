<?php
$l = new OC_L10N('files_groupshare');

OC_Hook::connect('OC_Filesystem', OC_Filesystem::signal_post_delete, 'OC_GroupShare_Handler_Hooks', 'deleteItem');
OC_Hook::connect('OC_Filesystem', OC_Filesystem::signal_post_create, 'OC_GroupShare_Handler_Hooks', 'createItem');
OC_Hook::connect('OC_Filesystem', OC_Filesystem::signal_post_write, 'OC_GroupShare_Handler_Hooks', 'updateItem');
OC_Hook::connect('OC_Filesystem', OC_Filesystem::signal_rename, 'OC_GroupShare_Handler_Hooks', 'renameItem');
OC_Hook::connect('OC_Filesystem', OC_Filesystem::signal_copy, 'OC_GroupShare_Handler_Hooks', 'copyItem');

//groupshare.js有使用到fileactions.js
//OC_Util::addScript('files', 'fileactions');
OC_Util::addScript('files_groupshare', 'groupshare');
/*
 OC_Util::addScript('3rdparty', 'chosen/chosen.jquery.min');
 OC_Util::addStyle( 'files_groupshare', 'sharing' );
 OC_Util::addStyle('3rdparty', 'chosen/chosen');*/

OC::$CLASSPATH['OC_Filestorage_GroupShared'] = 'apps/files_groupshare/groupsharedstorage.php';
OC::$CLASSPATH['OC_GroupShare'] = 'apps/files_groupshare/lib/groupshare.php';
OC::$CLASSPATH['OC_GroupShare_Manager'] = 'apps/files_groupshare/lib/groupshare_manager.php';
OC::$CLASSPATH['OC_GroupShare_Handler'] = 'apps/files_groupshare/lib/groupshare_handler.php';
OC::$CLASSPATH['OC_GroupShare_Handler_Hooks'] = 'apps/files_groupshare/lib/groupshare_handler_hooks.php';
OC::$CLASSPATH['OC_GroupShare_Notification'] = 'apps/files_groupshare/lib/groupshare_notification.php';

OC_App::addNavigationEntry(array(
    'id' => 'groupshare_inbox',
    'order' => 2,
    'href' => OC_Helper::linkTo('files', 'index.php?dir=/' . OC_GroupShare::groupSharedDir()),
    'icon' => OC_Helper::imagePath('files_groupshare', 'groupshare_inbox.png'),
    'name' => $l -> t('Shared Inbox')
));
//分享管理選單，放在分類為「settings」底下(分類設定放在/lib/template.php)
OC_App::addNavigationEntry(array(
    'id' => 'files_groupshare_manager',
    'order' => 3,
    'href' => OC_Helper::linkTo('files_groupshare', 'groupshare_manager.php'),
    'icon' => OC_Helper::imagePath('files_groupshare', 'groupshare_manager.png'),
    'name' => $l -> t('Group Share Manager')
), 'settings');
OC_Hook::connect('OC_User', 'post_deleteUser', 'OC_GroupShare_Handler_Hooks', 'updateGroupShareFilesByDelUser');
OC_Hook::connect('OC_Contact_Group', 'updateContactInGroup', 'OC_GroupShare_Handler_Hooks', 'updateGroupShareFiles');
OC_Hook::connect('OC_Contact_Group', 'delGroup', 'OC_GroupShare_Handler_Hooks', 'removeGidsInGroupShare');
OC_Hook::connect('OC_Contact', 'delContact', 'OC_GroupShare_Handler_Hooks', 'removeUidsInGroupShare');

//註冊data
OC_GroupShare::registerGroupShareStorage();
// regirter the group share storage when setup file system(for other device without web)
OC_Hook::connect('OC_Util', 'post_setupFS', 'OC_GroupShare', 'registerGroupShareStorage');
?>