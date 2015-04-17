<?php
$RUNTIME_NOSETUPFS = false;
require_once ('../lib/base.php');
OC_Util::checkAdminUser();
OC_Util::checkAppEnabled('media_streaming');
$appId = OC_MediaStreaming::appId;

OC_Util::addScript($appId, "media_streaming_convert_server_settings");
OC_Util::addStyle($appId, "media_streaming_convert_server_settings");

$tmpl = new OC_Template($appId, 'media_streaming_convert_server_settings');
return $tmpl -> fetchPage();
?>