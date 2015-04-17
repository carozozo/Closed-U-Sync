<?php
require_once ('../../lib/base.php');
$appId = 'audio_streaming';
OC_Util::checkLoggedIn();
OC_Util::checkAppEnabled($appId);

OC_Util::addScript($appId, "audio_player");
OC_Util::addStyle($appId, "audio_player");

$tmpl = new OC_Template($appId, 'index', 'blank');
$tmpl -> assign("source", $_REQUEST['source']);
if (isset($_REQUEST['mode'])) {
	$tmpl -> assign("mode", $_REQUEST['mode']);
} else {
	$tmpl -> assign("mode", '');
}
$tmpl -> printPage();
?>