<?php
require_once ('../../../lib/base.php');
OC_JSON::checkLoggedIn();
OC_JSON::checkAppEnabled('files_copy');

$action = $_REQUEST['action'];
switch ($action) {
    case 'getDirTree' :
        $dir = $_REQUEST['dir'];
        $dirTree = OC_FilesCopy::getDirTree($dir);
        OC_JSON::encodedPrint($dirTree);
        break;
    case 'copyToTarget' :
        $dir = $_REQUEST['dir'];
        $destDir = $_REQUEST['destDir'];
        $files = $_REQUEST['files'];
        $failedFiles = OC_FilesCopy::copyToTarget($dir, $destDir, $files);
        OC_JSON::success(array('failedFiles' => $failedFiles));
        break;
    default :
        OC_JSON::error();
        break;
}
