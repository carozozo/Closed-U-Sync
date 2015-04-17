<?php
require_once ('../../lib/base.php');

OC_JSON::checkLoggedIn();
OC_JSON::checkAppEnabled('files_thumbnail');

$dir = $_GET["dir"];
$filename = $_GET["file"];
$userId = (!empty($_GET["userId"])) ? $_GET["userId"] : OC_User::getUser();

if (!empty($filename)) {
	header("Content-Type: image/png");
	# create new imagick object
	$source = OC::$CONFIG_DATADIRECTORY_ROOT . '/' . $userId . '/files/' . $dir . '/.thumbs/' . $filename . '.jpg';
	$source = OC_Helper::pathForbiddenChar($source);
	$im = new Imagick($source);
	# change format to png
	$im -> setImageFormat("png");
	# output the image to the browser as a png
	echo $im;
	$im -> destroy();
}
?>