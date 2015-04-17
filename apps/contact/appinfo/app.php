<?php
//$l=new OC_L10N('baord');
OC::$CLASSPATH['OC_Contact'] = 'apps/contact/lib/contact.php';
OC::$CLASSPATH['OC_Contact_Group'] = 'apps/contact/lib/contact_group.php';
OC::$CLASSPATH['OC_Contact_System_Group'] = 'apps/contact/lib/contact_system_group.php';
OC::$CLASSPATH['OC_Contact_Hooks'] = 'apps/contact/lib/contact_hooks.php';

//OC::$CLASSPATH['OC_Thumbnail_Sync'] = 'files/lib/thumbnail_sync.php';
//OC_App::register( array( 'order' => 11, 'id' => 'board', 'name' => 'Board' ));

$l = new OC_L10N('contact');
$appName = $l -> t('Contact');
$siteTitle = OC_Helper::siteTitle();
if (!empty($siteTitle)) {
    $appName = $siteTitle . ' ' . $appName;
}
//左邊選單放在分類為「settings」底下(分類設定放在/lib/template.php)
OC_App::addNavigationEntry(array(
    'id' => 'contact_index',
    'order' => 5,
    'href' => OC_Helper::linkTo('contact', 'index.php'),
    'icon' => OC_Helper::imagePath('contact', 'contact.png'),
    'name' => $appName
), 'settings');

//hook 刪除某帳號時，也要把該聯絡人從contact及group中移除
OC_Hook::connect('OC_User', 'post_deleteUser', 'OC_Contact_Hooks', 'delContact');
OC_Hook::connect('OC_User', 'post_deleteUser', 'OC_Contact_Hooks', 'removeContactInGroup');
OC_Hook::connect('OC_User', 'post_deleteUser', 'OC_Contact_Hooks', 'removeContactInSystemGroup');
?>