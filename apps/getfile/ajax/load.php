<?php
require_once ('../../../lib/base.php');

OC_JSON::checkLoggedIn();
OC_JSON::checkAppEnabled('getfile');

if (empty($_POST['getfileSrc'])) {
	OC_JSON::error(array('data' => array('message' => 'No URL was supplied. Please send an url.')));
	exit();
}
$dir = stripslashes($_POST['dir']) . '/';
$fileUrl = $_POST['getfileSrc'];
$fileName = "";
if (!empty($_POST['getfileName'])) {
	$fileName = $_POST['getfileName'];
} else {
	$fileName = basename($fileUrl);
}
$error = '';
$target = $dir . $fileName;
$l = new OC_L10N('getfile');
try {
	// if (file_get_contents($fileUrl)) {
		if (OC_Filesystem::file_exists($target)) {
			$result = array('status' => 'file_exists', 'message' => $l -> t('File exist'));
			OC_JSON::encodedPrint($result);
			exit;
		}
		if (OC_Filesystem::file_put_contents($target, file_get_contents($fileUrl)) !== false) {
			$result = array('status' => 'success', 'mime' => OC_Filesystem::getMimeType($target), 'size' => OC_Filesystem::filesize($target), 'name' => $fileName);
			OC_JSON::encodedPrint($result);
		} else {
			$result = array('status' => 'fail', 'message' => $l -> t('Can not store file'));
			OC_JSON::encodedPrint($result);
		}
	// } else {
		// $result = array('status' => 'fail', 'message' => $l -> t('Can not get source file'));
		// OC_JSON::encodedPrint($result);
	// }
} catch(exception $e) {
	$result = array('status' => 'fail', 'message' => $l -> t('Can not store file'));
	OC_JSON::encodedPrint($result);
}
