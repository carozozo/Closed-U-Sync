<?php
$appId='files_mv';
OC_Util::addScript($appId, 'files_mv');
// OC_Util::addStyle($appId, 'files_mv');

OC::$CLASSPATH['OC_FilesMv'] = 'apps/' . $appId . '/lib/files_mv.php';
?>