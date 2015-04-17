<?php
/**
 * ownCloud
 *
 * @author Frank Karlitschek
 * @author Jakob Sack
 * @copyright 2010 Frank Karlitschek karlitschek@kde.org
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * Collection of useful functions
 */
class OC_Helper {
    /**
     * @brief Creates an url
     * @param $app app
     * @param $file file
     * @param $redirect_url redirect_url variable is appended to the URL
     * @returns the url
     *
     * Returns a url to the given app and file.
     */
    public static function linkTo($app, $file, $redirect_url = NULL, $absolute = false) {
        if ($app != '') {
            $app .= '/';
            // Check if the app is in the app folder
            if (file_exists(OC::$SERVERROOT . '/apps/' . $app . $file)) {
                $urlLinkTo = OC::$WEBROOT . '/apps/' . $app . $file;
            } else {
                $urlLinkTo = OC::$WEBROOT . '/' . $app . $file;
            }
        } else {
            if (file_exists(OC::$SERVERROOT . '/core/' . $file)) {
                $urlLinkTo = OC::$WEBROOT . '/core/' . $file;
            } else {
                $urlLinkTo = OC::$WEBROOT . '/' . $file;
            }
        }

        if ($absolute) {
            // Checking if the request was made through HTTPS. The last in line is for IIS
            $protocol = isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] != 'off');
            $urlLinkTo = ($protocol ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $urlLinkTo;
        }

