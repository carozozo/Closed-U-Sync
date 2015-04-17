<?php
require_once ('../../lib/base.php');
OC_Util::checkLoggedIn();
OC_Util::checkAppEnabled('media_streaming');
$appId = OC_MediaStreaming::appId;

OC_Util::addScript($appId, "streaming_player");
OC_Util::addStyle($appId, "streaming_player");

$tmpl = new OC_Template($appId, 'index', 'blank');
$tmpl -> assign("title", $_REQUEST['title']);
$tmpl -> assign("source", $_REQUEST['source']);
if (isset($_REQUEST['mode'])) {
	$tmpl -> assign("mode", $_REQUEST['mode']);
} else {
	$tmpl -> assign("mode", '');
}
$tmpl -> printPage();
?>