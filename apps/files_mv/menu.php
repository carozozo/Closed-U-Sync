<?php
// Init owncloud
require_once ('../../lib/base.php');
OC_Util::checkLoggedIn();
OC_Util::checkAppEnabled('files_mv');

OC_Util::addScript("files_mv", "menu");
OC_Util::addStyle("files_mv", "menu");

$dir = $_REQUEST['dir'];
$files = $_REQUEST['files'];

$tmpl = new OC_Template("files_mv", "menu", "blank");
$tmpl -> assign("dir", $dir);
$tmpl -> assign("files", $files);
$tmpl -> printPage();
?>
