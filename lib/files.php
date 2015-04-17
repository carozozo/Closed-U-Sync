<?php

/**
 * ownCloud
 *
 * @author Frank Karlitschek
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
 * Class for fileserver access
 *
 */
class OC_Files {
    static $tmpFiles = array();
    static $levelCount = 1;
    /**
     * 取得資料夾底下的檔案內容
     * @param $dir 資料夾路徑
     * @param $maxLevel 要取得的資料夾層級(預設為1，設為0代表全部層級)
     * @param $arg1 強制顯示全部檔案(SHOW_ALL)
     * @param $arg2 要取得的類型GET_DIR/GET_FILE(預設為GET_ALL)
     * @param $arg3 告知是由什麼呼叫(預設為ALL)
     * @return 由 OC_Files_Property 所組成的 array
     * 強制取得全部 -
     * SHOW_ALL
     *
     * 要取得的類型 -
     * GET_ALL 取得所有檔案類型
     * GET_DIR 取得資料夾
     * GET_FILE 取得檔案
     *
     * 指派呼叫名稱 -
     * ALL 任何呼叫這支程式的function都會生效
     *
     * EX1：
     * 當已指定 OC_Files::addHidePath('/123','COPY');
     * 而呼叫 OC_Files::getDirectoryContent('/','COPY') 時
     * '/123' 不會顯示在列表中
     *
     * EX2：
     * 當已指定 OC_Files::addMarkFileName('/123/dog','pig','FILE_LIST');
     * 而呼叫 OC_Files::getDirectoryContent('/','FILE_LIST') 時
     * '/123/dog' 會顯示為 '/123/pig'
     *
     * EX3：
     * 指定 OC_Files::addHidePath('/123');
     * 而呼叫 OC_Files::getDirectoryContent('/','SHOW_ALL') 時
     * '/123' 仍然會在列表中
     *
     * EX4：
     * 資料夾結構為/1/2/3/4
     * 而呼叫 OC_Files::getDirectoryContent('/',3) 時
     * 只會顯示到/1/2/3
     */
    public static function getDirectoryContent() {
        # 要取得的根目錄
        $dir = '';
        # 要取得的層級數
        $maxLevel = 1;
        # 計數目前所取得的層級數(內部呼叫的隱藏變數)
        $level = 0;
        # 是否強制顯示全部結構
        $showAll = false;
        # 取得的檔案類型(資料夾/檔案/全部)
        $getType = 'GET_ALL';
        # 告知是由什麼呼叫
        $calledBy = 'ALL';

        $argArr = func_get_args();
        # 如果傳進來的值是 array
        if (is_array($argArr[0])) {
            $argArr = $argArr[0];
        }
        foreach ($argArr as $index => $arg) {
            # 取得第一個變數為 dir
            if ($index == 0) {
                $dir = $arg;
                continue;
            }
            # 如果第二個變數是數字，則代入 maxLevel
            if ($index == 1 && is_int($arg)) {
                $maxLevel = $arg;
                continue;
            }
            # 如果第三個變數是數字，則代入 maxLevel
            if ($index == 2 && is_int($arg)) {
                $level = $arg;
                continue;
            }
            if ($arg === 'SHOW_ALL') {
                $showAll = true;
                continue;
            }
            if ($arg === 'GET_ALL' or $arg === 'GET_DIR' or $arg === 'GET_FILE') {
                $getType = $arg;
                continue;
            }
            if (is_string($arg)) {
                $calledBy = $arg;
            }
        }
        if (strpos($dir, OC::$CONFIG_DATADIRECTORY) === 0) {
            $dir = substr($dir, strlen(OC::$CONFIG_DATADIRECTORY));
        }
        $dirs = array();
        $files = array();
        # 累計目前取得的層級數
        $level++;
        if (OC_Filesystem::is_dir($dir) and $dh = OC_Filesystem::opendir($dir)) {
            while (($fileName = readdir($dh)) !== false) {
                if ($fileName != '.' and $fileName != '..' and substr($fileName, 0, 1) != '.' and substr($fileName, -6) != '.usync') {
                    $item = new OC_Files_Item($dir, $fileName);
                    $property = $item -> property;
                    $filePath = $property -> path;
                    $markName = '';
                    # 如果不是要取得全部結構
                    if (!$showAll) {
                        $hidePathArr = OC_Files_Filter::$hidePathArr;
                        $isInHidePath = OC_Files_Filter::isInHidePath($filePath, $calledBy);
                        # 有在隱藏名單內，跳出找下一筆
                        if ($isInHidePath) {
                            continue;
                        }
                        $isInShowPath = OC_Files_Filter::isInShowPath($filePath, $calledBy);
                        # 沒有在顯示名單內，跳出找下一筆
                        if (!$isInShowPath) {
                            continue;
                        }
                    }
                    # 設置遮罩
                    $item -> setMark($calledBy);
                    # 以 file name 當 key 值，放入檔案的屬性
                    if (($getType === 'GET_ALL' or $getType === 'GET_DIR') and $property -> type === 'dir') {
                        # 只要目前的層級小於要取得的層級，則繼續取得子目錄
                        if ($maxLevel == 0 or $maxLevel > $level) {
                            $tree = self::getDirectoryContent($filePath, $maxLevel, $level, $showAll, $getType, $calledBy);
                            # 取得的子目錄不是空 array，則設置到 $property 中
                            if ($tree) {
                                $property -> tree = $tree;
                            }
                        }
                        $dirs[$fileName] = $property;
                    }
                    if (($getType === 'GET_ALL' or $getType === 'GET_FILE') and $property -> type !== 'dir') {
                        $files[$fileName] = $property;
                    }
                }
            }
            closedir($dh);
        }
        $dirs = OC_Files_Helper::sortFileByBig5FileName($dirs);
        $files = OC_Files_Helper::sortFileByBig5FileName($files);
        $files = array_merge($dirs, $files);
        return $files;
    }

