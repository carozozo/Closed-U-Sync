<?php
$appId='files_copy';
OC_Util::addScript($appId, 'files_copy');
// OC_Util::addStyle($appId, 'files_copy');

OC::$CLASSPATH['OC_FilesCopy'] = 'apps/' . $appId . '/lib/files_copy.php';

?>