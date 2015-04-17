<?php
/**
 * ownCloud - Public Share plugin
 *
 * @author Caro Huang
 * @copyright 2014 www.u-sync.com
 *
 * 通用function
 */

class OC_PublicShare_Helper {
    /**
     * 產生隨機的 token
     * @return string
     */
    static function createRandomToken() {
        $randToken = OC_Helper::randomPassword(20, true, true, false);
        # 如果產生的 token 和 DB 裡的重複
        if (OC_PublicShare_DB::getDbByToken($randToken))
            return self::createRandomToken();
        else
            return $randToken;
    }

    /**
     * 取得到期日(UTC格式)
     * @param limit days
     * @return string date
     */
    static function getDeadlineUtcByLimitDays($limitDays = null) {
        # 取得今天的local day
        $nowDate = OC_Helper::formatDateTimeUTCToLocal(null, 'Y-m-d');
        # 將local day加上到期天數,但值為null時則使用系統預設
        if (!$limitDays) {
            # 因為限制天數包含當天，所以將取得的限制天數-1
            $limitDays = OC_PublicShare_Config::shareLimitDays() - 1;
        }
        $deadline = OC_Helper::computingDateTime($nowDate, array('day' => $limitDays));
        # 將到期日轉為UTC格式
        return $deadline = OC_Helper::formatDateTimeLocalToUTC($deadline);
    }

    /**
     * 回傳連結網址
     * @param token
     * @return url
     */
    static function getUrl($token) {
        return $url = $_SERVER['HTTP_HOST'] . OC_Helper::linkTo(OC_PublicShare_Config::appId, 'get.php') . '?token=' . $token;
    }

    /**
     * 用 curl 方式向短連結網站取得短連結
     * @return url
     * @return string short url
     */
    static function getShortUrl($url) {
        $toURL = $_SERVER['HTTP_HOST'] . '/s/api_public_share.php';
        if (stripos($url, 'https://') !== 0 && stripos($url, 'http://') !== 0) {
            $url = OC_Helper::getProtocol() . $url;
        }
        $post = array("url" => $url);
        $result = OC_Helper::curlToServer($toURL, true, false, $post);
        return $result;
    }

    /**
     * 回傳是否已到期
     * @param deadline UTC
     * @return bool
     */
    static function isOutOfDeadline($deadlineUtc) {
        $nowUtc = OC_Helper::formatDateTimeLocalToUTC();
        # 比對現在的時間和到期日
        $compare = OC_Helper::compareDateTime($nowUtc, $deadlineUtc);
        if ($compare > 0) {
            return true;
        }
        return false;
    }

}
?>