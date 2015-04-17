<?php
$RUNTIME_NOWEBFILES = true;
require_once ('../lib/base.php');
$params = OC_API::checkApiUser();

$dir = ($_REQUEST['dir']) ? $_REQUEST['dir'] : "";
$thumbnailObj = new OC_Thumbnail_Sync($dir);
OC_JSON::encodedPrint($thumbnailObj -> thumbsSync());