    /**
     * 取得指定資料夾底下的所有檔案
     * @param $dir 資料夾路徑
     * @param $getType 要取得的類型GET_DIR/GET_FILE(預設為GET_ALL)
     * @param $calledBy 告知是由什麼呼叫(預設為ALL)
     */
    static function getTree($dir, $getType, $calledBy) {
        $tree = self::getDirectoryContent($dir, 0, $getType, $calledBy);
        return $tree;
    }

    /**
     * 取得路徑的每個節點
     */
    static function getBreadcrumb($dir) {
        $dir = OC_Files_Helper::normalizePath($dir);
        $breadcrumb = array();
        $eachPath = "";
        foreach (explode( "/", $dir ) as $fileName) {
            if ($fileName != "") {
                $eachPath .= '/' . $fileName;
                $eachDir = dirname($eachPath);
                $item = new OC_Files_Item($eachDir, $fileName);
                # 設置遮罩
                $item -> setMark('Breadcrumb');
                $property = $item -> property;
                $breadcrumb[] = $property;
            }
        }
        return $breadcrumb;
    }

    /**
     * return the content of a file or return a zip file containning multiply files
     *
     * @param dir  $dir
     * @param file $file ; seperated list of files to download
     */
    public static function get($dir, $files) {
        setlocale(LC_ALL, 'zh_TW.UTF8');
        if (strpos($files, ';')) {
            $files = explode(';', $files);
        }

        if (is_array($files)) {
            OC_Files_Zip::validateZipDownload($dir, $files);
            $executionTime = intval(ini_get('max_execution_time'));
            set_time_limit(0);
            $zip = new ZipArchive();
            $filePath = get_temp_dir() . '/' . OC_User::getUser() . '_' . basename($dir) . '_' . mt_rand(10000, 99999) . '.zip';
            if ($zip -> open($filePath, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE) !== TRUE) {
                exit("cannot open <$filePath>\n");
            }
            foreach ($files as $file) {
                $file = $dir . '/' . $file;
                if (OC_Filesystem::is_file($file)) {
                    $tmpFile = OC_Filesystem::toTmpFile($file);
                    self::$tmpFiles[] = $tmpFile;
                    $zip -> addFile($tmpFile, iconv('UTF-8', 'Big5//IGNORE', basename($file)));
                } elseif (OC_Filesystem::is_dir($file)) {
                    OC_Files_Zip::zipAddDir($file, $zip);
                }
            }
            $zip -> close();
            self::$tmpFiles[] = $filePath;
            set_time_limit($executionTime);
        } elseif (OC_Filesystem::is_dir($dir . '/' . $files)) {
            OC_Files_Zip::validateZipDownload($dir, $files);
            $executionTime = intval(ini_get('max_execution_time'));
            set_time_limit(0);
            $zip = new ZipArchive();
            $filePath = get_temp_dir() . '/' . OC_User::getUser() . '_' . basename($dir) . '_' . mt_rand(10000, 99999) . '.zip';
            if ($zip -> open($filePath, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE) !== TRUE) {
                exit("cannot open <$filePath>\n");
            }
            $file = $dir . '/' . $files;
            OC_Files_Zip::zipAddDir($file, $zip);
            $zip -> close();
            self::$tmpFiles[] = $filePath;
            set_time_limit($executionTime);
        } else {
            $zip = false;
            $filePath = $dir . '/' . $files;
        }
        self::markTmpFiles();
        if ($zip or OC_Filesystem::is_readable($filePath)) {
            # IE11 的 user agent 沒有[MSIE]關鍵字，所以此判斷式無效
            // if (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
            // header('Content-Disposition: attachment; filename="' . urlencode(basename($filePath)) . '"');
            // } else {
            // header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            // }
            # 判斷檔名是不是中文
            $fileName = basename($filePath);
            if (mb_strlen($fileName, 'Big5') != strlen($fileName)) {
                # 非 safrai 瀏覽器，一律轉為 big5
                $userAgent = $_SERVER['HTTP_USER_AGENT'];
                if (!stristr($userAgent, 'safari')) {
                    $fileName = iconv('UTF-8', 'Big5//IGNORE', $fileName);
                }
            }
            header('Content-Disposition: attachment; filename="' . $fileName . '"');

            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            if ($zip) {
                header('Content-Type: application/zip');
                header('Content-Length: ' . filesize($filePath));
            } else {
                header('Content-Type: ' . OC_Filesystem::getMimeType($filePath));
                header('Content-Length: ' . OC_Filesystem::filesize($filePath));
            }
        } elseif ($zip or !OC_Filesystem::file_exists($filePath)) {
            header("HTTP/1.0 404 Not Found");
            $tmpl = new OC_Template('', '404', 'guest');
            $tmpl -> assign('file', $filePath);
            $tmpl -> printPage();
            // 			die('404 Not Found');
        } else {
            header("HTTP/1.0 403 Forbidden");
            die('403 Forbidden');
        }
        @ob_end_clean();
        if ($zip) {
            readfile($filePath);
            unlink($filePath);
        } else {
            OC_Filesystem::readfile($filePath);
        }
    }

