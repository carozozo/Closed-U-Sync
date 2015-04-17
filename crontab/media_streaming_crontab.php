<?php
# 每5分鐘執行一次格式精靈轉檔確認 [*/5 * * * * /usr/bin/php /var/www/html/crontab/media_streaming_crontab.php]
# crontab執行時，無法抓取$_SERVER['DOCUMENT_ROOT']，所以利用__FILE__取得路徑
$crontabPath = dirname(__FILE__);
$rootPath = preg_replace('#'.preg_quote('/crontab').'#', '', $crontabPath);

require_once ($rootPath.'/lib/base.php');
# media streaming app 未開啟的話則跳出
if (!OC_App::isEnabled('media_streaming')) {
	exit ;
}
# 更新串流狀態
OC_MediaStreaming::updateStreamingStatusJob();
OC_MediaConvert::checkConvertJob1_6();
# 執行新版格式精靈排程中的轉檔流程，需先確認轉檔中的資料，再執行要求轉檔
OC_MediaConvert::checkConvertJob();
OC_MediaConvert::askConvertJob();
?>