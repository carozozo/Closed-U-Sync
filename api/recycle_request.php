<?php
$RUNTIME_NOSETUPFS = true;
$RUNTIME_NOWEBFILES = true;
require_once ('../lib/base.php');
$params = OC_API::checkApiUser();
$userId = $params['userId'];
$action = $params['action'];

switch ($action) {
    /* 舊版 API */
    case 'moveToRecycle' :
        $dir = $_REQUEST['dir'];
        $src = $_REQUEST['src'];
        OC_Recycle::recyle($dir, $src);

        $success = OC_Helper::strToArr($src, '|');
        $error = array();
        $returnArray = array(
            'success' => $success,
            'error' => $error,
        );
        OC_JSON::success(array('returnArray' => $returnArray));
        break;
    case 'getRecycleList' :
        $items = OC_Recycle::getRecList();
        $recycleList = array();
        foreach ($items as $index => $property) {
            $recycleList[$index]['originPath'] = $property -> sourcePath;
            $recycleList[$index]['recycledDate'] = $property -> recycleTimeLocal;
        }
        OC_JSON::success(array('recycleList' => $recycleList));
        break;
    case 'revertRecycle' :
        $originPath = $_REQUEST['originPath'];
        $recycledDate = $_REQUEST['recycledDate'];
        $recycledDateUtc = OC_Helper::formatDateTimeLocalToUTC($recycledDate);
        $item = OC_Recycle_DB::getDbBySouAndRec($originPath, $recycledDateUtc);
        if ($item) {
            $sn = $item['sn'];
            $items = OC_Recycle::revert($sn, '');
            OC_JSON::success(array('result' => true));
        }
        break;
    case 'revertSelectedRecycle' :
        // $recycleArray = $_REQUEST['recycleArray'];
        $originPath = $_REQUEST['originPath'];
        $recycledDate = $_REQUEST['recycledDate'];
        $recycleArray = mergeToRecycleArray($originPath, $recycledDate);
        $resultArray = array();
        foreach ($recycleArray as $recycle) {
            $originPath = $recycle['originPath'];
            $recycledDate = $recycle['recycledDate'];
            $recycledDateUtc = OC_Helper::formatDateTimeLocalToUTC($recycledDate);
            $item = OC_Recycle_DB::getDbBySouAndRec($originPath, $recycledDateUtc);
            $sn = $item['sn'];
            $items = OC_Recycle::revert($sn, '');
            $resultArray[] = true;
        }
        OC_JSON::success(array('resultArray' => $resultArray));
        break;
    case 'deleteRecycle' :
        $originPath = $_REQUEST['originPath'];
        $recycledDate = $_REQUEST['recycledDate'];
        $recycledDateUtc = OC_Helper::formatDateTimeLocalToUTC($recycledDate);
        $item = OC_Recycle_DB::getDbBySouAndRec($originPath, $recycledDateUtc);
        if ($item) {
            $sn = $item['sn'];
            $items = OC_Recycle::delete($sn, '');
            OC_JSON::success(array('result' => true));
        }
        break;
    case 'deleteSelectedRecycle' :
        $originPath = $_REQUEST['originPath'];
        $recycledDate = $_REQUEST['recycledDate'];
        $recycleArray = mergeToRecycleArray($originPath, $recycledDate);
        $resultArray = array();
        foreach ($recycleArray as $recycle) {
            $originPath = $recycle['originPath'];
            $recycledDate = $recycle['recycledDate'];
            $recycledDateUtc = OC_Helper::formatDateTimeLocalToUTC($recycledDate);
            $item = OC_Recycle_DB::getDbBySouAndRec($originPath, $recycledDateUtc);
            $sn = $item['sn'];
            $items = OC_Recycle::delete($sn, '');
            $resultArray[] = true;
        }
        OC_JSON::success(array('resultArray' => $resultArray));
        break;
    case 'cleanUpRecycle' :
        $result = OC_Recycle::delete();
        OC_JSON::success(array('result' => $result));
        break;
    /* 新版 API */
    case 'recycle' :
        $dir = $_REQUEST['dir'];
        $files = $_REQUEST['files'];
        OC_Recycle::recyle($dir, $files);
        OC_JSON::success();
        break;
    case 'getRecList' :
        $sn = $_REQUEST['sn'];
        $assignPath = $_REQUEST['assignPath'];
        $sortBy = ($_REQUEST['sortBy']) ? $_REQUEST['sortBy'] : 'time';
        $sort = ($_REQUEST['sort']) ? $_REQUEST['sort'] : 'ASC';
        $items = OC_Recycle::getRecList($sn, $assignPath, $sortBy, $sort);
        OC_JSON::success(array('items' => $items));
        break;
    case 'revRec' :
        $sn = $_REQUEST['sn'];
        $assignPath = $_REQUEST['assignPath'];
        $items = OC_Recycle::revert($sn, $assignPath);
        OC_JSON::success(array('items' => $items));
        break;
    case 'delRec' :
        $sn = (isset($_REQUEST['sn'])) ? $_REQUEST['sn'] : null;
        $assignPath = (isset($_REQUEST['assignPath'])) ? $_REQUEST['assignPath'] : null;
        $items = OC_Recycle::delete($sn, $assignPath);
        OC_JSON::success(array('items' => $items));
        break;
    default :
        OC_JSON::error();
        break;
}

/**
 * 給舊版API用的 function，待 device 確認已經不用舊版 api 時可移除
 */
function mergeToRecycleArray($originPath, $recycledDate) {
    $originPathArray = explode('|', $originPath);
    $recycledDateArray = explode('|', $recycledDate);
    if (count($originPathArray) != count($recycledDateArray)) {
        return FALSE;
    }
    $recycleArray = array();
    foreach ($originPathArray as $index => $originPath) {
        $recycle = array(
            'originPath' => $originPath,
            'recycledDate' => $recycledDateArray[$index]
        );
        $recycleArray[] = $recycle;
    }
    return $recycleArray;
}
