<?php
require_once ('../../../lib/base.php');
OC_JSON::checkLoggedIn();
OC_JSON::checkAppEnabled('files_mv');

$action = $_REQUEST['action'];
switch ($action) {
    case 'getDirTree' :
        $dir = $_REQUEST['dir'];
        $dirTree = OC_FilesMv::getDirTree($dir);
        OC_JSON::encodedPrint($dirTree);
        break;
    case 'mvToTarget' :
        $dir = $_REQUEST['dir'];
        $destDir = $_REQUEST['destDir'];
        $files = $_REQUEST['files'];
        $filesArr = OC_FilesMv::mvToTarget($dir, $destDir, $files);
        OC_JSON::success($filesArr);
        break;
    default :
        OC_JSON::error();
        break;
}
