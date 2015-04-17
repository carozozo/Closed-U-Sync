<?php
require_once ('../../lib/base.php');
OC_Util::checkAdminUser();
OC_Util::checkAppEnabled('media_streaming');
$appId = OC_MediaStreaming::appId;

// OC_Util::addScript($appId, "media_streaming_convert_list");
OC_Util::addStyle($appId, "media_streaming_convert_list");

$convertList = OC_MediaConvert::getConvertList();

$tmpl = new OC_Template($appId, 'media_streaming_convert_list', 'blank');
$tmpl -> assign("convertList", $convertList);
$tmpl -> printPage();
?>