<?php
$appId = 'media_streaming';
$l = new OC_L10N($appId);

# 新增管理頁
OC_App::registerAdmin($appId, 'media_streaming_settings');
OC_App::registerAdmin($appId, 'media_streaming_convert_server_settings');

OC::$CLASSPATH['OC_MediaStreaming_Settings'] = 'apps/' . $appId . '/lib/media_streaming_settings.php';
OC::$CLASSPATH['OC_MediaStreaming'] = 'apps/' . $appId . '/lib/media_streaming.php';
OC::$CLASSPATH['OC_MediaStreaming_DB'] = 'apps/' . $appId . '/lib/media_streaming_db.php';
OC::$CLASSPATH['OC_MediaStreaming_Hooks'] = 'apps/' . $appId . '/lib/media_streaming_hooks.php';

OC_Hook::connect("OC_Filesystem", OC_Filesystem::signal_copy, "OC_MediaStreaming_Hooks", "copyItem");
OC_Hook::connect("OC_Filesystem", OC_Filesystem::signal_rename, "OC_MediaStreaming_Hooks", "renameItem");

OC_Hook::connect("OC_Filesystem", OC_Filesystem::signal_delete, "OC_MediaStreaming_Hooks", "deleteItem");
OC_Hook::connect("OC_Filesystem", OC_Filesystem::signal_fromUploadedFile, "OC_MediaStreaming_Hooks", "deleteItemAfterUploadedFile");

OC_Util::addScript($appId, $appId);

# 如果設定檔中有「開啟格式精靈」而且「有開啟HLS」
$convertEnable = OC_MediaStreaming::convertEnable();
if ($convertEnable) {
    OC::$CLASSPATH['OC_MediaConvert'] = 'apps/' . $appId . '/lib/media_convert.php';
    OC::$CLASSPATH['OC_MediaConvert_DB'] = 'apps/' . $appId . '/lib/media_convert_db.php';
    OC::$CLASSPATH['OC_MediaConvertServer'] = 'apps/' . $appId . '/lib/media_convert_server.php';
    OC::$CLASSPATH['OC_MediaConvertServer_DB'] = 'apps/' . $appId . '/lib/media_convert_server_db.php';
    OC::$CLASSPATH['OC_MediaConvert_Hooks'] = 'apps/' . $appId . '/lib/media_convert_hooks.php';

    OC_Hook::connect("OC_Filesystem", OC_Filesystem::signal_rename, "OC_MediaConvert_Hooks", "renameItem");
    OC_Hook::connect("OC_Filesystem", OC_Filesystem::signal_delete, "OC_MediaConvert_Hooks", "deleteItem");

    OC_Util::addScript($appId, "media_convert");
    OC_Util::addStyle($appId, "media_convert");
    OC_App::addNavigationEntry(array(
        "id" => "streaming_convert_output",
        "order" => 5,
        "href" => OC_Helper::linkTo("files", "index.php?dir=/" . OC_MediaConvert::convertDirPath()),
        "icon" => OC_Helper::imagePath($appId, "media_convert.png"),
        "name" => $l -> t('Convert Wizard')
    ));
    # 產生格式精靈的資料夾
    OC_MediaConvert::createMediaConvertFolder();
    # 透過其它device時，產生格式精靈的資料夾
    OC_Hook::connect("OC_Util", "post_setupFS", "OC_MediaConvert", "createMediaConvertFolder");
}