    /**
     * move a file or folder
     *
     * @param dir  $sourceDir
     * @param file $source
     * @param dir  $targetDir
     * @param file $target
     */
    public static function move($sourceDir, $source, $targetDir, $target) {
        if (OC_User::isLoggedIn()) {
            if (OC_Filesystem::diffCaseExistsInTargetPath($targetDir . '/' . $target))
                return false;
            $targetFile = OC_Files_Helper::normalizePath($targetDir . '/' . $target);
            $sourceFile = OC_Files_Helper::normalizePath($sourceDir . '/' . $source);
            return OC_Filesystem::rename($sourceFile, $targetFile);
        }
    }

    /**
     * copy a file or folder
     *
     * @param dir  $sourceDir
     * @param file $source
     * @param dir  $targetDir
     * @param file $target
     */
    public static function copy($sourceDir, $source, $targetDir, $target) {
        if (OC_User::isLoggedIn()) {
            $targetFile = $targetDir . '/' . $target;
            $sourceFile = $sourceDir . '/' . $source;
            return OC_Filesystem::copy($sourceFile, $targetFile);
        }
    }

    /**
     * create a new file or folder
     *
     * @param dir  $dir
     * @param file $name
     * @param type $type
     */
    public static function newFile($dir, $name, $type) {
        if (OC_User::isLoggedIn()) {
            $file = $dir . '/' . $name;
            if (OC_Filesystem::diffCaseExistsInTargetPath($file))
                return false;
            if ($type == 'dir') {
                return OC_Filesystem::mkdir($file);
            } elseif ($type == 'file') {
                $fileHandle = OC_Filesystem::fopen($file, 'w');
                if ($fileHandle) {
                    fclose($fileHandle);
                    return true;
                } else {
                    return false;
                }
            }
        }
    }

    /**
     * deletes a file or folder
     *
     * @param dir  $dir
     * @param file $name
     */
    public static function delete($dir, $file) {
        if (OC_User::isLoggedIn()) {
            $file = $dir . '/' . $file;
            return OC_Filesystem::unlink($file);
        }
    }

    static function markTmpFiles() {
        $worklistFile = get_temp_dir() . '/__ownCloudUndeletedTempfiles';
        if (file_exists($worklistFile)) {
            $unlinkFiles = unserialize(file_get_contents($worklistFile));
            if (is_array($unlinkFiles)) {
                self::$tmpFiles = array_merge(self::$tmpFiles, $unlinkFiles);
            }
        }
        if (count(self::$tmpFiles) > 0) {
            file_put_contents($worklistFile, serialize(self::$tmpFiles));
        }
    }

    static function cleanTmpFiles() {
        $worklistFile = get_temp_dir() . '/__ownCloudUndeletedTempfiles';
        if (file_exists($worklistFile)) {
            $unlinkFiles = unserialize(file_get_contents($worklistFile));
            if (is_array($unlinkFiles)) {
                foreach ($unlinkFiles as $key => $tmpFile) {
                    if (file_exists($tmpFile) and is_file($tmpFile)) {
                        if (unlink($tmpFile)) {
                            unset($unlinkFiles[$key]);
                        }
                    } else {
                        unset($unlinkFiles[$key]);
                    }
                }
            }
            unlink($worklistFile);
        }
    }

    /**
     * try to detect the mime type of a file
     * @param  string  path
     * @return string  guessed mime type
     */
    static function getMimeType($path) {
        return OC_Filesystem::getMimeType($path);
    }

