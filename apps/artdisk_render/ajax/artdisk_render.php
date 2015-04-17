<?php
require_once ('../../../lib/base.php');

OC_Util::checkLoggedIn();
OC_JSON::checkAppEnabled('artdisk_render');

$action = $_REQUEST['action'];
switch ($action) {
    case 'getRenderPath' :
        $renderPath = OC_ArtdiskRender::getRenderPath();
        OC_JSON::success(array('renderPath' => $renderPath));
        break;
    case 'createRender' :
        # 取得產生render後的屬性
        $property = OC_ArtdiskRender::createRender();
        $currentStatus = $property -> currentStatus;
        $renderPath = $property -> renderPath;
        # 取得狀態訊息(string)
        $mess = $property -> message();
        OC_JSON::success(array(
            'message' => $mess,
            'currentStatus' => $currentStatus,
            'renderPath' => $renderPath,
        ));
        break;
    default :
        OC_JSON::error();
        break;
}
?>