<?php
// Init owncloud
require_once ('../../lib/base.php');
OC_Util::checkLoggedIn();
OC_Util::checkAppEnabled('files_copy');

OC_Util::addScript("files_copy", "menu");
OC_Util::addStyle("files_copy", "menu");

$dir = $_REQUEST['dir'];
$files = $_REQUEST['files'];

$tmpl = new OC_Template("files_copy", "menu", "blank");
$tmpl -> assign("dir", $dir);
$tmpl -> assign("files", $files);
$tmpl -> printPage();
?>