        if ($redirect_url)
            return $urlLinkTo . '?redirect_url=' . urlencode($_SERVER["REQUEST_URI"]);
        else
            return $urlLinkTo;

    }

    /**
     * @brief Creates path to an image
     * @param $app app
     * @param $image image name
     * @returns the url
     *
     * Returns the path to the image.
     */
    public static function imagePath($app, $image) {
        // Check if the app is in the app folder
        if (file_exists(OC::$SERVERROOT . "/apps/$app/img/$image")) {
            return OC::$WEBROOT . "/apps/$app/img/$image";
        } elseif (!empty($app)) {
            return OC::$WEBROOT . "/$app/img/$image";
        } else {
            return OC::$WEBROOT . "/core/img/$image";
        }
    }

    /**
     * @brief get path to icon of file type
     * @param $mimetype mimetype
     * @returns the url
     *
     * Returns the path to the image of this file type.
     */
    public static function mimetypeIcon($mimetype = NULL) {
        if (isset($mimetype) && $mimetype) {
            $alias = array('application/xml' => 'code/xml');
            //      echo $mimetype;
            if (isset($alias[$mimetype])) {
                $mimetype = $alias[$mimetype];
                //          echo $mimetype;
            }
            // Replace slash with a minus
            $mimetype = str_replace("/", "-", $mimetype);
            // Is it a dir?
            if ($mimetype == "dir") {
                return OC::$WEBROOT . "/core/img/filetypes/folder.png";
            }

            // Icon exists?
            if (file_exists(OC::$SERVERROOT . "/core/img/filetypes/$mimetype.png")) {
                return OC::$WEBROOT . "/core/img/filetypes/$mimetype.png";
            }
            //try only the first part of the filetype
            $mimetype = substr($mimetype, 0, strpos($mimetype, '-'));
            if (file_exists(OC::$SERVERROOT . "/core/img/filetypes/$mimetype.png")) {
                return OC::$WEBROOT . "/core/img/filetypes/$mimetype.png";
            } else {
                return OC::$WEBROOT . "/core/img/filetypes/file.png";
            }
        } else {
            return OC::$WEBROOT . "/core/img/filetypes/file.png";
        }
    }

    /**
     * @brief Make a human file size
     * @param $bytes file size in bytes
     * @returns a human readable file size
     *
     * Makes 2048 to 2 kB.
     */
    public static function humanFileSize($bytes) {
        if ($bytes < 1024) {
            return "$bytes B";
        }
        $bytes = round($bytes / 1024, 1);
        if ($bytes < 1024) {
            return "$bytes kB";
        }
        $bytes = round($bytes / 1024, 1);
        if ($bytes < 1024) {
            return "$bytes MB";
        }

        // Wow, heavy duty for owncloud
        $bytes = round($bytes / 1024, 1);
        return "$bytes GB";
    }

    /**
     * @brief Make a computer file size
     * @param $str file size in a fancy format
     * @returns a file size in bytes
     *
     * Makes 2kB to 2048.
     *
     * Inspired by: http://www.php.net/manual/en/function.filesize.php#92418
     */
    public static function computerFileSize($str) {
        $bytes = 0;
        $str = strtolower($str);

        $bytes_array = array(
            'b' => 1,
            'k' => 1024,
            'kb' => 1024,
            'mb' => 1024 * 1024,
            'm' => 1024 * 1024,
            'gb' => 1024 * 1024 * 1024,
            'g' => 1024 * 1024 * 1024,
            'tb' => 1024 * 1024 * 1024 * 1024,
            't' => 1024 * 1024 * 1024 * 1024,
            'pb' => 1024 * 1024 * 1024 * 1024 * 1024,
            'p' => 1024 * 1024 * 1024 * 1024 * 1024,
        );

        $bytes = floatval($str);

        if (preg_match('#([kmgtp]?b?)$#si', $str, $matches) && !empty($bytes_array[$matches[1]])) {
            $bytes *= $bytes_array[$matches[1]];
        }

        $bytes = round($bytes, 2);

        return $bytes;
    }

    /**
     * @brief Checks $_REQUEST contains a var for the $s key. If so, returns the html-escaped value of this var; otherwise returns the default value provided by $d.
     * @param $s name of the var to escape, if set.
     * @param $d default value.
     * @returns the print-safe value.
     *
     */

    //FIXME: should also check for value validation (i.e. the email is an email).
    public static function init_var($s, $d = "") {
        $r = $d;
        if (isset($_REQUEST[$s]) && !empty($_REQUEST[$s]))
            $r = stripslashes(htmlspecialchars($_REQUEST[$s]));

        return $r;
    }

    /**
     * returns "checked"-attribut if request contains selected radio element OR if radio element is the default one -- maybe?
     * @param string $s Name of radio-button element name
     * @param string $v Value of current radio-button element
     * @param string $d Value of default radio-button element
     */
    public static function init_radio($s, $v, $d) {
        if ((isset($_REQUEST[$s]) && $_REQUEST[$s] == $v) || (!isset($_REQUEST[$s]) && $v == $d))
            print "checked=\"checked\" ";
    }

    /**
     * detect if a given program is found in the search PATH
     *
     * @param  string  program name
     * @param  string  optional search path, defaults to $PATH
     * @return bool    true if executable program found in path
     */
    public static function canExecute($name, $path = false) {
        // path defaults to PATH from environment if not set
        if ($path === false) {
            $path = getenv("PATH");
        }
        // check method depends on operating system
        if (!strncmp(PHP_OS, "WIN", 3)) {
            // on Windows an appropriate COM or EXE file needs to exist
            $exts = array(
                ".exe",
                ".com"
            );
            $check_fn = "file_exists";
        } else {
            // anywhere else we look for an executable file of that name
            $exts = array("");
            $check_fn = "is_executable";
        }
        // Default check will be done with $path directories :
        $dirs = explode(PATH_SEPARATOR, $path);
        // WARNING : We have to check if open_basedir is enabled :
        $obd = ini_get('open_basedir');
        if ($obd != "none")
            $obd_values = explode(PATH_SEPARATOR, $obd);
        if (count($obd_values) > 0 and $obd_values[0]) {
            // open_basedir is in effect !
            // We need to check if the program is in one of these dirs :
            $dirs = $obd_values;
        }
        foreach ($dirs as $dir) {
            foreach ($exts as $ext) {
                if ($check_fn("$dir/$name" . $ext))
                    return true;
            }
        }
        return false;
    }

    /**
     * 產生亂數
     * @param 指定亂數長度, 是否包含英文, 是否包含數字, 是否含英文大寫
     * @return string
     */
    public static function randomPassword($length, $ifEng = true, $ifNum = false, $ifUpper = false) {
        $alphabet = '';
        if ($ifNum)
            $alphabet .= "0123456789";
        if ($ifUpper)
            $alphabet .= "ABCDEFGHIJKLMNOPQRSTUWXYZ";
        if ($ifEng || $alphabet == '')
            $alphabet .= "abcdefghijklmnopqrstuwxyz";
        $pass = array();
        //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1;
        //put the length -1 in cache
        for ($i = 0; $i < $length; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
        //turn the array into a string
    }

    /**
     * 將字串轉為陣列
     * @param 字串, 分割附號
     */
    public static function strToArr($str, $delimiter) {
        if (is_array($str)) {
            return $str;
        }
        if (is_string($str)) {
            $arr = array();
            if (stripos($str, $delimiter) !== false) {
                # 有符號，轉成array
                $arr = explode($delimiter, $str);

            } else {
                $arr = array($str);
            }
            $arr = array_filter($arr);
            return $arr;
        }
    }

    /* ============================================= 系統相關訊息 ============================================= */

    /**
     * 從config檔中，取得是否開啟付費系統
     * @return boolean
     */
    public static function paidSystemEnable() {
        return $paidSystemEnable = OC_Config::getValue('paidSystemEnable', false, 'CONFIG_CUSTOM');
    }

    /**
     * 取得web的根目錄
     * @return boolean
     */
    public static function getWebRoot() {
        return OC::$WEBROOT;
    }

    /**
     * 取得 server類型
     * @return string
     */
    public static function serverType() {
        $serverType = OC_Config::getValue('serverType', null, 'CONFIG_CUSTOM');
        return $serverType;
    }

    /**
     * 取得 server主要類型
     * @return string
     */
    public static function serverMainType() {
        $serverType = OC_Config::getValue('serverType', null, 'CONFIG_CUSTOM');
        $serverType = substr($serverType, 0, 1);
        return $serverType;
    }

    /**
     * 取得 server次要類型
     * @return string
     */
    public static function serverSubType() {
        $serverType = OC_Config::getValue('serverType', null, 'CONFIG_CUSTOM');
        $serverType = substr($serverType, 1);
        return $serverType;
    }

    /**
     * 取得網站標題
     * @return string
     */
    public static function siteTitle() {
        $siteTitle = OC_Config::getValue('siteTitle', null, 'CONFIG_CUSTOM');
        return $siteTitle;
    }

    /**
     * 取得目前server的protocol
     * @return string
     */
    public static function getProtocol() {
        return $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    }

    /**
     * 利用curl的方式呼叫其它server
     * @param server url,if post, if ssl, post variable array
     * @return string
     */
    public static function curlToServer($toURL, $post = true, $ssl = true, $postFields = null) {
        $ch = curl_init();
        # CURLOPT_RETURNTRANSFER:網頁回應,CURLOPT_POST:使用POST,CURLOPT_SSL_VERIFYPEER:SSL驗證
        $options = array(
            CURLOPT_URL => $toURL,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => $post,
            CURLOPT_SSL_VERIFYPEER => $ssl,
        );
        if ($postFields) {
            $options[CURLOPT_POSTFIELDS] = http_build_query($postFields);
        }

        curl_setopt_array($ch, $options);
        return $result = curl_exec($ch);
    }

    /**
     * 回傳音樂格式的mime type列表
     * @return array
     */
    public static function audioTypeArr() {
        return $mediaTypeArray = array('audio/mpeg', );
    }

    /**
     * 回傳影片格式的mime type列表
     * @return array
     */
    public static function mediaTypeArr() {
        return $mediaTypeArray = array(
            'video/mp4',
            'video/x-ms-wmv',
            'video/quicktime',
            'video/quicktime',
            'video/3gpp',
            'video/x-matroska',
            'video/mpeg',
            'application/vnd.rn-realmedia',
            'video/vnd.rn-realvideo',
            'video/x-flv',
            'video/x-msvideo',
            'video/x-ms-asf',
            'application/x-shockwave-flash'
        );
    }

    /* ============================================= 資料夾/檔案相關處理 ============================================= */

    /**
     * requide_once 指定資料夾底下的檔案
     * @author Caro Huang
     * @param $dir 資料夾路徑
     * @param $includeType 要require的檔案類型
     * @param $ifLoop 是否全部掃瞄
     *
     */
    static function requireDirByFileType($dir, $includeType = null, $ifScan = false) {
        if (is_dir($dir) && $dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                $file = $dir . $file;
                if (is_dir($file)) {
                    if ($ifScan) {
                        self::requireDirByFileType($file, $includeType, $ifLoop);
                    }
                } else {
                    $fileType = pathinfo($file, PATHINFO_EXTENSION);
                    if ($includeType && $fileType === $includeType) {
                        require_once ($file);
                    }
                }
            }
            closedir($dh);
        }
    }

    /**
     * @brief Recusive editing of file permissions
     * @param $path path to file or folder
     * @param $filemode unix style file permissions as integer
     *
     */
    static function chmodr($path, $filemode) {
        if (!is_dir($path))
            return chmod($path, $filemode);
        $dh = opendir($path);
        while (($file = readdir($dh)) !== false) {
            if ($file != '.' && $file != '..') {
                $fullpath = $path . '/' . $file;
                if (is_link($fullpath))
                    return false;
                elseif (!is_dir($fullpath) && !chmod($fullpath, $filemode))
                    return false;
                elseif (!self::chmodr($fullpath, $filemode))
                    return false;
            }
        }
        closedir($dh);
        if (@chmod($path, $filemode))
            return true;
        else
            return false;
    }

    /**
     * 複製檔案/資料夾
     * @param string $src source folder
     * @param string $dest target folder
     *
     */
    static function copyr($src, $dest) {
        if (!file_exists($src)) {
            return false;
        }
        # 預先產生目的地的資料夾
        $destDir = dirname($dest);
        if (!self::createDirByFullPath($destDir)) {
            return false;
        }
        if (is_dir($src)) {
            $files = array_diff(scandir($src), array(
                '.',
                '..'
            ));
            if (count($files) <= 0) {
                # 底下沒檔案，直接產生目的地資料夾
                return self::createDirByFullPath($dest);
            }
            foreach ($files as $file) {
                $file1 = self::pathForbiddenChar($src . '/' . $file);
                $file2 = self::pathForbiddenChar($dest . '/' . $file);
                if (!self::copyr($file1, $file2)) {
                    return false;
                }
            }
            # 如果資料夾底下的檔案都複製了
            return true;
        }
        return copy($src, $dest);
    }

    /**
     * 移動/更名，如果是資料夾，則底下的檔案也全部跟著改變
     * @param $src 來源檔完整路徑
     * @param $dest 目的地完整路徑
     */
    static function renamer($src, $dest) {
        if (!file_exists($src)) {
            return false;
        }
        # 預先產生目的地的資料夾
        $destDir = dirname($dest);
        if (!self::createDirByFullPath($destDir)) {
            return false;
        }
        if (is_dir($src)) {
            $files = array_diff(scandir($src), array(
                '.',
                '..'
            ));
            if (count($files) <= 0) {
                # 底下沒檔案，直接移動資料夾
                return rename($src, $dest);
            }
            foreach ($files as $file) {
                $file1 = self::pathForbiddenChar($src . '/' . $file);
                $file2 = self::pathForbiddenChar($dest . '/' . $file);
                if (!self::renamer($file1, $file2)) {
                    return false;
                }
            }
            # 如果資料夾底下的檔案都移走了，則刪除該資料夾
            self::rmdirr($src);
            return true;
        }
        return rename($src, $dest);
    }

    /**
     * @brief Recusive deletion of folders
     * @param string $dir path to the folder
     *
     */
    static function rmdirr($dir) {
        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $file) {
                if ($file != "." && $file != "..") {
                    if (!self::rmdirr("$dir/$file")) {
                        return false;
                    }
                }
            }
            return rmdir($dir);
        } elseif (file_exists($dir)) {
            return unlink($dir);
        }
    }

    /**
     * 根據傳送過來的路徑，依序產生資料夾
     * @param 要 產生的資料夾完整路徑
     * @return boolean
     */
    public static function createDirByFullPath($fullPath) {
        if (!file_exists($fullPath)) {
            $pathArr = explode("/", $fullPath);
            $dirPath = '';
            foreach ($pathArr as $eachDir) {
                $dirPath .= '/' . $eachDir;
                if (!file_exists($dirPath)) {
                    @mkdir($dirPath);
                }
            }
        }
        return true;
    }

    /**
     * 刪除資料夾底下的所有檔案及無效的symboic link
     * @param 資料夾完整路徑,要排除的檔案列表,是否刪除資料夾本身,是否刪除底下的資料夾,是否刪除底下的檔案
     * @return boolean
     */
    static function deleteDirByFullPath($dirFullPath, $excludePathArr = array(), $deleteSelf = true, $deleteDir = true, $deleteFile = true) {
        if (!is_dir($dirFullPath)) {
            // throw new InvalidArgumentException("$dirFullPath must be a directory");
            return false;
        }
        #刪除該資料夾底下無效的連結
        exec("find -L " . $dirFullPath . " -type l -delete");

        if (substr($dirFullPath, strlen($dirFullPath) - 1, 1) != '/') {
            $dirFullPath .= '/';
        }
        # glob 無法找出隱藏檔案，所以改用 scandir
        // $files = glob($dirFullPath . '*', GLOB_MARK);
        $files = array_diff(scandir($dirFullPath), array(
            '.',
            '..'
        ));
        foreach ($files as $file) {
            $file = self::pathForbiddenChar($dirFullPath . '/' . $file);

            #如果不在排除名單內的話,則開始執行刪除
            if (!in_array($file, $excludePathArr)) {
                if (is_dir($file) && $deleteDir) {
                    self::deleteDirByFullPath($file);
                } elseif ($deleteFile) {
                    @unlink($file);
                }
            }
        }
        if ($deleteSelf) {
            @rmdir($dirFullPath);
        }
        return true;
    }

    /* ============================================= 日期時間相關處理 ============================================= */

    /**
     * 取得UTC格式的日期時間
     * @param 日期格式字串(未輸入時,則預設為現在的時間), 要輸出的格式
     * @return 日期格式字串
     */
    public static function formatDateTimeLocalToUTC($dateTimeStr = NULL, $format = 'Y-m-d H:i:s') {
        $date = new DateTime($dateTimeStr);
        $date -> setTimezone(new DateTimeZone('UTC'));
        return $dateStr = $date -> format($format);
    }

    /**
     * 取得本地日期時間
     * @param 日期格式字串(未輸入時,則預設為現在的時間), 要輸出的格式
     * @return 日期格式字串
     */
    public static function formatDateTimeUTCToLocal($dateTimeStr = NULL, $format = 'Y-m-d H:i:s') {
        $date = new DateTime($dateTimeStr, new DateTimeZone('UTC'));
        $date -> setTimezone(new DateTimeZone(date_default_timezone_get()));
        return $dateStr = $date -> format($format);
    }

    /**
     * 將時間字串轉為秒數
     * @param 時間字串, 例如 12:00:00
     * @return int
     */
    public static function formatTimeToSeconds($timeStr) {
        $hours = (int) substr($timeStr, 0, 2);
        $mins = (int) substr($timeStr, 3, 2) + $hours * 60;
        $secs = (int) substr($timeStr, 6, 2) + $mins * 60;
        if (strlen($timeStr) > 9) {
            $secs += ((int) substr($timeStr, 9, 2)) / 100;
        }
        return $secs;
    }

    /**
     * 日期時間的加減
     * @param 日期格式字串, 運算的array('year' => 0, 'month' => 0, 'day' => 0 'hour' => 0 'minute' => 0 'second' => 0), 要輸出的字串格式'Y-m-d H:i:s'
     * @return 日期格式字串
     */
    static function computingDateTime($dateTimeStr, $computArr, $format = 'Y-m-d H:i:s') {
        $date = new DateTime($dateTimeStr);
        $year = $date -> format('Y');
        $month = $date -> format('m');
        $day = $date -> format('d');
        $hour = $date -> format('H');
        $minute = $date -> format('i');
        $second = $date -> format('s');

        $c_year = (key_exists('year', $computArr)) ? $computArr['year'] : 0;
        $c_month = (key_exists('month', $computArr)) ? $computArr['month'] : 0;
        $c_day = (key_exists('day', $computArr)) ? $computArr['day'] : 0;
        $c_hour = (key_exists('hour', $computArr)) ? $computArr['hour'] : 0;
        $c_minute = (key_exists('minute', $computArr)) ? $computArr['minute'] : 0;
        $c_second = (key_exists('second', $computArr)) ? $computArr['second'] : 0;

        return $limitDate = date($format, mktime($hour + $c_hour, $minute + $c_minute, $second + $c_second, $month + $c_month, $day + $c_day, $year + $c_year));
    }

    /**
     * 比較日期時間大小
     * @param 日期格式字串1,日期格式字串2
     * @return 大於回傳 1, 等於回傳 0, 小於回傳 -1
     */
    static function compareDateTime($dateTimeStr, $dateTimeStr2) {
        if (date("Y-m-d H:i:s", strtotime($dateTimeStr)) == date("Y-m-d H:i:s", strtotime($dateTimeStr2))) {
            return 0;
        }
        if (date("Y-m-d H:i:s", strtotime($dateTimeStr)) > date("Y-m-d H:i:s", strtotime($dateTimeStr2))) {
            return 1;
        }
        if (date("Y-m-d H:i:s", strtotime($dateTimeStr)) < date("Y-m-d H:i:s", strtotime($dateTimeStr2))) {
            return -1;
        }
    }

    /* ============================================= 符號相關處理 ============================================= */

    /**
     * 移除多餘的斜線
     * @author 20130903 by Caro Huang
     * @param  string
     * @return string
     */
    static function filterMultiSlash($str) {
        return $str = preg_replace('/(\/+)/', '/', $str);
    }

    /**
     * 移除不符合規定的符號
     * @param string
     * @return string
     */
    static public function forbiddenChar($str) {
        $fc = OC_Filesystem::$forbiddenCharArray;
        foreach ($fc as $check) {
            $str = str_replace($check, "", $str);
        }
        return $str;
    }

    /**
     * 移除路徑中不符合規定的符號及多餘的斜線
     * @param $path 路徑
     * @param $headSlash 前面是否加上斜線
     * @param $tailSlash 尾巴是否加上斜線
     * @return string
     */
    static public function pathForbiddenChar($path, $headSlash = true, $tailSlash = false) {
        $fc = OC_Filesystem::$forbiddenCharArray;
        # 路徑中可以有斜線符號,所以先將斜線從禁用符號中移除
        if (($key = array_search('/', $fc)) !== false) {
            unset($fc[$key]);
        }
        foreach ($fc as $check) {
            $path = str_replace($check, "", $path);
        }
        if ($headSlash) {
            $path = '/' . $path;
        }
        if ($tailSlash) {
            $path .= '/';
        }
        $path = self::filterMultiSlash($path);
        return $path;
    }

}
