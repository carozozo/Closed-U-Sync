<?php
/**
 * ownCloud - Media Convert plugin
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 *
 * 檔案操作時，格式精靈要連動執行的相關動作
 */
class OC_MediaConvert_Hooks {

    /**
     * 檔案rename前，格式精靈要執行的動作
     * @param file system傳送過來的參數 array
     */
    static function renameItem($arguments) {
        $run = $arguments[OC_Filesystem::signal_param_run];
        try {
            self::postRenameItem($arguments);
        } catch(exception $e) {
            # 發生錯誤，設置不要處理檔案
            $run = false;
        }
    }

    /**
     * 檔案rename後，格式精靈要執行的動作
     * @param file system傳送過來的參數 array
     */
    static function postRenameItem($arguments) {
        $oldPath = $arguments[OC_Filesystem::signal_param_oldpath];
        $newPath = $arguments[OC_Filesystem::signal_param_newpath];
        if (OC_MediaStreaming::isMedia($oldPath) || OC_MediaStreaming::isMedia($newPath)) {
            $oldUserId = OC_LocalSystem::getLocalUserIdByPath($oldPath);
            $oldSourcePath = OC_LocalSystem::getLocalPath($oldPath);
            $newUserId = OC_LocalSystem::getLocalUserIdByPath($newPath);
            $newSourcePath = OC_LocalSystem::getLocalPath($newPath);
            OC_MediaConvert::renameConvert($oldUserId, $oldSourcePath, $newUserId, $newSourcePath);
        } else if (OC_Filesystem::is_dir($newPath)) {
            # 如果是rename的是資料夾，則取得裡面的檔案內容
            $content = OC_Files::getDirectoryContent($oldPath);
            foreach ($content as $key => $file) {
                $fileName = $file -> basename;
                $oldFilePath = OC_Helper::pathForbiddenChar($oldPath . '/' . $fileName);
                $newFilePath = OC_Helper::pathForbiddenChar($newPath . '/' . $fileName);
                $arguments = array(
                    OC_Filesystem::signal_param_oldpath => $oldFilePath,
                    OC_Filesystem::signal_param_newpath => $newFilePath
                );
                self::renameItem($arguments);
            }
        }
    }

    /**
     * 檔案被delete前，格式精靈要執行的動作
     * @param file system傳送過來的參數 array
     */
    static function deleteItem($arguments) {
        $run = $arguments[OC_Filesystem::signal_param_run];
        try {
            self::postDeleteItem($arguments);
        } catch(exception $e) {
            # 發生錯誤，設置不要處理檔案
            $run = false;
        }
    }

    /**
     * 檔案被delete後，格式精靈要執行的動作
     * @param file system傳送過來的參數 array
     */
    static function postDeleteItem($arguments) {
        $path = $arguments[OC_Filesystem::signal_param_path];
        if (OC_MediaStreaming::isMedia($path)) {
            $userId = OC_LocalSystem::getLocalUserIdByPath($path);
            $path = OC_LocalSystem::getLocalPath($path);
            OC_MediaConvert::delConvert($userId, $path);
        } else if (OC_Filesystem::is_dir($path)) {
            $content = OC_Files::getDirectoryContent($path);
            foreach ($content as $key => $file) {
                $fileName = $file -> basename;
                $filePath = OC_Helper::pathForbiddenChar($path . '/' . $fileName);
                $arguments = array(OC_Filesystem::signal_param_path => $filePath);
                self::deleteItem($arguments);
            }
        }
    }

}
?>