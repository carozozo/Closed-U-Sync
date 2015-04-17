<?php
if (isset($_REQUEST['progress_key'])) {
	$status = apc_fetch('upload_' . $_REQUEST['progress_key']);
	//echo $status['total'];
	if ($status['total'] != 0) {
		echo round($status['current'] / $status['total'] * 100);
	}
}
?>