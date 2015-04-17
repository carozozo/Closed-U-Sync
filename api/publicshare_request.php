<?php
$RUNTIME_NOSETUPFS = true;
$RUNTIME_NOWEBFILES = true;
require_once ('../lib/base.php');
$params = OC_API::checkApiUser();
$userId = $params['userId'];
$action = $params['action'];
switch($action) {
    /* ====================以下為舊版的 api 呼叫(以防 device 用戶如果沒有更新軟體時，呼叫舊的 api 還可以 work)==================== */
    case 'getMySharedItemBySourceInDealine' :
        $sourcePath = $_REQUEST['source'];
        $property = OC_PublicShare::getBySource($sourcePath);
        $ifFromDb = $property -> ifFromDb;
        $isOutOfDeadline = $property -> isOutOfDeadline;
        # 有資料而且沒過期
        if ($ifFromDb && !$isOutOfDeadline) {
            $shortUrl = $property -> shortUrl;
            $deadline = $property -> deadlineLocal;
            OC_JSON::success(array(
                'shortUrl' => $shortUrl,
                'deadline' => $deadline,
            ));
        } else {
            OC_JSON::error(array($source . ' Is Not Shared'));
        }
        break;
    case 'insertMyItem' :
        $sourcePath = $_REQUEST['source'];
        $property = OC_PublicShare::insert($sourcePath);
        if ($property) {
            $shortUrl = $property -> shortUrl;
            $deadline = $property -> deadlineLocal;
            OC_JSON::success(array(
                'shortUrl' => $shortUrl,
                'deadline' => $deadline,
            ));
        } else {
            OC_JSON::error(array('Can Not Insert To DB'));
        }
        break;
    case 'getPublicShareManagerList' :
        $items = OC_PublicShare::getListByUser();
        $returnItems = array();
        foreach ($items as $index => $property) {
            $returnItems[$index]['uid_owner'] = $property -> uid;
            $returnItems[$index]['source'] = $property -> sourcePath;
            $returnItems[$index]['target'] = $property -> token;
            $returnItems[$index]['createDate'] = $property -> insertTimeUtc;
            $returnItems[$index]['deadline'] = OC_Helper::formatDateTimeUTCToLocal($property -> deadlineUtc,'Y-m-d');
            $returnItems[$index]['shortUrl'] = $property -> shortUrl;
        }
        # 因為分享當天也有算，所以限制天數要-1
        $publicShareLimitDays = OC_PublicShare_Config::shareLimitDays() - 1;
        OC_JSON::success(array(
            'items' => $returnItems,
            'publicShareLimitDays' => $publicShareLimitDays
        ));
        break;
    case 'updateDeadlineByTarget' :
        $deadline = $_REQUEST['deadline'];
        $token = $_REQUEST['target'];
        if ($deadline && $token) {
            # 取得預計的最大到期日
            $limitDate = OC_PublicShare_Helper::getDeadlineUtcByLimitDays();
            $deadlineUtc = OC_Helper::formatDateTimeLocalToUTC($deadline);
            if (strtotime($deadlineUtc) <= strtotime($limitDate)) {
                $property = OC_PublicShare::updateByToken($token, null, null, $deadline);
                if ($property) {
                    OC_JSON::success();
                } else {
                    OC_JSON::error(array('errorMessage' => 'Update Failed'));
                }
            } else {
                OC_JSON::error(array('errorMessage' => 'The Date You Input Is Out Of Deadline'));
            }
        } else {
            OC_JSON::error(array('errorMessage' => 'No Deadline Or Target'));
        }
        break;
    case 'deleteItemByTarget' :
        $token = $_REQUEST['target'];
        if ($token) {
            $items = OC_PublicShare::deleteByToken($token);
            if ($items) {
                OC_JSON::success();
            } else {
                OC_JSON::error();
            }
        } else {
            OC_JSON::error(array('No Target'));
        }
        break;
    /* ==================== 以下為新版的 api ==================== */
    case 'getBySource' :
        $sourcePath = $_REQUEST['sourcePath'];
        $property = OC_PublicShare::getBySource($sourcePath);
        OC_JSON::success(array('property' => $property, ));
        break;
    case 'insert' :
        $sourcePath = $_REQUEST['sourcePath'];
        $property = OC_PublicShare::insert($sourcePath);
        OC_JSON::success(array('property' => $property, ));
        break;
    case 'getListByUser' :
        $items = OC_PublicShare::getListByUser();
        OC_JSON::success(array('items' => $items, ));
        break;
    case 'updateByToken' :
        $token = $_REQUEST['token'];
        $pwd = isset($_REQUEST['pwd']) ? $_REQUEST['pwd'] : null;
        $deadlineLocal = isset($_REQUEST['deadline']) ? $_REQUEST['deadline'] : null;
        $property = OC_PublicShare::updateByToken($token, null, $pwd, $deadlineLocal);
        OC_JSON::success(array('property' => $property, ));
        break;
    case 'deleteByToken' :
        $token = $_REQUEST['token'];
        $items = OC_PublicShare::deleteByToken($token);
        OC_JSON::success(array('items' => $items, ));
        break;
    default :
        break;
}
