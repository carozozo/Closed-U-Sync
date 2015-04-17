<?php
require_once ('../../../lib/base.php');

OC_JSON::checkAdminUser();
OC_JSON::checkAppEnabled('artdisk_render');

if (isset($_REQUEST['configArr'])) {
	$configArr = $_REQUEST['configArr'];
	$noMethodArr = array();
	foreach ($configArr as $index => $config) {
		$key = $config['name'];
		$val = $config['value'];
		$funName = 'set' . ucfirst($key);
		if (method_exists("OC_ArtdiskRender_Config", $funName)) {
			OC_ArtdiskRender_Config::$funName($val);
		} else {
			$noMethodArr[] = $funName;
		}
	}

	# 如果所有存值的funcion都有執行
	if (count($noMethodArr) <= 0) {
		# 回傳更新後的config值給前端
		$configItems = OC_ArtdiskRender_Config::getConfigItems();
		OC_JSON::success(array('configItems' => $configItems));
		exit();
	}
	$noMethodArr = implode(',', $noMethodArr);
	OC_JSON::error(array('noMethodArrStr' => $noMethodArr));
	exit();

}
OC_JSON::error();
?>