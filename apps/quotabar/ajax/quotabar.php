<?php
require_once ('../../../lib/base.php');

OC_JSON::checkLoggedIn();
OC_JSON::checkAppEnabled('quotabar');

try {
	$used = OC_Filesystem::filesize();
	$free = OC_Filesystem::free_space();
	$total = $free + $used;
	// $relative = 0;
	$relative = "?";
	if ( $total ) {
		$relative = intval(round(($used / $total) * 10000) / 100);
	}
	$used = round($used / 1024 / 1024 / 1024, 2);
	$total = round($total / 1024 / 1024 / 1024, 2);
	OC_JSON::success(array('used' => $used, 'total' => $total, 'relative' => $relative));
} catch(exception $e) {
	OC_JSON::error();
}
?>