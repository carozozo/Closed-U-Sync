<?php
$RUNTIME_NOSETUPFS = true;
require_once '../../lib/base.php';
OC_JSON::checkAppEnabled('files_publicshare');

# get the path of the shared file
$token = $_REQUEST['token'];
$filePath = $_REQUEST['filePath'];
$pwd = $_REQUEST['pwd'];
OC_PublicShare_Download::downloadByPwdToken($pwd, $token, $filePath);
?>