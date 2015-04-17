<?php
# 每天3點執行一次移除所有streaming的link [00 03 * * * /usr/bin/php /var/www/html/crontab/audio_streaming_remove_link_crontab.php]
# crontab執行時，無法抓取$_SERVER['DOCUMENT_ROOT']，所以利用__FILE__取得路徑
$crontabPath = dirname(__FILE__);
$rootPath = preg_replace('#' . preg_quote('/crontab') . '#', '', $crontabPath);

# 不要建立FS
$RUNTIME_NOSETUPFS = true;
require_once ($rootPath . '/lib/base.php');
# audio streaming app 未開啟的話則跳出
if (!OC_App::isEnabled('audio_streaming')) {
	exit ;
}
OC_AudioStreaming::removeStreamingLink();
?>