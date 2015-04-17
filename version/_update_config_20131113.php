<?php
OC_Helper::deleteDirByFullPath('/var/www/html/lib/servertype/p1');
OC_Helper::deleteDirByFullPath('/var/www/html/lib/servertype/s1');
OC_Helper::deleteDirByFullPath('/var/www/html/lib/servertype/s2');
$siteTitle = OC_Helper::siteTitle();
# 將口袋碟的server type改為 p2
if ($siteTitle == '口袋碟') {
	OC_Config::setValue('serverType', 'p2', 'CONFIG_CUSTOM');
}
?>