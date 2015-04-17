<?php

function sendJsonOverPost($url, $json) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);

	// For json, change the content-type.
	curl_setopt ($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/html;"));

	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

	// Return a variable instead of posting it directly
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // ask for results to be returned
	//if(CurlHelper::checkHttpsURL($url)) {
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	//}

	// Send to remote and return data to caller.
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}

function writeLog ($type, $postText) {
	$datetime = date('ymd_His');
	$logfile = dirname(__FILE__).'/log/'.$type.'_' . $datetime . '.log';
	$FileHandle = fopen($logfile, 'w');		// or die('can't open file');
	fwrite($FileHandle, $postText);
	fclose($FileHandle);
}

function file_get_json_utf8($fn) {
	$opts = array(
        'http' => array(
            'method'=>"GET",
            'header'=>"Content-Type: application/json; charset=utf-8"
		)
	);

	$context = stream_context_create($opts);
	$result = @file_get_contents($fn,false,$context);
	return $result;
}

function EmptyJsonFormat($arrayName)
{
	if (is_null($arrayName))
	{
		return array('' => '');
	}
	else
	{
		if (count($arrayName) == 0)
		{
			return array('' => '');
		}

		return $arrayName;
	}
}
