<?php
require_once ('../../../lib/base.php');

OC_JSON::checkLoggedIn();
OC_JSON::checkAppEnabled('files_thumbnail');

$action = $_REQUEST['action'];
switch ($action) {
	case 'getThumbsInDir' :
		$dir = $_REQUEST['dir'];
		$thumbnailObj = new OC_Thumbnail($dir);
		$thumbUrlArray = $thumbnailObj -> getThumbsInDir();
		OC_JSON::success(array('thumbUrlArray' => $thumbUrlArray));
		break;
	case 'createAndGetThumbByFile' :
		$dir = $_REQUEST['dir'];
		$file = $_REQUEST['file'];
		$file['name'] = $file[0];
		$file['mime'] = $file[1];
		$file['size'] = $file[2];
		$file['date'] = $file[3];
		$thumbnailObj = new OC_Thumbnail($dir, $file['name']);
		$thumbUrl = $thumbnailObj -> createAndGetThumbByFile($file);
		OC_JSON::success(array('thumbUrl' => $thumbUrl));
		break;
	default :
		break;
}
?>