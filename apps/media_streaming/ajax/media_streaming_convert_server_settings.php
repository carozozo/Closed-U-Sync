<?php
require_once ('../../../lib/base.php');

OC_JSON::checkAdminUser();
OC_JSON::checkAppEnabled('media_streaming');

$action = $_REQUEST['action'];
switch ($action) {
	case 'getConvertList' :
		$convertList = OC_MediaConvert::getConvertList();
		OC_JSON::success(array('convertList' => $convertList));
		break;
	case 'getConvertServerList' :
		$convertServerList = OC_MediaConvertServer::getConvertServerList();
		OC_JSON::success(array('convertServerList' => $convertServerList));
		break;
	case 'setConvertServerDefault' :
		$serverIp = $_REQUEST['serverIp'];
		$waitingConvert = OC_MediaConvert::waiting_convert;
		$mess = OC_MediaConvert::streamingStatusArr($waitingConvert);
		if (OC_MediaConvertServer::updateConvertServer(null, null, $waitingConvert, $serverIp)) {
			OC_JSON::success(array('message' => $mess));
		} else {
			OC_JSON::error();
		}
		break;
	case 'delConvertServer' :
		$serverIp = $_REQUEST['serverIp'];
		if (OC_MediaConvertServer::delConvertServer($serverIp)) {
			OC_JSON::success();
		} else {
			OC_JSON::error();
		}
		break;
	case 'newConvertServer' :
		$serverIp = $_REQUEST['serverIp'];
		$waitingConvert = OC_MediaConvert::waiting_convert;
		$mess = OC_MediaConvert::streamingStatusArr($waitingConvert);
		if (OC_MediaConvertServer::newConvertServer($serverIp)) {
			OC_JSON::success(array('message' => $mess));
		} else {
			OC_JSON::error();
		}
		break;
	default :
		OC_JSON::error();
		break;
}
