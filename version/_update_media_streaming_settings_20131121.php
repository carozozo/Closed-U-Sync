<?php
OC_Appconfig::setValue('media_streaming', 'tmpDirFullPath', '/vtmp/');

try {
	$query = OC_DB::prepare("REPLACE INTO *PREFIX*media_streaming_status (status, status_title) VALUES (?,?)");
	$result = $query -> execute(array(
		-14,
		'Output Not Exists',
	));
} catch(exception $e) {
}