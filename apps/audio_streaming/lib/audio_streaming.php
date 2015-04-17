<?php
/**
 * ownCloud - Audio Streaming plugin
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 * 將音樂檔做串流播放
 * 需要Tomcat Server做播放(只支援mp3)
 * 直接將檔案做link到Tomcat Server的temp路徑底下(預設為/var/www/html/data/MG/webapps/AudioGateTest/temp/md5(使用者ID)/輸出檔名(.mp3)
 * 回傳link的URL給前端播放器播放
 *
 * 另外有 audio_streaming_remove_link_crontab.php 執行清除所有link
 */

class OC_AudioStreaming extends OC_AudioStreaming_Settings {

    /**
     * 確認是否為音樂格式
     * @param 檔案的內部路徑
     * @return boolean
     */
    static function isAudio($path) {
        if (OC_Filesystem::file_exists($path)) {
            $mediaTypeArray = OC_Helper::audioTypeArr();
            $mime = OC_Filesystem::getMimeType($path);
            if (in_array($mime, $mediaTypeArray)) {
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * 產生link到Tomcat server下,並回傳link的網址
     * @param dir,fileName
     * @return array(status,message)
     */
    static function getStreamingSource($dir, $fileName, $ifCheckAutioType = true) {
        $sourcePath = OC_Helper::pathForbiddenChar($dir . '/' . $fileName);
        # 確認檔案是否存在
        if (!OC_Filesystem::file_exists($sourcePath)) {
            return;
        }
        # 確認檔案是否為音樂格式
        if ($ifCheckAutioType && !self::isAudio($sourcePath)) {
            return;
        }
        $path = OC_Helper::pathForbiddenChar($dir . '/' . $fileName);
        # 檔案原始路徑
        $sourcePath = OC_LocalSystem::getLocalPath($path);
        # 檔案完整路徑
        $sourceFullPath = OC_LocalSystem::getLocalFullPath($path);
        # 音樂擁有者的Id
        $userId = OC_LocalSystem::getLocalUserIdByPath($path);

        $outputName = self::linkName($userId, $sourcePath);
        $linkFullPath = self::createLink($sourceFullPath, $userId, $outputName);
        # 將完整路徑轉為網址
        if (is_string($linkFullPath)) {
            $streamingDocumentPath = self::streamingDocumentPath();
            $linkUrl = preg_replace('#' . preg_quote($streamingDocumentPath) . '#', $_SERVER['HTTP_HOST'] . '/', $linkFullPath);
            # 保險,去除開頭斜線及不符合path規則字元
            $linkUrl = OC_Helper::pathForbiddenChar(ltrim($linkUrl, '/'), false);
            # 補上 8080 port(後來不需要)
            // $linkUrl = preg_replace('#' .$_SERVER['HTTP_HOST'] . '#', $_SERVER['HTTP_HOST'] . ':8080', $linkUrl);
            # streaming server的protocol為http
            $linkUrl = "http://" . $linkUrl;
            return $linkUrl;
        }
    }

    /**
     * 移除所有串流播放的link(for crontab使用)
     */
    static function removeStreamingLink() {
        $streamingTempPath = self::streamingTempPath();
        # 將該資料夾底下的index.jsp列為不要刪除的名單
        $excludePathArr = array($streamingTempPath . 'index.jsp');
        # 保險, 只刪除Tomcat Server temp資料夾底下的檔案
        OC_Helper::deleteDirByFullPath($streamingTempPath, $excludePathArr, false, true, false);
    }

    /**
     * 產生檔案的連結到Tomcat Server的temp資料夾底下
     * @param 來源完整路徑,使用者ID,輸出檔名
     * @return link full path, or void
     */
    protected static function createLink($sourceFullPath, $userId, $outputName) {
        if (file_exists($sourceFullPath)) {
            $streamingTempPath = self::streamingTempPath();
            $linkFullPath = $streamingTempPath . '/' . md5($userId) . '/' . $outputName;
            $linkFullPath = OC_Helper::pathForbiddenChar($linkFullPath);
            if (file_exists($linkFullPath)) {
                return $linkFullPath;
            }
            $linkDirFullPath = dirname($linkFullPath);
            if (OC_Helper::createDirByFullPath($linkDirFullPath) && symlink($sourceFullPath, $linkFullPath)) {
                return $linkFullPath;
            }
        }
    }

    /**
     * 回傳link檔名
     * @param 使用者ID,來源路徑
     * @return string
     */
    protected static function linkName($userId, $sourcePath) {
        # 取得副檔名
        $lastDotPot = strrpos($sourcePath, '.');
        $extension = substr($sourcePath, $lastDotPot + 1);
        #output name即為link的名稱
        return $linkName = md5($userId . $sourcePath) . "." . $extension;
    }

}
?>