    /**
     * pull a file from a remote server
     * @param  string  source
     * @param  string  token
     * @param  string  dir
     * @param  string  file
     * @return string  guessed mime type
     */
    static function pull($source, $token, $dir, $file) {
        $tmpfile = tempnam(get_temp_dir(), 'remoteCloudFile');
        $fp = fopen($tmpfile, 'w+');
        $url = $source .= "/files/pull.php?token=$token";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_exec($ch);
        fclose($fp);
        $info = curl_getinfo($ch);
        $httpCode = $info['http_code'];
        curl_close($ch);
        if ($httpCode == 200 or $httpCode == 0) {
            OC_Filesystem::fromTmpFile($tmpfile, $dir . '/' . $file);
            return true;
        } else {
            return false;
        }
    }

    /**
     * set the maximum upload size limit for apache hosts using .htaccess
     * @param int size filesisze in bytes
     */
    static function setUploadLimit($size) {
        $size = OC_Helper::humanFileSize($size);
        $size = substr($size, 0, -1);
        //strip the B
        $size = str_replace(' ', '', $size);
        //remove the space between the size and the postfix
        $content = "ErrorDocument 404 /" . OC::$WEBROOT . "/core/templates/404.php\n";
        //custom 404 error page
        $content .= "php_value upload_max_filesize $size\n";
        //upload limit
        $content .= "php_value post_max_size $size\n";
        $content .= "SetEnv htaccessWorking true\n";
        $content .= "Options -Indexes\n";
        @file_put_contents(OC::$SERVERROOT . '/.htaccess', $content);
        //supress errors in case we don't have permissions for it
    }

    static function getDirectorySize($path) {
        $totalsize = 0;
        $totalcount = 0;
        $dircount = 0;
        if ($handle = opendir($path)) {
            while (false !== ($file = readdir($handle))) {
                $nextpath = $path . '/' . $file;
                if ($file != '.' and $file != '..' and !is_link($nextpath)) {
                    if (is_dir($nextpath)) {
                        $dircount++;
                        $result = self::getDirectorySize($nextpath);
                        $totalsize += $result['size'];
                        $totalcount += $result['count'];
                        $dircount += $result['dircount'];
                    } elseif (is_file($nextpath)) {
                        $totalsize += filesize($nextpath);
                        $totalcount++;
                    }
                }
            }
        }
        closedir($handle);
        $total['size'] = (string)$totalsize;
        $total['count'] = (string)$totalcount;
        $total['dircount'] = (string)$dircount;
        return $total;
    }

    /**
     * 清空隱藏的路徑
     * @param $pathArr 要清空的隱藏路徑
     */
    static function cleanHidePath($pathArr) {
        OC_Files_Filter::cleanHidePath($pathArr);
    }

    /**
     * 新增要隱藏的路徑
     * @param $pathArr 路徑，可為 array 或 string
     * @param $inCalledBy 指定，當被什麼呼叫的時候，才要隱藏(預設置為ALL，就是無論是由什麼呼叫，都要隱藏)
     */
    static function addHidePath($pathArr, $inCalledBy = 'ALL') {
        OC_Files_Filter::addHidePath($pathArr, $inCalledBy);
    }

    /**
     * 新增要顯示的路徑
     * @param $pathArr 路徑，可為 array 或 string
     * @param $inCalledBy 指定當被什麼呼叫的時候，才要顯示
     */
    static function addShowPath($pathArr, $inCalledBy = null) {
        if ($inCalledBy) {
            OC_Files_Filter::addShowPath($pathArr, $inCalledBy);
        }
    }

    /**
     * 新增/更改要另外要顯示檔名的路徑
     * @param $pathName 檔案路徑
     * @param $fileName 要顯示的檔名
     * @param $inCalledBy 指定，當被什麼呼叫的時候，才要另外顯示檔名(預設置為ALL，就是無論是由什麼呼叫，都要另外顯示檔名)
     */
    static function addMarkFileName($filePath, $fileName, $inCalledBy = 'ALL') {
        OC_Files_Mark::addMarkFileName($filePath, $fileName, $inCalledBy);
    }

    private static function _sortFileByBig5FileName($files) {
        //如果系統預設的語系是zh_TW
        if (OC_Config::getValue('defaultLanguage', null, 'CONFIG_CUSTOM') === 'zh_TW') {
            $fileNameArr = array();
            foreach ($files as $key => $property) {
                # 將file name 從utf8 轉成 big5 (如果遇到big5無法呈現在字元，則找出替代字或乎略)
                $fileNameArr[] = iconv("UTF-8", "big5//TRANSLIT//IGNORE", $property -> markName);
            }
            array_multisort($fileNameArr, SORT_STRING, SORT_ASC, $files);
        }
        return $files;
    }

}
