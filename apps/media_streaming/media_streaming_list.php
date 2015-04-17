<?php
require_once ('../../lib/base.php');
OC_Util::checkAdminUser();
OC_Util::checkAppEnabled('media_streaming');
$appId = OC_MediaStreaming::appId;

// OC_Util::addScript($appId, "media_streaming_clist");
OC_Util::addStyle($appId, "media_streaming_list");

# 取得串流轉檔中的資料
$streamingList = OC_MediaStreaming::getStreamingListByStatus(OC_MediaStreaming::converting);

$tmpl = new OC_Template($appId, 'media_streaming_list', 'blank');
$tmpl -> assign("streamingList", $streamingList);
$tmpl -> printPage();
?>